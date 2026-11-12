# BayanReady Phase 3 — address suggestions

Completed 12 November 2026 on `bayanready-map-responsive-frontend`.

## Provider decision and verified limits

Selected **Photon's public demo API**, operated by komoot, for light fictional classroom use. Its [published usage terms](https://github.com/komoot/photon#demo-server) permit reasonable project traffic, warn of throttling/banning for extensive use, and give no availability guarantee. There is no published numeric request quota in those terms. Do not describe our local limit as a provider entitlement. The [API documentation](https://github.com/komoot/photon/blob/master/docs/api-v1.md) describes reverse lookup and returned address properties.

Availability was checked with the public Irisan demo center: HTTP 200 and a readable locality result. An Origin-header check returned `Access-Control-Allow-Origin: *`, and an actual browser request succeeded. Photon needs no API key for this endpoint, so no PHP proxy or key storage was necessary. Displayed attribution links Photon and [OpenStreetMap contributors / ODbL](https://www.openstreetmap.org/copyright).

The public Nominatim service was considered but not selected. Its [policy](https://operations.osmfoundation.org/policies/nominatim/) imposes additional aggregate-traffic and use restrictions. No code in this phase calls that service.

## Behavior

The existing shared picker serves incident creation/correction, households, centers and relief locations. A small separate `address-suggestion.js` handles suggestions:

- Nothing is requested on initial page load, showing a saved pin, or turning suggestions back on. User map clicks, pin drags, typed coordinate changes and successful explicit current-location choices can trigger lookup.
- Wait 700 ms after selection settles; separate outgoing calls by at least two seconds; allow at most ten outgoing lookups per minute per open picker. This is a conservative client-side classroom limit, not an enforceable whole-website quota. Cache up to 30 labels only in page memory; closing/reloading clears that cache. No bulk lookup or retry loop.
- An eight-second timeout, AbortController and a generation counter handle failures and superseded responses. Clearing, disabling, choosing current location and submitting the form cancel pending suggestions. A failed lookup never clears coordinates or written text.
- An empty address field fills automatically. Existing saved text and manual edits are preserved. A visible candidate with **Use suggested address** lets the person explicitly replace their text. Manually clearing text is also respected. After accepting a suggestion, later point choices may update it until the person edits it again.
- Use only returned street/name, locality/district and city/town/village. Do not invent house numbers, add an Irisan suffix, or move coordinates to a provider's result point. Sparse responses can be locality-level; nearby suggestions are not address verification. The form says **Address suggested; please confirm**.
- Provider text is assigned through input.value and textContent, never raw HTML. Existing PHP validation, escaping, session guards and ordinary form saving remain in place.

`address_suggestions_enabled` and `address_lookup_url` are configuration settings in local.example.php. Set the enable flag to false in local.php to disable suggestions without affecting manual entry. Endpoint replacement must use a compatible Photon response format and HTTPS. Attribution/disclosure must be reviewed if changing providers. No schema change is needed.

## Privacy and real deployment gate

The browser sends the selected latitude and longitude to Photon after a user chooses a point. This also exposes the requester's network address to the provider. It sends no form names, phone numbers, incident descriptions, household IDs or address text. Fetch omits credentials and referrer. The checkbox/disclosure beside the picker explains the coordinate transfer and allows suggestions to be disabled. This concerns address lookups; the existing map tile requests are separate.

**Real resident use is not approved by this test.** Current fixtures are fictional and no real current-location request was made. Before deployment, the team and stakeholder must review provider terms/privacy, intended coordinate sensitivity, required notice/consent, traffic and reliability, then document the decision. Keep suggestions disabled for real household/emergency data until that review is accepted. If direct third-party coordinate transfer is unsuitable, retain manual entry or choose an approved service/self-hosted option. A proxy alone does not make coordinate transfer private. Do not rely on the demo endpoint for emergency response availability.

## Verification

- `node tests/bayanready_phase3_address.js`: **25 checks passed** for no initial lookup, debounce, request privacy options, autofill, unchanged selected point, cache reuse, manual edits during in-flight requests, explicit acceptance, cancellation/out-of-order responses, clearing, network/empty result fallback, opt-out/re-enable, invalid pairs, saved addresses, simulated geolocation denial/success without Leaflet, request cap, timeout, safe text insertion, and submission cancellation.
- Existing `revision4_picker.js` passes with and without Leaflet. Changed PHP syntax and diff checks pass.
- Actual browser, same isolated `bayanready_phase3_fixture` MySQL database: selected fictional center point **16.4179398, 120.5609179**; live Photon suggested **Irisan, District 16, Baguio**. Added “DEMO ONLY meeting point”, saved, reopened the edit page and confirmed both final address and exact selected coordinates remained. The edit page did not initiate a new address lookup.
- `tests/bayanready_phase3_persistence.py`: all **four module workflows passed** (incident, household, center, relief location), checking suggestion controls and final written text/coordinates in storage and reloaded details. These HTTP tests use the observed label as fictional input and do not repeatedly call the provider. Existing server-side coordinate validation is unchanged.
- Current-location permission/success/failure callbacks were simulated in automated tests; actual device location was not collected.

## Reproduce without real data

Use a copied checkout with empty base_url and the isolated MySQL connection from Phase 2, but database `bayanready_phase3_fixture` and PHP port **18968**. Import the fresh schema under that name, create the fictional marker table and seed the same two @example.invalid accounts. The HTTP test expects `/private/tmp/websys-rev6.sock` and refuses a nonempty incident fixture. Run the JavaScript test, then the persistence test. Live-provider/browser checks should remain small, deliberate and use only fictional public demo coordinates. Stop the temporary servers afterward.
