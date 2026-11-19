# Municipal acceptance · 19 November 2026

Application baseline: 35f6202. The runs used generated MySQL databases, fictional identities and a temporary copy of the site. Each run removed its database afterward.

| Area | Observed result |
| --- | --- |
| Installation and upgrade | The example configuration rendered registration. Missing configuration produced a setup message. The application suite ran on the fresh schema and on the pre-municipal schema from 8ebceef after migrations 009–011. The legacy household retained its name and awaited explicit barangay and representative selection. Migration 008 was also checked against populated history tables. |
| Accounts and forms | A mismatched confirmation could be corrected. Duplicate registration was rejected. Public registration produced a resident even with an admin role submitted. Login and logout, anonymous redirects, resident restrictions and other-resident incident denial passed. |
| Household registry | Create, edit, reload and barangay-filtered lists agreed. Two successive representative selections preserved household stays and distributions. Removal of the current representative required a replacement. |
| Incident reporting | Both account types received their own saved contact values. Assisted intake started blank. Invalid email retained entered values. Reporter edits stayed on the incident. PNG upload and authorized retrieval passed, and an invalid upload was rejected. |
| Response | Team create/edit, assignment, action history and reassignment passed. Completing one of two active assignments kept the incident responding. Completing both resolved it. |
| Evacuation | Five arrivals added five occupants. Partial arrival and checkout matched individual stay rows. A duplicate member or insufficient capacity blocked the entire batch. |
| Inventory and distribution | A two-item distribution deducted two bottles and two packs from separate balances of five. Asking for six of the second item preserved both balances, movement rows, distribution header and line items. Removing a linked zero-stock catalog item retained its history. |
| Announcements and summaries | Draft, edit, publish and withdrawal flows passed. Summary checks covered current households and occupancy, incident period boundaries, relief totals, empty periods and barangay selection. |
| Map and dashboard | Each of the four location forms preserved confirmed text and coordinates after edit and reload. Role-specific HTTP requests checked allowed marker fields, incident statuses, barangay selection and exclusion of households and storage sites. The picker suite exercised 23 cases including denied permission, stale responses and timeout. Dashboard, center list, center detail and summary totals matched after check-in and checkout. |
| Weather | The browser rendered a fictional successful forecast on the admin dashboard and the unavailable message on the resident dashboard. External requests were blocked for reproducibility. These checks cover the application’s response to the provider. |
| Print | The browser generated PDFs for the Topdac disaster summary, staff incident and owner incident. Extracted text contained the generation timezone and expected report identity or area. Staff history appeared only in the staff PDF. Print controls and navigation labels were absent. |
| History, navigation and layout | Hiding completed history preserved stored rows, stock, distribution details, occupancy and incident state. The suite checked 35 page states and 63 local destinations. Browser checks covered both dashboards, the shared map, incident forms/details and staff module pages at 375 and 1280 CSS pixels for page-level horizontal overflow. |

## Issues and rechecks

| Observation | Action and outcome |
| --- | --- |
| The older household test assumed a specific MAMP socket. | Added an optional test socket setting. The household suite and both HTTP database runs passed on the available socket. |
| The previous print check timed out launching a standalone PDF job. | Printed the authenticated application pages through the browser debugging connection. All three PDF text checks passed. |
| The first browser weather check reached the outage path during the simulated success case. | Enabled browser page instrumentation before installing the fictional provider. Both simulated outcomes passed on rerun. |

## Repeat the checks

Run from the repository root with PHP, Node and Python installed and a disposable local MySQL service available. The test runner expects the local development MySQL login used by MAMP. Set TEST_PHP and TEST_MYSQL_SOCKET if their paths differ. The upgrade test requires the repository history containing commit 8ebceef. For the browser check, set TEST_BROWSER to a Chromium-based browser executable and install pdftotext.

```sh
python3 tests/simplification_config.py
php tests/phase10_summary.php
node tests/household_map_address.js
export TEST_MYSQL_SOCKET=/tmp/mysql.sock
php tests/household_checkin.php
TEST_MUNICIPAL_UPGRADE=1 python3 tests/household_map_persistence.py
TEST_BROWSER='/Applications/Brave Browser.app/Contents/MacOS/Brave Browser' python3 tests/household_map_persistence.py
```

Physical handset use, printer margins on office hardware, and office approval of headings, paper format and signatories remain for the team to confirm. The browser tests use simulated weather and do not measure live provider availability.
