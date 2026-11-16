# Five-member code explanation and study guide

Updated 15 November 2026. The presentation has 28 slides. **Every member must explain actual code**, not only operate the website. Each gets a 60–90 second code segment inside their feature section. The full request traces below provide exact source, explanations, example values, failure branches and a complete speaking paragraph. Study the full chapter; show selected blocks during the talk.

| Member | Features to own and understand | Required code segment | Slide | Time including live demonstration/code |
|---|---|---|---|---|
| 1 | Login/session helpers, household and member registry | Household form → validation → PDO save → redirect/detail | 8 | 7 min |
| 2 | Incident reports/images, response teams and action history | Multipart form → validation/upload → incident/image transaction → ownership query | 12 | 7 min |
| 3 | Centers, household/walk-in arrivals, capacity and checkout | Selected IDs → guard/dispatch → locked batch validation → stay inserts → occupancy | 15 | 7 min |
| 4 | Inventory, distribution, relief locations and record removal | Goods map → validation → locked distribution transaction → detail/movement history | 19 | 7 min |
| 5 | Map/picker, dashboards, announcements, summaries and shared interface | Map page → guarded JSON → safe SQL fields → fetch → Leaflet marker/text popup | 23 | 7 min |

Total target: 35 minutes of explanation and demonstrations, plus 3 minutes for questions. Shorten repeated navigation rather than removing a member's code explanation. Client bodies stay blank. No video, screenshots or test-result presentation.

## Full source traces

- [Member 1: complete code trace](traces/member-1-households.md)
- [Member 2: complete code trace](traces/member-2-incidents.md)
- [Member 3: complete code trace](traces/member-3-evacuation.md)
- [Member 4: complete code trace](traces/member-4-relief.md)
- [Member 5: complete code trace](traces/member-5-map.md)

Each trace follows the actual current PHP/JavaScript, including parameters, returned values, database effects, validation failures and the result seen in the browser. Code blocks are exact pre-formatting excerpts with pinned source links and line anchors, not simplified replacement implementations. See READABILITY_CLEANUP.md and open this branch’s actual source for the current indentation. The companion excerpt manifest records what was copied so the documentation can be checked after code changes.

## Files each member should bookmark

| Member | Page / endpoint | Controller | Model / additional code |
|---|---|---|---|
| 1 | resources/views/admin/households.php; household.php; index.php | app/controllers/HouseholdController.php; UserController.php | app/models/Household.php; User.php; app/helpers.php; config/db.php |
| 2 | resources/views/incidents/create.php; view.php; image.php; admin/response/incident.php | app/controllers/ReportController.php; ResponseController.php | app/models/Report.php; Response.php |
| 3 | resources/views/admin/evacuation/center.php; centers.php | app/controllers/EvacuationController.php | app/models/Evacuation.php; app/evacuation-capacity.php; resources/assets/js/household-checkin.js |
| 4 | resources/views/admin/relief/distributions.php; inventory.php; movements.php; records.php | app/controllers/ReliefController.php; ReliefLocationController.php; RecordActionsController.php | app/models/Relief.php; ReliefLocation.php; RecordActions.php; resources/assets/js/record-selection.js |
| 5 | resources/views/maps/map.php; data.php; address.php; dashboard, announcement and summary pages | app/controllers/MapController.php; DashboardController.php; AnnouncementController.php; DisasterSummaryController.php | Matching app/models classes; resources/assets/js/community-map.js; location-picker.js; shared components/CSS |

## Everyone's minimum explanation

Explain the difference between a browser URL and a PHP filesystem path, GET and POST, a form value and a session identity, a controller and model, validation and escaping, and a successful redirect and an error returned to the same form. Know what prepare/execute/fetch do. Know that a database rollback cannot remove an uploaded filesystem file by itself.

Do not claim that the whole application is purely procedural, purely OOP or Laravel. It uses procedural page setup and functions with ordinary model/controller classes in a simple MVC organization. Existing types, helpers and safeguards are explained as implemented; this documentation does not silently simplify or rewrite them.

## Presentation and handoff

Keep MAMP running. Open the fictional website and source files before the talk. Each member introduces the feature, demonstrates it, traces the assigned code and returns to the next slide. Use actual displayed results rather than memorized database IDs or totals. Explain one rejected condition verbally if time is short. Retain detailed source chapters for questions rather than reading hundreds of lines aloud.

Review circle: Member 1 reviews 5; 2 reviews 1; 3 reviews 2; 4 reviews 3; 5 reviews 4. A reviewer should be able to follow the same request using the named files. The full speaking script is [here](../../presentation/LIVE_DEMO_PRESENTATION_SCRIPT.md). The current live deck is linked in [the presentation README](../../presentation/README.md).
