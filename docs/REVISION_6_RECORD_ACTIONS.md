# Revision 6 — Checkbox actions and history protection

Admins use **Manage records** in navigation or **Select records / view archived** from household/member, incident, team, center/walk-in, relief, and announcement pages. One shared management page provides all nine master-record lists, search by ID/name, 50-row pagination, current/archive filters, Select all on this page, and selected-count text. This avoids repeating the bulk-processing code in every module form. Normal module pages retain their existing editors and history links.

Select one or more records and choose archive/deactivate or permanent deletion. The review step shows each ID/name and the action. Confirmation submits one POST. The server binds the reviewed batch to a one-use session review ticket (15-minute expiry); it does not trust hidden table names, IDs, or operations on confirmation. The ticket is a batch-confirmation mechanism, not a claim of a general CSRF implementation. Search/page selection is checked when preparing review; changed records invalidate the confirmation.

The model has a literal entity/table/action allowlist. It locks selected master rows in ID order in one MySQL transaction, compares their reviewed state, rechecks references and active stays/stock, and then writes. If any selected record fails, the whole transaction rolls back. Foreign keys remain enabled and restrictive. A concurrent conflict gives a failure message and requires reviewing again.

## Implemented rules

- Delete unused households only when they have no members/distributions. Delete members only when never referenced by evacuation history.
- Archive households/members only without active stays. Preserve historical member relationships; current member counts exclude archived members.
- Delete incidents only without images/assignments/updates. Archive only resolved/rejected incidents with no active assignment; clear public visibility. Existing images and response history remain linked.
- Delete never-assigned teams; otherwise deactivate. Existing assignments remain manageable.
- Delete never-used centers/walk-ins. Archive only after all stays end. Centers close and lose public visibility.
- Delete never-used relief goods/locations. Goods must have zero stock for either delete or archive. History remains unchanged. Locations lose public visibility.
- Delete only never-published drafts with known history. Archive withdraws published/legacy notices. Migration 004's unknown-publication flag protects legacy notices.
- Users, images, assignments, response updates, stays, distributions/items, and stock movements are absent from the action allowlist. Their original guarded history pages remain available. Use ordinary checkout/completion or a documented correcting stock transaction instead of deleting history.

Archived records are hidden from current module lists, map data, and new active selections. Show archived/inactive on Manage records provides access again; no automatic restore or republication is performed. Historical detail queries remain unrestricted by archive state. New distributions reject archived households/goods; new check-ins reject archived centers, members/households, and walk-ins. Current affected-household summaries exclude archived households, while historical incident/distribution summaries retain archived references.

## Verification

Twenty MySQL fixture assertions passed: blocked batch rollback, occupied-center/member/household refusal, positive-stock refusal, linked-record deletion refusal, publication withdrawal, preserved response history, team deactivation, unused deletion, archive filtering, stale-review refusal, table allowlist, and single-record behavior.

HTTP fixture checks passed for anonymous/resident denial, review names, confirmed multi-delete, replay rejection, unsupported record types, and resulting empty list. The fixture uses a test-only session injector outside the repository; production pages use normal session role checks.

Dashboard and disaster-summary regression checks passed after updating the fictional schema fixture for archive fields. PHP syntax and JavaScript syntax checks passed. Browser visual/checkbox interaction and full concurrent multi-module stress testing remain pending. No existing application data was changed.

`tests/revision6_records.php` runs only in CLI against an empty disposable `websys_revision6_fixture` database via `/private/tmp/websys-rev6.sock`; import the fresh schema with that database name first. It never resets databases and refuses a fixture containing users. It inserts fictional records. No new schema migration is required beyond 004.
