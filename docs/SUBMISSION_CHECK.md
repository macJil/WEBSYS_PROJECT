# Classroom submission and demonstration

Use the **atok-workflow-usability** branch. On the presentation Mac, open MAMP and visit http://localhost:8888/WEBSYS_PROJECT/. Use separate browser sessions for staff and resident. Keep the barangay administrator's handoff account separate from the classroom demonstration.

## Accounts and records

The presentation Mac already has the seed data. The account emails below are login identifiers. The example.invalid addresses do not receive mail. Each login has its own random temporary password and must choose a different password before opening a dashboard. Give each person only their own credentials privately.

| Login | Purpose |
| --- | --- |
| staff@demo.example.invalid | Present the staff workflows |
| resident@demo.example.invalid | Show the seeded household member's report and its status |
| barangay.admin@atok.example.invalid | Initial local barangay administrator, reserved for handoff |

On a **fresh classroom installation**, import the schema, configure the database and private upload directory, then run these from the repository root with the PHP CLI. Run only the command you need. Output contains one-time passwords. Use a private terminal before sharing your screen.

- `php database/prepare-demo.php demo` inserts linked fictional records and the two demo logins.
- `php database/prepare-demo.php admin YOUR_LOGIN_EMAIL` creates the separate administrator. Substitute the intended account identifier.
- The commands insert records in a transaction. Running again with the same account does not reset passwords or append another copy. Use a fresh demo database when a clean rehearsal is needed.

## Connected rehearsal

| Screen | Action | What staff or residents should see |
| --- | --- | --- |
| Households | Find DEMO-ATOK-01 | Demo Resident One represents a Paoay household with two members. |
| Incidents and response | Open the seeded resident's pending flood report. Verify and assign Demo Response Alpha. Complete the assignment. | In the resident session, refresh My latest report after each action. It changes to Responding and then Resolved. |
| Evacuation | Open Demo Training Shelter and show the members of DEMO-ATOK-01. Check in only Demo Resident Two. | Initially one of five places is occupied. The existing occupant is labeled. After the arrival two are staying and three places remain. Individual checkout restores one place. Search for Demo Returning Visitor to demonstrate reuse of a past stay. |
| Inventory and distribution | Inspect Demo food pack and Demo drinking water. Distribute two of each to the demo household at Demo Relief Desk. | For each good: 20 received, two previously issued, 18 available before the exercise, 16 afterward. The actual receiver and both distribution lines remain in history. |
| Announcements | Show Demo exercise notice, then save a separate draft and publish it. | The resident sees the published notices. Staff can also see the draft before publication. |
| Dashboard, map and summaries | Filter by Paoay, review the center and incident, then print the summary and the resident's incident. | Active stays agree with the center. The resident map omits households and pending incidents. The resident incident copy omits internal history. |

These are **starting values for the named records**. Municipal totals can include earlier records on the same installation. The longer nine-member script uses a fresh household and an additional stock delivery; rehearse either scenario from its stated starting balances.

## Review results

- Fictional HTTP/database checks passed across all eight modules, including protected direct links, resident ownership, first-login password replacement, image uploads, response status changes, capacity and duplicate prevention, inventory rollback, announcement visibility and retained history. The runner checked 35 rendered page states and 75 local link destinations.
- Login, staff dashboard and resident dashboard fit browser viewports 390 and 1280 pixels wide. Map refresh and 26 location-picker checks passed, including denied location access and failed address lookup.
- Three browser-generated PDFs passed A4 size, page break, totals and staff/resident content checks. The first browser export timed out; the recheck used a fresh, short profile path and the browser print interface.
- The household arrival form previously selected members who already had active stays. It now labels and disables those rows. The regression check verified four existing stays and one eligible arrival. Server-side duplicate protection remains in place.
- Local MAMP had no upload directory configured. A private directory outside its web root was configured. Keep the file path and database credentials local.
- The demonstration script now uses the seed's actual occupancy and relief balances. All three local logins reached the first-password-change screen.

This review supports the **local classroom demonstration**. Office usability sign-off, an authorized online pilot, a physical phone check, the municipal boundary and final printed format remain pending. Use fictional records for classroom access. For an audience-accessible trial use a separate demo database and resident accounts; staff accounts can see municipal records.
