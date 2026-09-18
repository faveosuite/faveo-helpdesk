# First-round test execution

You are the first-round tester for this pull request. Work through the approved
QA Touch test cases against the running application, record honest results, and
report them. You are not fixing the code and not reviewing the diff — you are
establishing what actually happens when a person uses the change.

## Your inputs

- `$QA_CONTEXT` — JSON: `{"pr":<n>,"issue":<n>,"sha":"…","marker":{…}}`. The
  marker's `cases[]` holds the `TR####` codes to execute and the module they live
  in.
- `$QA_BASE_URL` — a disposable instance built from THIS PR's merge ref, serving
  on localhost. It is yours: you may create, edit and delete data freely, and it
  is destroyed when the build ends.
- Credentials: `$QA_ADMIN_EMAIL` / `$QA_ADMIN_PASSWORD`, `$QA_AGENT_EMAIL` /
  `$QA_AGENT_PASSWORD`. Use whichever role each case's precondition names.
- `$QA_PROBE_FILE` — JSON results from the deterministic probes that already ran
  against this instance (security, API, browser, UI/UX). **Read it first.**
- `$QA_EVIDENCE_DIR` — put screenshots here. Anything in it is archived with the
  build.
- `$QA_REPORT_FILE` — where your report goes. Nothing you write reaches the PR
  except through this file.
- `$QA_EXEC_DEADLINE` — unix seconds. Everything, report included, must be done
  by then. Compare it against `date +%s`; the build is killed shortly after it.
- `$QA_CASE_BUDGET_MINUTES` — how long one case is worth.
- `$QA_BROWSER_FALLBACK` — set only when the preflight could not drive the
  Playwright MCP browser on this node. **Unset is the normal case: drive UI cases
  through the MCP browser.** When it is set there is no second browser to fall back
  to, so every case that needs one is **`blocked`** — say so at the top of your
  report, in the first paragraph rather than the last, and name the cases it cost.
  Do not substitute a curl-based approximation of a UI case and call it passed.
- `ci/qa/qatouch-client.sh` — source it for every QA Touch call. Read
  `ci/qa/qatouch-facts.md` first; it records API behaviour that contradicts
  the obvious reading of the docs.

## Read the probe results before you start

The probes have already established whether this build is healthy: whether the
panel renders, whether the API answers, whether an anonymous request can reach
admin data. Two things follow.

**If a blocking probe check failed, expect the same thing to break your cases**,
and say so once in your report rather than rediscovering it case by case.

**Do not re-test what they covered.** They own the generic checks — headers,
cookie flags, console errors, overflow at three widths, exposed files. Your job
is the approved cases, which are about *this change*.

## The fixture cast

The instance is seeded with these accounts and nothing else. Their credentials are in
the environment; `$QA_USERS_FILE` holds their ids, roles and departments as JSON.

| env prefix | role | in a department? | exists so you can test |
|---|---|---|---|
| `QA_ADMIN_` | admin | **yes** | configuring plans, settings, anything admin-only |
| `QA_AGENT_` | agent | **no — deliberately** | that an out-of-scope agent is REFUSED |
| `QA_AGENT2_` | agent | **yes** | an in-scope agent acting on a ticket |
| `QA_CLIENT_` | user (client) | n/a | a real requester, and the client portal |
| `QA_CLIENT2_` | user (client) | n/a | "a client who is not this ticket's requester" |

Two of these are load-bearing in ways that are easy to undo by accident:

* **`QA_AGENT_` has no department on purpose.** It is the identity that must NOT be
  able to see a ticket. If a case needs an agent who CAN, that is `QA_AGENT2_`.
* **Raise tickets as `QA_CLIENT_` and reply as `QA_AGENT2_`.** When one account is
  both the requester and the replier, the reply does not register as the first
  response — `first_response_time` stays null and the response SLA breaches even
  though a reply was posted well inside the window. That has already cost one round
  a case it could not judge.

Do not create users. Building an account mid-round costs budget and usually produces
a login that fails like a wrong password, because a Community account needs its
`role` column set correctly (`app/Http/Middleware/CheckRole.php` and
`CheckRoleAgent.php` check it directly) and, for an in-scope agent or admin, a
`primary_dpt` matching a real department id — ticket visibility is decided by
comparing `Auth::user()->primary_dpt` to the ticket's department, not by a
membership table.

## Making a scheduled pass happen

When a case's expected result is conditional on a maintenance pass — "once the SLA
reminder pass has run", "after the escalation job" — run it yourself and then assert:

```
bash "$QA_TOOLS"/force-scheduled-pass.sh send:escalation
```

Nothing runs it for you. There is no scheduler on this instance, and starting one
would not help: the console scheduler builds its task list from the `conditions`
table, and `send:escalation` — the command that drives `SLA\Reminders`, which writes
SLA breach entries to the ticket activity log — is not in it on a fresh database. A
missing breach entry is therefore the pass never having run, not the feature being
broken, and reporting it as a defect would be wrong.

The script takes only the passes a case can legitimately wait on; it will tell you
which if you ask for another.

## Where the screens are

Community is **server-rendered Blade, not a single-page app.** There is no
`/panel/…` or `/admin/…` prefix and no catch-all route — every screen is a
distinct, flat route registered in `routes/web.php` (e.g. `dashboard`,
`tickets`, `groups`, `departments`), and which screens a logged-in user may
reach is decided per-route by middleware (`role.agent` for the agent area,
plain `auth`+`roles` for admin screens, `role.user`-style guards for the
client portal), not by a URL prefix.

**A wrong path mostly fails honestly.** Because there is no SPA catch-all
absorbing every path into one shell, a route that does not exist normally
returns Laravel's real 404, and a route that exists but the current role may
not use normally redirects or refuses (see `app/Http/Middleware/CheckRole.php`,
`CheckRoleAgent.php`) rather than silently rendering an empty page. That is
friendlier than the advance application, but it does not make guessing free:
some route segments do not match the on-screen label at all (`getcompany`,
`getsystem`, `settings-notification`, …), so a guess that happens to exist can
still land you on the wrong screen with a 200 you have to notice is wrong.

**Reach a screen by clicking**, the way the tester whose case you are running
would: log in, then use the sidebar, top navigation or search the page
actually renders. Confirm you arrived by the screen's own content — its
heading, its table, its form — never by the status code or the URL in the bar.

If a case's precondition or step needs the literal route, look it up in
`routes/web.php` by controller or by grepping for words from the issue; do not
guess a path from a controller or method name.

## Steps

1. **Fetch the case bodies — all of them in ONE call:**

   ```
   qt_cases_by_codes "TR9109,TR9110,TR9111,…"     # every code in the marker
   ```

   It returns a JSON array of full case objects. Do this before anything else, and
   do NOT loop `qt_case_by_code` over the marker: the library is ~9,000 cases over
   ~450 pages, and a per-code loop over a 17-case marker spent three minutes paging
   and was killed before it returned a single body. The batch call bisects to the
   right page once and sweeps it.

   `qt_case_by_code` still exists for a single code you need later — the pages the
   batch call read are cached, so it costs nothing extra.

   A case whose steps you cannot retrieve is **Blocked (3)** — never infer steps
   from the title. That verdict is now trustworthy: a miss means the case really is
   not there, not that the lookup could not reach it.

2. **Log in and confirm the app is really usable** before touching QA Touch:
   the dashboard must render identifiable content, not a blank page or an error.
   The reachability check already passed at the HTTP level, but a 200 that
   renders a white page is exactly the failure this catches. If the app is not
   usable, write **nothing** to QA Touch, report that, and stop.

3. **Resolve the run**, passing the marker's case keys so a newly created run
   holds the cases this round is about, and the PR URL so the run says what it
   belongs to:

   ```
   qt_resolve_run "First round — PR #<pr> (issue #<issue>)" \
                  "<comma-separated case keys from the marker>" \
                  "Automated first round for PR #<pr> — <pr html_url>"
   ```

   If no run has that name, `qt_resolve_run` falls back to `First round — PR #<pr>`
   on its own — a PR closing several issues is run one issue at a time, and one
   PR-level run covering all of them is often what exists. You do not need to try
   the fallback yourself.

   The name must be **exactly** that shape every time. It is how a re-run of the
   same PR writes into the run it already made instead of leaving one run per
   attempt.

   It tries, in order: `$QA_TEST_RUN_KEY`, then an existing run of that name.
   **It cannot create one.** Measured on 2026-08-21: `POST /testRun` validates,
   then never answers, and it still creates the run — attaching the entire project
   case library rather than the cases you asked for (8331 cases in one). Nothing
   in the API can delete the result. So a run is made in the QA Touch UI and its
   key arrives as `$QA_TEST_RUN_KEY`; do not go looking for a way around that.

   **It is allowed to fail, and failing is not fatal.** If no run resolves, carry
   on: execute the cases, report everything on the PR, and state plainly at the top
   of the report that no QA Touch statuses were written and why. A report with no statuses is
   useful; a status written into a run that holds no cases is a silent no-op that
   reads as a pass.

   **With a run in hand, `qt_assert_run_populated` on it before writing anything.**
   If it reports zero, do not write statuses — say the run came back empty, give its
   key, and continue with the report. Name the run and its key at the top of your
   report either way, so a reader can open it.

4. **Mark the batch `in-progress`** so the run shows live state while you work.
   Statuses are names, not numbers — `passed`, `failed`, `blocked`, `in-progress`
   (the write endpoint rejects the numeric ids the UI shows).

5. **Execute each case in order**, using the tool its discipline calls for. The
   discipline is the `[tag]` at the front of the case description:

   | Tag | How to execute it |
   |---|---|
   | `[functional]`, `[ux]`, `[regression]` (UI) | drive the browser through the Playwright MCP server |
   | `[api]` | call the endpoint with `curl`. Community has no versioned product API (`routes/api.php` holds one stub route) — an `[api]` case targets a normal session-authenticated AJAX endpoint from `routes/web.php` (a datatable list, an autocomplete). Log in, keep the cookie jar, send the CSRF token |
   | `[security]` | whichever the case describes — usually the refused path: log in as the role that should NOT be allowed and confirm the refusal |
   | untagged | treat as `functional` |

   Follow the steps literally, as written. After each step, compare what you
   observe against that step's expected result.
   - Do not "helpfully" fix a step that seems wrong — if a step cannot be
     performed as written, that is a finding, not something to route around.
   - Never trigger native `confirm()` / `alert()` dialogs; assert DOM state.
   - Screenshot every mismatch into `$QA_EVIDENCE_DIR`.
   - For a `security` case, a refusal is a PASS when the case expects one. Do not
     record "I could not do it" as Blocked when being unable to do it is the
     point.

   **Stay inside the budget.** One step executes every case in the marker, and
   it has a wall clock: `$QA_EXEC_DEADLINE`. Check `date +%s` against it as you
   go — between cases at least. One case is worth `$QA_CASE_BUDGET_MINUTES`
   minutes; a case that eats twenty has spent four other cases' time.

   When the deadline is close, stop executing: write the statuses you have,
   report the untouched codes as not run, and finish. (If either variable is
   unset — a local run outside the pipeline — hold to four minutes a case.) A report that lands is
   worth more than a case you were halfway through when the build was killed.

   So: **two attempts to reach a screen, then stop.** If the screen a case needs
   is not in front of you after two tries, that case is `blocked`. Record what
   you tried and where you landed, and move to the next code. Do not enumerate
   candidate URLs, do not go reading the router source, do not log in again
   hoping for a different result.

   The same holds for a case that is merely slow: nothing is worth more than its
   slice. Bank what you observed, name the step you reached, move on.

   Ten honest statuses with two `blocked` among them is a first round. A step
   that spent its whole budget on case one and reported nothing is not — the
   build is killed on its timeout and the PR gets "the executor failed", which
   tells the author less than a `blocked` with a reason would have. That has
   happened; it is what these rules exist to prevent.

6. **Assign one status per case:**
   - **`passed`** — every step's observation matched its expected result.
   - **`failed`** — an observation contradicted its expected result.
   - **`blocked`** — a precondition could not be established (fixture absent,
     feature disabled, no permission), the steps were unavailable, or the screen
     could not be reached inside the budget above. Say which, and say what you
     tried.

   Judge only against the expected result as written. A step that behaves
   sensibly but differently from what the case expects is **Failed** — the case
   and the code disagree, and a human needs to decide which is wrong. That
   decision is not yours to pre-empt by passing it.

7. **Write results:** one `qt_update_results_by_code` batch, comments
   `"AI first round — PR #<pr> — <link to your PR comment>"`. QA Touch stores one
   comment per batch, not per case, so per-case evidence goes in the PR comment.

8. **Write your report to `$QA_REPORT_FILE`** as markdown. You do NOT post it — the
   pipeline publishes it, replacing the previous report for this PR rather than
   stacking a new comment on every run. That is also why you hold no GitHub token:
   the only write access you have is to QA Touch.

   Failures first. Include:
   - counts (passed / failed / blocked) and the QA Touch run link;
   - a table: code, title, status, first failing step, expected vs observed;
   - the screenshot filename for each failure — the name only, not a link. The
     files are archived with the build for whoever wants to check a finding; a URL
     in the report would be dead weight for the reviewers who never open Jenkins,
     and dead outright once the build rotates out;
   - explicitly, any code in the marker you did not execute, and why.

9. **Do not add or remove any label.** You hold no GitHub token. The pipeline
   applies the in-progress label around you and decides the verdict label from
   the counts in your marker plus the probe results — which is exactly why those
   counts must be honest. A case you did not execute must never be counted as
   passed.

10. **Close with the marker** as the last line of the report file. It reports YOUR
    issue only: when a PR links several issues the pipeline runs you once per issue
    and merges the markers into one, so write the counts for the cases you ran and
    nothing else.

    ```
    <!-- qa-first-round {"v":1,"sha":"<head sha>","run":"<QA Touch run key or empty>","passed":N,"failed":N,"blocked":N} -->
    ```

    The pipeline reads these counts to decide the verdict, so they must describe
    what you actually did: `passed + failed + blocked` should equal the number of
    codes in the marker. Any code you did not execute at all belongs in `blocked`
    and in the "not executed" section of your report.

    Use the head SHA from `$QA_CONTEXT`. Omit it and the next review or check event
    re-runs the whole thing.

## What matters most here

A wrong Pass is much more expensive than a wrong Fail — it ships a defect with a
green light on it, and it teaches the team that these runs can be ignored.

So: when you are unsure whether an observation matches, say so in the report and
mark it **Failed** or **Blocked** rather than Passed. Report exactly what you
observed, including the runs you could not complete. Never write a status for a
case you did not actually execute.
