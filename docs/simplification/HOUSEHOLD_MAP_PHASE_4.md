# Phase 4 — Closer Atok opening view

Reviewed 2026-11-14 (Asia/Manila), branch bayanready-household-map-simplify.

## Behavior

- Configuration example, worktree local config and running MAMP local config all use Atok, Benguet (DEMO), center 16.6272 / 120.7672, opening zoom 14 and minimum zoom 11.
- These Phase 4 edits were already present when this review began; they were preserved and verified. No private configuration values were copied into documentation.
- The shared map starts at the configured demo center even with no records. An empty picker also opens at zoom 14. A picker with saved coordinates opens around its saved pin at zoom 16.
- Show all locations enables all authorized layers and fits every returned marker. It temporarily permits zooming out to 1 so a saved point outside the opening area remains reachable. It does not fetch additional private records or change saved coordinates.
- Return to demo area restores the opening center, zoom 14 and minimum zoom 11. An empty Show all locations request leaves the current view unchanged and explains that no points exist.
- Panning remains enabled; no boundary polygon or pan restriction is introduced.

## Files

config/config.example.php; local ignored config/config.php in both checkouts; resources/assets/js/community-map.js; resources/views/maps/map.php; tests/revision3_map.js. Existing location-picker.js handles saved-pin centering.

## Verification

- Shared-map regression suite passed for admin and resident: opening zoom/center, layer toggles, safe popups, all-layers fit, distant points, reset and empty-state behavior.
- Picker regression suite passed with and without Leaflet: saved point, manual coordinates, click/drag, invalid pair and geolocation fallback.
- Browser checked at 1366 × 900 and 390 × 844 using the actual copied views/assets with explicitly fictional data responses, without touching the application database.
- Desktop empty map displayed Atok tiles at zoom 14. Phone empty resident map displayed Atok at zoom 14. No page horizontal overflow.
- Two fictional centers (one near Atok and one deliberately outside the opening view) both fit inside the map after Show all locations, at zoom 8. Return restored the Atok opening view.
- Phone legend and controls remained readable above the map, without covering the map. The map and controls were reachable by scrolling.
- Empty picker loaded zoom-14 tiles; saved picker loaded zoom-16 tiles and retained 16.6500000 / 120.8000000. No address lookup was triggered just by opening either picker.
- Running MAMP config already contained the required values. The running copy was missing the reset status message; that single line was synchronized. Its map script and view now match the worktree Phase 4 files. Earlier phase files were not bulk-synchronized.
- No schema or saved-record edits were performed. The temporary preview server was stopped after review.

Screenshots are in the workspace artifacts/phase4 folder, outside application source. Runtime browser viewport overrides were reset.
