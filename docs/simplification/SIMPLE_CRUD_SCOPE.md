# BayanReady minimum working scope

This branch keeps two roles: resident and admin. Public registration creates resident accounts only. PHP sessions guard direct pages. The shared Leaflet map has separate staff and resident data queries. New incidents are private; staff may publish a verified, responding, or resolved incident with a saved point, or set it private again. A public marker uses the saved point and exposes only type, status, and date. Its exact point is visible to residents, so staff must check it before publication.

| Required module | Minimum pages | Required working flow |
| --- | --- | --- |
| Household and resident registry | `admin/households.php`, `admin/household.php` | Admin creates/edits households and members, searches them, records contact, vulnerability, and location. Member count comes from member rows. |
| Disaster incident reporting | `incidents/create.php`, `incidents/list.php`, `incidents/view.php`, `incidents/image.php` | Resident submits and sees own reports; admin enters walk-ins, reviews status, and handles images. Admin controls public/private map visibility separately. |
| Emergency response | `admin/response/teams.php`, `admin/response/incident.php` | Admin manages teams, assigns them, and records actions and status without losing required response history. |
| Evacuation centers | `admin/evacuation/centers.php`, `admin/evacuation/center.php`, `maps/centers.php` | Admin manages centers and check-in/out; active stays determine occupancy and capacity. Residents see approved open centers. |
| Relief inventory | `admin/relief/inventory.php`, `admin/relief/movements.php` | Admin manages goods and stock-in/out; stock cannot become negative. |
| Relief distribution | `admin/relief/distributions.php` | Admin records a registered household, representative, multiple goods, and one atomic stock reduction. |
| Emergency announcements | `admin/announcement/announcement.php`, `admin/announcement/editor.php`, `user/announcement.php` | Admin creates/edits and publishes/withdraws; residents see published notices. |
| Disaster reports | `admin/summaries/disaster.php`, `admin/summaries/current.php` | Admin sees affected households, occupancy, incident counts, and relief totals from live queries. |

Shared pages: root `index.php` for login/registration; `admin/admin.index.php` and `user/user.index.php` for role dashboards; `maps/map.php` and guarded `maps/data.php` for map display. Staff-only map layers remain private. Resident map data never includes household records or reporter names, contacts, descriptions, or images.

## Cleanup in this step

Removed `.editorconfig`, the unused publication-validation helper, and five redirect/include pages after switching their live links to shared pages. Kept history tables and transactions that support the required workflows. Older phase-specific tests and documentation still describe the former public-label design and need revision before they can serve as acceptance checks for this branch.

For existing databases, back up first and apply `database/migrations/005_simple_publication.sql` after migration 004. Existing public incidents will then display their saved report point; review each public incident before using a real community database. Fresh installs use the updated root schema.
