#!/usr/bin/env bash
# qt_resolve_run's name resolution, offline: no QA Touch token, no network.
# qt_run_key_by_name is stubbed so only two run names "exist".
#
#   ci/qa/tests/qatouch-run-resolution.sh
set -uo pipefail
pass=0; fail=0
QA_SRC_CLIENT="${QA_SRC:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}/qatouch-client.sh"
. ${QA_SRC:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}/qatouch-client.sh 2>/dev/null || { echo "source failed"; exit 1; }

set +e +u

# stub: only these two run names exist in the fake project
qt_run_key_by_name() {
  case "$1" in
    "First round — PR #16132")            printf 'PRLEVEL'; return 0 ;;
    "First round — PR #99 (issue #7)")    printf 'PERISSUE'; return 0 ;;
    *) return 1 ;;
  esac
}

t() { # t <label> <expected> <name> [fallbacks]
  local label="$1" exp="$2" name="$3" fb="${4:-}"
  local got rc
  got=$(QA_TEST_RUN_KEY= QA_RUN_NAME_FALLBACKS="$fb" QA_ALLOW_RUN_CREATE=0 qt_resolve_run "$name" 2>/tmp/err.$$); rc=$?
  if [[ "$got" == "$exp" ]]; then printf 'PASS  %-46s -> %s\n' "$label" "${got:-<none> rc=$rc}"; pass=$((pass+1))
  else printf 'FAIL  %-46s -> got %q want %q\n' "$label" "$got" "$exp"; sed 's/^/        stderr: /' /tmp/err.$$; fail=$((fail+1)); fi
  rm -f /tmp/err.$$
}

# CAPABILITY SPLIT (Community). The advance qt_resolve_run() consults
# QA_RUN_NAME_FALLBACKS — a |-separated list of run names to try when the exact
# per-issue name misses, which is how a per-issue round reuses a PR-level run.
# Community imported an older client whose signature is
# qt_resolve_run(name, cases, description): no fallback parameter at all, so the
# variable is simply never read.
#
# The assertions below are therefore split rather than skipped wholesale. The
# name-resolution and QA_ALLOW_RUN_CREATE behaviour IS Community's and is worth
# guarding; the fallback ones describe code Community does not have. Two of them
# would pass here for the wrong reason — a fallback that is never consulted and a
# lookup that legitimately misses both return the empty string — so leaving them
# in would report the feature as working.
if grep -q 'QA_RUN_NAME_FALLBACKS' "$QA_SRC_CLIENT"; then HAS_FB=1; else HAS_FB=0; fi

t "exact per-issue name hits"      PERISSUE "First round — PR #99 (issue #7)"
t "no match, no fallback"          ""       "First round — PR #1 (issue #2)"

if (( HAS_FB )); then
  t "falls back to PR-level run"     PRLEVEL  "First round — PR #16132 (issue #11514)" "First round — PR #16132"
  t "no match, fallback also misses" ""       "First round — PR #1 (issue #2)" "First round — PR #1"
  t "second fallback in the list"    PRLEVEL  "nope" "also-nope|First round — PR #16132"
  t "empty fallback var is ignored"  PERISSUE "First round — PR #99 (issue #7)" ""
else
  echo "SKIP  QA_RUN_NAME_FALLBACKS is not supported by this qatouch-client.sh (4 assertions)"
  echo "      Community rounds cannot reuse a PR-level run when the per-issue name"
  echo "      misses. Re-import qatouch-client.sh from advance to enable it."
fi
echo "--- message when nothing matches:"
QA_TEST_RUN_KEY= QA_RUN_NAME_FALLBACKS="First round — PR #1" QA_ALLOW_RUN_CREATE=0 \
  qt_resolve_run "First round — PR #1 (issue #2)" 2>&1 >/dev/null | sed 's/^/  /'
echo
echo "$pass passed, $fail failed"
exit $(( fail > 0 ? 1 : 0 ))
