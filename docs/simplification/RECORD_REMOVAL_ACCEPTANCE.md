# Record removal and address/upload follow-up

Verified 2026-11-14 using the disposable fictional MySQL fixture in tests/household_map_persistence.py. The earlier unavailable-database blocker is resolved.

Manage Records now has one searchable, paginated table with an All record types filter and per-record checkboxes. Delete selected uses a JavaScript confirmation. Unused records are permanently deleted; linked records are hidden from active lists using existing archive fields (teams are deactivated). Stock/distribution/response/stay history remains linked. Occupied centers, active stays/assignments, ongoing verified/responding incidents, and goods with stock show concrete steps before removal. A blocked selection changes nothing.

The successful run covered zero-stock good removal with stock/distribution history retained, active-record rejection, ordinary deletion, role/ownership guards, household capacity and rollback, record persistence, map field privacy, dashboard parity, 35 rendered page states and 62 link destinations. A real multipart PNG upload and owner/admin retrieval passed; a fake image was rejected without creating a report. The temporary database and image directory were cleaned up.

Address/picker checks passed (23 address scenarios and picker behavior with/without Leaflet). All 64 app/view PHP files passed syntax checks. The shared address lookup now uses a signed-in, rate-limited PHP endpoint with a fixed Photon destination; selected points remain editable and manual entry survives lookup failures. The PHP cURL extension and outbound HTTPS are required. Live provider retrieval was checked in the preceding fix; actual device permission prompts were not repeated in this test run.

Local upload configuration was repaired separately in the ignored MAMP config.php. It must not be committed: each teammate must configure their own private incident_upload_dir outside the web root. No database migration is needed.

Button styling and asset version strings are included. This run did not repeat a visual phone/desktop browser review of the newly simplified records table.
