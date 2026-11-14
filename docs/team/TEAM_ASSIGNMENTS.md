# Five-member ownership

Use Member 1–5 until you replace these labels with names. Assign by complete features, not by language: every member handles their PHP, SQL, forms and tests. These are ownership boundaries in one repository; do not copy the application into five separate websites.

| Member | Primary responsibility and files | Required demonstration | Reviewer |
|---|---|---|---|
| 1 | Login and registry: index.php; User and Household models/controllers; admin/households.php, household.php; config/db.php and app/helpers.php | Resident registration/login/logout, rejected login, admin-only household/member CRUD and calculated size | 2 |
| 2 | Incidents and response: Report and Response models/controllers; incidents/*; admin/response/*; incident-location.js | Valid/private image upload, verification, multiple teams, response updates and resolution | 3 |
| 3 | Evacuation: Evacuation model/controller; admin/evacuation/*; household-checkin.js; app/evacuation-capacity.php | Household/partial arrivals, walk-ins, duplicate/capacity rejection, checkout/history | 4 |
| 4 | Inventory/distribution and removal: Relief, ReliefLocation and RecordActions models/controllers; admin/relief/*; records.php, records-all.php; record-selection.js | Multi-good distribution, rollback on insufficient stock, removal with preserved history | 5 |
| 5 | Announcements, summaries, map/dashboard integration: Announcement, DisasterSummary, Map and Dashboard models/controllers; announcement, summaries, maps and dashboard views; community-map.js, location-picker.js; shared components/CSS | Draft/publish, safe map fields, address fallback, matching totals and responsive navigation | 1 |

Members 2 and 4 own the heavier transaction workflows. Member 5's breadth is balanced by integrating shared query/display components: Members 1–4 must style and test their own forms, not leave their frontend work to Member 5. Member 1 coordinates setup and database migrations; every member reviews any change to tables they use. Member 4 owns removal but must get the affected module owner's review before changing its dependencies. Member 3 owns the shared capacity definition and reviews Member 5's capacity displays.

## Shared-file rules

- Member 1 owns helpers, config examples and database setup; other members request a small reviewed change instead of duplicating a helper.
- Member 5 owns header/navigation/footer, shared map scripts and CSS. Members 1–4 implement module-specific markup and submit shared changes for review.
- Each member owns their tests and manual evidence. Test results and fixes count as work, not only code lines.
- Use short feature branches from the current team branch. Pull before editing; commit only your task. Never commit config.php, real people, image uploads or database dumps.
- A change is ready when its owner explains the request → controller → model → view flow, tests success/failure/role access, and obtains the assigned review.
- Integrate one feature at a time. Run the shared fixture after changes to schema, guards, capacity, stock or removal.

## Fairness and weekly handoff

Each member supplies: a working feature, explanation of its methods/tables, two successful examples, two rejected examples, phone/desktop screenshots and a peer review. Review workload weekly; redistribute documentation/testing if one member has more defects. Do not judge fairness by lines of code.

Report: task, files changed, database impact, tests, unresolved issue, reviewer. Everyone should understand sessions, prepared statements, escaping, redirects and transactions even when another member owns the implementation.
