# BayanReady municipal team handbook

## What the website does

BayanReady helps staff record households, incidents, response actions, evacuees, supplies, distributions and notices. Residents register, submit reports and view their own reports, published notices, open centers and the community map. Staff use the admin role. Public registration always creates a resident, never an admin.

The shared municipal map supports all eight barangays in Atok, opens at zoom 11 and allows panning. The client is the MDRRM Office at the Municipality of Atok. A pin does not calculate risk.

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

## Setup and study

Use the [municipal setup and self-test guide](../municipal/SETUP_AND_SELF_TEST.md), [file owners and reviewers](../municipal/INVENTORY.md), [nine code walkthroughs](MEMBER_CODE_EXPLANATIONS.md) and [current speaking script](../../presentation/LIVE_DEMO_PRESENTATION_SCRIPT.md).
