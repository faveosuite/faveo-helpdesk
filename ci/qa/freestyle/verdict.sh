#!/usr/bin/env bash
# Shell port of Jenkinsfile.qa's decideVerdict() + verdictBanner() +
# applyVerdictLabels() (advance repo, lines 2073-2134), reading counts from the
# executor's machine marker exactly as readCaseCounts()/extractFirstRoundMarker()
# do (lines 2031-2065).
#
#   ci/qa/freestyle/verdict.sh <pr-number>
#
# SIMPLIFICATION FROM THE ADVANCE PIPELINE (flagged, not silently absorbed):
# the advance Pipeline can run a round that covers SEVERAL linked issues in one
# PR, sums their counts, and merges their per-issue markers into one. This
# Freestyle port follows FREESTYLE-PLAN.md §6 steps 05-06, which execute (and
# therefore report on) a single linked issue per round — so this script reads
# ONE report file (qa-report-<pr>.md), not several. If Community later needs
# multi-issue rounds, this is the file to extend: sum counts across
# qa-report-<pr>-<issue>.md the way readCaseCounts() does, and merge markers
# the way firstRoundMarker() does.
#
# Reads:
#   qa-report-<pr>.md   — the executor's report; last line carries
#                         <!-- qa-first-round {...} -->
#   qa-probe-<pr>.json  — probes/summarize.sh output (verdict, blocking, notRun)
#
# Writes:
#   qa-body-<pr>.md     — banner + probe summary + report, ready for
#                         upsert-comment.sh
# Applies (unless QA_CI_SETS_VERDICT=false): QA_ROUND1_PASS_LABEL /
# QA_CORRECTION_LABEL via label.sh — never on an "unknown" verdict.
#
# Exit 0 always; the verdict itself, printed on stdout, is what matters
# ("pass" | "correction" | "unknown").

set -uo pipefail

pr="${1:?usage: verdict.sh <pr-number>}"
tools="${QA_TOOLS:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
report_file="qa-report-${pr}.md"
probe_file="qa-probe-${pr}.json"
body_file="qa-body-${pr}.md"

extract_marker() {
  # Same regex extractFirstRoundMarker() uses (@NonCPS in the Pipeline, plain
  # grep/sed here — there is no CPS transform to work around in shell).
  grep -o '<!--[[:space:]]*qa-first-round[[:space:]]*{.*}[[:space:]]*-->' "$1" 2>/dev/null \
    | tail -1 \
    | sed -E 's/^<!--[[:space:]]*qa-first-round[[:space:]]*//; s/[[:space:]]*-->$//'
}

counts_json='null'
if [[ -f "$report_file" ]]; then
  marker_json=$(extract_marker "$report_file")
  if [[ -n "$marker_json" ]] && jq -e . >/dev/null 2>&1 <<<"$marker_json"; then
    counts_json="$marker_json"
  fi
fi

probe_verdict=unknown
probe_blocking=0
probe_notrun=0
probe_summary=''
if [[ -f "$probe_file" ]]; then
  probe_verdict=$(jq -r '.verdict // "unknown"' "$probe_file")
  probe_blocking=$(jq -r '.blocking_failures // 0' "$probe_file")
  probe_notrun=$(jq -r '.blocking_skipped // 0' "$probe_file")
fi
summary_file="qa-probe-${pr}.md"
[[ -f "$summary_file" ]] && probe_summary=$(cat "$summary_file")

# decideVerdict(), verbatim rule set:
#   pass       nothing blocking failed AND at least one case ran and none failed
#   correction something blocking failed, or a case failed
#   unknown    not enough evidence either way — no label applied
if [[ "$probe_verdict" == "correction" ]]; then
  verdict=correction
elif [[ "$counts_json" == "null" ]]; then
  verdict=unknown
else
  failed=$(jq -r '.failed // 0' <<<"$counts_json")
  passed=$(jq -r '.passed // 0' <<<"$counts_json")
  blocked=$(jq -r '.blocked // 0' <<<"$counts_json")
  if [[ "$failed" -gt 0 ]]; then
    verdict=correction
  elif [[ "$passed" -gt 0 && "$blocked" == 0 && "$probe_verdict" == "pass" ]]; then
    verdict=pass
  else
    verdict=unknown
  fi
fi

# verdictBanner()
if [[ "$counts_json" == "null" ]]; then
  case_line='No approved test cases were executed in this run.'
else
  p=$(jq -r '.passed // 0' <<<"$counts_json")
  f=$(jq -r '.failed // 0' <<<"$counts_json")
  b=$(jq -r '.blocked // 0' <<<"$counts_json")
  case_line="Test cases: **${p} passed**, **${f} failed**, **${b} blocked**."
fi

if [[ -n "$probe_summary" ]]; then
  if [[ "$probe_notrun" -gt 0 ]]; then
    probe_line="Automated checks: **${probe_blocking} blocking failure(s)**, **${probe_notrun} blocking check(s) not run**."
  else
    probe_line="Automated checks: **${probe_blocking} blocking failure(s)**."
  fi
else
  probe_line='Automated checks did not run.'
fi

case "$verdict" in
  pass)
    banner="> [!TIP]
> **First round passed.** ${case_line} ${probe_line}" ;;
  correction)
    banner="> [!IMPORTANT]
> **Functionality correction needed.** ${case_line} ${probe_line}
> Details below. Fix and push — the next round re-runs automatically on the next review or check event." ;;
  *)
    banner="> [!NOTE]
> **Inconclusive.** ${case_line} ${probe_line}
> No verdict label was applied: part of the round did not run, and a pass would
> claim coverage that does not exist." ;;
esac

{
  printf '%s\n\n' "$banner"
  [[ -n "$probe_summary" ]] && printf '%s\n\n' "$probe_summary"
  [[ -f "$report_file" ]] && cat "$report_file"
} > "$body_file"

# applyVerdictLabels()
if [[ "${QA_CI_SETS_VERDICT:-true}" != "true" ]]; then
  echo "verdict ${verdict} — QA_CI_SETS_VERDICT is off, leaving labels to a person" >&2
else
  case "$verdict" in
    pass)
      bash "${tools}/label.sh" add    "$pr" "${QA_ROUND1_PASS_LABEL:-QA: Round 1 Testing Approved :clap:}" || true
      bash "${tools}/label.sh" remove "$pr" "${QA_CORRECTION_LABEL:-Functionality Correction :facepunch:}" || true
      ;;
    correction)
      bash "${tools}/label.sh" add    "$pr" "${QA_CORRECTION_LABEL:-Functionality Correction :facepunch:}" || true
      bash "${tools}/label.sh" remove "$pr" "${QA_ROUND1_PASS_LABEL:-QA: Round 1 Testing Approved :clap:}" || true
      ;;
    *)
      echo 'verdict inconclusive — no label applied' >&2
      ;;
  esac
fi

echo "$verdict"