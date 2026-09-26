#!/usr/bin/env bash
# Scenario runner for stage3-gate.sh, offline: no GitHub token, no network.
#
# The gate sources gh-client.sh and marker.sh from its OWN directory, so the suite
# assembles a directory holding the real gate beside the stubs in stubs/ and runs
# that.
#
#   ci/qa/tests/gate-scenarios.sh
#
# ADAPTED FOR COMMUNITY. Two differences from the advance/invoicing copy, both
# marked at the assertion that carries them: Community's gate returns ONE issue
# per round rather than an issues[] array, and Community's labels carry emoji
# shortcodes.
# Labels encode spaces as ~. COMMUNITY VALUES, which differ from the advance and
# invoicing ones: Community's GitHub labels carry emoji shortcodes, and the gate
# matches the label text verbatim. Dropping a shortcode here makes every scenario
# skip with "PR is not labelled ..." while still reporting passes, because a
# quiet skip is a legitimate outcome for several of them.
#
# These are hardcoded, as in the invoicing copy, because the authoritative values
# now live in the pipeline script's environment{} block and that script is not in
# this repo. ci/qa/tests/pipeline-contracts.sh checks the script's own labels
# against its trigger regex; keep these three in step with it by hand.
TRIG='Requires~Functionality~Review~:pray:'
CODE='Code~Approved~:heart_eyes_cat:'
APPR='QA:~Test~case~Approved'
pass=0; fail=0

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
QA_SRC="${QA_SRC:-$(cd "$HERE/.." && pwd)}"
STAGE_DIR="$(mktemp -d)"
trap 'rm -rf "$STAGE_DIR"' EXIT
cp "$QA_SRC"/stage3-gate.sh "$HERE"/stubs/gh-client.sh "$HERE"/stubs/marker.sh "$STAGE_DIR"/

run() { env "$@" bash "$STAGE_DIR"/stage3-gate.sh 42 2>/tmp/g.err; }

check() { # check <label> <expected-exit> <expected-issues-csv> <env...>
  local label="$1" wantrc="$2" wantissues="$3"; shift 3
  local out rc got
  out=$(run "$@"); rc=$?
  # Community's gate emits {pr,issue,...} — ONE issue, the first linked one whose
  # cases are approved. The advance/invoicing gate emits an issues[] array and
  # runs them all in one round. Reading .issues[] here returns empty for every
  # Community run, which silently turns each rc=0 scenario into a comparison of
  # "" against "" the moment the expectation is also blanked.
  got=$(jq -r '.issue // empty' <<<"$out" 2>/dev/null || echo '')
  if [[ "$rc" == "$wantrc" && "$got" == "$wantissues" ]]; then
    printf 'PASS  %-44s rc=%s issues=[%s]\n' "$label" "$rc" "$got"; pass=$((pass+1))
  else
    printf 'FAIL  %-44s rc=%s (want %s) issues=[%s] (want [%s])\n' "$label" "$rc" "$wantrc" "$got" "$wantissues"
    sed 's/^/        /' /tmp/g.err | tail -6; fail=$((fail+1))
  fi
}

BASE=(T_GREEN=1 T_REVIEW=0)
LB="42:${TRIG}|${CODE}"

# Community runs ONE issue per round, the first approved one — not all three.
check "first approved issue is selected" 0 "11" "${BASE[@]}" T_LINKED="11 22 33" \
  T_LABELS="$LB 11:${APPR} 22:${APPR} 33:${APPR}"

check "one unapproved, one has no cases" 0 "33" "${BASE[@]}" T_LINKED="11 22 33" \
  T_LABELS="$LB 33:${APPR}" T_NOMARKER="22"

# 22 is Manual and skipped; Community takes 11 and stops looking.
check "Manual issue excluded, first of rest runs" 0 "11" "${BASE[@]}" T_LINKED="11 22 33" \
  T_LABELS="$LB 11:${APPR} 22:${APPR}|Manual 33:${APPR}"

# DEVIATION, NOT A PREFERENCE. Issue 11 is already recorded as done for this
# sha; the advance gate skips it and returns [22,33]. Community returns 11 —
# it has no per-sha resume, because the multi-issue marker machinery that
# carries it (pr-marker.sh, marker.sh's pr_marker_issues) was not imported.
# The consequence is real: re-triggering a build on an unchanged commit
# re-runs the round and writes a second set of QA Touch results. This asserts
# what Community DOES so the behaviour is visible and a future import of the
# resume path fails this line loudly instead of passing unnoticed.
check "no per-sha resume: a done issue re-runs" 0 "11" "${BASE[@]}" T_LINKED="11 22 33" \
  T_LABELS="$LB 11:${APPR} 22:${APPR} 33:${APPR}" T_DONE="11"

# Same root cause as above: the advance gate skips the whole round (rc 3) once
# every linked issue is done for this sha. Community re-runs the first one.
check "no per-sha resume: all-done still runs" 0 "11" "${BASE[@]}" T_LINKED="11 22" \
  T_LABELS="$LB 11:${APPR} 22:${APPR}" T_DONE="11 22"

check "v1 marker still short-circuits"   3 "" "${BASE[@]}" T_LINKED="11" \
  T_LABELS="$LB 11:${APPR}" T_MARKER_SHA=1

check "nothing approved -> skip"         3 "" "${BASE[@]}" T_LINKED="11 22" T_LABELS="$LB"

check "PR Manual still stops everything" 3 "" "${BASE[@]}" T_LINKED="11" \
  T_LABELS="42:${TRIG}|${CODE}|Manual 11:${APPR}"

check "checks red -> skip"               3 "" T_GREEN=0 T_REVIEW=0 T_LINKED="11" \
  T_LABELS="$LB 11:${APPR}"

check "explain + red checks emits none"  3 "" T_GREEN=0 T_REVIEW=0 QA_GATE_EXPLAIN=1 \
  T_LINKED="11" T_LABELS="$LB 11:${APPR}"

# One issue per round, so the milestone travels with that one issue.
check "milestone travels with the selected issue" 0 "11" "${BASE[@]}" T_LINKED="11 22" \
  T_LABELS="$LB 11:${APPR} 22:${APPR}" T_MS="11=Billing_v9.4.3.7.RC.1"

# One unparseable marker among many good issues must not discard six issues that
# had already been selected and validated — it should exclude only itself.
# Community stops at the first approved issue (11), so a malformed marker on a
# LATER issue is never parsed. The advance gate examines every issue and drops
# only the bad one, hence [11,33]. Both refuse to act on a marker they cannot
# read; they differ only in how much they look at.
check "a malformed marker on a later issue is never reached" 0 "11" "${BASE[@]}" T_LINKED="11 22 33" \
  T_LABELS="$LB 11:${APPR} 22:${APPR} 33:${APPR}" T_BADMARKER="22"

# ...but when it is the ONLY reason nothing ran, staying quiet would leave a broken
# marker ignored on every event for the life of the commit.
check "malformed marker alone stops loudly"   4 "" "${BASE[@]}" T_LINKED="22" \
  T_LABELS="$LB 22:${APPR}" T_BADMARKER="22"

check "malformed + nothing approved: stops"   4 "" "${BASE[@]}" T_LINKED="11 22" \
  T_LABELS="$LB" T_BADMARKER="22"

check "explain mode stops on a bad marker"    4 "" "${BASE[@]}" QA_GATE_EXPLAIN=1 \
  T_LINKED="22" T_LABELS="$LB 22:${APPR}" T_BADMARKER="22"

# No malformed marker anywhere: nothing-runnable stays a QUIET skip, as before.
check "no marker at all is still a quiet skip" 3 "" "${BASE[@]}" T_LINKED="11" \
  T_LABELS="$LB 11:${APPR}" T_NOMARKER="11"

echo "--- malformed marker travels to the PR (scenario above):"
run "${BASE[@]}" T_LINKED="11 22 33" T_LABELS="$LB 11:${APPR} 22:${APPR} 33:${APPR}" T_BADMARKER="22" \
  | jq -r '.passed_over[] | "  #\(.issue): \(.reason)"'

echo "--- passed_over reasons (scenario 3):"
run "${BASE[@]}" T_LINKED="11 22 33" T_LABELS="$LB 11:${APPR} 22:${APPR}|Manual 33:${APPR}" \
  | jq -r '.passed_over[] | "  #\(.issue): \(.reason)"'
echo "--- compat shim (scenario 1):"
run "${BASE[@]}" T_LINKED="11 22 33" T_LABELS="$LB 11:${APPR} 22:${APPR} 33:${APPR}" \
  | jq -c '{issue, milestone, marker_present:(.marker!=null), sha}'
echo
echo "$pass passed, $fail failed"
exit $(( fail > 0 ? 1 : 0 ))
