#!/usr/bin/env bash
# Finding a case body in a large library, offline. qt_public_get is stubbed with a
# synthetic ekXp: 446 pages, 20 cases each, codes ascending with page number — a
# large-project shape (page 445 holds TR9645-TR9664, page 418 holds TR9105-TR9124).
#
#   ci/qa/tests/case-lookup.sh
#
# The bug this guards: the old lookup scanned the newest 15 pages and reported
# anything older as "not found — treat as Blocked". Markers sit around page 418, so
# it returned a WRONG verdict for every case in a round, against cases that exist.
set -uo pipefail

# CAPABILITY GUARD (Community). This suite came from the advance repo, where
# the function below exists. Community imported an older qatouch-client.sh and does not
# have it. Skipping with an explanation beats failing with "command not found",
# which says nothing about why, and beats deleting the suite, which would hide
# the gap entirely.
if ! grep -q "^qt_cases_by_codes()" "$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)/qatouch-client.sh"; then
  cat >&2 <<'MSG'
case-lookup: Community's ci/qa/qatouch-client.sh has no qt_cases_by_codes().

Community still uses the older qt_case_keys_for_codes(), which scans backwards
from the last page with a budget of QT_CASE_SCAN_PAGES (default 15) and reports
anything older as not-found. The advance client replaced that with an arithmetic
page locate (qt_code_number / qt_cases_locate_page / qt_cases_by_codes).

WHY THIS MATTERS FOR COMMUNITY, CONCRETELY: at ~20 cases per page a 15-page
budget covers roughly the newest 300 cases. The NEXQ project held about 180 when
this was written, so every case is currently reachable and the round is correct
TODAY. Once NEXQ passes ~300 cases, markers written against older cases fall off
the end of the scan and are reported as not-found, which the round then treats
as Blocked — a wrong verdict for real, existing cases.

Re-import qatouch-client.sh from the advance repo (see ci/qa/IMPORTED-FROM)
before NEXQ reaches that size, and this suite starts guarding it.

Skipping — not a failure.
MSG
  exit 0
fi
pass=0; fail=0

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
QA_SRC="${QA_SRC:-$(cd "$HERE/.." && pwd)}"
. "$QA_SRC/qatouch-client.sh" 2>/dev/null
set +e +u

LAST_PAGE=446

# The fetch counter lives in a FILE, not a variable. Every call under test runs
# inside $( ), which is a subshell — a variable incremented there never reaches the
# parent, and the counts silently stayed 0, making every "costs at most N reads"
# assertion pass no matter what the code did.
COUNTER="$(mktemp)"
fetches() { wc -l < "$COUNTER" | tr -d ' '; }
reset_fetches() { : > "$COUNTER"; }
reset_fetches

qt_project() { printf 'ekXp'; }

# page P holds TR<9645 + (P-445)*20> .. +19
qt_public_get() {
  local url="$1" page
  page=$(sed -n 's/.*[?&]page=\([0-9]\+\).*/\1/p' <<<"$url")
  [[ -z "$page" ]] && page=1
  echo x >> "$COUNTER"
  local first=$(( 9645 + (page - 445) * 20 ))
  jq -cn --argjson first "$first" --argjson last "$LAST_PAGE" '
    {link: {last: ("https://x/getAllTestCases/ekXp?page=" + ($last|tostring))},
     data: [range(0;20) | {case_code: ("TR" + (($first + .)|tostring)),
                           case_key:  ("K" + (($first + .)|tostring)),
                           title:     ("case " + (($first + .)|tostring)),
                           steps:     "step body"}]}'
}

t() { if [[ "$3" == "$2" ]]; then printf 'PASS  %-50s -> %s\n' "$1" "$3"; pass=$((pass+1))
      else printf 'FAIL  %-50s -> got %q want %q\n' "$1" "$3" "$2"; fail=$((fail+1)); fi }
tle() { if (( $3 <= $2 )); then printf 'PASS  %-50s -> %s (<= %s)\n' "$1" "$3" "$2"; pass=$((pass+1))
        else printf 'FAIL  %-50s -> %s exceeds %s\n' "$1" "$3" "$2"; fail=$((fail+1)); fi }

CACHE="$(mktemp -d)"; trap 'rm -rf "$CACHE" "$COUNTER"' EXIT

# --- bisection lands on the right page, uncached ---
unset QT_PAGE_CACHE_DIR
reset_fetches
t   "locates the page holding TR9109"        "418" "$(qt_cases_locate_page TR9109)"
tle "…in far fewer reads than a linear scan"  "12" "$(fetches)"

t "locates the newest page"                  "445" "$(qt_cases_locate_page TR9650)"
t "locates an ancient case"                  "1"   "$(qt_cases_locate_page TR20)"

# --- the case the old scan could not reach ---
reset_fetches
body=$(qt_case_by_code TR9109)
t   "fetches TR9109's body"                  "TR9109" "$(jq -r '.case_code' <<<"$body")"
t   "…with its steps"                        "step body" "$(jq -r '.steps' <<<"$body")"
tle "…within a bounded number of reads"      "14" "$(fetches)"

# --- a whole marker in one sweep ---
reset_fetches
codes="TR9109,TR9110,TR9111,TR9112,TR9113,TR9114,TR9115,TR9116,TR9117"
got=$(qt_cases_by_codes "$codes")
t   "batch returns all 9 marker cases"       "9" "$(jq -r 'length' <<<"$got")"
t   "…the right ones"                        "TR9109 TR9117" \
    "$(jq -r '[.[].case_code] | (min + " " + max)' <<<"$got")"
tle "…in one bisection plus a short sweep"   "14" "$(fetches)"

# --- a marker spanning a page boundary ---
t "batch spans two pages"                    "3" \
  "$(jq -r 'length' <<<"$(qt_cases_by_codes 'TR9123,TR9124,TR9125')")"

# --- genuine misses stay misses, and stay bounded ---
reset_fetches
qt_case_by_code TR99999 >/dev/null 2>&1
t   "a code newer than the library is a miss" "1" "$?"
tle "…and does not walk the library"          "20" "$(fetches)"

# --- the page cache is what makes per-code calls affordable ---
export QT_PAGE_CACHE_DIR="$CACHE"
qt_case_by_code TR9109 >/dev/null 2>&1     # warm
reset_fetches
for c in TR9110 TR9111 TR9112 TR9113 TR9114 TR9115 TR9116 TR9117; do
  qt_case_by_code "$c" >/dev/null 2>&1
done
t "8 more codes off a warm cache cost nothing" "0" "$(fetches)"

# A truncated response must not be cached as if it were a page.
qt_public_get() { echo x >> "$COUNTER"; printf '{"error":"boom"}'; }
rm -rf "$CACHE"/*
qt_cases_page 400 >/dev/null 2>&1
t "a bad response is not cached"              "0" "$(ls -1 "$CACHE" 2>/dev/null | wc -l)"

echo
echo "$pass passed, $fail failed"
exit $(( fail > 0 ? 1 : 0 ))
