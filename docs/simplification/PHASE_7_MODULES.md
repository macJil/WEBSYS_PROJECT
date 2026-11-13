# Phase 7 — Module readability and naming

Worked through registry, incidents, response, evacuation, relief, announcements, summaries, map and dashboard controllers/views in that order. Each module's controller call sites were updated together and PHP syntax checked before the next module. The initial mixed-template formatter produced invalid registry PHP; that output was discarded before proceeding. The final approach preserves embedded PHP while indenting HTML, and uses PHP-aware whitespace formatting separately.

Clearer controller names include `recordId`, `getHousehold`, `listHouseholds`, `incidentId`, `getIncident`, `listIncidents`, `queryValue`, `listAnnouncements`, `getAnnouncement`, `mapData`, and `dashboardData`. Models keep their existing domain query methods. Existing POST action values already name operations (create/update household, save team, save center, distribute, publish/withdraw); they were retained so forms and controllers continue to agree.

Maintained PHP, view markup, active/custom map scripts and shared CSS received spacing/indentation improvements. Added .editorconfig for four-space indentation in future edits. Vendor libraries are unchanged. Compatibility entry pages remain because current dashboard links still use them; no route or role guard was removed.

Verification: module PHP syntax checks passed. Map rendering, picker with/without Leaflet, 25 address-suggestion checks and dashboard checks passed. A fresh marked fictional MySQL fixture then passed 222 core workflow/access checks, 29 map privacy checks, 47 publication/archive checks and 22 dashboard/list parity checks: 320 total. No real database was modified. Browser visual checks were not repeated in this phase.
