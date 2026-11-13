# Atok demo and automatic map — phases 1 to 3

The current application keeps root login/registration, PHP sessions, admin/resident guards, one shared map, and all eight required modules. The complete page/helper/schema/link inventory is in `ATOK_BASELINE_INVENTORY.md`. No saved incident, household, center, or relief coordinates were moved.

The demo label is **Atok, Benguet (DEMO)**. The opening map point is approximately **16.6272, 120.7672**, zoom **12**, minimum zoom **11**. This is a municipal demo focus, not an approved boundary. The point follows the [Atok coordinate entry](https://www.wikidata.org/wiki/Q30104); the browser basemap rendered Atok around that opening point. A final barangay and any boundary still need stakeholder confirmation. The three `demo-locations.js` examples are explicitly fictional points near that view.

The map now shows the same public-safe layers to both roles:

- Verified, responding, and resolved incidents with valid saved coordinates.
- Open, unarchived evacuation centers with valid saved coordinates.
- Unarchived relief locations of type distribution or both with valid saved coordinates.

Pending/rejected incidents, closed centers, storage-only relief locations, and all household markers stay off the map. The incident response selects only ID, type, point, status, and date. Reporter names, phones, descriptions, images, and household links are absent. Staff links in popups still open protected detail pages. Staff no longer has a separate private map layer.

Removed the publication controller/model/page and all three `is_public` columns from the fresh schema. Existing databases must be backed up and migrated through 005, 006, then `007_automatic_map.sql`. The migration removes old visibility choices; eligible existing incidents with points will appear automatically afterward, so review records before applying it to real data.

Checks: PHP and JavaScript syntax passed; the fresh fictional schema had zero `is_public` columns; 200 core HTTP checks and 26 focused automatic-map checks passed. The browser loaded Atok tiles and showed exactly the three intended layer controls. Older phase-specific tests for manual publication describe the former behavior and are not current acceptance gates.
