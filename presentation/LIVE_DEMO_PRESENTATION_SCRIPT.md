# BayanReady live presentation · nine speakers · 30 minutes

Before the timer starts, run MAMP and open separate staff and resident browser profiles. Prepare one fictional Topdac household with three active members, an empty five-person center, two active teams, two goods with five units each and a draft announcement. Use a non-personalized demo image for the incident. Open each member’s source files in editor tabs with session credentials and private config closed. Keep a second completed fictional incident ready in case a page reload or network delay interrupts a handoff.

One person calls each handoff at the time shown. The intervals include speaking, changing windows, entering values and waiting for the audience to see the result. Read the spoken paragraphs aloud. Perform the italicized cues. Q&A starts after 30:00.


## 00:00–00:35 · Member 1 · Slide 1 · BayanReady for Atok

*Open the deck and the team’s interview record. Point to the office and the scope of approval.*

Good day. We are presenting BayanReady, our community disaster risk reduction and emergency response management system for Atok, Benguet. On 17 November 2026, our team visited the MDRRM Office at the Municipality of Atok. We met with municipal disaster risk reduction and management personnel and assistant staff. We brought the eight required modules to that meeting so the people who would use the system could respond to the proposal.

## 00:35–01:10 · Member 1 · Slide 2 · The stakeholder visit

*If a photograph is cleared, show it here. Otherwise show the dated interview record in the backup guide.*

During the visit we explained the eight modules and the staff approved them. Our team also reports approval from the office head, who was away during the visit. We exchanged contacts for follow-up. That gives us a record of what was discussed and a way to confirm further requests. The approval we are reporting today was for the proposal and the modules. The office will still need to review the finished system in its own workflow.

## 01:10–02:00 · Member 1 · Slide 3 · What staff asked to see

*Show the municipal requirements record. Then switch to the signed-out browser.*

Staff emphasized having a dashboard. They did not prescribe the order of the cards, so that arrangement is our team’s decision. They also asked for weather information for CAR and a way to print reports. Our revision gives staff a dashboard they can filter by barangay, a forecast for a stated point in Atok with links to official regional advisories, and two printable outputs. Paper size, headings and signatories still need the office’s direction. As we present, watch how one change on a form appears later in another member’s part.

## 02:00–04:00 · Member 1 · Slide 4 · How a sign-in reaches the dashboard

*Sign in as the fictional staff account. Show index.php, UserController::handlePost and UserModel::findByEmail. Leave the staff session open.*

Here is the entrance to the website. I enter the fictional staff email and password and press Log in. The address changes to the staff dashboard. Notice that I did not choose my role on the form. The account supplies it. In the code, index.php runs ordinary PHP statements in order and renders the form. The controller is an object. Its handlePost method reads the request, checks it and asks the user model to find the account. After password verification it starts the signed-in state for that account and chooses the dashboard. This is what we mean by procedural PHP and object-oriented PHP organized using simple MVC. MDN describes MVC as separating data, presentation and control. You can see that division directly in these three files. Member 2 will use this staff session to identify a household.

## 04:00–07:15 · Member 2 · Slide 5 · Identify a household by its representative

*Filter Households to Topdac. Open the prepared household with three members. Select a different representative, save and search by the new name. Show the two selectRepresentative methods.*

Our fictional household lives in Topdac. At the top of the page you can see its representative and address. Below are the people registered to this household. A newly created household can remain without a representative while staff enter its members. Once those records exist, staff can select one of them. I am changing the representative and saving. Look at the address bar and the heading after the page reloads. The household’s name on screen has changed. Its record ID is the same. In household.php the form posts the selected member ID. The controller checks the request and the model checks that the selected person is an active member of this household. It stores the relationship. If that person has to be removed later, staff must select a replacement first. I can find the household by its new representative, and the evacuation and relief teams can still use the same household record. Next, Member 3 will open the resident’s incident form.

## 07:15–10:45 · Member 3 · Slide 6 · Submit an incident as a resident

*In the resident browser submit a fictional Topdac obstruction with a written landmark, a demo point and small fictional image. Read out the new reference. Show ReportController::create and ReportModel::create.*

I’m signed in as our fictional resident. My name, email and phone are filled from my account. I can change the callback number for this incident. I choose road obstruction, describe the obstruction, select Topdac and enter a landmark. I can click the map and confirm an editable address suggestion. If the network is slow I can still type a location. I’m attaching a fictional image and pressing Submit. Please remember this incident number. I’ll use it to return to this exact report. The controller validates the form and upload, and the model saves the submitted contact details with the incident. The signed-in account remains its owner. Staff entering a telephone report can choose assisted intake, which starts the contact fields blank. Right now our report is pending. I can open my own details while it awaits review. Member 4 will use the same reference in the staff browser.

## 10:45–14:00 · Member 4 · Slide 7 · Keep response and incident status in step

*Open the new incident as staff, verify it, assign two prepared teams and complete them in turn. Read the overall status after each. Show ResponseController::record and ResponseModel::record.*

I find the incident by its reference, review the reported details and record a verification note. Now I can assign our two fictional response teams. The first team has finished its part. I’ll record its action and mark its assignment completed. Look at the incident status while the other team is still assigned. It remains responding. When I complete the second assignment, the incident becomes resolved. Staff can read what happened in the earlier updates. In the response page each submission identifies the team assignment and the action taken. The controller validates the action. The model records the change and derives the incident status from the assignments on that incident. This lets staff update the work once and see the corresponding status on the incident page. A different team assigned to another incident keeps its own status. Member 5 will now show how an arrival changes the count on a center record.

## 14:00–17:15 · Member 5 · Slide 8 · Count the people who actually arrive

*Open a prepared empty five-person center. Select two of three household members. Check in, show 2 occupants and 3 places, then check out one and show 1 and 4. Open checkinHousehold.*

Our center has space for five people. Three members belong to the household, but only two have arrived. I select the household, uncheck the absent person and confirm the arrival. The center now shows two occupants and three available places. Each selected person has an individual stay. I can check one person out while the other stays here. After that change you should see one occupant and four available places. In the code the form sends the selected member IDs. The controller passes them to checkinHousehold. The model checks the center, membership, duplicate active stays and remaining capacity before it saves the group together. If the center can take only one person from a group of two, staff get a message and can correct the selection. Nobody in that failed submission is counted. Walk-ins also have a check-in option. Member 6 will use the registered household again to record relief supplies.

## 17:15–20:30 · Member 6 · Slide 9 · Distribute several goods in one transaction

*Prepare five bottles and five packs. Request two bottles and six packs. Show the rejection, correct to two of each, save and show both balances of three. Open ReliefModel::distribute.*

We have five bottles of water and five food packs in our fictional stock. I choose the household and enter the name of the person actually receiving the goods. That can be a different person from the household representative. First I request two bottles and six packs. The form tells me the stock is insufficient. Now I change the request to two bottles and two packs and save. The distribution has two lines, and the inventory has three of each left. I’ll open the movement history so you can see how the outgoing quantities were recorded. In the code, the controller reads each requested good and quantity. The model checks stock and saves the distribution, its items and stock movements in one database transaction. The PHP manual explains a transaction as a unit of work that can be committed or rolled back. The two balances you just saw are the practical reason we use one here. Member 7 will show how staff communicate updates to residents.

## 20:30–23:30 · Member 7 · Slide 10 · Publish a notice and read the forecast

*Publish a prepared fictional advisory. Refresh the resident notice list and both dashboards. Point to the forecast location, retrieval time or outage text, and official links. Show AnnouncementController::save and atok-weather.js.*

I have a draft that tells our fictional residents where to find the center. I publish it and refresh the resident’s notice list. Residents can now read what staff wrote. If staff withdraw this notice later, it leaves that list. The announcement controller checks the staff role and the action, and the model saves the title, message and publication state. The weather panel answers a different client request. Its label identifies the forecast point in Atok. When available, it shows daily low and high temperatures, peak rain probability and the time the browser retrieved them. A single point forecast cannot describe conditions throughout CAR. The panel links to PAGASA for regional advisories. If the forecast is unavailable today, you can see the fallback message and still use the rest of the dashboard. The small JavaScript file handles retrieval and briefly caches a successful result. Member 8 will explain the figures that come from our own operating records.

## 23:30–25:20 · Member 8 · Slide 11 · Read the dashboard in people and records

*Apply Topdac on the staff dashboard. Open Active evacuees, then available capacity. Identify the fictional center and its four remaining places. Show DashboardController::dashboardData and DashboardModel::stays.*

The office told us the dashboard matters, so let’s trace a number back to a person’s stay. I select Topdac, open Active evacuees and find the person still checked in. Available capacity is the sum of unused person places in open, non-archived centers in the selected area. Our five-person center contributes four after the checkout. Another open center would contribute its own remaining places, even without map coordinates. Inventory is municipality-wide. Its card counts catalog items because adding bottles to packs would give a misleading total. The controller builds cards from the model’s queries. A resident’s dashboard restricts incident counts to that resident’s reports.

## 25:20–27:00 · Member 8 · Slide 12 · Print the record you are authorized to see

*Open filtered Disaster summaries and Print summary, cancel the dialog. Open the prepared resident incident print tab. Show DisasterSummaryModel::summarize and the incident detail role check.*

Here is the summary for our selected barangay and dates. The incident and relief sections use the selected period. The household and occupancy sections describe the current registry and active stays. That distinction matters when someone prints a report for last month. In print preview the report carries the scope and generation time. Staff can print or save it as a PDF. On the resident’s incident page, the owner can print the submission and its reference. Staff review history belongs to the staff version. We will confirm the office’s formal headings, signatories and preferred paper before adopting an official format. Member 9 will bring the demonstration together on the map.

## 27:00–29:20 · Member 9 · Slide 13 · Find the same incident on the shared map

*Filter the community map, fit the markers and identify the resolved incident. Toggle one layer. Show map.php, data.php, MapController::mapData and MapModel::publicMap.*

Here is the same resolved incident from earlier. I select Topdac and choose Show all locations so the eligible saved points come into view. The legend lets me distinguish incidents, centers and relief locations. Turning a layer off affects my display. It does not change a record in the database. The map request goes through a session check and the list of allowed barangays. The model selects the fields that the shared map needs. It includes verified, responding and resolved incidents with points. Pending and rejected reports stay off the map. Household markers, reporter contacts and incident images stay out of the shared response. A record can still appear in a list or count if it has no coordinates. We use Leaflet to display locations, and the boundaries of the opening screen do not define an official emergency zone.

## 29:20–30:00 · Member 9 · Slide 14 · Documentation and handoff

*Show the documentation index. Finish the last sentence at 30:00, then invite questions.*

Our documentation connects the office’s requests to the pages you have seen and gives each team member a workflow to maintain. The interview record supports what we have said about the client. The code and live pages show what is implemented today. The setup guide, ownership inventory and self-test guide are available for the next person who runs BayanReady. Thank you. This concludes our thirty-minute demonstration. We can now take questions.

## Supporting references

- Team interview record, MDRRM Office, Municipality of Atok, 17 November 2026. [Client profile and inputs](../docs/municipal/README.md#interview-record)
- PHP supports classes and ordinary procedural code. [PHP introduction](https://www.php.net/intro) and [classes and objects](https://www.php.net/manual/en/language.oop5.basic.php)
- The code walkthrough uses the distinction between model, view and controller. [MDN MVC](https://developer.mozilla.org/en-US/docs/Glossary/MVC)
- The relief workflow uses PDO transactions. [PHP transactions](https://www.php.net/manual/en/pdo.transactions.php)
- The dashboard uses an Atok point forecast and links to official advisories. [Open-Meteo forecast documentation](https://open-meteo.com/en/docs), [PAGASA Northern Luzon](https://www.pagasa.dost.gov.ph/regional-forecast/nlprsd), [PAGASA flood advisories](https://www.pagasa.dost.gov.ph/flood)
