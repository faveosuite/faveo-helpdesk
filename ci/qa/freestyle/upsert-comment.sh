#!/usr/bin/env bash
# Shell port of Jenkinsfile.qa's upsertPRComment() (advance repo, lines
# 2729-2751). Identical logic — this is GitHub API plumbing, nothing
# application-specific to adapt.
#
#   ci/qa/freestyle/upsert-comment.sh <pr-or-issue-number> <marker> <body-file>
#
# <marker> is a short tag, e.g. "qa-first-round" or "qa-author" — written into
# the comment as `<!-- jenkins:<marker> -->` so a later call replaces THIS
# marker's comment rather than stacking a new one on every round.
#
# Requires: GITHUB_TOKEN, GITHUB_REPO in the environment.
#
# Exit codes: 0 ok, 1 missing arguments/env, 2 the GitHub API call failed.

set -uo pipefail

number="${1:?usage: upsert-comment.sh <number> <marker> <body-file>}"
marker="${2:?usage: upsert-comment.sh <number> <marker> <body-file>}"
body_file="${3:?usage: upsert-comment.sh <number> <marker> <body-file>}"

: "${GITHUB_TOKEN:?GITHUB_TOKEN required}"
: "${GITHUB_REPO:?GITHUB_REPO required}"
[[ -f "$body_file" ]] || { echo "upsert-comment: no such file: $body_file" >&2; exit 1; }

tag="<!-- jenkins:${marker} -->"

existing_id=$(curl -sS -H "Authorization: token ${GITHUB_TOKEN}" \
    "https://api.github.com/repos/${GITHUB_REPO}/issues/${number}/comments?per_page=100" \
  | jq -r --arg tag "$tag" '.[] | select(.body | contains($tag)) | .id' | head -1)

payload=$(mktemp)
trap 'rm -f "$payload"' EXIT
jq -Rs --arg tag "$tag" '{body: ($tag + "\n" + .)}' "$body_file" > "$payload"

if [[ -n "$existing_id" ]]; then
  http_code=$(curl -sS -o /dev/null -w '%{http_code}' -X PATCH \
    -H "Authorization: token ${GITHUB_TOKEN}" -H 'Content-Type: application/json' \
    -d "@${payload}" \
    "https://api.github.com/repos/${GITHUB_REPO}/issues/comments/${existing_id}")
else
  http_code=$(curl -sS -o /dev/null -w '%{http_code}' -X POST \
    -H "Authorization: token ${GITHUB_TOKEN}" -H 'Content-Type: application/json' \
    -d "@${payload}" \
    "https://api.github.com/repos/${GITHUB_REPO}/issues/${number}/comments")
fi

case "$http_code" in
  2??) exit 0 ;;
  *)   echo "upsert-comment: GitHub API returned HTTP ${http_code}" >&2; exit 2 ;;
esac