# Phase 7 — Integrated acceptance and issue log

Date: 2026-11-13. Branch: `bayanready-map-responsive-frontend`.

## Result

The final clean-fixture run passed **320 MySQL/HTTP checks** on one isolated database:

| Suite | Passed | Coverage |
| --- | ---: | --- |
| `phase12_http.py` | 222 | Eight modules, registration/roles, direct GET/POST guards, uploads, household records, response history, occupancy, relief transactions, announcements, summaries, foreign keys, navigation |
| `revision7_http.py` | 29 | Public projection, withdrawal, center closure, relief unapproval, no-point records |
| `bayanready_phase7_http.py` | 47 | Actual publication forms, private ownership, new points, invalid public values, no-point reports, facility changes, archiving, all-or-nothing blocked batch |
| `bayanready_phase7_counts.php` | 22 | Admin and resident incident card/list parity, centers/occupancy, affected households, stays, low stock, notices |

All four ran in order against `barangay_drrm` on a dedicated MySQL instance at port 18960, with `websys_integration_fixture = fictional-only`. This is unrelated to the MAMP database. The clean run was repeated after the publication fix. Console evidence is in `docs/acceptance/phase7-results.txt`.

Additional regressions passed: PHP syntax checks across app/config/views, summary and coordinate PHP checks, resident map SQL projection, JavaScript map rendering, picker with and without Leaflet, 25 address-suggestion cases, and eight dashboard model checks.

## Defects, fixes, and rechecks

| ID | Finding | Fix | Recheck |
| --- | --- | --- | --- |
| P7-01 | Staff could not explicitly publish/withdraw incident, center, or relief map locations. Previous integration checks changed visibility through SQL, hiding this missing operational step. | Added one guarded direct PHP visibility page, small controller/model, and links from incident/center/relief details. Incident approval requires a separate safe label and approximate point; private report coordinates are never copied or altered. Row locks recheck current status/archive state. | 47 HTTP checks include actual publication/withdrawal, owner isolation, rejected/pending/archived blocking, closed-center blocking, public field allowlist, and batch rollback. Browser verified separate staff review and publication, then resident safe popup. |
| P7-02 | The new form's initial label limit was longer than the existing 150-byte database field. Caught during schema review before acceptance. | Matched PHP validation and HTML length to 150. | Overlength label rejected; valid publication succeeds. |
| P7-T1 | Initial browser test harness returned geolocation synchronously, unlike the browser API, so the suggestion was cancelled by the click handler. This was a test-harness defect. | Made the fictional callback asynchronous; no product change. | Browser displayed lookup-failure text while keeping the pin and editable written address. |

No schema migration, routing framework, or public/private map-query expansion was needed.

## Browser record

Used the in-app browser against the same isolated database before its final clean-suite reset. All names, reports, contacts, and locations represented fictional examples. The Irisan basemap is the existing demo context, not an approved boundary.

| Check | Observed result |
| --- | --- |
| Anonymous map URL | Redirected to login. |
| Resident report with map click | Selected point saved as incident #5 in that browser run; private detail showed matching coordinates after submission. Staff could open it and review the same saved point. |
| Manual coordinate entry | Numeric fields and Tab navigation updated the pin/status. HTTP checks saved manual coordinates and no-point reports into the same database. |
| Current-location approval/denial | Disposable harness used the actual shared picker scripts with simulated asynchronous geolocation callbacks. Denial displayed manual fallback. Approval showed fictional 16.4182, 120.5612 and 25m accuracy; submission saved as incident #6. No real device position was collected. |
| Address lookup failure | Forced fetch failure in that harness displayed “Address lookup unavailable…” while retaining the selected point and allowing manual typing. Automated tests additionally cover timeout, stale results, manual edits, clearing, and no request on page load. |
| Staff verification/publication | Staff verified browser report #5, then separately approved its safe public label/point through the new form. Resident popup showed type, safe label, status, date, approximate-location note; no private contact/description. |
| Ownership and withdrawal | HTTP suite checks owner access, another resident's 404, resident publication GET/POST 403, withdrawal removal, and facility changes. Browser confirmed the resident cannot open the staff publication URL. |
| Keyboard/text alternatives | Space toggled an incident layer; Enter opened a marker popup. Coordinate fields remained keyboard-editable. Legend labels and status/location text were readable. |
| Dashboard/list | Staff card showed four pending incidents; linked filtered list had four record rows. Broader parity covered by 22 checks on the final shared fixture. |
| Narrow/wide layouts | 390px and 1280px checks: household, incidents, response teams, centers, relief distribution, announcements, summaries, record actions, map. No page-wide overflow. Resident map at 390px had the legend above the map, working tiles, and no overflow. New publication form was usable at phone width. |
| Map privacy | Resident map contained only the explicitly approved incident at that checkpoint. The staff map also showed private points. Public response field allowlists checked over HTTP. |

Browser geolocation tests simulate API outcomes; they do not certify an actual operating-system permission prompt or hardware GPS. Live Photon availability was not a prerequisite: failure behavior was deliberately exercised. Other browser engines and physical devices remain useful team checks.

## Repeat the acceptance run

Follow `PHASE_12_INTEGRATION_REVIEW.md` to create an **empty, marked, disposable** MySQL instance on port 18960, import `barangay_drrm.sql`, configure ignored local settings/private image storage, seed with `php tests/phase12_seed.php`, and serve at localhost:18961. Then run:

```sh
python3 tests/phase12_http.py
python3 tests/revision7_http.py
python3 tests/bayanready_phase7_http.py
php tests/bayanready_phase7_counts.php
```

Do not run the mutating suites against MAMP or real barangay data. Fixtures use example.invalid contacts, dummy phone values, synthetic image bytes, and explicitly fictional location descriptions. Browser harness overrides belong only in an isolated test page, never application code.

## Explicit remaining stakeholder decisions

- Confirm the intended barangay/stakeholder. Irisan remains a demo label.
- Obtain an approved boundary and location catalog before adding any boundary restrictions. No polygon is assumed.
- Agree who approves public labels/approximate points and the retention/withdrawal policy with staff.
- Confirm third-party geocoding/privacy suitability before real resident use; live provider availability is not guaranteed.
- Reconcile walk-in identities operationally and define corrections for retained history. Historical records remain protected.

These are stakeholder/deployment decisions, not reasons to expose private fixture data or change the eight required modules.
