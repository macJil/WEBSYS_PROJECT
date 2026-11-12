# Phase 6 — Database review and existing-install upgrade

## Decision

Fresh installs now create 17 tables. Removed only `system_settings` and its fictional seed row from `barangay_drrm.sql`. Application, view, test, and migration reference searches found no runtime consumer. The demo label already exists as `demo_barangay_name` in the config example and ignored local config and is read by the footer/map. No credentials or existing local settings changed.

Retained all image, assignment, response update, distribution-item, stock movement and evacuation history tables and their foreign keys.

## Walk-in field review

Keep `evacuation_walkins` as the reusable identity for returning walk-ins. Its ID participates in the unique active-stay constraint and archive protections. Keep `evacuation_records.walkin_name` and `walkin_contact`: they record name/contact at check-in, are used in stay lists and summaries, and support legacy stays whose `walkin_id` is NULL. The check-in code explicitly checks matching legacy active names/contacts to prevent an accidental duplicate. Removing these fields would need a historical-data conversion and changed constraints; no simpler equivalent has been demonstrated. Age/address currently come from the walk-in master, so they should not be described as immutable historical snapshots.

## Existing databases — optional upgrade, not automatically applied

The app continues to work with the unused table present. Do not reimport the fresh schema into an existing database. No live database was changed in this phase.

Before choosing to remove the old table:

1. Back up the database and export `system_settings` separately; verify the export can be restored.
2. Confirm with teammates that no local scripts, views, triggers, events, integrations, or other applications use it. Repository reference checks cannot establish this for external code.
3. Inspect the row privately. If it contains a custom barangay label, carry it into `demo_barangay_name` in your ignored `config/config.php`. Keep the demo disclaimer until a stakeholder is confirmed. If an emergency contact exists, preserve it in the export and agree its destination before removing the table; do not silently discard it or commit it.
4. Confirm the footer/map show the intended local label. Check that no foreign key references the table using `information_schema.KEY_COLUMN_USAGE` in your selected schema.
5. Only after these checks, an administrator may execute `DROP TABLE system_settings;` against the explicitly selected database. MySQL table removal is not transactional. This is a manual, optional maintenance step, not part of migrations 001–004 and not an application action.
6. Recheck login, registry, incidents, response, centers, relief, announcements, maps and summaries. Restore the exported table if external dependencies are discovered.

Keeping the table temporarily is also a valid upgrade choice; the application does not read it.

## Verification

Fresh-schema verification is recorded below after importing into a separate fictional schema. Existing operational tables, foreign-key restrictions, and walk-in constraints are unchanged. The full workflow suite is not required again for removal of an unreferenced fresh-install table; schema import and constraint comparison directly check the affected artifact.

Verification passed: a fresh isolated MySQL import created exactly 17 tables without `system_settings`; both evacuation tables remained. Text comparison against the prior schema confirmed every other table definition and constraint is identical. The config example contains the demo label. No existing database/table was dropped during verification.
