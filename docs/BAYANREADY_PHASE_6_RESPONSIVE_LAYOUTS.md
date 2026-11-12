# Phase 6 — Responsive module layouts

Completed 2026-11-13 on `bayanready-map-responsive-frontend`.

## Shared patterns

- Bootstrap's existing collapse behavior controls a Menu button below 992px. Desktop navigation stays expanded. A noscript fallback keeps links available without JavaScript.
- Shared CSS stacks filter labels and form controls on narrow phones, wraps long text and buttons, and keeps primary buttons at least 44px high.
- All module tables have a focusable, labelled horizontal-scroll region. Columns retain readable widths; distribution quantity inputs remain usable. Actions outside tables remain within the phone width.
- Map legends remain above the map on phones, with larger layer targets and stacked location-picker buttons. Explicit map heights remain intact.
- Print styles remove scrolling constraints from summary tables.
- Ordinary form submissions, controllers, models, and role guards are unchanged.

## Issue found and fixed

The shared navigation reused `$label`, overwriting the record-name field used by Manage records. Selected households could appear as “Unnamed” during review. Navigation now uses `$navLabel` and `$navPath`; a focused PHP include check confirms the original record label survives.

## Verification

Browser checks used an isolated fictional MySQL fixture, before the temporary environment was cleared between sessions. The same layout changes were restored into this persistent checkout and automated checks rerun.

- Phone width 390px: household list/details, incident list/create/details, response teams/incident, evacuation centers/details, relief inventory/distribution/movements/locations, announcements, disaster summaries, record actions, and shared map had no page-wide overflow or clipped primary buttons. All rendered tables were wrapped.
- Desktop width 1280px: the same pages plus announcement editing had no page-wide overflow.
- Resident dashboard, incident create/list/details, announcements, public center list, and map checked at both widths without page-wide overflow.
- Mobile Menu expanded using Bootstrap. Record selection reached its review step without applying a destructive action.
- Staff map loaded six fictional markers and map tiles; its legend was above the map. Resident direct access to Manage records was denied.
- PHP view lint, map rendering tests, picker tests with/without Leaflet, 25 address-suggestion checks, public map SQL checks, and eight dashboard checks passed after restoration.

These are responsive-layout checks, not a new full operational regression run. External map tiles still need internet. Actual phone hardware and every browser engine were not tested.
