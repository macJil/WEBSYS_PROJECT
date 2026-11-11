# Phase 11 — Map search and location behavior

## Use

Open Report incident (resident) or Enter incident report (admin). Enter a location description/purok/landmark. Search the fictional catalog using **Sample A**, **Sample B**, or **Community Hall**, then choose a result. Selecting a result replaces both coordinate fields and the written description with the labeled fictional example. Alternatively click the map or enter latitude/longitude manually. When moving a point manually, update the description to match it. Clear pin empties both coordinates but keeps the written description.

Submit the ordinary incident form to save the selected point. Staff open that incident to review its written location, coordinates, and saved map point. Residents can review their own submissions only. Merely searching or selecting a point does not create a database record.

No migration is needed. This phase extends the existing incident location workflow; it does not add new household or evacuation-center map editors.

## Fictional search and map source

`resources/assets/js/demo-locations.js` contains three fictional labels and illustrative coordinates. These coordinates represent arbitrary points on a real basemap; they do not identify actual facilities, approved centers, or the intended barangay. Local text matching works without a geocoding API and sends no typed search terms to an external search service. It is intentionally a demo catalog, not a general real-address search service.

Leaflet 1.9.4 and OpenStreetMap tiles remain the basemap. Attribution is visible; requests use normal browser caching and no bulk/offline tile download. Map loading requires internet. Local catalog search, coordinate entry, and written location submission still work when Leaflet/tiles fail. Without JavaScript, use the ordinary description/coordinate fields. Automatic device-location capture was removed from the classroom form to keep the examples fictional.

References: [Leaflet 1.9.4 API](https://leafletjs.com/reference-1.9.4.html), [OpenStreetMap tile policy](https://operations.osmfoundation.org/policies/tiles/). Tile providers receive ordinary viewport/browser requests; no incident description, contact name, or household record is sent by these scripts.

## Limits and later stakeholder validation

A pin indicates a reported location only. It does not establish danger, vulnerability, safety, jurisdiction, response availability, or an approved evacuation site. Neutral markers are used. There is no automatic hazard scoring, assumed barangay polygon, or boundary restriction.

After selecting a barangay, confirm its boundary source and permission/accuracy with the stakeholder, validate approved landmarks/centers, choose the initial map view, and decide whether boundary restrictions or real-address geocoding are needed. Document those changes before replacing the demo catalog. Do not treat a third-party map label as stakeholder approval.

Server-side incident validation still requires a written location, accepts either both coordinates or neither, and checks latitude/longitude ranges. The database stores coordinates on the incident identified by the returned insert ID; no shared/global location record is used.

## Checkpoint

Passed:

- `node tests/phase11_location.js`: actual interaction scripts with small DOM/map stand-ins; search selection, filled description/coordinates, map click, invalid-coordinate messaging, clear pin, no-match result, and operation without Leaflet.
- `php tests/phase11_location.php`: actual report model with an isolated fictional SQLite database; staff retrieves selected coordinates/description for the correct incident, another incident remains separate, and another resident cannot retrieve the report.
- PHP syntax checks for changed pages and whitespace checks.

These checks do not replace a real-browser end-to-end submission or MySQL verification. Live tile rendering, browser layout, and the team's local MySQL save/review flow remain pending. No real locations or live records were created during verification.
