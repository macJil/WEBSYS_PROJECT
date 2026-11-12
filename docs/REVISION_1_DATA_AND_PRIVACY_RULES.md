# Revision 1 — Data and privacy rules

Status: implementation baseline based on the user's accepted choices. Individual team review has not been recorded. Future visibility/archive rules below are specifications for later revisions, not claims that those features already exist.

## Demo area and examples

Configurable name: Barangay Irisan, Baguio City (DEMO). Initial view: latitude 16.41792, longitude 120.56091, zoom 15. Boundary approval remains false; no polygon is provided or enforced.

The approximate locality point was checked against the [Irisan map listing](https://mapcarta.com/15766752), which identifies [OpenStreetMap node 311733296](https://www.openstreetmap.org/node/311733296). The direct OSM page could not be retrieved with the browsing tool. This is a locality reference, not a surveyed center or official boundary; live browser tile inspection remains pending.

| Fictional search label | Latitude | Longitude | Meaning |
|---|---|---|---|
| DEMO ONLY — Purok Sample A | 16.4179200 | 120.5609100 | Fictional meeting point |
| DEMO ONLY — Purok Sample B | 16.4192000 | 120.5623000 | Fictional roadside landmark |
| DEMO ONLY — Sample Community Hall | 16.4168000 | 120.5597000 | Fictional example address |

These labels do not identify real people, facilities, hazards, or official puroks at the coordinates. Existing database records are not rewritten. Config/local.php overrides example defaults: update existing local map latitude/longitude/zoom overrides to use this view. No database seed or migration is required in this revision.

## Map visibility contract

Anonymous requests receive no map data. The session determines the role; a posted role or layer name cannot grant access. Unpublished/private columns must not be sent to a resident browser, even in hidden HTML or JSON.

| Layer | Admin eligibility | Resident eligibility | Resident field allowlist |
|---|---|---|---|
| Incidents | Nonarchived reports with valid private coordinate pairs, including pending/rejected | Nonarchived, explicitly map-published, status verified/responding/resolved, valid separate public point and safe label | Opaque marker ID, type, public label, public latitude/longitude, status, submission date, approximate-location notice |
| Households | Nonarchived, valid point, manually recorded hazard_type other than unassessed | Never | None |
| Evacuation centers | Nonarchived centers with points, open or closed | Nonarchived, explicitly public, open, valid point | Marker ID, center name, address, coordinates, status, capacity, current occupancy, available places |
| Relief locations | Nonarchived locations with points | Nonarchived, explicitly public, type distribution or both, valid point | Marker ID, public location name/address, coordinates, location type |

Admin household popups use code, hazard category, location, and a protected detail link; family/member/contact details stay on the staff detail page. Other admin popups use a short label/status and protected record link. No household marker is automatically classified as dangerous because of its location.

Resident incident data excludes original report descriptions, reporter names/IDs, phone numbers, household links, images, exact private coordinates, source/walk-in details, review notes, and response assignment details. Public marker IDs grant no access to private detail endpoints. Public facility addresses must be suitable for residents; private notes must never be copied into those fields.

The resident's existing My reports page continues to show that resident's own submissions. That private access is separate from community publication.

## Publishing and withdrawal

- Defaults are private. Creating or verifying an incident does not enable map publication.
- Staff can publish verified/responding/resolved incidents only after supplying a safe public label and separate public point. Do not automatically copy private coordinates to public fields. Label public incident points approximate.
- Moving a published incident to pending or rejected must clear its publication flag in the same update. Reverification must not republish it automatically.
- Resolution retains a published marker with resolved status until staff withdraws it. Archive always hides a marker; restoring a record does not republish it automatically.
- Closing a center clears its public flag. Reopening requires explicit publication again.
- Relief locations of type storage cannot be resident-visible. Changing a public point to storage clears publication. Withdrawal removes it from subsequent requests.
- Map publication never grants permission to read private images or staff pages.

## Legend and picker rules

Use a blue shelter for open centers, gray shelter for closed centers (admin), orange warning triangle for incidents with a status label, green package for relief points, and purple house for manually recorded household concerns (admin). A legend and layer checkboxes explain these meanings; no color means a predicted risk level.

The reusable picker will support map clicks, manual coordinate inputs, and an opt-in current-location button. Show the returned accuracy and allow correcting the pin. Keep the written location, require paired coordinates or neither, and validate latitude -90..90 and longitude -180..180 server-side. Failed geolocation must leave the form usable. Selection alone saves nothing. These picker extensions belong to Revision 4.

## Record actions and eligibility

An admin bulk confirmation operates on the selected page IDs only. Recheck eligibility inside the transaction; if any item is blocked, change none. Preserve foreign keys. Never derive SQL table names from request input.

| Record | Permanent deletion eligibility | Archive/deactivate rule |
|---|---|---|
| Household | No members, stays, distributions, or other references | No active member stays; retain all links and historical membership |
| Household member | No evacuation records or references | No active stay; retain membership/history |
| Incident | No images, assignments, updates, or other references | Resolved/rejected only, no active assignments; clear public visibility |
| Response team | Never assigned | Deactivate; existing assignments remain manageable |
| Evacuation center | No evacuation history | Empty center only; close and clear visibility before archive |
| Walk-in | Never linked to a stay | No active stay; retain prior visits |
| Relief good | Zero stock and no movements/distribution details | Zero stock only; preserve unit and all history |
| Relief location | No distributions or other references | Clear public visibility, retain distribution links |
| Announcement | Never published and currently draft | Withdraw then archive |
| Images, assignments, response updates, stays, stock movements, distributions and their items | No bulk permanent deletion | Preserve as history; follow the existing completion/checkout/correcting-stock workflow |

Add a nullable first_published_at to announcements in Revision 2 so a withdrawn published notice cannot be mistaken for an unused draft. Backfill known published notices conservatively; the first publication time of existing withdrawn drafts is unknown, so protect legacy announcements from permanent deletion unless their unused status can be established. Archive is always the safe list-removal option.

Archived master records are excluded from new selections and normal active lists/maps. They remain reachable from protected history and through Show archived. Restore is a deliberate admin action; it does not automatically open facilities, activate teams, or republish markers. User-account management is outside this revision.

## Count definitions

- Current affected-household total: nonarchived households with registry_reason affected/both. Household member count uses nonarchived member rows; past stays/distributions retain their original references.
- Current incident cards: nonarchived incidents grouped by current status. Incident period summaries include archived historical reports unless an explicit filter says otherwise.
- Current occupancy: all stays without checkout time; archiving must never hide an active stay. Available places: max(capacity - occupancy, 0) for open, nonarchived centers only.
- Historical relief totals: all committed distributions and items, including those referencing archived goods/households. Never remove them by joining only active catalog rows.
- Low-stock cards: nonarchived goods with minimum_stock > 0 and quantity_on_hand < minimum_stock. Zero threshold disables the warning; equality is not below threshold.
- Resident cards: own reports by status, published nonarchived notices, and public open centers. Use the same conditions in the destination list.

## Handoff to Revision 2

Add the agreed fields through one additive migration: explicit public flags; separate incident public label/coordinates; minimum_stock; archived_at on eligible master tables; announcement publication history needed for delete eligibility. Keep operational states and archive states separate. Existing private data remains private by default.

Revision 1 delivers the rule matrices, demo configuration, and replacement fictional search points. Public map queries, dashboards, schema changes, and bulk actions remain the work of their respective later revisions.
