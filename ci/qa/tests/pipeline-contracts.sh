#!/usr/bin/env bash
# Every promise the QA pipeline script makes about the shell it drives, checked
# offline: no Jenkins, no network, no credentials.
#
#   ci/qa/tests/pipeline-contracts.sh [pipeline-script-path]
#
# COMMUNITY-SPECIFIC — there is no counterpart in the advance or invoicing repo.
# It exists because the pipeline calls ci/qa scripts by path and configures
# itself by string, and every one of those is only checked at RUNTIME, mid-round,
# after a label has already moved. Some fail silently, which is worse. These are
# the failures that actually happened while the pipeline was being written:
#
#   1. calling a script that does not exist in Community (the invoicing pipeline
#      calls code-map.sh, probes/api.sh, probes/dusk.sh, pr-marker.sh,
#      dusk-changed.sh and instance-health.sh; Community has none of them)
#   2. a label renamed in the environment block but not in the trigger's regex,
#      which produces a job that never fires and no error anywhere
#   3. the QA Touch project guard dropped, which silently writes Community's
#      cases into the ADVANCE project (MeLq), with no delete-case endpoint to
#      undo it
#   4. the executor keeping its GitHub token while driving untrusted PR code
#
# The pipeline script is pasted into the Jenkins job and NOT committed, so pass a
# path or set QA_PIPELINE_SCRIPT_PATH. With neither, this skips (exit 0).
set -uo pipefail
pass=0; fail=0

ok()  { printf 'PASS  %s\n' "$1"; pass=$((pass+1)); }
bad() { printf 'FAIL  %s\n' "$1"; fail=$((fail+1)); }
t()   { if [[ "$2" == "$3" ]]; then ok "$1"; else printf 'FAIL  %s\n        got  %q\n        want %q\n' "$1" "$2" "$3"; fail=$((fail+1)); fi; }

here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
QA="$(cd "${here}/.." && pwd)"
JF="${1:-${QA_PIPELINE_SCRIPT_PATH:-}}"

if [[ -z "$JF" ]]; then
  cat >&2 <<'MSG'
pipeline-contracts: no pipeline script path given.

  bash ci/qa/tests/pipeline-contracts.sh /path/to/JENKINS-PIPELINE-SCRIPT.groovy

Skipping — not a failure.
MSG
  exit 0
fi
[[ -f "$JF" ]] || { printf 'pipeline-contracts: %s does not exist\n' "$JF" >&2; exit 1; }

# Groovy // comments and shell # comments both name scripts that are deliberately
# absent, so a naive grep reports those as missing dependencies. Strip both.
code="$(mktemp)"; trap 'rm -f "$code"' EXIT
sed -e 's#^[[:space:]]*//.*##' -e 's#^[[:space:]]*\#.*##' "$JF" > "$code"

# --- 1. every script the pipeline executes exists --------------------------
# The [\\"]* around the variable is not paranoia. The pipeline writes this path
# several ways depending on which kind of Groovy string encloses it:
#   bash "$QA_TOOLS"/x.sh          a triple-quoted block, dollar escaped as \$
#   bash \"${QA_TOOLS:-ci/qa}\"/x.sh   a double-quoted string, quotes escaped
#   bash ci/qa/x.sh                a plain literal
# An extractor that handles only one of them silently checks a fraction of the
# calls and reports every one of them as passing.
echo "--- scripts the pipeline executes ---"
refs="$(grep -oE '(bash|php) +([\\"]*\$\{?QA_TOOLS(:-ci/qa)?\}?[\\"/]*|ci/qa)/?[A-Za-z0-9/._-]+\.(sh|php)' "$code" \
        | sed -E 's#^(bash|php) +##; s#^[\\"]*\$\{?QA_TOOLS(:-ci/qa)?\}?[\\"]*/?##; s#^ci/qa/##' \
        | sort -u)"
n_refs=$(grep -c . <<< "$refs")
# A floor, not decoration. Every assertion below is an "exists" check, so an
# extractor that matches nothing reports a clean run.
if (( n_refs >= 12 )); then ok "extractor found ${n_refs} executed script paths"
else bad "extractor found only ${n_refs} executed script paths — under-matching, so the checks below are vacuous"; fi
while IFS= read -r r; do
  [[ -z "$r" ]] && continue
  if [[ -f "${QA}/${r}" ]]; then ok "exists: ${r}"; else bad "MISSING: ${r} is executed but not in ci/qa/"; fi
done <<< "$refs"

echo "--- file arguments the pipeline reads ---"
for f in stage1-author-prompt.md stage3-prompt.md mcp-ci.json; do
  grep -q "$f" "$code" && { [[ -f "${QA}/${f}" ]] && ok "exists: ${f}" || bad "MISSING: ${f}"; }
done

# --- 2. scripts Community deliberately does not have -----------------------
echo "--- deliberately absent scripts stay unexecuted ---"
for f in code-map.sh pr-marker.sh dusk-changed.sh probes/api.sh probes/dusk.sh probes/security.sh instance-health.sh enable-api.php; do
  if grep -qE "(bash|php) +[^#]*${f//./\\.}" "$code"; then
    bad "EXECUTED: ${f} does not exist in Community (see ci/qa/IMPORTED-FROM)"
  else
    ok "not executed: ${f}"
  fi
done

# freestyle/ was removed when the pipeline absorbed its logic. A call to one
# would now be a path that does not exist.
if grep -q 'freestyle/' "$code"; then
  bad "the pipeline still calls ci/qa/freestyle/* — that directory was removed"
else
  ok "no ci/qa/freestyle/* calls remain"
fi

# --- 3. the trigger regex carries the real label text ----------------------
# This is the silent one: the job simply never fires.
echo "--- GenericTrigger regex matches the configured labels ---"
lbl() { sed -n "s/^[[:space:]]*$1[[:space:]]*=[[:space:]]*'\(.*\)'.*/\1/p" "$JF" | head -1; }
rx="$(grep -m1 'regexpFilterExpression' "$JF")"
for v in QA_TRIGGER_LABEL QA_PR_TRIGGER_LABEL; do
  val="$(lbl "$v")"
  if [[ -z "$val" ]]; then bad "${v} is not set in the environment block"
  elif [[ "$rx" == *"$val"* ]]; then ok "trigger regex contains ${v} (${val})"
  else bad "trigger regex does NOT contain ${v} (${val}) — the job will never fire on it"; fi
done

# Every label the pipeline applies must actually be declared, or label.sh is
# asked to add a label named "".
echo "--- every label the pipeline uses is declared ---"
for v in $(grep -oE 'env\.QA_[A-Z0-9_]*LABEL' "$code" | sed 's/^env\.//' | sort -u); do
  [[ -n "$(lbl "$v")" ]] && ok "declared: ${v}=$(lbl "$v")" || bad "${v} is used but not declared in the environment block"
done

# --- 4. the QA Touch project guard ----------------------------------------
echo "--- QA Touch project guard ---"
t "environment block pins the Community project" "$(lbl QATOUCH_PROJECT)" "NEXQ"
grep -q 'QATOUCH_PROJECT' "$code" && ok "preflight asserts QATOUCH_PROJECT" \
  || bad "nothing asserts QATOUCH_PROJECT — an unset value targets the ADVANCE project (MeLq)"

# --- 5. the executor credential split -------------------------------------
# The executor drives untrusted PR code. It holds the QA Touch token but must not
# hold a GitHub token: that split is why a prompt injection in a PR cannot reach
# the repository. Losing the unset is invisible until it is exploited.
echo "--- executor credential split ---"
grep -q 'unset GITHUB_TOKEN' "$code" && ok "executor unsets GITHUB_TOKEN before running" \
  || bad "executor does NOT unset GITHUB_TOKEN — untrusted PR code would run with repo write access"

# --- 6. Community provisioning facts --------------------------------------
# Each of these caused a real, diagnosed failure; a rewrite that drops one
# reintroduces it.
echo "--- Community provisioning facts ---"
grep -q 'rm -f .env' "$code" && ok ".env is DELETED before testing-setup (createEnv returns early if it exists)" \
  || bad ".env is not deleted — testing-setup would silently keep the previous round's credentials"
grep -q "DB_DATABASE=" "$code" && ok "DB_DATABASE is written to .env (Config::set does not survive the artisan process)" \
  || bad "DB_DATABASE is not written — later processes fall back to env('DB_DATABASE','forge')"
grep -q 'settings_system' "$code" && ok "settings_system.url is corrected off localhost:8000" \
  || bad "settings_system.url is not corrected — generated links point at a dead host"
grep -q 'tls-proxy.php' "$code" && ok "TLS proxy is started (AppServiceProvider forces https)" \
  || bad "no TLS proxy — the app would redirect every request to https and answer nothing"
grep -q 'instance-health-community.sh' "$code" && ok "uses the Community health check" \
  || bad "does not run instance-health-community.sh"

echo
echo "$pass passed, $fail failed"
exit $(( fail > 0 ? 1 : 0 ))
