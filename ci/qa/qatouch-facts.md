# QA Touch API facts — verified, do not re-derive

Every fact below was confirmed against the live `MeLq` project or by reading
`qatouch-mcp-server` v1.1.4 source (`/usr/lib/node_modules/qatouch-mcp-server/src/`).
Several contradict what the tool descriptions imply. Read this before changing
anything under `ci/qa/` or the two QA workflows.

## Two different services

| Service | Base | Used for |
|---|---|---|
| Public API | `https://api.qatouch.com/api/v1` | cases, modules, runs, results — everything the MCP server wraps |
| App backend | `https://ladybird.qatouch.com/v1` | the AI generation endpoints (the SPA's own backend) |

**They do NOT share an auth mechanism.** This is the single most important fact here.

The **public API** takes a header pair. `domain` is a bare subdomain, not a URL —
passing `https://ladybird.qatouch.com` fails with an opaque 401:

```
domain: ladybird
api-token: <secret>
```

The **app backend is session-authenticated and cannot be called from CI.**
Verified by capturing two real requests from a logged-in browser: neither carried
`domain` or `api-token`. They carried:

```
Cookie: laravel_session=…; XSRF-TOKEN=…; remember_web_<hash>=…
x-csrf-token: <per-page-load token>
x-requested-with: XMLHttpRequest
origin / referer: https://<domain>.qatouch.com
```

A GitHub runner has no session, and the CSRF token is minted per page load, so
**QA Touch's AI test-case generation is browser-only.** Scraping a login to mint a
session would make the release pipeline depend on reverse-engineering a vendor's
web app — it breaks on any login change and MFA defeats it outright. Don't.

### The AI contract, as captured (for if this ever becomes token-callable)

Two-step: upload, then extract by file id.

```
POST /v1/file/upload                         multipart/form-data
     image=<file>  module=cases  relatedid=

  -> {"status":true,
      "data":"pRen2",                        # <- the file id
      "file":{"file_id":"pRen2","file_ext":"png",
              "source":"https://media.qatouch.com/<tenant>/cases/<ts><name>.png",
              "download_url":"https://<domain>.qatouch.com/v1/file/download/cases/pRen2"}}

POST /v1/image/extract/p/<project>/tid/2     application/json
     ["pRen2"]                               # array of file ids from `data`
```

`tid` appears to be the case template id (2 = Test Case (Steps)). The equivalent
text-prompt and BRD-document endpoints were never captured — and are moot while
the auth is session-bound.

Worth re-asking QA Touch periodically: their JIRA integration generates cases
server-side from an external tracker's items, so the capability exists outside a
browser. If they expose it on the public API, Stage 1 can become fully automated
with QA Touch's AI doing the authoring.

## Case library shape

- `MeLq` holds ~8,200 cases. Page size is **20**, so ~411 pages.
- `list_test_cases` is ordered by `created_date` **ascending** — newest cases are on the LAST page.
- `meta.total` and `link.last` are reliable. **`meta.last_page` is not** — it reports `"1"` regardless.

## Traps

1. **`search_test_cases` and `search_modules` only search page 1.**
   `searchTestCases.js` calls `GET /getAllTestCases/{project}` with no `page` param, then
   filters client-side. On `MeLq` that searches 20 of 8,200 cases — 0.24% — and returns
   "no match" for everything else. **Never use them on a large project.** Page through
   `getAllTestCases` / `getAllModules` instead.

2. **Creating a case does not tell you its code.** `POST /testCase/steps` returns only a
   message. Resolve `TR####` codes afterwards with the arithmetic in
   `qatouch-client.sh:qt_resolve_new_case_codes`: record `meta.total` as `T` *before*
   creating, then fetch pages `floor(T/20)+1` through `link.last`.

3. **Bulk creation caps at 10 cases**, each needing **≥4 steps**, each step needing a
   **non-empty expected result**. The existing `[TC_SB_*]` cases in `MeLq` violate the last
   rule (expected result on step 1 only, 2–3 blank) — don't reproduce that shape.

4. **`testRunResults` gives you no steps.** It returns `code`, `run_key` (the per-case
   result key), `title`, `status`. Step bodies must be fetched separately.

5. **Per-case result comments are impossible.** The single-result endpoint has no comment
   param, and the batch endpoint takes ONE `comments` string for the whole batch. Per-case
   failure evidence has to live in the GitHub comment; QA Touch gets one batch comment
   that links to it.

6. **Test run creation takes no case parameter**, so a run created from the API may hold
   nothing, and results written into an empty run silently no-op. See "Test runs cannot be
   created from the API" below — this is why `qt_resolve_run` prefers a run made in the UI
   and why failing to get one is a reportable outcome rather than a build failure.

7. **A rejected write still returns HTTP 200.** `success:false` in the body is the only
   signal. See "Test run statuses" below — the numeric status ids the UI shows are rejected
   by the results endpoint, which wants slugs.

## Confirmed by a real write into an empty project (NEXQ, 2026-08-11)

Ten cases were created end to end. What that run established:

- **An empty project returns no `meta` and no `link` at all** — just
  `{"msg":"Test case(s) not found for the Project"}`. Both `qt_cases_total` and
  `qt_cases_last_page` fall back explicitly; without that the code-resolution
  arithmetic died on the first run in a fresh project.
- **`getAllModules` returns `section_name` / `section_key`, not `module_name` /
  `module_key`.** The read API calls a module a "section" — the same word
  `POST /testCase/steps` uses for `sectionKey`. Verified live on `MeLq`
  (327 modules, 17 pages): every row is `{"section_name": …, "section_key": …}`.
  `qt_module_key_by_name` filtered on `.module_name` and therefore matched nothing
  on every call, which made an existing-module collision unrecoverable.
- **Module names are matched case-insensitively by QA Touch, and `getAllModules` is
  the whole list.** The failure that looked like an unlistable folder was neither:
  `MeLq` holds **`Form builder`** (lowercase b, key `v1qgp`, page 4 of 17). An exact
  match on `Form Builder` found nothing, and `POST /module` then refused to create it
  because QA Touch's uniqueness check ignores case — so the module could be neither
  found nor created. Match case- and whitespace-insensitively (`qt_module_key_by_name`
  does, exact first). Trailing spaces are real in this library: `Create Package `,
  `Faveo Helpdesk Community `.
  **Lesson: page the WHOLE list before concluding a name is absent.** Checking pages
  1, 2, 16 and 17 of 17 and assuming creation order put the answer at the tail is how
  this got misdiagnosed twice.
- **A module key the module listing hides can still be recovered from a test case.**
  `getAllTestCases` rows carry `module_name` AND `module_key` for the case's own
  module, so any single case sitting in an unlisted folder reveals the key
  `getAllModules` withheld. `qt_module_key_from_cases` does this. It costs 400+
  requests on `MeLq` (page size fixed at 20), so it is a last resort — but it turns
  "the folder exists and I cannot use it" into a normal publish. A folder holding no
  cases of its own stays unreachable.
- **A refused `POST /module` carries no reason.** `success:false` with no `msg`,
  `message` or `errors`. Do not expect the API to explain a rejection.
- **`POST /module`'s response shape is NOT verified.** The `data.moduleKey` claim
  previously recorded here came from reading `createModule.js`, which just returns
  `response.data` — it never confirmed the field. `qt_create_module` now accepts
  several spellings *and* logs the whole body when it finds no key, so the next
  occurrence records the real shape. Until a passing run prints one, treat the
  field name as unknown.
- **A refused write is HTTP 200 with `success:false`** — already known for run
  results, and `POST /module` must be assumed to behave the same way. Checking the
  status code alone reports a no-op as a success.
- **The API's case code is not what the UI shows.** `getAllTestCases` reports
  `case_code: "TR8967"`; the QA Touch UI shows the same case as `FH-8967` — the
  project's own code prefix plus the number. Verified across the library: even
  2019-era cases created by hand in the UI come back as `TR0064`, so `TR` is the
  API's rendering, not an artefact of how a case was created. The number is the
  shared part. `GET /projects` does NOT expose the prefix (only name, key, url,
  estimate, status), so it is configured as `QATOUCH_CASE_PREFIX`. Publish the UI
  form to people and keep `TR####` in the marker — that is the form the results
  endpoint accepts.
- **Field naming is per-endpoint, not global.** `getAllModules` rows use
  `section_name`/`section_key`, while `getAllTestCases` rows carry
  `module_name`/`module_key` for the very same objects. Check the shape of the
  endpoint you are actually calling; do not carry a spelling across.
- **Case codes are not derived from case counts.** NEXQ held zero cases and the
  first created case came back as `TR0011`, not `TR0001` — the per-project counter
  survives deletions. Resolve codes by POSITION (see trap 2), never by arithmetic
  on the code number itself.
- **Steps, expected results, precondition, description and `reference` all
  round-trip intact**, including quotes inside step text.
- **Every created case defaults to `Type: Acceptance`, `Priority: Critical`,
  `ApprovalStatus: Pending`.** `POST /testCase/steps` accepts neither type nor
  priority, so everything the pipeline creates lands as Critical — which will skew
  any priority-based reporting. `POST /testcase/update/common` (MCP
  `update_test_case`) does accept `priority` and `type`, so a follow-up call per
  case is the only way to set them.
- Timestamps are **UTC** (the module create response states `timezone: UTC`).

## CSV import — the only way to set priority and type

`POST /testCase/steps` (MCP `create_test_case` / `create_bulk_test_cases`) accepts
neither `priority` nor `type`, so everything it creates is **Critical / Acceptance**.
`update_test_case` cannot fix that afterwards: it rejects a metadata-only change with
*"The parameter steps_template is missing"*, so changing one field means resending
every step — and its `steps_template` is declared as an **array** while create wants an
index-keyed **object**, so a wrong guess could flatten the steps of every case touched.
There is no delete-case endpoint to undo that.

**So: use CSV import when priority or type matter.** It also has no 10-row cap, unlike
`create_bulk_test_cases` — 30 cases imported in one call.

Header names are normalised (lowercase, spaces → underscores) and the controller reads
several of them **without null guards**, so every column below must be present even when
empty. Discovered by iterating on its error messages, which name the missing index and
create nothing on failure — so probing is free:

```
Module name, Test case title, Description, Precondition, Priority, Type, Mode,
Steps, Expected result, Test data, Reference, Estimate, Assigned to, Tags,
Test suite, Approval status, Feature script
```

Traps in that list:

- **`Expected result` is SINGULAR.** "Expected Results" normalises to `expected_results`
  and fails with *Undefined index: expected_result*.
- **`Test case title`, not `Title`** — the latter fails on `test_case_title`.
- **`Mode`** (`manual` / `automation`) is required, as are `Test data` and the rest.
- **Steps are newline-separated within one cell**, and `Expected result` holds the matching
  newline-separated list. Verified: four lines in each produced four discrete step/expected
  pairs.
- `Module name` routes the case by name, so the module must already exist — the endpoint
  takes no `sectionKey`.

Import **creates**; it cannot update. Re-importing existing cases duplicates them.

## Test run statuses — two vocabularies, and only one of them writes

`GET /testRunStatus` returns numeric keys:

| ID | Status |
|---|---|
| 1 | Passed |
| 2 | Untested |
| 3 | Blocked |
| 4 | Retest |
| 5 | Failed |
| 6 | Not-Applicable |
| 7 | In-Progress |
| 8 | Hold |

**The write endpoint rejects those numbers.** `POST /testRunResults/testrun/code`
with `status: 1` answers 200 with:

```json
{"success":false,"error_msg":"Provided Status is not Valid",
 "available_status":["passed","untested","blocked","retest","failed",
                     "not-applicable","in-progress","hold"]}
```

Verified against the live API 2026-08-10. So: **the read path speaks integers and
the write path speaks slugs.** Two consequences worth remembering:

- `qt_update_results_by_code` takes either and sends slugs, retrying with ids if a
  future build flips back. Callers should use names.
- A rejection comes back **HTTP 200 with `success:false`**, so any code that only
  checks the status code will report a run that never landed.

"The app is down" is **never** Blocked — that aborts the whole run before anything is
written, because a run full of false Fails is worse than no run.

## Test runs cannot be created from the API (as far as anyone has proven)

`POST /testRun` accepts `projectKey`, `assignTo`, `milestoneKey`, `testRun` — and
nothing for cases. All 27 runs in `MeLq` are `"Type": "specific"`, i.e. created in
the UI with cases explicitly selected. No vendor demo, wrapper library or Redoc
page exposes a create-run call; every integration only *updates results in a run
that already exists*.

**Measured on round 1 of PR #16113, which wrote no statuses at all:** `assignTo`
and `milestoneKey` are not optional, and the API names them **one at a time** —
supply `assignTo` and the next 400 asks for `milestoneKey`. So a create call that
omits either fails, including the reduced retry, and the round ends with no run.

`assignTo` wants a QA Touch **user key**, and exactly one endpoint returns one:

```
GET /testRun/availableUsers/<projectKey>
  -> {"success":true,"data":[{"user_key":"0Dmw","name":"admin ladybird", ...}]}
```

It is not findable by guessing — `getAllUsers`, `getUsers`, `projectUsers` and
`getAllMembers` all 404, and `Assigned_To` is null on every existing run in
`MeLq`, which is why that round concluded no such endpoint existed. It is in the
MCP server as `list_test_run_available_users`. `qt_available_users` wraps it and
`qt_default_assignee` takes the first key, or `QATOUCH_ASSIGN_TO` when set.

`milestoneKey` comes from `GET /getAllMilestones/<projectKey>` (the MCP server
calls these "releases"). There is deliberately **no fallback**: all 16 in `MeLq`
are product releases, and filing automated rounds under whichever one the API
lists first would put AI results into a human release's QA view.

Which one is decided by the **linked issue's** GitHub milestone, not the pull
request's — the release is chosen on the issue, and the issue is also where the
approved cases live; a PR carries at most a copy of that decision and frequently
nothing at all. `stage3-gate.sh` reads it from the issue it took the marker from
and puts it in the context as `milestone`; the pipeline exports it as
`QA_ISSUE_MILESTONE`.

**The two systems do not spell milestones alike, and neither name contains the
other.** GitHub carries `Helpdesk v9.4.3.8.RC.1`; QA Touch carries
`Release 9.4.3.8 RC1`, `Release 9.2.1 Official`, `Faveo Helpdesk release 7.1.6`,
even `Relaase 9.1.0 RC`. So `qt_canon_milestone` reduces both to
`<version>|<qualifier>` — product words (`helpdesk`, `faveo`, `release`, that typo,
`build`, a `v` glued to the version, and `official`) are noise, leading numbers are
the version, the rest is the qualifier, and a bare `rc` reads as `rc1`.

| GitHub | reads as | matches in QA Touch |
|---|---|---|
| `Helpdesk v9.4.1.2` | `9.4.1.2` | `Release 9.4.1.2` |
| `Helpdesk v9.2.1` | `9.2.1` | `Release 9.2.1 Official` |
| `Helpdesk v9.2.1.RC.1` | `9.2.1` + `rc1` | `Release 9.2.1 RC1` |
| `Helpdesk v9.4.1.2.RC.1` | `9.4.1.2` + `rc1` | **nothing — refuses** |

**The qualifier is part of the identity.** Substring matching was the second pass
once, and it filed an issue on `Release 9.4.1.2.RC.1` under `Release 9.4.1.2` — the
official release — because the issue's name contains the milestone's. QA Touch keeps
those apart deliberately, so dropping the qualifier puts RC results in the official
release's QA view with nothing in the log to say so.

No match and several matches are both reported with the canonical forms, never
guessed: an RC that does not exist in QA Touch yet is a question for a person, and
the failure prints every milestone beside how it reads so they can see why.
`QATOUCH_MILESTONE_KEY` overrides the lot.

### A case has two identifiers and they are not interchangeable

| Field | Looks like | Used by |
|---|---|---|
| `case_code` | `TR8977` | the results API, and the Stage 1 marker |
| `case_key` | `M7kRkX` | building a run |

Round 1 handed the codes to `POST /testRun` as `caseKeys`, which can match
nothing — so even with `assignTo` supplied the run would have come back empty.
`qt_case_keys_for_codes` translates in one sweep of the newest case pages, and
`qt_create_test_run` calls it, so callers keep passing `TR####`.

So `qt_resolve_run` is a chain, not a call:

1. `QA_TEST_RUN_KEY` — a run someone made in the UI for this pipeline. **The
   supported path.**
2. an existing run of the same name — so a re-run writes into the run it used
   last time instead of accumulating one per attempt
3. ~~`POST /testRun`~~ — **closed, and not a judgement call any more.** Measured
   against `MeLq` on 2026-08-21, with `projectKey`, `testRun`, `assignTo` and
   `milestoneKey` all valid:

   - it **never responds** — 25s, 40s, 45s, all HTTP 000, while an invalid
     `projectKey`, `assignTo` or `milestoneKey` comes back in 0.7s, so it is the
     creation itself that hangs, not the parsing
   - it **creates the run anyway** — four calls left four runs
   - it **ignores the case list** in every spelling (`caseKeys`, `cases`,
     `caseKey`, `testCases`) and attaches **the whole project instead**: those four
     runs hold 8331, 8331, 7624 and 3692 cases, the count tracking how long each
     request ran before the client gave up
   - **nothing can delete them.** `DELETE /testRun/<key>`, `DELETE /testRun`,
     `POST /testRun/delete` and `POST /testRun/delete/<key>` all answer nginx 400.
     They have to be removed in the UI.

   Two consequences beyond the endpoint. `qt_request` no longer retries a timed-out
   **write** — three retries of one create is how one bad call became three runs.
   And `qt_create_test_run` refuses unless `QA_FORCE_RUN_CREATE=1`, which exists to
   re-measure this if the vendor fixes it and for nothing else.

If all three miss, Stage 3 reports on the PR and writes no statuses. That is the
correct outcome — writing into an empty run is a silent no-op indistinguishable
from a clean pass.

## Approval

`POST /testcase/update/common` (MCP: `update_test_case`) accepts
`approval: approve | reject | reverify`, so tester approval can be written into QA Touch
from GitHub, or read from `ApprovalStatus` to drive a GitHub label.
