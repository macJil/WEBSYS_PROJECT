# Barangay DRRM — WEBSYS1

Official project repository: https://github.com/macJil/WEBSYS_PROJECT

A one-barangay classroom project using simple PHP MVC, PDO/MySQL, sessions, and direct PHP pages. Development is in progress.

## Project documents

- [Phase 1 baseline and five-member review assignments](docs/PHASE_1_PROJECT_BASELINE.md)
- [Phase 2 database installation, relationships, and implementation rules](docs/PHASE_2_DATABASE_SCHEMA.md)
- [Field dictionary](docs/PHASE_2_FIELD_DICTIONARY.md)
- [Fresh-install schema v1.4](barangay_drrm.sql)
- [One-time upgrade from the original v1.0 schema](database/migrations/001_phase_2.sql)
- [Read-only disaster summary query examples](database/report_queries.sql)

Read the Phase 2 installation instructions before importing SQL. Use fictional development data; do not commit real household records, contacts, uploaded images, or local credentials. The schema and application are separate deliverables: permissions, file handling, and transaction workflows still need PHP implementation.

## PHP foundation setup

See [Phase 3 setup and reusable page pattern](docs/PHASE_3_PHP_FOUNDATIONS.md). Copy `config/local.example.php` to the ignored `config/local.php`, configure your local connection/base URL, and open root `index.php`. The account screens use ordinary forms and PHP sessions.

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

[Phase 11 guide](docs/PHASE_11_MAP_LOCATIONS.md): incident forms support fictional purok/landmark search, map point selection, manual coordinates, and staff review of saved locations. No boundary or automatic hazard prediction is applied. No new migration required.

## Integration review

[Phase 12 results and repeatable test setup](docs/PHASE_12_INTEGRATION_REVIEW.md): all modules exercised together against isolated MySQL, 189 main HTTP checks passed, upgrade migrations and concurrent stock/capacity protections checked, and issues resolved. Browser visual/print and deployment acceptance checks remain separate.

## Planned revisions

[Shared map, dashboard, and bulk-action revision phases](docs/REVISION_PHASES_MAP_DASHBOARD_BULK_ACTIONS.md) define the agreed demo-area behavior, role-specific map data, location picker, dashboard counts, history-preserving admin actions, checkpoints, and five-member ownership. The revision plan is on `revision-map-dashboard-bulk-actions`; feature implementation follows in later commits.

[Revision 1 rules and demo setup](docs/REVISION_1_DATA_AND_PRIVACY_RULES.md) lock map field visibility, publication transitions, bulk-action eligibility, and count definitions. Demo map defaults and fictional search points now focus on Irisan; no official boundary is enforced.

[Revision 2 schema and map data](docs/REVISION_2_SCHEMA_AND_MAP_DATA.md): apply migration 004 before using the guarded map JSON endpoint. Fresh schema is v1.4; public flags default to private and resident queries use explicit safe fields.

[Revision 3 shared map](docs/REVISION_3_SHARED_MAP.md): both roles have a Map link, persistent legend, layer controls, and saved markers from their authorized data. Admin markers link to protected records; resident popups contain safe summaries only.

[Revision 4 location picker](docs/REVISION_4_LOCATION_PICKER.md): shared manual/map/device-location selection across incidents and staff location forms, plus relief-location management and saved-point views.

[Revision 5 dashboards](docs/REVISION_5_DASHBOARDS.md): current role-specific counts, matching list links, latest resident notices, public open-center list, and editable per-good minimum-stock thresholds.

[Revision 6 record actions](docs/REVISION_6_RECORD_ACTIONS.md): admin management lists with checkboxes, review/confirmation, transactional delete/archive rules, and protected history. Relevant module lists link to the shared management page.
