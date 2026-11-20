# Member 1 · Stakeholder and database study chapter

Present the MDRRM Office visit on 17 November 2026, staff approval of the eight modules, the team-reported head approval and the requests for a dashboard, weather and printing. Use the [interview record](../../municipal/README.md). Participant names, exact letterhead wording and final print format remain pending.

Use the team’s isolated fictional database in MySQL Workbench or your local database viewer. Open the table’s data view. Read the values and point to the linked IDs. Use the actual generated IDs in that demo copy.

| Tables to open | Values to point to | What the audience should understand |
|---|---|---|
| users | id, full_name, email, phone, password, role | Role determines access. Password contains a hash. One account can submit several incidents. |
| households and household_members | household id, barangay, representative_member_id; member id, household_id, full_name | Three member rows belonging to one household give a count of three. The representative is one of those members. |
| incident_reports and incident_images | reported_by, incident_type, status, barangay, reporter_email, latitude/longitude; incident_id, stored_filename | Account ownership and submitted contact details serve different purposes. Image references connect to an incident; bytes live in a separate folder. |
| responders, incident_assignments, response_updates | incident_id, responder_id, status, action_taken | Assignments connect teams to incidents. Updates preserve actions and status changes. |
| evacuation_centers, evacuation_records, evacuation_walkins | capacity, center_id, household_member_id or walkin_id, checked_in_at, checked_out_at | Capacity is in people. A stay without checkout is one current occupant. Walk-ins also have person records. |
| relief_items and relief_stock_movements | item_name, unit, quantity_on_hand; movement_type, quantity, item_id | Bottles and packs keep separate balances and movement history. |
| relief_distributions and relief_distribution_items | household_id, received_by; distribution_id, item_id, quantity | One receiving event can contain several goods. Actual receiver is recorded separately from the household representative. |
| announcements and relief_locations | title, message, is_published; name, barangay, location_type, coordinates | Publication controls resident notices. Distribution points supply shared-map markers. |

## A connected example to explain

Use one fictional Topdac household with three member rows. Point to its representative_member_id and the matching member id. After Member 5 checks in two people, two active evacuation_records rows refer to those member IDs. After Member 6 distributes two goods, one relief_distributions header points to the household and two detail rows point to that distribution. These are three members, two occupants and one receiving event containing two goods.

The household_directory view joins household data with the selected representative to provide readable labels. It reads existing records. Dashboard and disaster summaries also query existing records. Weather uses an external service and browser caching.

## Prepare for questions

Explain primary IDs as record identifiers and foreign IDs as links. Explain NULL checkout as an unfinished stay. Distinguish stored capacity from calculated occupancy. Link protection and history-preserving removal retain past operational relationships. Show fictional records when projecting the database. Read the [fresh schema](../../../barangay_drrm.sql) to confirm column meanings. Member 2 introduces MVC and shows login code next.
