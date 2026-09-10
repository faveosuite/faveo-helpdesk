#!/usr/bin/env bash
# Stage 1b: push authored test cases into QA Touch and publish them on the issue.
#
#   ci/qa/stage1-publish.sh <issue-number> <cases-json> [--dry-run]
#
# The authoring step (Claude, see stage1-author-prompt.md) produces the JSON; all
# the deterministic work lives here, in tested shell, rather than in a prompt:
# validate, resolve the module, persist, resolve the TR#### codes, comment, label.
#
# Exit codes:
#   0   cases published
#   3   skip, quietly (already published)
#   4   stop, loudly (bad input, nothing persisted)

set -euo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
. "${here}/gh-client.sh"
. "${here}/qatouch-client.sh"
. "${here}/marker.sh"

# The repo already has a QA label taxonomy; these map onto it rather than adding
# a parallel set. Two sets of labels meaning nearly the same thing is worse than
# no automation, because nobody can tell which one drives what.
TRIGGER_LABEL="${QA_TRIGGER_LABEL:-QA: Test case needed}"
PROGRESS_LABEL="${QA_PROGRESS_LABEL:-QA: Test case In Progress}"
DONE_LABEL="${QA_DONE_LABEL:-QA Test Cases Added}"
# Only used to tell the reviewer which label to apply next; the gate that
# actually enforces it lives in stage3-gate.sh and reads the same variable.
QA_APPROVED_LABELS="${QA_APPROVED_LABELS:-QA: Test case Approved}"
# Applied when the issue carries content the authoring step could not open — an
# Office document, a video, a Google Doc behind a login. The cases still get
# published (what the issue does support is worth having), but a human is told that
# part of the specification was never read. Verified to exist in the repo.
NEEDS_INFO_LABEL="${QA_NEEDS_INFO_LABEL:-Need more info about issues by QA team}"

SKIP=3
STOP=4

issue="${1:?usage: stage1-publish.sh <issue-number> <cases-json> [--dry-run]}"
cases_file="${2:?usage: stage1-publish.sh <issue-number> <cases-json> [--dry-run]}"
dry_run=false
[[ "${3:-}" == "--dry-run" ]] && dry_run=true

work=$(mktemp -d)
trap 'rm -rf "$work"' EXIT

skip() { printf 'stage1: skip — %s\n' "$1" >&2; exit $SKIP; }

# A tester who labelled an issue and saw nothing happen needs the reason on the
# issue; the workflow log is not somewhere they will look.
fail_on_issue() {
  printf 'stage1: stop — %s\n' "$1" >&2
  $dry_run && exit $STOP
  {
    printf '## Could not publish test cases\n\n%s\n\n' "$1"
    printf 'Nothing was written to QA Touch. The `%s` label has been put back — remove and re-add it to retry.\n' "$TRIGGER_LABEL"
  } > "${work}/fail.md"
  gh_comment "$issue" "${work}/fail.md"
  # Hand the issue back: cases genuinely are still needed, and leaving it parked
  # on "In Progress" would read as work happening when nothing is.
  gh_remove_label "$issue" "$PROGRESS_LABEL"
  gh_add_label "$issue" "$TRIGGER_LABEL"
  exit $STOP
}

gh_require_env || { printf 'stage1: GitHub env incomplete\n' >&2; exit $STOP; }
qt_require_env || { printf 'stage1: QA Touch env incomplete\n' >&2; exit $STOP; }

# Re-applying the trigger label to an issue that already has cases means "write
# more", not "write them again" — so this AMENDS. Appending only ever adds: no
# existing case is edited, renumbered or removed, because those cases may already
# be approved and already sitting in a run with results against them (marker.md,
# rule 3). To genuinely redo a set, delete the marker comment first.
previous=''
if marker_exists "$issue"; then
  if [[ "${QA_AMEND:-1}" != "1" ]]; then
    skip "issue #${issue} already has published cases and QA_AMEND is off"
  fi

  # A FRESH marker means the cases were published minutes ago, which almost never
  # means "a person wants more" — it means this build is a duplicate. Two webhook
  # deliveries for the same label (or a build queued behind another by
  # disableConcurrentBuilds) both resolve their work from their own payload, so the
  # second one arrives with the issue already done and amends it. On the first live
  # run that produced a second authoring pass and a failed publish on an issue that
  # was already finished.
  #
  # An intentional amend is a human re-applying the label, which will be well over
  # the window; the window only rejects the machine talking to itself.
  marker_age_minutes=$(gh_comments "$issue" \
    | jq -r --arg open "$MARKER_OPEN" '
        [.[] | select(.body | contains($open)) | .created_at] | last // empty' \
    | { read -r ts; [[ -n "$ts" ]] && printf '%s' "$(( ( $(date -u +%s) - $(date -u -d "$ts" +%s) ) / 60 ))" || printf ''; })

  if [[ -n "$marker_age_minutes" ]] && (( marker_age_minutes < ${QA_AMEND_MIN_AGE_MINUTES:-30} )); then
    skip "issue #${issue} was published ${marker_age_minutes} minute(s) ago — treating this as a duplicate trigger, not an amend (raise or lower QA_AMEND_MIN_AGE_MINUTES to change that)"
  fi
  set +e
  previous=$(marker_read "$issue")
  marker_rc=$?
  set -e
  case $marker_rc in
    0) printf 'stage1: issue #%s already has %s case(s) — amending\n' \
         "$issue" "$(jq '.cases | length' <<<"$previous")" ;;
    *) printf 'stage1: issue #%s has a marker this code cannot read — refusing to amend\n' "$issue" >&2
       exit $STOP ;;
  esac
fi

# ---------------------------------------------------------------------------
# 1. validate the authored JSON before touching QA Touch
# ---------------------------------------------------------------------------
# Partial writes are the failure mode to avoid: half a case set in a shared
# 8,200-case library, with no marker recording which half.
[[ -s "$cases_file" ]] || fail_on_issue "The authoring step produced no output."
jq -e . "$cases_file" >/dev/null 2>&1 || fail_on_issue "The authoring step produced invalid JSON."

module=$(jq -r '.module // ""' "$cases_file")
kind=$(jq -r '.kind // "enhancement"' "$cases_file")
total=$(jq '.cases | length' "$cases_file")

[[ -n "$module" ]] || fail_on_issue "The authored JSON has no \`module\`."
(( total > 0 ))    || fail_on_issue "The authored JSON contains no cases."
# No cap by default: the issue decides how many cases it needs, and a number here
# could only ever be wrong in one direction — silently refusing coverage a change
# genuinely required. QA_MAX_CASES is left unset in Jenkinsfile.qa; set it to a
# positive integer to reinstate a ceiling for a particular project.
#
# What still stands in for it: the step rules below reject the whole set if any case
# has fewer than 4 steps or a blank expected result, and they are a content check
# rather than a count — a set that fails them is malformed however big it is. That
# is the guard doing real work. A count never distinguished 150 legitimate cases
# from 150 junk ones.
if [[ -n "${QA_MAX_CASES:-}" ]] && (( QA_MAX_CASES > 0 )) && (( total > QA_MAX_CASES )); then
  fail_on_issue "The authored JSON contains ${total} cases, over the QA_MAX_CASES ceiling of ${QA_MAX_CASES}. Raise or unset QA_MAX_CASES to allow it."
fi

# Large sets are legitimate — a rewrite touching twenty areas needs them — but they
# are also what a malformed or manipulated run looks like, and there is no
# delete-case endpoint to undo one with. So: never block, always announce, and put
# the count on the issue so a human sees the size before reviewing.
if (( total >= 100 )); then
  printf 'stage1: NOTE %d cases in one set — large but permitted; review the set in QA Touch before approving\n' "$total" >&2
fi

# An explicit "Module: X" line in the issue beats the model's choice — it is the
# only signal that carries a human's intent about where these belong.
issue_module=$(gh_issue "$issue" | jq -r '.body // ""' \
  | grep -ioP '^\s*Module:\s*\K.+' | head -1 | sed 's/[[:space:]]*$//' | tr -d '\r' || true)
if [[ -n "$issue_module" && "$issue_module" != "$module" ]]; then
  printf 'stage1: issue specifies module "%s", overriding authored "%s"\n' "$issue_module" "$module"
  module="$issue_module"
fi

jq -c '.cases[]' "$cases_file" > "${work}/cases.jsonl"

# Same two rules qt_create_case enforces, checked up front so a bad set is
# rejected whole rather than persisted down to the first offender.
rejects=$(jq -c 'select((.steps | length) < 4 or ([.steps[] | select((.expectedResult // "") == "")] | length) > 0)
                 | {title: .caseTitle, steps: (.steps|length),
                    blank: ([.steps[] | select((.expectedResult // "") == "")] | length)}' \
          "${work}/cases.jsonl")

if [[ -n "$rejects" ]]; then
  printf 'stage1: %d of %d authored case(s) violate the step rules:\n' \
    "$(wc -l <<<"$rejects")" "$total" >&2
  jq -r '"  - \(.title): \(.steps) steps, \(.blank) blank expected result(s)"' <<<"$rejects" >&2
  fail_on_issue "$(printf 'The authored cases broke the step rules (≥4 steps, every step needing a non-empty expected result):\n\n```\n%s\n```\n\nThis is an authoring fault, not a QA Touch one.' \
    "$(jq -r '"- \(.title): \(.steps) steps, \(.blank) blank expected"' <<<"$rejects")")"
fi

printf 'stage1: %d case(s) authored for %s, module "%s"\n' "$total" "$kind" "$module"

# What could the authoring step not open? The manifest is written by
# ci/qa/fetch-attachments.sh; absent manifest means attachments were never fetched,
# which is not the same as "there were none" — so say nothing rather than imply it.
unopened=''

# The authoring step's own account of what it could not use. The manifest covers
# what the fetcher could not GET; this covers what the model could not make sense
# of, which is a different gap and only it can report.
declared=$(jq -r '(.unanalysed // [])[] | "- `unanalysed` " + .' "$cases_file" 2>/dev/null)
[[ -n "$declared" ]] && unopened="$declared"

manifest="${QA_ATTACHMENTS_DIR:-}/manifest.json"
if [[ -n "${QA_ATTACHMENTS_DIR:-}" && -s "$manifest" ]]; then
  from_manifest=$(jq -r '.items[] | select(.status == "unreadable" or .status == "unfetchable")
                    | "- `\(.status)` \(.url) — \(.note)"' "$manifest")
  unopened=$(printf '%s\n%s' "$unopened" "$from_manifest" | sed '/^$/d')
  if [[ -n "$unopened" ]]; then
    printf 'stage1: %d attachment(s) could not be opened — the issue will be labelled "%s"\n' \
      "$(wc -l <<<"$unopened")" "$NEEDS_INFO_LABEL" >&2
  fi
fi

if $dry_run; then
  printf 'stage1: --dry-run, no writes. Would create:\n'
  jq -r '"  " + .caseTitle + "  (" + (.steps|length|tostring) + " steps)"' "${work}/cases.jsonl"
  exit 0
fi

# ---------------------------------------------------------------------------
# 2. module, then persist
# ---------------------------------------------------------------------------
# An amend stays where the existing cases are. The marker carries ONE module, so
# scattering the additions across modules would make it describe something that
# is no longer true — and Stage 3 reports "module X" for cases that are not in it.
if [[ -n "$previous" ]]; then
  previous_module=$(jq -r '.module // ""' <<<"$previous")
  if [[ -n "$previous_module" && "$previous_module" != "$module" ]]; then
    printf 'stage1: amending into the existing module "%s" (authored "%s")\n' \
      "$previous_module" "$module"
    module="$previous_module"
  fi
fi

module_key=$(qt_module_key_by_name "$module" || true)
if [[ -z "$module_key" ]]; then
  # qt_create_module also re-looks-up on a keyless response — see its comment.
  # Its stderr carries QA Touch's own account of a refusal, so it goes to a file
  # and onto the issue rather than only into a build log nobody opens. The first
  # time this failed, the comment said "the response carried no module key" and
  # the reason had already been discarded.
  module_key=$(qt_create_module "$module" 2> "${work}/module-create.log" || true)
  cat "${work}/module-create.log" >&2 || true

  # A create that comes back with no key is almost always "the name is taken":
  # POST /module answers 200 with success:false and no message at all. The usual
  # cause is a case difference, which qt_module_key_by_name now handles — the
  # library holds "Form builder" and an exact match on "Form Builder" missed it
  # while QA Touch refused the duplicate.
  #
  # This is the remaining case: taken, and still not in the listing. A case already
  # sitting in that folder carries its module_key, so go and find one.
  if [[ -z "$module_key" ]]; then
    module_key=$(qt_module_key_from_cases "$module" || true)
    if [[ -n "$module_key" ]]; then
      printf 'stage1: module "%s" -> %s (resolved from an existing case; the module listing omits it)\n' \
        "$module" "$module_key"
    fi
  fi

  # Opt-in unblock: with QA_MODULE_FALLBACK_SUFFIX set, publish into
  # "<module> <suffix>" instead of failing. Off by default on purpose — a second
  # module beside the real one splits the taxonomy, and only a human should decide
  # to do that. Being stuck is sometimes the correct outcome.
  if [[ -z "$module_key" && -n "${QA_MODULE_FALLBACK_SUFFIX:-}" ]]; then
    fallback="${module} ${QA_MODULE_FALLBACK_SUFFIX}"
    printf 'stage1: "%s" is unusable; trying fallback module "%s"\n' "$module" "$fallback" >&2
    module_key=$(qt_create_module "$fallback" 2>> "${work}/module-create.log" || true)
    if [[ -n "$module_key" ]]; then
      fallback_used="$module"
      module="$fallback"
    fi
  fi

  if [[ -z "$module_key" ]]; then
    # Name the modules that ARE addressable, so the next action is one edit rather
    # than a hunt through 328 of them.
    candidates=$(qt_module_candidates "$module" 8 || true)
    fail_on_issue "$(printf 'Could not use the QA Touch module **%s**.\n\nQA Touch refused to create it, it is not in the module list the API returns, and no test case anywhere in the project belongs to it. So the name is taken by a folder that holds no cases of its own — a parent whose cases live in child folders, most likely. Nothing in the API exposes such a folder: `getAllModules` omits it and there is no case to reveal its key.\n\nWhat QA Touch said:\n\n```\n%s\n```\n\n**To unblock this issue, add a line to its description naming a module that is addressable, then re-apply `%s`:**\n\n```\nModule: <name>\n```\n%s\n\nAlternatively rename the existing **%s** folder in QA Touch, which frees the name for creation.' \
      "$module" "$(sed -n '1,25p' "${work}/module-create.log")" "$TRIGGER_LABEL" \
      "$([[ -n "$candidates" ]] && printf '\nClosest existing modules the API *can* write to:\n\n%s\n' "$(sed 's/^/- /' <<<"$candidates")")" \
      "$module")"
  fi
  module_was_created=1
  printf 'stage1: created module "%s" -> %s (NEW — the API creates at the top level of the tree)\n' \
    "$module" "$module_key"
else
  printf 'stage1: module "%s" -> %s (existing)\n' "$module" "$module_key"
fi

issue_url=$(gh_issue "$issue" | jq -r '.html_url')
total_before=$(qt_cases_total)
created=0
: > "${work}/created-titles.txt"

while IFS= read -r case_json; do
  # POST /testCase/steps accepts neither type nor priority, so the discipline a
  # case belongs to has nowhere structured to live. Prefix the description: it
  # round-trips intact, it shows in the QA Touch list, and Stage 3 reads it to
  # know whether it is driving a browser or calling an API.
  case_json=$(jq -c --arg ref "$issue_url" '
    . + {reference: $ref}
      + (if (.discipline // "") == "" then {}
         else {description: ("[" + .discipline + "] " + (.description // ""))} end)' <<<"$case_json")
  if qt_create_case "$module_key" "$case_json" >/dev/null; then
    jq -r '.caseTitle' <<<"$case_json" >> "${work}/created-titles.txt"
    # created=$(( created + 1 )), not (( created++ )): the post-increment form
    # evaluates to the OLD value, so the very first increment from 0 returns exit
    # status 1 and `set -e` kills the script mid-publish — after the first case
    # was created and before anything recorded that it was.
    created=$(( created + 1 ))
  fi
done < "${work}/cases.jsonl"

(( created > 0 )) || fail_on_issue "Every case was rejected by QA Touch. See the run log."

# Creating a case does not return its code, so resolve from the tail pages.
qt_resolve_new_case_codes "$total_before" "$created" > "${work}/resolved.jsonl"

# Match the tail pages back to what we just created, by title. Titles are not
# unique in a shared library — an abandoned earlier run can leave a case with the
# same title sitting in the same tail — so when more rows match than we created,
# keep the LAST $created of them: the pages are oldest-first, so the newest rows
# are ours.
cases_json=$(jq -sc --rawfile titles "${work}/created-titles.txt" --argjson created "$created" '
  ($titles | split("\n") | map(select(length > 0))) as $wanted
  | map(select(.case_title as $t | $wanted | index($t)))
  | (if (length > $created) then .[-$created:] else . end)
  | map({code: .case_code, key: .case_key})
' "${work}/resolved.jsonl")
resolved=$(jq 'length' <<<"$cases_json")

# A code that didn't resolve means Stage 3 cannot execute that case. Say so on
# the issue rather than letting it quietly go missing weeks later.
unresolved=$(( created - resolved ))
(( unresolved <= 0 )) || printf 'stage1: %d case(s) created but their codes did not resolve\n' "$unresolved" >&2

# ---------------------------------------------------------------------------
# 3. publish on the issue
# ---------------------------------------------------------------------------
# QA Touch renders a case as <project prefix>-<number> in its UI — FH-8967 on
# Faveo Helpdesk — while the API reports the very same case as TR8967. Same case,
# same number, two renderings. Publishing the API form meant a tester read TR8967
# on the issue, searched QA Touch for it, and found nothing.
#
# So: human-facing output uses the UI form, and the MARKER KEEPS THE API FORM,
# because TR#### is the identifier the results endpoint accepts and Stage 3 writes
# results with it. Do not "tidy" the marker to match the comment.
#
# The prefix cannot be discovered: GET /projects returns project_name, project_key,
# url, estimate and status, and no case-code prefix. It is therefore configured.
# Unset means "show the API code", which is never wrong, only less convenient.
case_prefix="${QATOUCH_CASE_PREFIX:-}"

marker=$(marker_build "$issue" "$(qt_project)" "$module" "$module_key" \
           "claude" "$cases_json" "$previous")
marker_json=$(sed 's/^<!-- qa-touch-cases //; s/ -->$//' <<<"$marker")
all_codes=$(jq -r --arg p "$case_prefix" '
  [.cases[].code
   | if $p == "" then . else ($p + "-" + (. | gsub("[^0-9]"; ""))) end]
  | join(", ")' <<<"$marker_json")
all_count=$(jq '.cases | length' <<<"$marker_json")

heading=$([[ -n "$previous" ]] && printf 'Additional test cases for review' || printf 'Test cases for review')

{
  printf '## %s\n\n' "$heading"
  printf '**%d case(s)** authored for this %s and created in QA Touch module **%s** (project `%s`).\n\n' \
    "$created" "$kind" "$module" "$(qt_project)"

  [[ -n "${module_was_created:-}" ]] && printf '> [!NOTE]\n> **%s** did not exist, so it was created. `POST /module` takes no parent, so it landed at the **top level** of the module tree — drag it where it belongs in QA Touch if it should sit under an existing folder.\n\n' \
    "$module"

  [[ -n "${fallback_used:-}" ]] && printf '> [!NOTE]\n> These were meant for **%s**, but a folder of that name already exists in QA Touch and the API cannot write into it, so they went to **%s** instead. Move them in the UI if you want them under the original.\n\n' \
    "$fallback_used" "$module"

  [[ -n "$previous" ]] && printf 'These are **added to** the %d case(s) already on this issue; nothing existing was changed. The issue now covers %d case(s) in total.\n\n' \
    "$(jq '.cases | length' <<<"$previous")" "$all_count"

  [[ -n "$unopened" ]] && printf '> [!IMPORTANT]\n> **Some content on this issue could not be opened, so these cases do not cover it.**\n>\n%s\n>\n> Paste the relevant detail into the issue (or attach it as an image or PDF) and re-apply `%s` to add the missing cases.\n\n' \
    "$(sed 's/^/> /' <<<"$unopened")" "$TRIGGER_LABEL"

  (( unresolved > 0 )) && printf '> [!WARNING]\n> %d case(s) were created but their codes could not be resolved, so they are **not** in the marker and will not be executed. Check the module in QA Touch.\n\n' "$unresolved"

  printf '| Code | Discipline | Title | Steps |\n|---|---|---|---|\n'
  jq -r --argjson codes "$cases_json" --arg p "$case_prefix" '
    select([.case_key] | inside([$codes[].key]))
    | (.Description // "" | capture("^\\[(?<d>[a-z/-]+)\\]") .d? // "functional") as $discipline
    | (if $p == "" then .case_code else ($p + "-" + (.case_code | gsub("[^0-9]"; ""))) end) as $shown
    | "| `" + $shown + "` | " + $discipline + " | " + .case_title + " | " + ((.Steps // [] | length)|tostring) + " |"
  ' "${work}/resolved.jsonl"

  # Said once, under the table, rather than as a second column on every row.
  [[ -n "$case_prefix" ]] && printf '\n<sub>QA Touch'"'"'s API reports these same cases as `TR<number>`; the numbers are identical.</sub>\n'


  printf '\n'
  jq -r --argjson codes "$cases_json" --arg p "$case_prefix" '
    select([.case_key] | inside([$codes[].key]))
    | (if $p == "" then .case_code else ($p + "-" + (.case_code | gsub("[^0-9]"; ""))) end) as $shown
    | "<details><summary><code>" + $shown + "</code> " + .case_title + "</summary>\n",
      (if (.Precondition // "") != "" then "**Precondition:** " + .Precondition + "\n" else empty end),
      "| # | Step | Expected result |", "|---|---|---|",
      ( (.Steps // []) | to_entries[] |
        "| " + ((.key + 1)|tostring) + " | " + (.value.step_description // "") + " | " +
        (.value.expected_result // "") + " |" ),
      "\n</details>\n"
  ' "${work}/resolved.jsonl"

  printf '\n**Next:** review and approve these in QA Touch, then apply `%s`.\n\n' \
    "${QA_APPROVED_LABELS%%|*}"
  printf '%s\n' "$marker"
} > "${work}/comment.md"

gh_comment "$issue" "${work}/comment.md"

# The codes also go into the issue BODY. The comment carries the detail, but the
# body is what a person sees first and what stays visible once the thread grows —
# "which cases cover this issue" should not be a scrolling exercise. The block is
# delimited, so an amend replaces it instead of stacking a second list.
{
  printf '**QA Touch cases** (%d) — project `%s`, module **%s**\n\n' \
    "$all_count" "$(qt_project)" "$module"
  printf '%s\n' "$all_codes"
} > "${work}/ids-block.md"

if ! gh_issue_upsert_ids_block "$issue" "${work}/ids-block.md"; then
  # Not fatal: the marker and the comment are the load-bearing records, and the
  # body edit can fail on a token without issue-write scope. Say so rather than
  # failing a run whose real work already landed.
  printf 'stage1: could not update the issue body with the case ids (marker and comment are published)\n' >&2
fi

gh_add_label "$issue" "$DONE_LABEL"
[[ -n "$unopened" ]] && gh_add_label "$issue" "$NEEDS_INFO_LABEL"
gh_remove_label "$issue" "$PROGRESS_LABEL"
gh_remove_label "$issue" "$TRIGGER_LABEL"

printf 'stage1: done — issue #%s has %d case(s), label moved to "%s"\n' \
  "$issue" "$created" "$DONE_LABEL"
