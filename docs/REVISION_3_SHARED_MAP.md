# Revision 3 — Shared map, legend, and saved markers

Open **Map** in either role's navigation or use the map link on the home page. The legacy resident community.map.php URL now opens the shared map. Migration 004 from Revision 2 must already be applied.

The initial view uses the Irisan demo center/zoom in configuration. There is no boundary restriction and no automatic fit on load. **Return to demo area** restores the configured view. **Show visible markers** fits only the checked layers; it explains when none are visible. Refresh reloads saved data from the role-guarded JSON endpoint. Changing checkboxes changes display only and does not write records or request broader access.

One Leaflet script maintains separate groups for incidents, centers, relief locations, and staff-only household concerns. The persistent legend uses symbols plus colors: orange warning triangle, blue/gray shelter for open/closed centers, green relief symbol, and purple household concern. Counts represent mapped records only; records without coordinates remain in their normal lists and totals.

Popups use DOM textContent for database values. Only fixed icon constants appear in icon HTML. Resident popups use public incident points/labels and never link to staff details. Admin popups link to guarded incident, household, center, and relief-location pages. A minimal read-only relief-location detail page was added so those markers have a valid destination; the location editor belongs to the next revision.

Public visibility defaults remain private. Therefore residents may see no markers until staff-approved fixture data exists; this phase does not automatically publish records or create real locations. No public publication editor is introduced here. Household concerns are manually recorded, not inferred from nearby markers.

Map-library, tile, data-loading, session-expiry, and empty-state messages are provided. The layout stacks the legend above the map on narrow screens. Leaflet and tiles require internet. Written record pages remain available when the external map library fails.

## Checks performed

- `node tests/revision3_map.js`: actual map script with DOM/Leaflet stand-ins, both roles, separate layers, correct coordinate sources, safe popup text, admin-only links, closed-center icons, toggles, no automatic fitting, and empty fit behavior.
- `php tests/revision2_map.php`: existing privacy and publication checks passed.
- HTTP checks against temporary fictional in-memory SQLite data: anonymous map redirect and JSON 401; non-GET JSON 405; resident HTML excludes the household control; resident JSON excludes private fields and ignores a forged role parameter; admin gets permitted layers; relief detail denies residents and admits admins.
- Changed PHP files passed syntax checks; whitespace checks passed.

The temporary fixture used a test-only header to choose fictional sessions outside the repository; production pages use the existing PHP session. No application database or local connection configuration was changed. Visual rendering with real Leaflet tiles and browser layout remains to be reviewed; scripted interaction checks do not claim screenshot verification.
