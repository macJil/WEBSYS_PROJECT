# BayanReady Phase 5 — shared visual system and map rendering

Completed 12 November 2026 on `bayanready-map-responsive-frontend`.

## Shared interface

`resources/assets/css/bayanready.css` is the single active custom stylesheet, layered after bundled Bootstrap. It defines the teal/navy palette, system-font typography, spacing, panels, dashboard cards, form controls, text status badges, keyboard focus, and map heights. The previous map-only stylesheet is consolidated into it. Authored inline presentation styles in views were replaced with shared classes.

The shared header now has a consistent brand, account area and active navigation link. Navigation wraps into two columns on phones and remains usable without JavaScript. Login/registration use panels, dashboards use matching summary cards, and notices have a consistent readable layout. Status badges retain written status labels. A skip-to-content link and visible focus outlines support keyboard use. Content and ordinary forms remain server-rendered; maps still require JavaScript. No new framework or build step was added.

## Empty-map investigation and fixes

1. The user's MAMP checkout had ignored `config/local.php` overrides still set to latitude 12.8797, longitude 121.7740 and zoom 6. These superseded Irisan defaults. Only those three settings were corrected to 16.41792, 120.56091 and zoom 15. Database settings and records were not changed.
2. Map pages previously depended on remote Leaflet CSS/JavaScript. The same version, **1.9.4**, is now bundled locally with marker images and its license. All shared pickers, saved-location views and the community map use these local assets. This removes a possible CDN-loading failure; it does not establish that the CDN caused the user's reported symptom.
3. Explicit shared CSS heights cover all map containers. Missing-library messages are now visible in pickers and saved-location views. The community map distinguishes no active points from no approved public points. Tile failures and data failures retain their separate messages. Basemap tiles still need internet.
4. The user's MAMP HTTP endpoint on localhost:8888 and its database connection were unreachable during checking (connection refused / SQLSTATE 2002). Opening the MAMP control window also timed out. The fixed application was verified on an isolated fictional site; a live MAMP check remains dependent on its servers being available. No real records were inspected or republished.

The team commit `c17812e` deleting the legacy controller checker was fast-forwarded into the working branch before these changes, preserving that work.

## Verification

A new isolated `bayanready_phase5_fixture` database contained fictional users, two incidents, two centers, one household concern, one relief point and two published notices. One incident and one center were public; the other incident, closed center and household concern remained staff-only.

- Browser at desktop width: dashboard cards, navigation, and staff map rendered. The staff map had **six markers**, visible background tiles, a 468px map height, and a local Leaflet script URL.
- Browser at **390 × 844**: login/registration, resident dashboard, notices, community map and incident picker were reviewed. Community map/picker had no horizontal page overflow. Resident map showed **three approved markers** and no household layer. Picker click populated coordinates and its locally served marker image loaded successfully. The temporary viewport was reset.
- HTTP smoke checks: **19 admin and 15 resident linked pages** loaded with the shared stylesheet; map projections retained role filtering. CSS, Leaflet JavaScript/CSS and marker image URLs returned HTTP 200.
- Existing map-query/publication, shared-map JavaScript, shared-picker, 25 address-suggestion behavior checks, and dashboard regression suites passed. Changed PHP files passed syntax checks; no authored inline styles or unpkg runtime references remain in views; git diff checks passed.
- No database migration is required. This phase does not change publication policy or automatically make private reports visible. The previously documented staff publication-editor gap remains separate work.

## Local use

The development branch contains the UI and local map assets. The MAMP checkout is updated by fast-forward only after a clean-state check. Its ignored local map settings must retain the Irisan values above. Start the local web/database servers and reload the page to use the updated assets. With no eligible saved points, the basemap should still render with an explanatory message. Offline tile availability is not promised.
