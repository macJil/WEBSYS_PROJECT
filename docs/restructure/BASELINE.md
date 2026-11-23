# BayanReady plain PHP rewrite baseline

Source review at readable-code commit 6e5bdc5. This is the proposed behavior contract for team review. The scenarios below are repeatable acceptance cases. They have not been run as part of this inventory.

## Version choices

| Area | Readable baseline (6e5bdc5) | Municipal (ca7e8ba) | Prototype (7938fa4) |
|---|---|---|---|
| Structure | MVC, local settings support either array or constants | MVC, constants | Municipal MVC, constants |
| Household identity and municipal coverage | Household code and recorded representative name | Select representative from active members, barangay fields and filters | Same municipal registry |
| Incident intake and dispatch | Reporter contact inputs, verify pending reports before assignment | Account contact prefill, email snapshot, assisted intake, dispatch directly from pending | Same municipal workflow |
| History | Staff can hide entries while preserving stored events | Owned history removed on eligible parent deletion, shared dependencies block deletion | Same deletion rules |
| Location | Shared map, point picker and address suggestions | Adds municipal view and barangay filtering | Written address and optional manual coordinates |
| Weather and printing | Disaster summary printing | Dashboard forecast and warning links, summary and incident printing | On-screen summaries only |

Use readable behavior as the starting point. Team decision pending on which municipal improvements to carry forward and whether the demonstration includes maps, weather and printing. Record decisions here before implementing the affected module. The eight required modules, access rules and linked data workflows below are the proposed minimum.

## Pages, forms and destinations

Paths below are relative to resources/views except for the root index.php. List filters use GET. Successful writes generally redirect with HTTP 303 so refreshing a result does not repeat the submission.

| Area | Current pages | Forms, destinations and connections |
|---|---|---|
| Accounts and dashboards | Root index.php, admin/admin.index.php, user/user.index.php | Register → login page. Login → role dashboard. Logout → login. Dashboard cards open filtered incident lists, announcements, current records or open centers. |
| Registry | admin/households.php, admin/household.php | Search, add or edit household → household detail. Add/edit member → same household. Member IDs feed evacuation and household IDs feed relief. |
| Incidents | incidents/create.php, list.php, view.php, image.php (all under incidents/) | Submit with optional image → incident detail. Filter list, correct location or review status → detail. Image access follows incident ownership. Detail opens team response. |
| Response | admin/response/teams.php, incident.php | Save team → teams list. Assign or update team/action → response detail for that incident. Assignment completion feeds overall incident status. |
| Evacuation | admin/evacuation/centers.php, center.php | Search/add/edit center → centers list. Household arrivals, walk-in or checkout → center detail. Active stays feed occupancy, dashboard and summaries. |
| Inventory | admin/relief/inventory.php, movements.php, locations.php, location.php | Item save → inventory. Stock in/out → movement list. Location save → locations list. Details show saved location. Movements also reflect distributions. |
| Distribution | admin/relief/distributions.php | Choose household, actual receiver and goods → distribution detail on same page with ID. Save goods, stock deductions and movement history together. |
| Announcements | admin/announcement/announcement.php, announcement.update.php, editor.php (shared editor), user/announcement.php | Draft/publish through staff editor → staff list. Staff can withdraw a notice. Resident listing displays published notices. |
| Disaster summaries | admin/summaries/disaster.php, current.php | Date-filtered summary, print summary, dashboard drill-down to current records. Historical incident/distribution totals and current occupancy have different time rules. |
| Map and open centers | maps/map.php, data.php, address.php, centers.php | Signed-in map loads marker data, picker requests address suggestions, open-center directory links from dashboard. Map data excludes private reporter fields. |
| Record actions and history | admin/records.php, records-all.php, history.php | Filter/search, select records, confirm deletion → filtered list. All-records shortcut → records.php?type=all. Hide-history form → originating history page. Preserve linked-record checks. |

Shared components under resources/views/components are header, navigation, footer, errors, messages, dashboard-cards, location-picker, saved-point and history-action. Keep guards on the receiving page even when its link is hidden. Anonymous visitors use the login page. Residents can submit and read their own incidents and images, read published notices, and access shared map/center information. Staff manage all eight modules. Protected requests must enforce the same role and ownership on form submission as on page display.

## Fictional acceptance walkthrough

Use an isolated database, one fictional staff account, and two fictional resident accounts. Run the following cases before and after moving each module.

| Module | Action and expected result |
|---|---|
| 1. Household registry | Create Demo Household A, add three members, edit one name and search again. Detail shows three members and the edited name. Resident access is denied. |
| 2. Incident reporting | Resident A reports fictional flooding with an image. Submission opens the pending report. Staff and A can open the image. Resident B cannot read either record. |
| 3. Emergency response | Verify that report, assign two teams and record actions. Complete one assignment and check that the incident is still responding. Complete the second and check that it resolves with its history intact. |
| 4. Evacuation | Open a two-person center. Select three household members and confirm nobody is admitted. Select two, confirm occupancy two, reject duplicate arrival, then check one out. Occupancy becomes one and both stays remain recorded. |
| 5. Relief inventory | Add Demo Water and record ten bottles in and two out. Stock is eight. Reject a stock-out of nine without adding a movement. |
| 6. Relief distribution | With eight bottles and five packs in stock, issue three bottles and two packs to Household A. Confirm balances five and three, one distribution and the receiver name. Retry with an excessive quantity and confirm both balances are unchanged. |
| 7. Announcements | Save a fictional advisory as draft, publish it, then withdraw it. Check the resident listing after each step. Only the published version appears. |
| 8. Disaster reports | Using only these fixtures, check one resolved flooding report, one active occupant and the distribution quantities for the selected period. Mark Household A affected and confirm the affected-household total follows the registry. Exclude the distribution date and confirm current occupancy still reads one. |

Team sign-off is pending. Record the agreed feature choices, reviewer and date after the team reviews this baseline.
