#!/usr/bin/env bash
# The abort-path safety net FREESTYLE-PLAN.md §6 step 07 and §11 call for.
#
# This has NO Jenkinsfile.qa equivalent to port: the advance Pipeline relies on
# `finally { teardownInstance() }`, which Freestyle cannot express — a
# Post-build Task (ci/qa/freestyle/teardown.sh) does not run when a build is
# ABORTED, and abort is exactly what happens when the 90-minute build timeout
# fires or someone presses stop. Without this job, one aborted round leaves a
# `qa_pr_<n>` database and a bound port that collide with every later build on
# the same node.
#
# Intended to run as its own Freestyle job, `faveo-qa-janitor`, on a nightly
# cron (see FREESTYLE-PLAN.md §6). Binds ONLY MySQL credentials — it needs no
# GitHub or QA Touch token, and per FREESTYLE-PLAN.md §4 the author job must
# never hold MySQL, so this stays a third, separate job rather than folding
# into either QA job.
#
#   ci/qa/freestyle/janitor.sh [--dry-run]
#
# STATE LOCATION (documented once, here, since this is the one script that
# has to find OTHER builds' state without being told a PR number): for every
# workspace root in QA_JANITOR_WORKSPACES (default: this job's own
# $WORKSPACE), a live or dead QA instance is described by
# `<workspace>/qa-instance-state-<n>.env` — the same file
# freestyle/provision.sh writes and freestyle/teardown.sh reads. That file,
# not a bare PID file and not a port number, is this script's source of
# truth for what a database or a bound port belongs to.
#
# What it does:
#   1. For every `qa_pr_%` database, looks for a matching
#      `qa-instance-state-<n>.env` under QA_JANITOR_WORKSPACES. If found and
#      its recorded serve/proxy PID is still alive AND that PID's cmdline
#      actually matches an artisan-serve/tls-proxy process (not just "some
#      process happens to have this number"), the database is in active use
#      — leave it alone. Otherwise it is orphaned — drop it.
#   2. For every port in this pipeline's own range
#      (QA_SERVE_PORT..+50, app: +100..+150), if a process holds it, checks
#      whether that PID is named in any state file's SERVE_PID/PROXY_PID AND
#      whether its cmdline confirms it is actually an artisan-serve or
#      tls-proxy process THIS pipeline started. Only then is it killed. A
#      port held by an unclaimed but ALSO unrecognised process (not
#      artisan/tls-proxy shaped at all) is left alone and reported — the
#      janitor cleans up after itself, it does not sweep a shared node.
#   3. Removes `*-qa-tools` sibling directories with no matching live
#      workspace, and stale `qa-instance-state-<n>.env` / `qa-instance-creds-
#      <n>.env` files whose PID(s) are gone.
#
# Deliberately conservative: it only acts on `qa_pr_%` databases, ports in
# this pipeline's own configured range, and paths this pipeline itself
# creates. It never touches an application database with a different name,
# and never kills a process outside that port range or one that does not
# cmdline-match artisan-serve / tls-proxy.php.
#
# Requires: DB_USER, DB_PASS. QA_JANITOR_WORKSPACES: space-separated list of
# workspace roots to check for state files (defaults to $WORKSPACE, i.e.
# the janitor job's own workspace on this node — override if QA jobs run from
# a different one).

set -uo pipefail

dry_run=0
[[ "${1:-}" == "--dry-run" ]] && dry_run=1

: "${DB_USER:?DB_USER required}"; : "${DB_PASS:?DB_PASS required}"
workspaces="${QA_JANITOR_WORKSPACES:-${WORKSPACE:-.}}"
port_base="${QA_SERVE_PORT:-8099}"

say() { printf 'janitor: %s\n' "$*"; }

# Same ownership test freestyle/teardown.sh uses: alive AND cmdline-shaped
# like a process this pipeline starts. A bare kill -0 is not enough — that is
# the check that let a coincidentally-numbered unrelated PID pass before.
is_qa_process() {
  local pid="$1"
  [[ -n "$pid" ]] || return 1
  kill -0 "$pid" 2>/dev/null || return 1
  [[ -r "/proc/${pid}/cmdline" ]] || return 1
  tr '\0' ' ' < "/proc/${pid}/cmdline" 2>/dev/null | grep -qE 'artisan[[:space:]].*serve|tls-proxy\.php'
}

# state_pid_for <n> <SERVE_PID|PROXY_PID> — searches every workspace's state
# file for PR <n> and prints the requested PID field if the file exists.
state_pid_for() {
  local n="$1" field="$2" ws sf pid
  for ws in $workspaces; do
    sf="${ws}/qa-instance-state-${n}.env"
    [[ -f "$sf" ]] || continue
    pid=$(sed -n "s/^QA_INSTANCE_${field}=//p" "$sf" | tail -1)
    [[ -n "$pid" ]] && { printf '%s\n' "$pid"; return 0; }
  done
  return 1
}

# ---------------------------------------------------------------------------
# 1. orphaned databases
# ---------------------------------------------------------------------------
dbs=$(mysql -u"$DB_USER" -p"$DB_PASS" -Nse "SHOW DATABASES LIKE 'qa\\_pr\\_%';" 2>/dev/null || true)
if [[ -z "$dbs" ]]; then
  say "no qa_pr_% databases found"
else
  while read -r db; do
    [[ -n "$db" ]] || continue
    n="${db#qa_pr_}"
    live=0
    serve_pid=$(state_pid_for "$n" SERVE_PID || true)
    proxy_pid=$(state_pid_for "$n" PROXY_PID || true)
    if is_qa_process "${serve_pid:-}" || is_qa_process "${proxy_pid:-}"; then
      live=1
    fi
    if [[ "$live" == 1 ]]; then
      say "${db}: a live, verified QA process still references it — leaving it alone"
      continue
    fi
    if [[ "$dry_run" == 1 ]]; then
      say "${db}: orphaned — would DROP (dry run)"
    else
      say "${db}: orphaned — dropping"
      mysql -u"$DB_USER" -p"$DB_PASS" -e "DROP DATABASE IF EXISTS \`${db}\`;" 2>/dev/null || true
    fi
  done <<< "$dbs"
fi

# ---------------------------------------------------------------------------
# 2. stale ports in this pipeline's own range — kill ONLY a verified QA
#    process; a port held by anything else, claimed or not, is left alone.
# ---------------------------------------------------------------------------
for offset in $(seq 0 50); do
  for p in $(( port_base + offset )) $(( port_base + offset + 100 )); do
    holder_pid=$(fuser "${p}/tcp" 2>/dev/null | tr -d '[:space:]' || true)
    [[ -n "$holder_pid" ]] || continue

    if ! is_qa_process "$holder_pid"; then
      say "port ${p}: held by pid ${holder_pid}, which is not an artisan-serve/tls-proxy process — leaving it alone (not ours to touch)"
      continue
    fi

    # It cmdline-matches our process shape. Is it named in a LIVE build's
    # state file (i.e. a round genuinely still in progress right now)?
    claimed=0
    for ws in $workspaces; do
      for f in "${ws}"/qa-instance-state-*.env; do
        [[ -f "$f" ]] || continue
        if grep -qE "^QA_INSTANCE_(SERVE|PROXY)_PID=${holder_pid}\$" "$f"; then
          claimed=1; break 2
        fi
      done
    done
    if [[ "$claimed" == 1 ]]; then
      say "port ${p}: held by pid ${holder_pid}, matches a live build's state file — a round is legitimately in progress, leaving it alone"
      continue
    fi

    if [[ "$dry_run" == 1 ]]; then
      say "port ${p}: held by unclaimed, QA-shaped pid ${holder_pid} — would kill (dry run)"
    else
      say "port ${p}: held by unclaimed, QA-shaped pid ${holder_pid} — killing"
      kill "$holder_pid" 2>/dev/null || true
    fi
  done
done

# ---------------------------------------------------------------------------
# 3. orphaned tools snapshots and stale per-PR state/credential files
# ---------------------------------------------------------------------------
for ws in $workspaces; do
  for tools_dir in "${ws}"-qa-tools; do
    [[ -d "$tools_dir" ]] || continue
    workspace_dir="${tools_dir%-qa-tools}"
    if [[ -d "$workspace_dir" ]]; then
      say "${tools_dir}: matching workspace still present — leaving it alone"
      continue
    fi
    if [[ "$dry_run" == 1 ]]; then
      say "${tools_dir}: no matching workspace — would remove (dry run)"
    else
      say "${tools_dir}: no matching workspace — removing"
      rm -rf "$tools_dir"
    fi
  done

  for sf in "${ws}"/qa-instance-state-*.env; do
    [[ -f "$sf" ]] || continue
    serve_pid=$(sed -n 's/^QA_INSTANCE_SERVE_PID=//p' "$sf" | tail -1)
    proxy_pid=$(sed -n 's/^QA_INSTANCE_PROXY_PID=//p' "$sf" | tail -1)
    if is_qa_process "${serve_pid:-}" || is_qa_process "${proxy_pid:-}"; then
      continue
    fi
    n=$(sed -n 's/^QA_INSTANCE_PR=//p' "$sf" | tail -1)
    creds=$(sed -n 's/^QA_INSTANCE_CREDS_FILE=//p' "$sf" | tail -1)
    if [[ "$dry_run" == 1 ]]; then
      say "${sf}: no live process — would remove state file (and creds file, if any) (dry run)"
    else
      say "${sf}: no live process — removing stale state file (PR ${n:-?})"
      rm -f "$sf" "${creds:-}"
    fi
  done
done

exit 0