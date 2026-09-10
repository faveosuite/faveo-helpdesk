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

issue_json=$(gh_issue "$issue")
comments_json=$(gh_comments "$issue")

jq -n --argjson i "$issue_json" --argjson c "$comments_json" --argjson n "$issue" '{
  number: $n,
  title: ($i.title // ""),
  body: ($i.body // ""),
  labels: [$i.labels[]?.name],
  comments: [$c[]? | {user: (.user.login // "unknown"), body: (.body // "")}]
}' > "$out"

printf 'fetch-issue: #%s -> %s (%s comment(s))\n' \
  "$issue" "$out" "$(jq '.comments | length' "$out")"
