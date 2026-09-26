#!/usr/bin/env bash
# Every offline check for the QA pipeline, shell and Groovy alike. No network, no
# credentials, no Jenkins — so the parts that decide whether a round runs, and
# where its results get written, can be changed without a real build to find out.
#
#   ci/qa/tests/run.sh
#
# Exits non-zero if any suite reports a failure. A suite that SKIPS exits 0 on
# purpose, for one of two reasons:
#
#   1. It checks the PIPELINE SCRIPT, which is pasted into the Jenkins job and
#      deliberately not committed. Set QA_PIPELINE_SCRIPT_PATH to your local copy
#      to run those three:
#        QA_PIPELINE_SCRIPT_PATH=~/Documents/JENKINS-PIPELINE-SCRIPT.groovy \
#          bash ci/qa/tests/run.sh
#
#   2. It came from the advance repo and tests a capability Community has not
#      imported (see ci/qa/IMPORTED-FROM). Each explains which function is
#      missing and what the gap costs, rather than failing with "command not
#      found" or being deleted and taking the knowledge of the gap with it.
set -uo pipefail
here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
rc=0
for suite in pipeline-contracts build-timeout jenkinsfile-parse gate-scenarios \
             qatouch-run-resolution marker-resume case-lookup fixture-cast; do
  printf '\n=== %s\n' "$suite"
  bash "$here/${suite}.sh" || rc=1
done
printf '\n=== overall: %s\n' "$([[ $rc == 0 ]] && echo PASS || echo FAIL)"
exit $rc
