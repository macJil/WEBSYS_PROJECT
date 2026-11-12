# Revision 7 integration review — 12 November 2026

Status: automated integration passed; device/browser acceptance remains partially open.

## Environment and evidence

Tested revision6 commit ed4669d in a separate copied checkout, using MySQL 8 on localhost port 18964, database `websys_revision7_fixture`. Only fictional accounts, contacts, records and images were used. Production/local application settings were not changed. Private test images were stored outside the test web root.

- Existing `phase12_http.py`: **216 checks passed** on the current schema. Covers registration/login, anonymous GET/POST and cross-role access, household/member editing, incident uploads and private access, responses, evacuation, inventory/distribution, announcements, summaries, foreign-key restrictions, and navigation.
- Added `revision7_http.py`: **29 checks passed** on that SAME database after the earlier workflow suite. Covers anonymous map/data/dashboard/selection access, resident selection rejection, resident map field allowlist despite forged role query, and removal after withdrawal, closure, and relief-point unapproval. No-point incident remains stored.
- Earlier isolated regression suites also passed: revision2 map/publication, revision3 map rendering, revision4 picker, revision5 dashboard, phase10 summaries, phase11 PHP and JavaScript location checks. These supplemental unit fixtures are separate from the shared MySQL HTTP fixture.
- Browser: signed into the shared fixture as admin; dashboard cards and navigation rendered. Opened staff map; legend text, named layer checkboxes, mapped counts, closed-center text alternative, map controls, and map limitations were present in the accessibility tree.
- Revision6's previously recorded 20 database checks cover single/batch actions, rollback, stock/occupancy archive blockers and history retention. These were not rerun in this Revision7 database; they remain prior evidence, not a new integrated pass.

## Issue log

| Issue | Resolution | Recheck |
| --- | --- | --- |
| Temporary root-served fixture inherited `/WEBSYS_PROJECT` base URL | Set base_url to empty in temporary fixture only | Login/navigation and 216 checks passed |
| Temporary fixture lacked private image directory setting | Set incident_upload_dir to a private temporary folder outside web root | Upload/private image checks passed |
| Existing integration suite omitted map JSON withdrawal/access checks | Added revision7_http.py, runs after phase12 suite | 29 checks passed |

No application defect was established by these runs. No schema migration was needed.

## Remaining acceptance work and decisions

- Actual browser map click/drag, manual coordinate save/edit, permission denial/approval, low-accuracy feedback and offline tile fallback still need a full browser run. Simulated picker tests passed; they do not prove operating-system permission behavior. Use a fictional/simulated device location, not personal coordinates.
- Resident browser legend/layout and visual accessibility review remain open; resident network projection checks passed.
- Re-run the complete checkbox review/confirmation matrix on the same integrated fixture, including concurrent stock/check-in attempts and dashboard/history totals before and after batches. Prior isolated transaction results do not substitute for this acceptance step.
- Team decision remains required before permitting occupied-center or positive-stock item archival. Current behavior rejects both. No approved barangay boundary is assumed.

## Reproducing

Use a disposable schema and copied checkout only. The existing phase12 suite defaults to its documented fixture port/socket/database. For this run, copies of phase12_seed.php and phase12_http.py were adjusted to port 18964, socket `/private/tmp/websys-rev6.sock`, and database `websys_revision7_fixture`; config/config.php in the copied checkout used the same connection, empty base_url, and private temporary image storage. Import the fresh schema with its database name replaced, create `websys_integration_fixture(label)` containing `fictional-only`, seed, serve on port 18961, run phase12_http.py then revision7_http.py. Never reset or run these destructive fixture workflows against real data.
