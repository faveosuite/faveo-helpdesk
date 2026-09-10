#!/usr/bin/env bash
# Community Freestyle configuration. Sourced by every ci/qa/freestyle/*.sh entry
# point and by the build steps described in FREESTYLE-PLAN.md.
#
# CONFIGURATION ONLY. Never put a secret in this file — it is committed. Secrets
# (GITHUB_TOKEN, QATOUCH_API_TOKEN, QATOUCH_DOMAIN, ANTHROPIC_API_KEY /
# CLAUDE_CODE_OAUTH_TOKEN, DB_USER, DB_PASS) are bound by the Jenkins job as
# "secret text(s) or file(s)" and reach these scripts through the environment.
# See FREESTYLE-PLAN.md §4-5.

# Verified against `git remote -v` in this working tree, 2026-09-09:
#   origin  https://github.com/faveosuite/faveo-helpdesk.git
export REPO_NAME=faveo-helpdesk
export GITHUB_REPO=faveosuite/faveo-helpdesk

# Must match the login of the GitHub account behind the `faveobot` credential —
# resolveWork()'s self-trigger loop guard compares gh_sender to this. Wrong value
# = a possible relabel-and-refire loop. Verify with:
#   curl -H "Authorization: Bearer <pat>" https://api.github.com/user | jq -r .login
export QA_BOT_LOGIN=faveobot

# CONFIRMED (FREESTYLE-PLAN.md): Community has its own QA Touch project,
# "Faveo Helpdesk Community", key NEXQ, ~180 existing test cases. This is NOT the
# advance project (MeLq) — see the qatouch-client.sh hazard note below.
export QATOUCH_PROJECT=NEXQ

# STILL UNCONFIRMED — do not guess. The API reports every case as TR<number>;
# the QA Touch UI shows <prefix>-<number> for the SAME case. Open any case in the
# NEXQ project in the QA Touch UI and read the code it displays — the letters
# before the dash are the prefix. Leave empty to publish the raw TR<number> form
# instead of a wrong guess.
export QATOUCH_CASE_PREFIX=

# --- KNOWN HAZARD (see ci/qa/IMPORTED-FROM) ---------------------------------
# ci/qa/qatouch-client.sh:49 defaults QATOUCH_PROJECT to MeLq (the advance
# project) when it is unset. If a caller forgets to source this file, every QA
# Touch call silently targets the wrong project, and there is no delete-case
# endpoint to undo it. Every caller of any qatouch-client.sh function MUST, as
# its first check:
#   [ "${QATOUCH_PROJECT:-}" = NEXQ ] || { echo "wrong QA Touch project" >&2; exit 1; }
# -----------------------------------------------------------------------------

export QA_SERVE_PORT=8099
export QA_EXEC_MINUTES=50
export QA_CASE_BUDGET_MINUTES=4
export QA_CLAUDE_BIN=claude
export QA_CLAUDE_MODEL=claude-opus-5
export QA_CLAUDE_EFFORT=medium
export QA_EXECUTOR_MODEL=claude-opus-5
export QA_EXECUTOR_EFFORT=high
export QA_CI_SETS_VERDICT=true
export QA_MAX_SWEEP=3

# The build's total wall clock. Freestyle's "Abort the build if it's stuck" is
# configured in the job, not in code — keep this number equal to that setting.
# Everything that must finish BEFORE Jenkins aborts derives its ceiling from
# this (see freestyle/provision.sh and the executor step in FREESTYLE-PLAN.md
# §6 step 05).
export QA_BUILD_TIMEOUT_MINUTES=90

# The ten exact label names. Matched verbatim, emoji shortcodes included — a
# label renamed here but not in the repo (or the reverse) produces a job that
# never fires and no error anywhere.
export QA_TRIGGER_LABEL='QA: Test case needed'
export QA_PROGRESS_LABEL='QA: Test case In Progress'
export QA_DONE_LABEL='QA Test Cases Added'
export QA_APPROVED_LABEL='QA: Test case Approved'
export QA_PR_TRIGGER_LABEL='Requires Functionality Review :pray:'
export QA_CODE_APPROVED_LABEL='Code Approved :heart_eyes_cat:'
export QA_MANUAL_LABEL='Manual'
export QA_ROUND1_PROGRESS_LABEL='QA: Round 1 Testing In Progress'
export QA_ROUND1_PASS_LABEL='QA: Round 1 Testing Approved :clap:'
export QA_CORRECTION_LABEL='Functionality Correction :facepunch:'

# --- Community application facts (verified in this working tree) ----------
# No licensing: no CheckValidLicense, no faveo_license, testing-setup takes no
# licence segments. Do NOT create a qa-license-code credential and do not set
# QA_HAS_LICENSE-style variables — there is nothing here to activate.
#
# No v3 API: routes/api.php has exactly one stub route (`/user/test`). Do not
# import enable-api.php or probes/api.sh.
#
# plugins table exists (database/migrations/2016_02_16_140450_create_plugins_table.php)
# but app/Plugins/ holds only ServiceProvider.php — no installed plugin ships
# with Community, so there is nothing for activate-plugins.php to activate.
# Not imported for that reason, not because the table is absent.
#
# No attempt_locks table, so there is nothing for relax-lockout.php to relax.
#
# No phpunit.dusk.xml (vendor/laravel/dusk ships phpunit.dusk.xml.dist only) —
# dusk suites, dusk-changed.sh and QA_DUSK_SUITE are dropped for v1.
export QA_ACTIVATE_PLUGINS=none