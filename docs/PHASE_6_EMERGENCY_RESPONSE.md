# Phase 6 — Emergency response management

## Use

Sign in as an admin. Open **Response teams** to add/view/edit a team, its contact number, and active/inactive availability. Open an incident and choose **Manage response teams and actions** to dispatch teams and record updates. Residents cannot access these pages or submit response-management forms.

No new database migration is needed: this phase uses `responders`, `incident_assignments`, and `response_updates` from Phase 2. Keep the Phase 5 migration applied for phone reporting.

## Workflow rules

1. Staff review a pending incident and mark it verified.
2. Assign an active team with dispatch instructions. Repeat to assign additional teams. Each incident/team pair has one assignment, enforced by the existing unique key.
3. Dispatch sets overall incident status to responding. Assignment states are assigned, on_scene, completed, and cancelled.
4. Record required action notes with each update. Choosing the current assignment status adds a progress note without changing that status.
5. Assigned teams may move to on_scene, completed, or cancelled. On-scene assignments may complete or cancel. Completed/cancelled assignments may be reactivated as assigned, provided the team is active. Their original assignment ID, staff encoder, and date remain intact; subsequent dispatches are recorded in history.
6. Complete or cancel all active assignments before changing the overall incident out of responding. Completion does not automatically mean the emergency is resolved. Staff explicitly resolve the incident in its review form with notes.
7. To resume work on a resolved/rejected incident, staff reopen it as verified and assign/reactivate teams. Pending/resolved/rejected incidents do not accept response updates until verified/reopened.
8. An inactive team cannot receive new assignments or reactivate a closed assignment. Deactivation does not cancel its existing work; those assignments remain available for progress, completion, or cancellation.

The same restrictions apply through the existing incident review form. A new incident cannot be manually set to responding without dispatching a team. An already-responding incident with no remaining active assignments stays responding until staff review it.

## History and record protection

Each dispatch/update appends a `response_updates` row containing incident, staff account, server timestamp, action text, previous/new incident statuses, assignment link, and assignment status snapshot. Assignment updates include the old/new assignment statuses and the team name at the time in their action text. Editing team details later does not rewrite historical action text. Review events appear in the same chronological response history.

Assignments and incident status update within a database transaction. Both response writes and incident reviews lock the incident row first, preventing a simultaneous closure from bypassing an active dispatch. Assignment updates are scoped to the incident in the URL. Team activation is checked again when saving dispatches, not just in the selection list.

There are no delete/edit-history controls. Team deactivation preserves assignments and their foreign-key relationships. Team directory edits themselves are not separately audited; the response action history is the scope of this phase. Staff names in history are resolved from user accounts.

## Implementation pattern

- `app/models/Response.php`: PDO queries, team storage, transactional response updates.
- `app/controllers/ResponseController.php`: admin guard before connection, IDs, validation, form handling, redirect after success.
- `resources/views/admin/response/teams.php`: team directory and same-page add/edit form.
- `resources/views/admin/response/incident.php`: dispatch, assignment updates, and response/review history.
- Existing incident model/controller enforce compatible review status rules.

Ordinary PHP forms, shared layouts, escaped output, and session role checks are used. No routes, public directory, AJAX, or new dependencies are introduced.

## Verification status

Changed code was reviewed by reading it. Tests, PHP execution, browser checks, and MySQL runtime checks were not run for this phase. No live database or real team data was changed.

Before presentation, verify with fictional data: admin/resident access; duplicate team names; multiple teams on one incident; inactive-team dispatch denial; assignment status transitions; progress notes; cross-incident assignment denial; rejection of premature incident closure; explicit resolution after completion; reopening/reactivation; and preservation of older action entries.
