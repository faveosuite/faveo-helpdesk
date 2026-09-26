#!/usr/bin/env bash
# offline stub. Scenario is supplied through env:
#   T_LINKED   space-separated issue numbers
#   T_LABELS   "<num>:<label>|<label>;<num>:<label>"   (num 0 = the PR)
gh_require_env() { :; }
gh_pr() { printf '{"head":{"sha":"deadbeefcafe"}}'; }
gh_pr_linked_issues() { printf '%s\n' ${T_LINKED:-}; }
gh_pr_is_approved() { [[ "${T_REVIEW:-0}" == "1" ]]; }
gh_pr_checks_green() { [[ "${T_GREEN:-1}" == "1" ]]; }
gh_has_label() {
  local n="$1" want="$2" entry num labels lab
  for entry in ${T_LABELS:-}; do
    num="${entry%%:*}"; labels="${entry#*:}"
    [[ "$num" == "$n" ]] || continue
    IFS='|' read -ra arr <<< "$labels"
    for lab in "${arr[@]}"; do [[ "${lab//"~"/ }" == "$want" ]] && return 0; done
  done
  return 1
}
gh_issue_milestone() {
  local entry
  for entry in ${T_MS:-}; do
    [[ "${entry%%=*}" == "$1" ]] && { printf '%s' "${entry#*=}"; return 0; }
  done
  printf ''
}
