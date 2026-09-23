#!/usr/bin/env bash
# Split a Faveo licence code into the four segments testing-setup takes.
#
#   QA_LICENSE_CODE=... ci/qa/license-segments.sh
#
# Prints the four segments space-separated on stdout, uppercased. Exits 1 with a
# diagnostic on stderr when the value cannot be read as four segments.
#
# Why this is not one `IFS=- read` line. The value arrives from a Jenkins secret,
# which means it is whatever a human pasted into a text box: a trailing newline, a
# stray \r from a Windows copy, wrapping spaces, segments separated by spaces or
# commas instead of dashes, or the 16 characters run together with no separator at
# all. All of those are the same licence, and all of them failed the strict check
# with the same unhelpful "must be four dash-separated segments" — which says what
# was wanted and nothing about what arrived.
#
# The diagnostic reports the NUMBER of segments found and their LENGTHS, never their
# contents. That is enough to tell "I pasted three" from "I pasted a whole URL"
# without printing a licence key into a build log.
set -uo pipefail

code="${QA_LICENSE_CODE:-}"

if [[ -z "$code" ]]; then
  printf 'license-segments: the licence credential is empty\n' >&2
  exit 1
fi

# Normalise: drop CR and any character that cannot be part of a segment or a
# separator, then treat every run of separators as one dash.
code=$(printf '%s' "$code" | tr -d '\r\n')
code=$(printf '%s' "$code" | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//')
normalised=$(printf '%s' "$code" | sed -e 's/[[:space:],_|]\+/-/g' -e 's/-\+/-/g' -e 's/^-//' -e 's/-$//')

IFS='-' read -r -a segments <<< "$normalised"

# A code pasted without separators: 16 characters, split 4-4-4-4. Only attempted
# when there is exactly one segment, so it can never re-split a real 4-part code.
if (( ${#segments[@]} == 1 )) && [[ ${#normalised} -eq 16 ]]; then
  segments=("${normalised:0:4}" "${normalised:4:4}" "${normalised:8:4}" "${normalised:12:4}")
  printf 'license-segments: no separators found; read the 16 characters as four segments of four\n' >&2
fi

if (( ${#segments[@]} != 4 )); then
  lengths=""
  for s in "${segments[@]}"; do lengths+="${#s} "; done
  printf 'license-segments: found %d segment(s) with length(s) [%s] in a value of %d character(s) — expected 4.\n' \
    "${#segments[@]}" "${lengths% }" "${#code}" >&2
  printf 'license-segments: the credential should hold the licence as AAAA-BBBB-CCCC-DDDD (dashes, spaces or commas all work). Segment CONTENTS are never logged.\n' >&2
  exit 1
fi

for s in "${segments[@]}"; do
  if [[ -z "$s" ]]; then
    printf 'license-segments: one of the four segments is empty — the value has two separators together or a leading/trailing one\n' >&2
    exit 1
  fi
done

printf '%s %s %s %s\n' \
  "${segments[0]^^}" "${segments[1]^^}" "${segments[2]^^}" "${segments[3]^^}"
