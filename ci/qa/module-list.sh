#!/usr/bin/env bash
# Write the project's existing QA Touch module names to a file, one per line.
#
#   ci/qa/module-list.sh <outfile>
#
# For the authoring step. Until this existed the agent had no idea what modules the
# library already had — it holds no QA Touch credential and cannot look — so it
# invented a module name from the issue text. Two runs on the same issue guessed
# "Form Builder" and "Settings", and the second created a brand new module beside
# 328 existing ones. Guessing was the only option it had.
#
# The names are not a secret and this is not a credential leak: the pipeline holds
# the token, calls the API, and hands the agent a flat text file — the same shape as
# the code map. The agent still cannot write to QA Touch.
#
# Exits 0 with an empty file if the list cannot be fetched. Authoring must not fail
# because this did; a guessed module is worse than a chosen one, not fatal.
set -uo pipefail

out="${1:?usage: module-list.sh <outfile>}"
here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

: > "$out"
# shellcheck source=/dev/null
. "${here}/qatouch-client.sh" || exit 0
set +e

qt_require_env >/dev/null 2>&1 || {
  printf 'module-list: QA Touch env incomplete — the authoring step will have to guess a module\n' >&2
  exit 0
}

qt_modules_all 2>/dev/null \
  | jq -r '(.section_name // .module_name // empty)' \
  | sed 's/[[:space:]]*$//' \
  | awk 'NF && !seen[tolower($0)]++' \
  | sort > "$out"

count=$(wc -l < "$out" | tr -d ' ')
printf 'module-list: %s existing module name(s) written to %s\n' "$count" "$out" >&2
exit 0
