#!/usr/bin/env bash
# Fetch a GitHub issue as JSON for the authoring step.
#
#   ci/qa/fetch-issue.sh <issue-number> <out.json>
#
# Exists so the authoring agent needs no GitHub tool of its own. It reads a file
# and writes a file — no network, no shell, no token. Two things follow:
#
#   * the agent's allowlist can be Read/Grep/Glob/Write with no Bash at all, so a
#     prompt injection in an issue body has nothing to reach for;
#   * the pipeline stops depending on the `gh` CLI, which is not installed on the
#     Jenkins build node and would otherwise make every build fail at Preflight
#     for the benefit of one stage.
#
# Emits {"number":…,"title":…,"body":…,"labels":[…],"comments":[{"user":…,"body":…}]}

set -euo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
. "${here}/gh-client.sh"

issue="${1:?usage: fetch-issue.sh <issue-number> <out.json>}"
out="${2:?usage: fetch-issue.sh <issue-number> <out.json>}"

gh_require_env

# The issue and its comments reach jq through FILES, never through argv.
# `--argjson` puts every byte of the JSON on jq's command line, and an issue
# accumulates comments without limit: #8356 reached 15, several of them 20KB
# case tables, and the exec failed with "Argument list too long" (exit 126) —
# killing the authoring stage before the agent was ever called, on the very
# issues with the most context to work from. A file has no ARG_MAX.
work=$(mktemp -d)
trap 'rm -rf "$work"' EXIT

gh_issue "$issue"    > "${work}/issue.json"
gh_comments "$issue" > "${work}/comments.json"

jq -n --argjson n "$issue" \
      --slurpfile i "${work}/issue.json" \
      --slurpfile c "${work}/comments.json" '{
  number: $n,
  title: ($i[0].title // ""),
  body: ($i[0].body // ""),
  labels: [$i[0].labels[]?.name],
  comments: [$c[0][]? | {user: (.user.login // "unknown"), body: (.body // "")}]
}' > "$out"

printf 'fetch-issue: #%s -> %s (%s comment(s))\n' \
  "$issue" "$out" "$(jq '.comments | length' "$out")"
