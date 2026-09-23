#!/usr/bin/env bash
# Turn a build's phase marks into a table.
#
#   ci/qa/phase-summary.sh <marks-file>
#
# The marks file holds one line per phase START — "<epoch seconds><TAB><label>" —
# so a phase lasts until the next mark and the file ends with an 'end' mark. That
# shape means a build can be timed by appending one line at each boundary, with no
# state carried in the pipeline.
#
# Why this exists: `timestamps()` puts a clock on every log line, which answers
# "when" but not "how long", and the question that actually gets asked after a slow
# round is where the hour went. Round 1 of PR #16113 took 1h 16m 29s and the split
# had to be reconstructed by hand from a DIFFERENT build's log. Anything that has
# to be reconstructed will eventually be reconstructed wrongly.
set -uo pipefail

marks="${1:-}"
if [[ -z "$marks" || ! -s "$marks" ]]; then
  printf 'phase-summary: no marks in %s\n' "${marks:-<no file>}" >&2
  exit 0   # never fail a build over its own instrumentation
fi

awk -F'\t' '
  function hms(s,    h, m) {
    h = int(s / 3600); m = int((s % 3600) / 60)
    if (h > 0) { return sprintf("%dh %02dm %02ds", h, m, s % 60) }
    return sprintf("%dm %02ds", m, s % 60)
  }
  NR == 1 { first = $1; prev = $1; label = $2; next }
  {
    # Marks are monotonic in a build, but a clock adjustment mid-run would print a
    # negative phase, which reads as an instrumentation bug rather than a slow step.
    d = $1 - prev; if (d < 0) { d = 0 }
    if (d > longest_d) { longest_d = d; longest = label }
    printf "  %-32s %10s\n", label, hms(d)
    prev = $1; label = $2
  }
  END {
    total = prev - first
    printf "  %-32s %10s\n", "─── total", hms(total)
    if (longest != "" && total > 0) {
      printf "\n  slowest phase: %s (%s, %d%% of the build)\n",
             longest, hms(longest_d), int(longest_d * 100 / total + 0.5)
    }
  }
' "$marks"
