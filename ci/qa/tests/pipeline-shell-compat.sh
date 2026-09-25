#!/usr/bin/env bash
# Every shell block the pipeline hands to Jenkins, checked for the one mistake
# that cannot be seen by reading it: bash syntax in a block that Jenkins runs
# with /bin/sh.
#
#   ci/qa/tests/pipeline-shell-compat.sh [pipeline-script-path]
#
# WHY THIS EXISTS
#
# The durable-task plugin writes each sh step out to a file. When the block does
# not start with a shebang it prepends "#!/bin/sh -xe", and on a Debian agent
# /bin/sh is dash. dash rejects bash-only redirections (<<< herestrings, < <()
# process substitution) at PARSE time, so the block does not half-run — nothing
# in it runs at all.
#
# That is how PR #8364 died: the report stage paged the PR comments with
# `jq 'length' <<<"$body"`, dash refused to parse it, and the build failed with
#
#     script.sh.copy: 6: Syntax error: redirection unexpected
#     script returned exit code 2
#
# after the executor had already finished and written qa-report-8364.md. A full
# round of work, ~28 minutes and ~$9.66 of model time, thrown away at the last
# step — and the log says only "Syntax error", which points at the shell rather
# than at the stage that owns the bug.
#
# The pipeline already gets this right in four places ("sh \"\"\"#!/bin/bash").
# It got it wrong in one. Nothing was checking, so it stayed wrong until a
# production round hit it. This checks.
#
# NOTE ON qaSh: it wraps its argument in a plain triple-quoted block and adds no
# shebang, so anything passed to qaSh also runs under dash. Those call sites are
# checked too, and cannot be fixed with a shebang — rewrite them POSIX, or call
# a ci/qa/*.sh script with `bash` the way the other stages do.
#
# The pipeline script is pasted into the Jenkins job and NOT committed, so pass a
# path or set QA_PIPELINE_SCRIPT_PATH. With neither, this skips (exit 0).
set -uo pipefail

JF="${1:-${QA_PIPELINE_SCRIPT_PATH:-}}"

if [[ -z "$JF" ]]; then
  cat >&2 <<'MSG'
pipeline-shell-compat: no pipeline script path given.

  bash ci/qa/tests/pipeline-shell-compat.sh /path/to/JENKINS-PIPELINE-SCRIPT.groovy

Skipping — not a failure.
MSG
  exit 0
fi
[[ -f "$JF" ]] || { printf 'pipeline-shell-compat: %s does not exist\n' "$JF" >&2; exit 1; }

awk '
# Bash-only constructs that dash cannot parse or does not implement. Kept narrow
# on purpose: a false positive here blocks a legitimate pipeline change, so this
# lists only what genuinely breaks under dash.
function bashism(s,   t) {
  t = s
  gsub(/#.*$/, "", t)                       # a trailing shell comment is not code
  if (t ~ /<<</)                    return "<<< herestring"
  if (t ~ /<[ \t]*\(/)              return "< <() process substitution"
  if (t ~ /\|&/)                    return "|& pipe-stderr"
  if (t ~ /&>/)                     return "&> redirect"
  if (t ~ /\[\[/)                   return "[[ ]] test"
  if (t ~ /(^|[ \t;])source[ \t]/)  return "source (use .)"
  if (t ~ /(^|[ \t;])local[ \t]/)   return "local"
  if (t ~ /(^|[ \t;])mapfile[ \t]/) return "mapfile"
  if (t ~ /(^|[ \t;])declare[ \t]+-[aA]/) return "declare -a/-A"
  if (t ~ /\$RANDOM/)               return "$RANDOM"
  if (t ~ /(^|[ \t;])read[ \t]+-a/) return "read -a"
  if (t ~ /function[ \t]+[A-Za-z_][A-Za-z0-9_]*[ \t]*\(/) return "function keyword"
  return ""
}

# Opening line of a shell block. Returns the delimiter it opened with.
function opener(s) {
  if (s ~ /(^|[^A-Za-z0-9_.])(sh|qaSh)[ \t]*(\(|")/ || s ~ /(^|[^A-Za-z0-9_.])(sh|qaSh)[ \t]*\x27/) {
    if (s ~ /"""/)  return "\"\"\""
    if (s ~ /\x27\x27\x27/) return "\x27\x27\x27"
  }
  return ""
}

BEGIN { inblk = 0; bad = 0; nblk = 0 }

{
  line = $0

  if (!inblk) {
    sub(/^[ \t]*\/\/.*/, "", line)            # a Groovy comment is not a block
    d = opener(line)

    # A one-line sh "..." / qaSh("...") never carries a shebang, so anything
    # bash-only inside it runs under dash. Only the quoted argument is scanned —
    # scanning the whole line would trip over Groovy syntax such as a nested
    # list literal.
    if (d == "" && line ~ /(^|[^A-Za-z0-9_.])(sh|qaSh)[ \t]*[("\x27]/) {
      q = ""
      p1 = index(line, "\""); p2 = index(line, "\x27")
      if (p1 > 0 && (p2 == 0 || p1 < p2)) { q = "\""; sp = p1 }
      else if (p2 > 0)                    { q = "\x27"; sp = p2 }
      if (q != "") {
        arg = substr(line, sp + 1)
        lp = 0
        for (k = length(arg); k >= 1; k--) if (substr(arg, k, 1) == q) { lp = k; break }
        if (lp > 1) {
          nblk++
          arg = substr(arg, 1, lp - 1)
          why = bashism(arg)
          if (why != "") {
            bad++
            printf "FAIL  L%d: %s\n", NR, why
            printf "        in a one-line sh/qaSh call (always runs under /bin/sh)\n"
            printf "        %s\n", substr(arg, 1, 100)
          }
        }
      }
      next
    }

    if (d == "") next

    nblk++
    # Text after the opening delimiter decides the shell: a shebang glued to the
    # delimiter is honoured by Jenkins, anything else means /bin/sh.
    i = index(line, d)
    rest = substr(line, i + length(d))
    isbash = (rest ~ /^#![ \t]*\/(usr\/bin\/env[ \t]+bash|bin\/bash)/)
    # qaSh re-wraps its argument in its own un-shebanged block, so a shebang
    # written by the caller never reaches the top of the generated file.
    if (line ~ /(^|[^A-Za-z0-9_.])qaSh[ \t]*\(/) isbash = 0

    start = NR; shell = (isbash ? "bash" : "sh(dash)")
    if (index(rest, d) > 0) next              # opened and closed on one line
    inblk = 1; delim = d; next
  }

  if (index(line, delim) > 0) { inblk = 0; next }
  if (isbash) next                            # runs under bash, bashisms are fine

  why = bashism(line)
  if (why != "") {
    bad++
    printf "FAIL  L%d: %s\n", NR, why
    printf "        in the %s block opened at L%d\n", shell, start
    gsub(/^[ \t]+/, "", line)
    printf "        %s\n", substr(line, 1, 100)
  }
}

END {
  printf "\nscanned %d shell block(s) in the pipeline script\n", nblk
  if (nblk < 10) {
    print "FAIL  extractor matched fewer blocks than this pipeline is known to have —"
    print "      it is under-matching, so a clean result here means nothing"
    exit 1
  }
  if (bad > 0) {
    printf "\n%d bash-only construct(s) in block(s) Jenkins runs with /bin/sh.\n", bad
    print "Add #!/bin/bash as the FIRST thing after the opening quotes, e.g."
    print "    sh \"\"\"#!/bin/bash"
    print "        set -e -o pipefail"
    print "or rewrite the construct POSIX (printf %s \"$x\" | jq ... instead of <<<)."
    exit 1
  }
  print "PASS  every bash-only construct is in a block with a bash shebang"
  exit 0
}
' "$JF"
