# Atok MDRRM System — login, dashboard and weather implementation plan

Implementation branch: `atok-login-dashboard-improvements`. Starting revision: `9971483` on `atok-mdrrm-assessment`. This plan covers the classroom version for all eight Atok barangays. Use fictional accounts and records throughout acceptance.

## Phase 1 — Establish the comparison data

Review `index.php`, both pages in `dashboard/`, `dashboard/common.php`, `includes/dashboard-cards.php`, `includes/weather.php`, and `assets/js/atok-weather.js`. Trace each existing card to its destination. Record which figures include archived records, how active stays are counted, and which filters carry through to incident lists.

Prepare two fictional residents, a staff account, open and closed centers, pending and verified reports across two barangays, and a resolved or archived example. Include a first-time resident with no report. Use the existing isolated MySQL runner for repeatable checks. Record the starting counts before adding more records.

**Checkpoint:** The team can predict the dashboard totals and identify which reports each resident owns.

## Phase 2 — Make the login page useful before sign-in

In `index.php`, use the existing Bootstrap grid for a sign-in column and a resident-guide column. Put the form first in the HTML so it appears first on phones. Keep the existing POST action, error handling, and password-change redirect.

Write three short pieces of fixed HTML. Explain that residents can report incidents, track their own reports, and read announcements. Explain that MDRRM staff confirm residency against a household member record and issue a temporary password. Explain what to prepare: a description, contact number, and barangay or landmark. Clarify that a photo and map pin are optional. Include the fictional classroom-pilot label. Use an emergency contact only after the office supplies and approves it.

**Checkpoint:** Anonymous visitors see the guide and login form. Invalid credentials display the normal error. Temporary credentials still lead to password change. Public registration remains closed. At phone width the login button is reachable before the guide.

## Phase 3 — Organize the staff dashboard and add the review queue

In `dashboard/staff.php`, label the top cards **Current municipal overview**. Place **Awaiting review** immediately below. In `dashboard/common.php`, use one query to select up to five non-archived pending reports ordered by submission time ascending, then ID ascending. Return only the ID, incident type, barangay, and submission time. Display **Awaiting confirmation** for unknown barangay. Each row opens its guarded incident review page. Link **View all pending reports** to the existing pending list.

Keep the queue municipal and label it accordingly. Put the existing date-period and barangay selectors directly under **Incident reports for the selected period**. Preserve their values when opening type cards. Add a **Relief distribution** shortcut to `relief-distribution/index.php` beside Relief inventory.

**Checkpoint:** With six pending reports the queue shows the oldest five and the linked list shows six. Verifying one removes it on refresh. The empty queue has a clear message. Each of the eight module shortcuts opens the intended page.

## Phase 4 — Show where evacuees can be accommodated

Reuse the open-center rows already loaded by the staff dashboard. Display **Center**, **People staying**, **Capacity (people)** and **Places remaining**. Link each center name to its guarded detail page. Use the existing occupancy and availability calculations, including centers without map pins. Give a full center a visible zero and an empty list a readable message. Keep this table above incident-type counts and weather.

**Checkpoint:** In a five-place center, checking in two people gives 2 staying and 3 remaining; checking one out gives 1 and 4. The table, center details, and municipal cards agree. A closed or archived center does not appear in the open-center table.

## Phase 5 — Put the resident’s next action and latest report first

On `dashboard/resident.php`, make **Report an incident** the primary action. Follow it with **My latest report** showing type, submission date, status and a **View report** link. Use a single prepared query filtered by the signed-in resident’s ID and the same archive rule as My reports. Sort by submission time descending and ID descending, limiting the result to one. Use the existing private detail page. Keep status totals, published notices, open centers, weather and map access available. Remove duplicate links when the primary action already serves the same purpose.

**Checkpoint:** A new resident sees a helpful empty state. Two residents see different latest reports. Newer reports owned by others or entered separately by staff do not replace the signed-in resident’s report. Changing the detail URL cannot reveal someone else’s report.

## Phase 6 — Clarify the weather source and handle outages

Keep one shared weather panel. Display **Sayangan daily forecast — Open-Meteo** beside the values, including the forecast date and retrieval time in Philippine time. Label the separate link **Official PAGASA advisories — Northern Luzon**. Keep official links visible with JavaScript disabled, during timeouts and when the forecast response is invalid. Reuse the current timeout and browser cache. Do not infer warning status from rain probability or a failed fetch.

**Checkpoint:** Inspect a successful forecast, a cached result and an unavailable response. In each case the provider is clear and dashboard actions remain usable.

### Conditional follow-up — Display official PAGASA forecast data

The team can request access in parallel. Once access is approved, verify Atok’s accepted location identifier, the fields actually returned, coverage dates, issuance timestamp, and permitted display. Fetch the official forecast from PHP with a host-provided token and a shared time-limited cache. Label the geographic coverage and issuance time. Test an unavailable response and an expired forecast. Keep the official warnings link available. Track this follow-up separately from classroom release acceptance. [PAGASA TenDay API documentation](https://tenday.pagasa.dost.gov.ph/static/media/api-doc.c9cf6abbaa781437ed96.pdf)

## Phase 7 — Verify the classroom workflow and update the demo

1. Run the existing fictional MySQL suite, then verify the new queue, center table, and resident latest-report cases. Check anonymous, resident, and staff direct access.
2. Follow one incident across both sessions: resident submits a flood report → staff opens it from Awaiting review → staff verifies and assigns → the queue loses the report → the resident sees Responding → the team completes the assignment → the resident sees Resolved. Check that the matching type count still links to the same filtered records.
3. Repeat the center arrival and checkout example. Open all eight module shortcuts. Confirm the resident dashboard and public login response contain no other household’s details or staff-only notes.
4. Check phone and desktop widths on the MAMP build. Verify keyboard navigation, labels, button reachability, long barangay/center names, table scrolling and the menu. Repeat the weather checks without internet.
5. Record each defect, its fix and its recheck. Update the setup notes and the nine-member live demo script for the features actually delivered. Record any hosted or physical-phone checks separately with their actual results.

**Completion:** The eight workflows still pass, both dashboards lead to the expected records, and the team can demonstrate the login-to-resolution workflow with fictional data. Use direct PHP pages, existing role guards, ordinary forms and the current shared styles. The core phases require no new tables, roles, routing layer or framework.
