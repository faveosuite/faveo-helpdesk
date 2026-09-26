#!/usr/bin/env bash
MARKER_NONE=3
# T_NOMARKER = issues with no generated cases
marker_read() {
  local n
  for n in ${T_NOMARKER:-}; do [[ "$n" == "$1" ]] && return "$MARKER_NONE"; done
  # 4 is what the real marker_read returns for a marker it refuses to guess at.
  for n in ${T_BADMARKER:-}; do [[ "$n" == "$1" ]] && return 4; done
  printf '{"v":1,"cases":[{"code":"TR%s"}]}' "$1"
}
pr_marker_exists() { [[ -n "${T_MARKER_SHA:-}" ]]; }
pr_marker_issues() { printf '%s\n' ${T_DONE:-} | sed '/^$/d'; }
