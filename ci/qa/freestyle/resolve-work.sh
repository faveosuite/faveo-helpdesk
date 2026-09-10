#!/usr/bin/env bash
# The shell port of Jenkinsfile.qa's resolveWork() (advance repo, lines 416-476),
# adapted for two separate Freestyle jobs instead of one Pipeline job that runs
# both stages. See FREESTYLE-PLAN.md §6 step 00 and §7.
#
#   ci/qa/freestyle/resolve-work.sh <author|execute>
#
# <author|execute> is WHICH JOB is asking — faveo-qa-author passes "author",
# faveo-qa-firstround passes "execute". Both jobs receive every webhook
# delivery (Jenkins Generic Webhook Trigger has no per-job payload routing), so
# each must independently decide whether this build's payload is its work.
#
# On stdout, on success (exit 0): one line, "QA_NUMBER=<n>" — meant to be
# appended to a file and sourced (`>> qa-number.env`), matching
# FREESTYLE-PLAN.md's step 00.
#
# Exit codes:
#   0   this job has work — QA_NUMBER printed
#   1   nothing eligible for THIS job in this payload — caller should skip
#       quietly (touch qa-skip; exit 0), never fail the build
#
# Requires: env.community.sh already sourced (QA_BOT_LOGIN, QA_TRIGGER_LABEL,
# QA_MAX_SWEEP). gh-client.sh and discover.sh must be reachable at $QA_TOOLS
# (or ci/qa, before the tools snapshot exists) for the sweep fallback.

set -uo pipefail

want="${1:?usage: resolve-work.sh <author|execute>}"
tools="${QA_TOOLS:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"

# 1. Ignore our own label changes. Without this the webhook and a failure path
# form a loop: authoring fails, the trigger label goes back on, GitHub fires
# `labeled`, this fires again — indefinitely, spending tokens each time.
if [[ -n "${gh_sender:-}" && "${gh_sender}" == "${QA_BOT_LOGIN:-faveobot}" ]]; then
  printf 'resolve-work: label change was made by %s — this pipeline'"'"'s own doing, ignoring\n' "$gh_sender" >&2
  exit 1
fi

# 2. An explicit QA_NUMBER parameter wins — that is how a human re-runs one
# issue or PR. Community fix over the advance behaviour: with QA_STAGE=auto the
# advance Pipeline always resolves to 'author' regardless of which job asked
# (a documented trap — FREESTYLE-PLAN.md §6 step 00's note). With two SEPARATE
# Freestyle jobs that trap would make a manual PR re-run on faveo-qa-firstround
# silently do nothing whenever a human forgets to set QA_STAGE=execute. Here,
# 'auto' resolves to the job's OWN identity ($1) instead of hardcoding
# 'author', and the number is only honoured if that resolves to $want.
if [[ -n "${QA_NUMBER:-}" ]]; then
  resolved_stage="${QA_STAGE:-auto}"
  [[ "$resolved_stage" == "auto" ]] && resolved_stage="$want"
  if [[ "$resolved_stage" == "$want" ]]; then
    printf 'QA_NUMBER=%s\n' "${QA_NUMBER//[!0-9]/}"
    exit 0
  fi
  printf 'resolve-work: QA_NUMBER=%s set but resolved stage "%s" != this job'"'"'s "%s" — not this job'"'"'s work\n' \
    "$QA_NUMBER" "$resolved_stage" "$want" >&2
  exit 1
fi

# 3. Webhook payload — an issue. gh_is_pr distinguishes a PR (which also fires
# an `issues`-shaped event with issue.pull_request.url set) from a real issue;
# without this check a PR label would be treated as an issue and authoring
# would run against a pull request body.
if [[ "$want" == "author" ]]; then
  if [[ -n "${gh_issue:-}" && -z "${gh_is_pr:-}" \
        && "${gh_label:-}" == "${QA_TRIGGER_LABEL:-QA: Test case needed}" ]]; then
    printf 'QA_NUMBER=%s\n' "${gh_issue//[!0-9]/}"
    exit 0
  fi
fi

# 4. Webhook payload — a PR, from either the pull_request or check_suite shape.
if [[ "$want" == "execute" ]]; then
  pr="${gh_pr:-${gh_check_pr:-}}"
  if [[ -n "$pr" ]]; then
    printf 'QA_NUMBER=%s\n' "${pr//[!0-9]/}"
    exit 0
  fi
fi

# 5. Unattributable webhook, or a cron/manual sweep with QA_NUMBER blank.
# discover.sh emits one JSON object per line; take the first eligible item.
#
# LIMITATION (flagged, not silently absorbed): the advance Pipeline processes
# every item the sweep returns (capped at QA_MAX_SWEEP) in one build, in a
# Groovy for-loop. This Freestyle port runs one Execute-shell sequence per
# build with no such loop, so only the FIRST eligible item is taken; the rest
# are named on stderr so a human can see what was deferred, exactly as the
# advance Pipeline's log line does, but nothing re-queues them automatically —
# the next webhook or a manual re-run with QA_NUMBER set is what picks them up.
if command -v jq >/dev/null && [[ -x "${tools}/discover.sh" ]]; then
  all_json=$(bash "${tools}/discover.sh" "$want" 2>/dev/null | jq -s . 2>/dev/null || printf '[]')
  count=$(jq 'length' <<<"$all_json" 2>/dev/null || printf 0)
  if [[ "$count" -gt 0 ]]; then
    first=$(jq -r '.[0].number' <<<"$all_json")
    limit="${QA_MAX_SWEEP:-3}"
    if [[ "$count" -gt 1 ]]; then
      deferred=$(jq -r --argjson n "$limit" '.[1:$n] | map("#" + (.number|tostring)) | join(", ")' <<<"$all_json")
      printf 'resolve-work: sweep found %s item(s) for "%s"; taking #%s in this build. Deferred: %s\n' \
        "$count" "$want" "$first" "${deferred:-none}" >&2
    fi
    printf 'QA_NUMBER=%s\n' "$first"
    exit 0
  fi
fi

printf 'resolve-work: nothing eligible for "%s" in this payload\n' "$want" >&2
exit 1