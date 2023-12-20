#!/usr/bin/env bash
# The build's total wall clock is written in two places that must agree, and
# nothing at runtime notices when they do not.
#
#   ci/qa/tests/build-timeout.sh [pipeline-script-path]
#
# options{ timeout } is what Jenkins enforces. QA_BUILD_TIMEOUT_MINUTES is what
# the executor's clamp derives its ceiling from. A declarative option cannot read
# a variable, so the number is a literal in one place and a variable in the other
# — both inside the pipeline script.
#
# Drift is silent and one-directional in the worst way: if the env var is LARGER
# than the real timeout, the executor is told it has time it does not have, runs
# past the abort, and the build is killed before publishing or teardown — no
# report on the PR, a database left behind, and a port still held.
#
# The pipeline script is pasted into the Jenkins job and NOT committed, so there
# is nothing in-repo to check by default: pass a path or set
# QA_PIPELINE_SCRIPT_PATH. With neither, this skips (exit 0).
set -uo pipefail
pass=0; fail=0

t() { if [[ "$3" == "$2" ]]; then printf 'PASS  %-46s -> %s\n' "$1" "$3"; pass=$((pass+1))
      else printf 'FAIL  %-46s -> got %q want %q\n' "$1" "$3" "$2"; fail=$((fail+1)); fi }

JF="${1:-${QA_PIPELINE_SCRIPT_PATH:-}}"

if [[ -z "$JF" ]]; then
  cat >&2 <<'MSG'
build-timeout: no pipeline script path given.

  bash ci/qa/tests/build-timeout.sh /path/to/JENKINS-PIPELINE-SCRIPT.groovy

Skipping — not a failure.
MSG
  exit 0
fi

[[ -f "$JF" ]] || { printf 'build-timeout: %s does not exist\n' "$JF" >&2; exit 1; }

option=$(sed -n "s/.*timeout(time: *\([0-9]\+\), *unit: *'MINUTES').*/\1/p" "$JF" | head -1)
envvar=$(sed -n "s/.*QA_BUILD_TIMEOUT_MINUTES *= *'\([0-9]\+\)'.*/\1/p" "$JF" | head -1)

t "options{ timeout } has a value"       "yes" "$([[ -n $option ]] && echo yes || echo no)"
t "QA_BUILD_TIMEOUT_MINUTES has a value" "yes" "$([[ -n $envvar ]] && echo yes || echo no)"
# Guarded against both being empty: "" == "" would otherwise pass while the file
# had no timeout at all.
t "the two agree"                        "${option:-A}" "${envvar:-B}"

echo
echo "$pass passed, $fail failed"
exit $(( fail > 0 ? 1 : 0 ))
