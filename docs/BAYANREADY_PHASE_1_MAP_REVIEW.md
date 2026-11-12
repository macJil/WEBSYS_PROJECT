# BayanReady Phase 1 — map investigation and locked behavior

Completed 12 November 2026 on `bayanready-map-responsive-frontend`.

## Locked view

Use the recommended focused opening view: center **16.41792, 120.56091**, opening zoom **15**, minimum zoom **14** on the community/staff map and shared form picker. Panning, zooming in, and point selection remain available. The view may include nearby areas; it is not an Irisan-only geographic boundary. Do not add maxBounds, a fabricated polygon, or geographic SQL filtering. Keep `map_boundary_approved=false`.

The existing center was visually checked against the Irisan label on the [OpenStreetMap basemap](https://www.openstreetmap.org/#map=15/16.41792/120.56091). This verifies an approximate demo view, not an official centroid, boundary, stakeholder identity, or address. In-browser zoom-out stopped after one step from 15, with the zoom-out control reporting aria-disabled=true at 14. The Return to demo area control retains the fixed opening view. Widely separated markers cannot necessarily fit simultaneously at minimum zoom; pan or open the protected incident detail to review its individual saved point.

## What was traced

`incidents/create.php` reads the shared picker inputs. `ReportController::create` requires a written location, validates coordinate ranges and pair completeness, fixes reporter/source values, and converts empty coordinate pairs to NULL. `ReportModel::create` saves those fields in one transaction. No lookup currently converts a written address into coordinates.

`MapModel::admin` includes unarchived incidents with valid coordinate pairs regardless of status. `MapModel::resident` uses an explicit safe-column projection and requires publication, eligible status, a label, and separate valid public coordinates. It never substitutes the reporter's private point. `maps/data.php` checks the session role, allows only GET, and sends no-store headers. The map script fetches once on opening with cache:no-store. An already-open page is a snapshot; use Refresh saved locations after a new report. Layer checkboxes affect display only.

## Fictional incident diagnosis

All application requests below used the same new MySQL database, `bayanready_phase1_fixture`, on isolated port 18964. The fixture contained only two fictional accounts and three reports created through the ordinary resident form. State transitions below refer to fixture report 1; they were applied by test SQL solely to exercise query rules.

| Test report/state | Staff map | Resident map | Explanation |
| --- | --- | --- | --- |
| 1: new pending report, valid Irisan point | Included immediately on next request | Hidden | Public approval is separate from submission |
| 1: verified but unpublished | Included | Hidden | Verification does not publish |
| 1: verified, safe label and public point, published | Included at private point | Included at separate public point | Both queries follow their role rules |
| 1: resolved and published | Included | Included | Resolving alone does not withdraw |
| 1: rejected | Included | Hidden | Rejected status is ineligible for public display |
| 1: visibility switched off | Included | Hidden | Explicit withdrawal |
| 1: incomplete public coordinate pair | Included | Hidden | No private-coordinate fallback |
| 1: archived | Hidden | Hidden | Active maps exclude archived records |
| 2: written location only | Hidden | Hidden | No coordinate pair; record still saved |
| 3: older test point 12.8812, 121.7763 | Included in data | Hidden, pending/unpublished | Far outside Irisan opening view; not a failed save |
| Invalid submissions: partial pair, latitude 91, nonnumeric latitude | Not created | Not created | Controller rejects the form before saving |
| Map snapshot captured before report 3 | Old marker collection | Subject to same snapshot behavior | Fresh request contains report 3; existing page does not poll |

The distant coordinates were found in the existing `tests/phase12_http.py` fixture and reproduced deliberately as the outside-view case. No real/user-created data was inspected or moved. Phase 2 should update ordinary demo examples; retain an explicitly labeled distant-point case only for regression coverage.

## Findings and changes

- Preserved the five existing working-tree minimum-zoom edits and verified their behavior; configuration feeds both shared map and picker.
- Added plain instructions explaining the Irisan opening view, off-screen points, and refreshing after changes.
- No incident save/query defect was reproduced for valid new reports. Missing marker reasons above are now documented instead of assuming all reports should be public.
- **Open implementation gap:** incident staff review has no public-map publication editor. `MapController::publicationErrors` exists, but the incident detail form only updates review status/notes. Public visibility tests used fixture SQL, not a functioning staff publication UI. Schedule a small guarded editor with safe label/public-point validation in the subsequent location/publication work. Do not auto-publish reports as a workaround.
- Automatic address suggestions remain Phase 3. Branding/responsive frontend remain their planned phases.

## Verification

- `tests/bayanready_phase1_http.py`: **38 checks passed**, including real form submission, invalid input rejection, staff visibility, role-safe public responses, no-point persistence, lifecycle exclusions, refresh behavior, and settings reaching both map pages.
- Updated `tests/revision3_map.js` and `tests/revision4_picker.js`: passed with minimum-zoom and unrestricted-panning assertions, existing popup privacy/layers and picker interactions retained.
- PHP syntax checks for changed configuration/views and git diff checks passed.
- Browser: OpenStreetMap center inspection and application map render/minimum-zoom control verified. Full phone, geolocation permission and address-lookup acceptance remain later phase work.

### Reproduce safely

Use a separate copied checkout and a new database with the exact fixture name above. Import `barangay_drrm.sql` with its database name replaced, without touching other databases. Create `websys_integration_fixture(label VARCHAR(30))` containing only `fictional-only`; seed admin@example.invalid and resident@example.invalid with their matching roles and a PHP password_hash of `DemoPass123!`. Configure only the copied checkout: host 127.0.0.1, port 18964, database bayanready_phase1_fixture, empty base_url. The test mysql client expects the isolated socket `/private/tmp/websys-rev6.sock`. Serve that copy on localhost:18966 and run `python3 tests/bayanready_phase1_http.py`. It refuses a missing marker or a nonempty incident fixture. It mutates only fictional fixture reports. No image upload is used in this focused test. Stop the temporary servers afterward.
