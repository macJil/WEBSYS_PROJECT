# Phase 6 — Simple buttons and return links

Completed 2026-11-14 on bayanready-household-map-simplify.

## Shared pattern

Save, Submit, Check in, Publish and record-writing actions use Bootstrap primary buttons. Search, filtering, map controls, printing, selection review and checkout use bordered secondary buttons. Ordinary View, Edit, History, Cancel and Return links are underlined by the shared stylesheet. Buttons wrap long labels and stay within their container. Phone primary/action buttons and form Cancel/Return links have at least 44-pixel target height. No new frontend framework or interaction script is required.

Return destinations are explicit PHP URLs, with descriptive text. Main module pages return to the dashboard. Household/member pages return to the registry/household, incident forms to reports, response actions to incident details, center pages to centers, relief forms to inventory or their list, and announcement forms to announcements. Cancellation uses ordinary GET navigation and does not save.

Added or reviewed return links across all eight modules, including stock movements, distribution entry, household selection, walk-in entry, response assignment, relief-location details, summaries and record review.

## Destructive actions

The existing server-side record review remains the confirmation step for deletion/archive/deactivation: selected names and IDs are displayed, the final button explicitly names the action, and Cancel returns to selection. The model still checks eligibility, foreign keys and history. No deletion is performed by a navigation link.

Published announcements now display an explicit withdrawal checkbox; the controller rejects Save and withdraw without confirmation. This is an ordinary form check that also works without JavaScript. Draft saving and publishing remain primary actions. The withdrawal action has a danger outline.

## Verification and issues

- Real PHP/HTTP checks in an isolated fictional MySQL database covered 35 rendered admin/resident page states and 62 local destinations. Every rendered button checked had a visible label and Bootstrap/button styling. Destinations were checked separately for each role.
- Verified successful create/edit redirects for teams, goods and announcements, plus stock recording, distribution, incident review, team assignment and response updates. Earlier household/incident/center/relief-location save/reload and check-in/checkout checks also passed in the same run.
- Confirmed withdrawal is blocked without its checkbox and succeeds with confirmation.
- Confirmed deletion review alone does not remove data; Cancel leaves records intact; a valid explicit confirmation deletes only the fictional unused team and returns to selection.
- Browser review at 390 × 844 and 1366 × 900 checked the actual temporary application's edit form and record review. Save, underlined Cancel, return navigation, the bordered Search button and clearly labelled deletion button were readable/reachable. Phone Save and deletion buttons were 44 pixels high; Cancel was about 45 pixels. No page horizontal overflow. Wide tables retain their own horizontal scroll.
- Browser review followed Cancel from edit to list and from deletion review to selection. No final deletion was submitted through the browser; disposable deletion was exercised by the HTTP test.
- Initial preview attempt ended because input was unavailable in a non-interactive terminal. Re-ran with an interactive terminal; application checks and browser review passed. This was a preview harness issue, not an application failure.
- PHP syntax and whitespace checks passed. Browser viewport override reset; temporary server and fictional database cleaned up.

Commands: python3 tests/household_map_persistence.py (includes tests/simple_actions.py); TEST_BROWSER_PAUSE=1 uses an interactive terminal for visual review.

Screenshots: workspace artifacts/phase6/phone.png and desktop.png, outside application source.

All changes remain local in the worktree. The separate running MAMP copy has not been synchronized for Phase 6. No commit/push or schema change was made.
