#!/usr/bin/env bash
# Shared plumbing for the deterministic probes (security, API, UI/UX).
# Source this, don't execute it.
#
# Every probe emits one JSON object per check to a JSONL file:
#
#   {"id":"SEC-01","discipline":"security","title":"…","status":"pass|fail|warn|skip",
#    "severity":"critical|high|medium|low|info","blocking":true|false,"evidence":"…"}
#
# Two separate axes, and conflating them is the mistake to avoid:
#
#   severity  — how bad it would be if real. Reporting only.
#   blocking  — whether THIS pipeline turns it into a "needs correction" verdict.
#
# Only a small, unambiguous core is blocking: things that are wrong on any build
# of this application, in any configuration, with no judgement call
# (a served .env, a stack trace in a response, an admin endpoint answering an
# anonymous request). Everything else is reported and left to a person, because a
# first-round gate that fails on a pre-existing hardening gap fails every PR
# equally and teaches the team to ignore it.

set -uo pipefail

PROBE_OUT="${PROBE_OUT:-probe-results.jsonl}"
PROBE_UA='faveo-qa-first-round/1.0'
PROBE_TIMEOUT="${PROBE_TIMEOUT:-20}"

probe_init() {
  PROBE_OUT="$1"
  : > "$PROBE_OUT"
}

# probe_record <id> <discipline> <severity> <blocking> <status> <title> <evidence>
probe_record() {
  jq -cn --arg id "$1" --arg d "$2" --arg sev "$3" --argjson blocking "$4" \
        --arg st "$5" --arg t "$6" --arg e "$7" \
    '{id:$id, discipline:$d, severity:$sev, blocking:$blocking, status:$st, title:$t, evidence:$e}' \
    >> "$PROBE_OUT"
  printf '  %-8s %-5s %s\n' "$1" "$5" "$6" >&2
}

# pass/fail take the blocking flag; warn never blocks.
#   probe_pass      <id> <discipline> <severity> <blocking> <title> [evidence]
#   probe_warn      <id> <discipline> <severity> <title> [evidence]
#   probe_skip      <id> <discipline> <title> [evidence]     a non-blocking check
#   probe_unchecked <id> <discipline> <title> [evidence]     a BLOCKING check that
#                                                            did not run
#
# The distinction matters more than it looks. A skipped blocking check is not a
# pass — it is the absence of evidence, and summarize.sh turns any of them into an
# "unknown" verdict so no approval label is applied. Without that, a runner where
# Playwright cannot install emits one skip, finds zero blocking failures, and the
# round signs off having never opened a browser.
probe_pass()      { probe_record "$1" "$2" "$3" "$4" pass "$5" "${6:-}"; }

# Findings that reproduce on the base branch predate every PR the pipeline will
# ever see. Charging one to whichever change happens to be under test blocks an
# author on a defect they never touched, and a gate that fails every PR equally
# is one the team learns to ignore. Those ids are listed in
# known-pre-existing.txt and reported as warnings, carrying the reason, so they
# stay visible without blocking. Delete an entry the moment its defect is fixed:
# a stale one would silence a genuine regression in the same check.
PROBE_KNOWN_FILE="${PROBE_KNOWN_FILE:-${BASH_SOURCE[0]%/*}/known-pre-existing.txt}"

probe_known_reason() {
  [[ -f "$PROBE_KNOWN_FILE" ]] || return 1
  awk -v want="$1" '
    /^[[:space:]]*(#|$)/ { next }
    $1 == want { $1 = ""; sub(/^[[:space:]]+/, ""); print; found = 1; exit }
    END { exit(found ? 0 : 1) }
  ' "$PROBE_KNOWN_FILE"
}

probe_fail() {
  local reason
  if reason=$(probe_known_reason "$1") && [[ -n "$reason" ]]; then
    probe_record "$1" "$2" "$3" false warn "$5" \
      "pre-existing, not introduced by this change — ${reason}${6:+ | }${6:-}"
    return
  fi
  probe_record "$1" "$2" "$3" "$4" fail "$5" "${6:-}"
}
probe_warn()      { probe_record "$1" "$2" "$3" false warn "$4" "${5:-}"; }
probe_skip()      { probe_record "$1" "$2" info false skip "$3" "${4:-}"; }
probe_unchecked() { probe_record "$1" "$2" high true skip "$3" "${4:-}"; }

# ---------------------------------------------------------------------------
# HTTP
# ---------------------------------------------------------------------------

# curl with the settings every check wants: no redirect following (a 302 is
# frequently the answer being tested), a timeout, and no proxy inheritance.
probe_curl() {
  local extra=()
  # QA hosts frequently carry a self-signed certificate. Opt-in rather than
  # always-on: silently accepting any certificate would hide a genuine TLS
  # misconfiguration on a host that is supposed to have a real one.
  [[ "${PROBE_INSECURE:-0}" == "1" ]] && extra+=(-k)
  curl -sS --noproxy '*' --max-time "$PROBE_TIMEOUT" -A "$PROBE_UA" "${extra[@]}" "$@"
}

# probe_code <url> [curl args...] -> HTTP status code
probe_code() {
  local url="$1"; shift
  probe_curl -o /dev/null -w '%{http_code}' "$@" "$url" 2>/dev/null || printf '000'
}

# probe_body <url> [curl args...] -> response body on stdout
probe_body() {
  local url="$1"; shift
  probe_curl "$@" "$url" 2>/dev/null || true
}

# probe_headers <url> [curl args...] -> response headers on stdout
probe_headers() {
  local url="$1"; shift
  probe_curl -sI "$@" "$url" 2>/dev/null || true
}

# probe_header_value <headers-blob> <name> -> value, case-insensitive
probe_header_value() {
  printf '%s\n' "$1" | awk -v want="$(printf '%s' "$2" | tr '[:upper:]' '[:lower:]')" '
    { line = $0; sub(/\r$/, "", line)
      split(line, kv, ":")
      k = tolower(kv[1])
      if (k == want) { sub(/^[^:]*:[ \t]*/, "", line); print line } }'
}

# Laravel and PHP leak their internals in recognisable ways. One list, used by
# every check that asks "did this response spill the stack?".
PROBE_LEAK_PATTERN='Whoops|Stack trace|vendor/laravel/framework|Illuminate\\\\|SQLSTATE|Fatal error|Uncaught .*Exception|/var/www/'

probe_leaks_internals() {
  grep -qEi "$PROBE_LEAK_PATTERN" <<<"$1"
}

# ---------------------------------------------------------------------------
# session login — needed by every authorisation check
# ---------------------------------------------------------------------------
# Faveo's panel API is session-authenticated (routes/web.php, `auth` middleware),
# not token-authenticated, so an authorisation probe has to hold a real session.
# That means a cookie jar and the CSRF token out of the login page.

# probe_login <base> <email> <password> <cookie-jar> -> 0 on success
#
# Reuses a jar that still authenticates. Logging in is not free here: the
# application counts every attempt against a 5-attempt lockout, so two probes each
# logging in twice is most of the budget spent before the browser suite starts.
# Point QA_SESSION_JAR_DIR at a directory shared between probe runs to make one
# login serve all of them.
probe_login() {
  local base="$1" email="$2" password="$3" jar="$4" page token

  # A shared jar, when one is configured and still valid.
  if [[ -n "${QA_SESSION_JAR_DIR:-}" ]]; then
    local shared="${QA_SESSION_JAR_DIR}/$(printf '%s' "$email" | md5sum | cut -c1-12).jar"
    mkdir -p "$QA_SESSION_JAR_DIR" 2>/dev/null || true
    if [[ -s "$shared" ]] \
       && [[ "$(probe_code "${base}/api/agent-info" -b "$shared" -H 'Accept: application/json')" == "200" ]]; then
      cp -f "$shared" "$jar"
      return 0
    fi
    # Log in below, then publish the jar for the next probe.
    QT_PUBLISH_JAR="$shared"
  fi

  # Dusk's session-bypass route, when the ids are published. One GET, one cookie —
  # no CSRF token, no concurrent-session 409, and nothing counted against the
  # 5-attempt lockout that otherwise blocks the rest of the round.
  # DuskServiceProvider registers it on any non-production environment, which the
  # disposable per-PR instance is.
  if [[ -s "${QA_USERS_FILE:-}" ]]; then
    local uid
    uid=$(jq -r --arg e "$email" 'to_entries[] | select(.value.email == $e) | .value.id' "$QA_USERS_FILE" 2>/dev/null | head -1)
    if [[ -n "$uid" && "$uid" != "null" ]]; then
      rm -f "$jar"
      probe_curl -L -c "$jar" -b "$jar" -o /dev/null "${base}/_dusk/login/${uid}" 2>/dev/null || true
      if [[ "$(probe_code "${base}/api/agent-info" -b "$jar" -H 'Accept: application/json')" == "200" ]]; then
        [[ -n "${QT_PUBLISH_JAR:-}" ]] && cp -f "$jar" "$QT_PUBLISH_JAR" 2>/dev/null || true
        return 0
      fi
      printf 'probe: /_dusk/login/%s did not authenticate — falling back to the login form\n' "$uid" >&2
    fi
  fi

  rm -f "$jar"
  # GET /login is a redirect to the SPA login route, so follow it: without -L the
  # body is the empty redirect page and the CSRF token is never found, which
  # surfaces later as an unexplained 419.
  # /login, not /auth/login: only POST auth/logout and POST auth/register live
  # under the auth/ prefix. GET auth/login 404s.
  page=$(probe_curl -L -c "$jar" -b "$jar" "${base}/login") || return 1

  # The token appears as a meta tag on the SPA shell and as a hidden input on the
  # server-rendered form; accept either rather than depending on which one this
  # build serves.
  token=$(grep -oE 'name="csrf-token" content="[^"]+"' <<<"$page" | head -1 | sed 's/.*content="//; s/"$//')
  [[ -n "$token" ]] || token=$(grep -oE 'name="_token" value="[^"]+"' <<<"$page" | head -1 | sed 's/.*value="//; s/"$//')

  local code
  code=$(probe_curl -o /dev/null -w '%{http_code}' -c "$jar" -b "$jar" \
           -X POST "${base}/login" \
           -H "X-CSRF-TOKEN: ${token}" \
           -H 'X-Requested-With: XMLHttpRequest' \
           --data-urlencode "_token=${token}" \
           --data-urlencode "email=${email}" \
           --data-urlencode "password=${password}")

  # 200 (XHR login) and 302 (form post) both mean the credentials were accepted;
  # 419 means the CSRF token never made it, which is a probe fault, not a finding.
  case "$code" in
    200|302)
      [[ -n "${QT_PUBLISH_JAR:-}" ]] && cp -f "$jar" "$QT_PUBLISH_JAR" 2>/dev/null || true
      return 0 ;;
    419) printf 'probe: login CSRF token missing or stale (HTTP 419)\n' >&2; return 2 ;;
    *)   printf 'probe: login as %s failed with HTTP %s\n' "$email" "$code" >&2; return 1 ;;
  esac
}
