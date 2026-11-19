# Municipal ownership inventory · application baseline f64d389

Paths are repository-relative. Shared schema edits coordinate through Member 2. Member 8 owns dashboard queries and printing, with Member 3 reviewing incident disclosure. Member 9 coordinates integration checks.

|File|Primary|Reviewer|
|---|---:|---:|
| `app/controllers/AnnouncementController.php` | 7 | 8 |
| `app/controllers/Controller.php` | 1 | 2 |
| `app/controllers/DashboardController.php` | 8 | 9 |
| `app/controllers/DisasterSummaryController.php` | 8 | 9 |
| `app/controllers/EvacuationController.php` | 5 | 6 |
| `app/controllers/HistoryController.php` | 9 | 1 |
| `app/controllers/HouseholdController.php` | 2 | 3 |
| `app/controllers/MapController.php` | 9 | 1 |
| `app/controllers/RecordActionsController.php` | 9 | 1 |
| `app/controllers/ReliefController.php` | 6 | 7 |
| `app/controllers/ReliefLocationController.php` | 6 | 7 |
| `app/controllers/ReportController.php` | 3 | 4 |
| `app/controllers/ResponseController.php` | 4 | 5 |
| `app/controllers/UserController.php` | 1 | 2 |
| `app/evacuation-capacity.php` | 5 | 6 |
| `app/helpers.php` | 1 | 2 |
| `app/models/Announcement.php` | 7 | 8 |
| `app/models/Dashboard.php` | 8 | 9 |
| `app/models/DisasterSummary.php` | 8 | 9 |
| `app/models/Evacuation.php` | 5 | 6 |
| `app/models/History.php` | 9 | 1 |
| `app/models/Household.php` | 2 | 3 |
| `app/models/Map.php` | 9 | 1 |
| `app/models/RecordActions.php` | 9 | 1 |
| `app/models/Relief.php` | 6 | 7 |
| `app/models/ReliefLocation.php` | 6 | 7 |
| `app/models/Report.php` | 3 | 4 |
| `app/models/Response.php` | 4 | 5 |
| `app/models/User.php` | 1 | 2 |
| `barangay_drrm.sql` | 2 | 3 |
| `config/config.example.php` | 1 | 2 |
| `config/db.php` | 1 | 2 |
| `database/migrations/001_phase_2.sql` | 2 | 3 |
| `database/migrations/002_phase_5.sql` | 2 | 3 |
| `database/migrations/003_phase_7.sql` | 2 | 3 |
| `database/migrations/004_revision_2.sql` | 2 | 3 |
| `database/migrations/005_simple_publication.sql` | 2 | 3 |
| `database/migrations/006_remove_low_stock.sql` | 2 | 3 |
| `database/migrations/007_automatic_map.sql` | 9 | 1 |
| `database/migrations/008_history_visibility.sql` | 9 | 1 |
| `database/report_queries.sql` | 8 | 9 |
| `index.php` | 1 | 2 |
| `resources/assets/css/bayanready.css` | 9 | 1 |
| `resources/assets/css/disaster-summary.css` | 8 | 9 |
| `resources/assets/js/community-map.js` | 9 | 1 |
| `resources/assets/js/household-checkin.js` | 5 | 6 |
| `resources/assets/js/incident-location.js` | 3 | 4 |
| `resources/assets/js/location-picker.js` | 9 | 1 |
| `resources/assets/js/print-summary.js` | 8 | 9 |
| `resources/assets/js/record-selection.js` | 9 | 1 |
| `resources/views/admin/admin.index.php` | 8 | 9 |
| `resources/views/admin/announcement/announcement.php` | 7 | 8 |
| `resources/views/admin/announcement/announcement.update.php` | 7 | 8 |
| `resources/views/admin/announcement/editor.php` | 7 | 8 |
| `resources/views/admin/evacuation/center.php` | 5 | 6 |
| `resources/views/admin/evacuation/centers.php` | 5 | 6 |
| `resources/views/admin/history.php` | 9 | 1 |
| `resources/views/admin/household.php` | 2 | 3 |
| `resources/views/admin/households.php` | 2 | 3 |
| `resources/views/admin/records-all.php` | 9 | 1 |
| `resources/views/admin/records.php` | 9 | 1 |
| `resources/views/admin/relief/distributions.php` | 6 | 7 |
| `resources/views/admin/relief/inventory.php` | 6 | 7 |
| `resources/views/admin/relief/location.php` | 6 | 7 |
| `resources/views/admin/relief/locations.php` | 6 | 7 |
| `resources/views/admin/relief/movements.php` | 6 | 7 |
| `resources/views/admin/response/incident.php` | 4 | 5 |
| `resources/views/admin/response/teams.php` | 4 | 5 |
| `resources/views/admin/summaries/current.php` | 8 | 9 |
| `resources/views/admin/summaries/disaster.php` | 8 | 9 |
| `resources/views/components/dashboard-cards.php` | 8 | 9 |
| `resources/views/components/errors.php` | 1 | 2 |
| `resources/views/components/footer.php` | 9 | 1 |
| `resources/views/components/header.php` | 9 | 1 |
| `resources/views/components/history-action.php` | 9 | 1 |
| `resources/views/components/location-picker.php` | 9 | 1 |
| `resources/views/components/messages.php` | 1 | 2 |
| `resources/views/components/navigation.php` | 9 | 1 |
| `resources/views/components/saved-point.php` | 9 | 1 |
| `resources/views/incidents/create.php` | 3 | 4 |
| `resources/views/incidents/image.php` | 3 | 4 |
| `resources/views/incidents/list.php` | 3 | 4 |
| `resources/views/incidents/view.php` | 3 | 4 |
| `resources/views/maps/address.php` | 9 | 1 |
| `resources/views/maps/centers.php` | 9 | 1 |
| `resources/views/maps/data.php` | 9 | 1 |
| `resources/views/maps/map.php` | 9 | 1 |
| `resources/views/user/announcement.php` | 7 | 8 |
| `resources/views/user/user.index.php` | 8 | 9 |
| `tests/atok_auto_map.py` | 9 | 1 |
| `tests/bayanready_phase1_http.py` | 9 | 1 |
| `tests/bayanready_phase2_http.py` | 9 | 1 |
| `tests/bayanready_phase3_address.js` | 9 | 1 |
| `tests/bayanready_phase3_persistence.py` | 9 | 1 |
| `tests/bayanready_phase7_counts.php` | 9 | 1 |
| `tests/bayanready_phase7_http.py` | 9 | 1 |
| `tests/household_checkin.php` | 2 | 3 |
| `tests/household_map_address.js` | 2 | 3 |
| `tests/household_map_persistence.py` | 2 | 3 |
| `tests/incident_upload.py` | 3 | 4 |
| `tests/integrated_acceptance.py` | 9 | 1 |
| `tests/phase10_summary.php` | 8 | 9 |
| `tests/phase11_location.php` | 9 | 1 |
| `tests/phase12_http.py` | 9 | 1 |
| `tests/phase12_seed.php` | 9 | 1 |
| `tests/response_history.py` | 4 | 5 |
| `tests/revision2_map.php` | 9 | 1 |
| `tests/revision3_map.js` | 9 | 1 |
| `tests/revision4_picker.js` | 9 | 1 |
| `tests/revision5_dashboard.php` | 8 | 9 |
| `tests/revision6_records.php` | 9 | 1 |
| `tests/revision7_http.py` | 9 | 1 |
| `tests/simple_actions.py` | 9 | 1 |
| `tests/simplification_config.py` | 9 | 1 |
| `app/barangays.php` | 2 | 3 |
| `database/migrations/009_atok_barangay.sql` | 2 | 3 |
| `database/migrations/010_household_representative.sql` | 2 | 3 |
| `database/migrations/011_incident_reporter_email.sql` | 3 | 2 |
| `resources/assets/js/atok-weather.js` | 7 | 8 |
| `resources/views/components/barangay.php` | 2 | 3 |
| `resources/views/components/weather.php` | 7 | 8 |
| `tests/municipal_browser.js` | 9 | 1 |

## Tables in the fresh schema

users, households, household_members, incident_reports, incident_images, responders, incident_assignments, response_updates, announcements, evacuation_centers, evacuation_walkins, evacuation_records, relief_items, relief_locations, relief_distributions, relief_distribution_items, relief_stock_movements

## Shared helpers

e, url, redirect, isPost, input, validDateInput, validPhone, flash, dashboardPath, session_checker, requireRole, locationErrors, hasMapPoint

## Navigation and redirect destinations

Literal PHP page destinations found in views and controllers. IDs and filter values are supplied at runtime. Role-dependent dashboard destinations are defined in app/helpers.php.

- `app/controllers/AnnouncementController.php` → `resources/views/admin/announcement/announcement.php`
- `app/controllers/EvacuationController.php` → `resources/views/admin/evacuation/center.php`, `resources/views/admin/evacuation/centers.php`
- `app/controllers/HouseholdController.php` → `resources/views/admin/household.php`
- `app/controllers/ReliefController.php` → `resources/views/admin/relief/distributions.php`, `resources/views/admin/relief/inventory.php`, `resources/views/admin/relief/movements.php`
- `app/controllers/ReliefLocationController.php` → `resources/views/admin/relief/locations.php`
- `app/controllers/ReportController.php` → `resources/views/incidents/view.php`
- `app/controllers/ResponseController.php` → `resources/views/admin/response/incident.php`, `resources/views/admin/response/teams.php`
- `app/controllers/UserController.php` → `index.php`
- `app/helpers.php` → `index.php`, `resources/views/admin/admin.index.php`, `resources/views/user/user.index.php`
- `app/models/RecordActions.php` → `resources/views/admin/announcement/announcement.update.php`, `resources/views/admin/evacuation/center.php`, `resources/views/admin/household.php`, `resources/views/admin/relief/inventory.php`, `resources/views/admin/relief/location.php`, `resources/views/admin/response/teams.php`, `resources/views/incidents/view.php`
- `index.php` → `index.php`
- `resources/views/admin/admin.index.php` → `resources/views/maps/map.php`
- `resources/views/admin/announcement/announcement.php` → `resources/views/admin/announcement/announcement.php`, `resources/views/admin/announcement/announcement.update.php`, `resources/views/admin/records.php`
- `resources/views/admin/announcement/announcement.update.php` → `resources/views/admin/announcement/announcement.php`
- `resources/views/admin/announcement/editor.php` → `resources/views/admin/announcement/announcement.php`
- `resources/views/admin/evacuation/center.php` → `resources/views/admin/evacuation/center.php`, `resources/views/admin/evacuation/centers.php`, `resources/views/admin/records.php`
- `resources/views/admin/evacuation/centers.php` → `resources/views/admin/evacuation/center.php`, `resources/views/admin/evacuation/centers.php`, `resources/views/admin/records.php`
- `resources/views/admin/history.php` → `resources/views/admin/admin.index.php`
- `resources/views/admin/household.php` → `resources/views/admin/household.php`, `resources/views/admin/households.php`, `resources/views/admin/records.php`
- `resources/views/admin/households.php` → `resources/views/admin/household.php`, `resources/views/admin/households.php`, `resources/views/admin/records.php`
- `resources/views/admin/records-all.php` → `resources/views/admin/records.php`
- `resources/views/admin/records.php` → `resources/views/admin/records.php`
- `resources/views/admin/relief/distributions.php` → `resources/views/admin/relief/distributions.php`, `resources/views/admin/relief/inventory.php`, `resources/views/admin/relief/movements.php`
- `resources/views/admin/relief/inventory.php` → `resources/views/admin/records.php`, `resources/views/admin/relief/distributions.php`, `resources/views/admin/relief/inventory.php`, `resources/views/admin/relief/locations.php`, `resources/views/admin/relief/movements.php`
- `resources/views/admin/relief/location.php` → `resources/views/admin/relief/locations.php`, `resources/views/maps/map.php`
- `resources/views/admin/relief/locations.php` → `resources/views/admin/records.php`, `resources/views/admin/relief/inventory.php`, `resources/views/admin/relief/location.php`, `resources/views/admin/relief/locations.php`
- `resources/views/admin/relief/movements.php` → `resources/views/admin/relief/distributions.php`, `resources/views/admin/relief/inventory.php`, `resources/views/admin/relief/movements.php`
- `resources/views/admin/response/incident.php` → `resources/views/admin/response/teams.php`, `resources/views/incidents/view.php`
- `resources/views/admin/response/teams.php` → `resources/views/admin/records.php`, `resources/views/admin/response/teams.php`
- `resources/views/admin/summaries/current.php` → `resources/views/admin/evacuation/center.php`, `resources/views/admin/household.php`
- `resources/views/admin/summaries/disaster.php` → `resources/views/admin/summaries/disaster.php`
- `resources/views/components/dashboard-cards.php` → `resources/views/admin/summaries/current.php`, `resources/views/incidents/list.php`, `resources/views/maps/centers.php`, `resources/views/user/announcement.php`
- `resources/views/components/history-action.php` → `resources/views/admin/history.php`
- `resources/views/components/location-picker.php` → `resources/views/maps/address.php`
- `resources/views/components/navigation.php` → `index.php`, `resources/views/admin/announcement/announcement.php`, `resources/views/admin/evacuation/centers.php`, `resources/views/admin/households.php`, `resources/views/admin/records.php`, `resources/views/admin/relief/inventory.php`, `resources/views/admin/response/teams.php`, `resources/views/admin/summaries/disaster.php`, `resources/views/incidents/create.php`, `resources/views/incidents/list.php`, `resources/views/maps/map.php`, `resources/views/user/announcement.php`
- `resources/views/incidents/create.php` → `resources/views/incidents/list.php`
- `resources/views/incidents/list.php` → `resources/views/admin/records.php`, `resources/views/incidents/create.php`, `resources/views/incidents/list.php`, `resources/views/incidents/view.php`
- `resources/views/incidents/view.php` → `resources/views/admin/response/incident.php`, `resources/views/incidents/image.php`, `resources/views/incidents/list.php`
- `resources/views/maps/centers.php` → `resources/views/admin/evacuation/center.php`
- `resources/views/maps/map.php` → `resources/views/maps/data.php`, `resources/views/maps/map.php`
- `resources/views/user/announcement.php` → `resources/views/user/announcement.php`
- `resources/views/user/user.index.php` → `resources/views/incidents/create.php`, `resources/views/incidents/list.php`, `resources/views/maps/map.php`, `resources/views/user/announcement.php`
