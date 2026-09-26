#!/usr/bin/env bash
# The PR-side marker's read path, offline: gh_comments is stubbed with canned comment
# bodies. This is what the resume path stands on — a round that stops part-way through
# its issues records the ones it finished, and the next build reads them back and runs
# only the remainder.
#
#   ci/qa/tests/marker-resume.sh
set -uo pipefail

# CAPABILITY GUARD (Community). This suite came from the advance repo, where
# the function below exists. Community imported an older marker.sh and does not
# have it. Skipping with an explanation beats failing with "command not found",
# which says nothing about why, and beats deleting the suite, which would hide
# the gap entirely.
if ! grep -q "^pr_marker_issues()" "$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)/marker.sh"; then
  cat >&2 <<'MSG'
marker-resume: Community's ci/qa/marker.sh has no pr_marker_issues().

That function is the PR-side multi-issue resume: a round that stops part-way
records the issues it finished, and the next build runs only the remainder.
Community's gate selects ONE issue per round -- ci/qa/stage3-gate.sh emits
{pr, issue, ...}, not an issues[] array -- so the
resume path was never imported and there is nothing here to test.

This is a real gap, not just an absent test — ci/qa/tests/gate-scenarios.sh
asserts the consequence directly: re-triggering a build on an unchanged commit
re-runs the round. Importing pr-marker.sh and the marker resume functions is
what makes this suite meaningful; until then it skips.

Skipping — not a failure.
MSG
  exit 0
fi
pass=0; fail=0

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
QA_SRC="${QA_SRC:-$(cd "$HERE/.." && pwd)}"

gh_comments() { printf '%s' "${T_COMMENTS:-[]}"; }
. "$QA_SRC/marker.sh"

SHA=1d44f9dd
V2='<!-- qa-first-round {"v":2,"sha":"1d44f9dd","run":"M7kRkX","passed":8,"failed":1,"blocked":0,"issues":[11514,15232]} -->'
V1='<!-- qa-first-round {"v":1,"sha":"1d44f9dd","run":"M7kRkX","passed":8,"failed":1,"blocked":0} -->'
OTHER='<!-- qa-first-round {"v":2,"sha":"0badcafe","run":"","passed":0,"failed":0,"blocked":0,"issues":[999]} -->'

comments() { jq -n --arg a "$1" --arg b "${2:-}" '[{body:$a}] + (if $b == "" then [] else [{body:$b}] end)'; }

t() { # t <label> <expected> <actual>
  if [[ "$3" == "$2" ]]; then printf 'PASS  %-50s -> %s\n' "$1" "${3:-<empty>}"; pass=$((pass+1))
  else printf 'FAIL  %-50s -> got %q want %q\n' "$1" "$3" "$2"; fail=$((fail+1)); fi
}

export T_COMMENTS="$(comments "round 1 report$'\n'$V2")"
t "v2: issues for a matching sha"      "11514 15232" "$(pr_marker_issues 42 "$SHA" | tr '\n' ' ' | sed 's/ $//')"
t "v2: nothing for a different sha"    ""            "$(pr_marker_issues 42 deadbeef | tr '\n' ' ' | sed 's/ $//')"
t "v2: counts come back whole"         "8 1 0"       "$(pr_marker_json 42 "$SHA" | jq -r '"\(.passed) \(.failed) \(.blocked)"')"
t "v2: pr_marker_exists still true"    "yes"         "$(pr_marker_exists 42 "$SHA" && echo yes || echo no)"

export T_COMMENTS="$(comments "round 1 report$'\n'$V1")"
t "v1: no issue list"                  ""            "$(pr_marker_issues 42 "$SHA" | tr '\n' ' ' | sed 's/ $//')"
t "v1: still short-circuits the gate"  "yes"         "$(pr_marker_exists 42 "$SHA" && echo yes || echo no)"

export T_COMMENTS="$(comments "unrelated comment" "another one")"
t "no marker at all"                   ""            "$(pr_marker_issues 42 "$SHA" | tr '\n' ' ' | sed 's/ $//')"
t "no marker: exists is false"         "no"          "$(pr_marker_exists 42 "$SHA" && echo yes || echo no)"

export T_COMMENTS="$(comments "old commit$'\n'$OTHER" "this commit$'\n'$V2")"
t "picks the marker for THIS sha only" "11514 15232" "$(pr_marker_issues 42 "$SHA" | tr '\n' ' ' | sed 's/ $//')"

# The build the union exists for: round 1 covered 11514, round 2 covers 15232, and the
# comment that replaces round 1's must still name both.
export T_COMMENTS="$(comments "round 1$'\n'<!-- qa-first-round {\"v\":2,\"sha\":\"1d44f9dd\",\"run\":\"M7kRkX\",\"passed\":5,\"failed\":0,\"blocked\":0,\"issues\":[11514]} -->")"
covered_before=$(pr_marker_issues 42 "$SHA" | tr '\n' ' ' | sed 's/ $//')
t "resume: prior coverage is readable" "11514" "$covered_before"
t "resume: prior counts are readable"  "5"     "$(pr_marker_json 42 "$SHA" | jq -r '.passed')"

echo
echo "$pass passed, $fail failed"
exit $(( fail > 0 ? 1 : 0 ))
