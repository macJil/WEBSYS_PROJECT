# Phase 5 — Accurate dashboard counts

Implemented 2026-11-14, bayanready-household-map-simplify.

## Definitions and changes

Available capacity (people) is the sum of remaining person capacity in open, non-archived centers. Each center contributes max(0, capacity minus active stays). A stay is active when checked_out_at is NULL. Centers do not need coordinates to contribute. Household membership changes do not add or remove stays.

A small shared availableCapacity() function in app/evacuation-capacity.php now supplies the same calculation to DashboardModel, EvacuationModel, MapModel and DisasterSummaryModel. Each query counts active stays with the same checked_out_at IS NULL rule. The public map keeps its existing safe column selection and only maps valid points; ordinary lists and totals include usable no-point centers. No occupancy column or schema change was added.

The label is now Available capacity (people) on the card, center lists, center detail, map popup and disaster summary. Cards sum the model's available values rather than independently calculating them. Explanation text states which centers contribute.

Incident cards and linked status lists both exclude archived incidents. Resident queries filter reported_by to the signed-in resident; admin cards use all non-archived reports. All five statuses were checked against their matching list query. Existing current affected-household and notice rules remain unchanged.

Historical incident summaries intentionally retain archived reports in the selected date range; explanatory text now makes that distinction explicit. Historical center rows also remain visible with an archived label, but closed/archived centers contribute zero available capacity. Summary total installed capacity explicitly includes closed and archived centers; available capacity matches the current dashboard.

## Issue and recheck

Found: disaster summary could include capacity from an archived center that still had status open. Fixed by using the shared capacity rule, which rejects archived centers. Rechecked with a fictional archived open center of capacity 100 and a closed center of capacity 50; neither increases available capacity, and both remain in summary history.

## Acceptance

- Expanded fictional MySQL household suite: 83 checks passed, covering before/after household check-in, blocked batches, individual checkout, transfer, walk-ins, no-point center totals, map/detail/list/summary parity, archived history and incident owner/status parity. Temporary database removed afterward.
- Actual HTTP forms in a copied temporary site: available capacity 12 / active evacuees 0 before check-in; 7 / 5 after five members arrive; 8 / 4 after one checkout. Linked cards returned working pages and center details matched at each step.
- The same HTTP suite passed incident/household/center/relief create-edit-reload workflows and resident denial on staff forms.
- Updated dashboard regression fixture to current automatic visibility behavior, removing obsolete public flags and low-stock checks. Dashboard, historical-summary and shared-map regression checks passed.
- PHP syntax and diff checks passed. No new visual browser check was needed for the text/query changes; phone/desktop map review is recorded in Phase 4.

Changes are in the project worktree. The separate MAMP application copy has not been synchronized for Phase 5. No commit or push was performed.
