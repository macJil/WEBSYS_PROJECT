# BayanReady: 30-minute presentation without slides

A separate backup presentation, prepared 16 November 2026. Use the running website, actual source code, project documents and saved fictional screenshots. This does not replace the Google Slides deck. Q&A starts after minute 30 and is not included.

## Timing contract

Each member has exactly six minutes. The schedule budgets both speaking and visible actions; it is not 30 minutes of uninterrupted reading. Speak naturally at about 110–120 words per minute, pause while the audience looks at the indicated result, and follow the time gates. Rehearse with a stopwatch on the presentation computer. Network delays and individual speaking rates mean a written script cannot guarantee an exact duration by itself.

| Member | Time | Website responsibility | Required code | Document/image |
|---|---|---|---|---|
| 1 | 00:00–06:00 | Login and household registry | Household save through MVC | Course requirement, client-status note, household image |
| 2 | 06:00–12:00 | Incident submission and response | Controller upload/save and model transaction | Incident and response images |
| 3 | 12:00–18:00 | Centers, arrivals, capacity and checkout | Household check-in transaction | Evacuation image and trace chapter |
| 4 | 18:00–24:00 | Inventory, distribution and removal | Distribution transaction | Distribution and movement images |
| 5 | 24:00–30:00 | Announcements, summaries, map and dashboard | Safe map SQL and JSON-to-marker flow | Handbook, references and map image |

## Open these before starting

Use one browser window with a resident session and another browser/profile with an admin session. Keep MAMP running. Open the code editor with each member's files and a Markdown preview of the handbook and this script. Use readable zoom and hide configuration credentials. Have the original instructor announcement ready. If its original file is unavailable, identify the supplied text as a transcription rather than a signed client document.

Prepare fictional records: one five-member household, one empty center with capacity five, one response team, ten packs and twenty bottles, a small valid PNG, and a draft announcement. Use the same household throughout. Actual IDs need not match any example in the guides. Have an existing complete example ready in case a new submission fails.

Images are supporting exhibits and fallback evidence, not live results. The saved images under `presentation/screenshots/` were captured earlier and can differ from the current demo data. Do not announce their numbers as today's totals. Refresh them before class if they no longer represent the page. The presentation can run without these images when the website is available.

Keep a visible six-minute timer for the current member and one total timer. One teammate controls the timer and gives a quiet 60-second warning. The presenter operates their own prepared tabs. Begin the next member exactly at 06:00, 12:00, 18:00 and 24:00.

## Member 1 — 00:00 to 06:00

### 00:00–01:15 — Purpose, client profile and inputs

**Show:** login/home page, then the course requirement and client-status section in the handbook. Do not show a made-up client profile.

**Say:**

“Good day, everyone. We are presenting BayanReady, our Community Disaster Risk Reduction and Emergency Response Management System for WEBSYS1. Instead of using slides, we will demonstrate the website and use selected code and documents to explain how it works.

Our presentation covers the client profile, client inputs, documentation and system overview. We have not yet acquired a stakeholder, so the client profile and interview findings remain pending. Atok, Benguet is our temporary municipal map area, not a confirmed client or approved barangay boundary.

For now, our scope follows the instructor's eight required modules. We will not describe assumptions as client requests. Once a stakeholder is confirmed, we will document their actual process, validated needs and requested adjustments.”

**Action/pause:** Point to the project title and instructor's assigned project. Allow the audience to read the pending client status.

### 01:15–02:15 — Accounts and household registry

**Show:** resident navigation briefly, then admin household list and prepared household.

**Say:**

“The website has two roles. Public registration creates resident accounts. Residents submit reports and view their own private submissions. Authorized staff use admin accounts for operational records.

Here is our fictional household. Staff can record its address, emergency contact, assistance classification and individual members. The member count comes from active member records. A map point does not automatically assess risk.

This household will also be used in our evacuation and relief examples. That connection is part of the system's purpose: related workflows use the same registered household rather than unrelated demonstration records.”

**Action/pause:** Open the member list, count the five members on screen, open Edit and return without unnecessary changes. Use actual displayed values if the prepared count differs.

### 02:15–03:15 — Programming approach and supporting reference

**Show:** editor project folders, then the PHP and MDN links in the reference document.

**Say:**

“Our application combines procedural PHP with object-oriented PHP in a simple MVC organization. The official PHP introduction explains that PHP supports procedural, object-oriented or mixed programming. We use ordinary statements for page setup and functions, and classes for related controller and model operations.

According to MDN, MVC separates data, presentation and control logic. We use that organization so each member can locate the display, request handling and database code for a feature. Our direct PHP pages still contain setup logic, so this is a simple MVC arrangement, not Laravel.”

**Action/pause:** Point to `resources/views`, `app/controllers` and `app/models`. Do not read the documentation page aloud.

### 03:15–05:15 — Complete household save code trace

**Show in order:** `resources/views/admin/households.php`, `HouseholdController::saveHousehold()`, `HouseholdModel::save()`.

**Say:**

“This page creates the controller before displaying the form. Its constructor checks admin access. On a GET request, the save method returns without writing. On submission, the controller collects known fields and checks the requested action, required values and location pair.

Here is the model. It uses a fixed column list, prepares an insert or update and supplies the submitted values separately. A new household gets a new database ID; an edit uses the existing ID.

After saving, the controller stores a success message and redirects to the detail page. The browser then requests that page again and displays the saved data. Validation failures return errors instead of taking the successful redirect.

The code is indented so the PHP loops and the HTML they generate are visible together. We are explaining one request through its actual files, rather than claiming that the folder names alone prove MVC.”

**Action/pause:** Spend approximately 35 seconds on each file. Point to the guard, validation return, prepare/execute and redirect. Use the remaining time to follow the generated detail link and show its member count. Keep other methods collapsed.

### 05:15–06:00 — Document and handoff

**Show:** [Member 1's trace](../docs/team/traces/member-1-households.md); optionally `screenshots/household.png`.

**Say:**

“Our code guide follows this request from the page to the controller, model and returned result. It also explains the error path. This saved image is an earlier fictional example of the household screen, useful if the live page is unavailable; it is not a new save.

Member 2 will now show how a resident report reaches staff and becomes part of the response workflow.”

**Action/pause:** Let the audience see the trace headings. Return to the resident report page by 06:00.

## Member 2 — 06:00 to 12:00

### 06:00–07:15 — Incident form

**Show:** resident incident form.

**Say:**

“The incident module accepts flooding, fire, landslide, road obstruction and other emergencies. A report includes a description, contact information and a written location. The person may also choose a map point and attach a supported image.

We will use a fictional classroom example. The written landmark remains important because a coordinate or suggested address may not fully describe where help is needed. The address suggestion can be edited before saving.

The website records a report for staff review. It does not automatically call emergency responders or guarantee an immediate response.”

**Action/pause:** Fill the prepared type, description and fictional contact, select a fictional map point and confirm the written address. Attach the small PNG. On ordinary LAN HTTP, use map click rather than promising device geolocation.

### 07:15–08:15 — Save and private ownership

**Show:** submitted report detail, then the same report as staff.

**Say:**

“The new report starts as pending. The resident can see their own full submission, including its private image. Staff can open it for review. Another resident does not receive private access simply by knowing the report's ID.

A pending incident does not appear on the shared map. Saving a private report and displaying an eligible map marker are separate steps. We will return to the shared map later.”

**Action/pause:** Submit once and wait for the result. Read the displayed status. If the response is lost, check the list before resubmitting. Open staff review in the prepared admin session.

### 08:15–09:45 — Code explanation

**Show:** `incidents/create.php`, `ReportController::create()` / `upload()`, `ReportModel::create()`.

**Say:**

“The form uses multipart encoding so PHP receives both fields and file bytes. The controller checks the incident type, required details and coordinate pair. It takes the reporter ID from the session rather than trusting a posted account ID.

The upload method checks the upload error, size and actual image content. It generates a filename and places the image in private storage. The database stores its metadata and incident link, not the image bytes.

The model starts a transaction, inserts the incident and uses its new ID for the optional image row. If the database save fails, rollback removes the database changes and the controller removes the newly moved file. SQL rollback cannot delete that file by itself.

The redirected detail query checks ownership again. This protects the private page independently of whether its link appears in the menu.”

**Action/pause:** Point to session ID, MIME/size check, transaction, last inserted ID and cleanup. Do not project private storage settings.

### 09:45–11:15 — Staff response workflow

**Show:** review status, team assignment and action history.

**Say:**

“Staff review and verify the report before assigning a response team. One incident can have several teams. An assignment records its status and the action taken.

Earlier updates remain in history when staff add a new action. The overall incident can be pending, verified, responding, resolved or rejected. Active team assignments must be completed or cancelled before staff move the incident out of responding.

Completing one team's assignment does not automatically resolve the entire incident. The staff member makes that overall decision through the workflow.”

**Action/pause:** Verify the fictional report, show an assignment and its action entry. Explain the next transition without racing through the entire lifecycle. Have `screenshots/incident.png` and `screenshots/response.png` available if needed.

### 11:15–12:00 — Handoff

**Show:** [Member 2's trace](../docs/team/traces/member-2-incidents.md).

**Say:**

“The incident trace documents the input, upload checks, database transaction and ownership query. Image validation checks the file format, not whether the event is true. Human verification is still necessary.

Member 3 will now explain how staff record the people actually arriving at an evacuation center.”

**Action/pause:** Point to the response history, then switch to the prepared center at 12:00.

## Member 3 — 12:00 to 18:00

### 12:00–13:00 — Center and capacity

**Show:** the prepared empty five-person center.

**Say:**

“An evacuation center has a name, written location, status and capacity measured in people. Occupancy comes from active stays: people checked in who have not checked out.

Our fictional center has capacity for five people. It is currently empty. Available capacity therefore means space for five additional people. The application does not store a second occupancy total that staff must update manually.”

**Action/pause:** Point to capacity, occupancy and available capacity. If the demo state differs, explain its actual values instead.

### 13:00–14:15 — Partial household arrival

**Show:** household selector and arriving-member checkboxes.

**Say:**

“Staff choose the registered household. Its active members start selected, and staff uncheck anyone who is not arriving.

Here the household has five members, but only three are arriving. We will select those three. Each selected person gets a separate stay, so the count increases by three rather than automatically using the entire household size.

This lets someone leave while other household members remain. A later change to household membership does not automatically rewrite an earlier stay.”

**Action/pause:** Uncheck two people, show the preview, submit once and read the resulting occupancy and remaining capacity.

### 14:15–15:45 — Code explanation

**Show:** center form, `EvacuationController::handleStay()`, `EvacuationModel::checkinHousehold()`.

**Say:**

“The form sends selected member IDs as an array. The controller chooses the household check-in action and passes the center, household, members and session staff ID to the model.

Inside the transaction, the model locks the center and checks its current state. It reads active occupancy and checks capacity for the whole arriving group. It then verifies that each selected person belongs to the household and has no active stay elsewhere.

Only after all checks pass does it insert one stay per person. Commit saves the group. If one selected member is ineligible or the group will exceed capacity, the batch fails and nobody in that submission is added.

The browser preview is helpful, but the database checks remain authoritative when more than one staff member uses the system.”

**Action/pause:** Point to the array, capacity condition, duplicate-stay query, insert loop and rollback.

### 15:45–17:00 — Checkout and history

**Show:** active occupants, one checkout and history.

**Say:**

“We will check out one of our three arrivals. That ends this person's active stay, leaving two active occupants and available capacity for three people in this example.

Checkout records the end of the stay rather than deleting it. Staff can still review the earlier arrival. Walk-ins have their own basic registration workflow for people not already selected from a registered household.

The dashboard's available capacity adds the remaining capacity of open, non-archived centers. An open center without a map point still contributes, even though it has no map marker.”

**Action/pause:** Check out one person, show the previous stay, then compare center and dashboard totals. Explain other centers' contributions if the dashboard total differs.

### 17:00–18:00 — Evidence and handoff

**Show:** [Member 3's trace](../docs/team/traces/member-3-evacuation.md) and, if useful, `screenshots/evacuation.png`.

**Say:**

“This chapter explains the whole-group validation and individual stay records. An earlier saved screenshot can help show the page layout, but the current numbers come from the live database.

The reason for selecting a household is convenient entry. The reason for saving individual stays is accurate occupancy and checkout. Member 4 will now explain how relief supplies connect to the same household.”

**Action/pause:** Leave the audience time to compare active and historical rows. Switch to inventory at 18:00.

## Member 4 — 18:00 to 24:00

### 18:00–19:00 — Inventory and movements

**Show:** ten food packs, twenty water bottles and movement history.

**Say:**

“The relief inventory records each good in its own unit. Food packs and water bottles remain separate balances. Adding them into one stock quantity would not describe a useful amount.

Staff record stock-in when supplies arrive and stock-out when supplies leave. Each movement explains a quantity change and records the responsible staff account. Here we can inspect the balance and the entries that explain it.”

**Action/pause:** Open the two prepared goods and their movement history. Read actual balances if the class has already changed them.

### 19:00–20:15 — Distribution

**Show:** household distribution form and saved detail.

**Say:**

“A distribution selects a registered household and records the receiving representative. One distribution may include several goods.

We will give three packs and four bottles to our fictional household. Starting from ten packs and twenty bottles, the expected balances are seven packs and sixteen bottles. The saved detail identifies what was distributed, to which household and by which staff account.”

**Action/pause:** Submit the prepared quantities, inspect the detail, then show the updated balances. Keep this screen visible while the audience compares the numbers.

### 20:15–21:45 — Transaction code

**Show:** `ReliefController::distribute()` and `ReliefModel::distribute()`.

**Say:**

“The submitted quantities form a map keyed by item ID. The controller omits blank or zero entries and accepts positive whole quantities.

The model starts one transaction. It locks the registered household and selected goods, then checks every requested quantity before deducting anything. It creates the distribution header, deducts each quantity, writes the item details and records the stock movements.

According to the PHP transaction documentation, related database changes can be committed together or rolled back. We apply that here so an insufficient quantity or another failure does not leave a partial distribution.

The model returns the distribution ID and the controller redirects to a read-only detail page. Opening that page does not repeat the stock deduction.”

**Action/pause:** Point to the validation loop, transaction, header ID, detail/movement writes and commit/catch. Briefly show the PHP reference link if asked for the source.

### 21:45–23:00 — Manage Records

**Show:** record type filter, search, checkboxes and Delete control.

**Say:**

“Manage Records lets staff find and select records. The confirmation asks them to review their intention before deletion, but PHP still checks whether the operation is allowed.

An eligible unused record can be permanently deleted. A linked record may leave active lists while its stored history remains available. A good with remaining stock or a center with occupants can require an operational step before removal.

We therefore do not describe every Delete action as erasing all related data. The displayed guidance explains what blocks a particular record.”

**Action/pause:** Show an unused demonstration record and a linked example. Do not delete the records needed by the next presenter. Use `screenshots/records.png` only as an identified earlier example if necessary.

### 23:00–24:00 — Document and handoff

**Show:** [Member 4's trace](../docs/team/traces/member-4-relief.md), optionally `screenshots/distribution.png`.

**Say:**

“Our guide traces the submitted good quantities through validation, the transaction and the saved history. The same principle explains why some linked catalog records are retained: earlier distributions must still make sense.

Member 5 will now show the announcements, summaries, map and supporting documents.”

**Action/pause:** Let the audience inspect the distribution and movement link, then return to announcements by 24:00.

## Member 5 — 24:00 to 30:00

### 24:00–25:00 — Announcements and summaries

**Show:** one published resident notice, then staff summary.

**Say:**

“Staff can draft, publish and withdraw announcements. Residents see published notices, while drafts remain staff-only. This supports advisories, evacuation instructions and preparedness reminders entered by staff.

The summary pages show recorded affected households, incidents, evacuation occupancy and distributed goods. Historical periods and current occupancy have different meanings. These figures summarize the application's records; they are not a claim that we have complete real barangay data.”

**Action/pause:** Point to publication state and one summary with its date selection. Keep the database discussion at this functional overview.

### 25:00–26:00 — Shared map and dashboard

**Show:** verified example incident, center and relief point, then a dashboard card.

**Say:**

“The shared map displays verified, responding and resolved incidents with valid points, open centers and active distribution locations. It excludes pending and rejected incidents and household markers.

The map shows saved locations, not automatic hazard predictions or an approved boundary. The legend identifies each marker type. Staff and residents can refresh after a record changes.

Dashboard cards link to the corresponding records. A resident's incident counts concern their own submissions. A map count can differ from a list count because a record without coordinates still exists in the normal records.”

**Action/pause:** Refresh, toggle a layer, open one marker, then click a card and return.

### 26:00–27:30 — Map code

**Show:** `maps/data.php`, `MapModel::publicMap()`, fetch/marker block in `community-map.js`.

**Say:**

“The browser requests this direct PHP endpoint. It checks the session and only accepts GET. The controller calls the model, which selects the permitted fields and eligible rows.

Notice that the incident query does not return reporter names, phone numbers, images or private descriptions. It does return the saved point, so we do not describe the coordinates as automatically anonymous. That sharing policy still needs stakeholder confirmation before real use.

The endpoint encodes the result as JSON. JavaScript receives it and Leaflet creates markers. The popup writes record values using textContent. Layer checkboxes change display only; they do not update the database.

Leaflet's documentation supports the marker, popup and layer features. Our own SQL controls which records reach the browser.”

**Action/pause:** Follow endpoint to SELECT to fetch to popup. Do not scroll through the entire JavaScript file.

### 27:30–29:00 — Documentation and supporting evidence

**Show:** handbook, five-member guide, references below, and optional earlier map image.

**Say:**

“Our handbook documents the roles and workflows. Each member's source trace explains the inputs, validation, database effects and result of their assigned feature. The code we have shown is the implementation evidence.

The instructor announcement supports the required scope. The official PHP introduction supports our mixed programming description. MDN explains MVC, the PHP manual explains transactions, and Leaflet documents its map features. These sources support specific statements rather than proving client approval or measured effectiveness.

Client profile and interview documentation remain pending. Future stakeholder changes need a recorded request, reason and affected workflow. Screenshots are useful reference images, but we distinguish them from operations demonstrated live today.

Our MySQL database stores related operational records, and PHP uses PDO to access them. We have kept that explanation brief because the website and its use are the focus.”

**Action/pause:** Point to each document's purpose and one actual source URL. Do not show verification logs or test-result documents.

### 29:00–30:00 — Closing

**Show:** website home page.

**Say:**

“BayanReady brings the eight required modules into one application: household registry, incident reporting, emergency response, evacuation centers, relief inventory, relief distribution, announcements and disaster summaries.

Today we followed a household through related workflows, showed how report review connects to response, and explained the code behind each member's part. The system uses procedural and object-oriented PHP in a simple MVC organization.

Our next step is acquiring a stakeholder, validating the actual process and documenting the changes needed for that client. Thank you for listening.”

**Action/pause:** Keep the home page visible. Finish at 30:00. Only then say, “We are ready for your questions.” If the class will try the website, use the separate [LAN activity guide](../docs/team/PRESENTATION_QA_AND_LAN_DEMO.md); that activity is not included in these 30 minutes.

## Actual supporting reference links

- [PHP Introduction](https://www.php.net/intro)
- [MDN: MVC](https://developer.mozilla.org/en-US/docs/Glossary/MVC)
- [PHP: Transactions and auto-commit](https://www.php.net/manual/en/pdo.transactions.php)
- [Leaflet 1.9.4 API reference](https://leafletjs.com/reference-1.9.4.html)
- Instructor's original announcement: supplied course material; no public URL has been supplied.

## Rehearsal and timing adjustment

Record each member's actual start/end time. At the one-minute warning, stop optional scrolling and use the scripted document/handoff section. If a save or external map request stalls for more than about 15 seconds, state that it has not completed and use an existing fictional record or identified screenshot. Never claim the screenshot proves the just-attempted save.

If a member finishes early, use their remaining action time to let the audience inspect the relevant code and result. Do not add new unplanned features. If a member runs over, shorten repeated navigation or optional image viewing on the next rehearsal. Do not simply speak faster. Rehearse until each section fits its six-minute window. The timing gates, visible demonstrations and pauses are part of the 30-minute presentation.

| Rehearsal | M1 end | M2 end | M3 end | M4 end | M5 end | Adjustment |
|---|---|---|---|---|---|---|
| Target | 06:00 | 12:00 | 18:00 | 24:00 | 30:00 | Q&A excluded |
| First run | | | | | | |
| Final run | | | | | | |
