# Simplified branch acceptance and issue log

Date: 2026-11-13. Tested against a separate, guarded fictional MySQL instance on loopback port 18960 and a localhost PHP server. The fixture marker was checked before resetting its disposable schema. No real resident data was used.

## Optional-feature decisions

| Feature | Decision |
| --- | --- |
| Advanced filters | Keep basic search/date/status filters already used by required lists and summaries; removing them would not meaningfully simplify the database. |
| Bulk actions | Keep for now because this is the current admin delete/archive path. Remove only after a simpler single-record CRUD delete replaces it and linked history remains protected. |
| Low-stock alerts | Removed threshold field, dashboard card, filter, model queries, and form input. Stock quantity and movement history remain. |
| Address suggestions | Removed the third-party lookup script and controls. Manual text, map click, coordinates, and current-location selection remain. |
| Diagnostic logging | Removed application `error_log()` calls. Controlled errors remain; response actions, evacuation stays, stock movements, and distribution history remain. |

Fresh installs omit incident public-label/public-coordinate fields and the relief minimum-stock field. Existing databases require a backup and migrations 005 then 006; migration 004 remains part of the earlier upgrade sequence. Verify public incident points before deploying because the simplified public map uses the saved report point.

## Checks

- 204 core HTTP checks passed on a freshly seeded fictional schema: registration and sessions, anonymous/resident/admin guards, eight-module workflows, navigation, images, stock/distribution transactions, and history.
- 29 map/record-action checks passed: resident field allowlist, publication withdrawal, center/relief visibility, no-point records, and access checks.
- 44 publication/record-action checks passed: private default, staff-only public/private action, resolved/rejected behavior, owner access, archived records, and blocked-batch rollback.
- Standalone summary, incident location, and location-picker checks passed. PHP syntax and JavaScript syntax checks passed. The fresh fixture had zero columns named `minimum_stock`, `public_label`, `public_latitude`, or `public_longitude`.
- Browser review: all ten admin navigation destinations and all five resident destinations had headings and no page-wide horizontal overflow at 390 px and 1280 px. Map pages opened in both roles. Browser geolocation permission was not granted; automated picker checks exercised its approval/denial paths.

## Defects and rechecks

| ID | Defect | Fix | Recheck |
| --- | --- | --- | --- |
| SA-01 | Staff dashboard still linked to the deleted incident wrapper page. Core HTTP navigation check stopped there. | Linked the dashboard card to the shared incident list. | Reset the fictional database and reran the complete core HTTP suite: 204 passed. Both screen widths also opened the incident list. |

## Remaining test maintenance

Older phase-specific tests for address suggestions, separate public incident points, and low-stock thresholds describe removed features. They are not acceptance gates for this simplified branch. `revision6_records.php` expects a separate legacy test socket and was not run in this fixture. The three suites above cover current record actions and history protection.
