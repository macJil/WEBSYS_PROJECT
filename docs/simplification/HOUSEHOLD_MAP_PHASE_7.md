# Phase 7 — Module cleanup

Reviewed against the current household check-in, shared picker, Atok map, capacity, and button changes. No tables or columns changed; no migration is needed. Existing work from phases 1–6 is retained.

## Changes and retained behavior

| Module | Cleanup / review | Workflow evidence |
| --- | --- | --- |
| Registry | Replace field-value and update-assignment arrow functions with one ordinary foreach. Keep archive checks and calculated member counts. | Fictional household create/edit and saved-point reload; household-member check-in tests. |
| Incidents | Remove an unreachable empty-WHERE branch; build the location-correction history message in named steps. Keep ownership, uploads, review and history. | Create/edit/reload, review and response redirects, owner/status count parity. |
| Response | Remove redundant sorting by is_active when all selected teams are active. Keep assignment locks, transitions and response actions. | Team saves, assignment and response-update submissions. |
| Evacuation | Remove unused members() methods from model and controller, superseded by households()/householdMembers(). Expand compressed return/archive checks. Keep existing single-person compatibility check-in, walk-ins and stay history. | 83 fictional MySQL checks including five-person arrival, failed batch rollback, checkout, walk-ins and historical totals. |
| Relief | Use named data/statement/location variables and braced steps in location handling. Expand stock addition/subtraction. Keep stock movements and multi-good distribution transactions. | Goods, stock and distribution submissions; location create/edit/reload; summary multi-good totals. |
| Announcements | Remove unreachable empty-WHERE branch. Keep draft/published filtering, first-publication history and withdrawal confirmation. | Draft, publish, blocked unconfirmed withdrawal and confirmed withdrawal. |
| Summaries | Put returned summary fields on separate lines. Retain the short query helper and shared read transaction. | Ten summary checks: period edges, affected households, occupancy, grouped goods and empty periods. |
| Maps/dashboard | Retain current shared scripts, privacy field allowlists and shared availableCapacity helper. Remove retired incident-map.js and demo-locations.js, which no current page loads. | Shared map, picker, address-suggestion and dashboard/list parity checks. |

The obsolete tests/phase11_location.js was removed with the retired scripts it exclusively exercised. Its old fictional search-catalog behavior is no longer in the application. Current interaction coverage is tests/revision4_picker.js and tests/household_map_address.js. Older phase documents mentioning those removed files describe historical implementations, not current test commands.

## Verification and issue record

- PHP syntax: all 67 application, view, root and configuration files passed.
- tests/household_checkin.php: all 83 checks passed in a disposable fictional MySQL database.
- tests/household_map_persistence.py: create/edit/reload for four location-bearing records; household arrivals/checkout; resident staff-page rejection; all tests/simple_actions.py submissions passed. Checked 35 rendered page states and 62 role-specific local link destinations.
- tests/phase10_summary.php: 10 passed; tests/revision5_dashboard.php: 7 passed.
- tests/revision3_map.js and tests/revision4_picker.js: passed with both roles and with/without Leaflet respectively.
- tests/household_map_address.js: all 23 checks passed.
- git diff --check: passed. No current app, resource or test references to the retired assets remain.
- No failing workflow was found in these checks. Duplicate/dead branches and the obsolete evacuation listing were removed and rechecked as above.

No layout or browser integration was changed in this phase; phone/desktop visual evidence remains the Phase 6 review. The JavaScript checks use simulated browser APIs, not a fresh live geolocation/provider test. This is targeted regression coverage, not a claim that every possible input has been exercised. Temporary databases were removed by the test runners. Real records were not used.

Changes remain local on bayanready-household-map-simplify; no commit or push was performed in this phase.
