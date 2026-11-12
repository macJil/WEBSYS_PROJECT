# BayanReady: map, address, and responsive interface phases

Planning baseline, 12 November 2026. This plan continues the eight required modules and the existing admin/resident privacy rules. No implementation or deployment is included in this branch yet.

## Decisions carried forward

- Barangay Irisan is a temporary demo label and approximate map center, not a confirmed client or official boundary. Never claim that a marker is inside Irisan without a verified boundary.
- A resident's newly submitted incident is immediately visible in that resident's private report list. A valid saved point is immediately visible to staff. It appears on the community map only after staff separately approves public visibility, a safe label, and an approximate public point. Rejected or withdrawn reports disappear from the community map; resolved published reports remain until withdrawn.
- Written location remains required for incident reports. Coordinates are optional and must be a valid pair. Reports without points remain in lists and counts.
- Current location is requested only when the button is pressed. A selected point never predicts risk.
- Automatic address lookup is a suggestion. The person reviews and may edit it. The final written location and coordinates are saved together. Failed lookup never prevents manual entry.

## Phase 1 — Investigate and lock the map behavior

Inspect the incident form, save query, staff/public map queries, and existing demo records using one isolated fictional database. Record why each test report does or does not display: missing coordinates, invalid pair, filtered status/visibility, stale map data, or a point outside the initial view. Confirm the existing Irisan center against a basemap and set a useful opening zoom and minimum zoom; keep panning and point selection available. Do not fabricate an Irisan boundary. Decide whether the requested “only Irisan” means a focused opening view with limited zoom (recommended) or a formally bounded map once an approved polygon exists.

**Done when:** Every missing marker has a documented reason; a valid new incident point appears on the staff map; the community map still follows publication rules; the map opens focused on Irisan.

## Phase 2 — Fix location saving and demo fixtures

Use the shared picker for incident, center, household, and relief-location forms. Check that click, typed coordinate pair, current-location choice, clearing, and editing all save to the intended record. On edit, show the saved pin. Replace fictional sample coordinates outside the Irisan demo view with clearly fictional points near the verified center. Do not move any real or user-created report to make it fit. Add a clear “no map point” note to relevant lists/details; keep such records in counts and search results.

**Done when:** A new report and edited center show the saved point on staff views after reload. Records without coordinates remain usable.

## Phase 3 — Suggest an address from a selected point

Choose one free reverse-geocoding provider after checking its current terms, rate limits, attribution, and availability. The browser requests a readable address only after the person selects a point or explicitly chooses current location. Debounce rapid pin moves, cancel/ignore stale results, and do not send precise points to the provider merely by opening a page. Never embed a private API key in JavaScript. Use a small PHP proxy only if provider rules or key handling require it, with simple validation and a modest request limit. Prefer a short street/purok/landmark label when returned; do not invent a house number or assert Irisan from a partial result. Display “Address suggested; please confirm” and allow edits. Manual typing must work during timeout, denied permission, unavailable tiles, or lookup failure. Document that third-party lookup receives the selected coordinates and verify this is acceptable before deployment with real resident data.

**Done when:** Click/current-location suggestions fill the written field without overwriting later manual edits; failed lookup leaves the selected coordinates and manual form usable; saved text and point survive reload.

## Phase 4 — Rename the visible product

Set the visible name to **BayanReady** in shared navigation, browser titles, login/registration, dashboards, footer, and user-facing copy. Keep existing PHP classes, table names, and database name for simplicity unless a user-visible mismatch requires a narrow change. Retain the demo-area disclaimer.

**Done when:** Both roles see BayanReady throughout the shared interface and no old product title remains in visible pages.

## Phase 5 — Establish the simple visual system

Use the bundled Bootstrap grid, forms, buttons, and utilities with one small shared CSS file for colors, typography, spacing, cards, status badges, focus states, and map sizing. Update shared header/navigation first. Avoid new frameworks, build steps, duplicated inline styles, or complicated components. Keep labels and status text meaningful without relying on color alone.

**Done when:** Login, navigation, dashboard, forms, and notices use one consistent style and remain readable without custom JavaScript.

## Phase 6 — Apply responsive layouts module by module

Update incident/report pages, household registry, response, evacuation, relief, announcements, summaries, and record actions using the same patterns. Stack form fields/cards at phone width. Collapse navigation with the existing Bootstrap behavior. Give tables horizontal scrolling plus clear key fields; keep action buttons reachable. Keep map controls and legend usable on a phone without covering the map. Preserve ordinary PHP forms and direct role guards.

**Done when:** The eight modules work at phone and desktop widths with no clipped primary action, unreadable form, or inaccessible map legend.

## Phase 7 — Integrated acceptance and issue log

Run the full fictional MySQL suite, then browser-check anonymous, resident, and admin views. Test a new incident with map click, manual coordinates, current-location approval/denial, and no point; verify staff visibility, resident private ownership, and separate public publication/withdrawal. Test center and relief-point changes, address suggestion failures, map legend, keyboard/text alternatives, dashboard/list count parity, and archived record behavior. Review at representative narrow and wide widths. Record each defect, fix, and recheck in an issue log. Keep real locations and contacts out of fixtures.

**Done when:** All required workflows pass on the same database, browser checks are recorded, and any remaining stakeholder/boundary decisions are explicit.

## Simple implementation order and ownership

One person should first own the shared picker/map change and its field contract; other members can work on visible naming, shared CSS/navigation, responsive module pages, and integrated testing after that contract is stable. Review each other's pages before merging. Keep each phase in a small commit so regressions are easy to locate.
