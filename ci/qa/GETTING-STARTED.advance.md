# First round of AI testing — integration guide

How to stand this up on a repository, and what actually happens once it runs.

`README.md` beside this file is the reference: every script, every environment
variable, every hard-won fact about the QA Touch API. This document is the path
through it — read it once, top to bottom, and you can configure the pipeline and
know what its output means.

---

## 1. What it is

Claude writes test cases from an approved issue, a human approves them, and Claude
later executes them against a throwaway instance built from the pull request's own
merge ref. The result is one comment on the PR and a verdict label.

```
Issue created
  └─ label "QA: Test case needed"          → STAGE 1  Claude authors cases into
       │                                              QA Touch, publishes them on
       │                                              the issue, leaves a marker
       └─ label "QA Test Cases Added"
            └─ ▓▓ STAGE 2 — A PERSON ▓▓
               the QA Lead reviews the cases in QA Touch and applies
               "QA: Test case Approved"; the developer builds the change
               and raises a PR with "Fixes #<issue>"
                 └─ PR approved + checks green + "Requires Functionality Review 🙏"
                      └─ STAGE 3  gate → disposable instance → probes →
                                  execute the approved cases → report + verdict
```

**Stage 2 is not automated and that is the point.** The same model writes the
cases and executes them; without a person in between, a bad case would author
itself into a passing result. The gate refuses to run a round until that approval
label exists. That is why the stages are numbered 1 and 3 — the gap is a person.

### What a round does

| | |
|---|---|
| **Probes** | ~35 deterministic checks in about a minute: exposed files, debug output, the anonymous and cross-role authorisation boundary, CSRF, cookie and session handling, and both API surfaces against this project's conventions |
| **Executor** | drives the approved `TR####` cases — the browser through Playwright MCP for UI cases, curl for API cases — and records honest per-case statuses |
| **Report** | one comment per PR, replaced on each round rather than stacked |
| **Verdict** | a label, from the case counts plus the probe results |

### What it does not do

- It does not review the diff. It establishes what happens when a person uses the change.
- It does not repeat static analysis. Semgrep, Larastan and SonarQube already run in the main pipeline; what none of them can see is a running application.
- It does not test the UI deterministically. The browser and UI/UX probes were removed; UI coverage is the approved test cases and nothing else.
- It never turns "we could not check" into a pass. A check that could not run is recorded as not-run *and* blocking, which makes the verdict `unknown` and applies no label.

---

## 2. Before you start

**On the build node** — a node that runs the unit tests already has everything except a browser and the CLI:

| Tool | Needed for |
|---|---|
| `git`, `curl`, `jq`, `awk`, `file` | both stages |
| Node + the `claude` CLI | both stages invoke `claude -p` headlessly |
| PHP + `composer` | `testing-setup`, `serve`, `dusk` |
| `yarn` (Berry) | builds the SPA assets |
| `mysql` client + a reachable server | `testing-setup` creates `qa_pr_<n>`; teardown drops it |
| Google Chrome | the executor's Playwright MCP browser, and the Dusk suite |
| Reachable `api.github.com`, `api.qatouch.com`, and the npm registry | the last one because the MCP server is fetched with `npx` on every run |

Install the CLI and Chrome **as root into the system prefix**. A tool in
`~/.local/bin` works when you test it in a login shell and is invisible to the
non-login shell a Jenkins `sh` step runs — which surfaces as Preflight reporting a
tool missing on a node where you just installed it.

**Accounts you need:** a GitHub bot user with write access to the repo, a QA Touch
project and API token, and an Anthropic API key *or* an OAuth token from
`claude setup-token`.

---

## 3. Step 1 — the Jenkins job

One Pipeline job runs both stages; `QA_STAGE` decides which.

- **Pipeline script from SCM** → your repo → **Branch Specifier `*/development`** (or wherever `Jenkinsfile.qa` is maintained)
- **Script Path** → `Jenkinsfile.qa`
- Save, then **build it once by hand**. Declarative `triggers { }` and `parameters { }` are registered when the pipeline first runs — until that build, the webhook matches nothing and the parameters do not exist.

> **The single most common setup mistake** is leaving the Branch Specifier on an
> old branch. The job takes both `Jenkinsfile.qa` *and* its `ci/qa` tools snapshot
> from whatever it checks out, so a stale branch silently runs a stale pipeline
> while the branch you are editing has no effect at all.

---

## 4. Step 2 — credentials

All in Jenkins. IDs are the defaults; the ones marked *env* can be renamed by
overriding that variable on the job.

| ID | Kind | For | Required |
|---|---|---|---|
| `faveobot` *(env `GITHUB_CREDENTIALS_ID`)* | Username with password | reading issues and PRs, posting comments, moving labels — the password is a GitHub token | yes |
| `mysql_credentials_id` *(env `MYSQL_CREDENTIALS_ID`)* | Username with password | creating and dropping the per-build database | yes |
| `qatouch-api-token` | Secret text | reading cases, writing results | yes |
| `qatouch-domain` | Secret text | your QA Touch subdomain — **the bare word** (`ladybird`), not a URL | yes |
| `anthropic-api-key` | Secret text | billing the API | one of these two |
| `anthropic-oauth-token` | Secret text | billing a subscription seat (`claude setup-token`) | one of these two |
| `qa-license-code` *(env `QA_LICENSE_CREDENTIAL_ID`)* | Secret text | licensing the throwaway instance — an unlicensed one redirects everything to `/licenseError` and no case can run | yes for Stage 3 |
| `qa-instance-admin`, `qa-instance-agent` | Username with password | fixed logins for the instance | **optional** |

The instance logins are optional by design: without them the round generates a
password for itself, used once and destroyed with the database. When they are
absent Jenkins prints `Could not find credentials entry with ID
'qa-instance-admin'` — that line is expected and the pipeline says so just above it.

**Who holds what, deliberately.** The authoring agent consumes untrusted issue
text and holds *no* credential — its only output is a JSON file. The executor
holds the QA Touch token but no GitHub token: it writes its report to a file and
the pipeline publishes it. A fully successful prompt injection therefore cannot
reach GitHub or QA Touch.

---

## 5. Step 3 — the GitHub webhook

**Settings → Webhooks → Add webhook**

- **Payload URL** `https://<jenkins>/generic-webhook-trigger/invoke?token=faveo-qa-pipeline`
- **Content type** `application/json`
- **Let me select individual events** — and tick exactly these four:

| Event | Carries | Without it |
|---|---|---|
| **Issues** | `labeled` | Stage 1 never starts |
| **Pull requests** | `labeled`, `synchronize` | **labelling a PR does nothing, and pushing a fix never re-runs the round** |
| **Pull request reviews** | `submitted` | an approving review does not wake a round |
| **Check suites** | `completed` | a PR labelled while CI is red never starts when CI goes green |

GitHub does **not** send `issues` events for pull requests — a PR label emits a
`pull_request` event. Subscribing only to `issues` is the failure that looks
exactly like a broken pipeline: the gate is correct, the labels are correct, and
nothing happens, because the event never leaves GitHub.

There is **no cron**. The webhook is the only trigger, so that a label starts a
build now rather than within four hours. The cost is that a dropped delivery is
lost: GitHub keeps deliveries ~30 days under **Recent Deliveries → Redeliver**, and
building with `QA_STAGE=author` and `QA_NUMBER` blank sweeps whatever is labelled
right now.

---

## 6. Step 4 — labels

Create these in the repo. **Names are matched exactly, emoji shortcodes included** —
a label renamed here but not in the job produces a pipeline that never fires and no
error anywhere.

| Label | Applied by | Meaning |
|---|---|---|
| `QA: Test case needed` | a person | author cases for this issue |
| `QA: Test case In Progress` | the pipeline | Stage 1 is running |
| `QA Test Cases Added` | the pipeline | cases are in QA Touch, awaiting review |
| `QA: Test case Approved` | **the QA Lead** | Stage 2 — the gate requires this |
| `Requires Functionality Review :pray:` | a person | run a first round on this PR |
| `Code Approved :heart_eyes_cat:` | a person | satisfies code review without a GitHub review |
| `QA: Round 1 Testing In Progress` | the pipeline | a round is running |
| `QA: Round 1 Testing Approved :clap:` | the pipeline | the round passed |
| `Functionality Correction :facepunch:` | the pipeline | the round found something |
| `Manual` | a person | **opt out** — this issue or PR is handled by hand and the pipeline stays off it entirely |

Code review is satisfied by *either* the `Code Approved` label *or* a standing
GitHub review approval — either, not both, so the pipeline is not inert in a repo
that does not use the label.

Set `QA_CI_SETS_VERDICT=false` on the job if you want the pipeline to report but
leave every label to a person.

---

## 7. Step 5 — QA Touch

Set on the job:

- `QATOUCH_PROJECT` — the project key (`MeLq` here)
- `QATOUCH_CASE_PREFIX` — what the UI shows. The API says `TR8987`, the UI shows `FH-8987`, same case. Human-facing output uses the prefix; the marker keeps `TR`. **Searching the UI for a `TR` code finds nothing.**

**A test run must already exist for statuses to be written.** `POST /testRun`
cannot be used: with every key valid it never answers, creates the run anyway, and
attaches the entire case library instead of the cases asked for — and no API call
deletes the result. So:

1. Create the run in the QA Touch UI — Name, Release and *Assign to* are required.
2. Name it exactly `First round — PR #<pr> (issue #<issue>)`, **or** pass its key as the `QA_TEST_RUN_KEY` build parameter.
3. Prefer the key. `getAllTestRuns` does not list a run that has no results yet, so the name lookup cannot see a brand-new one.

With no run, the round still executes every case and reports on the PR — it just
writes no statuses, and says so at the top of the report. That is deliberate: a
status written into a run that holds no cases is a silent no-op that reads as a pass.

---

## 8. Step 6 — prove it works

**Stage 1, safely.** Build with `QA_STAGE=author`, `QA_NUMBER=<issue>`,
`QA_DRY_RUN=true`. It authors and validates, writes nothing to QA Touch and nothing
to the issue, and prints what it would create.

**Stage 3, safely.** Build with `QA_STAGE=execute`, `QA_NUMBER=<pr>`,
`QA_GATE_EXPLAIN=true`. Instead of stopping at the first unmet condition, it
evaluates them all and prints a checklist. It never lets a round run that the gate
would otherwise refuse, so it is safe on any PR:

```
gate: [ OK ] PR #16113 is not labelled 'Manual'
gate: [ OK ] PR #16113 is labelled 'Requires Functionality Review :pray:'
gate: [ OK ] no first-round report yet for 3a9e7721
gate: [ OK ] code review: 'Code Approved :heart_eyes_cat:' label present
gate: [ OK ] PR #16113 checks are green
gate: [ OK ] PR #16113 links issue(s): 16111
gate: [ OK ] issue #16111 cases are approved
```

Every `[FAIL]` names what to fix. **Use this before debugging anything else** — it
answers "why did nothing happen" in one build.

**Then a real round.** Label a PR that meets all seven conditions and watch for:
provisioning (~7 min) → probes (~1 min) → the executor → the report. The build ends
with a phase-timing table, so "why did this take an hour" is answered by the log.

---

## 9. Day to day

1. Label an issue `QA: Test case needed`.
2. The bot posts the cases on the issue and labels it `QA Test Cases Added`.
3. **Review them in QA Touch.** Amend anything wrong *there* — the round fetches steps from QA Touch at execution time, so an amended case is picked up next round with no pipeline change. Then apply `QA: Test case Approved`.
4. The developer raises a PR with `Fixes #<issue>` in the body.
5. When the PR is code-approved and green, label it `Requires Functionality Review 🙏`.
6. Read the report. Fix and push — the next round replaces the comment rather than adding one.

**A failed case is not automatically a defect.** The executor judges only against
the expected result as written, so a case that disagrees with the code comes back
`failed` for a human to adjudicate. On the first live round, four of six failures
were the cases being wrong. Amend the case in QA Touch or fix the code — that
decision is deliberately not the pipeline's.

---

## 10. Configuration reference

**Build parameters** (per build, from *Build with Parameters*)

| Parameter | Default | Use |
|---|---|---|
| `QA_STAGE` | `auto` | `auto` decides from the payload; `author` / `execute` force a stage |
| `QA_NUMBER` | *empty* | issue (author) or PR (execute). Blank sweeps for work |
| `QA_DRY_RUN` | `false` | author and validate only; writes nothing anywhere |
| `QA_GATE_EXPLAIN` | `false` | report all gate conditions instead of the first unmet one |
| `QA_SKIP_CHECKS` | `false` | run even though the PR's checks are not green; the report says so on the PR |
| `QA_TEST_RUN_KEY` | *empty* | write statuses into this run instead of looking one up |
| `QA_DUSK_SUITE` | *empty* | also run this suite from `phpunit.dusk.xml` |
| `QA_AGENT_LABEL` | `slave1` | which node to run on |

**Job environment** (set on the job when the default does not suit)

| Variable | Default | Use |
|---|---|---|
| `QA_EXEC_MINUTES` | `50` | the executor's time budget, clamped to what is left of the build |
| `QA_CASE_BUDGET_MINUTES` | `4` | how long one case is worth before it becomes `blocked` |
| `QA_SERVE_PORT` | `8099` | base port for the disposable instance |
| `QA_MAX_CASES` | *empty* | cap on cases per issue; empty means the issue decides |
| `QA_CI_SETS_VERDICT` | `true` | `false` leaves every label to a person |
| `QA_ACTIVATE_PLUGINS` | *empty* | plugins to activate on the instance before testing |
| `QA_PROBE_BRUTE_FORCE` | *unset* | `1` enables the login rate-limit check — **only in a build that does nothing else**, since it locks the account the round logs in with |
| `QATOUCH_PROJECT`, `QATOUCH_CASE_PREFIX` | `MeLq`, `FH` | your project |
| `QA_*_LABEL` | see §6 | rename any label |

---

## 11. Troubleshooting

| Symptom | Cause | Fix |
|---|---|---|
| Labelling a PR does nothing | the webhook is not subscribed to **Pull requests** | §5 |
| Nothing happens and the log is silent | the gate skipped — every skip is quiet by design | rebuild with `QA_GATE_EXPLAIN=true` |
| "checks are not all green" on a green PR | the job is on a stale branch, before the fix that reads commit statuses as well as check runs | point the Branch Specifier at the maintained branch |
| A round runs one case when the issue has eight | the marker was edited. Stage 3 reads the hidden `<!-- qa-touch-cases … -->` comment, never the visible list | re-apply `QA: Test case needed`; Stage 1 rebuilds both from QA Touch, deduping on code |
| The report shows checks that no longer exist | probe result files left in the reused workspace by an earlier build | fixed in `runProbes`; delete stale `qa-probe-<pr>-*.jsonl` by hand to confirm |
| "No QA Touch statuses were written" | no run exists with the expected name | §7 |
| Every UI case comes back `blocked` | the MCP browser could not start — usually no Chrome, or no npm registry egress for `npx @playwright/mcp` | the preflight tells the two apart before any case runs |
| "usage or credit limit" | the Anthropic account's window | the report names the reset time; the round re-runs by itself afterwards |
| `Could not find credentials entry with ID 'qa-instance-admin'` | none — the instance logins are optional | ignore; the next line says logins were generated |

---

## 12. Things worth knowing before you trust it

- **A round is only as good as its cases.** The pipeline's judgement lives in Stage 1's authoring and Stage 2's approval. Reviewing the cases properly is where the value is decided.
- **Pre-existing findings should not fail a PR.** A probe finding that reproduces on the base branch goes in `probes/known-pre-existing.txt` with a reason, and is reported as a warning instead. Delete the entry when the defect is fixed — a stale one silences a real regression, which is worse than the false failure it replaced.
- **Blocking is a small, boring set** — a served `.env`, a stack trace in a response, an admin endpoint answering anonymously. Adding judgement calls to it makes the gate red on every PR, and a gate that is always red stops being read.
- **The instance is disposable and destroyed at teardown.** Anything the round creates — contacts, tickets, permission changes — dies with it. Nothing touches a shared environment.
