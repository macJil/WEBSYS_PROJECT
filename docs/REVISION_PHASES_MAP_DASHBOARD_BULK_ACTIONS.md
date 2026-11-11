# Revision plan — shared map, dashboards, and bulk actions

Branch: `revision-map-dashboard-bulk-actions` (starts from Phase 12 commit `e614060`).

This plan extends the existing one-barangay PHP/PDO application. It does not replace the eight required modules. Use root `index.php`, direct guarded PHP pages, simple controllers/models/views, sessions, ordinary forms, and small JavaScript for Leaflet. Keep fictional personal records and contacts in development data.

## Locked choices

1. Use **Barangay Irisan, Baguio City as the named demo area**, solely to focus the map and fictional records on one barangay. This is a configurable demo label, not a stakeholder identity or an approved boundary. Before entering sample points, verify the approximate map center on a basemap; do not invent a boundary polygon. Existing fictional sample points outside the chosen demo area must be replaced with clearly labeled fictional examples inside the chosen view.
2. One Leaflet map design is shared by admin and resident pages. Both roles can see approved, open evacuation centers; publicly approved relief points; and incidents staff explicitly choose to show on the community map. Admins additionally see all incident points, recorded household hazard concerns, closed centers, and internal relief locations.
3. An incident's `verified` status does **not** publish it. Staff must enable public map visibility separately and provide a safe public label and public map point. Keep exact reporter coordinates, images, names, phones, descriptions, and household links private. A public point may be approximate and must be described as such. Residents may still see their own full submitted incident through the existing private view.
4. Household markers and personal details remain staff-only. Their hazard category is manually recorded. A point, marker color, or nearby report never calculates risk or proves safety.
5. Marker meaning uses **shape/icon and color** together, with a legend and layer checkboxes. A clear default: blue shelter = open center; gray shelter = closed center (admin only); orange warning triangle = published incident; green package = approved relief point; purple house = staff-only recorded household concern. Do not label every household “at risk” automatically.
6. Location entry offers a written address/purok/landmark, map click, and **Use my current location**. The browser asks permission only after the button is pressed. Users can move/clear the pin; written location remains required for incident reports. If permission or map tiles fail, manual entry still works. Staff get the same picker on incident, household, evacuation-center, and relief-location forms. Residents use it for their incident reports.
7. Dashboard counts are live SQL summaries on existing records. Admin: pending/verified/responding incidents, affected households, current evacuees, open centers/available places, and goods below a configured minimum. Resident: own incident statuses, published notices, and approved open centers. Counts link to the matching list and use the same inclusion rules as that list.
8. Admin lists support checkbox selection and an explicit bulk action. **Permanent delete is allowed only for unreferenced records.** Archive/deactivate linked master records; retain completed distributions, stock movements, response actions, and evacuation stays as history. A failed or ineligible selection is explained. No cascade deletion or disabling foreign keys.
9. Keep one barangay-wide inventory. No geocoding service, automatic hazard scoring, live responder tracking, or new user role is needed for these revisions.

## Revision 1 — Freeze data and privacy rules

**Goal:** Agree on exactly what each role may see and what “delete” means for each record type before changing tables or pages.

- Create a visibility matrix for every map layer and popup field. Treat the resident map as a separate public projection of approved information, not a copy of the admin map with hidden buttons.
- Set the demo barangay label, map center/zoom, and clearly fictional place names in configuration/seed fixtures. Keep an approved-boundary field unset.
- Define public incident fields: type, safe general location label, approximate/public point, current public status, and report date. Decide how withdrawn/rejected incidents disappear from the resident map. Recommendation: staff can turn visibility off at any time; rejected reports cannot be published; resolving a published incident keeps it visible until staff withdraws it, with resolved status shown.
- Decide record classes for bulk actions (matrix below). Determine which archived records stay in historical summaries and which are excluded from “current” dashboard counts.
- Confirm that residents see open/approved centers and approved relief points only. Staff can mark a center or relief point public; closing a center removes it from the resident map automatically.

**Checkpoint:** A written field-by-field role matrix, bulk-action matrix, and fictional demo location list are approved by the team. No implementation relies on an assumed official boundary.

## Revision 2 — Add small schema changes and shared data queries

**Goal:** Make map visibility, safe public points, low-stock thresholds, and archiving explicit in MySQL.

- Create one additive migration for the selected fields. Suggested fields: incident public visibility, public label, public latitude/longitude; center and relief-location public visibility; minimum stock quantity per relief good; `archived_at` for master records that need archiving. Do not add a second household risk score or duplicate occupancy/member-count columns.
- Preserve existing foreign keys and history. Validate public coordinate pairs and visibility requirements in PHP; migration defaults keep current private records unpublished.
- Build one simple map data model with two role-specific queries. Resident SQL selects only public-safe fields. Admin SQL joins permitted internal records. Return JSON from a guarded direct PHP page or embed the same data in the map view; no routing layer is required.
- Keep records without coordinates in their normal lists and dashboard totals, but omit them from map markers with a visible “no map point” note where useful.

**Checkpoint:** Fresh schema and upgrade migration produce the same fields. A resident map request never contains private columns, unpublished incidents, household markers, or closed/unapproved facilities.

## Revision 3 — Shared map, legend, and saved markers

**Goal:** Let both roles explore the locations that have been added.

- Add **Map** to both navigation menus and a link or small map preview on each home page.
- Use one Leaflet script with layer checkboxes and a persistent legend. Keep a separate marker group per type. Popup text is created as text nodes/escaped values; avoid inserting record text as raw HTML.
- Center the initial view on the named demo barangay. Use the verified approximate center; do not pan-lock or filter by an unconfirmed boundary.
- Admin popup links to the protected record page. Resident popup links only to a public-safe detail if one exists; otherwise it shows the safe summary on the map. A resident cannot reach admin detail URLs by changing a link.
- Mark center status visibly. Optional fit-to-visible-markers button is useful, but keep the initial map predictable.

**Checkpoint:** Admin and resident maps show their intended layers and legend. Changing checkboxes affects display only; refreshing reloads current saved records. No private map data appears in resident responses.

## Revision 4 — One reusable location picker

**Goal:** Use the same simple location behavior across forms without copying large map scripts.

- Refactor the existing incident map selection into a small reusable picker: written description, latitude/longitude, click-to-place, clear/move pin, and **Use my current location**.
- Geolocation is opt-in per click. Handle permission denied, timeout, missing API, and low accuracy with a plain message and manual fallback. Show an accuracy circle or textual accuracy value if available; the person must confirm the point.
- Use the picker on resident and admin incident forms, then on staff household, evacuation-center, and relief-location add/edit pages. Add a simple relief-location editor if needed; the database already has relief locations but no current management page.
- For incident reports, keep the current required written location and optional valid coordinate pair. Staff location forms should also require a clear address/description when a point is added. Server-side validation must verify coordinate ranges and pair completeness, independent of JavaScript.
- On edit, show the saved point and update only that record's coordinates. On staff detail pages, show the saved point and written location together.

**Checkpoint:** All three selection methods save the intended record's point. Denied geolocation and unavailable tiles still allow manual reporting. An incident point does not alter hazard classification.

## Revision 5 — Useful home dashboards

**Goal:** Let each role see a small, current overview when signing in.

- Add model queries for the agreed cards. Reuse the definitions already used by incident filters, evacuation occupancy, affected-household registry status, and public facility visibility.
- Admin cards: pending, verified, and responding incidents; affected households; active evacuees; open centers with total available places; low-stock item count. Keep each card linked to a list/filter.
- Resident cards: counts of the signed-in resident's own reports by status; latest published announcement count/list; approved open center count. Include a map link or compact preview. Do not expose total private household or incident data.
- Add `minimum_stock` as an item-level threshold if approved in Revision 1. A zero threshold means no low-stock warning for that item until staff sets one. Never compare different goods' units in one stock quantity total.

**Checkpoint:** Card counts match the corresponding filtered pages for fictional records. Admin/resident dashboards have no broken links or private cross-role data.

## Revision 6 — Admin checkbox actions and history protection

**Goal:** Let admins process several selected master records safely while preserving the operational history.

- Add checkbox selection, Select all on this page, and a selected-count label to relevant admin lists. Search/filter results determine the selectable page, not all records in the database.
- Show a review step listing chosen IDs/names and the proposed action. Use one POST and one database transaction per confirmed batch. Recheck roles, IDs, links, current state, and active occupancy/stock inside the transaction. Use server-side allowlists for table/action names; never interpolate a posted table name into SQL.
- For unreferenced master records, allow permanent delete. For referenced master records, offer archive/deactivate and hide them from active lists while preserving access from history and a Show archived filter. Reject attempts to archive occupied centers or goods with remaining stock until the team defines a safe handling procedure.
- A batch is **all or nothing**: if any selected record is ineligible, change none and show which record blocked it. This is easiest to understand and prevents surprising partial deletion.
- Keep transaction/event history immutable: distributions, stock movements, evacuation stays, response updates, and incident assignments are not bulk-deleted. Provide links to their parent/history and instructions for a documented correcting transaction when needed.
- Apply the same backend rules to a single selected record; the checkboxes also support one-item actions. Never rely on hidden/disabled buttons as the permission check.

**Suggested action matrix**

| Record list | Delete when unused | Preserve or archive when linked |
|---|---|---|
| Households and members | Yes, only if no linked stay/distribution | Archive household/member; keep historical links |
| Incidents | Yes, only if no images, assignments, or history | Archive incident; keep report/response history and private image linkage |
| Response teams | Yes, only if never assigned | Deactivate team |
| Evacuation centers and walk-ins | Yes, only if never linked to a stay | Close/archive center or archive walk-in after all stays end |
| Relief goods and locations | Yes, only if never used | Archive catalog/location after safe stock and distribution checks |
| Announcements | Yes for an unlinked draft | Withdraw, then archive a previously published notice |
| Distribution, stock, evacuation, and response history | No | Keep history available; use a documented corrective action where applicable |

Users and authentication accounts are outside this bulk-list revision because there is no admin account-management page; referenced staff/resident IDs remain protected by foreign keys.

**Checkpoint:** Single and multi-select actions obey the same rules. A blocked batch changes no records. History links, stock balances, occupancy, and disaster report totals remain consistent after eligible actions.

## Revision 7 — Integrated review and issue log

**Goal:** Confirm the revisions work with the eight existing modules on one database.

- Use fictional accounts and records in one isolated MySQL database. Check every map/dashboard/selection page as anonymous, resident, and admin, including direct URL and submitted-form access.
- Check public map removal after incident withdrawal, center closure, and location unapproval; check that resident network responses contain no private fields.
- Check map click, current-location permission/denial, manual coordinates, edit persistence, and no-point records. Confirm legend meaning and text alternatives in a browser.
- Check dashboard counts against list queries. Check single/multi-select deletion, archive, blocked batches, and foreign-key restrictions. Confirm event history and totals remain correct.
- Run relevant earlier module checks after schema/query changes and record each issue, fix, and recheck in a revision integration log.

**Checkpoint:** Shared map, pickers, dashboards, and bulk actions work with the same database and navigation. The issue log states what passed and what still needs a team decision.

## Five-member work split

Use the existing cyclic review assignments from the Phase 1 baseline: Member 1’s work is reviewed by Member 2, Member 2’s by Member 3, Member 3’s by Member 4, Member 4’s by Member 5, and Member 5’s by Member 1. No one merges an unreviewed schema or privacy change.

| Member | Primary ownership | Review/coordination focus |
|---|---|---|
| 1 | Revision 1 decisions and Revision 2 migration/public queries | Map data contract and resident privacy |
| 2 | Revision 3 shared map, legend, layers | Coordinates/marker behavior with Member 3 |
| 3 | Revision 4 reusable picker and location forms | Server validation and saved-point checks |
| 4 | Revision 5 dashboard models/views | Count definitions and filter links |
| 5 | Revision 6 bulk actions and history rules | Foreign keys, transactions, archive effects |

All five contribute fictional fixtures and review in Revision 7. Integrate in small increments in the order above; changes to shared tables/query contracts are agreed before parallel view work begins.
