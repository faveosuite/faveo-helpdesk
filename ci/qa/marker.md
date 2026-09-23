# The `qa-touch-cases` marker contract

Stage 1 (case generation) and Stage 3 (execution) are separate workflow runs on separate
triggers, days apart, on different runners. The marker comment is the only thing that
carries state between them.

## Shape

Stage 1 appends exactly one HTML comment to its issue comment:

```html
<!-- qa-touch-cases {"v":1,"project":"MeLq","issue":1234,"module":"Ticket Listener","module_key":"eVDJ8","generated_at":"2026-08-11T06:00:00Z","source":["qatouch-ai"],"cases":[{"code":"TR8927","key":"lRvrkQ"},{"code":"TR8928","key":"rGVLke"}]}} -->
```

| Field | Why it's there |
|---|---|
| `v` | schema version; bump if fields change so Stage 3 can refuse an unknown shape rather than mis-read it |
| `project` | QA Touch project key — never assume `MeLq` at read time |
| `issue` | lets Stage 3 confirm it read the marker off the issue it thinks it did |
| `module` / `module_key` | where the cases live, for the report and for re-runs |
| `generated_at` | staleness signal: cases generated before the issue was last edited may not cover it |
| `source` | how the cases were authored (`qatouch-ai`) — future-proofs the marker if another author is ever added |
| `cases[].code` | `TR####`, the join key for result writes (`update_test_run_results_by_code`) |
| `cases[].key` | `case_key`, needed for approval writes (`update_test_case`) |
| `amended_at` | present only on an amended marker; `generated_at` keeps the original date |

## Rules

1. **Stage 3 must never parse prose.** The human-readable table in the same comment is for
   people; it is not a data source. Only the marker is.
2. **The marker is the idempotency guard, and the amend record.** Re-applying
   `QA: Test case needed` to an issue that already carries a marker **amends**:
   Stage 1 authors additional cases and republishes the marker with the union of
   old and new codes, deduplicated on `code`. Set `QA_AMEND=0` to refuse instead.
3. **Appending only ever adds.** No existing case is edited, renumbered or
   removed by an amend, because those cases may already be approved and already
   sitting in a run with results against them — and QA Touch has no delete-case
   endpoint to undo a mistake with. An amend also stays in the module the
   existing cases live in, since the marker records exactly one module.

   To genuinely *rewrite* a set, a human deletes the marker comment first. That
   is an explicit, auditable act, and it leaves the old cases in QA Touch where a
   person can retire them.

   In-place edits of a case's steps are deliberately not automated:
   `POST /testcase/update/common` rejects a metadata-only change and demands the
   full `steps_template`, which it declares as an array while create wants an
   index-keyed object — a wrong guess flattens the steps of every case it touches,
   with no undo (`qatouch-facts.md`).
4. **Absent marker means ignore, not error.** A PR whose linked issue has no marker exits
   quietly. This is the common case for PRs outside the QA pipeline and must stay cheap and
   silent, or the workflow becomes noise on every unrelated PR.
5. **Codes, not titles.** Titles get edited by reviewers in QA Touch; codes never change.

## Reading it

```bash
# extract the marker JSON from an issue's comments
gh_api "/repos/$REPO/issues/$ISSUE/comments" \
  | jq -r '.[].body' \
  | grep -o '<!-- qa-touch-cases .* -->' \
  | sed 's/^<!-- qa-touch-cases //; s/ -->$//' \
  | jq .
```

`ci/qa/marker.sh` implements this, plus the `v` check and an exit code that
distinguishes "no marker" (quiet skip) from "malformed marker" (loud failure).
