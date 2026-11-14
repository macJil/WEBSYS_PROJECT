# Phase 8 — Integrated acceptance

Date: 2026-11-14. Branch: bayanready-household-map-simplify.

The successful integrated run used one randomly named, disposable MySQL database shared by HTTP tests and browser review, a temporary copy of the current application, and fictional accounts/records only. The runner removed the database and stopped its server afterward. Earlier unsuccessful test attempts also cleaned up their databases. No application database, schema or production record was changed.

## Results

| Area | Checked | Result |
| --- | --- | --- |
| Access | Anonymous and resident GET/POST requests to eight staff pages; anonymous map JSON; owner versus another resident's incident detail | Passed: login redirect, forbidden staff access, map JSON 401, other-owner detail 404 |
| Registry/incidents | Create/edit/reload of household and incident text/points; incident verification; private owner detail | Passed |
| Response | Team creation/edit, unused-team deletion after review, assignment, response action | Passed; response history remains |
| Evacuation | Center create/edit, five-member arrival, individual checkout, partial return, duplicate-member batch, full-center rejection | Passed; rejected batches leave occupancy unchanged |
| Inventory/distribution | Good creation/edit, stock-in, household distribution, excess-stock rejection | Passed; rejected distribution leaves stock and movement totals unchanged |
| Announcements | Draft, publish, unconfirmed withdrawal rejected, confirmed withdrawal | Passed |
| Summaries | Rendered incident, center and relief summaries; separate deterministic summary checks | Passed; historical summaries remain read-only |
| Shared map | Both roles: pending/rejected excluded, verified/responding/resolved included; exact incident response field allowlist; no household layer; storage-only point excluded | Passed |
| Coordinates/address | Four forms preserve text and coordinates after edit/reload; browser map click fills point; clear keeps manual address | Passed |
| Dashboards | Available capacity and occupancy checked against center details before arrival, after five arrivals and after checkout; cards open matching pages | Passed |
| History/protection | Referenced household/incident/center/good delete rejected; response updates, distribution detail and checked-out stay retained | Passed |
| Links/actions | 35 rendered page states, 62 role-specific local link destinations, save redirects, cancellation and explicit unused-team deletion | Passed |
| Responsive browser | 390×844 and 1366×900: registry, incident form, response, evacuation, inventory, distribution, announcements and summaries | No page-wide horizontal overflow or displayed error alerts in 16 page/width checks |
| Browser interaction | Staff/resident dashboards, mobile Menu expansion, logout/login, live map tiles/markers, Show all locations, map click and Clear pin | Passed |

The map and summaries are read-only projections, not separate CRUD tables. Operational stays, stock movements and response updates retain history rather than supporting destructive CRUD.

## Short issue log

| ID | Finding | Fix | Recheck |
| --- | --- | --- | --- |
| A8-01 | New test wrongly expected anonymous map JSON to redirect to login | Changed assertion to the endpoint's intended HTTP 401; no application change | Complete integrated rerun passed |
| A8-02 | Older acceptance scripts assume removed publication controls | Added tests/integrated_acceptance.py to the current persistence runner, exercising automatic status visibility and safe fields | Both roles and all five statuses passed |
| A8-03 | No application defect found in the executed acceptance cases | No application code change needed | HTTP, browser, map/picker and summary checks passed |

## Reproduce and evidence

- `PYTHONDONTWRITEBYTECODE=1 python3 tests/household_map_persistence.py`: one isolated MySQL fixture; includes simple_actions.py and integrated_acceptance.py. Uses the local MAMP PHP/MySQL installation.
- Add `TEST_BROWSER_PAUSE=1` to retain that same fixture during browser review; press Enter afterward for cleanup.
- `node tests/revision3_map.js`, `node tests/revision4_picker.js`, `node tests/household_map_address.js`: map/picker checks including 23 address scenarios.
- `php tests/phase10_summary.php` and `php tests/revision5_dashboard.php`: 10 summary and 7 dashboard checks in separate in-memory SQLite fixtures; these are supplemental unit checks, not extra integration databases.
- Browser evidence: artifacts/phase8/desktop-map.png, phone-resident.png, layout-checks.json.
- `git diff --check` passed.

## Explicit limits

Current-location success/denial, unavailable Leaflet, stale lookup results, timeouts and provider errors were exercised with simulated browser APIs. No real device location was requested. Live map tiles and point selection were verified, but a successful live address-provider response and actual operating-system permission prompts are not claimed. Image-upload regression, concurrency stress, and every possible CRUD input were not re-executed in this phase. Representative layout checks are not exhaustive device certification.

Atok remains a municipal demo area. The final barangay, approved boundary, stakeholder rules and approval to send real selected coordinates to an address provider remain future decisions.

No commit or push was performed.
