# Revision 4 — Reusable location picker

One shared PHP fragment and JavaScript picker now serve resident/admin incidents, staff households, centers, and relief-location forms. Written descriptions remain on each form. Coordinate input, map click, marker dragging, clear pin, and opt-in device location share the same fields. Permission denial, timeout, unavailable API, poor accuracy, and unavailable tiles leave manual inputs usable. Device location requires permission and a secure context such as HTTPS/localhost. Returned accuracy is displayed; users must confirm the point before saving. Late callbacks cannot overwrite a later manual selection or cleared pin.

Household and center models now save their own coordinate pair. The relief inventory links to a new simple relief-location list/add/edit page. It supports storage/distribution/both; changing to storage clears public visibility. This editor does not publish locations. Staff household, center, and relief details reuse the saved-point viewer alongside the address. Hazard classifications are not changed by picker events.

No migration is required beyond Revision 2's migration 004. Existing saved coordinates prefill on edit. Missing coordinates remain null. Server validation checks coordinate pairs, finite ranges, and a written description when a point is supplied; incidents retain their existing required location validation.

Checks: PHP syntax across app/views, JavaScript syntax, picker-script tests with and without a map library (opt-in, denial, low accuracy, stale callbacks, manual input and map click), and existing incident-model save/retrieve tests passed. Live browser geolocation and MySQL end-to-end save checks for all four forms remain pending; this checkpoint is not fully runtime-verified yet.
