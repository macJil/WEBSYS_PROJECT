# BayanReady Phase 2 — location saving and fictional fixtures

Completed 12 November 2026 on `bayanready-map-responsive-frontend`.

## Changes

- Incident creation, household, center and relief-location forms continue to share one picker script and component. Edit forms populate the component with saved coordinates; failed forms explicitly pass submitted values back. The component no longer takes values implicitly from every POST, which matters when incident review and location correction share a page.
- Added a staff-only incident location correction form using the same picker. Written location and correction reason are required. Coordinate pair/range checks run on the server. Updating the point and recording the previous/new location in staff response history use one transaction. Archived incidents cannot be corrected. Status, reporter ownership, images and the approved public point are unchanged. Residents can submit reports and read their own saved location; they cannot use staff correction actions.
- Incident, household, center and relief-location lists/details now explicitly say **No map point** when coordinates are unavailable. Zero is treated as a coordinate, not as an empty field. Records without points remain available in lists, search and totals.
- Updated the older ordinary incident fixture in `tests/phase12_http.py` to fictional Irisan Sample B (16.4192, 120.5623). The existing demo location catalog already uses the verified Irisan view. The Phase 1 distant-point regression case is intentionally retained and labeled as such. No migration or mass coordinate update was run; no real or user-created records were relocated.

## Checkpoint evidence

A separate copied checkout connected only to `bayanready_phase2_fixture`, an isolated MySQL database on port 18964. It contained fictional accounts, two incidents, two households, two centers and two relief locations created through ordinary forms.

- `tests/bayanready_phase2_http.py`: **82 checks passed** in two sequential batches on that fixture (77 workflow checks, then five added correction checks). New report appeared in staff map JSON; reporter detail showed its saved coordinates. Center edit appeared in staff map JSON and staff detail after reload.
- Each master form was checked for create/edit, saving only the intended record, saved-pin input values on GET, invalid pair/range/address rejection, retention of attempted coordinates after validation errors, zero coordinates, clearing to NULL, no-point detail/list notes, record retention, and resident access rejection.
- Incident checks covered staff correction history, unchanged workflow status, invalid review preserving the saved picker point, clearing, searchable no-point reports, public/private point separation, and archived-record rejection without a history mutation.
- Shared picker JavaScript tests passed with and without Leaflet: saved edit pin, map click, marker drag, manually typed pair, simulated geolocation success/denial/timeout/unavailable API, accuracy message, clear, stale location callback, zero and invalid pair.
- Earlier shared map, incident ownership/location, and dashboard regression suites passed. Changed PHP files passed syntax checks; diff formatting passed.
- Actual browser check on the same fixture: opened center #1 edit form, observed the saved pin, clicked a map point, saved, opened staff center detail and confirmed the exact selected pair **16.4168038, 120.5596948** persisted. No real device location was requested. Real operating-system geolocation permissions remain separate from the simulated callback tests.

## Reproduce

Use the Phase 1 isolated fixture setup procedure with database `bayanready_phase2_fixture`, copied site `/private/tmp/bayanready-phase2-web`, and PHP server port **18967**. Keep the fictional marker table and seed the same two @example.invalid accounts. The test expects the isolated MySQL socket `/private/tmp/websys-rev6.sock`. Run `python3 tests/bayanready_phase2_http.py` against a fresh fixture, then `node tests/revision4_picker.js`, `node tests/revision3_map.js`, `php tests/phase11_location.php`, and `php tests/revision5_dashboard.php`. The HTTP suite refuses a nonempty incident fixture. Never point it at a real database.

## Next work

Automatic address suggestions remain Phase 3. Public incident publication controls identified in Phase 1 are still outstanding; a private location correction does not publish a report or silently move its approved public point. The current UI makes that distinction explicit. Responsive styling and the visible BayanReady rename remain in their scheduled phases.
