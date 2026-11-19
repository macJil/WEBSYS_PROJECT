# Run and rehearse the Atok municipal version

## Local setup

1. Check out `atok-municipal-implementation` in your PHP server’s document root. Start Apache/PHP and MySQL in MAMP. Use PHP with PDO MySQL, fileinfo and cURL. Note your actual web and MySQL ports.
2. On a fresh development installation import [barangay_drrm.sql](../../barangay_drrm.sql). It creates and selects barangay_drrm. On an existing database, first make a backup and identify the last applied migration. A database already through 008 needs [009](../../database/migrations/009_atok_barangay.sql), [010](../../database/migrations/010_household_representative.sql), then [011](../../database/migrations/011_incident_reporter_email.sql), once each in the intended database. Earlier versions need their missing predecessors first. The fresh schema already contains the municipal changes.
3. Copy [config/config.example.php](../../config/config.example.php) to config/config.php. Set DB_HOST, DB_PORT, DB_NAME, DB_USER and DB_PASSWORD to your local values. Set BASE_URL to the site’s folder beginning with a slash, or an empty string when serving at the domain root. Create a writable directory outside the web root and set INCIDENT_UPLOAD_DIR to its absolute path. Keep local credentials and images off GitHub.
4. Open the root index.php through the server. For example, a MAMP server on port 8888 serving the WEBSYS_PROJECT folder uses http://localhost:8888/WEBSYS_PROJECT/index.php. Register two fictional residents. In a separate demo database only, the setup owner can register a third fictional account and change that account’s role to admin in MySQL, matching its unique email. Sign in again after the change. Passwords should still be set through registration.
5. Open staff and resident sessions in separate browser profiles. Verify each profile lands on its own home page. Maps, address suggestions and the weather panel use external services. For a classroom LAN demo, use the host’s LAN address on other devices; manual location entry remains available if browser geolocation is unavailable.

## Team self-test and rehearsal fixture

Use fresh fictional records for each pass. Prepare three members of one Topdac household, two response teams, an empty five-person center, an active distribution point, and two goods with five units each. Use a clearly fictional landmark, point and non-personal image. Identify the exact item and center names before starting.

| Who | Do this | Look for this result |
|---|---|---|
| 1 | Register, retry an invalid form, sign in, log out and reopen a staff URL. | Public registration creates a resident. Staff-only pages require an authorized session. |
| 2 | Create the household, add three members, select a representative and then replace that person. Search by member and filter by Topdac. | The label changes, the household ID remains and member count is three. |
| 3 | Submit a resident incident with an edited callback number, small image and demo point. Open it as its owner and then try through the second resident. Try staff-assisted intake. | Owner sees saved contact and image. Other resident cannot open it. Assisted intake starts with blank reporter fields. |
| 4 | Verify it, assign two teams and complete them one at a time. | First completion leaves the other responding. Both completed resolves the incident. Action history is readable. |
| 5 | Select two of the three members at the center. Try a duplicate and an over-capacity group, then check one person out. | Occupancy goes 0 → 2 → 1. Failed batches add nobody. Available places go 5 → 3 → 4. |
| 6 | Distribute two units of one good and six of the other. Retry with two of each. | Failed attempt leaves both at five. Success leaves both at three and records the named receiver and both lines. |
| 7 | Publish a draft, view it as a resident, then confirm withdrawal. On the dashboard check provider attribution and advisory links. | Only published notices appear. A forecast shows its location and retrieval time. With the forecast request blocked and cache cleared, the unavailable message appears. |
| 8 | Select Topdac, follow a card, then open Disaster summaries, apply dates and preview print. Preview an owner incident and a staff incident. | Counts align with lists. Period totals and current occupancy are labeled separately. Staff history appears only in the staff output. |
| 9 | Filter the map, fit locations, toggle layers and inspect the map data response. Reopen the incident’s saved location. Try the same pages at phone width. | Eligible incident, center and distribution points appear. Household and reporter data stay private. Legend, return links and manual entry remain reachable. |

For repeatable automated checks and exact commands see the [integrated acceptance record](PHASE_13_ACCEPTANCE.md). For a timed practice use the [slide script](../../presentation/LIVE_DEMO_PRESENTATION_SCRIPT.md) or [no-slides backup](../../presentation/NO_SLIDES_30_MINUTE_SCRIPT.md). Each reviewer should ask the presenter to identify the input, show the method that saves or queries it, and explain one failure case without reading the script. Record actual start/finish times, missed handoffs and one change for the next run. Q&A begins after the timed close.
