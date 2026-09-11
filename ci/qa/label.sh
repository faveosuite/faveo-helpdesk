#!/usr/bin/env bash
# Label operations, resolved against the repo's real label names.
#
#   ci/qa/label.sh add    <issue-or-pr> <name>
#   ci/qa/label.sh remove <issue-or-pr> <name>
#   ci/qa/label.sh has    <issue-or-pr> <name>     (exit 0 = present)
#
# One implementation for both the shell stages and the Jenkinsfile. The pipeline
# used to build these calls with curl in two places; the duplicate mattered once
# this repo's labels turned out to carry emoji shortcodes in the name
# ("Requires Functionality Review :pray:"), because a raw POST with the
# un-suffixed name CREATES a second, near-identical label instead of failing.
#
# Exit codes: 0 ok / 1 no such label in the repo (nothing written) / 2 usage.

set -uo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
. "${here}/gh-client.sh"

action="${1:-}"; number="${2:-}"; name="${3:-}"
[[ -n "$action" && -n "$number" && -n "$name" ]] || {
  printf 'usage: label.sh add|remove|has <number> <name>\n' >&2; exit 2; }

gh_require_env || exit 2

case "$action" in
  add)    gh_add_label "$number" "$name" ;;
  remove) gh_remove_label "$number" "$name" ;;
  has)    gh_has_label "$number" "$name" ;;
  *)      printf 'label.sh: unknown action "%s"\n' "$action" >&2; exit 2 ;;
esac
