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

# shellcheck disable=SC1091
. "${tools}/gh-client.sh"
# gh-client.sh unconditionally does `set -euo pipefail` when sourced. This
# script is deliberately -e-free (see the exit-code contract in the header —
# a "not eligible" result is data this script returns via exit status, not an
# error), so restore that immediately rather than let the source change it.
set +e
set -uo pipefail

# ---------------------------------------------------------------------------
# Organization-membership gate. Faveo Community is a public repository: anyone
# can open an issue or PR, and on a repo where triage/write access is not
# limited to maintainers, anyone with that access could apply the trigger
# label. Per the TL decision, the label alone must not be sufficient to start
# a round — the actor whose action produced this webhook delivery (gh_sender)
# must also belong to the Faveo GitHub organization.
#
# Applies to cases 3 and 4 below (the webhook-driven label/synchronize/
# check_suite triggers) — NOT to case 2 (an explicit QA_NUMBER build
# parameter). That path only runs when someone with Jenkins access starts the
# build by hand, which is already access-controlled by Jenkins itself, not by
# a label anyone with repo triage rights could apply.
#
# Fails CLOSED: no token, no actor, a network error, or any HTTP status other
# than the two GitHub documents (204 member / 404 not-a-member) all block the
# trigger rather than allow it. A maintainer can always re-run a wrongly
# blocked build with QA_NUMBER=<n> from Jenkins directly (case 2, unaffected
# by this gate) — that costs one manual step. A silent bypass costs a lot more.
gh_actor_is_org_member() {
  local actor="${1:-}" org="${QA_GITHUB_ORG:-${GITHUB_REPO%%/*}}"
  if [[ -z "$actor" ]]; then
    printf 'resolve-work: no actor on this payload — cannot verify org membership, blocking (fail closed)\n' >&2
    return 1
  fi
  if [[ -z "${GITHUB_TOKEN:-}" ]]; then
    printf 'resolve-work: GITHUB_TOKEN not set — cannot verify %s is a member of %s, blocking (fail closed)\n' "$actor" "$org" >&2
    return 1
  fi
  # GET /orgs/{org}/members/{username}: 204 = requester (this token's account)
  # AND target user are both members; 404 = target is not a member; 302 = the
  # TOKEN's own account is not a member of $org, so membership cannot be
  # determined at all (this usually means the bound faveobot token itself is
  # misconfigured, not that the actor is legitimately blocked).
  local code
  code=$(curl -sS -o /dev/null -w '%{http_code}' \
    -H "Authorization: Bearer ${GITHUB_TOKEN}" \
    -H 'Accept: application/vnd.github+json' \
    -H 'X-GitHub-Api-Version: 2022-11-28' \
    "${GH_API:-https://api.github.com}/orgs/${org}/members/${actor}" 2>/dev/null)
  case "${code:-000}" in
    204) return 0 ;;
    404) printf 'resolve-work: %s is not a member of %s — blocking trigger\n' "$actor" "$org" >&2; return 1 ;;
    302) printf 'resolve-work: org membership check for %s inconclusive (HTTP 302 — the bound token'"'"'s own account may not be a member of %s) — blocking (fail closed)\n' "$actor" "$org" >&2; return 1 ;;
    *)   printf 'resolve-work: org membership check for %s returned unexpected HTTP %s — blocking (fail closed)\n' "$actor" "${code:-000}" >&2; return 1 ;;
  esac
}

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
    if gh_actor_is_org_member "${gh_sender:-}"; then
      printf 'QA_NUMBER=%s\n' "${gh_issue//[!0-9]/}"
      exit 0
    fi
    printf 'resolve-work: issue #%s labelled by non-member %s — authoring blocked\n' \
      "${gh_issue}" "${gh_sender:-<unknown>}" >&2
    exit 1
  fi
fi

# 4. Webhook payload — a PR, from either the pull_request or check_suite shape.
if [[ "$want" == "execute" ]]; then
  pr="${gh_pr:-${gh_check_pr:-}}"
  if [[ -n "$pr" ]]; then
    if gh_actor_is_org_member "${gh_sender:-}"; then
      printf 'QA_NUMBER=%s\n' "${pr//[!0-9]/}"
      exit 0
    fi
    printf 'resolve-work: PR #%s event by non-member %s — execution blocked\n' \
      "${pr}" "${gh_sender:-<unknown>}" >&2
    exit 1
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
#
# NOT covered by the org-membership gate above, and NOT merely a narrow
# "webhook was missed" corner case: discover.sh finds already-labelled items
# by their current label state, with no notion of who applied the label, so
# there is no actor here to check at all.
#
# CONFIRMED REACHABLE on ordinary traffic, not just a missed delivery: cases 3
# and 4 above are `if want == "author"/"execute"` blocks that only exit when
# their INNER shape-match also succeeds. If it doesn't, execution falls
# through past them into this sweep. Since both jobs receive every webhook
# delivery, any PR-side event (a PR labelled, a review submitted, a
# check_suite completing, a new commit pushed) is the "wrong shape" for the
# author job's case 3 and falls through here — same for any issue-side event
# reaching the execute job's case 4. That means this sweep, UNGUARDED, would
# run on most ordinary webhook traffic on the repo, each time picking the
# first item discover.sh finds regardless of who labelled it — a direct
# bypass of the gate above.
#
# THE GUARD: gh_action ($.action) is populated by GitHub on all four
# subscribed event types (issues, pull_request, pull_request_review,
# check_suite all carry .action) — it is empty ONLY on a genuine manual/cron
# invocation with no webhook payload at all. Restricting the sweep to that
# case preserves the documented, intentional uses (a human building with
# QA_NUMBER blank to sweep after a truly missed delivery; a cron janitor-style
# run) while refusing to let a differently-shaped-but-real webhook event fall
# through into an unguarded search. A GitHub "Redeliver" of a genuinely missed
# webhook still has gh_action set, so it is handled by cases 3/4 (with the
# actor check) on replay, never by this sweep — nothing legitimate is lost.
if [[ -z "${gh_action:-}" ]] && command -v jq >/dev/null && [[ -x "${tools}/discover.sh" ]]; then
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