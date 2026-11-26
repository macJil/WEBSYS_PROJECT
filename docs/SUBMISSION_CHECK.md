# Classroom submission and demonstration

Use the **atok-staff-workflow-review** branch for the revised screens. On the presentation Mac, open MAMP and visit http://localhost:8888/WEBSYS_PROJECT/. Use separate browser sessions for staff and resident. Keep the barangay administrator's handoff account separate from the classroom demonstration.

## Accounts and records

Import the full `barangay_drrm.sql` into an empty classroom database. It includes the schema, demonstration records and accounts. Open the root login page in separate staff and resident sessions.

| Classroom login | Password | Dashboard |
| --- | --- | --- |
| barangay.admin@atok.example.invalid | AtokDemoAdmin26! | Administrator |
| staff@demo.example.invalid | AtokDemoStaff26! | Staff |
| resident@demo.example.invalid | AtokDemoResident26! | Resident |

The login page shows classroom logins when `demo_logins` is enabled in config/config.php. Disable that setting outside the classroom. Set `emergency_hotline` after the office confirms its number.

These published credentials are for fictional classroom data only. The email addresses are website login names. Create private credentials before any operational deployment. Newly enrolled residents still change their temporary passwords. The ready-made presentation accounts open their dashboards directly.

## Incident examples

The fresh demo has ten reports across all eight Atok barangays: two each for flooding, fire, landslide, road obstruction and other emergencies. With all barangays and dates selected, each staff type filter returns two reports. On the community map choose **Show all locations** to see all five symbols. The demo resident owns two flood reports; the pending Paoay report is the latest.

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

## Staff workflow branch checks · 26 November 2026

The fictional HTTP suite exercised all eight modules. A 101-report fixture verified the 100-result limit, both sort orders and resident ownership. A distribution saved its chosen relief point with its stock movements. Browser checks opened the login page, ten staff pages and four resident pages at 390 and 1280 pixels without horizontal page overflow. The household entry screen was given a primary heading after the first browser pass. Delete confirmation, both mobile-number formats, the shared map and its location picker were checked separately.

## Review results

- Fictional HTTP/database checks passed across all eight modules, including protected direct links, resident ownership, first-login password replacement, image uploads, response status changes, capacity and duplicate prevention, inventory rollback, announcement visibility and retained history. The runner checked 35 rendered page states and 75 local link destinations.
- Login, staff dashboard and resident dashboard fit browser viewports 390 and 1280 pixels wide. Map refresh and 26 location-picker checks passed, including denied location access and failed address lookup.
- Three browser-generated PDFs passed A4 size, page break, totals and staff/resident content checks. The first browser export timed out; the recheck used a fresh, short profile path and the browser print interface.
- The household arrival form previously selected members who already had active stays. It now labels and disables those rows. The regression check verified four existing stays and one eligible arrival. Server-side duplicate protection remains in place.
- Local MAMP had no upload directory configured. A private directory outside its web root was configured. Keep the file path and database credentials local.
- The demonstration script now uses the seed's actual occupancy and relief balances. The seeded presentation accounts open their authorized dashboards directly.

This review supports the **local classroom demonstration**. Office usability sign-off, an authorized online pilot, a physical phone check, the municipal boundary and final printed format remain pending. Use fictional records for classroom access. For an audience-accessible trial use a separate demo database and resident accounts; staff accounts can see municipal records.
