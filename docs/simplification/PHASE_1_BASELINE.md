# Simplification Phase 1 — Working baseline

Branch: `bayanready-simplify-code`. Starting commit: `bc8022e` (Phase 7 acceptance). This document is an inventory, not a cleanup or schema change.

## Current application pages

Entry: root `index.php` handles login and resident registration. Direct PHP pages use session/role guards.

| Area | Current pages and shared pieces |
| --- | --- |
| Dashboards | `admin/admin.index.php`, `user/user.index.php`, shared `components/dashboard-cards.php` |
| Household and resident registry | `admin/households.php`, `admin/household.php` |
| Incidents and images | `incidents/create.php`, `incidents/list.php`, `incidents/view.php`, `incidents/image.php` |
| Response | `admin/response/teams.php`, `admin/response/incident.php` |
| Evacuation | `admin/evacuation/centers.php`, `admin/evacuation/center.php` |
| Relief | `admin/relief/inventory.php`, `movements.php`, `distributions.php`, `locations.php`, `location.php` |
| Announcements | `admin/announcement/announcement.php`, `announcement.update.php`, shared `editor.php`, `user/announcement.php` |
| Reports | `admin/summaries/disaster.php`, `current.php` |
| Maps and record actions | `maps/map.php`, `data.php`, `centers.php`, `admin/map-publication.php`, `admin/records.php` |
| Shared interface | `components/header.php`, `navigation.php`, `footer.php`, `messages.php`, `errors.php`, `location-picker.php`, `saved-point.php` |

Compatibility entry pages still present: `admin/reports/report.php`, `report.update.php`, `user/community.map.php`, `report.incident.php`, `reports.php`. These require or redirect to canonical pages and must be checked for incoming links before removal.

## Database baseline: 18 tables

| Required area | Tables | Why retained now |
| --- | --- | --- |
| Accounts and registry | `users`, `households`, `household_members` | Two roles and household member counts |
| Incidents | `incident_reports`, `incident_images` | Private reports and multiple uploaded images |
| Response | `responders`, `incident_assignments`, `response_updates` | Multiple teams and action history |
| Notices | `announcements` | Draft/published/withdrawn notices |
| Evacuation | `evacuation_centers`, `evacuation_walkins`, `evacuation_records` | Capacity, returning walk-ins, active occupancy and history |
| Relief | `relief_items`, `relief_locations`, `relief_distributions`, `relief_distribution_items`, `relief_stock_movements` | Stock, public points, multiple goods per distribution and history |
| Configuration candidate | `system_settings` | Seeded demo label; no application read found in `app/`, `config/`, or `resources/views/` |

Do not remove referenced history tables or foreign-key restrictions to reduce the count. The four files in `database/migrations/` are upgrade scripts, not live tables.

## Passing fictional-data checks

The completed Phase 7 clean, marked, isolated MySQL fixture on port 18960 passed in order: `phase12_http.py` **222**, `revision7_http.py` **29**, `bayanready_phase7_http.py` **47**, and `bayanready_phase7_counts.php` **22** checks: **320 total**. The run and browser observations are in [Phase 7 acceptance](../BAYANREADY_PHASE_7_ACCEPTANCE.md) and [captured check labels](../acceptance/phase7-results.txt). These are the unchanged starting result; this inventory does not claim to rerun that MySQL suite.

At the start of this phase, PHP lint across `app/`, `config/` (excluding ignored `local.php`), and `resources/views/` showed no syntax errors. `node tests/revision3_map.js` and `node tests/revision4_picker.js` passed. Git was clean before creating this document.

## Removal or consolidation candidates — no files removed yet

| Candidate | Reference check | Decision gate |
| --- | --- | --- |
| `system_settings` table | Defined and seeded only in `barangay_drrm.sql`; no application references found. | Confirm teammate databases and external use; remove from fresh schema and write safe upgrade guidance if agreed. Demo label belongs in the planned local config. |
| `config/settings.php`, `config/local.example.php` | `app/helpers.php` loads settings; `config/db.php` consumes them; README and tests reference local settings. | Replace with ignored `config/config.php` plus committed `config/config.example.php`; update all references and setup docs before removing old files. |
| Static connection in `config/db.php` | Every model uses `new DATABASE()->connect()`; several tests inject PDO through `ReflectionProperty`. | Move to a simple `db()` helper and update models/tests together. Preserve one consistent connection per request where transactions need it. |
| `Controller::results_checker()` and `content_checker()` | Definitions found; no call found in application PHP. | Repeat scoped reference search, then remove if unused. |
| Bootstrap duplicates and source maps | Header loads `bootstrap.min.css`; footer loads `bootstrap.bundle.min.js`. Other Bootstrap CSS/JS variants and maps were not found in page references. | Keep loaded pair and license; remove only unreferenced duplicate copies after asset checks. |
| `resources/assets/js/vendor/jquery.min.js`, `app.js`, `user-weather.js`, `weather.js`, `bulk-select.js`; `resources/assets/css/style.css` | No current PHP page reference found. | Confirm no external/team page depends on them, then remove. Do not confuse with active `record-selection.js`. |
| `demo-locations.js`, `incident-map.js` | Older `tests/phase11_location.js` directly loads both; current PHP pages do not. | Retire or update that older test with equivalent current picker coverage before removing these scripts. |
| `disaster-summary.css`, `print-summary.js` | Both loaded by `admin/summaries/disaster.php`. | Keep until print layout/actions are migrated and checked. |
| Compatibility entry pages listed above | Some navigation/dashboard links still target them. | Retarget links and verify direct role guards/old URLs before removing any alias. |
| `incident-location.js`, `location-picker.js`, `community-map.js`, `address-suggestion.js` | Active saved-point, picker, and shared map views load them. | Retain; simplify contents only if behavior and privacy checks pass. |

The next phase should start with formatting and a reference audit. Delete nothing solely because a file looks old or a table increases the count.
