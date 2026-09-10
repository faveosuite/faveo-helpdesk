#!/usr/bin/env bash
# When the authoring or executing agent stops on a usage limit, work out WHEN the
# limit resets and print it in a form a person can act on.
#
#   ci/qa/limit-reset.sh <stream.jsonl>
#
# Prints one line to stdout and exits 0 when a reset time was found:
#   1755705600|2026-08-20 16:00 UTC|2026-08-20 21:30 IST
# Prints nothing and exits 1 when the stream does not carry one.
#
# Why the shotgun of field names: the CLI knows the reset instant (its own status
# line renders "continuing automatically at ..." from resetsAtSeconds), but which
# of those fields reaches --output-format stream-json is NOT verified on a real
# limit — no run here has hit one yet. So accept every shape it plausibly emits,
# and when none matches, say nothing rather than invent a time. A wrong reset
# timestamp is worse than none: it sends someone away for an hour on a limit that
# already lifted, or has them hammering one that has not.
#
# Exit 1 with no output is a normal outcome, not an error.
set -uo pipefail

stream="${1:-}"
[[ -n "$stream" && -s "$stream" ]] || exit 1

# Absolute epoch, under any of the spellings seen in the CLI bundle. Seconds and
# milliseconds are both accepted: anything past year ~5138 is milliseconds.
epoch=$(jq -r '
  [ .. | objects
    | (.resetsAtSeconds?, .resetsAt?, .resetAt?, .reset_at?, .resets_at?,
       .rateLimitResetsAt?, .unifiedRateLimitResetsAt?)
  ] | flatten | map(select(. != null))
    | map(if type == "number" then . elif type == "string" then (tonumber? // empty) else empty end)
    | map(select(. > 1000000000))
    | if length > 0 then (max | floor) else empty end' \
  "$stream" 2>/dev/null | tail -1)

# The message form: "Claude AI usage limit reached|1755705600".
if [[ -z "$epoch" ]]; then
  epoch=$(grep -oE 'limit reached\|[0-9]{10,13}' "$stream" 2>/dev/null \
          | grep -oE '[0-9]{10,13}' | sort -rn | head -1)
fi

# An ISO-8601 reset instant rather than an epoch.
if [[ -z "$epoch" ]]; then
  iso=$(jq -r '[ .. | objects | (.resetsAt?, .resetAt?, .reset_at?)
                ] | flatten | map(select(type == "string"))
                  | map(select(test("^[0-9]{4}-[0-9]{2}-[0-9]{2}T")))
                  | if length > 0 then .[-1] else empty end' "$stream" 2>/dev/null | tail -1)
  [[ -n "$iso" ]] && epoch=$(date -u -d "$iso" +%s 2>/dev/null || true)
fi

# Relative: seconds from when the failure was recorded, which is the stream's own
# last-modified time — the run had already ended by then.
if [[ -z "$epoch" ]]; then
  after=$(jq -r '[ .. | objects | (.retryAfter?, .retry_after?, .retryAfterSeconds?)
                 ] | flatten | map(select(. != null))
                   | map(if type == "number" then . elif type == "string" then (tonumber? // empty) else empty end)
                   | map(select(. > 0 and . < 2592000))
                   | if length > 0 then (max | floor) else empty end' "$stream" 2>/dev/null | tail -1)
  if [[ -n "$after" ]]; then
    base=$(date -r "$stream" +%s 2>/dev/null || date +%s)
    epoch=$(( base + after ))
  fi
fi

[[ -n "$epoch" ]] || exit 1
# Milliseconds -> seconds.
(( epoch > 99999999999 )) && epoch=$(( epoch / 1000 ))
# Anything more than ~35 days out is not a usage window; treat it as a misparse
# rather than telling someone to come back next month.
(( epoch > $(date +%s) - 86400 && epoch < $(date +%s) + 3024000 )) || exit 1

utc=$(date -u -d "@${epoch}" '+%Y-%m-%d %H:%M UTC' 2>/dev/null || true)
ist=$(TZ='Asia/Kolkata' date -d "@${epoch}" '+%Y-%m-%d %H:%M IST' 2>/dev/null || true)
[[ -n "$utc" ]] || exit 1
printf '%s|%s|%s\n' "$epoch" "$utc" "${ist:-$utc}"
