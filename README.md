# Atok MDRRM System — WEBSYS1

Official project repository: https://github.com/macJil/WEBSYS_PROJECT

Atok MDRRM System uses plain PHP pages grouped by module, shared session checks, and PDO/MySQL. Start at `index.php`.

[Setup guide](docs/PHASE_3_PHP_FOUNDATIONS.md) · [Classroom results, sample PDFs and pending pilot checks](docs/pilot/README.md)

## Fresh installation and sign-in

Import [barangay_drrm.sql](barangay_drrm.sql) into an empty database. It creates one inactive office account, **MDRRMO**. Before activating it, set a private password and confirm the office login address. The example.org addresses are website usernames.

For a classroom copy only, separately import [presentation-records.sql](database/presentation-records.sql) into the same fresh database. It adds records for the eight modules and a sample resident, **Maria Santos**. On a fresh database, both classroom sign-ins use the credentials in the SQL header. Existing databases should use their migration history instead of reimporting either file.

Generate a unique password and PHP hash for each initial account on a trusted local terminal. Store the displayed password privately.

```sh
php -r '$p = bin2hex(random_bytes(16)); echo $p, PHP_EOL, password_hash($p, PASSWORD_DEFAULT), PHP_EOL;'
```

In your local database administrator, replace the placeholder below with that account’s generated hash. The optional classroom SQL handles activation for a fresh presentation installation.

```sql
UPDATE users SET password = 'PASTE_GENERATED_HASH', is_active = 1
WHERE email = 'mdrrmo.atok@example.org' AND is_active = 0;
```

For everyday enrollment, staff issue resident accounts from verified household records. The running login page never displays credentials. See the [presentation walkthrough](docs/SUBMISSION_CHECK.md).

Use [README_FINAL.md](README_FINAL.md) for the short submission guide. For public hosting, disable directory listing and configure the server to deny browser access to config, includes, tests, repository metadata, database scripts and submission materials. Keep uploaded images outside the web root. The application uses direct PHP URLs without rewrite rules.

For an existing classroom installation, importing a fresh schema does not rotate old logins. Retire unused classroom accounts, rotate the retained accounts’ passwords and keep their IDs so their history remains linked. Use a fresh database for real office records.

## Where to start reading this branch

Follow one form from its role check through its POST handling and prepared statement to its redirect. The same page shows entered values again on validation errors.

| Workflow | Starting page | What to follow |
| --- | --- | --- |
| Sign in or out | `index.php`, `logout.php` | Email and password verification, active account, session, required first-password change, then role dashboard. Logout accepts POST. |
| Households | `households/index.php` | Registry → household → member → staff-issued account. |
| Incident reporting | `incidents/create.php` | Submission → private incident detail. Image delivery and printing have separate access checks. |
| Response | `response/incident.php` | Assignment → team progress → incident status. |
| Evacuation | `evacuation/center.php` | Selected center → arriving people → active stays and remaining capacity. |
| Inventory | `relief-inventory/movements.php` | Incoming or outgoing goods → quantity and movement history. |
| Distribution | `relief-distribution/index.php` | Household and goods → one transaction for distribution and stock. |
| Announcements | `announcements/index.php` | Create, edit, publish, or withdraw. Residents read published notices at `announcements/published.php`. |
| Disaster summaries | `disaster-reports/index.php` | Date and barangay filters → totals → printable summary. |

A protected page loads `includes/session.php` directly or through its module’s shared file and calls its role guard before processing a request. Shared input checks and escaping live in `includes/form.php`, coordinate checks in `includes/atok-boundary.php`, and display functions in `includes/display.php`. The shared header loads the display functions. The map picker serves all location forms.

**Switching an existing installation to this branch:** Keep the branch’s version of `config/config.php` and transfer your local database settings, base URL, hotline, and private upload path into its settings array. The file now defines `db()` as well. Replacing it with the entire configuration file from an older branch will display a setup message. There is no database migration for this revision.

## Presentation and verification

Use the [nine-presenter walkthrough](presentation/LIVE_DEMO_PRESENTATION_SCRIPT.md) for this branch. The eight module folders are `households/`, `incidents/`, `response/`, `evacuation/`, `relief-inventory/`, `relief-distribution/`, `announcements/`, and `disaster-reports/`. Shared markup, session helpers, and the capacity calculation live in `includes/`. Stylesheets, JavaScript, and Leaflet files live in `assets/`. The settings array and the shared PDO connection are in `config/config.php`. Staff and residents open their own pages in `dashboard/`.

The current fictional HTTP acceptance runner is `tests/household_map_persistence.py`. It creates a disposable database and removes it after the checks. Set `TEST_PHP` and `TEST_MYSQL_SOCKET` for your local test installation. Earlier phase documents and historical tests describe their respective branch versions.

## Project documents

- [Phase 1 baseline and five-member review assignments](docs/PHASE_1_PROJECT_BASELINE.md)
- [Phase 2 database installation, relationships, and implementation rules](docs/PHASE_2_DATABASE_SCHEMA.md)
- [Field dictionary](docs/PHASE_2_FIELD_DICTIONARY.md)
- [Fresh-install schema v1.4](barangay_drrm.sql)
- [One-time upgrade from the original v1.0 schema](database/migrations/001_phase_2.sql)
- [Read-only disaster summary query examples](database/report_queries.sql)

Read the Phase 2 installation instructions before importing SQL. Use fictional development data; do not commit real household records, contacts, uploaded images, or local credentials. Use the setup guide below for the running application.

## PHP foundation setup

Edit the settings array in `config/config.php` and open the root login page. For a private database password, set the ATOK_DB_PASSWORD environment variable on the host. See the [setup guide](docs/PHASE_3_PHP_FOUNDATIONS.md).

## Resident enrollment

For an existing database on this branch, apply `database/migrations/assessment_01_resident_accounts.sql` once before updating the PHP pages. Fresh installations use the full schema. Existing accounts retain their passwords.

Active staff can enroll residents from any Atok barangay. Confirm the household’s barangay before enrollment.

Open **Households → View / members → Create resident account**, confirm residency and enter the resident’s email. Give the displayed temporary password privately to the resident. On first login the resident must replace it to access protected pages.

## Household registry

[Phase 4 guide](docs/PHASE_4_HOUSEHOLD_REGISTRY.md): sign in as an admin and select **Households** to manage household details, emergency contacts, vulnerability assessments, and individual members. Member totals are calculated automatically.

## Incident reporting

[Phase 5 setup and workflow](docs/PHASE_5_INCIDENT_REPORTING.md): residents submit incidents and view their own reports; admins enter in-person/phone reports and review incidents. Images require a writable private directory outside the web server document root. Existing databases need `database/migrations/002_phase_5.sql` before using phone reports.

## Emergency response

[Phase 6 guide](docs/PHASE_6_EMERGENCY_RESPONSE.md): admins manage active/inactive response teams, assign teams from incident details, and record actions with history. Dispatch sets an incident to responding; staff close active assignments before resolving it. No additional database migration is required for this phase.

## Evacuation centers

[Phase 7 guide](docs/PHASE_7_EVACUATION.md): admins manage centers, check in registered members or walk-ins, and check out occupants while preserving stays. Apply migration 003 on an existing database before using this module.

## Relief inventory and distribution

[Phase 8 guide](docs/PHASE_8_RELIEF.md): admins manage goods, record stock-in/out, search movement history, and distribute multiple goods to registered households. Stock deductions and distribution history are saved in one transaction. No additional database migration is required for this phase.

## Emergency announcements

[Phase 9 guide](docs/PHASE_9_ANNOUNCEMENTS.md): admins create/edit drafts, publish notices, and withdraw them. Residents see only published notices. Includes title/status/date filtering and fictional classroom contact guidance. No new migration is required.

## Disaster reports and summaries

[Phase 10 guide](docs/PHASE_10_DISASTER_SUMMARIES.md): admin summaries for current affected households and occupancy, date-filtered incident counts, and relief distributed by good/unit. Includes a print layout without personal records and reproducible fictional-data total checks. No new migration required.

## Map search and locations

[Phase 11 guide](docs/PHASE_11_MAP_LOCATIONS.md): incident forms support fictional purok/landmark search, map point selection, manual coordinates, and staff review of saved locations. The current picker checks points against the approximate Atok outline. No new migration required.

## Integration review

[Phase 12 results and repeatable test setup](docs/PHASE_12_INTEGRATION_REVIEW.md): all modules exercised together against isolated MySQL, 189 main HTTP checks passed, upgrade migrations and concurrent stock/capacity protections checked, and issues resolved. Browser visual/print and deployment acceptance checks remain separate.

## Planned revisions

[Shared map, dashboard, and bulk-action revision phases](docs/REVISION_PHASES_MAP_DASHBOARD_BULK_ACTIONS.md) define the agreed demo-area behavior, role-specific map data, location picker, dashboard counts, history-preserving admin actions, checkpoints, and five-member ownership. The revision plan is on `revision-map-dashboard-bulk-actions`; feature implementation follows in later commits.

[Earlier revision rules](docs/REVISION_1_DATA_AND_PRIVACY_RULES.md) record the former map behavior. The current demo map opens on Atok, Benguet and automatically shows verified incidents, open centers, and distribution points with coordinates. No official boundary is enforced.

[Revision 2 schema and map data](docs/REVISION_2_SCHEMA_AND_MAP_DATA.md): apply migration 004 before using the guarded map JSON endpoint. Fresh schema is v1.4; public flags default to private and resident queries use explicit safe fields.

[Revision 3 shared map](docs/REVISION_3_SHARED_MAP.md): both roles have a Map link, persistent legend, layer controls, and saved markers from their authorized data. Admin markers link to protected records; resident popups contain safe summaries only.

[Revision 4 location picker](docs/REVISION_4_LOCATION_PICKER.md): shared manual/map/device-location selection across incidents and staff location forms, plus relief-location management and saved-point views.

[Revision 5 dashboards](docs/REVISION_5_DASHBOARDS.md): current role-specific counts, matching list links, latest resident notices, and the public open-center list.

[Revision 6 record actions](docs/REVISION_6_RECORD_ACTIONS.md): admin management lists with checkboxes, review/confirmation, transactional delete/archive rules, and protected history. Relevant module lists link to the shared management page.

Revision 7 review: [integration log](docs/REVISION_7_INTEGRATION_LOG.md).

BayanReady Phase 1: [map investigation and locked behavior](docs/BAYANREADY_PHASE_1_MAP_REVIEW.md).

BayanReady Phase 2: [location saving and fixture review](docs/BAYANREADY_PHASE_2_LOCATIONS.md).

BayanReady Phase 3: [address suggestions and provider/privacy review](docs/BAYANREADY_PHASE_3_ADDRESS_SUGGESTIONS.md).

BayanReady Phase 4: [visible product name](docs/BAYANREADY_PHASE_4_PRODUCT_NAME.md).

BayanReady Phase 5: [shared visual system and map verification](docs/BAYANREADY_PHASE_5_VISUAL_SYSTEM.md).

- [Phase 6 responsive layouts and checks](docs/BAYANREADY_PHASE_6_RESPONSIVE_LAYOUTS.md)

- [Phase 7 acceptance and issue log](docs/BAYANREADY_PHASE_7_ACCEPTANCE.md)

The tracked settings array is in `config/config.php`. Run `python3 tests/simplification_config.py` to check a fresh setup.

Database simplification: fresh installs use 17 tables; existing databases can retain the unused settings table. See the [database review and optional upgrade steps](docs/simplification/PHASE_6_DATABASE_REVIEW.md) before removing any existing table.

Current household/map revision: [Phase 3 shared address suggestions](docs/simplification/HOUSEHOLD_MAP_PHASE_3.md). Uses the shared picker with Photon, editable suggestions, manual fallback, and save/reload checks.

Current household/map revision: [Phase 4 Atok map view and responsive checks](docs/simplification/HOUSEHOLD_MAP_PHASE_4.md).

Current household/map revision: [Phase 5 accurate dashboard counts](docs/simplification/HOUSEHOLD_MAP_PHASE_5.md).

Current household/map revision: [Phase 6 buttons, confirmations and return links](docs/simplification/HOUSEHOLD_MAP_PHASE_6.md).

## Atok demo map

The map opens near Sayangan in Paoay. **Show all Atok** fits the municipal outline, including areas without saved markers. The picker and PHP save checks read one Polygon with a single exterior ring in `assets/data/atok.geojson`. No database migration is needed.

The approximate outline is the Atok feature extracted from the [Benguet municipal boundaries dataset](https://github.com/faeldon/philippines-json-maps/blob/680e32e62baeccbcd8845d3b1387bc67c1baf78a/latest/geojson/provdists/medres/municities-provdist-1401100000.0.01.json). The opening view uses the [DepEd Sayangan locality reference](https://www.deped.gov.ph/wp-content/uploads/2019/10/Annex-A-Recipient-List.pdf). Confirm the outline and treatment of border locations with the municipality before operational use. A resident can submit a written landmark without coordinates when the demo outline or address suggestion is inaccurate.

## Incident counts and walk-in search

On an existing assessment-branch database apply `database/migrations/assessment_04_incident_barangay.sql` once. Earlier incidents appear under **Awaiting confirmation** until staff identify the incident barangay through **Correct incident location**. Dashboard type counts include all statuses of non-archived reports and use their submission date. The date and barangay selectors apply to the **Reports by incident type** section.

For returning evacuees, open the center and search under **Check in walk-in** by name or phone. Confirm the matching person and address before recording the new stay. If no person matches, expand the new walk-in form. Each check-in adds a separate stay to the same returning person's history.

## Printable reports

For an existing assessment database, apply `database/migrations/assessment_05_center_barangay.sql` once. Identify each center’s barangay on its edit form. Older centers are included in **All barangays** and **Awaiting confirmation**.

Staff can open **Disaster summaries**, apply inclusive dates and a barangay, then choose **Print / Save as PDF**. The first section shows current registry and center figures. The next shows activity during the selected period. Evacuation follows the center’s barangay (including walk-ins), incidents follow the recorded incident barangay, and relief follows the receiving household’s current barangay. Each incident also has a **Printable incident report** link. Staff get an operational copy, and residents can print their own submission. The A4 headings are provisional pending office confirmation.

## Manage Records

Staff select records from the current filtered page. **Archive** hides a record from active selections while keeping its ID and history. Use **Archived → Restore** to return it. Restored centers stay closed until staff reopen them, and restored notices require publication. Archiving a household hides its members from active selection while preserving each member’s individual archive status. Resident accounts are managed separately.

**Permanently delete** removes eligible unused records. A blocked deletion identifies the selected record and examples of its linked records. Operational history is retained, including stays, distributions, stock movements, incident images, assignments, response updates, and publication history. A blocked batch changes nothing.
