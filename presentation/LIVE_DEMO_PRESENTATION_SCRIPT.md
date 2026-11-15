# BayanReady: presentation with live website demonstrations

Prepared 15 November 2026. This replaces the earlier screenshot/RRL speaking draft. **Applied to Google Slides on 15 November 2026.** The live deck contains 28 slides with complete speaking notes. Slide numbers below match the live deck; visible text was shortened to fit the shared theme.

## Presentation arrangement

| Presenter | Slides | Responsibility | Time including demonstrations |
|---|---|---|---|
| Member 1 | 1–8 | Introduction, client sections, purpose, PHP/MVC and one code example | 7 minutes |
| Member 2 | 9–13 | Roles, registry, reporting/response and incident code | 7 minutes |
| Member 3 | 14–17 | Centers, arrivals, capacity/history and check-in code | 7 minutes |
| Member 4 | 18–20 | Inventory, distribution/removal and transaction code | 7 minutes |
| Member 5 | 21–27 | Notices, summaries, map/dashboard code, documentation and close | 7 minutes |
| Team | 27; 28 available if needed | Questions | 3 minutes |

Target: 38 minutes, including live website and code demonstrations running through MAMP. Rehearse the combined talk and clicks; reading the speech alone will be shorter. Slide 28 is a reference appendix, not another talk section. Replace member numbers with names.

Use concise slide text with the existing BayanReady theme. Remove all screenshot-only slides and the standalone RRL/research discussion. Keep client slide bodies empty. Put the complete speech and demo cues in speaker notes. References appear only beside the statements they support and on one final reference slide.

The speaking pattern is **our decision and reason, a brief supporting source when needed, then our actual example**. A source explains a principle. The project's code and live website demonstrate its implementation. Avoid claiming that an external document proves this prototype's effectiveness.

## Member 1

### Slide 1 — BayanReady

**On screen**

BayanReady
Community Disaster Risk Reduction and Emergency Response Management System
WEBSYS1 • Team names

**Speech**

“Good day, everyone. We are presenting BayanReady, our website for organizing community disaster and emergency records. We will discuss the client sections, explain how we organized the application, and demonstrate the required modules in the website itself.

We will switch between this presentation and the website so you can see how the features work. We will also show short code examples from each member and the documentation that supports our explanation.”

**Action:** Stay on slides. Introduce the five presenters.

### Slide 2 — Presentation overview

**On screen**

- Client profile and client inputs
- System overview and live demonstrations
- Documentation
- Questions

**Speech**

“Our presentation follows the four required areas: client profile, client inputs, documentation and system overview. We will first state the status of our client information. Then we will explain the system's purpose and structure before demonstrating its features. At the end, we will show the documents that describe the requirements, workflows and code responsibilities.

The examples shown today use fictional household and incident records.”

### Slide 3 — Client profile

**On screen:** Title only. Leave the body empty.

**Speech**

“We have reserved this section for the client profile. We have not yet acquired a stakeholder, so we are leaving the details blank. We will add the organization, its responsibilities and its existing process after confirming these with the client. Atok, Benguet is currently our demonstration map area and is not a confirmed client.”

**Action:** Move on without presenting invented organization details.

### Slide 4 — Client inputs

**On screen:** Title only. Leave the body empty.

**Speech**

“This section is also pending. We cannot yet claim that a stakeholder requested or approved particular features. Once an initial interview takes place, we will document their actual problems and validated requests here. Our current development follows the instructor's required modules. We will distinguish those requirements from the future client's suggestions.”

### Slide 5 — Purpose and scope

**On screen**

- One application for disaster-related records
- Eight required modules
- Intended scope: one barangay
- Atok, Benguet: temporary demo map area

*Source: WEBSYS1 instructor's final requirement, supplied course announcement.*

**Speech**

“BayanReady brings related records into one application. For example, a household record can support an evacuation arrival and a relief distribution. An incident report can connect to team assignments and response actions.

According to our instructor's announcement, our assigned project is the Community Disaster Risk Reduction and Emergency Response Management System. Its required modules provide the current development scope. The problem we aim to address is organizing and retrieving these related records. Whether a particular barangay experiences that problem in the way we expect still needs stakeholder validation.

Our intended client scope remains one barangay. Atok is a municipality used for the current demonstration view, so the final barangay and its approved locations still need confirmation.”

### Slide 6 — PHP approach

**On screen**

Procedural PHP and object-oriented PHP
Organized in a simple MVC structure

- Page setup and shared functions
- Model and controller classes
- PDO for MySQL access

*Supporting reference: PHP Manual, Introduction.*

**Speech**

“Our website uses both procedural PHP and object-oriented PHP, organized in a simple MVC structure. We use ordinary PHP statements and functions for page setup and shared operations. We use classes to group related model and controller methods.

According to the official PHP introduction, PHP supports procedural programming, object-oriented programming, or a mixture of both. This supports how we describe our application. For example, a page creates a controller object, calls its methods and then displays HTML. We will show that actual code shortly.

We chose this approach to stay close to the PHP concepts we are learning while giving related responsibilities a clear place in the project.”

**Notes reference:** The PHP Group. Introduction. https://www.php.net/intro (accessed 15 November 2026).

### Slide 7 — Why we use MVC

**On screen**

Model: database operations
View: forms and displayed results
Controller: request handling and validation

Example: saving a household

*Supporting reference: MDN Web Docs, MVC.*

**Speech**

“We use MVC to organize the work so that a form's appearance, its request handling and its database operation have identifiable responsibilities. This also helps our five members locate the code for their assigned features.

According to MDN Web Docs, MVC separates data, presentation and control logic. We apply that separation in a simple PHP arrangement. The Model performs database operations, the View contains the form and displayed results, and the Controller handles the submitted request and validation.

For example, saving a household begins with a form. The controller checks the request and passes the accepted values to the model. The model saves them, and the application returns to the household page. We use direct PHP pages rather than a Laravel routing framework.”

**Notes reference:** MDN Web Docs. MVC. https://developer.mozilla.org/en-US/docs/Glossary/MVC (accessed 15 November 2026). The explanation of team ownership is our design rationale, not a measured benefit established by this reference.

### Slide 8 — One code example: saving a household

**On screen**

Household form
HouseholdController::saveHousehold()
HouseholdModel::save()

Live code overview

**Speech**

“Let us connect those terms to the actual project. At the top of this page, ordinary PHP statements load the controller, create an object and call its methods. This is an example of procedural page setup working with an object.

The same file contains the form that staff see. Next, this controller class checks the role and handles the household request. Its saveHousehold method checks the form values before asking the model to save them.

Finally, the HouseholdModel class prepares the insert or update operation and passes the field values to PDO. After a successful save, the application redirects to the household record. We are showing the request flow rather than explaining every line.

Member 2 will now demonstrate how staff and residents use the application.”

**Live code, about 90 seconds:** Open these three files in advance at readable zoom. Show only the indicated blocks, then return to slide 9.

1. `resources/views/admin/households.php`: opening page setup and a few form fields.
2. `app/controllers/HouseholdController.php`: constructor and `saveHousehold()`.
3. `app/models/Household.php`: `save()`, especially `prepare()` and `execute()`.

**Evidence:** Actual project files. Do not call the pages a strict framework implementation: they contain page setup as well as view markup.

## Member 2

### Slide 9 — Users and access

**On screen**

Resident: own reports and community information
Admin: operational records and staff workflows
Public registration creates resident accounts

**Speech**

“The application has two account roles. Residents submit incident reports, view their own reports and read community information. Admin accounts represent authorized staff who manage households, incidents, evacuation records, supplies and notices.

Public registration creates a resident account. Staff access must be provided separately. Login sessions identify the signed-in account, and protected pages check the user's role. A resident's report details also require ownership checks.

We will demonstrate the two roles in separate prepared sessions so we do not spend presentation time repeatedly logging in and out.”

**Website cue, about 20 seconds:** Show the resident dashboard and the admin navigation. Keep passwords and configuration files off screen.

### Slide 10 — Eight required modules

**On screen**

1. Household and Resident Registry
2. Disaster Incident Reporting
3. Emergency Response Management
4. Evacuation Center Management
5. Relief Goods Inventory
6. Relief Distribution Management
7. Emergency Announcement Management
8. Disaster Reports

*Source: instructor's project requirements.*

**Speech**

“These are the eight required modules from the course announcement. The map, dashboard and account system support them. We will show related modules together so the audience can follow a complete workflow: household records, incident follow-up, evacuation and relief, then announcements and summaries.

Our examples show the intended use of the application. They are not claims of adoption or approval by a barangay.”

### Slide 11 — Household and resident registry

**On screen**

- Household address and emergency contact
- Registered members and calculated member count
- Recorded assistance needs and hazard notes

Live demo: household details and members

**Speech**

“The registry gives staff a starting record for households and their members. This fictional household has an address, an emergency contact and a list of registered people. The member count comes from active member records rather than a second manually entered household-size value.

Staff can create, search, view and edit these records. The assistance category and hazard notes are entered by staff. Selecting a map point does not automatically classify a household's risk.

Notice that this information belongs to the staff workflow. The shared community map does not display household markers or personal member information.”

**Website cue, about 45 seconds:** Open one fictional household, show its member list and Edit form, then return. Use the same household later for evacuation and distribution.

### Slide 12 — Incident reporting

**On screen**

- Incident type, description and written location
- Optional selected map point and supported image
- Pending report for staff review

Live demo: resident submission and staff view

**Speech**

“A resident can report flooding, fire, landslide, road obstruction or another emergency. The written location helps explain where it happened. The person may also select a map point and attach a supported image.

I will enter a clearly fictional report, choose a location and review the address suggestion before saving. A suggested address remains editable because it may not describe the exact landmark the person intends.

The new report begins as pending. The resident can open their own full submission, and staff can review it. Staff can also enter a report received by phone or in person. The shared map does not automatically display a pending incident. We will explain the map's visibility rule later.”

**Website cue, about 70 seconds:** Submit a prepared fictional report with a small supported image. Show its saved private detail, then open the same report as staff. Do not describe a save as successful until the page confirms it. If connection or map lookup fails, keep the written location and continue manually.

**Required code demonstration: Member 2 (60–90 seconds)**

“This page accepts an ordinary multipart form so PHP receives both the fields and the uploaded file. The controller checks the incident type, written details and coordinate pair. It assigns the reporter ID from the session. The upload method checks the file's real content and gives it a generated private filename. In the model, one transaction saves the incident and its image reference using the new incident ID. If saving fails, the database rolls back and the controller removes the newly stored file. On success, the redirect opens the detail page. That query also checks the resident's ownership, so knowing another report ID does not grant access to its private information.”

**Code cue:** Follow [Member 2's full source trace](../docs/team/traces/member-2-incidents.md). Open the page entry, controller and model blocks in advance. For Member 5, also show the JSON fetch and marker rendering. Show only the main steps during the talk; the complete trace is for study and questions. Return to slide 13.

### Slide 13 — Emergency response

**On screen**

Review and verify
Assign response teams
Record actions and status
Keep previous updates

Live demo: report review and team assignment

**Speech**

“Staff review the report before verifying it. An incident can have more than one assigned team. The response workflow records who is assigned, the assignment status and the action taken.

We keep earlier actions so a later update does not replace the record of what happened before. The overall incident can move through pending, verified, responding and resolved. Staff can reject a report when appropriate.

On this page, we will verify our fictional incident and show a team assignment. Operational decisions still belong to staff. The website records those decisions and does not guarantee that submitting a form produces an immediate emergency response.

Member 3 will now demonstrate how the evacuation module records the people staying in a center.”

**Website cue, about 50 seconds:** Verify the new incident. Show assignment controls and one existing action history. If demonstrating dispatch, explain its effect on incident status and leave a clear demo state for the later map.

## Member 3

### Slide 14 — Evacuation centers

**On screen**

Center name and written location
Capacity measured in people
Current occupancy from active stays
Open or closed status

Live demo: center details

**Speech**

“The evacuation module records the centers available to staff and the people currently staying in them. Capacity means the maximum number of people the center can accommodate. Occupancy is the number of active stays, meaning people who have checked in and have not checked out.

A center can have a saved map point, but the written location remains useful. A center without coordinates still appears in its normal records and contributes to the appropriate totals.

For this example, we will use a fictional center with capacity for five people and no current occupants. We will show how the numbers change as people arrive and leave.”

**Website cue, about 40 seconds:** Open the prepared five-person center. Point to capacity, current occupants and location. If using another center, state its actual numbers instead of reading the example unchanged.

### Slide 15 — Household-based arrivals

**On screen**

Choose a household
Confirm the arriving members
Uncheck absent members
Save one stay per arriving person

Live demo: three members arrive

**Speech**

“Staff select a household rather than entering each registered person separately. The form lists the household's active members and selects them by default. Staff then uncheck anyone who is not arriving.

In our example, the household has five registered members, but only three are arriving. We will leave three selected. The application saves an individual stay for each of those people, so occupancy increases by three, not by the entire household size.

Individual stays allow someone to check out while the other members remain. They also preserve the arrival record even if the household's membership changes later.”

**Website cue, about 75 seconds:** Choose the five-member fictional household, uncheck two, show the arriving count and submit. Read the resulting occupancy from the page.

**Required code demonstration: Member 3 (60–90 seconds)**

“The household form sends an array of selected member IDs. It does not simply submit the whole household size. The controller chooses the household check-in branch and passes the center, household, selected people and signed-in staff ID to the model. Inside the transaction, the model locks the center and reads current occupancy. It checks capacity for the entire group, then checks each member's household and whether they already have an active stay. Only after all checks pass does it insert one stay per selected person. Commit saves the group together, while an error rolls the group back. After redirecting, the center query counts active stays again. Checkout later ends a stay rather than deleting it.”

**Code cue:** Follow [Member 3's full source trace](../docs/team/traces/member-3-evacuation.md). Open the page entry, controller and model blocks in advance. For Member 5, also show the JSON fetch and marker rendering. Show only the main steps during the talk; the complete trace is for study and questions. Return to slide 16.

### Slide 16 — Available capacity in people

**On screen**

Example: capacity 5 − active occupants 3 = available capacity 2

Dashboard total: remaining capacity across open, non-archived centers

**Speech**

“Available capacity means how many additional people can be accommodated. In this example, a capacity of five minus three active occupants leaves room for two people.

The dashboard adds the remaining capacity of open, non-archived centers. It does not add unused places from closed centers. It also includes usable centers that have no map point, because missing coordinates do not remove their capacity.

These are counts of people, not counts of households or beds unless a center's person capacity has been defined that way by staff. The center page, list and dashboard should use the same definition.”

**Website cue, about 45 seconds:** Compare the example center's detail with its list entry. The dashboard total may include other centers, so explain that difference rather than expecting it to equal this center alone.

### Slide 17 — Arrival safeguards and history

**On screen**

- No duplicate active stay
- No arrival beyond capacity
- Walk-in registration
- Individual checkout with history retained

Live demo: checkout and previous stay

**Speech**

“The application checks arrivals before saving them. It blocks a selected person who already has an active stay and rejects a household submission when the selected arrivals exceed capacity. A rejected batch should add nobody.

Walk-ins have a separate registration workflow so staff can record people who are not already registered household members. Checkout ends a person's active stay while keeping the earlier stay available in history.

We will check out one of our three example arrivals. That leaves two active occupants and room for three more people in this center. We can still open the previous stay, which explains why the person was counted earlier.

Member 4 will now show how relief goods and distributions connect to household records.”

**Website cue, about 60 seconds:** Check out one fictional person and show the history. Point to the walk-in form without creating an unnecessary record. Use observed numbers if the prepared example differs.

## Member 4

### Slide 18 — Relief goods inventory

**On screen**

Goods and their units
Stock-in and stock-out
Movement history

Live demo: item balance and movements

**Speech**

“The inventory module records supplies such as food packs, drinking water, medicines and blankets. Each good has its own unit. Ten packs and twenty bottles are separate balances, so we do not combine them into one misleading stock quantity.

Staff record stock-in when supplies arrive and stock-out when supplies leave. The movement history explains the changes, including the quantity, date and responsible staff account.

We will open our fictional food-pack item and show its current balance and previous movements. This history is useful when staff need to understand why the available stock changed.”

**Website cue, about 70 seconds:** Show an item prepared with ten packs and its movement list. Avoid editing a balance directly as a substitute for recording a movement.

### Slide 19 — Relief distribution

**On screen**

Registered household and receiving representative
Several goods in one distribution
Distribution and stock changes save together

Example: 10 packs − 3 packs = 7 packs

*Supporting reference: PHP Manual, Transactions and auto-commit.*

**Speech**

“A distribution identifies the registered household, the receiving representative and the goods provided. One distribution can contain several goods.

We use a database transaction because the distribution and its stock deductions belong to the same operation. According to the PHP manual, a transaction groups changes so they can be committed together or rolled back. In our application, if a required item cannot be supplied, the transaction should not leave behind a partial distribution.

For example, giving three of ten food packs leaves seven. If we also give four of twenty water bottles, sixteen bottles remain. We will save that fictional distribution and show its detail and the resulting item balances.”

**Website cue, about 120 seconds:** Use the same household as the registry example, enter a fictional representative, select two goods and submit. Show the saved distribution. Use actual balances if the example was not prepared exactly.

**Notes reference:** The PHP Group. Transactions and auto-commit. https://www.php.net/manual/en/pdo.transactions.php (accessed 15 November 2026). The application example comes from its own relief workflow.

**Required code demonstration: Member 4 (60–90 seconds)**

“The quantities arrive as a map keyed by each good's ID. The controller skips blank or zero entries and accepts positive whole quantities. The model then starts one transaction. It locks the registered household and selected goods, and checks every requested quantity before deducting anything. Next it creates one distribution record, one detail for each good and a stock movement for each deduction. Commit saves all of those changes together. If any step throws an error, rollback restores the previous state. The returned distribution ID takes us to a read-only detail page, so viewing that page does not distribute the goods again.”

**Code cue:** Follow [Member 4's full source trace](../docs/team/traces/member-4-relief.md). Open the page entry, controller and model blocks in advance. For Member 5, also show the JSON fetch and marker rendering. Show only the main steps during the talk; the complete trace is for study and questions. Return to slide 20.

### Slide 20 — Record removal and retained history

**On screen**

Select records and confirm Delete
Unused records: permanent removal
Linked records: retained history where permitted
Active use or remaining stock: guidance before removal

**Speech**

“Manage Records provides a filter, search and checkboxes. Staff select the intended records and confirm the Delete action.

The result depends on the record's use. An unused eligible record can be permanently removed. A linked record may be removed from active pages while its historical relationships remain stored. This is why a past distribution can still identify the good that was given.

Some records cannot be removed immediately. For example, goods with remaining stock or a center with active occupants need the appropriate operational step first. The page explains what must be handled. We do not describe Delete as erasing every linked event, because keeping response, stay and stock history is part of the system's purpose.”

**Website cue, about 55 seconds:** Show the filter and selection controls. Avoid deleting the records needed later in this demonstration. If demonstrating deletion, use a separate unused fictional item.

**Handoff:** “Member 5 will now demonstrate the information pages and explain the documents that support the project.”

## Member 5

### Slide 21 — Emergency announcements

**On screen**

Draft, publish and withdraw
Advisories and evacuation instructions
Published notices for residents

Live demo: staff and resident views

**Speech**

“Staff use announcements for disaster advisories, evacuation instructions, emergency notices and preparedness reminders. They can prepare a draft before publishing it. Residents see published notices, while drafts remain available to staff.

We will show a fictional published notice and then its resident view. The information should remain clear and current. A staff member can withdraw a notice when it should no longer appear to residents.

This feature presents information entered by staff. It does not create an official advisory or independently verify its content.”

**Website cue, about 35 seconds:** Show the staff list and corresponding resident notice. If demonstrating publication, use a clearly fictional notice and explain its audience.

### Slide 22 — Disaster reports and summaries

**On screen**

Affected households
Incident counts
Evacuation occupancy
Relief distribution over a selected period

Live demo: summary and print layout

**Speech**

“The summary pages bring together figures from the operational records. They include affected households, incidents, evacuation occupancy and distributed relief goods. A selected period helps staff review historical information where the page supports it.

For example, staff can see how many food packs were distributed during a period without opening every distribution individually. Each good keeps its own unit. The summary uses the records already entered into the system.

We will show the date selection and printable layout briefly. These figures describe what has been recorded in the application. They should not be presented as a complete real-world disaster count without confirmed client data.”

**Website cue, about 40 seconds:** Open a populated fictional summary. Distinguish current occupancy from historical activity in a selected period. Do not show test-result documents.

### Slide 23 — Shared map and location entry

**On screen**

Eligible incidents, open centers and distribution points
Legend and layer controls
Editable address suggestions
Manual entry remains available

*Supporting reference: Leaflet 1.9.4 API reference.*

**Speech**

“We use Leaflet to display saved locations in an interactive map. Its documentation describes markers, popups and layer controls, which are the features we use to distinguish incidents, centers and relief points. Our own PHP queries decide which records the map receives.

Verified, responding and resolved incidents with coordinates appear automatically. Pending and rejected incidents stay off the shared map. Household markers, reporter names, phone numbers, private descriptions and incident images are not included in the community incident response.

On a form, clicking the map or choosing current location selects a point and requests an editable address suggestion. The person must confirm the wording. Manual entry remains possible if permission, map tiles or lookup fail.

The selected incident coordinates can be visible on the shared map once eligible, so that location-sharing policy still needs confirmation with the eventual stakeholder. The map does not calculate hazard risk or establish an official boundary.”

**Website cue, about 60 seconds:** Show the newly verified incident, a center, a relief point and the legend. Toggle one layer. Open a form, select a point and show the address field without saving an unintended change. Do not promise browser geolocation accuracy.

**Notes reference:** Leaflet. Leaflet 1.9.4 API reference. https://leafletjs.com/reference-1.9.4.html (accessed 15 November 2026). Visibility rules and address suggestions are application behavior, not claims established by the Leaflet reference.

**Required code demonstration: Member 5 (60–90 seconds)**

“The map page gives JavaScript the address of this PHP data endpoint. The endpoint checks the session and only accepts GET. The controller calls the map model, whose query names the fields that may be returned. Notice that the incident query excludes reporter names, phone numbers, descriptions and images, and filters for eligible statuses and valid coordinates. The result is encoded as JSON. In the browser, this fetch receives the rows and Leaflet creates the markers. Popup values use textContent, so record text is displayed as text. The layer checkboxes only change which marker groups are shown. They do not change the database. New or updated records appear after the map is refreshed.”

**Code cue:** Follow [Member 5's full source trace](../docs/team/traces/member-5-map.md). Open the page entry, controller and model blocks in advance. For Member 5, also show the JSON fetch and marker rendering. Show only the main steps during the talk; the complete trace is for study and questions. Return to slide 24.

### Slide 24 — Role-specific dashboards

**On screen**

Admin: operational overview
Resident: own reports and community information
Available capacity measured in people

Live demo: counts linked to records

**Speech**

“The dashboard provides a starting overview after login. Staff see operational counts such as incident statuses and evacuation information. Residents see counts for their own submissions and the community information available to them.

A number should have a clear meaning and a matching list. For example, an incident status card should open the corresponding filtered reports. Available capacity means the remaining person capacity across open, non-archived centers, as Member 3 explained.

We will open one card and its matching list. The dashboard helps users reach the underlying records instead of presenting a number without a way to understand it.”

**Website cue, about 40 seconds:** Click a status card, compare its filter, then return. Show the resident report count in the resident session. Use current displayed numbers, not memorized totals.

### Slide 25 — Project documentation

**On screen**

Course requirements and module coverage
User guide and workflow descriptions
Code guide and member responsibilities
Client interview and change records: pending

**Speech**

“Our documentation provides the basis for explaining the project. The instructor's announcement supports the required scope. The system handbook explains the user roles and workflows. The code guide identifies the files and methods assigned to each member.

We will show the requirements and one workflow description so the audience can compare the documented purpose with the feature we demonstrated. Our technical references support specific explanations, such as the meaning of MVC. They do not replace the project's own documents or a future client interview.

The client profile and interview findings remain pending. When a stakeholder requests changes, we will document the request, the reason, the affected feature and the resulting adjustment. We will keep those requests separate from assumptions made during classroom development.”

**Document cue, about 60 seconds:** Open the course requirements, `docs/team/README.md`, then the updated member code guide. Show headings and one relevant workflow only. Do not open verification or test-result sections during the presentation.

### Slide 26 — Database overview

**On screen**

MySQL stores related operational records
PDO connects PHP to the database
History connects past actions to their records

**Speech**

“Our database is MySQL, and PHP uses PDO to access it. It stores related household, incident, evacuation and relief information. We will keep this at an overview because the website's use is the focus of our presentation.

For example, a household can have several members, and a distribution can include several goods. Previous stays and stock movements remain connected to the appropriate records. These relationships support the pages we demonstrated without requiring us to explain every table or column.

The important point is that the modules use related saved information rather than separate, disconnected examples.”

**Action:** Stay on the slide. Open database code only if asked a specific question.

### Slide 27 — Summary and questions

**On screen**

Eight required modules in one application
Simple PHP MVC organization
Live workflows supported by documentation
Next: stakeholder interview and validated adjustments

**Speech**

“BayanReady brings the eight required modules into one application, with accounts, dashboards and a shared map supporting the workflows. We have shown how a resident report reaches staff, how selected household members affect evacuation occupancy and how a distribution connects to inventory.

We described our implementation as procedural and object-oriented PHP organized in a simple MVC structure, and we showed the corresponding code. Our references support those technical explanations, while our documentation describes the project's scope and intended behavior.

Our next step is to acquire a stakeholder, validate the actual process and document the adjustments needed for that client. Thank you for listening. We are ready for your questions.”

**Action:** Reserve about three minutes. Direct each module question to its owner. Keep the reference appendix ready if the instructor asks for a source.

### Slide 28 — Supporting references

**On screen**

- WEBSYS1 instructor. Final project announcement. Supplied course material.
- The PHP Group. Introduction. https://www.php.net/intro
- MDN Web Docs. MVC. https://developer.mozilla.org/en-US/docs/Glossary/MVC
- The PHP Group. Transactions and auto-commit. https://www.php.net/manual/en/pdo.transactions.php
- Leaflet. API reference, version 1.9.4. https://leafletjs.com/reference-1.9.4.html

**Speech, only if asked**

“These sources support the specific statements where we cited them. The course announcement defines the required modules. The PHP manual supports our description of PHP and transactions. MDN explains MVC, and Leaflet documents the map features we use. Our own code and website demonstrate how we applied those ideas.”

**Speaker-note bibliography**

1. WEBSYS1 instructor. Final requirement and assigned project modules. Announcement supplied by the team in the project conversation. Author name and announcement date have not been supplied. Retain the original course announcement for reference.
2. The PHP Group. (n.d.). Introduction. https://www.php.net/intro
3. MDN Web Docs. (n.d.). MVC. https://developer.mozilla.org/en-US/docs/Glossary/MVC
4. The PHP Group. (n.d.). Transactions and auto-commit. https://www.php.net/manual/en/pdo.transactions.php
5. Leaflet. (n.d.). Leaflet API reference, version 1.9.4. https://leafletjs.com/reference-1.9.4.html

Web references accessed 15 November 2026. No standalone RRL slide or research-literature discussion is included.

## Live switching sequence

For each feature: explain its purpose on the slide, switch to the running MAMP website, demonstrate the relevant fields and action, explain the displayed result, then return to the next slide. Each member also switches briefly to their assigned source code at slides 8, 12, 15, 19 and 23. There is no separate video or end-of-talk demonstration.

| Member | Live website or code demonstration | Return point |
|---|---|---|
| 1 | Household form, controller and model code | Slide 9, Member 2 |
| 2 | Roles, household registry, incident submission and response | Slide 14, Member 3 |
| 3 | Center, household arrivals, capacity and checkout | Slide 18, Member 4 |
| 4 | Inventory, multi-good distribution and record selection | Slide 21, Member 5 |
| 5 | Announcements, summaries, map, dashboard and documentation | Slide 27, closing and questions |

Every member has a required 60–90 second code demonstration within the allocated time. Explain one complete request path, then return to the next slide. Full study traces are in docs/team/traces/.

## Presenter preparation

- Open Slides, the fictional demo website, the three code files and the handbook before presenting. Use separate browser sessions for resident and admin.
- Prepare one five-member household, an empty five-person center, a response team, ten food packs, twenty water bottles and a draft notice. Confirm the actual example state before rehearsal.
- Keep the same fictional household throughout the story. Do not show passwords, configuration secrets, real household details or private contacts.
- One presenter speaks and operates at a time. Return to the named next slide at each handoff.
- Use the script around the live actions. Pause to let the audience see results; do not read the whole script while rapidly clicking.
- If an action fails, describe the displayed result honestly. Continue with an existing fictional record rather than claiming an unsaved action succeeded.
- Start MAMP and confirm Apache and MySQL are running before presenting. Open the working local website address and keep MAMP running throughout the presentation.
- No video segment is included. Each member demonstrates their assigned components live, immediately after introducing them on the relevant slide.
- Rehearse to 35 minutes before questions. If long, shorten navigation and repeat explanations; keep all eight modules and the required client/documentation sections.

## Class questions and local-network activity

Use the [Q&A and LAN demo guide](../docs/team/PRESENTATION_QA_AND_LAN_DEMO.md) for possible teacher/classmate questions, suggested answers and the hands-on activity. This activity follows the timed presentation unless the instructor allocates time within it.
