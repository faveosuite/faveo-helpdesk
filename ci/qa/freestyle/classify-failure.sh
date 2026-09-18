#!/usr/bin/env bash
# Shell port of the failure-classification logic embedded in Jenkinsfile.qa's
# authorForIssue() and runExecutor() (advance repo, ~lines 690-720 and
# ~1600-1646) — identical in both stages, so it is one script here instead of
# two copy-pasted blocks.
#
#   ci/qa/freestyle/classify-failure.sh <exit-code> <stream.jsonl>
#
# Prints one of: timeout | limit | other | success
#   success  exit code was 0 and the stream's last "result" event is subtype
#            "success" with is_error != true (mirrors the advance re-check —
#            exit 0 does NOT mean success on its own: a usage limit reached
#            mid-run, max turns, or an execution error can all still exit 0)
#   timeout  rc 124 (timeout(1)) or 137 (SIGKILL after -k)
#   limit    the stream mentions a usage/rate/credit limit
#   other    anything else non-zero
#
# On limit, also prints QA_RESET=<epoch>|<utc>|<ist> on a second line when
# ci/qa/limit-reset.sh (imported unchanged) can find a reset instant in the
# stream — same format the advance Pipeline's fail_file carries.
#
# Exit status: 0 always (classification is data, never a build failure).

set -uo pipefail

rc="${1:?usage: classify-failure.sh <exit-code> <stream.jsonl>}"
stream="${2:?usage: classify-failure.sh <exit-code> <stream.jsonl>}"
tools="${QA_TOOLS:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"

effective_rc="$rc"

if [[ "$rc" == 0 && -s "$stream" ]]; then
  subtype=$(jq -r 'select(.type == "result") | .subtype // empty' "$stream" 2>/dev/null | tail -1)
  iserr=$(jq -r 'select(.type == "result") | (.is_error // false) | tostring' "$stream" 2>/dev/null | tail -1)
  # No result event at all in a non-empty stream means the process died
  # mid-stream rather than finishing.
  if [[ -z "$subtype" || ( -n "$subtype" && "$subtype" != "success" ) || "$iserr" == "true" ]]; then
    effective_rc=70
  fi
fi

if [[ "$effective_rc" == 0 ]]; then
  echo "success"
  exit 0
fi

if [[ "$effective_rc" == 124 || "$effective_rc" == 137 ]]; then
  echo "timeout"
  exit 0
fi

if grep -qiE 'usage limit|rate.?limit|credit balance|quota|insufficient' "$stream" 2>/dev/null; then
  echo "limit"
  reset=$(bash "${tools}/limit-reset.sh" "$stream" 2>/dev/null || true)
  [[ -n "$reset" ]] && echo "QA_RESET=${reset}"
  exit 0
fi

echo "other"
exit 0