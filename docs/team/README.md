# BayanReady team handbook

Current implementation, 14 November 2026. Start here; older phase documents describe earlier designs and may mention removed publication switches, archive buttons or demo-location search.

Read [five-member ownership](TEAM_ASSIGNMENTS.md), [code walkthrough and complete source companion](CODE_WALKTHROUGH.md), [manual acceptance guide](TESTING_GUIDE.md), and [final verification](FINAL_VERIFICATION.md).

## What the website does

BayanReady helps staff record households, incidents, response actions, evacuees, supplies, distributions and notices. Residents register, submit reports and view their own reports, published notices, open centers and the community map. Staff use the admin role. Public registration always creates a resident, never an admin.

The final scope is one barangay. Atok, Benguet is currently a municipal demonstration focus, not the confirmed stakeholder or an approved boundary. The map opens at zoom 14 and allows panning. A pin does not calculate risk.

## Normal operating sequence

1. Staff register households and individual members. Household size comes from active member rows.
2. A resident submits an incident, written location, optional point and optional image. Staff can enter phone/in-person reports.
3. Staff verify the incident. Assigning a response team changes it to responding. Staff record actions, complete/cancel assignments and then resolve the incident.
4. Staff open evacuation centers with person capacities. Choose a household, uncheck absent members and check in the arriving members. Each person gets a stay record; walk-ins are also supported.
5. Staff enter relief goods and stock-in. A distribution selects a registered household, receiver and one or more goods. Stock and distribution history commit together.
6. Staff publish announcements. Residents see only published, active notices.
7. Dashboards and summaries calculate totals from the shared records. Reports are queries, not a separate report-entry table.

## Map, history and totals

Both roles' shared map automatically shows non-archived verified/responding/resolved incidents with coordinates, open centers and active distribution/both relief locations. Pending/rejected incidents and household markers are excluded. Incident map JSON contains only ID, type, status, date and coordinates; personal report details and images stay in guarded private pages. A resident's own pending report remains accessible in their report list even though it has no shared marker.

The shared picker supports map click, dragging, typed coordinates and device location. Explicit map/device selections request an editable address via the signed-in PHP Photon endpoint. It sends selected coordinates to an external provider. Opening an edit page does not request a new address. Internet failures leave manual entry available.

Available capacity (people) = sum of max(0, capacity − active occupants) across open, non-archived centers. Centers without coordinates still count. Active stays determine occupancy. Current dashboard incident cards exclude archived records; historical incident summaries retain them. Date filters apply to incident submissions and distributions, not current occupancy or current affected-household designation. Goods with different units are never combined into one quantity.

Manage Records has one searchable table and All/type filtering. Checkboxes select only the displayed page. Delete asks for JavaScript confirmation. Unlinked records are permanently deleted. Linked records are hidden from active pages while history remains stored. This uses archived_at internally, or is_active for teams. Remaining stock, active occupants and ongoing responses must be handled first; errors provide steps. A blocked batch changes nothing. Removing a linked household also hides its members. History is not a generic audit log of every edit.

## Local setup

Use PHP with PDO MySQL, fileinfo and cURL, plus MySQL. Import barangay_drrm.sql only into a new development database. Never re-import the fresh schema over records you need. For an existing database, review the migration files and apply only those missing from that installation.

Copy config/config.example.php to ignored config/config.php. Enter host, port, database, username, password and base_url. Set incident_upload_dir to an absolute, writable directory OUTSIDE the web root. This was the cause of the earlier all-images-fail issue: the running copy had an empty setting. Do not commit local configuration or image files. The address endpoint requires outbound HTTPS; maps need access to OpenStreetMap tiles.

Open index.php through your PHP web server, not directly as a file. Create fictional resident accounts using registration. Have the database/setup owner provide a fictional admin account with a password_hash-generated password; there is no public admin registration. The automatic test runner creates its own accounts and separate database.
