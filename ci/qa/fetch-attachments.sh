#!/usr/bin/env bash
# Pull the issue's attachments down so the authoring agent can actually look at
# them, and record honestly what could not be opened.
#
#   ci/qa/fetch-attachments.sh <issue.json> <out-dir>
#
# Writes <out-dir>/manifest.json:
#   {"issue":N,"needs_more_info":true|false,"items":[
#     {"url":…,"kind":"image|pdf|text|office|video|external-doc|link",
#      "status":"readable|unreadable|unfetchable|skipped","path":…,"note":…}]}
#
# Why this exists: a screenshot IS the requirement on most bug reports, and a
# linked design doc IS the specification on most enhancements. Issue #13814's
# entire body is a Google Docs link — the agent wrote usable cases only because
# the feature already existed in the code to read. On a genuinely new feature it
# would have had nothing, and nothing is what it would have written.
#
# Three honest outcomes, and the distinction matters more than the download:
#   readable    fetched, and a model can look at it (image, PDF, plain text)
#   unreadable  fetched, but not usable here (.docx, .xlsx, .zip, video)
#   unfetchable behind a login (Google Docs, Drive, SharePoint) — never guessed at
#
# Anything unreadable or unfetchable sets needs_more_info, which the publish step
# turns into a label and a line on the issue: a person can then paste the content
# in. Silently authoring from a title while a specification sits unread in a link
# is the failure this prevents.

set -uo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
. "${here}/gh-client.sh"

# gh-client.sh sets `set -euo pipefail`, and sourcing it applies errexit HERE too.
# This script must not abort on a single failure: a grep that finds no URLs exits
# 1, and that alone killed the run on an issue with no links at all — silently, with
# no manifest written. Every fetch below handles its own failure.
set +e

issue_file="${1:?usage: fetch-attachments.sh <issue.json> <out-dir>}"
out_dir="${2:?usage: fetch-attachments.sh <issue.json> <out-dir>}"

MAX_BYTES="${QA_ATTACHMENT_MAX_BYTES:-26214400}"   # 25 MiB
PROBE_UA_WEB='faveo-qa-first-round/1.0 (+issue attachment reader)' 

mkdir -p "$out_dir"
manifest="${out_dir}/manifest.json"
items="${out_dir}/.items.jsonl"
: > "$items"

issue_number=$(jq -r '.number // 0' "$issue_file")

# Every URL in the body and the comments, deduplicated, trailing punctuation
# stripped — markdown puts links inside parentheses and sentences end in dots.
urls=$(jq -r '[.body, (.comments[]?.body)] | join("\n")' "$issue_file" \
       | grep -oE 'https?://[^][ )<>"'"'"'`]+' \
       | sed -E 's/[.,;:]+$//' \
       | sort -u || true)

record() {
  jq -cn --arg url "$1" --arg kind "$2" --arg status "$3" --arg path "${4:-}" --arg note "${5:-}" \
    '{url:$url, kind:$kind, status:$status, path:(if $path=="" then null else $path end), note:$note}' \
    >> "$items"
  printf '  %-11s %-12s %s\n' "$3" "$2" "${1:0:78}" >&2
}

# github_ref <url> — a linked issue or PR in this repo, read through the API.
github_ref() {
  local url="$1" num json out
  num="${url##*/}"; num="${num%%[!0-9]*}"
  [[ -n "$num" ]] || { record "$url" link skipped '' 'no issue number in the URL'; return; }

  n=$(( n + 1 ))
  out="${out_dir}/ref-${n}-issue-${num}.txt"

  json=$(gh_api GET "/repos/$(gh_repo)/issues/${num}" 2>/dev/null) || json=''
  if [[ -z "$json" ]] || ! jq -e '.title' >/dev/null 2>&1 <<<"$json"; then
    record "$url" link unfetchable '' "could not read issue #${num} (another repo, or no access)"
    return
  fi

  {
    jq -r '"# " + (.title // "") + "\n\nstate: " + (.state // "") + "\n\n" + (.body // "")' <<<"$json"
    printf '\n\n--- comments ---\n'
    gh_comments "$num" 2>/dev/null | jq -r '.[]? | "\n[" + (.user.login // "?") + "] " + (.body // "")'
  } > "$out" 2>/dev/null

  record "$url" text readable "$out" "linked issue #${num}, read through the API"
}

# web_page <url> — a public page, reduced to text.
#
# Crude tag-stripping on purpose: no pandoc, no lynx, no python dependency on the
# build node. It is enough to carry the prose of a documentation page, which is
# what these links normally are.
web_page() {
  local url="$1" out raw sniffed
  n=$(( n + 1 ))
  raw="${out_dir}/page-${n}.raw"

  curl -sS -L --max-time 45 --max-filesize "$MAX_BYTES" -A "$PROBE_UA_WEB" -o "$raw" "$url" >/dev/null 2>&1
  if [[ ! -s "$raw" ]]; then
    rm -f "$raw"
    record "$url" link unfetchable '' 'no response'
    return
  fi

  sniffed=$(file -b --mime-type "$raw" 2>/dev/null || printf 'application/octet-stream')

  case "$sniffed" in
    text/html|application/xhtml*)
      if grep -qiE 'accounts\.google\.com|ServiceLogin|<title>[^<]*(sign in|log in|login)' "$raw"; then
        rm -f "$raw"
        record "$url" link unfetchable '' 'answered with a sign-in page'
        return
      fi
      out="${out_dir}/page-${n}.txt"
      sed -e 's/<script[^>]*>.*<\/script>//gI' -e 's/<style[^>]*>.*<\/style>//gI' "$raw" \
        | tr '\n' ' ' | sed -e 's/<[^>]*>/ /g' -e 's/&nbsp;/ /g' -e 's/&amp;/\&/g' \
        | tr -s ' ' | fold -s -w 100 | head -c 200000 > "$out"
      rm -f "$raw"
      if [[ "$(wc -c < "$out")" -lt 200 ]]; then
        record "$url" link unreadable "$out" 'page carried almost no text (rendered by JavaScript?)'
      else
        record "$url" text readable "$out" 'public page, reduced to text'
      fi ;;
    application/pdf) mv -f "$raw" "${out_dir}/page-${n}.pdf"; record "$url" pdf readable "${out_dir}/page-${n}.pdf" 'public PDF' ;;
    text/*)          mv -f "$raw" "${out_dir}/page-${n}.txt"; record "$url" text readable "${out_dir}/page-${n}.txt" "$sniffed" ;;
    video/*)         record "$url" video unreadable "$raw" "$sniffed — nobody here can watch it" ;;
    *)               record "$url" link unreadable "$raw" "$sniffed" ;;
  esac
}

# google_export <url> — try the document's export endpoint, accept only real content.
#
# Three ways this is reached and only the first two produce anything:
#   * the doc is shared "anyone with the link" -> export returns text/csv/pdf
#   * QA_GOOGLE_ACCESS_TOKEN is set -> the Drive API exports it as that identity.
#     Mint it however you like (gcloud auth print-access-token, a service account
#     shared into the Drive) and pass it in; nothing here holds a Google credential.
#   * neither -> Google answers with a sign-in page, which is recorded as
#     unfetchable rather than passed off as the document.
google_export() {
  local url="$1" id kind_hint export_url out sniffed

  case "$url" in
    *docs.google.com/document/d/*)      id="${url#*document/d/}";     id="${id%%/*}"; kind_hint=doc ;;
    *docs.google.com/spreadsheets/d/*)  id="${url#*spreadsheets/d/}"; id="${id%%/*}"; kind_hint=sheet ;;
    *docs.google.com/presentation/d/*)  id="${url#*presentation/d/}"; id="${id%%/*}"; kind_hint=slides ;;
    *drive.google.com/file/d/*)         id="${url#*file/d/}";         id="${id%%/*}"; kind_hint=file ;;
    *) record "$url" external-doc unfetchable '' 'unrecognised Google URL shape'; return ;;
  esac
  id="${id%%\?*}"

  n=$(( n + 1 ))
  out="${out_dir}/doc-${n}"

  if [[ -n "${QA_GOOGLE_ACCESS_TOKEN:-}" ]]; then
    # Drive API export. text/plain for docs, CSV for sheets, PDF for anything else.
    local mime
    case "$kind_hint" in
      doc)   mime='text/plain' ;;
      sheet) mime='text/csv' ;;
      *)     mime='application/pdf' ;;
    esac
    export_url="https://www.googleapis.com/drive/v3/files/${id}/export?mimeType=$(jq -rn --arg m "$mime" '$m|@uri')"
    curl -sS -L --max-time 90 --max-filesize "$MAX_BYTES" \
         -H "Authorization: Bearer ${QA_GOOGLE_ACCESS_TOKEN}" \
         -o "$out" "$export_url" >/dev/null 2>&1
  else
    case "$kind_hint" in
      doc)   export_url="https://docs.google.com/document/d/${id}/export?format=txt" ;;
      sheet) export_url="https://docs.google.com/spreadsheets/d/${id}/export?format=csv" ;;
      slides) export_url="https://docs.google.com/presentation/d/${id}/export/pdf" ;;
      *)     export_url="https://drive.google.com/uc?export=download&id=${id}" ;;
    esac
    curl -sS -L --max-time 90 --max-filesize "$MAX_BYTES" -o "$out" "$export_url" >/dev/null 2>&1
  fi

  if [[ ! -s "$out" ]]; then
    rm -f "$out"
    record "$url" external-doc unfetchable '' 'export returned nothing'
    return
  fi

  # A sign-in page is HTML that mentions Google's login flow, and Drive's "too
  # large to scan" interstitial is HTML too. Either way it is not the document.
  if grep -qiE 'accounts\.google\.com|ServiceLogin|<title>[^<]*(sign in|meet google)|Google Drive - Virus scan' "$out" 2>/dev/null; then
    rm -f "$out"
    record "$url" external-doc unfetchable '' \
      'not shared beyond its owner — set QA_GOOGLE_ACCESS_TOKEN, or paste the content into the issue'
    return
  fi

  sniffed=$(file -b --mime-type "$out" 2>/dev/null || printf 'application/octet-stream')
  case "$sniffed" in
    text/*|application/json|application/csv) mv -f "$out" "${out}.txt"; record "$url" text readable "${out}.txt" "exported as ${sniffed}" ;;
    application/pdf)                         mv -f "$out" "${out}.pdf"; record "$url" pdf  readable "${out}.pdf" "exported as PDF" ;;
    text/html)                               rm -f "$out"; record "$url" external-doc unfetchable '' 'export returned a web page, not the document' ;;
    *)                                       record "$url" external-doc unreadable "$out" "exported as ${sniffed}, not readable here" ;;
  esac
}

n=0
while IFS= read -r url; do
  [[ -n "$url" ]] || continue

  case "$url" in
    # Google Docs, Sheets, Slides and Drive files have real export endpoints. Try
    # them: a doc shared as "anyone with the link" exports its text, and that is
    # the whole specification on plenty of enhancement issues. A restricted doc
    # redirects to a sign-in page instead — which is checked for, because an agent
    # handed a sign-in page will faithfully summarise the sign-in page.
    *docs.google.com/*|*drive.google.com/*)
      google_export "$url"
      continue ;;
    *sharepoint.com/*|*onedrive.live.com/*|*dropbox.com/*|*notion.so/*)
      record "$url" external-doc unfetchable '' 'behind a login and has no public export endpoint — paste the relevant content into the issue'
      continue ;;
    # This project's own CI output. Not a requirement, and noisy.
    *jenkins.faveotools.com/*|*sonarqube.faveotools.com/*)
      record "$url" link skipped '' 'CI artifact, not a requirement'
      continue ;;
  esac

  case "$url" in
    # A linked issue or PR in this repo is context the pipeline can simply read —
    # it already holds the token. Left unread it would be a hole in the analysis.
    *github.com/*/issues/[0-9]*|*github.com/*/pull/[0-9]*)
      github_ref "$url"
      continue ;;
    # GitHub's attachment hosts — handled by the download path below.
    *github.com/user-attachments/*|*user-images.githubusercontent.com/*|*github.com/*/files/*) ;;
    # Anything else public: fetch it and reduce it to text. "Compare with
    # Freshdesk" plus a link is a real requirement, and refusing to open the link
    # leaves the agent guessing at the comparison it was asked to make.
    http*)
      web_page "$url"
      continue ;;
  esac

  n=$(( n + 1 ))
  tmp="${out_dir}/att-${n}.bin"
  headers=$(curl -sS -L --max-time 60 --max-filesize "$MAX_BYTES" \
              -H "Authorization: Bearer ${GITHUB_TOKEN}" \
              -D - -o "$tmp" -w '%{http_code}' "$url" 2>/dev/null) || headers=''
  code="${headers##*$'\n'}"
  served_as=$(printf '%s' "$headers" | awk 'BEGIN{IGNORECASE=1} /^content-type:/{sub(/^[^:]*:[ \t]*/,""); gsub(/\r/,""); t=$0} END{print tolower(t)}')

  if [[ ! -s "$tmp" || ! "$code" =~ ^2 ]]; then
    rm -f "$tmp"
    record "$url" link unfetchable '' "HTTP ${code:-000}"
    continue
  fi

  # Sniff the BYTES, do not trust the header. GitHub serves user-attachments
  # through a redirect chain that reports text/html for what is plainly a PNG —
  # three real screenshots on issue #15936 were classified unreadable on the
  # header alone. The content is authoritative; the header is a hint worth keeping
  # in the note when they disagree.
  ctype=$(file -b --mime-type "$tmp" 2>/dev/null) || ctype="$served_as"
  [[ -n "$ctype" ]] || ctype="$served_as"

  case "$ctype" in
    image/png)  ext=png;  kind=image; status=readable ;;
    image/jpeg) ext=jpg;  kind=image; status=readable ;;
    image/gif)  ext=gif;  kind=image; status=readable ;;
    image/webp) ext=webp; kind=image; status=readable ;;
    application/pdf) ext=pdf; kind=pdf; status=readable ;;
    text/plain*|text/markdown*|text/csv*|application/json*) ext=txt; kind=text; status=readable ;;
    video/*) ext=bin; kind=video; status=unreadable ;;
    application/vnd.openxmlformats*|application/msword|application/vnd.ms-excel|application/vnd.ms-powerpoint)
      ext=bin; kind=office; status=unreadable ;;
    application/zip|application/x-zip*) ext=zip; kind=office; status=unreadable ;;
    *) ext=bin; kind=link; status=unreadable ;;
  esac

  final="${out_dir}/att-${n}.${ext}"
  [[ "$final" != "$tmp" ]] && mv -f "$tmp" "$final"

  detail="$ctype"
  [[ "$served_as" != "$ctype"* && -n "$served_as" ]] && detail="${ctype} (served as ${served_as})"

  if [[ "$status" == readable ]]; then
    record "$url" "$kind" readable "$final" "$detail"
  else
    record "$url" "$kind" unreadable "$final" "${detail} — downloaded but not readable here"
  fi
done <<< "$urls"

# The description itself. Strip the URLs and the markdown image tags and see what
# prose is left: #13814's entire body was a Google Docs link, and "Announcement
# Module Enhancement" plus a link is not a specification. Better to say so than to
# let the agent write from a title and have nobody notice.
body_prose=$(jq -r '.body // ""' "$issue_file" \
             | sed -E 's#<img[^>]*>##g; s#!?\[[^]]*\]\([^)]*\)##g; s#https?://[^ )>"]+##g' \
             | tr -d '[:space:]' | wc -c)
# 30, not 80. The threshold is there to catch a body that is ONLY a link — #13814
# scores 0 — not to punish a terse but complete one-liner. Issue #16111's whole
# body is "Remove Mobile Form Field from Requester Form Builder from admin panel":
# 69 characters, entirely requirement, and flagging it as "not written down" would
# be a false alarm on exactly the kind of clear bug report you want to encourage.
if (( body_prose < ${QA_MIN_BODY_CHARS:-30} )); then
  if (( body_prose == 0 )); then
    note='the body contains no prose at all — only links or images'
  else
    note="only ${body_prose} characters of prose once links and images are removed"
  fi
  record "(issue description)" description unreadable '' "$note"
fi

jq -sc --argjson issue "${issue_number:-0}" '
  {issue: $issue,
   items: .,
   counts: (group_by(.status) | map({(.[0].status): length}) | add // {}),
   needs_more_info: (any(.[]; .status == "unreadable" or .status == "unfetchable"))}
' "$items" > "$manifest"
rm -f "$items"

printf 'fetch-attachments: %s\n' "$(jq -c '.counts + {needs_more_info}' "$manifest")" >&2
