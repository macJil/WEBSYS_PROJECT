# Phase 1 — Project baseline

Project: Community Disaster Risk Reduction and Emergency Response Management System

Official project repository: https://github.com/macJil/WEBSYS_PROJECT

Prepared: November 11, 2026

Status: Development baseline based on the group's decisions in this chat. Individual team acknowledgement and stakeholder validation have not been recorded.

## 1. Purpose and scope

Build a simple, functional website that helps one barangay record households, incidents, response actions, evacuees, relief goods, distributions, and announcements. The first version serves one barangay. Expansion to additional barangays is future work.

All eight instructor-required modules and their listed components remain in scope through the course presentation. The presentation sequence will be selected after the first milestone; this does not remove required components from the project.

The prospective stakeholder is still being identified. Official barangay information, operational policies, and deployment requirements remain subject to interview and validation. Use fictional development data.

## 2. Required modules and workflows

| Module | Required scope | Agreed workflow |
|---|---|---|
| 1. Household and Resident Registry | Household information, number of members, address/location description, vulnerability classification, emergency contacts | Admin registers a household and its members, edits records, and searches the registry. Calculate member count from member records. |
| 2. Disaster Incident Reporting | Flood, fire, landslide, road obstruction, other emergencies, incident image uploads | Logged-in residents submit reports. Admins may record reports received from walk-ins. Reports begin pending. Image upload functionality must be implemented; an image is optional for an individual report so a missing photo does not block reporting. |
| 3. Emergency Response Management | Verification, response team assignment, action taken, response status monitoring | Admin verifies or rejects a report, assigns one or more teams, and records actions and progress. Keep action and assignment history. |
| 4. Evacuation Center Management | Center registration, available capacity, occupancy, evacuee registration, evacuation history | Admin manages centers and checks individuals in/out. Accept registered household members and walk-ins. Capacity is measured in people; occupancy comes from active check-ins. |
| 5. Relief Goods Inventory | Food packs, water, medicines, blankets/other supplies, stock-in/out | Admin maintains the item catalog and records every stock movement. Item units must be explicit. |
| 6. Relief Distribution Management | Affected households, distributed goods, receiving representatives, distribution history | Register the household first, identify it as affected, then record its receiving representative and one or more goods. Distribution and stock deduction succeed together or neither is saved. |
| 7. Emergency Announcement Management | Advisories, evacuation instructions, emergency announcements, preparedness reminders | Admin creates and edits drafts, publishes announcements, and withdraws published entries. Residents see published entries only. |
| 8. Disaster Reports | Affected-household counts, center occupancy, incident statistics, relief distribution reports | Admin views searchable/date-filtered summaries and printable reports calculated from stored records. |

Maps supplement the records. Start with address/purok and optional coordinates. Add map search and point selection after the barangay and map source are chosen. Map position must not generate a vulnerability or hazard classification automatically.

## 3. Roles and access

There are exactly two login roles in the first version: `admin` and `resident`. The admin role represents authorized system administrators, BDRRMC staff, barangay officials, and emergency response staff, with the same application permissions for this version.

| Action or information | Admin | Resident |
|---|---|---|
| Public registration | New accounts always become residents | Can register as resident |
| Admin account creation | Authorized setup/admin process only | Cannot create or promote admins |
| Household/member registry and private locations | Manage and view | No access |
| Incident submission | Can enter reports, including on behalf of walk-ins | Can submit while logged in |
| Incident history | View all | View own submissions and their status only |
| Incident verification, assignments, response updates | Manage | No access |
| Evacuee personal records and relief records | Manage and view | No access |
| Disaster summary reports | View | No access |
| Announcements | Manage drafts and publication | View published announcements |

A walk-in incident report is recorded under the logged-in admin account as its creator; the walk-in does not need an account. Keep the supplied reporting contact in the incident's contact field.

Precise incident locations, images, contacts, household details, and operational records are private to staff, with residents allowed to view their own incident submissions. General publication of incident records/maps is not part of the initial implementation. Any later public view must use an explicit publication action and an agreed list of safe fields; verification alone must not publish a report.

Every protected page and every submitted action checks the session and role in PHP. Hiding navigation links alone is insufficient.

## 4. Incident and response states

| Current incident status | Allowed next status | Meaning |
|---|---|---|
| `pending` | `verified`, `rejected` | Awaiting staff review |
| `verified` | `responding`, `resolved` | Accepted by staff; may be resolved without a team if no response is required, with an explanation |
| `responding` | `resolved` | Response activity is underway |
| `resolved` | None in the initial version | Work is completed and history remains available |
| `rejected` | None in the initial version | Not accepted; store the reason |

New incidents always start `pending`. Only admins change incident status. Record the reviewing/updating admin, date, and action/reason. Keep historical action notes.

Team assignments use `assigned`, `on_scene`, `completed`, or `cancelled`. An incident may have several teams. Only verified/responding incidents receive new assignments. Mark the incident responding when response begins. Before resolving an incident, complete or cancel its outstanding team assignments. Reopening incidents can be considered later if the stakeholder needs it.

## 5. Vulnerability categories

Use these working definitions for the first implementation. These are team design defaults for review, not stakeholder-approved assessments.

| Stored value | Display label | Definition |
|---|---|---|
| `unassessed` | Not yet assessed | Staff have not recorded an assessment. This is the default. |
| `no_additional_need_identified` | No additional assistance need identified | Staff have reviewed the record and have not identified an additional assistance need. This does not mean the household is safe from disasters. |
| `assistance_needed` | Additional assistance needed | Staff have identified a need for extra help with warning, evacuation, or relief access. Add a brief practical assistance note. |

Record the category manually, with assessment source/note, date, and responsible admin. Keep hazard type and hazard notes separate. Do not infer vulnerability from age, map location, weather, or coordinates. Do not use High/Moderate/Low hazard severity labels as substitutes for these categories.

## 6. Implementation conventions

Use PHP fundamentals, small OOP classes, PDO/MySQL, HTML/CSS, and ordinary forms. Bootstrap may be used for layout. Small JavaScript/jQuery/AJAX features may be added where useful, especially map search. The course plan includes a small JSON/AJAX feature; confirm any strict jQuery requirement with the instructor.

Keep root `index.php`, existing `app/`, `config/`, and `resources/` folders. Use direct PHP page URLs; there is no routing layer or public folder. Keep PHP sessions for authentication, with no custom cookie features. Normal PHP sessions ordinarily use a browser session cookie. CSRF tokens are outside the agreed classroom implementation scope.

### Naming

| Item | Convention | Example |
|---|---|---|
| Tables | Plural `snake_case` | `incident_reports` |
| Columns | `snake_case`; primary key `id`; relation key ends `_id` | `household_id`, `created_at` |
| Role/status values | Lowercase `snake_case` | `resident`, `on_scene` |
| Controller files/classes | PascalCase, ending Controller | `HouseholdController.php`, `HouseholdController` |
| Model files | Singular PascalCase | `Household.php` |
| Model classes | PascalCase ending Model, matching the existing UserModel pattern | `HouseholdModel` |
| PHP methods and variables | `camelCase` for new code | `findById()`, `$householdId` |
| Views | Lowercase `snake_case.php` | `household_members.php` |
| Form action values | Lowercase `snake_case` | `create_household` |

Existing methods may be normalized during foundation work; update all callers together. Use consistent stored values from the schema rather than inventing alternate status strings in screens.

### Page and class responsibilities

1. A view page loads shared helpers, checks its role, and instantiates its controller before sending HTML.
2. For a submitted form, the page checks the HTTP method and dispatches an explicit, permitted `action` to a controller method.
3. The controller validates input, applies permissions and workflow rules, and calls the model.
4. The model executes prepared PDO queries and returns data/results. It must not return HTML.
5. On success, redirect to the display page and show a session message. On a validation failure, show errors and preserve appropriate entered fields.
6. The view renders escaped data. JavaScript may improve the interface but server-side rules remain authoritative.

Do not call arbitrary methods or include arbitrary filenames based on user input. Use an explicit if/switch for supported form actions.

### Method and action examples

| Operation | Controller method | Model method | POST action |
|---|---|---|---|
| List/search | `index()` | `getAll($filters)` | No mutation; use GET filters |
| View one | `show($id)` | `findById($id)` | No mutation; use GET id |
| Create | `store($data)` | `create($data)` | `create_household` |
| Update | `update($id, $data)` | `update($id, $data)` | `update_household` |
| Delete eligible record | `destroy($id)` | `delete($id)` | `delete_household` |

Use specific actions for workflows, such as `verify_incident`, `assign_team`, `check_in`, `check_out`, `record_stock_in`, `record_distribution`, and `publish_announcement`. Use POST for changes; GET is for viewing/searching.

### Shared data rules

- Public registration cannot accept a requested admin role.
- Household member count and evacuation occupancy are calculated from their records.
- Preserve linked incident, evacuation, stock, and distribution history; block deletion of linked records and deactivate accounts/teams where appropriate.
- Evacuation check-in must prevent active duplicates and exceeding capacity, including simultaneous submissions.
- Relief distributions must validate stock and update distribution details, stock movements, and current quantity in one transaction.
- Required image upload functionality accepts optional images per report. Agree on supported types/size in Phase 2/3; generate server filenames and validate uploads in PHP.
- Database connection failure must produce a controlled error; models must receive PDO rather than an HTML error string.
- Keep local database credentials, uploaded files, and real resident records out of Git.

## 7. Five-member ownership and review

Replace Member 1–5 with actual names before creating assignments. This assigns review duties but does not claim any review or team approval has already occurred.

| Owner | Main implementation responsibility | Assigned reviewer |
|---|---|---|
| Member 1 | Shared PDO/session/layout foundation, accounts, announcements | Member 2 |
| Member 2 | Household/member registry and disaster summary reports | Member 3 |
| Member 3 | Incident reporting/images, verification, teams and response management | Member 4 |
| Member 4 | Centers, individual check-in/out, occupancy and evacuation history | Member 5 |
| Member 5 | Relief item catalog, stock movements, multi-item distributions | Member 1 |

Each owner delivers the model, controller, views, validation, and documentation for their modules. Each module owner also supplies the queries/data definitions Member 2 needs for disaster reports. Schema decisions are shared; module owners cannot change shared field names independently.

This is a starting allocation, not a claim of equal effort. At each milestone, compare remaining tasks and move bounded tasks when incident/response or relief work needs help. All members contribute interview preparation, integration checks, and presentation explanations.

Keep the existing `master` branch as the integration branch. Use one personal branch per member initially. Review changes before merging. Rotate integration coordination each milestone: Members 1, 2, 3, 4, 5, then repeat. Owners remain responsible for fixing their module's integration issues.

## 8. Phase 1 completion and Phase 2 handoff

Recorded in this document:

- Eight required modules and their initial workflows.
- Two roles and resident-only public registration.
- Resident and admin-entered walk-in reporting.
- Incident/assignment states and private record visibility.
- Working vulnerability definitions and manual assessment rules.
- Naming, direct page flow, and model/controller/view responsibilities.
- Five-member ownership and reviewer assignments using placeholders.

Team acknowledgement still to record: actual names, confirmation that each member has read the baseline, and any agreed changes to the working vulnerability definitions or assignment balance. Do not mark these as completed without the team's response.

Phase 2 should update the existing database rather than recreate unrelated schemas. Priorities are incident images, vulnerability assessment fields, verification/action metadata, and any walk-in reporting fields required by this baseline. Preserve the existing table names where they fit. Produce one reviewed schema and field dictionary before parallel feature work.

Stakeholder name, official barangay/purok list, boundary/map source, real contacts, deployment ownership, and hosting remain unresolved. These do not block the shared foundation and classroom modules using fictional data.

## 9. Baseline change record

| Date | Change | Basis | Approval status |
|---|---|---|---|
| 2026-11-11 | Initial Phase 1 implementation baseline | User's confirmed choices and instructor module list | User-directed baseline; individual team acknowledgement pending |

For deployment requests, record the original required component, requested addition/revision/removal, reason, interview evidence, decision/approver, date, and resulting implementation changes. Do not call a proposal stakeholder-approved before validation.
