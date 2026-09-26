#!/usr/bin/env bash
# Compile the pipeline script with Groovy. Catches an unbalanced brace, a broken
# GString, a malformed closure — without a Jenkins.
#
#   ci/qa/tests/jenkinsfile-parse.sh [pipeline-script-path]
#
# @NonCPS is stripped first: the annotation lives in the Jenkins plugin, not on a
# plain Groovy classpath, and an unresolved annotation is a compile error that says
# nothing about the code under it.
#
# The pipeline script is pasted into the Jenkins job's "Pipeline script" field
# and is deliberately NOT committed, so there is nothing in-repo to check by
# default. Pass the path to your local copy, or set QA_PIPELINE_SCRIPT_PATH.
# With neither, this explains that and SKIPS (exit 0) rather than failing.
#
# Skips (exit 0), rather than failing, when groovy is not installed — a node
# without it is not a broken pipeline.
set -uo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
f="${1:-${QA_PIPELINE_SCRIPT_PATH:-}}"

if [[ -z "$f" ]]; then
  cat >&2 <<'MSG'
jenkinsfile-parse: no pipeline script path given.

The pipeline script lives outside this repo — it is pasted into the Jenkins job's
"Pipeline script" field. Point this at a local copy to run it:

  bash ci/qa/tests/jenkinsfile-parse.sh /path/to/JENKINS-PIPELINE-SCRIPT.groovy
  QA_PIPELINE_SCRIPT_PATH=/path/to/it bash ci/qa/tests/jenkinsfile-parse.sh

Skipping — not a failure.
MSG
  exit 0
fi

[[ -f "$f" ]] || { printf 'parse: %s does not exist\n' "$f" >&2; exit 1; }

if ! command -v groovy >/dev/null 2>&1; then
  echo "parse: groovy not installed — skipped"
  exit 0
fi

# groovy needs a JAVA_HOME and the Debian package points at /usr/lib/jvm/default-java,
# which the openjdk packages do not always create. Fall back to the JDK that owns
# the java on PATH rather than reporting a parse failure that is really a packaging
# detail.
if [[ -z "${JAVA_HOME:-}" || ! -x "${JAVA_HOME}/bin/java" ]]; then
  if command -v java >/dev/null 2>&1; then
    JAVA_HOME="$(dirname "$(dirname "$(readlink -f "$(command -v java)")")")"
    export JAVA_HOME
  else
    echo "parse: no java — skipped"
    exit 0
  fi
fi

tmp="$(mktemp -d)"
trap 'rm -rf "$tmp"' EXIT
sed 's/^@NonCPS$//' "$f" > "$tmp/candidate.groovy"

cat > "$tmp/parse.groovy" <<'GROOVY'
try {
  new groovy.lang.GroovyShell().parse(new File(args[0]))
  println 'parse: OK'
} catch (Throwable t) {
  println 'parse: FAILED'
  println t.message.take(3000)
  System.exit(1)
}
GROOVY

groovy "$tmp/parse.groovy" "$tmp/candidate.groovy"
