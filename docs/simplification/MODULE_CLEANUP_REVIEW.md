# Shared PHP and module cleanup review

The first safe shared change removed the unused `flash()` type argument. `input()` still needs its optional trim argument because passwords must preserve spaces. Optional PDO constructor arguments remain because isolated tests inject a test connection. Date, phone, username, and stored-image checks still use `preg_match()` where replacing it would add more code or change accepted input.

Work order and minimum recheck for each module:

| Module | Keep | Next cleanup and recheck |
| --- | --- | --- |
| Registry | Household/member CRUD, role guard, member count query, vulnerability category | Format long controller/model lines; recheck add, edit, search, and resident denial. |
| Incidents | Resident submit/own list, staff entry/review, image upload, private/public choice | Update old publication tests to saved-point behavior; recheck submit, status, image access, and public map response. |
| Response | Team CRUD, assignment and action records | Format dense branches; recheck assignment status and history. |
| Evacuation | Center CRUD, capacity, registered and walk-in stays | Recheck duplicate check-in, checkout, occupancy, and closed-center visibility. |
| Relief | Goods and location CRUD, stock movements, multi-good distribution transaction | Recheck insufficient-stock rollback and historical totals. |
| Announcements | Draft, publish, withdraw, resident listing | Recheck drafts stay staff-only. |
| Summaries | Live SQL totals and printable view | Recheck counts against fixture rows. |
| Map and dashboards | Separate resident/staff queries, saved points, role-specific cards | Recheck response field allowlist, marker controls, card/list parity, and both screen widths. |

Do not remove response updates, evacuation stays, stock movements, or distribution items as “logs”; they are required records. The old phase-specific publication tests still refer to deleted public-label and public-coordinate columns, so they must be revised before the full suite can be used as an acceptance gate on this branch.
