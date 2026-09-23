#!/usr/bin/env bash
# Is the per-build instance actually usable before we test against it?
#
#   ci/qa/instance-health.sh <base-url>
#
# Exit codes, each mapped to a different message on the PR:
#   0  healthy — serving, licensed, and serving its built front-end assets
#   3  unlicensed — requests redirect to /licenseError
#   4  unusable — no answer, or the panel's built assets are missing
#
# Why this is not one curl for a 2xx/3xx:
#
# * A fresh database from `php artisan testing-setup` holds no license row, and
#   CheckValidLicense (in the `web` middleware group) then redirects every request
#   to /licenseError — with a 302, which a naive reachability check reads as
#   healthy. The round would proceed, every browser and panel-API check would
#   fail, and the report would blame the PR for a licensing problem.
#
# * The panel is a Vue SPA served out of public/build/v<version>/. Without
#   `yarn build` the shell still returns 200 and the entry script 404s, which
#   renders as a white page — the failure this pipeline is most likely to hit,
#   and the one most likely to be misread as "the PR broke the panel".
#
# The form fields themselves cannot be checked here: they exist only after Vue
# mounts, so curl never sees them. That check belongs to the browser probe.

set -uo pipefail

base="${1:?usage: instance-health.sh <base-url>}"
base="${base%/}"
curl_opts=(-sS --noproxy '*' --max-time 25 -A 'faveo-qa-first-round/1.0')
[[ "${PROBE_INSECURE:-0}" == "1" ]] && curl_opts+=(-k)

# One request, both answers: the status after following redirects and where it
# ended up. `|| true` and a separator rather than a fallback string, because curl
# prints "000" itself on a connection failure and a second fallback would
# concatenate into "000000".
read -r root_code final_url < <(
  curl "${curl_opts[@]}" -o /dev/null -L -w '%{http_code} %{url_effective}\n' "${base}/" 2>/dev/null || true
)
root_code="${root_code:-000}"

case "$root_code" in
  2*|3*) ;;
  *) printf 'health: %s did not answer (HTTP %s)\n' "$base" "$root_code" >&2; exit 4 ;;
esac

if [[ "${final_url:-}" == *licenseError* ]]; then
  printf 'health: %s redirects to licenseError — this instance has no verified license\n' "$base" >&2
  exit 3
fi

# Where did / actually go? Report it, because the answer is the diagnosis.
if [[ "${final_url:-}" == *probe.php* ]]; then
  printf 'health: %s redirects to probe.php — the instance reports itself as NOT INSTALLED.\n' "$base" >&2
  printf 'health: app/Http/Middleware/Redirect.php sends every request there while isInstall() is false, and isInstall() is only true when config(database.install) == 1, i.e. DB_INSTALL=1 in .env.\n' >&2
  exit 4
fi

# The login page: GET /login on the SAME base URL. Not /auth/login (routes/web.php
# registers GET `login`; only POST auth/logout and POST auth/register live under the
# auth/ prefix, so /auth/login 404s with an empty body). And not the redirect target
# either — following it blindly once led this check to https://127.0.0.1:8104/probe.php,
# a scheme and a path that could never answer.
login_url="${base}/login"

login_page=$(mktemp)
# No `|| printf '000'`: curl already prints 000 through -w on a connection failure, and
# a second fallback concatenates into "000000" — which is exactly what this reported.
login_code=$(curl "${curl_opts[@]}" -L -o "$login_page" -w '%{http_code}' "$login_url" 2>/dev/null)
login_code="${login_code:-000}"
login_body=$(cat "$login_page" 2>/dev/null || printf '')
rm -f "$login_page"

if [[ -z "$login_body" ]]; then
  printf 'health: GET %s answered HTTP %s with an empty body (/ had redirected to %s)\n' \
    "$login_url" "$login_code" "${final_url:-?}" >&2
  exit 4
fi

if grep -qi 'licenseError\|license_error\|license has expired' <<<"$login_body"; then
  printf 'health: the login page renders the license error\n' >&2
  exit 3
fi

# The SPA entry script, e.g. /build/v9.4.3.6/entry/app.Dh5E61Wm.js
#
# Matched as a path and rebuilt against $base rather than captured whole: the
# shell serves some of these tags unquoted (src=https://…), so a pattern greedy
# enough to catch the URL also swallows the `src=` and produces a request for a
# path that cannot exist.
entry_path=$(grep -oE "build/v[^\"' >]+/entry/[^\"' >]+\.js" <<<"$login_body" | head -1)

if [[ -z "$entry_path" ]]; then
  printf 'health: the login page references no built entry script — public/build/v<version>/ is missing, so run yarn build (%s bytes of HTML served)\n' \
    "${#login_body}" >&2
  exit 4
fi

entry="${base}/${entry_path}"
entry_code=$(curl "${curl_opts[@]}" -o /dev/null -w '%{http_code}' "$entry" 2>/dev/null || printf '')

if [[ "$entry_code" != "200" ]]; then
  printf 'health: the SPA entry script /%s answered HTTP %s — the build manifest points at an asset that is not there (yarn build)\n' \
    "$entry_path" "$entry_code" >&2
  exit 4
fi

printf 'health: %s is serving, licensed, and its built assets load (entry %s)\n' "$base" "${entry_path##*/}"
exit 0
