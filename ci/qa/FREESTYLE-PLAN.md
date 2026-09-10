# Porting the AI first round to Faveo Community

Implementation plan: the paid version's AI first-round QA pipeline, hosted on
Jenkins **Freestyle** jobs, against the Community application.

The testing methodology transfers intact. The Jenkins orchestration does not, and
neither do the parts that assume an application Community does not have.

Verified against the advance repository at `/var/www/html/favins`
(`Jenkinsfile.qa`, 2,220 lines; `ci/qa/`, 30 files) and the Community working tree
at `/var/www/html/faveocommunity` (remote `faveosuite/faveo-helpdesk`).
Where this plan says a Community component is absent, it was checked in that tree
rather than assumed.

---

## Findings first

### 1. Community has no `ci/` and no `Jenkinsfile`

There is no `ci/` directory and no `Jenkinsfile*` of any kind in the Community
repo. **This is not a port, it is a first import.** Every script has to be copied
in from the advance repo as new files, which makes §12 the largest section here.

### 2. Community is a different application, not just a different Jenkins

Five load-bearing assumptions in Stage 3 are false here:

- **No API surface** — `routes/api.php` holds one stub route, `/user/test`
- **No Vue SPA** — no `resources/assets`, no `public/build`; the UI is Blade
- **No plugins** — no plugins directory, no plugins table
- **No licensing** — no `CheckValidLicense`, no `faveo_license`
- **No `phpunit.dusk.xml`** — `laravel/dusk` is a dev dependency and
  `DuskTestCase.php` exists, but there is no suite config

Roughly a third of the paid Stage 3 has nothing to run against. Freestyle is the
smaller of the two problems.

### 3. `testing-setup` is a different command with the same name

Community's `SetupTestEnv` signature is:

```
testing-setup {--username=} {--password=} {--database=}
```

**No licence arguments.** It writes `.env` directly rather than `.env.testing` /
`.env.dusk.testing`, and it sets `APP_ENV=development`. Critically,
`createEnv()` **returns early if `.env` already exists** ("Environment file
already exists. It is assumed that username and password in the file is
correct"). On a reused Jenkins workspace that means your DB credentials are
silently not written.

**Provisioning must delete `.env` before calling it.**

### 4. Freestyle is workable; teardown is the one real risk

~29 shell entry points are callable from an *Execute shell* step; ~8 Groovy
behaviours need rewriting. The one you cannot approximate is the Pipeline's
`finally`. A Freestyle post-build task that runs regardless of result is close,
**but it does not run when a build is aborted** — so plan a separate janitor job
as well.

---

## 1. The current paid-version flow

Three stages, and the gap between 1 and 3 is deliberate: the same model authors
and executes, so without a person in between a bad case would author itself into
a passing result.

```
GitHub issue
  │  label: QA: Test case needed
  ▼
STAGE 1 — author            fetch-issue · code-map · kb-sync · fetch-attachments
  │                         claude -p (no credentials held)  →  cases.json
  │                         stage1-publish.sh (holds QA Touch token, reads no prose)
  ▼
QA Touch  +  hidden marker comment on the issue
  │
  ▼
STAGE 2 — a person          QA Lead reviews cases, applies "QA: Test case Approved"
  │
  ▼
Developer opens PR  "Fixes #<issue>"
  │  label: Requires Functionality Review :pray:
  ▼
STAGE 3 — execute           stage3-gate.sh   → 0 go · 3 skip · 4 stop
  │                         provisionInstance → disposable app + qa_pr_<n>
  │                         probes/api.sh, probes/security.sh, summarize.sh
  │                         claude -p (holds QA Touch token, NO GitHub token)
  │                         publishReport → decideVerdict → applyVerdictLabels
  ▼
PR comment (upserted, never stacked) + verdict label + teardown
```

### Which layer owns what

| Layer | Where it lives | Portable? |
|---|---|---|
| **AI testing logic** | `stage1-author-prompt.md`, `stage3-prompt.md` — the briefs. All judgement lives here | Adapt copy |
| **Shell scripts** | ~29 entry points under `ci/qa/`, all configured through environment variables | Mostly ships |
| **Pipeline/Groovy** | ~8 behaviours in `Jenkinsfile.qa`'s `def` blocks: provision, execute, publish, verdict, browser preflight, teardown, work resolution, failure classification | Rewrite |
| **External services** | GitHub API, QA Touch API, Anthropic API, npm registry (Playwright MCP fetched per run) | Unchanged |
| **Credentials** | 7 Jenkins credentials, bound per-step by `withCredentials` | Rebind |
| **GitHub integration** | `gh-client.sh`, `label.sh`, `marker.sh`, `upsertPRComment` | Ships + 1 rewrite |
| **QA Touch integration** | `qatouch-client.sh` (33 functions), `qatouch-facts.md` | Ships |

> **The security split that makes this safe.** The authoring agent consumes
> untrusted issue text and holds *no* credential — its only output is a JSON
> file. The executor holds the QA Touch token but *no* GitHub token — it writes
> to a file and the pipeline publishes. A fully successful prompt injection
> therefore reaches neither service. This is the single thing a one-job Freestyle
> build gives away.

---

## 2. What changes for Faveo Community

Two independent axes of change. Conflating them is how this goes wrong — keep
them separate in your head and in your commits.

### Axis A — must change because Jenkins is Freestyle

| Concern | Paid version | Community |
|---|---|---|
| Jenkins type | Pipeline, script from SCM | Freestyle project |
| Orchestration | `Jenkinsfile.qa`, declarative Groovy | Ordered *Execute shell* steps + wrapper scripts |
| Parameters | `parameters { }`, auto-registered | *This project is parameterized*, recreated by hand |
| Trigger | `triggers { GenericTrigger(…) }` in code | Same plugin, configured in the UI |
| Credentials | `withCredentials` per step; conditional (OAuth *or* API key) | *Use secret text(s) or file(s)*, bound for the whole build |
| Node selection | `agent { label params.QA_AGENT_LABEL }` | Static *Restrict where this project can be run* |
| Teardown | `finally` — runs on abort | Post-build task + a separate janitor job |
| Skip semantics | `return` from a `def`, build stays green | `exit 0` plus a sentinel file later steps check |
| Stage view | Named stages with timings | One flat console log; `phase-summary.sh` substitutes |

### Axis B — must change because Community is a different application

This axis is **not** in the reference document, because that document was written
against the advance repo. Every row below was verified against the Community
working tree.

| Component | Assumes | Community reality | Action |
|---|---|---|---|
| `probes/api.sh` (12 checks) | `/api/*` and `/v3/api/*` product API | `routes/api.php` has one stub route | **Rewrite or drop** |
| `probes/security.sh` (19 checks) | Half generic, half `/api/admin/*` boundary | Exposed-file, debug, CSRF, header, cookie and session checks apply; the `/api/admin/*` cross-role checks have no endpoints | **Split it** |
| `enable-api.php` | A v3 API feature flag | No v3 API exists | Drop |
| `activate-plugins.php` | A `plugins` table and directory | No plugins | Drop |
| `relax-lockout.php` | An `attempt_locks` table | Not present | Drop |
| Licence handling | `CheckValidLicense` → `/licenseError`; four segments to `testing-setup` | No licensing; `testing-setup` takes no segments | Drop the whole branch |
| `instance-health.sh` | Exit 3 means unlicensed; login page must reference a Vue SPA entry script under `build/v<version>/entry/*.js` | Exit 3 unreachable; Community serves plain Blade, so the shared script's SPA check fails on a healthy instance | **Superseded for Community** — use `ci/qa/instance-health-community.sh` (checks for the real Blade login form instead); the shared file stays imported unchanged per `IMPORTED-FROM` and is not called by `freestyle/provision.sh` |
| `yarn build` step | Vue SPA at `public/build/v<version>/` | Blade views under `themes.default1` | Verify, likely drop |
| Dusk suites | `phpunit.dusk.xml`, `license-verify`, `dusk-changed.sh` | No suite config | Drop for v1 |
| `seed-qa-users.php` | Advance's users/roles schema | Roles exist as `users.role` (`CheckRole` tests `== 'admin'`); schema differs | Rewrite |
| `stage3-prompt.md` "Where the screens are" | Two Vue catch-alls that answer 200 for any path | Conventional Blade routes; a wrong path 404s honestly | **Rewrite that section** |
| `tls-proxy.php` | `URL::forceScheme('https')` is unconditional | **Also true here** — `AppServiceProvider.php:43` | **Ships as-is** |
| `testing-setup` invocation | Writes `.env.testing`, `.env.dusk.testing`; `APP_ENV=testing` | Writes `.env` only, `APP_ENV=development`, **skips if `.env` exists** | New provisioning sequence |
| `REPO_NAME` | `faveo-helpdesk-advance` | `faveo-helpdesk` | One variable |

### Must *not* change — the testing methodology

None of the above touches what the round *does*. All fourteen behaviours survive
intact: AI authoring, QA Touch storage, human approval, PR-triggered rounds, the
seven-condition gate, executing approved cases, deterministic probes, browser and
API testing where applicable, result collection, QA Touch writes, report
generation, GitHub labelling, and cleanup. What changes is which endpoints the
probes point at and which shell the steps run in.

> **The one methodology risk.** Dropping `probes/api.sh` and half of
> `probes/security.sh` reduces the probe count from ~35 to roughly 10–12. That is
> honest — Community has less attack surface — but it changes what a `pass`
> verdict means. **Do not let the reduced set quietly imply the same coverage.**
> Say the count in the report header, as `summarize.sh` already does.

---

## 3. The Freestyle job design

**Three jobs, not one.** A single job binds credentials for the whole build and
so destroys the authoring/executor split. Separate jobs restore it at no real
cost, because the stages never run in the same build anyway.

```
faveo-qa-author       Stage 1   binds: GitHub, QA Touch, Anthropic
faveo-qa-firstround   Stage 3   binds: GitHub, MySQL, QA Touch, Anthropic
faveo-qa-janitor      cleanup   binds: MySQL          (scheduled, see §6)
```

Both QA jobs take the same webhook. Each decides in its first build step whether
the payload is *its* work and exits 0 quietly if not — the same rule the
Pipeline's `resolveWork()` applies, moved into shell.

### General

- **Restrict where this project can be run** → the node with PHP, Composer, MySQL
  client, Node/npm, Chrome and the `claude` CLI. Freestyle has no parameterised
  node selection, so this replaces `QA_AGENT_LABEL`.
  *Why:* a round scheduled onto a node without Chrome blocks every UI case for a
  reason that has nothing to do with the PR.
- **Do not tick** *Execute concurrent builds*.
  *Why:* two rounds on one node collide on the serve port and on `qa_pr_<n>`; it
  also replaces `disableConcurrentBuilds()`.
- **Discard old builds** → 50 builds.
  *Why:* matches `logRotator(numToKeepStr: '50')`; archived evidence is large.
- **Build Environment → Abort the build if it's stuck** → 90 minutes.
  *Why:* the executor's budget clamp is computed *from* this number; without it a
  hung agent holds the node indefinitely.
- **Build Environment → Delete workspace before build starts** → **leave OFF.**
  *Why:* the round reuses the workspace deliberately for the Composer and Yarn
  caches. Stale-file hygiene is handled per-step instead (§6, step 04).

### Source Code Management

- **Git**, repository `https://github.com/faveosuite/faveo-helpdesk.git`,
  credentials `faveobot`.
- **Branch Specifier:** `*/development`.
  *Why this matters more than it looks:* the job reads `ci/qa/**` from whatever
  it checks out, so **the branch you configure is the pipeline you run**.
  Pointing it at a stale branch silently runs a stale pipeline while the branch
  you are editing has no effect — the most common setup mistake in the paid
  version, and Freestyle does nothing to protect you from it.
- **Additional Behaviours → Advanced clone behaviours**: fetch tags off, shallow
  depth if the clone is slow.
  *Why:* Stage 3 fetches the PR merge ref explicitly in step 03.
- **Do not** add *Check out to a sub-directory*. Step 01 snapshots `ci/qa` out of
  the workspace instead; that separation is load-bearing.

### Build Triggers → Generic Webhook Trigger

Same plugin the Pipeline declares in code, configured in the UI. Post content
parameters, all JSONPath:

| Variable | Expression | Default | Needed for |
|---|---|---|---|
| `gh_action` | `$.action` | — | Filter text |
| `gh_label` | `$.label.name` | empty | Filter text; which stage |
| `gh_issue` | `$.issue.number` | empty | Stage 1 target |
| `gh_pr` | `$.pull_request.number` | empty | Stage 3 target |
| `gh_check_pr` | `$.check_suite.pull_requests[0].number` | empty | Stage 3 when CI goes green |
| `gh_is_pr` | `$.issue.pull_request.url` | empty | **Tells a PR apart from an issue** |
| `gh_sender` | `$.sender.login` | empty | **Self-trigger loop guard** |

Token: `faveo-qa-pipeline`. Optional filter — text `$gh_action|$gh_label`,
expression exactly as the Pipeline declares it:

```
^(labeled\|(QA: Test case needed|Requires Functionality Review :pray:|Code Approved :heart_eyes_cat:)|submitted\||completed\||synchronize\|)$
```

> **Do not omit `gh_is_pr` and `gh_sender`.** They are the two that prevent real
> damage. Without `gh_is_pr` a PR label is treated as an issue and **authoring
> runs against a pull request body**. Without `gh_sender` the failure path
> re-applies the trigger label, GitHub fires `labeled`, the job runs again — an
> indefinite loop spending tokens on every iteration.

### Build parameters

Tick *This project is parameterized* and add:

| Name | Type | Default | Why |
|---|---|---|---|
| `QA_STAGE` | Choice: `auto`, `author`, `execute` | `auto` | Manual re-runs; `auto` reads the payload |
| `QA_NUMBER` | String | empty | Issue (author) or PR (execute); blank sweeps |
| `QA_DRY_RUN` | Boolean | `false` | Stage 1 safe test (§10 level 4) |
| `QA_GATE_EXPLAIN` | Boolean | `false` | Stage 3 diagnosis (§10 level 3) |
| `QA_SKIP_CHECKS` | Boolean | `false` | Run with red CI; report says so on the PR |
| `QA_TEST_RUN_KEY` | String | empty | Write statuses into a specific QA Touch run |

Drop `QA_AGENT_LABEL` (no parameterised node selection) and `QA_DUSK_SUITE` (no
`phpunit.dusk.xml` in Community).

### Environment variables

Freestyle has no `environment { }`. Use *Build Environment → Inject environment
variables* (EnvInject), or a `ci/qa/env.community.sh` that step 01 sources.
**Prefer the file** — it is version-controlled, reviewable, and works identically
when you run locally.

```bash
# ci/qa/env.community.sh — no secrets, ever
export REPO_NAME=faveo-helpdesk
export GITHUB_REPO=faveosuite/faveo-helpdesk
export QA_BOT_LOGIN=faveobot          # must match the faveobot account's login
export QATOUCH_PROJECT=NEXQ            # "Faveo Helpdesk Community", confirmed
export QATOUCH_CASE_PREFIX=<your UI prefix>   # still to confirm — see note below
export QA_SERVE_PORT=8099
export QA_EXEC_MINUTES=50
export QA_CASE_BUDGET_MINUTES=4
export QA_CLAUDE_BIN=claude
export QA_CLAUDE_MODEL=claude-opus-5
export QA_CLAUDE_EFFORT=medium
export QA_EXECUTOR_MODEL=claude-opus-5
export QA_EXECUTOR_EFFORT=high
export QA_CI_SETS_VERDICT=true
export QA_TRIGGER_LABEL='QA: Test case needed'
export QA_PROGRESS_LABEL='QA: Test case In Progress'
export QA_DONE_LABEL='QA Test Cases Added'
export QA_PR_TRIGGER_LABEL='Requires Functionality Review :pray:'
export QA_CODE_APPROVED_LABEL='Code Approved :heart_eyes_cat:'
export QA_MANUAL_LABEL='Manual'
export QA_ROUND1_PROGRESS_LABEL='QA: Round 1 Testing In Progress'
export QA_ROUND1_PASS_LABEL='QA: Round 1 Testing Approved :clap:'
export QA_CORRECTION_LABEL='Functionality Correction :facepunch:'
```

> **`QATOUCH_PROJECT` is resolved.** Community has its own QA Touch project —
> **Faveo Helpdesk Community**, key **`NEXQ`**, 180 existing test cases. It is
> *not* the advance project (`MeLq`), so Stage 1 will not pollute the 8,200-case
> advance library. Good.
>
> **`QATOUCH_CASE_PREFIX` is still unknown.** The API reports every case as
> `TR<number>` while the UI shows `<prefix>-<number>` — the same case. Open any
> case in the NEXQ project in the QA Touch UI and read the code it displays; the
> letters before the dash are the prefix. Get this wrong and the issue comment
> tells a tester to search for a code the UI cannot find. Leave it empty to
> publish the raw `TR` form instead.
>
> **NEXQ has 0 test runs.** A run must already exist for statuses to be written —
> `POST /testRun` is unusable (see `qatouch-facts.md`). Before the first real
> round, create one in the UI named exactly
> `First round — PR #<pr> (issue #<issue>)`, or pass its key as
> `QA_TEST_RUN_KEY`. Without it the round still executes and still reports on the
> PR; it just writes no statuses, and says so at the top of the report.
>
> **The 180 existing cases matter for module selection.** `module-list.sh` reads
> the project's existing modules and hands the authoring agent the list, so cases
> land in an existing feature area rather than inventing a 181st module. Run it
> against NEXQ at level L2 to confirm it returns those modules.

### Post-build actions

- **Archive the artifacts:** `qa-report-*.md, qa-probe-*.json, qa-probe-*.md,
  qa-probe-*-*.jsonl, qa-evidence-*/**, qa-serve-*.log, qa-context-*.json`.
  Tick *Do not fail build if archiving returns nothing*.
  *Why:* a skipped build produces none of these and must still be green.
- **Post-build Task** (or Post Build Script plugin) → run
  `ci/qa/freestyle/teardown.sh`, configured to **run regardless of build result**.
  *Why:* this is the `finally` substitute.
- **Do not** add *Editable Email Notification*. The round reports on the PR; a
  second channel splits the audience.

### Workspace requirements

- Disk for a full Composer install plus a MySQL database per build — budget ~2 GB
  (rounds are serialised).
- The MySQL account needs `CREATE`/`DROP` on `qa_pr_%`.
- Outbound reachability to `api.github.com`, `api.qatouch.com`,
  `api.anthropic.com` **and the npm registry** — `mcp-ci.json` launches
  `npx -y @playwright/mcp@latest`, which hits the registry on *every* run.

---

## 4. Credentials

Six required, one optional pair, one dropped. Nothing here is invented — each
maps to a binding that exists in `Jenkinsfile.qa` or a variable a `ci/qa` script
reads.

| ID | Jenkins type | Exposed as | Used for | Consumed by |
|---|---|---|---|---|
| `faveobot` | Username with password (password = GitHub PAT, repo write) | `GITHUB_USER`, `GITHUB_TOKEN` | Read issues and PRs, post/update comments, move labels, clone | `gh-client.sh`, `label.sh`, `marker.sh`, `stage3-gate.sh`, `discover.sh`, your `publish-report.sh` |
| `mysql_credentials_id` | Username with password | `DB_USER`, `DB_PASS` | Create and drop `qa_pr_<n>` | your `provision.sh` / `teardown.sh`; passed to `testing-setup` |
| `qatouch-api-token` | **Secret text** | `QATOUCH_API_TOKEN` | Read cases, write run results | `qatouch-client.sh`, `stage1-publish.sh`, `module-list.sh`, the executor |
| `qatouch-domain` | **Secret text** | `QATOUCH_DOMAIN` | Tenant subdomain sent as the `domain:` header | `qatouch-client.sh` |
| `anthropic-oauth-token` *or* `anthropic-api-key` | Secret text | `CLAUDE_CODE_OAUTH_TOKEN` *or* `ANTHROPIC_API_KEY` | Billing the authoring and executor agents | `claude` CLI, both stages |
| `qa-instance-admin`, `qa-instance-agent` | Username with password (**optional**) | `QA_ADMIN_EMAIL`/`_PASSWORD`, `QA_AGENT_EMAIL`/`_PASSWORD` | Fixed logins on the disposable instance | Probes and executor, via `qa-instance-creds-<n>.sh` |
| ~~`qa-license-code`~~ | **Not needed** | — | Community has no licensing and `testing-setup` takes no segments. **Do not create this credential.** | — |

### Rules for every one of them

- **Never committed to Git.** All eight are secrets. `env.community.sh` holds
  configuration only — that is the point of separating them.
- **All environment-variable based.** Every `ci/qa` script reads its credentials
  from the environment; that design decision is what makes them runnable outside
  Jenkins, and what `local-stage3.sh` depends on.
- **Never on a command line.** The one unavoidable exception is
  `mysql -u"$DB_USER" -p"$DB_PASS"`, visible in `ps` for the life of the call.
  Prefer a `--defaults-extra-file` written with `umask 077` and deleted in
  teardown if your node is multi-tenant.
- **The instance logins are genuinely optional.** If absent, generate one password
  per round with `openssl rand`, write it to `qa-instance-creds-<n>.sh` under
  `umask 077`, and delete it in teardown. It then exists nowhere else and dies
  with the database.

---

## 5. Where credentials live, and how the build reaches them

```
Jenkins
  └─ Manage Jenkins
      └─ Credentials
          └─ System  →  Global credentials (unrestricted)
              ├─ Username with password   faveobot
              ├─ Username with password   mysql_credentials_id
              ├─ Username with password   qa-instance-admin      (optional)
              ├─ Username with password   qa-instance-agent      (optional)
              ├─ Secret text              qatouch-api-token
              ├─ Secret text              qatouch-domain
              └─ Secret text              anthropic-oauth-token  or anthropic-api-key
```

**Global, not per-job** — unless you put the three jobs in a folder, in which case
scope them to that folder and nothing else can read them. Three jobs sharing one
GitHub token means one thing to rotate.

### Reaching them from a Freestyle build

*Build Environment → Use secret text(s) or file(s)*:

| Binding kind | Credential | Variable(s) |
|---|---|---|
| Username and password (separated) | `faveobot` | `GITHUB_USER`, `GITHUB_TOKEN` |
| Username and password (separated) | `mysql_credentials_id` | `DB_USER`, `DB_PASS` |
| Secret text | `qatouch-api-token` | `QATOUCH_API_TOKEN` |
| Secret text | `qatouch-domain` | `QATOUCH_DOMAIN` |
| Secret text | `anthropic-oauth-token` | `CLAUDE_CODE_OAUTH_TOKEN` |

**Use "separated", not "combined".** The combined form produces `user:pass` in one
variable, which every consuming script would then have to split — and a split
that goes wrong puts half a token in a log.

### Keeping them out of the four places they leak

- **Git** — nothing secret in `env.community.sh`; add `qa-instance-creds-*.sh`,
  `qa-*.jsonl`, `qa-evidence-*/` and `.env.qa-original` to `.gitignore`.
- **Shell scripts** — read from the environment; never accept a secret as a
  positional argument.
- **Console logs** — Jenkins masks a bound secret as `****` automatically, *but
  only if it is non-empty*. An empty credential passes the binding and then fails
  the request with nothing in the log to explain it. Add an explicit non-empty
  assertion in step 00, exactly as the paid Preflight does.
- **Command lines** — use `curl -H "Authorization: token $GITHUB_TOKEN"` (expanded
  by the shell, not written into `argv` by you) and prefer `-d @file.json` over
  inline bodies.

> **The two-job split, concretely.** `faveo-qa-author` binds GitHub + QA Touch +
> Anthropic but **never** MySQL. `faveo-qa-firstround` binds all four. Within the
> author job the `claude` invocation still runs in its own *Execute shell* step —
> and because Jenkins bindings are build-scoped, the token is visible to it. To
> restore the paid guarantee fully, `unset QATOUCH_API_TOKEN GITHUB_TOKEN` before
> `exec`ing the CLI. **Do the `unset`; it costs one line and recovers most of the
> protection.**

---

## 6. Build steps, in order

One *Execute shell* step per phase, so a failure names its phase in the console
log. Each step begins `set -euo pipefail` and sources the tools. These are the
**Stage 3** job; Stage 1 is shorter and follows the same shape.

> **The skip sentinel.** Freestyle has no early `return`. When the gate says "not
> eligible" the build must go **green and stop**. Do it with a sentinel file: step
> 02 writes `qa-skip` and exits 0; every later step begins
> `[ -f qa-skip ] && exit 0`. Do not use *Exit code to set build unstable* — a
> skip is not unstable, and this job wakes for every check event in the repo.

### 00 — Preflight and work resolution

Replaces the Pipeline's *Preflight* stage and `resolveWork()`. Fails fast and
cheaply, before anything is labelled or built.

```bash
set -euo pipefail
. ci/qa/env.community.sh
rm -f qa-skip
echo "BUILD_START=$(date +%s)" > qa-number.env

# tooling — fatal
for b in curl jq "$QA_CLAUDE_BIN" php composer mysql node npm; do
  command -v "$b" >/dev/null || { echo "$b not on PATH: $PATH" >&2; exit 1; }
done

# non-empty credential assertion — a set-but-empty secret masks as nothing
[ -n "${GITHUB_TOKEN:-}" ]      || { echo "GITHUB_TOKEN empty" >&2; exit 1; }
[ -n "${QATOUCH_API_TOKEN:-}" ] || { echo "QATOUCH_API_TOKEN empty" >&2; exit 1; }
case "${QATOUCH_DOMAIN:-}" in
  ''|*://*|*.*) echo "qatouch-domain must be the bare subdomain" >&2; exit 1 ;;
esac

# self-trigger loop guard — the Pipeline's resolveWork() rule
if [ -n "${gh_sender:-}" ] && [ "$gh_sender" = "$QA_BOT_LOGIN" ]; then
  echo "our own label change — ignoring"; touch qa-skip; exit 0
fi

# is this build's payload MY stage? gh_is_pr distinguishes issue from PR
bash ci/qa/freestyle/resolve-work.sh execute >> qa-number.env || { touch qa-skip; exit 0; }
. qa-number.env
```

*Needs:* `GITHUB_TOKEN`, `QATOUCH_*`. *On failure:* red — misconfiguration.
*Next step:* only if no `qa-skip`.

### 01 — Snapshot the tools

Copy `ci/qa` out of the workspace **before** anything checks out the merge ref, so
the pull request cannot rewrite the pipeline that judges it — and so the probes
still exist on a PR branched from before `ci/qa` was added.

```bash
[ -f qa-skip ] && exit 0
set -euo pipefail
. ci/qa/env.community.sh; . qa-number.env

export QA_TOOLS="$WORKSPACE-qa-tools"
rm -rf "$QA_TOOLS" && mkdir -p "$QA_TOOLS"
cp -r ci/qa/. "$QA_TOOLS"/
echo "export QA_TOOLS=$QA_TOOLS" >> qa-number.env

# phase timing — the stage-view substitute
rm -f "qa-phases-$QA_NUMBER.tsv"
printf '%s\t%s\n' "$(date +%s)" 'gate + tools snapshot' >> "qa-phases-$QA_NUMBER.tsv"
```

*Output:* `$WORKSPACE-qa-tools/`. *On failure:* red.

### 02 — The gate (reuse `stage3-gate.sh` unchanged)

Ships as-is. Exit `3` means not eligible and must end the build **green**; exit
`4` is a malformed marker or misconfiguration and must go red.

```bash
[ -f qa-skip ] && exit 0
set -uo pipefail
. ci/qa/env.community.sh; . qa-number.env

export QA_GATE_EXPLAIN="${QA_GATE_EXPLAIN:-0}"
export QA_GATE_SKIP_CHECKS="${QA_SKIP_CHECKS:-0}"

bash "$QA_TOOLS"/stage3-gate.sh "$QA_NUMBER" \
  > "qa-context-$QA_NUMBER.json" 2> "qa-gate-$QA_NUMBER.err"
rc=$?
cat "qa-gate-$QA_NUMBER.err"
[ "$rc" = 3 ] && { touch qa-skip; exit 0; }
[ "$rc" = 0 ] || exit 1
bash "$QA_TOOLS"/label.sh add "$QA_NUMBER" "$QA_ROUND1_PROGRESS_LABEL" || true
```

*Ships unchanged:* yes. *Output:* `qa-context-<n>.json`. *On skip:* green, later
steps no-op.

### 03 — Provision the disposable instance (**write this**)

The substantial piece, and where Community diverges most. Read `provisionInstance`
and `local-stage3.sh` side by side, then write a **Community-specific** sequence —
the licence, plugin, v3-API, lockout and SPA steps all come out; the `.env`
pre-delete goes in.

```bash
[ -f qa-skip ] && exit 0
set -euo pipefail
. ci/qa/env.community.sh; . qa-number.env
bash "$QA_TOOLS"/freestyle/provision.sh "$QA_NUMBER"
```

What `provision.sh` must do, in order:

1. `git fetch origin pull/$n/merge` (fall back to `/head`); checkout
2. `fuser -k` on the port, in case a prior build died
3. `cp .env .env.qa-original` then **`rm -f .env`** ← REQUIRED;
   `SetupTestEnv::createEnv()` returns early if `.env` exists
4. `composer install --no-interaction --prefer-dist`
5. `mysql DROP DATABASE IF EXISTS qa_pr_$n`
6. `php artisan testing-setup --username --password --database`
   (**no licence segments** — Community's signature has none)
7. Set `APP_URL` in `.env` to the https base
8. Seed the `qa-admin` / `qa-agent` users (Community schema)
9. `php artisan serve --no-reload --port $((port+100))`
10. `openssl` self-signed cert + `tls-proxy.php` on `$port`
    — required: `AppServiceProvider.php:43` forces https
11. Poll up to 30s for 200/301/302, then `instance-health-community.sh` (not
    the shared `instance-health.sh` — see the table above)

*Health:* 0 ok · 4 unusable (3 unreachable in Community).
*On failure:* comment on the PR, run teardown, exit 1.

### 04 — Probes (**split and adapt**)

`summarize.sh` ships unchanged. `security.sh` needs its `/api/admin/*` checks
separated out; `api.sh` has nothing to test until Community grows an API.

```bash
[ -f qa-skip ] && exit 0
set -uo pipefail
. ci/qa/env.community.sh; . qa-number.env
. "$WORKSPACE/qa-instance-creds-$QA_NUMBER.sh"

# MANDATORY: the workspace is reused and summarize.sh takes a GLOB,
# so a stale file is summarised as though this build produced it.
rm -f qa-probe-"$QA_NUMBER"-*.jsonl \
      qa-probe-"$QA_NUMBER".json qa-probe-"$QA_NUMBER".md

export PROBE_INSECURE=1
export QA_SESSION_JAR_DIR="$WORKSPACE/qa-session-$QA_NUMBER"
mkdir -p "$QA_SESSION_JAR_DIR"

bash "$QA_TOOLS"/probes/security-community.sh \
     "$QA_BASE_URL" qa-probe-"$QA_NUMBER"-security.jsonl
bash "$QA_TOOLS"/probes/summarize.sh \
     qa-probe-"$QA_NUMBER" qa-probe-"$QA_NUMBER"-*.jsonl
exit 0   # a finding is data, not a build failure
```

*Ships unchanged:* `lib.sh`, `summarize.sh`.
*Order note:* if you restore `api.sh`, it must run **before** `security.sh` —
`security.sh` sends deliberately-bad credentials and `api.sh` logs in for real.

### 05 — Browser preflight and the executor (**write this**)

The budget clamp is what stops one case eating the build. The hard stop must land
*before* Jenkins aborts the job, or you lose both the report and the teardown.

```bash
[ -f qa-skip ] && exit 0
set -uo pipefail
. ci/qa/env.community.sh; . qa-number.env
. "$WORKSPACE/qa-instance-creds-$QA_NUMBER.sh"

# is the MCP browser actually drivable? tells "no npm egress" apart
# from "server started but no tool call completed"
bash "$QA_TOOLS"/freestyle/browser-preflight.sh "$QA_NUMBER" || true
[ -f "qa-browser-unavailable-$QA_NUMBER.txt" ] && export QA_BROWSER_FALLBACK=1

export QA_CONTEXT="$(cat "qa-context-$QA_NUMBER.json")"
export QA_REPORT_FILE="$WORKSPACE/qa-report-$QA_NUMBER.md"
export QA_PROBE_FILE="$WORKSPACE/qa-probe-$QA_NUMBER.json"
export QA_EVIDENCE_DIR="$WORKSPACE/qa-evidence-$QA_NUMBER"
rm -f "$QA_REPORT_FILE" "qa-exec-failure-$QA_NUMBER.txt"

# clamp to what is left of the 90-minute job timeout, minus 15 for
# publishing and teardown. BUILD_START comes from step 00.
mins=${QA_EXEC_MINUTES:-50}; grace=10
ceil=$(( 90 - ( $(date +%s) - BUILD_START ) / 60 - 15 ))
[ "$ceil" -lt 7 ] && ceil=7
[ $((mins + grace)) -gt "$ceil" ] && mins=$((ceil - grace))
[ "$mins" -lt 5 ] && { mins=5; grace=2; }
export QA_EXEC_DEADLINE=$(( $(date +%s) + mins * 60 ))

# the executor must NOT hold a GitHub token
unset GITHUB_TOKEN GITHUB_USER

timeout -k 30s "$((mins + grace))m" "$QA_CLAUDE_BIN" -p "$(cat "$QA_TOOLS"/stage3-prompt.md)

Your context is in the QA_CONTEXT environment variable.
Write your report as markdown to \$QA_REPORT_FILE — do not post it yourself." \
  --allowed-tools "Read,Grep,Glob,Write,Bash,ToolSearch,mcp__playwright" \
  --mcp-config "$QA_TOOLS"/mcp-ci.json --strict-mcp-config \
  ${QA_EXECUTOR_MODEL:+--model "$QA_EXECUTOR_MODEL"} \
  ${QA_EXECUTOR_EFFORT:+--effort "$QA_EXECUTOR_EFFORT"} \
  --output-format stream-json --verbose < /dev/null \
  > "qa-exec-stream-$QA_NUMBER.jsonl"
rc=$?

# exit 0 is NOT success — read the final result event
sub=$(jq -r 'select(.type=="result").subtype // "?"' "qa-exec-stream-$QA_NUMBER.jsonl" | tail -1)
[ "$sub" = "success" ] || rc=70
bash "$QA_TOOLS"/freestyle/classify-failure.sh "$rc" "qa-exec-stream-$QA_NUMBER.jsonl" \
  > "qa-exec-failure-$QA_NUMBER.txt"
exit 0   # let step 06 report what happened
```

*Failure kinds:* 124/137 = timeout · stream keyword match = limit · else other.
*Reset time:* `limit-reset.sh` ships unchanged.

### 06 — Verdict, report and labels (**write this**)

Three Groovy functions in shell. The counts come from the executor's **machine
marker**, never from its prose — that is what keeps the verdict mechanical.

```bash
[ -f qa-skip ] && exit 0
set -uo pipefail
. ci/qa/env.community.sh; . qa-number.env

# counts from <!-- qa-first-round {...} --> on the last line of the report
counts=$(grep -o '<!-- qa-first-round .* -->' "qa-report-$QA_NUMBER.md" \
         | sed 's/^<!-- qa-first-round //; s/ -->$//')
pv=$(jq -r '.verdict' "qa-probe-$QA_NUMBER.json")
f=$(jq -r '.failed//0' <<<"$counts"); p=$(jq -r '.passed//0' <<<"$counts")
b=$(jq -r '.blocked//0' <<<"$counts")

# decideVerdict, verbatim from Jenkinsfile.qa
if   [ "$pv" = correction ];                            then v=correction
elif [ -z "$counts" ];                                  then v=unknown
elif [ "$f" -gt 0 ];                                    then v=correction
elif [ "$p" -gt 0 ] && [ "$b" = 0 ] && [ "$pv" = pass ]; then v=pass
else                                                         v=unknown; fi

cat qa-banner.md "qa-probe-$QA_NUMBER.md" "qa-report-$QA_NUMBER.md" > qa-body.md
bash "$QA_TOOLS"/freestyle/upsert-comment.sh "$QA_NUMBER" qa-first-round qa-body.md

case "$v" in
  pass)       bash "$QA_TOOLS"/label.sh add    "$QA_NUMBER" "$QA_ROUND1_PASS_LABEL"
              bash "$QA_TOOLS"/label.sh remove "$QA_NUMBER" "$QA_CORRECTION_LABEL" ;;
  correction) bash "$QA_TOOLS"/label.sh add    "$QA_NUMBER" "$QA_CORRECTION_LABEL"
              bash "$QA_TOOLS"/label.sh remove "$QA_NUMBER" "$QA_ROUND1_PASS_LABEL" ;;
  *)          echo 'inconclusive — no label applied' ;;
esac
```

*Ships unchanged:* `label.sh`. **Never** label on `unknown`.

### 07 — Teardown (**post-build, runs regardless of result**)

Not a build step. A *Post-build Task* configured to run whatever the outcome was.
This is the `finally` substitute and the step you cannot skip.

```bash
. ci/qa/env.community.sh; . qa-number.env 2>/dev/null || exit 0
[ -n "${QA_NUMBER:-}" ] || exit 0

# the label that actively lies if left behind
bash "$QA_TOOLS"/label.sh remove "$QA_NUMBER" "$QA_ROUND1_PROGRESS_LABEL" || true

kill "$(cat "qa-serve-$QA_NUMBER.pid" 2>/dev/null)" 2>/dev/null || true
kill "$(cat "qa-proxy-$QA_NUMBER.pid" 2>/dev/null)" 2>/dev/null || true
fuser -k "${QA_ACTIVE_PORT:-$QA_SERVE_PORT}"/tcp 2>/dev/null || true
fuser -k "${QA_APP_PORT:-8199}"/tcp             2>/dev/null || true
mysql -u"$DB_USER" -p"$DB_PASS" -e "DROP DATABASE IF EXISTS qa_pr_$QA_NUMBER;" || true

[ -f .env.qa-original ] && mv -f .env.qa-original .env || rm -f .env
rm -f "qa-instance-creds-$QA_NUMBER.sh" qa-tls-"$QA_NUMBER".{pem,crt,key}
git checkout -q - 2>/dev/null; git branch -qD "qa-pr-$QA_NUMBER" 2>/dev/null
rm -rf "$WORKSPACE-qa-tools"

printf '%s\tend\n' "$(date +%s)" >> "qa-phases-$QA_NUMBER.tsv"
bash ci/qa/phase-summary.sh "qa-phases-$QA_NUMBER.tsv" || true
```

*Runs:* success, failure, unstable. *Does NOT run:* on abort — see below.

> **The janitor job — the gap a post-build task cannot close.** A Post-build Task
> does not run when a build is **aborted**, and abort is exactly what happens when
> the 90-minute timeout fires or someone presses stop. Create a third Freestyle
> job, `faveo-qa-janitor`, on a nightly cron: drop every `qa_pr_%` database with
> no running build, `fuser -k` the port range, and delete orphaned `*-qa-tools`
> directories. Without it, one aborted round poisons every later build on that
> node with a port collision and a stale database.

---

## 7. Webhook and trigger flow

Four events, each load-bearing. The paid version's list is not padded.

| GitHub event | Action | Wakes | Without it |
|---|---|---|---|
| **Issues** | `labeled` | Stage 1 | Stage 1 never starts |
| **Pull requests** | `labeled`, `synchronize` | Stage 3 | Labelling a PR does nothing, and pushing a fix never re-runs the round |
| **Pull request reviews** | `submitted` | Stage 3 | An approving review does not wake a round |
| **Check suites** | `completed` | Stage 3 | A PR labelled while CI is red never starts when CI goes green |

```
Payload URL   https://<jenkins>/generic-webhook-trigger/invoke?token=faveo-qa-pipeline
Content type  application/json
Secret        (empty — the token in the URL is the authentication)
Events        Let me select individual events → the four above
```

> **The failure that looks exactly like a broken pipeline.** GitHub does **not**
> send `issues` events for pull requests — a PR label emits `pull_request`.
> Subscribe only to *Issues* and everything looks correct: the gate is right, the
> labels are right, and nothing happens, because the event never leaves GitHub.

### How the job decides which stage it is

Both jobs receive every event. `resolve-work.sh` — the shell port of
`resolveWork()` — applies the same precedence and exits non-zero when the payload
is not this job's work:

```bash
# 1. our own label change?  → skip, or the failure path becomes a loop
[ "$gh_sender" = "$QA_BOT_LOGIN" ] && exit 1

# 2. an explicit parameter wins — this is how a human re-runs one item
if [ -n "$QA_NUMBER" ]; then
  # NOTE: with QA_STAGE=auto the paid version defaults to AUTHOR.
  # For a manual PR run you MUST set QA_STAGE=execute.
  stage="${QA_STAGE/auto/author}"; echo "QA_NUMBER=$QA_NUMBER"; exit 0
fi

# 3. an issue — but only if it is not really a PR
if [ -n "$gh_issue" ] && [ -z "$gh_is_pr" ] \
   && [ "$gh_label" = "$QA_TRIGGER_LABEL" ]; then
  [ "$want" = author ] || exit 1
  echo "QA_NUMBER=$gh_issue"; exit 0
fi

# 4. a PR, from either the pull_request or the check_suite payload
pr="${gh_pr:-$gh_check_pr}"
[ -n "$pr" ] && [ "$want" = execute ] && { echo "QA_NUMBER=$pr"; exit 0; }

# 5. unattributable → sweep with discover.sh, capped at QA_MAX_SWEEP
bash "$QA_TOOLS"/discover.sh "$want" | head -n "${QA_MAX_SWEEP:-3}"
```

`QA_DRY_RUN` reaches the build as the boolean parameter and is passed straight
through to `stage1-publish.sh` as `--dry-run`. `QA_GATE_EXPLAIN` becomes the
`QA_GATE_EXPLAIN=1` environment variable `stage3-gate.sh` already reads.

There is **no cron** on the QA jobs, by design: the team wants a label to start a
build now, not within four hours. The cost is that a dropped webhook delivery is
lost. Recover it from *Settings → Webhooks → Recent Deliveries → Redeliver*
(GitHub keeps ~30 days), or build with `QA_NUMBER` blank to sweep whatever is
labelled right now. The janitor job's cron is unrelated and does not sweep for
work.

---

## 8. Keeping the AI testing flow identical

| Methodology step | Paid | Community Freestyle | Equivalent? |
|---|---|---|---|
| AI authoring | `claude -p` in a Groovy `sh` step, tools allowlisted, no credentials held | Same invocation in *Execute shell*, plus an explicit `unset` of the tokens | **Exact** |
| Case validation | `stage1-publish.sh` — ≥4 steps, no blank expected results | Unchanged | **Exact** |
| QA Touch storage | `qatouch-client.sh`, cases created one at a time | Unchanged | **Exact** |
| Marker contract | `marker.sh`, `v:1`, amend-only | Unchanged | **Exact** |
| Human approval | QA Lead applies a label; the gate enforces it | Unchanged | **Exact** |
| The seven gate conditions | `stage3-gate.sh` | Unchanged | **Exact** |
| Disposable instance | Advance provisioner | Community provisioner — same isolation, fewer steps | Equivalent |
| Deterministic probes | ~35 checks, two disciplines | ~10–12 checks, one discipline (no API surface to probe) | Narrower, honestly reported |
| Severity vs blocking | Two axes; only an unambiguous core blocks | Unchanged (`lib.sh`) | **Exact** |
| Browser/UI testing | Playwright MCP, `--browser chrome` | Unchanged (`mcp-ci.json`); the prompt's screen-navigation section is rewritten for Blade | Equivalent |
| API testing | `curl` against `/api/*` and `/v3/api/*` | Deferred — Community has no API surface | Not applicable |
| Result collection | Per-case status, honest `blocked` | Unchanged (prompt) | **Exact** |
| QA Touch writes | `qt_update_results_by_code`, run must pre-exist | Unchanged | **Exact** |
| Report | Upserted PR comment, replaced per round | `upsert-comment.sh` — same marker, same PATCH | **Exact** |
| Verdict | `decideVerdict` from the machine marker | Same rule, in shell | **Exact** |
| Cleanup | `finally` | Post-build task + janitor job | Close, not equal |

### The two that are not exact

- **Cleanup on abort.** Genuinely weaker. Mitigated by the janitor job in §6; do
  not skip it.
- **Probe breadth.** Narrower because the application is smaller, not because
  Freestyle made it so. `summarize.sh` already prints the check count in the
  report header — leave that in, so nobody reads a 12-check pass as a 35-check
  pass.

---

## 9. Local testing, before Jenkins exists

Every `ci/qa` script takes its configuration from the environment precisely so
this is possible — that is what `local-stage3.sh` demonstrates. Do all of §10's
levels 1–6 on your own machine and touch Jenkins only at level 7.

### Required software

```
git curl jq awk file        # both stages
node npm  +  claude CLI     # both stages invoke claude -p
php composer                # testing-setup, serve
mysql client + a server     # creates qa_pr_<n>
google-chrome               # the Playwright MCP browser
npx -y @playwright/mcp@latest --version   # proves registry egress
```

### Environment and credentials for a local run

```bash
. ci/qa/env.community.sh
export GITHUB_REPO=faveosuite/faveo-helpdesk
export GITHUB_TOKEN=...          # a PAT with repo scope
export QATOUCH_API_TOKEN=...
export QATOUCH_DOMAIN=...        # bare subdomain
export ANTHROPIC_API_KEY=...     # or CLAUDE_CODE_OAUTH_TOKEN
export QA_TOOLS="$PWD/ci/qa"     # no snapshot needed locally
```

### Running each piece

| What | Command | Writes |
|---|---|---|
| Jenkinsfile lint (if you keep one) | `bash ci/qa/lint-jenkinsfile.sh` | Nothing |
| Discover eligible work | `bash ci/qa/discover.sh all` | Nothing |
| The gate, explained | `QA_GATE_EXPLAIN=1 bash ci/qa/stage3-gate.sh <pr>` | Nothing |
| Marker read | `bash ci/qa/marker.sh read <issue>` | Nothing |
| Code map | `bash ci/qa/code-map.sh /tmp/qa-map` | Local files only |
| Module list | `bash ci/qa/module-list.sh /tmp/mods.txt` | Local file (reads QA Touch) |
| **Stage 1, dry** | `bash ci/qa/stage1-publish.sh <issue> cases.json --dry-run` | **Nothing** — exits before any write |
| **Stage 3, local** | `bash ci/qa/local-stage3.sh --pr <n> --probes --keep` | A worktree + a local DB. **No** GitHub, QA Touch or label writes |
| Probes against a running instance | `PROBE_INSECURE=1 bash ci/qa/probes/security-community.sh https://127.0.0.1:8099 out.jsonl` | One JSONL file |
| Summarise | `bash ci/qa/probes/summarize.sh out out*.jsonl` | `out.json`, `out.md` |

> **`local-stage3.sh` is the model to follow.** It already refuses to run
> `php`/`artisan`/`yarn` as root, provisions inside a **git worktree** rather than
> the dev tree (so `cp .env.dusk.testing .env` cannot clobber a running instance),
> makes no GitHub or QA Touch writes, and mirrors the tools-beside-the-app layout
> Jenkins uses. Adapt it for Community rather than writing a new local workflow —
> a parallel reimplementation would tell you nothing about Jenkins.

### Verifying what came out

- **Generated cases:** the dry run prints module, kind, and every case with its
  steps and expected results. Check each has ≥4 steps and a non-empty expected
  result on every step — those are the two rules `stage1-publish.sh` rejects the
  whole set on.
- **Probe output:** `jq -r '.verdict, .total, .blocking_failures, .blocking_skipped' out.json`.
  A `blocking_skipped` above zero means "we could not check", which correctly
  yields `unknown`, not `pass`.
- **The report:** the last line must be the `<!-- qa-first-round {…} -->` marker,
  and `passed + failed + blocked` must equal the number of codes in the issue's
  marker. If it does not, the executor counted a case it never ran.

---

## 10. The safe testing progression

Each level is allowed to modify strictly more than the one before it. Do not skip
a level to save time; every one catches a class of failure the next level would
present as something else.

| Level | What | Modifies |
|---|---|---|
| **L1** Syntax and configuration | `bash -n` every script; `jq -e . mcp-ci.json`; confirm every binary resolves in a **non-login** shell (`env -i bash -c 'command -v claude'`) | Nothing |
| **L2** Read-only API reachability | `discover.sh`, `module-list.sh`, `marker.sh read`, authenticated `curl` to both APIs. Proves the tokens and tenant subdomain before anything writes | Nothing |
| **L3** Gate only | `QA_GATE_EXPLAIN=1 stage3-gate.sh <pr>` against a real PR. Safe on any PR — it breaks before emitting a go-context when any condition failed, so it can never let an ineligible round run | Nothing |
| **L4** AI authoring, dry | `QA_DRY_RUN=true` against a real issue. Spends Anthropic tokens; writes nothing to QA Touch, the issue, or the labels | Anthropic spend only |
| **L5** QA Touch write, throwaway issue | First non-dry Stage 1. **Use a scratch issue, and check `QATOUCH_PROJECT` first** — there is no delete-case endpoint, so a wrong project key leaves cases in a shared library permanently | QA Touch; one issue's labels and comments |
| **L6** Full Stage 3, locally | `local-stage3.sh --pr <n> --probes`. Provisions, serves, probes, optionally runs the executor. No GitHub or QA Touch writes. Where the Community provisioning sequence gets debugged, at ~1 minute a cycle | A local worktree and database |
| **L7** Jenkins, gate-explain first | Build with `QA_STAGE=execute`, `QA_GATE_EXPLAIN=true`. Confirms node tooling, credential bindings and the skip sentinel without provisioning anything. Then one real round on a PR meeting all seven conditions | Everything |

> **Before L7's real round.** Create the QA Touch test run **in the UI**, named
> exactly `First round — PR #<pr> (issue #<issue>)`, or pass its key as
> `QA_TEST_RUN_KEY`. `POST /testRun` cannot be used: with every key valid it never
> answers, creates the run anyway, and attaches the entire case library instead of
> the cases asked for — and no API call deletes the result.

---

## 11. What moved, and what did not

| Pipeline responsibility | Becomes, in Freestyle |
|---|---|
| `parameters { }` | *This project is parameterized* — six parameters by hand |
| `triggers { GenericTrigger(…) }` | Generic Webhook Trigger plugin, seven JSONPath variables in the UI |
| `environment { }` | `ci/qa/env.community.sh`, sourced by every step |
| `agent { label … }` | *Restrict where this project can be run* (static) |
| `options { disableConcurrentBuilds() }` | Leave *Execute concurrent builds* unticked |
| `options { timeout(90) }` | *Abort the build if it's stuck* → 90 minutes |
| `options { buildDiscarder(…) }` | *Discard old builds* → 50 |
| `withCredentials([…]) { }` | *Use secret text(s) or file(s)*, build-scoped; two jobs restore the split |
| `claudeCredentials()` — conditional binding | Bind one; the CLI reads whichever variable is set |
| `credentialExists()` probes | `[ -n "${VAR:-}" ]` in step 00 |
| `resolveWork()` | `ci/qa/freestyle/resolve-work.sh` |
| `stage('…')` + `when { }` | Ordered *Execute shell* steps + the `qa-skip` sentinel |
| `provisionInstance()` | `ci/qa/freestyle/provision.sh` (Community sequence) |
| `runProbes()` | One shell step; `summarize.sh` unchanged |
| `assertBrowserUsable()` | `ci/qa/freestyle/browser-preflight.sh` |
| `runExecutor()` + budget clamp | One shell step; `BUILD_START` captured in step 00 |
| Failure classification | `ci/qa/freestyle/classify-failure.sh`; `limit-reset.sh` unchanged |
| `upsertPRComment()` | `ci/qa/freestyle/upsert-comment.sh` |
| `decideVerdict()`, `verdictBanner()`, `applyVerdictLabels()` | `ci/qa/freestyle/verdict.sh`; `label.sh` unchanged |
| `archiveArtifacts` | Post-build *Archive the artifacts* |
| `finally { teardownInstance() }` | Post-build Task (all results) **+ a nightly janitor job** |
| `qaMark()` / `qaPhaseSummary()` | `printf` into a TSV; `phase-summary.sh` unchanged |

### Behaviour that does not change

- The **seven gate conditions**, and the quiet-skip discipline
- **Severity vs blocking**, and the rule that a not-run blocking check yields `unknown`
- **Case selection** — the marker, the linked issue, the codes, amend-only
- **QA Touch semantics**, including the run-must-pre-exist rule
- The **webhook contract** — same token, four events, same filter
- The **verdict rule**, read from a machine marker rather than prose
- Both **agent prompts** and their tool allowlists

### What you give up

- **Guaranteed teardown.** No `finally`; a post-build task misses aborts
- **Per-step credential scope.** Two jobs plus an `unset` recovers most of it
- **Parameterised node selection.** Static restriction instead
- **Stage view.** One flat log; `phase-summary.sh` substitutes
- **Config as code.** Trigger, parameters and bindings now live in Jenkins, not
  Git — so they are not reviewed and not versioned

---

## 12. Repository changes

Because Community has no `ci/` at all, almost everything is a create. Group the
work in three commits so the review is tractable.

### Commit 1 — import unchanged from the advance repo

Copy verbatim into `ci/qa/`. Nothing here needs editing, and editing it is how the
two copies diverge.

```
CREATE  ci/qa/gh-client.sh          ci/qa/qatouch-client.sh
        ci/qa/marker.sh             ci/qa/marker.md
        ci/qa/label.sh              ci/qa/discover.sh
        ci/qa/stage3-gate.sh        ci/qa/stage1-publish.sh
        ci/qa/fetch-issue.sh        ci/qa/fetch-attachments.sh
        ci/qa/module-list.sh        ci/qa/limit-reset.sh
        ci/qa/phase-summary.sh      ci/qa/instance-health.sh
        ci/qa/license-segments.sh   # keep: harmless, cheap to keep in sync
        ci/qa/tls-proxy.php         # REQUIRED: forceScheme('https') is unconditional here too
        ci/qa/mcp-ci.json           ci/qa/qatouch-facts.md
        ci/qa/probes/lib.sh         ci/qa/probes/summarize.sh
        ci/qa/probes/known-pre-existing.txt
        ci/qa/README.md             ci/qa/GETTING-STARTED.md
```

### Commit 2 — adapt for the Community application

| Action | Path | Why |
|---|---|---|
| **Create** | `ci/qa/env.community.sh` | Replaces `environment { }`. Configuration only, never secrets |
| **Create** | `ci/qa/probes/security-community.sh` | The generic half of `security.sh`: exposed files, debug output, CSRF, headers, cookie flags, session-after-logout, `_dusk/login`. Drops the `/api/admin/*` boundary checks, which have no endpoints here |
| **Create** | `ci/qa/seed-qa-users.php` | Community's `users` schema and `role` column, not advance's. Publishes ids so probes can log in |
| **Adapt** | `ci/qa/code-map.sh` | Route and permission extraction assumes advance's structure. Verify `routes.txt` and `labels.txt` are non-empty against Community before trusting it |
| **Adapt** | `ci/qa/kb-sync.sh` | Points at the Faveo KB. Confirm the articles describe Community behaviour, or the agent triangulates against the wrong product |
| **Rewrite** | `ci/qa/stage3-prompt.md` § "Where the screens are" | Community is Blade, not two Vue catch-alls. The advance text tells the agent a wrong URL answers 200 — false here, and it would change how it navigates |
| **Adapt** | `ci/qa/stage1-author-prompt.md` § "Faveo conventions" | The 400/412/422 API conventions and the session-vs-token note describe advance's API. Remove or replace, or every `api` case is authored against a surface that does not exist |
| **Do not import** | `enable-api.php`, `activate-plugins.php`, `relax-lockout.php`, `probes/dusk.sh`, `dusk-changed.sh` | No v3 API, no plugins, no `attempt_locks`, no `phpunit.dusk.xml` |
| **Defer** | `probes/api.sh` | `routes/api.php` has one stub route. Import it when Community grows an API, not before |

### Commit 3 — the Freestyle orchestration

```
CREATE  ci/qa/freestyle/resolve-work.sh      # resolveWork() — the loop guard lives here
        ci/qa/freestyle/provision.sh         # provisionInstance(), Community sequence
        ci/qa/freestyle/browser-preflight.sh # assertBrowserUsable()
        ci/qa/freestyle/classify-failure.sh  # timeout vs limit vs other
        ci/qa/freestyle/upsert-comment.sh    # upsertPRComment()
        ci/qa/freestyle/verdict.sh           # decideVerdict + banner + labels
        ci/qa/freestyle/teardown.sh          # the post-build task
        ci/qa/freestyle/janitor.sh           # the abort-path safety net
        ci/qa/local-community.sh             # local-stage3.sh, adapted

MODIFY  .gitignore                           # qa-instance-creds-*.sh, qa-*.jsonl,
                                             # qa-evidence-*/, .env.qa-original

DO NOT MODIFY
        app/  routes/  database/  resources/     # the application under test
        stage1-publish.sh  stage3-gate.sh        # shared logic — a divergent
        qatouch-client.sh  gh-client.sh          # copy is the thing to avoid
```

> **Keeping the two copies from drifting.** Commit 1 is a verbatim import, which
> means it will go stale the moment the advance repo fixes a bug in
> `qatouch-client.sh`. Record the source commit SHA in a `ci/qa/IMPORTED-FROM`
> file and re-sync deliberately. The alternative — editing the imported files
> "just a little" — is how you end up maintaining two clients with two different
> sets of QA Touch workarounds.

---

## 13. Implementation checklist

**Decide first**

- [ ] QA Touch project key and case prefix for Community confirmed
- [ ] Anthropic billing account and budget confirmed
- [ ] Two-job split agreed (author / firstround / janitor)

**Repository**

- [ ] Commit 1 — unchanged scripts imported, source SHA recorded
- [ ] Commit 2 — Community adaptations, dropped scripts left out
- [ ] Commit 3 — `freestyle/` orchestration scripts
- [ ] `.gitignore` updated for credential and evidence files
- [ ] `stage3-prompt.md` screen-navigation section rewritten for Blade
- [ ] `stage1-author-prompt.md` API conventions removed or replaced

**Node**

- [ ] All §9 binaries resolve in a non-login shell
- [ ] `npx -y @playwright/mcp@latest --version` succeeds
- [ ] MySQL account can `CREATE`/`DROP` on `qa_pr_%`
- [ ] Outbound access to GitHub, QA Touch, Anthropic, npm

**Jenkins**

- [ ] Six credentials created, correct types
- [ ] No `qa-license-code` credential created
- [ ] `faveo-qa-author` Freestyle job created
- [ ] `faveo-qa-firstround` Freestyle job created
- [ ] `faveo-qa-janitor` job created and scheduled
- [ ] Branch Specifier on the maintained branch
- [ ] Concurrent builds disabled; 90-minute abort set
- [ ] Seven webhook JSONPath variables configured
- [ ] Post-build archiving configured, empty-allowed
- [ ] Post-build teardown set to run on all results

**GitHub**

- [ ] All ten labels created, emoji shortcodes exact
- [ ] Webhook added with exactly four event types
- [ ] `QA_BOT_LOGIN` matches the bot account's actual login

**Verified working**

- [ ] L1 syntax and PATH
- [ ] L2 read-only API reachability
- [ ] L3 gate explains correctly on a real PR
- [ ] L4 Stage 1 dry run produces usable cases
- [ ] L5 QA Touch write on a scratch issue
- [ ] Human approval step exercised end to end
- [ ] L6 full Stage 3 locally
- [ ] L7 Jenkins gate-explain, then one real round
- [ ] Report upserts rather than stacking on a re-run
- [ ] Verdict label applied, and *not* applied on `unknown`
- [ ] Teardown verified: database dropped, port free, `.env` restored
- [ ] Janitor verified against a deliberately aborted build

---

## The ten labels

Names are matched exactly, emoji shortcodes included. A label renamed in the repo
but not in the job produces a pipeline that never fires and no error anywhere.

```
QA: Test case needed
QA: Test case In Progress
QA Test Cases Added
QA: Test case Approved
Requires Functionality Review :pray:
Code Approved :heart_eyes_cat:
QA: Round 1 Testing In Progress
QA: Round 1 Testing Approved :clap:
Functionality Correction :facepunch:
Manual
```

---

Source of truth remains the advance repository: `ci/qa/GETTING-STARTED.md` for the
supported Pipeline setup, `ci/qa/README.md` for every script and variable, and
`Jenkinsfile.qa` for the Groovy this plan asks you to replace. Read its comments
before rewriting any of it — most of them exist because something went wrong once.
