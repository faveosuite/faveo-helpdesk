#!/usr/bin/env bash
# Find issues and PRs that the QA pipeline should look at.
#
#   ci/qa/discover.sh [author|execute|all]
#
# Emits one JSON object per line: {"stage":"author","number":1234}
#
# The webhook is the primary trigger — this exists for the cron safety net and
# for manual runs. Webhook deliveries do get dropped (GitHub retries, but not
# forever, and a Jenkins restart mid-delivery loses one), and a missed delivery
# on the issue side means test cases silently never get written. A cheap periodic
# sweep costs two API calls and removes that failure mode.
#
# Requires gh-client.sh to be sourced-able alongside this file.

set -euo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
. "${here}/gh-client.sh"
. "${here}/marker.sh"

AUTHOR_LABEL="${QA_TRIGGER_LABEL:-QA: Test case needed}"
# Exact, including the emoji shortcode: this one goes into a GitHub SEARCH query
# (label:"…"), which is matched server-side and is NOT covered by the tolerant
# resolution in gh-client.sh. Verified against the repo's label list.
EXECUTE_LABEL="${QA_PR_TRIGGER_LABEL:-Requires Functionality Review :pray:}"

# The opt-out. An issue or PR carrying this is being handled by a person, and the
# pipeline stays off it entirely — no cases authored, no round run, no comment. It is
# excluded in the search query itself, so a manual item never becomes work and never
# costs a build. The stages check it again for the webhook path, where a `labeled`
# event names a number directly and discovery never runs.
MANUAL_LABEL="${QA_MANUAL_LABEL:-Manual}"

want="${1:-all}"

gh_require_env

# gh_search_by_label <label> <type: issue|pr>
# Uses the search API so the label filter happens server-side. `is:open` matters:
# a closed issue carrying the label is not work, and processing one would post a
# comment on something the team considers finished.
gh_search_by_label() {
  local label="$1" kind="$2" q
  q=$(jq -rn --arg r "$(gh_repo)" --arg l "$label" --arg k "$kind" --arg m "$MANUAL_LABEL" \
        '"repo:\($r) is:open is:\($k) label:\"\($l)\" -label:\"\($m)\""|@uri')
  gh_api GET "/search/issues?q=${q}&per_page=100" \
    | jq -r '.items[]?.number'
}

if [[ "$want" == "author" || "$want" == "all" ]]; then
  while read -r n; do
    [[ -n "$n" ]] || continue
    # Skip anything already carrying a marker. Stage 1 would skip it too, but
    # doing it here keeps the cron sweep from queueing a build per already-done
    # issue every few hours.
    if marker_exists "$n" 2>/dev/null; then continue; fi
    jq -cn --arg s author --argjson n "$n" '{stage:$s, number:$n}'
  done < <(gh_search_by_label "$AUTHOR_LABEL" issue)
fi

if [[ "$want" == "execute" || "$want" == "all" ]]; then
  while read -r n; do
    [[ -n "$n" ]] || continue
    # No marker pre-check here: stage3-gate.sh decides eligibility, and it keys
    # on the head SHA so a pushed fix legitimately re-runs.
    jq -cn --arg s execute --argjson n "$n" '{stage:$s, number:$n}'
  done < <(gh_search_by_label "$EXECUTE_LABEL" pr)
fi
