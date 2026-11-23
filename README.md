# BayanReady core prototype

This branch keeps login sessions, admin/resident access, dashboards, and all eight core modules: household registry, incident reports, emergency response, evacuation, relief inventory, relief distribution, announcements, and disaster summaries.

Maps, current-location/address lookup, weather panels, and application printing controls are excluded. Enter addresses manually; coordinates remain optional for compatibility with existing records. The schema and upgrade migrations are retained without deleting stored data.

## Local setup

1. Import `barangay_drrm.sql` into a new local MySQL database. Existing installations should use the numbered migrations appropriate to their current schema instead of reimporting.
2. Copy `config/config.example.php` to ignored `config/config.php`. Set database credentials and `BASE_URL` (empty when served at the web root).
3. Set `INCIDENT_UPLOAD_DIR` to a writable absolute folder outside the web root.
4. Open root `index.php`. Registration creates resident accounts. For fictional testing, promote a test account to admin directly in the local database.

Do not commit real resident records, private configuration, or uploaded images.
