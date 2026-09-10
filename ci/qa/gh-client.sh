#!/usr/bin/env bash
# GitHub API helpers for the first-round QA pipeline.
# Source this, don't execute it.
#
# `gh` CLI is deliberately not used — it isn't installed on the self-hosted
# runner and plain curl has no install step. Required env:
#   GITHUB_TOKEN   PAT or the workflow's GITHUB_TOKEN
#   GITHUB_REPO    "owner/name" (Actions sets GITHUB_REPOSITORY; we accept either)

set -euo pipefail

GH_API="https://api.github.com"

gh_repo() { printf '%s' "${GITHUB_REPO:-${GITHUB_REPOSITORY:?GITHUB_REPO or GITHUB_REPOSITORY required}}"; }

gh_require_env() {
  [[ -n "${GITHUB_TOKEN:-}" ]] || { printf 'gh: GITHUB_TOKEN is required\n' >&2; return 1; }
  gh_repo >/dev/null
  command -v jq >/dev/null || { printf 'gh: jq is required\n' >&2; return 1; }
}

# gh_api <method> <path> [json body]
gh_api() {
  local method="$1" path="$2" body="${3:-}"
  local args=(-sS -X "$method"
              -H "Authorization: Bearer ${GITHUB_TOKEN}"
              -H 'Accept: application/vnd.github+json'
              -H 'X-GitHub-Api-Version: 2022-11-28')
  [[ -n "$body" ]] && args+=(-H 'Content-Type: application/json' -d "$body")
  curl "${args[@]}" "${GH_API}${path}"
}

gh_graphql() {
  curl -sS -X POST "${GH_API}/graphql" \
    -H "Authorization: Bearer ${GITHUB_TOKEN}" \
    -H 'Content-Type: application/json' \
    -d "$1"
}

# ---------------------------------------------------------------------------
# issues / PRs
# ---------------------------------------------------------------------------

gh_issue()    { gh_api GET "/repos/$(gh_repo)/issues/$1"; }
gh_pr()       { gh_api GET "/repos/$(gh_repo)/pulls/$1"; }
gh_comments() { gh_api GET "/repos/$(gh_repo)/issues/$1/comments?per_page=100"; }

# The milestone an ISSUE is filed under, "" when none.
#
# The issue, not the pull request. The issue is where the release is decided and
# where the test cases live; a PR carries at most a copy of that decision and often
# nothing at all. QA Touch will not create a test run without a milestoneKey, and
# the linked issue's milestone is the only non-arbitrary answer to "which one" —
# see qt_default_milestone.
gh_issue_milestone() { gh_issue "$1" | jq -r '.milestone.title // ""'; }

# gh_comment <issue-or-pr number> <body-file>
# Body comes from a file: report bodies contain backticks, quotes and newlines
# that do not survive shell interpolation intact.
gh_comment() {
  local number="$1" file="$2" payload
  payload=$(jq -Rs '{body: .}' < "$file")
  gh_api POST "/repos/$(gh_repo)/issues/${number}/comments" "$payload" >/dev/null
}

# ---------------------------------------------------------------------------
# label names
# ---------------------------------------------------------------------------
# This repo's QA labels carry emoji shortcodes in the NAME — "Requires
# Functionality Review :pray:", "Functionality Correction :facepunch:",
# "QA: Round 1 Testing Approved :clap:", "Code Approved :heart_eyes_cat:" — while
# four others do not. Exact matching against the un-suffixed form silently matched
# nothing, which on the Stage 3 trigger meant skipping every PR and saying nothing.
#
# So: resolve a requested name against the repo's real labels. An exact hit wins;
# otherwise compare with trailing " :shortcode:" suffixes stripped and case
# folded, and answer with the repo's exact spelling. That survives someone
# changing the emoji, which is a thing people do.
#
# Writes go through the same resolution deliberately: POST .../labels with an
# unknown name CREATES it, so a near-miss would quietly add a second
# "Functionality Correction" next to the real one and split the taxonomy.

GH_LABEL_CACHE=""

gh_repo_labels() {
  if [[ -z "$GH_LABEL_CACHE" || ! -s "$GH_LABEL_CACHE" ]]; then
    GH_LABEL_CACHE=$(mktemp)
    local page=1 got
    while (( page <= 10 )); do
      got=$(gh_api GET "/repos/$(gh_repo)/labels?per_page=100&page=${page}" | jq -r '.[]?.name')
      [[ -z "$got" ]] && break
      printf '%s\n' "$got" >> "$GH_LABEL_CACHE"
      page=$(( page + 1 ))
    done
  fi
  cat "$GH_LABEL_CACHE"
}

# gh_label_normalise <name> -> lowercase, trailing :shortcode: groups removed
gh_label_normalise() {
  printf '%s' "$1" \
    | sed -E 's/([[:space:]]*:[A-Za-z0-9_+-]+:)+[[:space:]]*$//' \
    | sed -E 's/[[:space:]]+$//' \
    | tr '[:upper:]' '[:lower:]'
}

# gh_resolve_label <name> -> the repo's exact name, or exit 1
gh_resolve_label() {
  local want="$1" wanted_norm
  if gh_repo_labels | grep -qxF "$want"; then
    printf '%s' "$want"
    return 0
  fi
  wanted_norm=$(gh_label_normalise "$want")
  while IFS= read -r existing; do
    [[ -n "$existing" ]] || continue
    if [[ "$(gh_label_normalise "$existing")" == "$wanted_norm" ]]; then
      printf '%s' "$existing"
      return 0
    fi
  done < <(gh_repo_labels)
  printf 'gh: no label in %s matches "%s"\n' "$(gh_repo)" "$want" >&2
  return 1
}

gh_add_label() {
  local number="$1"; shift
  local resolved=() name exact
  for name in "$@"; do
    [[ -n "$name" ]] || continue
    if ! exact=$(gh_resolve_label "$name"); then
      printf 'gh: refusing to create label "%s" — fix the configured name instead\n' "$name" >&2
      return 1
    fi
    resolved+=("$exact")
  done
  (( ${#resolved[@]} )) || return 0
  local payload; payload=$(printf '%s\n' "${resolved[@]}" | jq -Rn '{labels: [inputs | select(length>0)]}')
  gh_api POST "/repos/$(gh_repo)/issues/${number}/labels" "$payload" >/dev/null
}

# Removing a label that isn't there returns 404 — harmless, so it's swallowed.
# Resolved first so "Functionality Correction" removes
# "Functionality Correction :facepunch:".
gh_remove_label() {
  local number="$1" label="$2" encoded resolved
  resolved=$(gh_resolve_label "$label" 2>/dev/null) || resolved="$label"
  encoded=$(jq -rn --arg l "$resolved" '$l|@uri')
  gh_api DELETE "/repos/$(gh_repo)/issues/${number}/labels/${encoded}" >/dev/null 2>&1 || true
}

# Tolerant on purpose: matches the repo's exact name, and also the same name with
# its emoji shortcode added or removed. A gate that goes silent because someone
# swapped :pray: for :point_right: is worse than one that is slightly generous.
gh_has_label() {
  local number="$1" label="$2" want name
  want=$(gh_label_normalise "$label")
  # Process substitution, not a pipe: a piped `while` runs in a subshell where
  # `return 0` cannot answer for this function.
  while IFS= read -r name; do
    [[ -n "$name" ]] || continue
    [[ "$(gh_label_normalise "$name")" == "$want" ]] && return 0
  done < <(gh_issue "$number" | jq -r '.labels[]?.name')
  return 1
}

# ---------------------------------------------------------------------------
# PR readiness gates
# ---------------------------------------------------------------------------

# gh_pr_is_approved <pr>
# An APPROVED review is not enough on its own: a later CHANGES_REQUESTED from
# the same reviewer supersedes it, so reviews are collapsed to each reviewer's
# most recent verdict before counting.
gh_pr_is_approved() {
  gh_api GET "/repos/$(gh_repo)/pulls/$1/reviews?per_page=100" | jq -e '
    [ .[] | select(.state == "APPROVED" or .state == "CHANGES_REQUESTED") ]
    | group_by(.user.login)
    | map(sort_by(.submitted_at) | last)
    | (any(.state == "APPROVED")) and (all(.state != "CHANGES_REQUESTED"))
  ' >/dev/null
}

# gh_pr_checks_green <pr>
# Neutral and skipped count as non-blocking, matching how GitHub itself renders
# a green PR. A commit that NOTHING has reported on is not green — treating silence
# as success would test unvalidated code.
#
# GitHub reports a commit's CI through TWO independent APIs, and a repo may use
# either or both:
#
#   /commits/<sha>/check-runs   the Checks API — GitHub Apps, Actions
#   /commits/<sha>/status       commit statuses — anything POSTing to /statuses
#
# This repo uses the second one: Jenkins posts contexts like "Jenkins / Larastan"
# and "continuous-integration/jenkins/pr-head" to /statuses/<sha>. It creates NO
# check runs at all, which is why the PR page reads "Checks 0" next to "All checks
# have passed - 6 successful checks".
#
# Reading only check-runs therefore found zero of them on every pull request, and
# zero was treated as not-green — so condition 4 could never pass and every round
# was skipped with "checks are not all green" while the PR was in fact entirely
# green. Measured on PR #16125.
#
# Green now means: every source that reported anything is green, and at least one
# source reported. Zero from both still fails, deliberately — nothing having
# verified the commit is not the same as the commit being good.
gh_pr_checks_green() {
  local sha runs_json status_json runs_total statuses_total
  sha=$(gh_pr "$1" | jq -r '.head.sha')
  [[ -n "$sha" && "$sha" != "null" ]] || return 1

  runs_json=$(gh_api GET "/repos/$(gh_repo)/commits/${sha}/check-runs?per_page=100") || return 1
  status_json=$(gh_api GET "/repos/$(gh_repo)/commits/${sha}/status") || return 1

  runs_total=$(jq -r '(.check_runs // []) | length' <<<"$runs_json")
  statuses_total=$(jq -r '(.statuses // []) | length' <<<"$status_json")

  if (( runs_total == 0 && statuses_total == 0 )); then
    printf 'gate: no check runs and no commit statuses on %s — nothing has verified it\n' \
      "${sha:0:8}" >&2
    return 1
  fi

  if (( runs_total > 0 )); then
    jq -e '
      all(.check_runs[]; .status == "completed")
      and all(.check_runs[]; .conclusion == "success" or .conclusion == "neutral" or .conclusion == "skipped")
    ' <<<"$runs_json" >/dev/null || {
      printf 'gate: check runs not all green: %s\n' \
        "$(jq -r '[.check_runs[] | select((.status != "completed") or ((.conclusion // "") | IN("success","neutral","skipped") | not)) | .name] | join(", ")' <<<"$runs_json")" >&2
      return 1
    }
  fi

  # The combined state is success only when every context is; pending or failure
  # on any one of them makes it pending/failure.
  if (( statuses_total > 0 )); then
    local state
    state=$(jq -r '.state' <<<"$status_json")
    if [[ "$state" != "success" ]]; then
      printf 'gate: commit status state is %s: %s\n' "$state" \
        "$(jq -r '[.statuses[] | select(.state != "success") | .context + "=" + .state] | join(", ")' <<<"$status_json")" >&2
      return 1
    fi
  fi

  return 0
}

# gh_pr_linked_issues <pr> -> one issue number per line
# Uses GraphQL closingIssuesReferences. REST has no equivalent: the issue link
# created by "Fixes #123" is not exposed on the pulls endpoint at all.
gh_pr_linked_issues() {
  local pr="$1" slug owner repo_name query
  slug="$(gh_repo)"
  owner="${slug%%/*}"
  repo_name="${slug#*/}"

  query=$(jq -n --arg o "$owner" --arg r "$repo_name" --argjson pr "$pr" '{
    query: "query($o:String!,$r:String!,$pr:Int!){repository(owner:$o,name:$r){pullRequest(number:$pr){closingIssuesReferences(first:20){nodes{number}}}}}",
    variables: {o: $o, r: $r, pr: $pr}
  }')

  gh_graphql "$query" \
    | jq -r '.data.repository.pullRequest.closingIssuesReferences.nodes[]?.number'
}

# ---------------------------------------------------------------------------
# issue body
# ---------------------------------------------------------------------------
# The case codes go on the issue twice, on purpose: in a comment (full steps, for
# a person reviewing them) and as a compact block in the ISSUE BODY. The body is
# what someone sees first, what search indexes, and what stays visible when the
# comment thread grows — "which cases cover this issue" should not require
# scrolling. The block is delimited so a second run replaces it rather than
# stacking, and everything the author wrote outside the delimiters is preserved.

QA_IDS_OPEN='<!-- qa-touch-ids -->'
QA_IDS_CLOSE='<!-- /qa-touch-ids -->'

# gh_issue_upsert_ids_block <issue> <block-file>
# awk rather than a richer language on purpose: the runner is guaranteed curl,
# jq and awk, and adding a python3 requirement to a CI agent for one substitution
# is how a pipeline starts failing on a host nobody remembered to provision.
gh_issue_upsert_ids_block() {
  local issue="$1" block_file="$2" body_file new_file payload

  body_file=$(mktemp); new_file=$(mktemp)
  gh_issue "$issue" | jq -r '.body // ""' > "$body_file"

  if grep -qF "$QA_IDS_OPEN" "$body_file"; then
    awk -v mopen="$QA_IDS_OPEN" -v mclose="$QA_IDS_CLOSE" -v block="$block_file" '
      index($0, mopen) == 1 && !done { print mopen; while ((getline line < block) > 0) print line; print mclose; skip = 1; done = 1; next }
      skip { if (index($0, mclose) == 1) { skip = 0 } ; next }
      { print }
    ' "$body_file" > "$new_file"
  else
    { cat "$body_file"; printf '\n%s\n' "$QA_IDS_OPEN"; cat "$block_file"; printf '%s\n' "$QA_IDS_CLOSE"; } > "$new_file"
  fi

  payload=$(jq -Rs '{body: .}' < "$new_file")
  rm -f "$body_file" "$new_file"
  gh_api PATCH "/repos/$(gh_repo)/issues/${issue}" "$payload" >/dev/null
}

# ---------------------------------------------------------------------------
# PR shape
# ---------------------------------------------------------------------------

# gh_pr_files <pr> -> one changed path per line, with the status prefixed:
#   added|modified|removed|renamed<TAB>path
#
# Pages to 300. A PR touching more than that is a scope problem, not a paging one,
# and the callers here degrade sanely: a Dusk change beyond file 300 just does not
# trigger a run.
gh_pr_files() {
  local page
  for page in 1 2 3; do
    gh_api GET "/repos/$(gh_repo)/pulls/$1/files?per_page=100&page=${page}" \
      | jq -r '.[]? | "\(.status)\t\(.filename)"'
  done
}

# gh_pr_labels <pr> -> one label per line
gh_pr_labels() { gh_issue "$1" | jq -r '.labels[]?.name'; }
