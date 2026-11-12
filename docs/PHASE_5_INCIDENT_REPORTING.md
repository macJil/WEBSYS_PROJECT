# Phase 5 — Incident reporting and image uploads

## Setup

1. Fresh database: import root `barangay_drrm.sql` (v1.2). Existing Phase 2 database: back up first, then run `database/migrations/002_phase_5.sql` once in your selected database. If starting from v1.0, apply migration 001 before 002. Do not reimport the fresh schema over existing records.
2. In ignored `config/config.php`, set `incident_upload_dir` to an absolute folder outside **both the repository and the web server document root**, for example `/Users/YOUR_USER/WEBSYS-private/incident-images`. Give the PHP process permission to create/write that directory. Uploads fail with a controlled message if storage is not configured; reports without an image remain available.
3. Enable PHP PDO MySQL and Fileinfo. Set `upload_max_filesize = 5M` and `post_max_size = 8M` or larger; restart PHP after changing settings. The app independently enforces a 5 MiB limit. Web server request limits may reject large requests before PHP handles them.
4. Set `map_latitude`, `map_longitude`, and `map_zoom` in local configuration once a barangay is selected. Until then the map shows a Philippines overview; this is not a selected incident location.
5. Sign in. Residents use **Report incident** and **My reports**. Staff use **Incidents**, then **Enter incident report** or open a report to review it.

## Implemented workflow

- Types: flooding (`flood` in the existing schema), fire, landslide, road obstruction, other emergency.
- Required text: reporter/contact name, contact phone, description, written location/purok/landmark.
- Optional coordinate pair: select a map point, use browser location, or enter latitude/longitude. Server checks both coordinates and their valid ranges. Written location remains mandatory when a map or geolocation is unavailable.
- Upload support is included for presentation. As agreed in the baseline, attaching a photo to every emergency is optional. One image per submission keeps the form simple; the schema supports multiple images for later expansion.
- Admin sources: in person (`walk_in`), phone (`phone`), staff observation (`admin`). Residents always receive source `resident`, regardless of submitted fields.
- The signed-in account is stored in `reported_by`. For staff-entered reports it identifies the staff encoder; reporter name/phone identify the actual caller or visitor. No resident account needs to be created for a walk-in/caller.
- New reports always start pending. Only staff can review or change status to pending, verified, responding, resolved, or rejected. Required review notes, reviewer, and server timestamp are recorded. Each review adds a response history entry, including the previous/new status. Changes are transactional and lock the incident row during review.
- Staff can correct/reopen a status with documented notes; no stricter transition policy is imposed yet. Team dispatch/assignment belongs to the next phase.
- Search description and location; combine incident type, submission date (database date), and status filters. Residents' search always includes their account ownership condition.
- Successful submissions use redirect-after-POST. Validation failures preserve text but require selecting the file again.

## Images and privacy

- Actual upload errors, uploaded-file provenance, server-detected MIME, image structure, and actual file length are checked. Allowed formats: JPEG, PNG, WebP; maximum 5 MiB. SVG and arbitrary files are rejected.
- Random server filenames are used. Original client filenames are metadata only and never used for a path.
- Incident and image metadata are saved in one database transaction. A moved file is removed if database saving fails. A process/server crash can still leave an unreferenced file; back up and maintain the private directory together with MySQL.
- File bytes remain outside the served site. `image.php` checks session and incident ownership before streaming, with private/no-store caching and nosniff headers. Knowing an image ID does not grant access.
- Residents can view only their submitted incidents/images. Staff can view all. Staff review notes/history are staff-only. Legacy page URLs now delegate to the guarded implementation. Destructive incident deletion has been removed to preserve history.
- Photos are retained as uploaded; metadata is not stripped. No real emergency photos or personal records are included in Git.

## Map dependencies

The small map scripts use Leaflet 1.9.4 from a CDN and OpenStreetMap tiles, with visible attribution and normal browser caching. No geocoding API, map search service, or API key is needed. Browser geolocation usually needs HTTPS or localhost and permission. Written locations/manual coordinates work without JavaScript or internet. External map providers receive ordinary browser tile requests; do not include reporter names or descriptions in requests.

References: [Leaflet 1.9.4 documentation](https://leafletjs.com/reference-1.9.4.html), [OpenStreetMap tile policy](https://operations.osmfoundation.org/policies/tiles/). Review provider capacity/terms before deployment; there is no offline tile download feature.

## Verification status

Implementation has been reviewed by reading the changed code. Automated tests, PHP execution, browser checks, and MySQL migration/runtime checks were not run in this phase. Before presentation, verify with fictional accounts/data: resident ownership, staff review, each filter, valid/invalid/oversized uploads, upload-storage failure, map fallback, and saved coordinates. Confirm MySQL migration 002 and upload-directory permissions on each member's local setup.
