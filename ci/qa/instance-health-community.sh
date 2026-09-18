#!/usr/bin/env bash
# Community-specific replacement for the health-check HALF of the shared,
# verbatim-imported ci/qa/instance-health.sh (Commit-1). That file is NOT
# edited here — see ci/qa/IMPORTED-FROM's rule against forking shared files —
# because it hard-codes two things that are true in the advance app and false
# in Community:
#
#   * a licence gate (CheckValidLicense -> /licenseError) — Community has no
#     licensing at all (env.community.sh, "no CheckValidLicense, no
#     faveo_license"), so that branch can never fire here and exit code 3 is
#     unreachable by design, not by omission (FREESTYLE-PLAN.md, "Keep 0/4,
#     ignore 3").
#   * a Vue SPA entry script under public/build/v<version>/entry/*.js on the
#     /login page — Community serves plain Blade (verified below), so this
#     check ALWAYS reports exit 4 ("unusable") on a perfectly healthy
#     Community instance. That is a real, previously-flagged defect in the
#     shared file (see the prior implementation report, "defects found in
#     imported files") and freestyle/provision.sh must not rely on it.
#
#   ci/qa/instance-health-community.sh <base-url>
#
# Exit codes (matches the shared script's contract so callers do not need two
# code paths — only the MEANING of 4 differs in what it checks for):
#   0  healthy — serving, not stuck on the install wizard, and the real Blade
#      login form renders
#   4  unusable — no answer, install wizard, or the login form did not render
#      as expected. A caller MUST treat this as a hard stop: do not proceed to
#      probes or the executor against an instance that failed this check.
#
# Verified against this working tree, not assumed:
#   - app/Http/Middleware/Install.php redirects to `step1` (the install
#     wizard) whenever base_path('.env') does not exist. provision.sh writes
#     .env via testing-setup before this ever runs, so landing on `step1`
#     here means provisioning did not actually complete — a real failure, not
#     a flaky check.
#   - Auth::routes() (routes/web.php:29) registers the standard Laravel
#     `GET login` route Community's health check targets. The rendered view
#     (resources/views/themes/default1/login/login.blade.php) is Blade, not a
#     Vue mount point: it contains a `login-box` div and a
#     `{!! html()->form('POST', route('post.login'))->open() !!}` form with
#     `name="email"` and `name="password"` fields rendered server-side. All
#     three strings are checked for below — a page that answers 200 but is
#     missing them did not actually render the login form (a stack trace, an
#     unrelated redirect target, or a half-broken theme all fail this check
#     honestly instead of passing on HTTP status alone).
#
# Does NOT attempt an authenticated check — that needs the seeded fixture
# cast (freestyle/provision.sh seeds users AFTER this could run, and
# probes/security-community.sh already exercises an authenticated round trip
# with real credentials). This script only answers "is the instance up and
# actually serving Community's UI", which is what must be true before either
# probes or the AI executor start.

set -uo pipefail

base="${1:?usage: instance-health-community.sh <base-url>}"
base="${base%/}"
curl_opts=(-sS --noproxy '*' --max-time 25 -A 'faveo-qa-first-round/1.0')
[[ "${PROBE_INSECURE:-0}" == "1" ]] && curl_opts+=(-k)

# One request, both answers: the status after following redirects and where
# it ended up.
read -r root_code final_url < <(
  curl "${curl_opts[@]}" -o /dev/null -L -w '%{http_code} %{url_effective}\n' "${base}/" 2>/dev/null || true
)
root_code="${root_code:-000}"

case "$root_code" in
  2*|3*) ;;
  *) printf 'health(community): %s did not answer (HTTP %s)\n' "$base" "$root_code" >&2; exit 4 ;;
esac

if [[ "${final_url:-}" == *step1* || "${final_url:-}" == *installer* ]]; then
  printf 'health(community): %s redirects to the install wizard (%s) — .env is missing or the app was never migrated; provisioning did not complete\n' \
    "$base" "${final_url}" >&2
  exit 4
fi

login_url="${base}/login"
login_page=$(mktemp)
login_code=$(curl "${curl_opts[@]}" -L -o "$login_page" -w '%{http_code}' "$login_url" 2>/dev/null)
login_code="${login_code:-000}"
login_body=$(cat "$login_page" 2>/dev/null || printf '')
rm -f "$login_page"

if [[ -z "$login_body" ]]; then
  printf 'health(community): GET %s answered HTTP %s with an empty body (/ had redirected to %s)\n' \
    "$login_url" "$login_code" "${final_url:-?}" >&2
  exit 4
fi

if grep -qiE 'whoops|stack trace|ErrorException|exception' <<<"$login_body" \
   && ! grep -qF 'login-box' <<<"$login_body"; then
  printf 'health(community): GET %s rendered an error page instead of the login form\n' "$login_url" >&2
  printf 'health(community): first 200 bytes: %s\n' "$(head -c 200 <<<"$login_body" | tr -d '\n')" >&2
  exit 4
fi

missing=()
grep -qF 'login-box' <<<"$login_body"          || missing+=('login-box container')
grep -qE 'name=["'\'']email["'\'']' <<<"$login_body"    || missing+=('email field')
grep -qE 'name=["'\'']password["'\'']' <<<"$login_body" || missing+=('password field')

if [[ "${#missing[@]}" -gt 0 ]]; then
  printf 'health(community): the login page did not render Community'"'"'s Blade login form — missing: %s (%s bytes of HTML served, final URL %s)\n' \
    "$(IFS=', '; echo "${missing[*]}")" "${#login_body}" "${final_url:-?}" >&2
  exit 4
fi

printf 'health(community): %s is serving and its real Blade login form renders (HTTP %s at %s)\n' \
  "$base" "$login_code" "$login_url"
exit 0