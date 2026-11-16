# Response status and history visibility — 16 November 2026

## Setup

For an existing database, back it up and run `database/migrations/008_history_visibility.sql` once before using the updated PHP pages. Fresh installs use `barangay_drrm.sql`, which already includes these four nullable columns. Do not run this migration again on a fresh install. No operational rows are deleted.

## One update for team and incident

Update the team under Incident → Manage response. The same transaction updates its assignment, derives the incident state and saves an action record.

- At least one assigned or on-scene team: responding.
- Every assignment completed: resolved.
- No active assignments, but at least one cancelled: verified (cancelled work is not completed work).
- Reactivating a completed/cancelled assignment: responding.
- No assignments: normal staff review remains available; assign a team to start responding.

Staff review cannot override the derived status while assignments exist. Existing stored statuses are not silently rewritten by the schema migration: save the appropriate team update to synchronize an older incident. Pending/rejected reports must first be verified. Maps and dashboard queries continue to use the saved incident status.

Member 2 should explain `ResponseModel::assignmentStatus()`, the transaction in `record()`, and the matching guard in `ReportModel::review()`. The assignment rows remain separate because one incident may have several teams.

## Hide an entry, keep its records

Admins can use **Hide history entry** on staff review/response history, completed evacuation stays, stock movements and distribution history. A JavaScript confirmation explains that this hides the entry only. Active stays must be checked out first. Residents and anonymous visitors cannot submit this action.

`history-action.php` submits a type and ID to the guarded `admin/history.php` page. `HistoryController` validates the request; `HistoryModel` uses a fixed table allowlist, locks the selected row, and sets `history_hidden_at`. The destination comes from that stored row, never a posted URL. History-list queries exclude hidden entries; occupancy, stock, distribution totals and foreign keys do not.

Member 3 explains completed-stay hiding and occupancy. Member 4 explains stock/distribution hiding and unchanged balances. Member 1 explains the admin guard and reusable confirmation form. Member 5 explains why summaries still include stored operations. There is no restore interface in this revision; hiding is not erasure, and linked distribution details remain accessible from retained records.

## Team test steps

1. Use fictional records in a separate test database; apply the migration if upgrading.
2. Verify an incident, assign two active teams, and confirm responding on incident details and dashboard.
3. Complete one team: still responding. Complete the second: resolved.
4. Reactivate one team: responding. Cancel it while the other is completed: verified. Reactivate and complete it: resolved.
5. Try a conflicting incident status in staff review: expect an explanation and no change.
6. Hide a response entry; reload both staff review and response history. The entry disappears, but the status remains unchanged.
7. Check a person in: no hide action on the active stay. Check out, then hide the stay. Occupancy must remain unchanged by hiding.
8. Record stock-in and a distribution. Write down stock and distribution summary totals. Hide each history entry and confirm those totals remain identical.
9. As resident and anonymous, attempt the history action URL: deny/redirect without any write.
10. Cancel a browser confirmation: the entry remains. Confirm another entry: its button works and returns to its correct parent page. Check buttons on phone and desktop.

## Issue and recheck log

- Team completion previously left an incident status independent. Added shared derivation within the existing transaction. Fictional HTTP tests passed for one/two teams, cancellation, reactivation and conflicting manual review.
- Added display-only hiding rather than deleting operational events. Tests passed for admin-only POST, invalid type, active-stay rejection, list removal and preserved rows, stock, distribution details and active occupancy.
- Existing databases need new visibility columns. Tested upgrade against populated fictional tables; row counts survived. Fresh schema used by the full integration suite.
- Full existing HTTP suite passed: 35 rendered page states, 62 local links, role guards, report ownership, map privacy, household capacity/duplicate checks, stock rollback, linked history and valid/invalid image uploads. PHP 8.3 syntax checks passed.
- Interactive browser confirmation and phone/desktop visual checks remain team checks; no new visual-browser pass is claimed. The running MAMP checkout has not been replaced by this branch.
