# Nine code walkthroughs for the municipal demonstration

Open the linked entry page, controller and model in adjacent editor tabs. Follow one submitted value to its result. Member numbers match the [ownership and review inventory](../municipal/INVENTORY.md). The live script supplies the spoken words and exact handoffs.

| Member | Time | Feature and value to follow | Reviewer |
|---|---|---|---|
| 1 | 00:00–04:00 | Client evidence, MVC, account email → staff session | 2 |
| 2 | 04:00–07:15 | Registry, member ID → household representative | 3 |
| 3 | 07:15–10:45 | Incident, edited callback number → incident snapshot | 4 |
| 4 | 10:45–14:00 | Response, second completed assignment → resolved incident | 5 |
| 5 | 14:00–17:15 | Evacuation, two selected people → two active stays | 6 |
| 6 | 17:15–20:30 | Relief inventory and distribution, quantities → two balances | 7 |
| 7 | 20:30–23:30 | Announcement, Publish → resident notice; forecast panel | 8 |
| 8 | 23:30–27:00 | Dashboard, selected barangay → count, drill-down and print | 9 |
| 9 | 27:00–30:00 | Shared map, selected barangay → eligible marker; documentation | 1 |

### 1. Sign in

The form posts its action, email and password to the root page. UserController::handlePost reads them and calls UserModel::findByEmail. The model binds the email to a prepared query. The controller verifies the password hash and account state, regenerates the session ID, stores the account ID and role, and redirects to the corresponding home page. The guard on a staff page checks the session again. Show where this role originates. Public registration calls createResident. [Entry form](../../index.php) · [Controller](../../app/controllers/UserController.php) · [Model](../../app/models/User.php) · [Guards](../../app/helpers.php)

### 2. Choose the representative

The household detail form sends select_representative and member_id. The controller obtains the household ID from the open record and validates the submitted member ID. HouseholdModel::selectRepresentative checks the household/member relationship and active status before updating the representative. The household_directory view joins the member name to the same household ID for labels used downstream. Show that replacing the representative leaves prior stays and distributions linked. [Form](../../resources/views/admin/household.php) · [Controller](../../app/controllers/HouseholdController.php) · [Model](../../app/models/Household.php) · [Directory definition](../../database/migrations/010_household_representative.sql)

### 3. Save a resident’s incident

Open the create form as a resident. Initial contact values come from the authenticated account. On a failed submission the form retains the report-specific edits. ReportController::create validates report and optional image data. ReportModel::create persists the incident and the image reference when supplied. The submitted name, email and phone describe this incident; reported_by comes from the signed-in account. The detail lookup restricts a resident by owner ID. Show the distinction between a contact value and an ownership decision. [Form](../../resources/views/incidents/create.php) · [Controller](../../app/controllers/ReportController.php) · [Model](../../app/models/Report.php) · [Result and print](../../resources/views/incidents/view.php)

### 4. Complete the second team assignment

The response form sends update_assignment with assignment ID, status and notes for the open incident. ResponseController::record validates the status and passes the authenticated staff ID. ResponseModel::record updates the assignment, records the action and synchronizes the incident within the transaction. Point to the rule for the remaining assignments. Completing the first of two active teams leaves the incident responding. Completing both resolves it. Earlier actions remain available in history. [Form and result](../../resources/views/admin/response/incident.php) · [Controller](../../app/controllers/ResponseController.php) · [Model](../../app/models/Response.php)

### 5. Check in a partial household

The center form posts checkin_household, household_id and the checked member_ids. EvacuationController::handleStay requires a list of IDs and passes the center and staff IDs. EvacuationModel::checkinHousehold locks the relevant records, checks membership, current stays and capacity, then inserts one stay for each arriving person. The transaction rolls back if any selected person is ineligible. Checkout ends one stay. An active stay has no checkout timestamp. Show the before and after center totals. [Form and result](../../resources/views/admin/evacuation/center.php) · [Controller](../../app/controllers/EvacuationController.php) · [Model](../../app/models/Evacuation.php)

### 6. Record a multi-item distribution

The form submits household_id, received_by and quantities keyed by item ID. ReliefController::distribute validates the selections and quantities. ReliefModel::distribute checks available stock and writes the distribution, item rows, deductions and movement history in one transaction. Staff can read the saved lines on the detail page. Follow one item ID from its input to its outgoing quantity. A shortage in the second item must leave the first balance intact. [Form and detail](../../resources/views/admin/relief/distributions.php) · [Controller](../../app/controllers/ReliefController.php) · [Model](../../app/models/Relief.php)

### 7. Publish an advisory

Show the staff editor’s title, message and publish action. AnnouncementController::save checks the staff role, valid action and text lengths. AnnouncementModel::save stores the publication state. The resident list calls the listing query in resident mode, which selects published, unarchived notices. Reopen the same notice on the resident page. Then show the separate weather component. Its JavaScript requests a fixed Atok forecast, displays a valid result or fallback text and leaves the PAGASA links accessible. [Editor](../../resources/views/admin/announcement/editor.php) · [Controller](../../app/controllers/AnnouncementController.php) · [Model](../../app/models/Announcement.php) · [Resident list](../../resources/views/user/announcement.php) · [Forecast script](../../resources/assets/js/atok-weather.js)

### 8. Reconcile a dashboard card and print a summary

The staff dashboard sends its selected barangay by GET. DashboardController::dashboardData validates the selection and filters the center and stay rows. DashboardModel::stays selects people whose checkout time is still empty. The active evacuee card and its linked list should agree after the checkout in Member 5’s demonstration. For printing, the summary form sends the dates and barangay to DisasterSummaryController. DisasterSummaryModel::summarize applies period limits to incidents and distributions; current affected households and occupancy remain current snapshots. Show the coverage label and generation time in print preview. [Dashboard form](../../resources/views/admin/admin.index.php) · [Controller](../../app/controllers/DashboardController.php) · [Model](../../app/models/Dashboard.php) · [Summary form](../../resources/views/admin/summaries/disaster.php) · [Summary controller](../../app/controllers/DisasterSummaryController.php) · [Summary model](../../app/models/DisasterSummary.php)

### 9. Follow a map filter to one marker

The map page’s GET form carries the selected barangay into the data URL. The browser requests the guarded data page. MapController::mapData validates the selection and calls MapModel::publicMap. Show the incident query’s status and coordinate conditions and the explicit columns it returns. Both roles receive this shared projection. The browser draws the saved coordinates with Leaflet; toggling a layer changes display locally. A pending report remains visible to its owner through the private incident detail page. [Filter](../../resources/views/maps/map.php) · [Data page](../../resources/views/maps/data.php) · [Controller](../../app/controllers/MapController.php) · [Model](../../app/models/Map.php) · [Rendering](../../resources/assets/js/community-map.js)
