# AI first-round QA pipeline

Label-driven automation across the issue → PR lifecycle. Claude authors the test
cases from an approved issue and later executes them against a running Faveo
instance as the first round of testing, with deterministic API and security probes
underneath the agent. QA Touch is the store and the review surface; its own AI
generator is browser-only and cannot be called from CI (see `qatouch-facts.md`).

> **Setting this up, or trying to understand what a round means?** Start with
> **[GETTING-STARTED.md](GETTING-STARTED.md)** — the path through this document:
> what a round does, how to configure Jenkins, GitHub and QA Touch, how to prove
> it works, and what to do when nothing happens. This file is the reference it
> points into.

**A PR that closes several issues.** The round runs the cases of exactly one of
them — the first, in GitHub's own `closingIssuesReferences` order, that carries
approved cases. An issue whose cases are absent or unapproved is passed over and the
loop tries the next one, so an unapproved issue no longer costs the round. Every
issue not chosen is named in the report with the reason, and the milestone comes from
the issue that was chosen; the others' milestones are never consulted. If a PR really
spans three releases, that is a PR worth splitting, and the report says enough for
someone to notice.

**Opt-out: the `Manual` label.** On an issue or a pull request it turns this
pipeline off for that item completely — no cases authored, no round run, no comment,
no label touched. The cron sweep excludes it in the GitHub search query, and both
stages check it again for the webhook path, where a `labeled` event names a number
directly and the sweep never runs. Silent by design: an opt-out that announces
itself on every event is noise.

```
Issue created
  └─ label: "QA: Test case needed"     → Stage 1: Claude authors cases from the
       │                                  description, pushes them to QA Touch
       └─ label: "QA Test Cases Added"  → cases posted on the issue + marker comment
            └─ reviewed in QA Touch → "QA: Test case Approved" (QA Lead)
                 └─ developer works, raises PR with "Fixes #<issue>"
                      └─ PR approved (review or "Code approved") + checks green
                         + label: "Requires Functionality Review"
                           → Stage 3: gate → build a throwaway instance →
                             probes (security, API, browser, UI/UX) →
                             execute the approved cases → statuses in QA Touch,
                             one report on the PR, verdict label
```

Stage 3's verdict:

| Outcome | Label | When |
|---|---|---|
| pass | `QA: Round 1 Testing Approved` | no blocking probe failed, cases ran, none failed, none blocked |
| correction | `Functionality Correction` | a blocking probe failed, or a case failed |
| inconclusive | *none* | part of the round did not run — a pass would claim coverage that does not exist |

Set `QA_CI_SETS_VERDICT=false` in the job to make the round advisory: CI still
reports, a person still applies the label.

## Files

| File | Role |
|---|---|
| `qatouch-facts.md` | **Read first.** Verified API behaviour that contradicts the docs. |
| `marker.md` | The `<!-- qa-touch-cases -->` contract between Stage 1 and Stage 3. |
| `qatouch-client.sh` | QA Touch client: auth, cases, modules, runs, results. |
| `gh-client.sh` | GitHub client: issues, labels, comments, PR readiness gates. Plain curl — the `gh` CLI is used nowhere. |
| `fetch-issue.sh` | Hands the authoring agent the issue as a JSON file, so it needs no GitHub tool and no shell. |
| `fetch-attachments.sh` | Downloads the issue's images, PDFs and text so the agent can look at them; records what it could not open. |
| `kb-sync.sh` | Mirrors the product knowledge base to text, through Faveo's own KB API, so the agent can read the documentation. |
| `phase-summary.sh` | Turns a build's phase marks into a table — per-phase duration, total, and the slowest phase as a percentage. Printed at the end of every Stage 3 build, failures included. |
| `code-map.sh` | Generates routes / permissions / validation-rules / lang-label indexes so the agent looks features up instead of grepping 53 plugins. |
| `marker.sh` | Read/write/validate the marker. |
| `stage1-author-prompt.md` | The authoring brief Claude follows. |
| `stage1-publish.sh` | Validates authored JSON → QA Touch → issue comment → marker → labels. |
| `stage3-gate.sh` | Decides whether a PR earns a first round. Quiet skips by design. |
| `stage3-prompt.md` | Instructions the executor follows. |
| `mcp-ci.json` | Playwright MCP for the agent (no Chrome extension in CI). |
| `discover.sh` | Lists issues/PRs needing attention — cron safety net and manual runs. |
| `seed-qa-users.php` | Creates the admin and agent logins on the per-build instance. |
| `probes/lib.sh` | Finding format, HTTP helpers, session login. |
| `probes/security.sh` | Runtime security: exposed files, debug output, the authorisation boundary, CSRF, cookies, injection surfaces. |
| `probes/api.sh` | API contract on both surfaces, against the project's own conventions. |
| `probes/dusk.sh` | Runs a suite *or specific test files* from `phpunit.dusk.xml` and converts the JUnit report into findings. |
| `dusk-changed.sh` | The Dusk tests this PR added or changed — the automatic trigger for the above. |
| `label.sh` | Add/remove/check a label, resolved against the repo's real label names. |
| `probes/summarize.sh` | Merges every probe into one markdown section and one verdict. |
| `instance-health.sh` | Serving? licensed? assets built? Refuses the round rather than reporting false failures. |

Host: **`Jenkinsfile.qa`** — one parameterised Jenkins job covering both stages.

Jenkins rather than GitHub Actions because it already holds the GitHub credential,
already posts PR comments and manages labels (`upsertPRComment`, `addLabel`,
`removeLabel` in the main `Jenkinsfile`), already has PHP, Node and MySQL for the app
under test, and is not gated behind the org's Actions billing. A second CI system
just for QA would also split the picture for anyone debugging a PR.

## What the round actually covers

Two layers, and the split matters.

**Deterministic probes** run first, on every round, in seconds. They own the
generic ground — the things that are true or false regardless of what the PR
changed, and that a written test case is bad at describing:

| Probe | Covers |
|---|---|
| `security` | `.env` and `.git` exposure, debug stack traces, anonymous access to panel APIs, an agent session reaching admin-only endpoints, CSRF, session cookie flags and logout invalidation, security headers, SQL errors and reflected input, login rate limiting |
| `api` | both surfaces — public token routes and session-authenticated panel routes — status codes, JSON-not-HTML errors, the 400-for-missing-record convention, method handling, that invalid credentials get no token |
| `browser` | the login form renders, assets load, an admin can log in, the panel is not a blank shell, no console errors or failed requests on the dashboard |
| `ux` | no horizontal overflow at 390/820/1440px, a visible reason when an empty form is submitted, page title and `lang`, labelled fields, images with `alt`, keyboard reachability and a visible focus indicator, dashboard render time |

**The project's own Dusk suite** can run alongside them (`QA_DUSK_SUITE`), against
the same instance, through the same `testing-setup` environment it was written for —
reported as findings, not blocking, until its pass rate on a per-build database is
known.

**The agent** then executes the approved QA Touch cases — which are about *this
change*: the feature working, the endpoint answering, the role that should be
refused actually being refused, the message a person sees when they get it wrong.

Findings carry two independent axes:

- **severity** — how bad it would be if real. Reporting only.
- **blocking** — whether it decides the verdict.

Only unambiguous findings block: a served `.env`, a stack trace in a response, an
admin endpoint answering an anonymous request, a panel that renders blank. Missing
CSP, no `SameSite`, an unlabelled image and a missing focus ring are reported and
left to a person. A gate that fails every PR for a pre-existing hardening gap is a
gate nobody reads — and then the blocking findings go unread with it.

Static analysis is deliberately **not** repeated here: Semgrep, Larastan and
SonarQube already run in the main pipeline. What they cannot see is a running
application, which is the whole point of this round.

## Labels

**No new labels.** The pipeline keys off the repo's existing QA taxonomy. A parallel
set of labels meaning nearly the same thing would be worse than no automation,
because nobody could tell which one drives what.

All nine names below were **read off the repo's 329 labels through the API**, not
guessed. Four of them carry an emoji shortcode *in the name* and five do not —
copy them exactly:

| Pipeline point | Label (exact) | Who sets it |
|---|---|---|
| Author cases | `QA: Test case needed` | human |
| Authoring running | `QA: Test case In Progress` | CI (or a person writing them by hand) |
| Cases published on the issue | `QA Test Cases Added` | CI |
| Cases ready for a developer | `QA: Test case Approved` (QA Lead) | human |
| Code signed off | `Code Approved :heart_eyes_cat:` **or** a GitHub review approval | human |
| Run the first round | `Requires Functionality Review :pray:` | human |
| Execution running | `QA: Round 1 Testing In Progress` | CI |
| First round passed | `QA: Round 1 Testing Approved :clap:` | CI (see below) |
| First round found defects | `Functionality Correction :facepunch:` | CI (see below) |

`ci/qa/label.sh` resolves a requested name against the repo's real labels — exact
first, then ignoring a trailing ` :shortcode:` and case — so changing an emoji
cannot silence the gate, and a name that matches nothing is **refused rather than
created** (a raw POST would add a second, near-identical label and split the
taxonomy). The one place tolerance does not reach is `discover.sh`, whose cron
sweep uses a server-side `label:"…"` search: that name must be exact.

The repo also has a **Dusk test-case family** — `Dusk Test Case Required`,
`Dusk test case added`, `Dusk Test Case Pending`, `Dusk test case review required`,
`QA to test Dusk test cases` — plus `Needs Retesting`, `QA: Round 2 Testing …`,
`Testing Blocker` and a `VAPT Testing …` set. None of them drive this pipeline
today; they are the obvious hooks if you want the Dusk suite or a second round
label-driven too.

Every name is overridable through the `QA_*_LABEL` variables in the job's
`environment` block. **If you rename a trigger label, change the webhook
`regexpFilterExpression` in `Jenkinsfile.qa` too** — a label renamed in one place
and not the other produces a job that never fires, with no error anywhere.

**Code sign-off is either/or.** A standing GitHub review approval satisfies the
gate, and so does a `Code approved` label. Requiring both would make the pipeline
inert on a repo that only uses one of them, and "inert" looks exactly like a
broken webhook.

**CI applies the verdict labels.** This is a deliberate change from the pipeline's
first design, which held that a person must apply
`QA: Round 1 Testing Approved` because it means "tested *and* the failures fixed".
The team asked for CI to apply it, so it does — with two constraints that keep it
honest:

- CI applies `QA: Round 1 Testing Approved` **only** when nothing blocking failed,
  cases actually ran, none failed and none were blocked. A blocked case means part
  of the round did not happen, and signing off on tests nobody ran is the one
  failure mode worth engineering against.
- When it cannot tell, it applies nothing and says so on the PR.

`QA_CI_SETS_VERDICT=false` reverts to advisory: CI reports, a person labels.

Re-running is prevented by a head-SHA-keyed marker comment, not a label, so pushing
a fix correctly earns a fresh run where a label guard would have suppressed it. The
report itself is **upserted** — one comment per PR, replaced on each run, rather
than a new comment every time. Both in-progress labels are cleared on crash or
cancellation — an item showing work in flight with nothing running is the one label
state that lies.

**Approval gate:** `QA: Test case Approved` — QA Lead sign-off. The repo also defines
`by first reviewer` / `by second reviewer`, which are not accepted; matching is exact,
so neither satisfies the gate despite `QA: Test case Approved` being a prefix of both.
Widen with `QA_APPROVED_LABELS` (pipe-separated) if the process changes.

On the issue side CI marks `In Progress` before authoring — which takes a few
minutes, since the model reads the codebase — and on **any** failure, crash or
cancellation it puts `QA: Test case needed` back.

Dry runs touch no labels at all.

## Setup

**1. Jenkins credentials** (Manage Jenkins → Credentials)

| Credential ID | Kind | Value |
|---|---|---|
| `qatouch-domain` | Secret text | bare subdomain, e.g. `ladybird` — **not** a URL |
| `qatouch-api-token` | Secret text | User → Edit profile → Generate API Key (Professional/Enterprise only) |
| `anthropic-api-key` | Secret text | Console → API keys. Bills the organisation's **API credit** |
| `anthropic-oauth-token` | Secret text (**alternative**) | Output of `claude setup-token`. Bills a **Claude subscription seat** instead. Present ⇒ used in preference to the API key |
| `qa-instance-admin` | Username/password | QA instance admin login |
| `qa-instance-agent` | Username/password | QA instance agent login |
| `qa-license-code` | Secret text (**optional but effectively required**) | Four dash-separated licence segments, `AAAA-BBBB-CCCC-DDDD`. A `testing-setup` database has no licence, and without this every request redirects to `/licenseError` and the round refuses to start |
| `github_credentials_id` | Username/password | already exists — reused from the main pipeline |

**Which Claude credential?** Exactly one is needed, and they bill different meters:

- `anthropic-api-key` draws on the Console organisation's **prepaid API credit**. If
  that balance is empty the CLI fails with *"Credit balance is too low"* — a Claude
  Pro/Max subscription does **not** fund it.
- `anthropic-oauth-token` draws on a **Claude subscription seat**. Mint it where you
  can complete a browser login:

  ```bash
  claude setup-token          # prints a URL, then a token starting sk-ant-oat…
  ```

  Paste that into the Jenkins credential. The pipeline injects it as
  `CLAUDE_CODE_OAUTH_TOKEN`; when it exists it is used in preference to the API key,
  because a team that added one has said which meter it wants. It is tied to the
  seat of whoever ran the command — if that person leaves or the seat is removed,
  the pipeline stops authoring, so mint it from a service account or a long-lived
  team member.

Preflight runs a one-word request through the CLI on every build, so a missing
balance or an expired token fails in the first thirty seconds rather than minutes
into an authoring run with the issue parked on `In Progress`.

**2. Job config**

Create a pipeline job from `Jenkinsfile.qa`. It reuses `mysql_credentials_id` from
the main pipeline. These live in its `environment` block:

| Variable | Default | What it does |
|---|---|---|
| `QATOUCH_PROJECT` | `MeLq` | project the cases live in |
| `QA_SKIP_CHECKS` (parameter) | off | Stage 3: run even though the PR's CI checks are not green. For exercising the round itself. The PR report states that check status was not verified — a case can pass against a build whose own tests are failing |
| `QA_GATE_EXPLAIN` (parameter) | off | Stage 3: report all six gate conditions instead of stopping at the first unmet one. Never lets a round run the gate would refuse |
| `QA_ACTIVATE_PLUGINS` | *empty* | plugins the round activates after provisioning (`plugins.status = 1`). Empty = all, space-separated names/paths = only those, `none` = leave them off. `testing-setup` migrates and seeds plugins but leaves them inactive, which is right for the unit suite and wrong for a round driving the real UI |
| `qa-instance-admin` / `qa-instance-agent` | *optional* | Username-with-password credentials for the logins the round uses on the disposable instance. **Not required** — when absent the round generates them, because `seed-qa-users.php` creates those accounts rather than looking them up. Supply them only to pin fixed logins |
| `QA_LICENSE_CREDENTIAL_ID` | `qa-license-code` | Jenkins secret holding the licence as `AAAA-BBBB-CCCC-DDDD` — the same code the Dusk `license-verify` suite types into `/licenseError`. Point it at an existing licence credential rather than copying the secret into a second one |
| `QA_TEST_RUN_KEY` (param) | *empty* | write statuses into this run instead of looking one up. Empty is normal: the round finds a run named `First round — PR #<pr> (issue #<issue>)`. Created in the UI — the API cannot make one |
| `QA_ALLOW_RUN_CREATE` | `1` | the round creates its run, sending the marker's case keys with the create. Safe because `qt_assert_run_populated` refuses to write into an empty run — worst case is a report with no statuses, never a false pass |
| `QA_SERVE_PORT` | `8099` | port the disposable instance serves on |
| `QA_MODULE_SCAN_MAX_PAGES` | `600` | page cap on the last-resort scan of `getAllTestCases` that recovers a module key the module listing hides |
| `QA_MODULE_FALLBACK_SUFFIX` | *(empty)* | when set, a module name QA Touch refuses *and* cannot be found is retried as `<module> <suffix>` instead of failing the run. Off by default: a second module beside the real one splits the taxonomy, and that should be a human's call |
| `QATOUCH_MILESTONE_KEY` | — | overrides the milestone a test run is filed under. Normally it comes from the **linked issue's** GitHub milestone (`QA_ISSUE_MILESTONE`), matched by name; the gate alerts on the PR when the issue has none. `GET /getAllMilestones/<projectKey>` lists them |
| `QATOUCH_ASSIGN_TO` | first available user | the user key `POST /testRun` requires as `assignTo`. Resolved from `GET /testRun/availableUsers/<projectKey>` when unset |
| `QA_EXEC_MINUTES` | `50` | the executor's time budget, clamped to what is left of the build (`90 - elapsed - 15`). The prompt reads it as `QA_EXEC_DEADLINE`. 50 is the largest value that fits a 90-minute build untrimmed; the first complete round spent 1h01m here |
| `QA_CASE_BUDGET_MINUTES` | `4` | how long one test case is worth before it becomes `blocked` |
| `QATOUCH_CASE_PREFIX` | `FH` | the prefix QA Touch's UI puts on a case code. The API reports `TR8967`, the UI shows `FH-8967`, same case. Human-facing output uses this; the marker keeps `TR`. Empty = publish the raw API code |
| `QA_MAX_CASES` | *(empty)* | no ceiling — the issue decides how many cases it needs. Set a positive integer to reinstate one. The ~10 limit people remember is QA Touch's own `create_bulk_test_cases` (`maxItems: 10`), which this pipeline does not use |
| `QA_CI_SETS_VERDICT` | `true` | whether CI applies the verdict labels |
| `QA_AMEND` | `1` | whether re-labelling an issue with cases adds more |
| `QA_*_LABEL` | see the Labels table | label names |
| `QA_PLAYWRIGHT_CHANNEL` | auto | set to `chrome` to force the installed browser; auto-detected when one is present |
| `QA_AGENT_LABEL` (param) | *empty* | node to run on. Empty = any, i.e. the Jenkins server |
| `QA_DUSK_SUITE` (param) | *empty* | also run this suite from `phpunit.dusk.xml`; empty = skip |
| `QA_DUSK_BLOCKING` | `0` | `1` makes a Dusk failure decide the verdict |

**`QA_TEST_RUN_KEY` is the one that silently costs you results.** A run cannot be
created from the API: `POST /testRun` never answers, creates the run anyway, and
attaches the whole case library rather than the cases asked for — measured, and the
four runs it left in `MeLq` had to be deleted by hand. So `qt_resolve_run` takes
`QA_TEST_RUN_KEY`, then a run already named `First round — PR #<n> (issue #<n>)`, and
if neither exists Stage 3 takes its report-only path: a full report on the PR, a
verdict label, and nothing in QA Touch. That is deliberate — a status written into an
empty run is a silent no-op that reads as a pass — but it is also the answer to "why
is QA Touch empty when the pipeline is green".

One consequence worth planning around: `QA_TEST_RUN_KEY` is a single job-level value,
so setting it points **every** round at that one run whichever issue was chosen. Per-PR
runs need a run created in the UI with exactly the name above, which is what the
round looks for when the key is empty.

Create a run in the UI (`/v2#/testrun/p/<project>/add` — Name, Release and Assign
to are all required), take the key from its title, and set it here.

**There is no standing QA environment, by design.** Stage 3 builds a throwaway
instance from the PR's own merge ref **on the Jenkins server itself**, provisioned
by the same command the unit tests use, tests it, and drops it:

```
git fetch pull/<pr>/merge        ← the merge ref, not the head: test what would land
composer install
DROP DATABASE qa_pr_<pr>         ← a leftover from an earlier build must not be reused
php artisan testing-setup --username=… --password=… --database=qa_pr_<pr> [licence]
        ↑ creates the DB, migrates and seeds core + plugins + modules,
          and writes .env.testing AND .env.dusk.testing
APP_URL=http://127.0.0.1:8099 → appended to .env.dusk.testing, copied to .env
yarn install && yarn build       ← MANDATORY, see below
php ci/qa/seed-qa-users.php      ← after testing-setup: its seeders write users
PHP_CLI_SERVER_WORKERS=8 php artisan serve --port=8099
ci/qa/instance-health.sh         ← serving? licensed? assets built?
  → probes, then the cases →
drop the database, restore .env, kill the process, delete the branch
```

**Why `php artisan testing-setup`.** It is the same provisioning the backend test
stage in the main `Jenkinsfile` uses, so the browser round runs against the schema
and seed data the rest of CI already trusts — and there is one place to fix when
migrations or seeders change. It also writes `.env.dusk.testing`, which is what makes
the project's own Dusk suite runnable here.

**What testing-setup does not do, and this pipeline has to.** Three gaps, each of
which produced a real failure before it was handled:

1. **No `APP_URL`.** It writes `APP_ENV`, `DB_*`, `DB_INSTALL` and `APP_KEY` — no URL.
   Dusk takes its base URL from `config('app.url')`, so without one every
   `visit('/…')` goes to `http://localhost`. Provisioning appends it.
2. **No front-end build.** Unit tests need no assets; browser tests need all of
   them. `yarn build` is **not** an optimisation — the panel is a Vue SPA served
   from `public/build/v<version>/`, and without it `/panel` is a white page. Do not
   delete this step as redundant just because the unit tests do not have it.
3. **No licence.** A fresh database has no licence row, and `CheckValidLicense`
   redirects every request to `/licenseError` — with a 302, which a naive
   reachability check reads as healthy. `ci/qa/instance-health.sh` catches it, and
   with a `qa-license-code` credential the round activates the instance itself by
   running the repo's own `license-verify` Dusk suite first, exactly as
   `phpunit.dusk.xml` orders it.

Why not a standing QA box: you can never be certain it is running the PR's code, and
an executor reporting confidently about the wrong build is worse than no run at all.
Building per PR makes "the code under test is this PR's code" true by construction.

**Logging in uses Dusk's bypass, not the form.** `seed-qa-users.php` publishes the
seeded user ids to `QA_USERS_FILE`, and both the curl probes and the browser suite
then authenticate with a single `GET /_dusk/login/{id}` — the route Laravel Dusk
registers on every non-production environment. That is not a shortcut for its own
sake: posting the login form means the concurrent-session limit answers 409 and
opens a confirm modal, and every attempt (including that one) counts against a
5-attempt lockout which then blocks the rest of the round. The bypass has none of
those moving parts, and it cut the login-dependent checks roughly in half
(UI-03 8.1s → 3.3s, UX-06 11.5s → 6.4s). The form path is still there as a
fallback, and `SEC-24` reports the route on every round — expected on a testing
instance, a full authentication bypass anywhere `APP_ENV` is not `production`.

**`.env` is handled explicitly, not left to Dusk.** Three things write it —
`testing-setup`, our copy of `.env.dusk.testing`, and `php artisan dusk`, which swaps
it and restores from `.env.backup`. An aborted Dusk run leaves that backup behind, so
teardown prefers it, then the workspace's original, and only removes the file if
there was none. A shared workspace that silently loses its `.env` breaks every later
build on that node.

Teardown runs in a `finally` under all exits. A leaked `artisan serve` would hold the
port and break the next build, so the provision step also frees the port defensively
before binding.

**The project's own Dusk suite runs on two triggers.**

*Automatically, when the PR touches Dusk tests.* `ci/qa/dusk-changed.sh` reads the
PR's file list and emits targets: each added or changed `*Test.php` under a
directory `phpunit.dusk.xml` covers runs **as itself**, and those results are
**blocking** — an author's own new browser test failing is not a judgement call,
and this is the cheapest moment to catch it. If the change is to the **shared
harness** (`DuskTestCase`, `BrowserTestCase`, `tests/Browser/Helpers`,
`tests/Browser/Pages`, `phpunit.dusk.xml`), the whole `application` suite runs
instead: those files are inherited by every browser test, so "run only what
changed" would be exactly backwards. Deleted files are dropped — handing phpunit a
path that no longer exists fails the run for something that is not a defect.

*Manually, via the `QA_DUSK_SUITE` parameter* — a suite name (`application`,
`service-desk`, `service-catalogue`, `advance-dashboard`, `all`). **Non-blocking**,
because that suite's pass rate on a freshly seeded per-build database is not
established, and failing PRs on a pre-existing browser-test failure is a gate
nobody trusts. `QA_DUSK_BLOCKING=1` overrides.

Either way `ci/qa/probes/dusk.sh` converts the JUnit report into one finding per
test, and Dusk's own screenshots and console logs (`tests/Browser/screenshots`,
`tests/Browser/console`) are archived with the build.

**3. Webhook.** Subscribe to **Issues** and **Pull request reviews** only.

It is tempting to add **Check suites** and **Pull requests** as well, since a PR
becomes eligible the moment its checks go green — and that is exactly what makes the
job unusable on a busy repo. Every check-suite completion and every push to any PR
wakes the pipeline: measured here, twenty builds an hour, each one a 15–20 second
no-op that occupies an executor and clutters the run list. The realistic workflow
does not need them, because `Requires Functionality Review :pray:` is applied *after*
checks are green — the label is the signal, not the checks.

The cost of leaving them out: a PR labelled while its checks are still red will not
start by itself when they turn green. A review, a re-applied label, or a manual build
starts it.

**Payload URL.**  Point the repo webhook at the Generic Webhook Trigger endpoint with
token `faveo-qa-pipeline`, subscribed to **issues, pull_request,
pull_request_review and check_suite**.

The last two matter: a PR becomes eligible when its checks go green or a review
lands, not only when the label is applied. Without them a PR labelled before CI
finishes would sit there until the cron sweep noticed it.

**The pipeline ignores its own label changes.** On failure the `finally` puts
`QA: Test case needed` back so the work is not lost — and with a webhook listening
for `labeled`, that restoration is itself an event. Without a guard, a failing
authoring run loops indefinitely, spending tokens each time. `resolveWork()` drops
any event whose `sender.login` matches **`QA_BOT_LOGIN`** (default `faveobot`). Set
that to the login the credential actually belongs to:

```bash
curl -H "Authorization: Bearer <the faveobot PAT>" https://api.github.com/user | jq -r .login
```

**There is no cron.** The webhook is the only trigger — a label starts a build in
seconds, which is the point. The cost is stated rather than hidden: webhook
deliveries do get dropped (GitHub retries, but not forever, and a Jenkins restart
mid-delivery loses one), and a missed delivery on the issue side now means test
cases are silently never written, with nothing watching for it.

Two manual recoveries, both quick. GitHub keeps deliveries for ~30 days —
**Settings → Webhooks → Recent Deliveries → Redeliver** replays the exact event. Or
build with `QA_STAGE=author` and `QA_NUMBER` blank, which runs `discover.sh` and
sweeps everything currently labelled (capped by `QA_MAX_SWEEP`). To restore the
automatic net, uncomment the `cron` line in `Jenkinsfile.qa`.

**The job runs on the node labelled `slave1`.** Use the label, not the node's name
("Slave 1"): a Jenkins label expression splits on whitespace and ANDs the parts, so
a name containing a space has to be quoted to match anything, while a label just
works — and can move to a second node later.

Pinning is not fussiness. Stage 3 needs PHP, Composer, Yarn, a MySQL client,
Node/npm, Chrome, a matching chromedriver and the `claude` CLI; a node missing one
of them fails intermittently, which is the worst way to fail. Never the controller —
it holds every credential, and this job both runs an agent over untrusted issue text
and serves an application.

The pipeline-level `agent` falls back to the same literal, which matters because on
a job's first build no parameter has been registered yet and an empty fallback
would send that build to any node. Pinning is not fussiness: Stage 3 needs PHP, Composer, Yarn, a
MySQL client, Node/npm, Chrome, a matching chromedriver and the `claude` CLI, and a
node missing one of them fails intermittently, which is the worst way to fail.
Never the controller — it holds every credential, and this job both runs an agent
over untrusted issue text and serves an application. Stage 3 needs PHP, Composer, Yarn, a MySQL client, Node/npm, Chrome, a
matching chromedriver and the `claude` CLI, so a node missing any of them fails
intermittently, which is the worst way to fail. The instance binds `QA_SERVE_PORT + EXECUTOR_NUMBER`, so two executors on that node
cannot contend, and the `fuser -k` before binding can only reach this executor's own
leftover process. `disableConcurrentBuilds()` still means one round at a time for
this job; to run rounds in parallel, drop that option — the port and the database
name (`qa_pr_<n>`) are already per-build.

**Which branch does the job run from?** Whatever the job is configured for — this
is a plain Pipeline job with *Pipeline script from SCM*, not a multibranch one, so
it always runs `Jenkinsfile.qa` and `ci/qa/**` from that one branch. GitHub issues
have no branch at all, so Stage 1 works from a feature branch on day one; there is
nothing to merge first.

Stage 3 has one wrinkle that follows from this, and it is handled: provisioning
checks the PR's merge ref out into the **same workspace**, which would replace this
pipeline's own scripts with the pull request's copy of them — and on a PR based on
`development`, where `ci/qa/` does not exist yet, every probe would be
"No such file or directory". So `executeForPr` snapshots `ci/qa/` to
`$WORKSPACE-qa-tools` **before** anything is checked out, and runs the probes from
there against the code in the workspace. `seed-qa-users.php` and
`relax-lockout.php` take `QA_APP_ROOT` for the same reason: the tools and the
application under test are two different trees.

**4. Node.** The job runs wherever `QA_AGENT_LABEL` points; **empty, its default,
means any node — the Jenkins server itself**, the same machine that runs the unit
tests. Because Stage 3 builds, serves and drives the application there, that node
needs the full app toolchain, not just the CI basics.

| Need | For |
|---|---|
| `git`, `curl`, `jq`, `awk`, `file` | both stages (`file` sniffs attachment types) |
| Node + the `claude` CLI | both stages invoke `claude -p` headlessly |
| **PHP + `composer`** | `testing-setup`, `serve`, `dusk` |
| **`yarn`** (Berry, `yarn@4.10.3`) | builds the SPA assets |
| **`mysql` client + a reachable MySQL server** | `testing-setup` creates `qa_pr_<n>`; teardown drops it |
| **Google Chrome** | two consumers: the Dusk suite (via chromedriver) and the executing agent's Playwright MCP — both pointed at the installed Chrome so nothing downloads a second one |
| Reach `api.github.com`, `api.qatouch.com` | both stages — **confirmed reachable** |
| No external app URL needed | the instance is served on `127.0.0.1` |

A node that runs the unit tests perfectly already has everything except a browser:
the main pipeline never opens one. Install Chrome and the `claude` CLI and it is
ready. Preflight warns when either is missing rather than discovering it mid-round.

Install the two extras **as root, into the system prefix**, not under the jenkins
user. A tool in `~/.npm-global/bin` or `~/.local/bin` works when you test it in a
login shell and is invisible to the non-login shell a Jenkins `sh` step runs —
which surfaces as Preflight reporting the tool missing on a node where you just
installed it. (Preflight prints the PATH it saw, for exactly this reason.)

```bash
# a browser, shared by the executing agent's MCP server and the Dusk suite
apt-get install -y google-chrome-stable        # or chromium

# the driver Dusk talks to, matched to that browser's version
php artisan dusk:chrome-driver --detect        # ci/qa/probes/dusk.sh also does this

# the MCP server the executing agent drives the browser through
npx -y @playwright/mcp@latest --version
```

Verify as the build user, in a **non-login** shell — the closest thing to what a
build actually runs:

```bash
sudo -u jenkins -H bash -c 'command -v claude && claude --version && node --version'
```

**Watch out for nvm.** If root uses nvm, `npm install -g` as root installs into
`/root/.nvm/versions/node/<v>/bin`, which the build user cannot even read — root's
home is 0700 — while `command -v claude` in root's shell reports success. Find the
Node the *build user* actually has and install with that one:

```bash
sudo -u jenkins -H bash -c 'command -v node npm; node --version'
/usr/bin/npm install -g @anthropic-ai/claude-code     # explicit path: bypasses nvm
```

Where that is genuinely impossible — a Jenkins NodeJS tool directory, say — set
`QA_CLAUDE_BIN` in the job to the absolute path instead. It must still be readable
and executable by the build user.

A **Preflight** stage checks the essentials before any work starts: `curl`, `jq` and
the `claude` CLI (fatal), the app toolchain and a browser (warnings), an authenticated call
to `api.github.com/rate_limit`, and an authenticated call to
`api.qatouch.com/api/v1/count/allProjects`. Without it, a stale QA Touch token is
only discovered by the publish step — after the authoring agent has spent minutes
reading the codebase, with the issue left sitting on `In Progress`.

**What running on the build node costs, stated plainly.** Stage 1 hands an agent
file-write access over the text of a GitHub issue — untrusted input, since anyone who
can file an issue supplies it — and on the main node that agent shares a machine with
the MySQL and SonarQube credentials. Two things bound it: the authoring step's tool
allowlist — `Read,Grep,Glob,Write`, with no `Bash` at all, because the pipeline hands
it the issue as a file — and the fact that authoring holds **no** QA Touch token — only the publish step does, and that step never reads
issue prose as instructions. Neither bounds the blast radius the way a disposable
node would. If you later stand up a dedicated one, set `QA_AGENT_LABEL` to its label;
nothing else changes.

## First run: dry run, no writes

Before labelling anything, exercise the whole chain with nothing at stake:

Run the job with **QA_STAGE=author**, **QA_NUMBER=<issue>**, **QA_DRY_RUN checked**.

That authors the cases, validates them, prints them in the log, and writes nothing
to QA Touch or the issue. Read what it produced — if the cases are vague or don't
match the real UI, fix the brief in `stage1-author-prompt.md` and run it again.
The first live run should go into a throwaway module, since code resolution is the
most fragile step in the pipeline.

## How it runs

Apply `QA: Test case needed` to an issue. Claude reads the description (and the code,
where the issue names something locatable), authors as many cases as the change warrants, and a second step
pushes them into QA Touch and publishes them on the issue with full steps. The
label moves to `QA Test Cases Added`. No human in the loop.

Add a `Module: <name>` line to an issue to pin where the cases land — an explicit
human choice overrides the module Claude picks.

The case codes land in two places: a comment with every step, for review, and a
delimited block in the **issue body**, so "which cases cover this issue" is
visible without scrolling a thread.

**Attachments are fetched, and what cannot be read is declared.** On a bug report
the screenshot usually *is* the requirement; on an enhancement a linked document
*is* the specification. `fetch-attachments.sh` pulls every attachment down before
the agent runs and classifies each one:

| Status | Meaning |
|---|---|
| `readable` | image, PDF or text — downloaded, and the agent is told to open it |
| `unreadable` | fetched but not usable here: `.docx`, `.xlsx`, `.zip`, video |
| `unfetchable` | tried and refused: a restricted Google Doc, or a host with no export endpoint (SharePoint, Dropbox, Notion) |
| `skipped` | an ordinary link, or this project's own CI artifacts — not an attachment |

**Everything in the description is analysed, or the issue is labelled.** That is the
rule; the table above is how it is enforced. Beyond attachments:

- a **linked issue or PR in this repo** is read through the API — title, body and
  comments — because the pipeline already holds the token and an unread link is a
  hole in the analysis;
- a **public web page** is fetched and reduced to text (`docs.faveo.com`, a
  competitor's help page — "compare with Freshdesk" plus a link is a real
  requirement);
- the **description itself** is measured: strip the URLs and image tags, and if
  under 80 characters of prose remain, that is recorded as unreadable. #13814's
  entire body was a Google Docs link — 0 characters of prose — and the agent wrote
  usable cases only because the feature already existed in the code to read. On a
  new feature it would have had nothing;
- the **agent declares its own gaps** in an `unanalysed` array: an attachment it
  could not open, a requirement too vague to test. The fetcher reports what could
  not be *retrieved*; only the model can report what could not be *understood*.

Any of those sets `needs_more_info`, and the publish step then applies
`Need more info about issues by QA team` and quotes every unread item on the issue.

**Google Docs, Sheets, Slides and Drive files are attempted, not dismissed.** Each
has a real export endpoint, and a doc shared as *anyone with the link* exports its
full text — which on plenty of enhancement issues is the entire specification:

| Kind | Export used |
|---|---|
| Doc | `/export?format=txt` |
| Sheet | `/export?format=csv` |
| Slides | `/export/pdf` |
| Drive file | `uc?export=download` |

A **restricted** doc answers with a sign-in page instead — verified against issue
#13814's link, which returns HTTP 401 and HTML containing `accounts.google.com`.
That is detected and recorded as `unfetchable`, because an agent handed a sign-in
page will faithfully summarise the sign-in page.

To read restricted docs, set **`QA_GOOGLE_ACCESS_TOKEN`** in the job and the script
exports through the Drive API as that identity instead. Nothing here holds a Google
credential — mint the token however you prefer (`gcloud auth print-access-token`, or
a service account shared into the Shared Drive, which is the version that scales:
share once, and every doc in it becomes readable).

Content is sniffed from the **bytes**, not the `Content-Type`: GitHub serves
`user-attachments` through a redirect chain that reports `text/html` for what is
plainly a PNG, and three real screenshots on issue #15936 were misclassified until
this used `file --mime-type`.

Anything `unreadable` or `unfetchable` means part of the specification was never
read. The cases still publish — what the issue *does* support is worth having — and
the issue additionally gets **`Need more info about issues by QA team`** plus a
comment naming each unopened item, so a person can paste the content in and
re-apply the trigger label to add the missing cases. Issue #13814, whose entire
body is a Google Docs link, is exactly this case.

**Re-applying `QA: Test case needed` to an issue that already has cases amends
it** — Claude writes additional cases, into the same module, and republishes the
marker with the union of old and new codes. Nothing existing is edited: those
cases may already be approved and already carry results in a run, and QA Touch
has no delete-case endpoint to undo a mistake with. To genuinely rewrite a set,
delete the marker comment first. `QA_AMEND=0` refuses the amend instead.

**Why the authoring step holds no QA Touch credentials.** It reads a GitHub issue,
which is untrusted text anyone can write, so the job is split: authoring can only
write a JSON file, and only the publish step — which never reads issue prose as
instructions — holds the API token. A successful prompt injection in an issue
cannot reach QA Touch, because nothing in that step can.

Validation is a whole-set gate: every case needs ≥4 steps and a non-empty expected
result on each. A step whose outcome cannot be observed becomes a Blocked result
during execution, so it is rejected at authoring time instead — and rejected as a
set, never half-written into a shared 8,200-case library.

## Design rules worth keeping

- **Quiet skips.** Stage 3 evaluates on every check and review event repo-wide.
  Not-eligible is exit 3, no comment, green run. Only misconfiguration fails loudly.
- **Never write a status for a case you didn't execute**, and never turn "app is
  down" into Failed. A run of false failures is worse than no run, so the health
  gate aborts before any QA Touch write.
- **A missing tool is not a pass.** A check that could not run is recorded as
  not-run *and marked blocking*, which makes the verdict `unknown` and applies no
  label. "We couldn't check" must never render as "we checked and it was fine" —
  and a verdict rule that only counted failures would have done exactly that.
- **Blocking is a small, boring set.** Adding a judgement call to it costs more
  than the finding is worth: the gate goes red on every PR and stops being read.
- **The marker is the only inter-stage state**, and the idempotency guard. Stage 3
  never parses prose — including the executor's own report, whose counts are read
  from its marker, not from its English.
- **Codes, not titles.** Reviewers edit titles in QA Touch; `TR####` never changes.
- **No QA Touch run, no problem.** The PR report is the primary output; statuses
  are the secondary one. A round that cannot resolve a run still reports.
- **Provision the way the unit tests provision.** One `testing-setup` for both, so
  the browser round tests the schema and seed data the rest of CI tests, and there is
  one place to fix when migrations change. Where browser testing genuinely needs
  more — built assets, an APP_URL, a licence — the difference is documented above
  rather than left as folklore in a shell script.
