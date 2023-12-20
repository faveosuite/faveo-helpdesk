#!/usr/bin/env bash
# QA Touch API client for the first-round QA pipeline.
#
# Source this, don't execute it:  . "$(dirname "$0")/qatouch-client.sh"
#
# Read ci/qa/qatouch-facts.md before changing anything here. Several
# functions look needlessly indirect and are not — they work around verified
# API behaviour (page-1-only search, codes absent from create responses,
# meta.last_page lying).
#
# Required env:
#   QATOUCH_DOMAIN     bare subdomain, e.g. "ladybird" (NOT a URL)
#   QATOUCH_API_TOKEN  from User -> Edit profile -> Generate API Key
# Optional env:
#   QATOUCH_PROJECT    default project key (default: MeLq)
#   QT_DEBUG           set to 1 to log every request to stderr

set -euo pipefail

QT_PUBLIC_BASE="https://api.qatouch.com/api/v1"
QT_PAGE_SIZE=20          # verified: fixed at 20 on getAllTestCases
QT_MAX_RETRIES=3

# ---------------------------------------------------------------------------
# preflight
# ---------------------------------------------------------------------------

qt_require_env() {
  local missing=()
  [[ -n "${QATOUCH_DOMAIN:-}" ]]    || missing+=(QATOUCH_DOMAIN)
  [[ -n "${QATOUCH_API_TOKEN:-}" ]] || missing+=(QATOUCH_API_TOKEN)

  if (( ${#missing[@]} )); then
    printf 'qatouch: missing required env: %s\n' "${missing[*]}" >&2
    return 1
  fi

  # A URL here is the single most common misconfiguration: the API wants the
  # bare subdomain in a header, and a URL fails with an opaque 401.
  if [[ "$QATOUCH_DOMAIN" == *"://"* || "$QATOUCH_DOMAIN" == *"."* ]]; then
    printf 'qatouch: QATOUCH_DOMAIN must be the bare subdomain (e.g. "ladybird"), got "%s"\n' \
      "$QATOUCH_DOMAIN" >&2
    return 1
  fi

  command -v jq >/dev/null || { printf 'qatouch: jq is required\n' >&2; return 1; }
}

qt_project() { printf '%s' "${QATOUCH_PROJECT:-MeLq}"; }

# ---------------------------------------------------------------------------
# transport
# ---------------------------------------------------------------------------

# qt_request <method> <url> [curl args...]
# Retries on 429 and 5xx only. A 4xx is a bug in our request, not a blip —
# retrying it just multiplies the same mistake.
qt_request() {
  local method="$1" url="$2"; shift 2
  local attempt=1 body http

  while :; do
    body=$(mktemp)
    http=$(curl -sS -o "$body" -w '%{http_code}' \
             -X "$method" "$url" \
             -H "domain: ${QATOUCH_DOMAIN}" \
             -H "api-token: ${QATOUCH_API_TOKEN}" \
             -H 'Accept: application/json' \
             "$@" || printf '000')

    [[ "${QT_DEBUG:-}" == "1" ]] && printf 'qatouch: %s %s -> %s\n' "$method" "${url%%\?*}" "$http" >&2

    if [[ "$http" =~ ^2 ]]; then
      cat "$body"; rm -f "$body"; return 0
    fi

    # A timed-out POST is NOT safe to repeat. POST /testRun creates the run and
    # then never answers: three retries of one create left three duplicate runs in
    # MeLq, each holding thousands of cases, and nothing in this API can delete
    # them. Retry timeouts for reads only; for a write, report and stop.
    if [[ "$http" == "000" && "$method" != "GET" ]]; then
      printf 'qatouch: %s %s timed out. NOT retried — a write that timed out may have succeeded server-side.\n' \
        "$method" "${url%%\?*}" >&2
      rm -f "$body"
      return 1
    fi

    if [[ "$http" == "429" || "$http" =~ ^5 || "$http" == "000" ]] && (( attempt < QT_MAX_RETRIES )); then
      rm -f "$body"
      sleep $(( attempt * 2 ))
      attempt=$(( attempt + 1 ))
      continue
    fi

    printf 'qatouch: %s %s failed with HTTP %s\n' "$method" "${url%%\?*}" "$http" >&2
    sed -n '1,20p' "$body" >&2
    rm -f "$body"
    return 1
  done
}

# The public API takes POST parameters in the QUERY STRING, not a body —
# verified in both the MCP server source and QA Touch's own API demo.
qt_public_get() { qt_request GET "${QT_PUBLIC_BASE}/$1"; }

# qt_post_params <path> <key=value>...  (query-string POST, values URL-encoded)
# -G puts the data in the query string; -X POST keeps the method.
qt_post_params() {
  local path="$1"; shift
  local args=()
  for kv in "$@"; do args+=(--data-urlencode "$kv"); done
  qt_request POST "${QT_PUBLIC_BASE}/${path}" -G "${args[@]}"
}

# ---------------------------------------------------------------------------
# reads
# ---------------------------------------------------------------------------

qt_cases_page() { qt_public_get "getAllTestCases/$(qt_project)?page=${1:-1}"; }

# The user key POST /testRun demands as assignTo.
#
# Round 1 on PR #16113 wrote no statuses at all for want of this: the create call
# answers "The parameter assignTo is missing", and any guessed value answers
# "User Key not Found For Project!". The endpoint that returns real keys is
# testRun/availableUsers/<projectKey>. It is not discoverable by guessing —
# getAllUsers, getUsers, projectUsers and getAllMembers all 404, which is what
# led that round to conclude no such endpoint exists. Verified against MeLq:
# HTTP 200 {"success":true,"data":[{"user_key":"0Dmw","name":"admin ladybird",
# "email":"admin@faveohelpdesk.com"}]}.
qt_available_users() { qt_public_get "testRun/availableUsers/$(qt_project)"; }

# qt_case_keys_for_codes TR8977,TR8978,... -> M7kRkX,DrqGqP,...
#
# A case has TWO identifiers and they are not interchangeable: `case_code`
# (TR8977) is what the results API writes against and what the Stage 1 marker
# carries, while `case_key` (M7kRkX) is what a run is built from. Round 1 on
# PR #16113 handed the codes to POST /testRun as caseKeys, which can never match
# anything — so even had assignTo been supplied, the run would have come back
# holding no cases.
#
# One pass over the newest pages for the whole set rather than qt_case_by_code
# per code: that pages up to 15 times each, and ten codes would page 150 times
# for data one sweep already has. Tokens that do not look like a code (a letter
# run then a digit run, TR8977) are assumed to be keys and passed through.
qt_case_keys_for_codes() {
  local wanted="$1" last page budget found=""
  [[ -n "$wanted" ]] || return 0

  local codes=() passthrough=() tok
  IFS=',' read -r -a _toks <<<"$wanted"
  for tok in "${_toks[@]}"; do
    [[ -z "$tok" ]] && continue
    if [[ "$tok" =~ ^[A-Za-z]+[0-9]+$ ]]; then codes+=("$tok"); else passthrough+=("$tok"); fi
  done

  if (( ${#codes[@]} == 0 )); then
    printf '%s' "$wanted"
    return 0
  fi

  last=$(qt_cases_last_page) || return 1
  budget="${QT_CASE_SCAN_PAGES:-15}"
  local filter
  filter=$(printf '%s\n' "${codes[@]}" | jq -R . | jq -sc .)

  for (( page = last; page > 0 && budget > 0; page--, budget-- )); do
    found+=$(qt_cases_page "$page" \
             | jq -r --argjson want "$filter" \
                 '.data[]? | select(.case_code as $c | $want | index($c)) | .case_code + "=" + .case_key')
    found+=$'\n'
    # Every wanted code accounted for: stop paging.
    if (( $(grep -c '=' <<<"$found") >= ${#codes[@]} )); then break; fi
  done

  local out=() c key
  for c in "${codes[@]}"; do
    key=$(awk -F= -v c="$c" '$1 == c { print $2; exit }' <<<"$found")
    if [[ -n "$key" ]]; then
      out+=("$key")
    else
      printf 'qatouch: no case_key found for %s — it is left out of the run\n' "$c" >&2
    fi
  done
  out+=("${passthrough[@]+"${passthrough[@]}"}")

  (IFS=','; printf '%s' "${out[*]}")
}

# The key to assign a new run to: QATOUCH_ASSIGN_TO when set, else the project's
# first available user. Deliberately not cached — a cache written inside a command
# substitution dies with its subshell, and this is called once per run creation.
# Milestones, which the API calls milestones and the MCP server calls releases.
qt_milestones() { qt_public_get "getAllMilestones/$(qt_project)?page=${1:-1}"; }

# A milestone name reduced to "<version>|<qualifier>", so two naming conventions can
# be compared: GitHub carries "Helpdesk v9.4.3.8.RC.1" and QA Touch carries
# "Release 9.4.3.8 RC1", and neither is a substring of the other.
#
#   Helpdesk v9.4.3.8.RC.1        -> 9.4.3.8|rc1
#   Release 9.4.1.2               -> 9.4.1.2|
#   Helpdesk v9.4.1.2             -> 9.4.1.2|
#   Release 9.2.1 RC1             -> 9.2.1|rc1
#   Release 9.2.1 Official        -> 9.2.1|       (an unqualified version, so equal
#                                                  to GitHub's "Helpdesk v9.2.1")
#   Faveo Helpdesk release 7.1.6  -> 7.1.6|
#   Relaase 9.1.0 RC              -> 9.1.0|rc1     (their typo, and a bare RC)
#
# Product words are noise and dropped: helpdesk, faveo, release, the misspelling
# already in the data, build, a "v" glued to a version — and "official", because an
# unqualified version IS the official release. That is what lets GitHub's
# "Helpdesk v9.2.1" meet QA Touch's "Release 9.2.1 Official" while staying distinct
# from "Release 9.2.1 RC1". A project holding both "Release 9.2.1" and "Release 9.2.1
# Official" would make them collide, and a collision is reported, not guessed. What is left splits at
# the first non-numeric token: leading numbers are the version, the rest is the
# qualifier. A bare "rc" is read as "rc1" — the data holds both spellings for what is
# plainly the same thing, and no project numbers a second candidate without saying so.
#
# The qualifier is part of the identity, never dropped: "9.4.1.2|rc1" must NOT match
# "9.4.1.2|", because QA Touch keeps release candidates and official releases apart
# on purpose ("Release 9.2.1 RC1" and "Release 9.2.1 Official" are both in MeLq).
qt_canon_milestone() {
  printf '%s' "$1" | awk '
    {
      s = tolower($0)
      gsub(/[^a-z0-9]+/, " ", s)
      n = split(s, t, " ")
      ver = ""; qual = ""
      for (i = 1; i <= n; i++) {
        tok = t[i]
        if (tok ~ /^v[0-9]/) { sub(/^v/, "", tok) }
        if (tok == "" || tok == "v" || tok == "helpdesk" || tok == "faveo" \
            || tok == "release" || tok == "relaase" || tok == "build" \
            || tok == "official") { continue }
        if (qual == "" && tok ~ /^[0-9]+$/) { ver = (ver == "" ? tok : ver "." tok) }
        else { qual = qual tok }
      }
      if (qual == "rc") { qual = "rc1" }
      print ver "|" qual
    }'
}

# Every milestone as a flat JSON array. Paged, because the default page size holds
# 10 and MeLq has 16 — matching only page 1 would miss the newest releases, which
# are exactly the ones a live PR is filed under.
qt_milestones_all() {
  local out="[]" body total per pages page rows

  body=$(qt_milestones 1) || return 1
  out=$(jq -c '.data // []' <<<"$body")

  # meta.last_page is unusable here for the same reason as on the case list: it
  # reports "1" on page 1 and "2" on page 2. total and per_page are honest, so the
  # page count is arithmetic. Measured: 16 milestones, per_page 10, last_page "1" —
  # trusting it fetched ten and lost every recent release, which is precisely the
  # half a live PR is filed under.
  total=$(jq -r '.meta.total // 0' <<<"$body")
  per=$(jq -r '.meta.per_page // 10' <<<"$body")
  (( per > 0 )) || per=10
  pages=$(( (total + per - 1) / per ))

  for (( page = 2; page <= pages; page++ )); do
    body=$(qt_milestones "$page") || break
    rows=$(jq -r '(.data // []) | length' <<<"$body")
    (( rows == 0 )) && break
    out=$(jq -c --argjson acc "$out" '$acc + (.data // [])' <<<"$body")
  done

  printf '%s' "$out"
}

# POST /testRun also demands milestoneKey. The API names its required parameters
# one at a time — supply assignTo and it asks for this next — so the full set is
# projectKey, testRun, assignTo, milestoneKey.
#
# There is deliberately NO fallback. Every milestone in MeLq is a product release
# ("Release 9.4.1.2", "Faveo Helpdesk release 7.1.6"), and quietly filing an
# automated round under whichever one the API happens to list first would put AI
# results into a human release's QA view. Better to fail with the list in the log
# and have someone choose once.
qt_default_milestone() {
  if [[ -n "${QATOUCH_MILESTONE_KEY:-}" ]]; then
    printf '%s' "$QATOUCH_MILESTONE_KEY"
    return 0
  fi

  # The LINKED ISSUE's GitHub milestone, passed in as QA_ISSUE_MILESTONE by the
  # pipeline — the issue, because that is where the release is decided and where the
  # approved cases live; a PR carries at most a copy of that and often nothing.
  #
  # Two passes: the name as both sides spell it, then the canonical form from
  # qt_canon_milestone. Substring matching used to be the second pass and it silently
  # mis-filed release candidates — an issue on "Release 9.4.1.2.RC.1" matched
  # "Release 9.4.1.2", the OFFICIAL release, because the issue's name contains the
  # milestone's. Comparing versions and qualifiers separately makes that impossible,
  # and it is the only way "Helpdesk v9.4.3.8.RC.1" can ever meet "Release 9.4.3.8 RC1".
  #
  # Several matches are reported with their canonical forms, never guessed. So is no
  # match: an RC that does not exist in QA Touch yet is a question for a person, and
  # the answer is one click — create it, or set QATOUCH_MILESTONE_KEY.
  if [[ -n "${QA_ISSUE_MILESTONE:-}" ]]; then
    local all key want canon
    all=$(qt_milestones_all)

    key=$(jq -r --arg n "$QA_ISSUE_MILESTONE" \
            'first(.[] | select(.milestone_name == $n) | .milestone_key) // ""' <<<"$all")

    want=$(qt_canon_milestone "$QA_ISSUE_MILESTONE")
    # A name with no version number canonicalises to "|something" and would match on
    # the qualifier alone. Nothing good comes of that.
    if [[ -z "$key" && "${want%%|*}" != "" ]]; then
      local hits=() k n c
      while IFS=$'\t' read -r k n; do
        [[ -n "$k" ]] || continue
        c=$(qt_canon_milestone "$n")
        [[ "$c" == "$want" ]] && hits+=("${k}"$'\t'"${n}")
      done < <(jq -r '.[] | .milestone_key + "\t" + .milestone_name' <<<"$all")

      if (( ${#hits[@]} == 1 )); then
        key="${hits[0]%%$'\t'*}"
        printf 'qatouch: milestone "%s" (%s) matched %s\n' \
          "$QA_ISSUE_MILESTONE" "$want" "${hits[0]}" >&2
      elif (( ${#hits[@]} > 1 )); then
        printf 'qatouch: milestone "%s" (%s) matches %s QA Touch milestones — set QATOUCH_MILESTONE_KEY to choose:\n' \
          "$QA_ISSUE_MILESTONE" "$want" "${#hits[@]}" >&2
        printf '  %s\n' "${hits[@]}" >&2
        return 1
      fi
    fi

    if [[ -n "$key" ]]; then
      printf '%s' "$key"
      return 0
    fi

    printf 'qatouch: the issue milestone "%s" reads as %s and no QA Touch milestone matches — create it there, or set QATOUCH_MILESTONE_KEY. Milestones in %s, with how they read:\n' \
      "$QA_ISSUE_MILESTONE" "$want" "$(qt_project)" >&2
    while IFS=$'\t' read -r k n; do
      [[ -n "$k" ]] || continue
      printf '  %-6s %-32s %s\n' "$k" "$n" "$(qt_canon_milestone "$n")" >&2
    done < <(jq -r '.[] | .milestone_key + "\t" + .milestone_name' <<<"$all")
    return 1
  fi

  printf 'qatouch: neither QA_ISSUE_MILESTONE nor QATOUCH_MILESTONE_KEY is set, and POST /testRun requires a milestone. Milestones in %s:\n' \
    "$(qt_project)" >&2
  qt_milestones_all | jq -r '.[] | "  \(.milestone_key)\t\(.milestone_name)"' >&2 || true
  return 1
}

qt_default_assignee() {
  if [[ -n "${QATOUCH_ASSIGN_TO:-}" ]]; then
    printf '%s' "$QATOUCH_ASSIGN_TO"
    return 0
  fi
  local key
  key=$(qt_available_users | jq -r '.data[0].user_key // ""') || return 1
  if [[ -z "$key" ]]; then
    printf 'qatouch: project %s lists no available users, so a run cannot be created — set QATOUCH_ASSIGN_TO\n' \
      "$(qt_project)" >&2
    return 1
  fi
  printf '%s' "$key"
}

# Total case count — the anchor for code resolution. Must come from meta.total;
# meta.last_page reports "1" and cannot be used.
#
# A project with NO cases returns {"msg":"Test case(s) not found for the Project"}
# with no meta and no link at all — verified against an empty project. Without the
# // 0 fallback this returned empty, and the arithmetic in
# qt_resolve_new_case_codes then died under set -e. That made the very first run in
# any fresh project fail, which is exactly the run you least want to fail.
qt_cases_total() { qt_cases_page 1 | jq -r '.meta.total // 0'; }

# Defaults to 1 for the same reason: an empty project has no link object.
qt_cases_last_page() {
  local page
  page=$(qt_cases_page 1 | jq -r '.link.last // ""' | sed -n 's/.*[?&]page=\([0-9]\+\).*/\1/p')
  printf '%s' "${page:-1}"
}

qt_run_results() { qt_public_get "testRunResults/$(qt_project)/$1?page=${2:-1}"; }

# Full module list. Deliberately pages instead of calling searchModules —
# that endpoint only ever looks at page 1 (see qatouch-facts.md trap 1).
qt_modules_all() {
  local page=1 out
  while :; do
    out=$(qt_public_get "getAllModules/$(qt_project)?page=${page}")
    printf '%s' "$out" | jq -c '.data[]?'
    [[ "$(printf '%s' "$out" | jq -r '.link.next')" == "null" ]] && break
    page=$(( page + 1 ))
  done
}

# qt_module_key_by_name <name> -> module key, empty if absent
#
# getAllModules answers with section_name/section_key, NOT module_name/module_key:
# the read API calls a module a "section", the same word POST /testCase/steps uses
# for its sectionKey parameter. This filtered on .module_name for its whole life,
# so it matched NOTHING and every lookup returned empty — an existing module was
# never reused, and the "it already exists" recovery in qt_create_module could
# never find anything either, which is what turned a name collision into a hard
# failure. Both spellings are accepted so that a rename either way is survivable.
qt_module_key_by_name() {
  local name="$1" rows key
  rows=$(qt_modules_all)

  # Exact first, so that if both "Form builder" and "Form Builder" somehow exist the
  # requested one still wins.
  key=$(jq -r --arg n "$name" '
          select(((.section_name // .module_name) // "") == $n)
          | (.section_key // .module_key // empty)' <<<"$rows" | head -1)
  [[ -n "$key" ]] && { printf '%s' "$key"; return 0; }

  # Then case- and whitespace-insensitively, which is the CORRECT semantics rather
  # than a convenience: QA Touch's own uniqueness check ignores case. Observed on
  # MeLq — the library holds "Form builder" (key v1qgp, page 4 of 17), an exact
  # match on "Form Builder" found nothing, and POST /module then refused to create
  # it as a duplicate. Case-sensitive here meant a module could be neither found nor
  # created, which read as "the folder is unreachable" when it was simply the same
  # folder spelled differently. Trailing spaces are stripped for the same reason:
  # names like "Create Package " and "Faveo Helpdesk Community " are real.
  key=$(jq -r --arg n "$name" '
          ($n | ascii_downcase | gsub("^\\s+|\\s+$"; "")) as $want
          | select((((.section_name // .module_name) // "")
                    | ascii_downcase | gsub("^\\s+|\\s+$"; "")) == $want)
          | (.section_key // .module_key // empty)' <<<"$rows" | head -1)
  [[ -n "$key" ]] || return 0
  printf '%s' "$key"
}

# qt_module_key_from_cases <name> -> module key, empty if no case is in that module
#
# The second route to a module key, and the one that saves a run when the first
# fails. getAllModules does not return every folder the UI has — a name can be
# refused by POST /module as taken and still be missing from all 17 pages of the
# listing. But getAllTestCases rows carry module_name AND module_key for the case's
# own module (verified against live rows), so any single case sitting in that folder
# reveals the key the module listing withheld.
#
# Expensive on purpose-of-last-resort: page size is fixed at 20, so a project with
# 8,200 cases is 400+ requests of full case bodies. Only call this when a module
# could neither be found nor created. It stops at the first match.
#
# Blind spot worth knowing: a folder containing no cases of its own — a parent whose
# cases all live in children — cannot be found this way either, because nothing
# references it. That case is genuinely unreachable through this API.
qt_module_key_from_cases() {
  local name="$1" page=1 out key last
  last=$(qt_cases_last_page)
  local cap="${QA_MODULE_SCAN_MAX_PAGES:-600}"
  (( last > cap )) && last="$cap"

  printf 'qatouch: scanning up to %s page(s) of test cases for a case in module "%s"\n' \
    "$last" "$name" >&2

  while (( page <= last )); do
    out=$(qt_cases_page "$page") || return 1
    key=$(printf '%s' "$out" \
          | jq -r --arg n "$name" '
              .data[]? | select((.module_name // "") == $n) | (.module_key // empty)' \
          | head -1)
    if [[ -n "$key" ]]; then
      printf 'qatouch: module "%s" -> %s (found via a test case on page %d)\n' \
        "$name" "$key" "$page" >&2
      printf '%s' "$key"
      return 0
    fi
    (( page % 50 == 0 )) && printf 'qatouch: ...scanned %d/%s pages\n' "$page" "$last" >&2
    page=$(( page + 1 ))
  done

  printf 'qatouch: no test case anywhere in the project belongs to a module named "%s"\n' \
    "$name" >&2
  return 1
}

# qt_module_candidates <name> [limit] -> module names that share a word with <name>
#
# For the failure message when a module cannot be resolved OR created. QA Touch's
# UI has folders that getAllModules does not return — a parent folder, or one the
# listing simply omits — and POST /module then refuses the name as taken while the
# lookup cannot find it. There is no API route to that folder's key: getAllModules
# takes nothing but a page number.
#
# So the only useful thing left is to help the human choose a module that IS
# addressable. Matching is by shared word, case-insensitively, because "Form
# Builder" should surface "Form Builder Settings" and "Custom Forms".
qt_module_candidates() {
  local name="$1" limit="${2:-8}"
  qt_modules_all \
    | jq -r '(.section_name // .module_name // empty)' \
    | awk -v want="$name" -v lim="$limit" '
        BEGIN { n = split(tolower(want), w, /[^a-z0-9]+/) }
        {
          line = tolower($0); score = 0
          for (i = 1; i <= n; i++) if (length(w[i]) > 2 && index(line, w[i])) score++
          if (score > 0) printf "%d\t%s\n", score, $0
        }' \
    | sort -rn -k1,1 | head -n "$limit" | cut -f2-
}

# ---------------------------------------------------------------------------
# writes
# ---------------------------------------------------------------------------

# qt_create_module <name> -> module key on stdout
#
# POST /module takes its parameters in the query string like every other write
# here, and answers HTTP 200. What it puts *in* that 200 is the part to be careful
# about: QA Touch reports a refused write as 200 with success:false, so a create
# that did nothing is indistinguishable from one that worked unless the body is
# read. This function used to pull .data.moduleKey and discard the rest, which is
# why a refusal surfaced on the issue as the uninformative "the create call
# returned no module key" — with the API's own explanation thrown away.
#
# So: keep the body, name the reason when the API gives one, and print the whole
# response when no key can be found in it. The next failure then explains itself
# instead of needing an investigation.
#
# Key extraction accepts the section_* spelling as well. The read API uses it
# (see qt_module_key_by_name), and this response body has never been observed on
# a run that passed, so the moduleKey spelling is an assumption, not a fact.
#
# The retry loop stays: a missing key is often "it already exists". getAllModules
# is eventually consistent, so a module created seconds ago can still be absent
# from the listing, the caller decides to create it, and the create declines.
qt_create_module() {
  local name="$1" resp key ok msg attempt

  resp=$(qt_post_params "module" "projectKey=$(qt_project)" "moduleName=${name}") || {
    printf 'qatouch: POST /module for "%s" failed at the transport level\n' "$name" >&2
    return 1
  }

  key=$(jq -r '(.data.moduleKey // .data.sectionKey // .data.module_key
                // .data.section_key // .moduleKey // .sectionKey // empty)' \
        <<<"$resp" 2>/dev/null || true)
  if [[ -n "$key" ]]; then
    printf '%s' "$key"
    return 0
  fi

  # NOT `.success // "absent"`: jq's alternative operator treats false as absent,
  # so the one value being tested for would have been reported as "no field".
  ok=$(jq -r 'if has("success") then (.success | tostring) else "absent" end' \
       <<<"$resp" 2>/dev/null || printf 'absent')
  msg=$(jq -r '(.msg // .message // .errors // empty)
               | if type == "string" then . else tojson end' <<<"$resp" 2>/dev/null || true)
  if [[ "$ok" == "false" ]]; then
    printf 'qatouch: POST /module refused "%s" — success:false%s\n' \
      "$name" "${msg:+, $msg}" >&2
  else
    printf 'qatouch: POST /module returned no recognisable module key for "%s". Full response:\n%s\n' \
      "$name" "$resp" >&2
  fi

  for attempt in 1 2 3; do
    sleep $(( attempt * 2 ))
    key=$(qt_module_key_by_name "$name" || true)
    if [[ -n "$key" ]]; then
      printf 'qatouch: module "%s" already existed -> %s (found on lookup %d)\n' \
        "$name" "$key" "$attempt" >&2
      printf '%s' "$key"
      return 0
    fi
  done

  # Reached only when the create produced no key AND the name is genuinely not in
  # the listing. Report both halves; which one is the real story depends on the
  # message above, and guessing here would put a wrong reason on the issue.
  printf 'qatouch: "%s" is still absent from the module list after three lookups\n' \
    "$name" >&2
  return 1
}

# qt_create_case <section_key> <case-json>
#
# case-json: {caseTitle, description, precondition, reference, estimate,
#             steps:[{step, expectedResult}, …]}
#
# The API wants steps as an index-keyed object under a different field naming
# than the input uses — {"0":{"steps":"…","expected_result":"…"}} — so the
# translation happens here rather than in every caller.
#
# Enforces the two rules the API cares about and that QA Touch's own AI output
# has been seen to violate: at least 4 steps, and no empty expected result.
qt_create_case() {
  local section="$1" case_json="$2" steps_template step_count empty_expected

  step_count=$(printf '%s' "$case_json" | jq '.steps | length')
  if (( step_count < 4 )); then
    printf 'qatouch: "%s" has %d steps, minimum is 4 — skipping\n' \
      "$(printf '%s' "$case_json" | jq -r '.caseTitle')" "$step_count" >&2
    return 1
  fi

  empty_expected=$(printf '%s' "$case_json" | jq '[.steps[] | select((.expectedResult // "") == "")] | length')
  if (( empty_expected > 0 )); then
    printf 'qatouch: "%s" has %d step(s) with an empty expected result — skipping\n' \
      "$(printf '%s' "$case_json" | jq -r '.caseTitle')" "$empty_expected" >&2
    return 1
  fi

  steps_template=$(printf '%s' "$case_json" | jq -c '
    [.steps[] | {steps: .step, expected_result: .expectedResult}]
    | to_entries | map({key: (.key|tostring), value: .value}) | from_entries
  ')

  qt_post_params "testCase/steps" \
    "projectKey=$(qt_project)" \
    "sectionKey=${section}" \
    "caseTitle=$(printf '%s' "$case_json" | jq -r '.caseTitle')" \
    "description=$(printf '%s' "$case_json" | jq -r '.description // ""')" \
    "precondition=$(printf '%s' "$case_json" | jq -r '.precondition // ""')" \
    "reference=$(printf '%s' "$case_json" | jq -r '.reference // ""')" \
    "estimate=$(printf '%s' "$case_json" | jq -r '.estimate // ""')" \
    "steps_template=${steps_template}"
}

# qt_resolve_new_case_codes <total_before> <expected_count>
#
# Creating a case does not return its code. Cases are ordered by created_date
# ascending, so N new cases occupy 0-indexed positions [T, T+N) and land on the
# final pages. Fetch only those pages.
#
# Emits one JSON object per line: {case_code, case_key, case_title, References}
qt_resolve_new_case_codes() {
  local before="$1" expected="$2"
  local total_now first_page last_page grew

  total_now=$(qt_cases_total)
  grew=$(( total_now - before ))

  if (( grew != expected )); then
    printf 'qatouch: expected %d new cases but total grew by %d — concurrent creation likely; widening scan\n' \
      "$expected" "$grew" >&2
  fi

  first_page=$(( before / QT_PAGE_SIZE + 1 ))
  last_page=$(qt_cases_last_page)
  [[ -z "$last_page" ]] && last_page=$first_page

  # The whole row, not a projection. The publish step renders the steps table,
  # the precondition and the discipline tag out of Description — all of which a
  # {case_code, case_key, case_title} projection silently dropped, leaving every
  # published case with an empty step table and no way to tell why.
  local page
  for (( page = first_page; page <= last_page; page++ )); do
    qt_cases_page "$page" | jq -c '.data[]? | . + {references: .References}'
  done
}

# qt_case_by_code <TR####> -> the full case object (including Steps), or nothing
#
# There is no fetch-one-case endpoint, and search only sees page 1, so this scans
# backwards from the newest page. Cases written by Stage 1 are recent, so the hit
# is normally on the first page read.
#
# The scan is BOUNDED (QT_CASE_SCAN_PAGES, default 15 pages = 300 cases). An
# unbounded scan would silently walk 411 pages on a miss and look like a hang;
# the caller is expected to record a miss as Blocked rather than wait.
qt_case_by_code() {
  local code="$1" last page hit
  local budget="${QT_CASE_SCAN_PAGES:-15}"

  last=$(qt_cases_last_page)
  [[ -n "$last" ]] || return 1

  for (( page = last; page > 0 && budget > 0; page--, budget-- )); do
    hit=$(qt_cases_page "$page" | jq -c --arg c "$code" '.data[]? | select(.case_code == $c)')
    if [[ -n "$hit" ]]; then
      printf '%s' "$hit"
      return 0
    fi
  done

  printf 'qatouch: case %s not found in the newest %s pages — treat as Blocked, do not guess its steps\n' \
    "$code" "${QT_CASE_SCAN_PAGES:-15}" >&2
  return 1
}

# ---------------------------------------------------------------------------
# test runs
# ---------------------------------------------------------------------------
# The public v1 API has NO documented create-run endpoint, and every one of the
# 27 runs in MeLq is Type="specific" — created in the UI with cases explicitly
# selected. So run resolution is a chain, not a single call, and the pipeline
# must survive the case where no run can be obtained at all: the report on the
# PR is the primary output, QA Touch statuses are the secondary one.

qt_test_runs_page() { qt_public_get "getAllTestRuns/$(qt_project)?page=${1:-1}"; }

# qt_run_key_by_name <name> -> testrun_key, exact match, newest wins
# Pages the whole list: there is no server-side run search, and the runs list is
# short (27 in MeLq) so paging it costs two calls.
qt_run_key_by_name() {
  local want="$1" page=1 last hit
  last=$(qt_test_runs_page 1 | jq -r '.link.last // ""' | sed -n 's/.*[?&]page=\([0-9]\+\).*/\1/p')
  last="${last:-1}"
  for (( page = last; page > 0; page-- )); do
    hit=$(qt_test_runs_page "$page" | jq -r --arg n "$want" '.data[]? | select(.Name == $n) | .testrun_key' | tail -1)
    if [[ -n "$hit" ]]; then printf '%s' "$hit"; return 0; fi
  done
  return 1
}

# qt_create_test_run <name> [case-keys-csv] [description] [milestoneKey] [assignTo]
#
# STILL PARTLY UNPROVEN. Two separate unknowns, and they fail differently:
#
#   1. Whether POST /testRun creates a run at all. No wrapper, demo or Redoc page
#      exposes a create-run call, and every run in this project was made in the UI.
#   2. Whether it can be told WHICH cases the run holds. The MCP server's create
#      path sends only projectKey and testRun, and doc.qatouch.com is a JS-rendered
#      page that yields nothing to fetch, so the parameter name — if one exists —
#      is not discoverable from outside.
#
# A run created without cases is the dangerous outcome, not an error: results
# written into an empty run either fail or silently no-op, and a silent no-op is
# indistinguishable from a clean pass. That is why qt_assert_run_populated exists
# and why every caller must run it.
#
# So this sends the case list under several plausible spellings in ONE request.
# Unknown query parameters are normally ignored, and whichever name QA Touch
# actually honours takes effect; the rest are noise. The full response is logged so
# the first real run records the truth instead of leaving it a guess — the same
# tactic that turned the module-create failure from folklore into a one-line fix.
# If the enriched request is rejected outright, it retries with the minimal pair.
# DO NOT TURN THIS ON. Measured against MeLq on 2026-08-21, and the reason it is
# now a wall rather than a fallback:
#
# POST /testRun validates fast — a bad projectKey, assignTo or milestoneKey comes
# back in 0.7s — and then, with all three valid, NEVER RESPONDS. It does create the
# run: four calls made four runs. It also ignores the case list completely, in every
# parameter spelling tried, and instead attaches the WHOLE PROJECT — the four runs
# hold 8331, 8331, 7624 and 3692 cases, the count varying with how long each request
# ran before the client gave up. There is no delete endpoint in the public API (every
# DELETE and .../delete shape answers nginx 400), so each attempt is permanent.
#
# So the supported path is the only path: a run made in the QA Touch UI, its key
# passed as QA_TEST_RUN_KEY. QA_FORCE_RUN_CREATE=1 exists to re-measure this if the
# vendor ever fixes it, and for nothing else.
qt_create_test_run() {
  local name="$1" cases="${2:-}" description="${3:-}" milestone="${4:-}" assign="${5:-}"

  if [[ "${QA_FORCE_RUN_CREATE:-0}" != "1" ]]; then
    printf 'qatouch: POST /testRun is disabled — it never answers, and it creates a run holding the entire case library. Make the run in the UI and pass QA_TEST_RUN_KEY.\n' >&2
    return 1
  fi

  # assignTo is REQUIRED, so it belongs in the base set rather than the enriched
  # one: leave it out and the fallback retry at the bottom is rejected as well,
  # which is how a whole round ended with no run and no statuses.
  [[ -n "$assign" ]] || assign=$(qt_default_assignee) || return 1
  [[ -n "$milestone" ]] || milestone=$(qt_default_milestone) || return 1

  local base=("projectKey=$(qt_project)" "testRun=${name}" "assignTo=${assign}" "milestoneKey=${milestone}")
  local params=("${base[@]}") resp

  [[ -n "$description" ]] && params+=("description=${description}")
  # Codes in, keys out — see qt_case_keys_for_codes. Still four parameter names:
  # which one POST /testRun honours is undocumented, and sending the set costs
  # nothing while guessing wrong costs a whole round's statuses.
  if [[ -n "$cases" ]]; then
    local keys
    keys=$(qt_case_keys_for_codes "$cases") || keys="$cases"
    printf 'qatouch: run cases %s -> keys %s\n' "$cases" "$keys" >&2
    params+=("caseKeys=${keys}" "cases=${keys}" "caseKey=${keys}" "testCases=${keys}")
  fi

  if resp=$(qt_post_params "testRun" "${params[@]}"); then
    printf 'qatouch: POST /testRun response: %s\n' "$(head -c 400 <<<"$resp")" >&2
    printf '%s' "$resp"
    return 0
  fi

  printf 'qatouch: POST /testRun rejected the enriched parameter set; retrying with projectKey+testRun only\n' >&2
  resp=$(qt_post_params "testRun" "${base[@]}") || return 1
  printf 'qatouch: POST /testRun response: %s\n' "$(head -c 400 <<<"$resp")" >&2
  printf '%s' "$resp"
}

# qt_resolve_run <preferred-name> -> run key on stdout, or exit 1
#
# In order:
#   1. QA_TEST_RUN_KEY   — a run someone made in the UI for this pipeline. This
#      is the supported path: it is the only one guaranteed to hold cases.
#   2. a run already named <preferred-name>  — so a re-run of the same PR writes
#      into the run it created rather than accumulating one run per attempt.
#   3. create one, only if QA_ALLOW_RUN_CREATE=1. Off by default because an empty
#      run in a shared project is litter no API call can remove.
#
# Failure here is NOT fatal to the pipeline. It means "report on the PR, write no
# statuses" — see stage3-prompt.md.
# qt_resolve_run <preferred-name> [case-keys-csv] [description]
qt_resolve_run() {
  local name="$1" cases="${2:-}" description="${3:-}" key=""

  if [[ -n "${QA_TEST_RUN_KEY:-}" ]]; then
    printf '%s' "$QA_TEST_RUN_KEY"
    return 0
  fi

  if key=$(qt_run_key_by_name "$name" 2>/dev/null) && [[ -n "$key" ]]; then
    printf 'qatouch: reusing existing run "%s" -> %s\n' "$name" "$key" >&2
    printf '%s' "$key"
    return 0
  fi

  if [[ "${QA_ALLOW_RUN_CREATE:-0}" != "1" ]]; then
    printf 'qatouch: no run named "%s" and QA_ALLOW_RUN_CREATE is off — statuses will not be written\n' \
      "$name" >&2
    return 1
  fi

  local resp
  resp=$(qt_create_test_run "$name" "$cases" "$description") || return 1
  key=$(jq -r '.data.testRunKey // .data.testrun_key // .data.key // ""' <<<"$resp")
  [[ -n "$key" ]] || {
    printf 'qatouch: create run returned no key: %s\n' "$(head -c 200 <<<"$resp")" >&2
    return 1
  }
  printf '%s' "$key"
}

# Refuse to report on a run with no cases in it. An empty run silently accepts
# nothing and would otherwise be indistinguishable from a clean pass.
qt_assert_run_populated() {
  local run_key="$1" count
  count=$(qt_run_results "$run_key" | jq -r '.meta.total // 0')
  if [[ "$count" == "0" ]]; then
    printf 'qatouch: test run %s contains 0 cases — refusing to write results into an empty run\n' \
      "$run_key" >&2
    return 1
  fi
  printf '%s' "$count"
}

# ---------------------------------------------------------------------------
# result statuses
# ---------------------------------------------------------------------------
# TWO VOCABULARIES, and the wrong one is silently accepted by nothing:
#   GET /testRunStatus returns numeric status_key 1..8.
#   POST /testRunResults/testrun/code REJECTS those numbers —
#     {"success":false,"error_msg":"Provided Status is not Valid",
#      "available_status":["passed","untested","blocked","retest","failed",
#                          "not-applicable","in-progress","hold"]}
# Verified against the live API 2026-08-10. So the write path speaks slugs while
# the read path speaks integers. Callers use names; this maps them, and the
# writer falls back to the numeric id if a future API build flips back.

qt_status_slug() {
  case "$(tr '[:upper:]' '[:lower:]' <<<"$1")" in
    1|pass|passed)               printf 'passed' ;;
    2|untested)                  printf 'untested' ;;
    3|block|blocked)             printf 'blocked' ;;
    4|retest)                    printf 'retest' ;;
    5|fail|failed)               printf 'failed' ;;
    6|na|not-applicable)         printf 'not-applicable' ;;
    7|in-progress|inprogress)    printf 'in-progress' ;;
    8|hold)                      printf 'hold' ;;
    *) printf 'qatouch: unknown status "%s"\n' "$1" >&2; return 1 ;;
  esac
}

qt_status_id() {
  case "$(qt_status_slug "$1")" in
    passed) printf 1 ;; untested) printf 2 ;; blocked) printf 3 ;; retest) printf 4 ;;
    failed) printf 5 ;; not-applicable) printf 6 ;; in-progress) printf 7 ;; hold) printf 8 ;;
  esac
}

# qt_update_results_by_code <run_key> <comments> <code:status>...
#
# status may be a name ("passed") or an id (5) — both are normalised. The
# endpoint wants an index-keyed JSON object, not an array:
#   {"0":{"case":"TR0010","status":"failed"},"1":{...}}
# A malformed payload here can stamp statuses onto the wrong cases, so the
# payload is built with jq rather than string concatenation.
#
# Keep batches to ~45 rows: the results ride the query string.
qt_update_results_by_code() {
  local run_key="$1" comments="$2"; shift 2
  local pairs=() kv code status

  for kv in "$@"; do
    [[ -n "$kv" ]] || continue
    code="${kv%%:*}"
    status="${kv#*:}"
    pairs+=("${code}:$(qt_status_slug "$status")")
  done
  (( ${#pairs[@]} )) || { printf 'qatouch: no results to write\n' >&2; return 1; }

  local resp
  resp=$(qt_results_post "$run_key" "$comments" "$(qt_results_payload "${pairs[@]}")") || return 1

  # A slug rejection comes back 200 with success:false, so the HTTP status is not
  # enough to trust. Retry once with numeric ids rather than reporting a run that
  # never landed.
  if jq -e '.success == false' >/dev/null 2>&1 <<<"$resp"; then
    printf 'qatouch: slug statuses rejected (%s) — retrying with numeric ids\n' \
      "$(jq -r '.error_msg // "no message"' <<<"$resp")" >&2
    local numeric=()
    for kv in "${pairs[@]}"; do
      numeric+=("${kv%%:*}:$(qt_status_id "${kv#*:}")")
    done
    resp=$(qt_results_post "$run_key" "$comments" "$(qt_results_payload "${numeric[@]}")") || return 1
    jq -e '.success != false' >/dev/null 2>&1 <<<"$resp" || {
      printf 'qatouch: both status vocabularies rejected: %s\n' "$(head -c 300 <<<"$resp")" >&2
      printf '%s' "$resp"
      return 1
    }
  fi

  printf '%s' "$resp"
}

# Build the index-keyed result object. Values stay strings unless they are ids —
# the API accepts "5" and 5 alike, but not "passed" quoted differently.
qt_results_payload() {
  printf '%s\n' "$@" | jq -Rn '
    [inputs | select(length > 0) | split(":") | {case: .[0], status: .[1]}]
    | to_entries
    | map({key: (.key | tostring), value: .value})
    | from_entries
  '
}

qt_results_post() {
  qt_post_params "testRunResults/testrun/code" \
    "project=$(qt_project)" \
    "test_run=$1" \
    "result=$3" \
    "comments=$2"
}

# qt_set_case_approval <case_key> <approve|reject|reverify>
qt_set_case_approval() {
  qt_post_params "testcase/update/common" \
    "projectKey=$(qt_project)" "caseKey=$1" "approval=$2"
}
