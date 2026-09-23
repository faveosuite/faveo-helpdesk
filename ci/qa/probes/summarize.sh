#!/usr/bin/env bash
# Merge probe results into one markdown section and one machine-readable verdict.
#
#   ci/qa/probes/summarize.sh <out-prefix> <results.jsonl>...
#
# Writes <out-prefix>.md and <out-prefix>.json.
#
# The verdict rule, and the reason for it:
#
#   any blocking failure           -> "correction"
#   any blocking check NOT RUN     -> "unknown"   (no label is applied)
#   otherwise                      -> "pass"
#
# The middle rule is the one worth defending. A skipped blocking check is the
# absence of evidence: if Playwright cannot install, the browser suite emits one
# skip, there are zero blocking failures, and a naive rule would sign the round
# off having never opened a browser. "We could not check" must never render as
# "we checked and it was fine".
#
# Warnings never decide the verdict. A first round that fails a PR for a
# pre-existing hardening gap or a missing alt attribute fails every PR equally,
# and a gate that is always red is a gate nobody reads. Blocking findings are the
# ones with no judgement in them: a served .env, a stack trace in a response, an
# admin endpoint answering anonymously, a panel that renders blank.

set -euo pipefail

prefix="${1:?usage: summarize.sh <out-prefix> <results.jsonl>...}"; shift
(( $# )) || { printf 'summarize: no result files given\n' >&2; exit 2; }

all=$(mktemp)
trap 'rm -f "$all"' EXIT
for f in "$@"; do [[ -s "$f" ]] && cat "$f" >> "$all"; done
[[ -s "$all" ]] || printf '' > "$all"

jq -s '
  {
    total:    length,
    passed:   [.[] | select(.status == "pass")]  | length,
    failed:   [.[] | select(.status == "fail")]  | length,
    warnings: [.[] | select(.status == "warn")]  | length,
    skipped:  [.[] | select(.status == "skip")]  | length,
    blocking_failures: [.[] | select(.status == "fail" and .blocking)] | length,
    blocking_skipped:  [.[] | select(.status == "skip" and .blocking)] | length,
    by_discipline: (group_by(.discipline) | map({
      key: .[0].discipline,
      value: {
        passed: ([.[] | select(.status == "pass")] | length),
        failed: ([.[] | select(.status == "fail")] | length),
        warnings: ([.[] | select(.status == "warn")] | length),
        skipped: ([.[] | select(.status == "skip")] | length)
      }}) | from_entries),
    findings: .
  }
  | .verdict = (if .blocking_failures > 0 then "correction"
                elif .blocking_skipped > 0 then "unknown"
                else "pass" end)
' "$all" > "${prefix}.json"

# ---------------------------------------------------------------------------
# markdown
# ---------------------------------------------------------------------------
{
  jq -r '
    def icon: if . == "pass" then ":white_check_mark:"
              elif . == "fail" then ":x:"
              elif . == "warn" then ":warning:"
              else ":heavy_minus_sign:" end;

    "### Automated checks",
    "",
    "| Discipline | Passed | Failed | Warnings | Skipped |",
    "|---|---:|---:|---:|---:|",
    ( .by_discipline | to_entries[]
      | "| " + .key + " | " + (.value.passed|tostring) + " | " + (.value.failed|tostring)
        + " | " + (.value.warnings|tostring) + " | " + (.value.skipped|tostring) + " |" ),
    "",

    ( if .blocking_failures > 0 then
        "> [!CAUTION]\n> **" + (.blocking_failures|tostring) +
        " blocking check(s) failed.** These are unambiguous defects, not judgement calls."
      elif .blocking_skipped > 0 then
        "> [!WARNING]\n> **" + (.blocking_skipped|tostring) +
        " blocking check(s) did not run**, so this round cannot say the build is sound. Nothing failed, but nothing proved it either — see \"Not run\" below."
      else
        "> [!NOTE]\n> No blocking check failed."
      end ),
    "",

    ( if ([.findings[] | select(.status == "fail")] | length) > 0 then
        ( "#### Failures",
          "",
          "| Check | Discipline | Severity | Blocking | What happened |",
          "|---|---|---|---|---|",
          ( .findings[] | select(.status == "fail")
            | "| `" + .id + "` | " + .discipline + " | " + .severity + " | "
              + (if .blocking then "yes" else "no" end) + " | **" + .title + "**"
              + (if (.evidence // "") == "" then "" else "<br>" + (.evidence | gsub("\\|"; "\\|") | gsub("\n"; " ")) end)
              + " |" ),
          "" )
      else empty end ),

    ( if ([.findings[] | select(.status == "warn")] | length) > 0 then
        ( "<details><summary>Warnings — reported, not blocking (" +
          ([.findings[] | select(.status == "warn")] | length | tostring) + ")</summary>",
          "",
          "| Check | Discipline | Severity | Observation |",
          "|---|---|---|---|",
          ( .findings[] | select(.status == "warn")
            | "| `" + .id + "` | " + .discipline + " | " + .severity + " | " + .title
              + (if (.evidence // "") == "" then "" else "<br>" + (.evidence | gsub("\\|"; "\\|") | gsub("\n"; " ")) end)
              + " |" ),
          "",
          "</details>",
          "" )
      else empty end ),

    ( if ([.findings[] | select(.status == "skip")] | length) > 0 then
        ( "<details open><summary>Not run (" +
          ([.findings[] | select(.status == "skip")] | length | tostring) +
          ") — these prove nothing either way</summary>",
          "",
          ( .findings[] | select(.status == "skip")
            | "- " + (if .blocking then "**(blocking)** " else "" end)
              + "`" + .id + "` " + .title
              + (if (.evidence // "") == "" then "" else " — " + .evidence end) ),
          "",
          "</details>",
          "" )
      else empty end ),

    ( "<details><summary>All checks (" + (.total|tostring) + ")</summary>",
      "",
      ( .findings[] | "- " + (.status | icon) + " `" + .id + "` " + .title ),
      "",
      "</details>" )
  ' "${prefix}.json"
} > "${prefix}.md"

verdict=$(jq -r '.verdict' "${prefix}.json")
printf 'summarize: %s — %s blocking failure(s), %s blocking not run, %s failure(s), %s warning(s), %s skipped\n' \
  "$verdict" \
  "$(jq -r '.blocking_failures' "${prefix}.json")" \
  "$(jq -r '.blocking_skipped' "${prefix}.json")" \
  "$(jq -r '.failed' "${prefix}.json")" \
  "$(jq -r '.warnings' "${prefix}.json")" \
  "$(jq -r '.skipped' "${prefix}.json")" >&2
