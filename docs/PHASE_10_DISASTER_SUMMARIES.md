# Phase 10 — Disaster reports and summaries

## Use and setup

Sign in as admin and select **Disaster summaries**. Select From and Through dates, then **Apply period**. The default is the current month through today in Asia/Manila. The end date is inclusive: queries use midnight on the following day as an exclusive bound. Invalid dates or a reversed range are rejected.

Use **Print summary** or the browser print command for a paper/PDF summary. No migration or new database table is needed. SQL examples are in `database/report_queries.sql`; the model runs equivalent parameterized aggregate queries.

## Meaning of the totals

| Summary | Meaning | Date filter |
|---|---|---|
| Affected households | Current registry_reason is affected or both | Not applied |
| Center capacity | Registered capacity of all centers, including closed centers | Not applied |
| Center occupancy | Number of evacuation records without checkout time | Not applied |
| Available places | Unoccupied places in open centers only, clamped to zero | Not applied |
| Incidents | Reports submitted during the selected period, grouped by type and current status | created_at |
| Relief goods | Quantities from distribution detail rows, separated by item and unit | distributed_at |
| Distribution events | Number of distribution headers, not number of goods rows | distributed_at |
| Households receiving relief | Distinct household IDs among those events | distributed_at |

The database does not currently link incident events to affected households. Accordingly, this page does not claim to calculate households affected by a particular disaster or within a historical period. Current incident status is not a historical status snapshot. Manual stock-out is excluded from goods distributed to households.

Queries run in a read transaction. With the normal MySQL/InnoDB REPEATABLE READ configuration, results share one snapshot. Align PHP/database session time zones to Asia/Manila for deployment and consistent local development; date filtering compares the timestamps as interpreted by the database session. The printed generation timestamp is explicitly labeled Asia/Manila.

## Privacy and printing

Only admins may load the report. The model retrieves aggregates and center/item names, never household codes, representatives, contact details, resident names, incident narratives, or private coordinates. There is no resident report endpoint.

The local print stylesheet hides navigation (including the signed-in staff name), filters, buttons, and footer. Tables have print borders, repeated headers, and unrestricted width. Item quantities remain separate by unit. Review any exported report before sharing it outside the barangay; this phase does not publish summaries to residents.

## Files

- `app/models/DisasterSummary.php`: aggregate queries and read transaction.
- `app/controllers/DisasterSummaryController.php`: admin guard and date validation.
- `resources/views/admin/summaries/disaster.php`: summary page and filters.
- `resources/assets/css/disaster-summary.css`: print layout.
- `resources/assets/js/print-summary.js`: browser print button.
- `database/report_queries.sql`: aggregate query reference.
- `tests/phase10_summary.php`: isolated fictional SQLite fixture using the real summary model.

## Checkpoint results

PHP syntax checks passed for the new model, controller, and report page. The actual model queries passed ten assertions against fictional in-memory SQLite records:

- Affected households: 2.
- Capacity: 10; active occupancy: 2; available places in open centers: 3.
- Selected-period incidents: 3 (2 flood/pending, 1 fire/resolved); both date boundaries verified.
- Relief distribution events: 3, reaching 2 distinct households.
- Goods: 6 food packs and 8 water bottles, without duplicated sums from joins.
- Empty-period totals are zero, while current affected-household/occupancy counts remain unchanged.

Run `php tests/phase10_summary.php` with PDO SQLite enabled to reproduce. It does not connect to MySQL or alter real records. This verifies the aggregation checkpoint on fictional fixture data, not on the team's local MySQL installation. Browser print appearance, HTTP role/date handling, and MySQL runtime/snapshot behavior still need verification on the team's setup.
