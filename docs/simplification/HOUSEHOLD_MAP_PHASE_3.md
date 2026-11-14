# Phase 3 — Shared address suggestions

Implemented on bayanready-household-map-simplify. The same location-picker component and JavaScript serve incident create/admin correction, household add/edit, evacuation center add/edit, and relief-location add/edit. No schema, routing or model changes are needed for address persistence.

## Behavior

- Clicking or dragging a pin, or explicitly choosing current location, updates coordinates and requests a readable address. The point is never replaced by the provider's nearest-feature coordinates.
- Saved address and pin are displayed without a lookup on page load. Typed coordinates remain local and require the user to confirm the written address.
- A deliberate new point selection may replace the previous address with the suggestion. Manual input after selection, including while device location is pending, is preserved.
- New selections, clearing, manual coordinate changes, and submission cancel pending updates. Older responses cannot overwrite later values.
- Results use input.value and status.textContent. No provider text is inserted as HTML. Only returned address components are used; no barangay or house number is invented.
- Failed, empty, overly long or malformed results retain text and coordinates. Manual entry remains available when map files, tiles, permission or lookup fail.

## Provider review — 2026-11-13

Photon public demo API: https://photon.komoot.io/reverse
Terms: https://photon.komoot.io/ (Terms of Use)
Maintainer policy: https://github.com/komoot/photon#demo-server
API: https://github.com/komoot/photon/blob/master/docs/api-v1.md
OSM attribution/license: https://www.openstreetmap.org/copyright

Photon permits reasonable project usage; extensive use can be throttled or banned and availability is not guaranteed. It publishes no fixed numerical quota. Our demo limits are 700 ms debounce, at least two seconds between request starts, ten lookups per minute per picker, an eight-second timeout, and in-memory reuse for repeated points. These are application choices, not provider guarantees or a global multi-user quota. Reassess usage before a larger deployment.

The endpoint needs no private key. Live testing of the public Atok demo center returned HTTP 200 and Access-Control-Allow-Origin: * with a browser Origin header, so a PHP proxy is unnecessary for this demo. No user location was used in that provider check.

The component displays Photon and OpenStreetMap attribution and explains that selecting a point sends coordinates to Photon. Requests omit application cookies and referrer. The provider necessarily receives selected coordinates and network information. Confirm suitability with the stakeholder before real resident use; this implementation does not establish deployment approval.

`address_lookup_url` in config/config.php can override the Photon-compatible endpoint; set it to an empty string to disable suggestions. Older local configs default to Photon so the feature works without a new setup step. Config example documents the setting. No persistent lookup logs or database cache were introduced.

## Validation and issue record

- 23 mocked JavaScript checks pass: load/edit preservation; debounce; request privacy; autofill; fixed coordinates; cache; late manual edits; out-of-order results; clearing; failure; submit cancellation; permission denial; geolocation without Leaflet; typing during geolocation; no result; timeout; request cap; safe text; disabled endpoint; HTTP and JSON failures; manual coordinates.
- Existing picker regression checks pass with and without Leaflet, covering saved pins, dragging, invalid pairs, zero coordinates, permission errors and fallback.
- Actual PHP HTTP forms and an isolated fictional MySQL database passed create, edit and reload checks for all four record types. Both confirmed text and coordinates survived reload; resident access to staff forms remained blocked. The temporary site and database were removed afterward.
- The initial persistence test expected the wrong logout label. Corrected the test to the existing “Log out” label and reran successfully; no application login defect was found.
- PHP and JavaScript syntax checks and diff whitespace checks pass.
- Visual browser review is still part of integrated acceptance. These checks do not claim a visual browser pass.

Commands: `node tests/household_map_address.js`, `node tests/revision4_picker.js`, `python3 tests/household_map_persistence.py` (local MAMP prerequisites documented in the test).

The separate MAMP website checkout has not been synchronized by this phase. Application edits remain in the project worktree until explicitly synchronized/committed.
