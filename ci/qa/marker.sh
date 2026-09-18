#!/usr/bin/env bash
# The qa-touch-cases marker: the only state carried from Stage 1 to Stage 3.
# Source this, don't execute it. Contract documented in ci/qa/marker.md.
#
# Requires gh-client.sh to be sourced first.

set -euo pipefail

MARKER_VERSION=1
MARKER_OPEN='<!-- qa-touch-cases '
MARKER_CLOSE=' -->'

# Exit codes, kept distinct because the callers react differently:
#   0  marker found and valid
#   3  no marker  -> quiet skip (the common case for unrelated PRs)
#   4  malformed or unknown version -> loud failure, never a guess
MARKER_NONE=3
MARKER_BAD=4

# marker_read <issue> -> marker JSON on stdout
marker_read() {
  local issue="$1" raw parsed version

  raw=$(gh_comments "$issue" \
        | jq -r '.[].body' \
        | grep -o "${MARKER_OPEN}.*${MARKER_CLOSE}" \
        | tail -1 || true)

  if [[ -z "$raw" ]]; then
    printf 'marker: no qa-touch-cases marker on issue #%s\n' "$issue" >&2
    return $MARKER_NONE
  fi

  raw="${raw#"$MARKER_OPEN"}"
  raw="${raw%"$MARKER_CLOSE"}"

  if ! parsed=$(printf '%s' "$raw" | jq -c . 2>/dev/null); then
    printf 'marker: malformed JSON in the marker on issue #%s — refusing to guess\n' "$issue" >&2
    return $MARKER_BAD
  fi

  version=$(printf '%s' "$parsed" | jq -r '.v // "missing"')
  if [[ "$version" != "$MARKER_VERSION" ]]; then
    printf 'marker: issue #%s has marker version %s, this code speaks %s\n' \
      "$issue" "$version" "$MARKER_VERSION" >&2
    return $MARKER_BAD
  fi

  # A marker written for a different issue means someone copy-pasted a comment;
  # executing the wrong issue's cases would be worse than failing here.
  local claimed
  claimed=$(printf '%s' "$parsed" | jq -r '.issue // "missing"')
  if [[ "$claimed" != "$issue" ]]; then
    printf 'marker: marker on issue #%s claims issue #%s — refusing to use it\n' \
      "$issue" "$claimed" >&2
    return $MARKER_BAD
  fi

  printf '%s' "$parsed"
}

# marker_exists <issue> — the Stage 1 idempotency guard
marker_exists() {
  gh_comments "$1" | jq -r '.[].body' | grep -q "${MARKER_OPEN}"
}

# marker_build <issue> <project> <module> <module_key> <sources-csv> <codes-json> [previous-marker-json]
# codes-json: [{"code":"TR8927","key":"lRvrkQ"},…]
#
# With a previous marker, this is an AMEND: the new cases are appended to the old
# ones and the original generated_at is kept, with amended_at recording the
# addition. Appending only ever adds — no existing case is edited, renumbered or
# removed — because those cases may already be approved and already sitting in a
# run with results against them. See marker.md, rule 3.
marker_build() {
  local issue="$1" project="$2" module="$3" module_key="$4" sources="$5" cases="$6" previous="${7:-}"
  local json now
  now=$(date -u +%Y-%m-%dT%H:%M:%SZ)

  if [[ -n "$previous" ]]; then
    # Dedupe on code: re-publishing the same case twice would have Stage 3
    # execute it twice and write the second result over the first.
    cases=$(jq -cn --argjson old "$(jq -c '.cases // []' <<<"$previous")" --argjson new "$cases" \
      '($old + $new) | unique_by(.code)')
  fi

  json=$(jq -cn \
    --argjson v "$MARKER_VERSION" \
    --arg project "$project" \
    --argjson issue "$issue" \
    --arg module "$module" \
    --arg module_key "$module_key" \
    --arg generated_at "$(if [[ -n "$previous" ]]; then jq -r '.generated_at // empty' <<<"$previous"; fi)" \
    --arg now "$now" \
    --arg sources "$sources" \
    --argjson cases "$cases" \
    --argjson amended "$(if [[ -n "$previous" ]]; then printf 'true'; else printf 'false'; fi)" \
    '{v:$v, project:$project, issue:$issue, module:$module, module_key:$module_key,
      generated_at:(if $generated_at == "" then $now else $generated_at end),
      source:($sources|split(",")), cases:$cases}
     + (if $amended then {amended_at: $now} else {} end)')

  printf '%s%s%s' "$MARKER_OPEN" "$json" "$MARKER_CLOSE"
}

# marker_codes <marker-json> -> one TR#### per line
marker_codes() { printf '%s' "$1" | jq -r '.cases[]?.code'; }

# marker_case_keys <marker-json> -> one case_key per line
marker_case_keys() { printf '%s' "$1" | jq -r '.cases[]?.key'; }

# ---------------------------------------------------------------------------
# PR-side marker: has the first round already run for this commit?
# ---------------------------------------------------------------------------
# Stage 3 re-evaluates on every label, review and check_suite event, so it needs
# to know whether it has already reported. Keying that on the head SHA rather
# than on a label is deliberate and better in both directions:
#
#   - A developer pushing a fix SHOULD get a fresh run. A label-based guard would
#     suppress exactly the re-run you want.
#   - The team owns "Requires Functionality Review" and "QA: Round 1 Testing
#     Approved". CI adding or removing those would overwrite human intent — the
#     approval label in particular means "tested AND the failures were fixed",
#     which is a person's judgement, not something a green run establishes.
#
# So Stage 3 changes no labels at all. It reports, and a human signs off.

PR_MARKER_OPEN='<!-- qa-first-round '

# pr_marker_exists <pr> <head-sha>
pr_marker_exists() {
  gh_comments "$1" | jq -r '.[].body' \
    | grep -o "${PR_MARKER_OPEN}.*-->" \
    | sed "s|^${PR_MARKER_OPEN}||; s| -->$||" \
    | jq -r --arg sha "$2" 'select(.sha == $sha) | .sha' 2>/dev/null \
    | grep -q .
}

# pr_marker_build <head-sha> <run-key> <passed> <failed> <blocked>
pr_marker_build() {
  local json
  json=$(jq -cn --arg sha "$1" --arg run "$2" \
    --argjson passed "$3" --argjson failed "$4" --argjson blocked "$5" \
    '{v:1, sha:$sha, run:$run, passed:$passed, failed:$failed, blocked:$blocked}')
  printf '%s%s -->' "$PR_MARKER_OPEN" "$json"
}
