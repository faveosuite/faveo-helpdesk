#!/usr/bin/env bash
# Runtime (DAST) security probe against the throwaway Community instance.
#
#   ci/qa/probes/security-community.sh <base-url> <out.jsonl>
#
# The generic-application half of the advance repo's probes/security.sh
# (ci/qa/README.advance.md / FREESTYLE-PLAN.md §2, axis B). Community has no
# `/api/admin/*` boundary and no `/api/agent-info` — routes/api.php holds one
# stub route — so the role-boundary and admin-data-leak checks that depend on
# that surface (advance SEC-08, SEC-22) are DROPPED, not adapted: there is
# nothing behind those paths to probe, and reporting them as "pass" would claim
# coverage that does not exist. Session/auth/CSRF/header/cookie/exposed-file
# checks apply unchanged in spirit and are kept, retargeted at Community's real
# routes (routes/web.php, verified against this working tree).
#
# Uses probes/lib.sh's existing framework (probe_pass/probe_fail/probe_warn/
# probe_skip/probe_unchecked, probe_login, probe_code/probe_body/probe_headers) —
# same JSONL shape and severity/blocking discipline as the advance probes, so
# probes/summarize.sh (imported unchanged) reads this file the same way.
#
# Always exits 0. Findings are data, not build failures.

set -uo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
. "${here}/lib.sh"

base="${1:?usage: security-community.sh <base-url> <out.jsonl>}"
out="${2:?usage: security-community.sh <base-url> <out.jsonl>}"
base="${base%/}"

probe_init "$out"
printf 'security probe (community) against %s\n' "$base" >&2

D=security
jar_admin=$(mktemp); jar_agent=$(mktemp)
trap 'rm -f "$jar_admin" "$jar_agent"' EXIT

# ---------------------------------------------------------------------------
# exposed files — blocking, no configuration makes serving these acceptable
# ---------------------------------------------------------------------------

check_not_served() {
  local id="$1" path="$2" needle="$3" sev="$4" body code
  code=$(probe_code "${base}${path}")
  body=$(probe_body "${base}${path}")
  if [[ "$code" == "200" ]] && grep -qE "$needle" <<<"$body"; then
    probe_fail "$id" "$D" "$sev" true "${path} is served over HTTP" \
      "HTTP 200, body matched /${needle}/ — first 120 bytes: $(head -c 120 <<<"$body" | tr -d '\n')"
  else
    probe_pass "$id" "$D" "$sev" true "${path} is not served" "HTTP ${code}"
  fi
}

check_not_served SEC-01 '/.env'                     'APP_KEY|DB_PASSWORD|APP_ENV' critical
check_not_served SEC-02 '/.git/config'              '\[core\]|remote "origin"'    critical
check_not_served SEC-03 '/storage/logs/laravel.log' 'local\.|production\.|Stack trace' high
check_not_served SEC-04 '/composer.json'            '"require"|"autoload"'        medium
check_not_served SEC-05 '/.env.example'             'APP_KEY|DB_DATABASE'         low

# ---------------------------------------------------------------------------
# error handling — blocking. A stack trace names the framework, the paths and
# frequently the query.
# ---------------------------------------------------------------------------

body=$(probe_body "${base}/this-route-does-not-exist-$(date +%s)")
if probe_leaks_internals "$body"; then
  probe_fail SEC-06 "$D" critical true "A 404 response leaks framework internals" \
    "matched: $(grep -oEi "$PROBE_LEAK_PATTERN" <<<"$body" | head -3 | tr '\n' ' ')"
else
  probe_pass SEC-06 "$D" critical true "404 responses do not leak internals" ''
fi

# routes/api.php holds one stub route (`/user/test`, auth:api). A malformed
# request to a nonexistent API path exercises the same APP_DEBUG question the
# advance probe asks of its mobile/dependency endpoint, without assuming any
# Community API route exists.
body=$(probe_body "${base}/api/this-does-not-exist-%00%ff")
if probe_leaks_internals "$body"; then
  probe_fail SEC-07 "$D" critical true "A malformed API-prefixed request returns a debug stack trace" \
    "APP_DEBUG is almost certainly on — $(head -c 160 <<<"$body" | tr -d '\n')"
else
  probe_pass SEC-07 "$D" critical true "Malformed API-prefixed requests do not return debug output" ''
fi

# ---------------------------------------------------------------------------
# authentication boundary — blocking
# ---------------------------------------------------------------------------
# routes/web.php:366 gates `dashboard` behind `install, update, auth,
# role.agent` — no prefix, unlike the advance /panel/... catch-all.
code=$(probe_code "${base}/dashboard")
if [[ "$code" == "302" || "$code" == "301" || "$code" == "401" || "$code" == "403" ]]; then
  probe_pass SEC-09 "$D" high true "/dashboard is not reachable while logged out" "HTTP ${code}"
else
  probe_warn SEC-09 "$D" medium "/dashboard returned HTTP ${code} while logged out" \
    'expected a redirect to login'
fi

# NOTE (dropped, not adapted): advance SEC-08 (anonymous /api/admin/* and
# /api/agent-info data leak) and SEC-22 (agent session reaching admin-only
# /api/admin/* endpoints) have no Community equivalent — routes/api.php has no
# such surface. Do not add a synthetic replacement; an absent API is not the
# same finding as a present-but-guarded one.

# ---------------------------------------------------------------------------
# CSRF — blocking
# ---------------------------------------------------------------------------
# routes/web.php:30 registers POST `login` (post.login) as well as POST
# auth/login; probe against the same bare `/login` the advance probe uses,
# since Community registers it too.

csrf_code=$(probe_code "${base}/login" -X POST -H 'Accept: application/json' \
        --data-urlencode 'email=qa-probe@example.com' --data-urlencode 'password=whatever')
csrf_body=$(probe_body "${base}/login" -X POST -H 'Accept: application/json' \
        --data-urlencode 'email=qa-probe@example.com' --data-urlencode 'password=whatever')

if [[ "$csrf_code" == "419" || "$csrf_code" == "403" ]]; then
  probe_pass SEC-10 "$D" high true "POST without a CSRF token is rejected" "HTTP ${csrf_code}"
elif [[ "$csrf_code" == "422" ]] && grep -qiE 'token|csrf|refresh the page' <<<"$csrf_body"; then
  probe_pass SEC-10 "$D" high true "POST without a CSRF token is rejected" \
    "HTTP 422 — $(jq -r '.message // empty' <<<"$csrf_body" 2>/dev/null || head -c 80 <<<"$csrf_body")"
else
  probe_fail SEC-10 "$D" high true "POST /login without a CSRF token returned HTTP ${csrf_code}" \
    "expected a token rejection; body: $(head -c 160 <<<"$csrf_body" | tr -d '\n')"
fi

# ---------------------------------------------------------------------------
# headers and cookies — reported, not blocking (pre-existing hardening gaps
# must not fail every PR equally).
# ---------------------------------------------------------------------------

headers=$(probe_headers "${base}/auth/login")
for pair in 'X-Frame-Options:SEC-11' 'X-Content-Type-Options:SEC-12' 'Strict-Transport-Security:SEC-13'; do
  name="${pair%%:*}"; id="${pair##*:}"
  value=$(probe_header_value "$headers" "$name")
  if [[ -n "$value" ]]; then
    probe_pass "$id" "$D" low false "${name} is set" "$value"
  else
    probe_warn "$id" "$D" medium "${name} is missing" ''
  fi
done

xfo=$(probe_header_value "$headers" 'X-Frame-Options')
if grep -qi 'allow.from' <<<"$xfo"; then
  probe_warn SEC-14 "$D" medium 'X-Frame-Options uses the obsolete ALLOW-FROM form' \
    "value: ${xfo} — no current browser honours ALLOW-FROM; frame-ancestors in a CSP is the replacement"
fi

if [[ -z "$(probe_header_value "$headers" 'Content-Security-Policy')" ]]; then
  probe_warn SEC-15 "$D" medium 'No Content-Security-Policy header' \
    'the main defence-in-depth control against injected script'
fi

cookies=$(probe_headers "${base}/auth/login" | grep -i '^set-cookie:' || true)
if [[ -n "$cookies" ]]; then
  session_cookie=$(grep -iE 'session' <<<"$cookies" | head -1)
  if [[ -n "$session_cookie" ]]; then
    grep -qi 'httponly' <<<"$session_cookie" \
      && probe_pass SEC-16 "$D" high true 'Session cookie is HttpOnly' '' \
      || probe_fail SEC-16 "$D" high true 'Session cookie is not HttpOnly' \
           "$(head -c 160 <<<"$session_cookie")"
    grep -qi 'samesite' <<<"$session_cookie" \
      && probe_pass SEC-17 "$D" low false 'Session cookie sets SameSite' '' \
      || probe_warn SEC-17 "$D" medium 'Session cookie has no SameSite attribute' \
           "$(head -c 160 <<<"$session_cookie")"
  fi
fi

server_header=$(probe_header_value "$headers" 'X-Powered-By')
[[ -n "$server_header" ]] && probe_warn SEC-18 "$D" low 'X-Powered-By discloses the PHP version' "$server_header"

# ---------------------------------------------------------------------------
# injection surfaces — reported. Only unauthenticated endpoints are reachable
# here, so absence proves little and must not read as "no injection issues".
# ---------------------------------------------------------------------------

xss_marker='qaprobe<svg/onload=1>'
xss_body=$(probe_body "${base}/auth/login?redirect=$(jq -rn --arg s "$xss_marker" '$s|@uri')")
if grep -qF "$xss_marker" <<<"$xss_body"; then
  probe_fail SEC-20 "$D" critical true 'A query parameter is reflected into the page unescaped' \
    'payload came back verbatim in the HTML'
else
  probe_pass SEC-20 "$D" high false 'Query parameters are not reflected unescaped' ''
fi

# NOTE (dropped): advance SEC-19 sends a SQLi payload at /api/url-info, which
# does not exist in Community. No unauthenticated Community route accepts a
# comparable free-text query parameter that reaches a database query without
# authentication first — left unchecked rather than invented against a route
# that isn't there.

# ---------------------------------------------------------------------------
# brute force — reported, never blocking. Off by default: see advance
# probes/security.sh for the account-lockout rationale. Community's Kernel.php
# applies only the framework default `throttle:api` (RouteServiceProvider); no
# attempt_locks table or per-account lockout exists here (verified: no
# migration creates attempt_locks), so a burst only risks the framework's
# general rate limiter, not a specific account lockout — still off by default
# to avoid burning the framework limiter budget the rest of the round needs.
# ---------------------------------------------------------------------------

if [[ "${QA_PROBE_BRUTE_FORCE:-0}" != "1" ]]; then
  probe_skip SEC-21 "$D" 'Login rate-limit check not run (off by default)' \
    'set QA_PROBE_BRUTE_FORCE=1 in a build that does nothing else'
else
  throttled=false; attempts=0
  for _ in $(seq 1 20); do
    attempts=$(( attempts + 1 ))
    code=$(probe_code "${base}/login" -X POST \
            -H 'Accept: application/json' \
            --data-urlencode 'email=qa-probe@example.com' \
            --data-urlencode 'password=definitely-wrong')
    [[ "$code" == "429" ]] && { throttled=true; break; }
  done

  if $throttled; then
    probe_pass SEC-21 "$D" medium false 'Failed logins are rate limited' \
      "HTTP 429 after ${attempts} attempts"
  else
    probe_warn SEC-21 "$D" medium "${attempts} failed logins in a row were not rate limited" \
      'no login-specific limiter found'
  fi
fi

# ---------------------------------------------------------------------------
# Dusk's test-login route — reported, never blocking. laravel/dusk is a dev
# dependency here too (vendor/laravel/dusk present, tests/DuskTestCase.php
# exists) and DuskServiceProvider registers /_dusk/login/{id} on any
# non-production environment, same as advance.
# ---------------------------------------------------------------------------

dusk_route_code=$(probe_code "${base}/_dusk/login/1")
if [[ "$dusk_route_code" == "404" ]]; then
  probe_pass SEC-24 "$D" high false "Dusk's test-login route is not registered" \
    'the app is running as production, or Dusk is not installed'
else
  probe_warn SEC-24 "$D" high "Dusk's password-free login route answers HTTP ${dusk_route_code}" \
    'expected on a testing instance — Dusk registers /_dusk/login/{id} on every non-production environment. On any real deployment APP_ENV MUST be production, or this is a full authentication bypass.'
fi

# ---------------------------------------------------------------------------
# session-after-logout — blocking. The one session-management failure that is
# both common and unambiguous. Uses probe_login (lib.sh, unmodified) and the
# `dashboard` route as the authenticated probe endpoint, since Community has
# no /api/agent-info.
#
# CAVEAT: probe_login()'s shared-jar reuse and its /_dusk/login fast path both
# validate success by requesting lib.sh's hardcoded `${base}/api/agent-info`,
# which does not exist in Community and always answers non-200 here. Every
# call therefore falls through to the real login-form POST, which still
# authenticates correctly (probe_login returns 0 on HTTP 200/302 from that
# POST) but neither shortcut applies — see this port's report, "found
# defects in imported files", for detail. lib.sh is Commit-1 shared logic and
# is not edited here.
# ---------------------------------------------------------------------------

if [[ -n "${QA_ADMIN_EMAIL:-}" && -n "${QA_ADMIN_PASSWORD:-}" ]] \
   && probe_login "$base" "$QA_ADMIN_EMAIL" "$QA_ADMIN_PASSWORD" "$jar_admin"; then
  before=$(probe_code "${base}/dashboard" -b "$jar_admin")

  token=$(probe_body "${base}/auth/login" -b "$jar_admin" -L \
          | grep -oE 'name="csrf-token" content="[^"]+"' | head -1 | sed 's/.*content="//; s/"$//')
  logout_code=$(probe_curl -o /dev/null -w '%{http_code}' -b "$jar_admin" -c "$jar_admin" \
    -X GET "${base}/auth/logout" \
    -H "X-CSRF-TOKEN: ${token}" -H 'X-Requested-With: XMLHttpRequest' 2>/dev/null || printf '000')
  after=$(probe_code "${base}/dashboard" -b "$jar_admin")

  if [[ "$before" != "200" ]]; then
    probe_unchecked SEC-23 "$D" 'Logout check did not run' \
      "GET /dashboard returned ${before} while logged in, so the check proves nothing"
  elif [[ "$logout_code" != "200" && "$logout_code" != "302" && "$logout_code" != "204" ]]; then
    probe_unchecked SEC-23 "$D" 'Logout check did not run' \
      "GET /auth/logout answered ${logout_code} — the probe never logged out"
  elif [[ "$after" == "200" ]]; then
    probe_fail SEC-23 "$D" high true 'The session still authenticates after logout' \
      "GET /dashboard was ${before} before logout, GET /auth/logout returned ${logout_code}, and it still answered ${after}"
  else
    probe_pass SEC-23 "$D" high true 'Logout invalidates the session' \
      "${before} before, ${after} after (logout ${logout_code})"
  fi
else
  probe_unchecked SEC-23 "$D" 'Logout check did not run' 'no admin credentials, or the admin login failed'
fi

printf 'security probe (community): %s checks\n' "$(wc -l < "$out")" >&2
exit 0
