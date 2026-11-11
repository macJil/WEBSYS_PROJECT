# Phase 2 — Database schema v1.1

Official repository: https://github.com/macJil/WEBSYS_PROJECT

This is the implementation contract for the one-barangay classroom baseline. It has 17 tables, including incident image metadata. No resident, account, contact, incident, or image seed data is included. The only initial row is the fictional `Demo Barangay` setting. Individual team/stakeholder approval is not implied.

## Install or upgrade

- Fresh database: import root `barangay_drrm.sql` into a new development database using MySQL 8.0.16 or newer. Do not import it over an existing database.
- Existing original v1.0 database: back up its schema/data, review the stock preflight queries below, then run `database/migrations/001_phase_2.sql` once. Do not run the upgrade on a fresh v1.1 installation.
- The migration is additive; it does not delete tables or records. ALTER statements implicitly commit. If a statement fails, stop and inspect which statements completed before continuing; do not rerun the entire script blindly.
- Existing incident reports get `report_source='resident'` as a compatibility default. Review old staff-entered reports and correct their source manually. Old verification/action metadata stays NULL; do not invent past reviewers or timestamps.
- The application code is unchanged in this phase. Future controllers/models must implement the rules below. The schema alone does not provide permissions or workflow enforcement.

Stock preflight: both queries must return no rows before adding the new stock constraints. Resolve discrepancies against actual development records rather than deleting history to satisfy constraints.

```sql
SELECT id, movement_type, distribution_id
FROM relief_stock_movements
WHERE (movement_type = 'distribution' AND distribution_id IS NULL)
   OR (movement_type <> 'distribution' AND distribution_id IS NOT NULL);

SELECT distribution_id, item_id, COUNT(*) AS duplicate_count
FROM relief_stock_movements
WHERE distribution_id IS NOT NULL
GROUP BY distribution_id, item_id
HAVING COUNT(*) > 1;
```

## Module and relationship map

| Module | Tables and relationships |
|---|---|
| Accounts | `users`; public registration inserts resident role only; staff use admin role |
| Registry | One `households` record has many `household_members`; optional assessment author links to users |
| Incidents/images | `incident_reports.reported_by` links to the creator account; optional reviewer links to users; one report has many `incident_images` |
| Response | `responders` stores teams; `incident_assignments` joins reports to teams, with one assignment per report/team pair; `response_updates` stores dated action/status history and optionally references an assignment |
| Evacuation | One center has many `evacuation_records`; each record identifies either a household member or a walk-in |
| Relief inventory | `relief_items` stores global stock per item/unit; `relief_stock_movements` records all increases/decreases |
| Relief distribution | `relief_distributions` is one transaction for one household and representative; `relief_distribution_items` holds its goods; movement rows reference that distribution |
| Announcements | `announcements` links to its author and has explicit publication state |
| Settings/reports | `system_settings` is one row; reports use SELECT queries, with no report table |

The existing `households.evacuation_status` describes the staff-entered household advisory state; it is not an occupancy counter. Members may be split across centers. The household representative's name is not automatically a member record: enter that person as a member when applicable.

## Vulnerability definitions

| Stored category | Meaning |
|---|---|
| `unassessed` | Default; staff have not recorded an assessment |
| `no_additional_need_identified` | Staff reviewed the household and identified no additional assistance need; this does not certify disaster safety |
| `assistance_needed` | Extra help is needed with warnings, evacuation, or relief access; record a practical assistance note |

These are working team definitions from Phase 1, pending stakeholder validation. Store assessment notes/source in `vulnerability_notes`, the responsible admin in `vulnerability_assessed_by`, and the assessment date in `vulnerability_assessed_at`. Require all three when saving an assessed category. Setting the category back to unassessed clears those assessment fields. Hazard type/notes are separate and do not automatically set vulnerability. A future edit history is outside this initial assessment snapshot.

## Reporting, verification, and history

- `reported_by` always identifies the logged-in creator. For resident submissions set `report_source='resident'`; for admin-entered walk-ins set `walk_in`, record their name in `reporter_name`, and use `contact_phone` for the supplied contact. Admin observations use `admin`.
- `occurred_at` is optional actual occurrence time; `created_at` is when the website received the record. Initial reports are pending. Residents can read only their own submissions; staff can read all. No incident publication flag exists in this version, so status changes never publish incidents.
- `reviewed_by`, `reviewed_at`, and `review_notes` represent verification/rejection. Save review metadata, status, and a `response_updates` history row in one transaction. Rejection needs a reason. Record every later status change with `from_status`/`to_status` and `action_taken`; note-only updates leave status snapshots NULL.
- Allowed incident transitions: pending to verified/rejected; verified to responding/resolved; responding to resolved. Resolved and rejected are terminal initially. Verified-to-resolved requires an explanation if no response was needed.
- Team statuses are assigned/on_scene/completed/cancelled. New assignments require a verified/responding incident and an active team. Before resolving, complete or cancel outstanding assignments. Keep the existing assignment row when changing status; record each change in response_updates with assignment_id and assignment_status. A referenced assignment must belong to the same incident, enforced in PHP.
- FKs protect referenced records with RESTRICT. Deactivate accounts/teams rather than deleting records with history. Incident images protect their incident and uploader. Permanent media removal needs an explicit authorized process coordinating database and disk files; don't cascade-delete histories.

## Image handling contract

Incident images are optional per report, but upload functionality is mandatory. Allow at most 3 images per report in the initial UI, each up to 5 MiB, JPEG/PNG/WebP. These small limits are implementation defaults. Store generated basenames in `stored_filename`, the original display name separately, verified MIME type, byte size, uploader, and upload time. MySQL stores no image bytes or absolute paths.

Files belong under the configured incident-upload directory outside Git. Validate actual file type/size, generate filenames on the server, disallow execution in the upload directory, and serve images through an access-checked PHP endpoint because filenames alone are not authorization. Only staff and the incident's submitting account may view them. Clean up staged files if a database save fails. The maximum image count, ownership, file validity, and physical file handling are PHP responsibilities; the database enforces links, unique stored names, supported MIME values, and positive size up to 5 MiB.

## Occupancy and relief consistency

- Household size is COUNT of household_members per household; do not add a second count column.
- Evacuation occupancy is COUNT of records whose checked_out_at IS NULL, per center. Check-in takes a transaction: lock the center row, confirm it is open and below capacity, lock the selected registered member row (if any), check active registrations across all centers, then insert. Use one consistent lock order. Capacity edits also lock the center and reject values below occupancy. Do not close a center with active occupants without an agreed transfer/checkout workflow.
- Walk-in names are not unique person identifiers. Ask staff to review likely duplicates; do not globally prohibit different people with the same name. A registered member must have at most one active check-in, enforced by the PHP transaction across centers.
- Each relief item has one global balance, not stock per storage location. relief_locations labels where a distribution happens; it does not model separate warehouse balances. Transfers/per-location inventory are future scope if requested.
- All quantities are positive whole units. Create items with zero balance; stock changes only through movement workflows. To distribute, verify a registered affected/both household, require at least one item and a representative, begin a transaction, lock all item rows in ascending ID order, validate stock, insert header/details, decrement balances, and insert matching distribution movements. Commit everything together. Stock-in/out also lock and update balance plus movement in one transaction. Roll back on failure.
- The stock CHECK requires a distribution_id exactly when movement_type is distribution. A unique distribution/item pair prevents double movement entries for a distribution. PHP must ensure those quantities match its detail rows. Never deduct twice through both stock-out and distribution actions.
- Completed stock/distribution history is not directly edited/deleted. Corrections need a separately recorded movement with an explanation. Item units cannot change after movement history exists. Keep quantity_on_hand equal to stock_in minus stock_out/distribution quantities.

## Summary queries and acceptance handoff

`database/report_queries.sql` contains read-only examples for household size, affected-household counts, occupancy, incident counts, and distributions. Affected-household counts use registry_reason affected/both and mean the current registry designation. They are not disaster-specific or historical affected counts; incident-to-household impact tracking would require a future scope decision.

The team should verify a fresh import, a populated v1.0 upgrade, FK protection, and the PHP transaction flows against a disposable development database before calling implementation ready. This phase has not executed an import or changed any running database. SQL/schema review is not evidence that application transactions or access checks are implemented.
