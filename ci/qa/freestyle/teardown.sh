#!/usr/bin/env bash
# Shell port of Jenkinsfile.qa's teardownInstance() (advance repo, lines
# 2657-2702), trimmed of the parts that only apply to the advance Dusk/licence
# flow (.env.backup from `php artisan dusk`, .env.testing restoration —
# Community's testing-setup writes only .env; see freestyle/provision.sh).
#
#   ci/qa/freestyle/teardown.sh <pr-number>
#
# Meant to run from a Freestyle Post-build Task configured to run REGARDLESS OF
# BUILD RESULT (FREESTYLE-PLAN.md §6 step 07) — the closest Freestyle
# equivalent to the Pipeline's `finally { teardownInstance() }`. It does NOT
# run when the build is ABORTED; ci/qa/freestyle/janitor.sh exists to catch
# what this misses in that case.
#
# Every step is best-effort: a teardown failure must never mask the round's
# real result, but a leaked process or database would break the NEXT build on
# this node.
#
# OWNERSHIP SAFETY (hardening pass, FIX 3). The previous version of this
# script freed ports with an unconditional `fuser -k <port>/tcp`, which kills
# WHATEVER process holds that port — including an unrelated process, if the
# port was reused (a busy node running two things, or a stale QA round from a
# different, unrelated pipeline entirely). Ownership is now established
# before anything is killed:
#
#   1. Read PIDs from qa-instance-state-<pr>.env (written by
#      freestyle/provision.sh — the SAME per-PR state file provision.sh and
#      janitor.sh use; there is no second state mechanism).
#   2. Verify each PID is still running AND its /proc/<pid>/cmdline matches
#      the process THIS pipeline started (`artisan serve --port=<app-port>`
#      or `tls-proxy.php <port> <app-port> ...`) before sending a signal.
#   3. Port-based `fuser -k` is used ONLY as a fallback when a PID is
#      missing/stale, and even then only after the same cmdline check is
#      applied to whatever PID currently holds that exact port — so a fresh,
#      unrelated process that happens to have grabbed the same port number
#      after this instance died is left alone.
#
# Idempotent: every step is a no-op on a re-run (state file / PID files /
# database already gone), and running this twice in a row must not error or
# kill anything new the second time.
#
# Requires: DB_USER, DB_PASS (the same MySQL credential the firstround job
# binds — the post-build task must have it too, or the DROP DATABASE below is
# silently skipped).

set -uo pipefail

pr="${1:?usage: teardown.sh <pr-number>}"
db_name="qa_pr_${pr}"
ws="${WORKSPACE:-.}"
state_file="${ws}/qa-instance-state-${pr}.env"
tools_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
tools="${QA_TOOLS:-$tools_dir}"

# ---------------------------------------------------------------------------
# ownership check shared by the PID path and the fallback port path
# ---------------------------------------------------------------------------
# is_our_process <pid> <grep-pattern>
# True only if the pid is alive AND its cmdline contains the given pattern —
# e.g. "artisan serve" together with this instance's exact --port value, or
# "tls-proxy.php" together with this instance's exact port pair. A bare "is
# it alive" check is not enough: that is exactly what let an unrelated
# process sharing the port number get killed before this pass.
is_our_process() {
  local pid="$1" pattern="$2"
  [[ -n "$pid" ]] || return 1
  kill -0 "$pid" 2>/dev/null || return 1
  [[ -r "/proc/${pid}/cmdline" ]] || return 1
  tr '\0' ' ' < "/proc/${pid}/cmdline" 2>/dev/null | grep -qE "$pattern"
}

kill_if_ours() {
  local pid="$1" pattern="$2" label="$3"
  if is_our_process "$pid" "$pattern"; then
    kill "$pid" 2>/dev/null || true
    echo "teardown: killed ${label} pid ${pid}"
  elif [[ -n "$pid" ]]; then
    echo "teardown: pid ${pid} for ${label} is not (or no longer) this pipeline's process — leaving it alone" >&2
  fi
}

# ---------------------------------------------------------------------------
# load state — falls back to the legacy *.pid files and global env vars only
# when qa-instance-state-<pr>.env itself is missing (a build that failed
# before freestyle/provision.sh ever wrote it), so teardown still does
# SOMETHING useful on a very early failure.
# ---------------------------------------------------------------------------
serve_pid='' proxy_pid='' port='' app_port='' creds_file=''
if [[ -f "$state_file" ]]; then
  # shellcheck disable=SC1090
  . "$state_file"
  serve_pid="${QA_INSTANCE_SERVE_PID:-}"
  proxy_pid="${QA_INSTANCE_PROXY_PID:-}"
  port="${QA_INSTANCE_PORT:-}"
  app_port="${QA_INSTANCE_APP_PORT:-}"
  creds_file="${QA_INSTANCE_CREDS_FILE:-}"
  db_name="${QA_INSTANCE_DB:-$db_name}"
else
  echo "teardown: no ${state_file} — falling back to *.pid files and env-derived ports (best effort; this build likely failed before provisioning finished)" >&2
  [[ -f "${ws}/qa-serve-${pr}.pid" ]] && serve_pid=$(cat "${ws}/qa-serve-${pr}.pid" 2>/dev/null)
  [[ -f "${ws}/qa-proxy-${pr}.pid" ]] && proxy_pid=$(cat "${ws}/qa-proxy-${pr}.pid" 2>/dev/null)
  port="${QA_ACTIVE_PORT:-${QA_SERVE_PORT:-8099}}"
  app_port="${QA_APP_PORT:-8199}"
  creds_file="${ws}/qa-instance-creds-${pr}.sh"
fi

# Round 1 label: the one that actively lies if left behind. Best-effort against
# whatever ci/qa copy is reachable — QA_TOOLS if the tools snapshot still
# exists, ci/qa otherwise (a PR based on a branch that predates ci/qa/ has
# neither, and label.sh's own absence is not a teardown failure).
if [[ -x "${tools}/label.sh" ]]; then
  bash "${tools}/label.sh" remove "$pr" "${QA_ROUND1_PROGRESS_LABEL:-QA: Round 1 Testing In Progress}" || true
fi

# ---------------------------------------------------------------------------
# stop the app server and TLS proxy — PID + ownership first, port only as a
# narrow, verified fallback
# ---------------------------------------------------------------------------
kill_if_ours "$serve_pid" 'artisan[[:space:]].*serve' 'artisan serve'
kill_if_ours "$proxy_pid" 'tls-proxy\.php' 'tls-proxy.php'
sleep 1

for p in "$port" "$app_port"; do
  [[ -n "$p" ]] || continue
  holder=$(fuser "${p}/tcp" 2>/dev/null | tr -d '[:space:]' || true)
  [[ -n "$holder" ]] || continue
  if is_our_process "$holder" 'artisan[[:space:]].*serve|tls-proxy\.php'; then
    echo "teardown: port ${p} still held by our pid ${holder} after the PID-based kill — killing by port as a narrow fallback"
    (fuser -k "${p}/tcp" 2>/dev/null || true)
  else
    echo "teardown: port ${p} is held by pid ${holder}, which is NOT this pipeline's process — leaving it alone" >&2
  fi
done

if [[ -n "${DB_USER:-}" && -n "${DB_PASS:-}" ]]; then
  mysql -u"$DB_USER" -p"$DB_PASS" -e "DROP DATABASE IF EXISTS ${db_name};" 2>/dev/null || true
else
  echo 'teardown: DB_USER/DB_PASS not set — the database was NOT dropped; the next build on this node will collide unless something else cleans it up (see freestyle/janitor.sh)' >&2
fi

# Restore the workspace .env — provision.sh copied it to .env.qa-original
# before deleting it (SetupTestEnv::createEnv() would otherwise skip writing
# on the next round). No .env.backup / .env.testing.qa-original branch here:
# Community's testing-setup writes only .env, not the Dusk-specific env files
# the advance teardown also restores.
if [[ -f .env.qa-original ]]; then
  mv -f .env.qa-original .env
else
  rm -f .env
fi

rm -f "${creds_file:-${ws}/qa-instance-creds-${pr}.sh}" \
      "${ws}/qa-instance-state-${pr}.env" \
      "${ws}/qa-provision-phase-${pr}.txt" \
      "${ws}/qa-health-${pr}.log" \
      "${ws}/qa-serve-${pr}.pid" "${ws}/qa-proxy-${pr}.pid" \
      "${ws}/qa-tls-${pr}.pem" "${ws}/qa-tls-${pr}.crt" "${ws}/qa-tls-${pr}.key"
rm -rf "${ws}/qa-session-${pr}"

git checkout -q -f - 2>/dev/null || true
git branch -qD "qa-pr-${pr}" 2>/dev/null || true

rm -rf "${ws}-qa-tools"

if [[ -f "qa-phases-${pr}.tsv" ]]; then
  printf '%s\tend\n' "$(date +%s)" >> "qa-phases-${pr}.tsv"
  bash "${tools}/phase-summary.sh" "qa-phases-${pr}.tsv" 2>&1 || true
fi

exit 0