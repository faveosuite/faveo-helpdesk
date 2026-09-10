#!/usr/bin/env bash
# Shell port of Jenkinsfile.qa's assertBrowserUsable() (advance repo, lines
# 1384-1451). Unmodified in spirit for Community — the Playwright MCP browser
# and mcp-ci.json are identical infrastructure, nothing here is advance-only.
#
#   ci/qa/freestyle/browser-preflight.sh <pr-number>
#
# Never fails the build (always exits 0, matching the Pipeline's own
# `assertBrowserUsable` which only warns): a browser that cannot be driven
# blocks every UI test case, but that is the executor's problem to report, not
# a reason to abandon the round before probes and API-shaped cases have run.
#
# On failure to drive a browser, writes qa-browser-unavailable-<pr>.txt — the
# caller checks for this file and sets QA_BROWSER_FALLBACK=1 for the executor
# step (FREESTYLE-PLAN.md §6 step 05).
#
# Requires: QA_CLAUDE_BIN, QA_TOOLS, QA_BASE_URL, Claude credentials already in
# the environment (claude subscription/API key — NOT GitHub or QA Touch
# tokens, which must already be unset by the caller before this runs).

set -uo pipefail

tools="${QA_TOOLS:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
pr="${1:?usage: browser-preflight.sh <pr-number>}"
marker="qa-browser-unavailable-${pr}.txt"
stream="qa-browser-probe-${pr}.jsonl"
rm -f "$marker"

# mcp-ci.json launches `npx -y @playwright/mcp@latest`, which reaches the npm
# registry on EVERY invocation. A node with no registry egress gets a server
# that never starts, and no tool allowlist fixes that — tell it apart from "the
# browser itself would not drive" up front.
npx_rc=0
npx -y @playwright/mcp@latest --version > qa-browser-npx.txt 2>&1 || npx_rc=$?
if [[ "$npx_rc" -ne 0 ]]; then
  why=$(cat qa-browser-npx.txt 2>/dev/null || true)
  {
    echo "the MCP server package could not be started:"
    echo "$why"
  } > "$marker"
  echo "WARNING: @playwright/mcp cannot start on this node (npx exited ${npx_rc}) — there is no other browser, so every UI test case will be blocked." >&2
  printf '%s\n' "${why:0:600}" >&2
  exit 0
fi

rc=0
"${QA_CLAUDE_BIN:-claude}" -p "Open ${QA_BASE_URL} with the browser and reply with only the page title." \
  --allowed-tools "ToolSearch,mcp__playwright" \
  --mcp-config "${tools}/mcp-ci.json" \
  --strict-mcp-config \
  --output-format stream-json --verbose \
  < /dev/null > "$stream" 2> "qa-browser-probe-${pr}.err" || rc=$?

# The verdict is mechanical, not read out of prose: a model that cannot reach
# the browser tends to say "I was unable to open the page" and still exit 0.
# What counts is a browser tool CALLED and a result that came back without
# is_error.
called=$(jq -Rr 'fromjson? // empty | (.message | objects)
    | [.content[]? | select(.type == "tool_use")
       | select(.name | startswith("mcp__playwright__")) | .name] | .[]' "$stream" \
  2>/dev/null | head -3 | tr '\n' ' ')
errored=$(jq -Rr 'fromjson? // empty | (.message | objects)
    | [.content[]? | select(.type == "tool_result")
       | select(.is_error == true)] | length' "$stream" 2>/dev/null \
  | awk '{ t += $1 } END { print t + 0 }')

if [[ "$rc" -ne 0 || -z "$called" ]]; then
  tail_bytes=$(tail -c 800 "$stream" 2>/dev/null || true)
  {
    echo "no mcp__playwright tool call completed (rc=${rc})"
    echo "$tail_bytes"
  } > "$marker"
  echo "WARNING: the Playwright MCP browser was not driven on this node — there is no other browser, so every UI test case will be blocked. Called: '${called}', tool errors: ${errored:-0}" >&2
  printf '%s\n' "${tail_bytes:0:600}" >&2
else
  echo "playwright MCP is driving a browser (${called}, ${errored:-0} tool error(s))"
fi

exit 0