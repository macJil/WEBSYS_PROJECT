# Phase 12 — Integration and end-to-end review

Review date: 12 November 2026 (Asia/Manila).

## Environment and scope

All module workflows were exercised through HTTP against one fresh MySQL 8.0.46 database using PHP 8.5.12. MySQL ran as an isolated loopback instance on port 18960, with a separate temporary data directory and socket. PHP served the application on port 18961. Only fictional accounts, households, images, and records were used. No existing development database was changed. Temporary local connection settings are not committed.

The initial sandboxed MySQL initialization crashed. Retrying the same binary outside the sandbox, using a new isolated data directory, succeeded. This allowed actual MySQL verification that earlier phases could not complete.

## Issues found and resolved

| Issue | Resolution | Verification |
|---|---|---|
| Null bytes in date filters caused HTTP 500 in incidents, movements, announcements, and summaries | Added shared strict date validation before calling PHP's parser | All four endpoints reject null-byte, impossible, and nonsensical dates with HTTP 400 (12 checks) |
| Staff home emphasized only households; resident home was a placeholder | Added integrated staff module links and resident task links | Shared navigation and dashboard links return successful responses for the correct role |
| Legacy community map was a blank page | Redirects to the guarded resident incident location form | Included in role/access coverage |
| Two unused map scripts referenced an assumed Irisan boundary and absent local assets | Removed unused scripts; confirmed no PHP page referenced them | Active incident map scripts remain; Phase 11 interaction checks pass |
| PHP regression fixtures could be requested through a web server | Marked executable PHP fixtures CLI-only, including the new seed helper | HTTP requests return 404; normal CLI fixtures still pass |

Code fixes were committed separately from review records and supporting test material.

## Completed workflow checks

The final main HTTP suite passed **189 checks**, including its disposable-fixture guards. It runs actual PHP forms, follows redirects, checks HTML responses, and compares database state.

| Module / concern | Verified behavior |
|---|---|
| Accounts and roles | Public registration creates residents despite forged admin role; login; anonymous GET/POST protection for every standalone module page; residents denied every admin page/form; admin denied resident-only pages |
| Households and members | Add/edit/search; escaped stored text; member age zero and editing; count from member rows |
| Incidents and maps | Resident report, staff phone report, source forced server-side, coordinate range rejection, search, staff review; other residents cannot read incident or image |
| Images | PNG, JPEG, WebP accepted; invalid content and oversize rejected without incident insertion; original filename never becomes storage path; authorized streams return expected bytes/type; no-store/nosniff headers; direct-path guessing does not expose file bytes; invalid image IDs rejected |
| Response | Verify before dispatch; multiple teams; responding status; premature closure rejected; complete assignments before resolution; six retained review/response events; team edit/deactivation |
| Evacuation | Create/edit/search centers; member/walk-in check-in; duplicate active stays across centers rejected; full capacity and capacity reduction rejected; checkout; close empty center; history retained |
| Relief | Add/edit/search goods; stock-in/out; unit changes blocked after history; unregistered household rejected; insufficient stock rolls back all changes; multiple goods saved with linked movements and correct balances; movement search/filter |
| Announcements | Draft private even with forged resident status filter; publish shows escaped text; withdrawal hides notice |
| Summaries | Real MySQL queries match the combined fictional records; no household names, receiver names, or contact numbers appear in report content |
| History | MySQL rejects deletion of referenced households, members, incidents, teams, centers, goods, and distributions |
| Integration | Shared navigation links resolve; all workflows use the same database, session helpers, and layouts |

No live deletion was attempted: foreign-key deletion checks targeted only fictional records in the isolated fixture.

## MySQL upgrade and concurrency checks

- Imported the original `master` v1.0 schema into a second isolated database and applied migrations 001, 002, and 003. All succeeded.
- Compared table/column names, types, nullability, and generated expressions between fresh and upgraded schemas: matched.
- Two parallel distributions competing for insufficient shared stock: one committed, one rejected; balance stayed correct.
- Two parallel admissions competing for the last place: one committed, one rejected; capacity was not exceeded.
- Two simultaneous check-ins for one registered member at different centers: one committed, one rolled back; only one active stay remained. In this run MySQL detected a deadlock and the application handled it as a failed operation. Staff may retry after checking the existing stay; automatic transaction retry is not implemented.

These concurrency checks used separate PHP processes/connections, not the single-worker development HTTP server.

## Summary checkpoint on shared MySQL data

After the workflow suite and concurrency fixtures, before additional image-format fixtures:

- Affected households: 1.
- Incident reports: 2.
- Active evacuees: 1.
- Distribution events: 2, to 1 distinct household.
- Distributed goods: 8 food packs and 3 water bottles.

The real summary model returned these values. Additional image-format tests subsequently added their own fictional incident rows; the above values describe the stated checkpoint, not the final mutable test database.

## Repeating the main HTTP suite

Use a disposable instance only. The suite inserts fictional records and expects an otherwise empty fresh schema plus one demo admin.

1. Start a dedicated MySQL instance on loopback port **18960**, socket **/private/tmp/websys-phase12.sock**, using a separate temporary data directory. Never use your development or production server for this fixture.
2. Import root `barangay_drrm.sql` into that instance.
3. In its `barangay_drrm` database, explicitly create the marker:

   ```sql
   CREATE TABLE websys_integration_fixture (label VARCHAR(30));
   INSERT INTO websys_integration_fixture VALUES ('fictional-only');
   ```
4. Temporarily configure ignored `config/local.php` for port 18960, database barangay_drrm, base_url empty, and a private image directory outside the served repository. Preserve/restore any existing local config. This fixture instance used temporary local root credentials; do not commit local settings.
5. Run `php tests/phase12_seed.php`. It refuses non-CLI requests, an unmarked connection, the wrong port, or an existing user database.
6. Serve the project at **http://127.0.0.1:18961** using PHP, with upload_max_filesize 5M and post_max_size 8M. Use a dedicated temporary session directory.
7. Run `python3 tests/phase12_http.py`. Python uses only its standard library and the installed `/usr/local/mysql/bin/mysql` client. The script checks the fixture marker and application's database connection before writing. Console output contains check labels only.
8. Stop the temporary servers and restore local configuration. Recreate only the disposable fixture database before repeating; the script deliberately does not reset databases itself.

Other reproducible regressions: `php tests/phase10_summary.php`, `php tests/phase11_location.php`, and `node tests/phase11_location.js`. All passed again during integration. PHP syntax checks passed across app/config/views. No real contacts, images, or database dumps are committed.

## Remaining limits

- Real-browser visual checks (including print pagination and live external map tiles) were not performed. HTTP and scripted interaction checks cover behavior, not visual presentation.
- The fixture uses the PHP development server; deployment server configuration and stakeholder acceptance remain separate work.
- Walk-in identity matching still depends on staff reconciliation if a person supplies different details or also has a household-member record.
- The agreed classroom scope still excludes CSRF tokens. This review does not claim a production security certification.
- There is no confirmed barangay boundary, approved real location catalog, or automatic hazard prediction.

**Checkpoint:** Each required workflow passed on the same fictional MySQL database and shared navigation. The limitations above remain explicitly recorded.
