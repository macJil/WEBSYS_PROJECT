# Integrated acceptance — simplification branch

Date: 2026-11-13  
Branch: `bayanready-simplify-code`  
Fixture: separate MySQL instance on loopback port 18960, disposable `barangay_drrm` schema, `fictional-only` guard marker, fictional accounts and records, private temporary image storage. The fixture was reset only after confirming its marker and port. No real resident records or contacts were used.

## Results

| Check | Result |
| --- | --- |
| `tests/phase12_http.py` | 222 passed on the fresh fixture after the layout fix |
| `tests/revision7_http.py` | 29 passed |
| `tests/bayanready_phase7_http.py` | 47 passed |
| `tests/bayanready_phase7_counts.php` | 22 passed |
| Shared header PHP syntax and `git diff --check` after layout fix | Passed |

The 320 automated checks covered anonymous, resident, and admin access (including direct GET/POST attempts); registration and login; household registry; incident submission, images, review, publication, and withdrawal; response assignments and action history; evacuation stays and capacity; relief stock and multi-good distribution; announcements; summaries and dashboard counts; map privacy, saved points, and archiving/bulk-action history safeguards. The scripts use the same isolated database, so the later checks exercised records made by the earlier workflows.

Browser review used the isolated PHP server at `127.0.0.1:18961`. Anonymous access to the protected map redirected to login. A resident could see their dashboard, reports, incident form, announcements, centers, and community map. Admin navigation opened all ten primary destinations. At 390 px and 1280 px, every admin destination rendered its heading inside the main content area without page-wide horizontal overflow. Resident dashboard and incident list were rechecked at both widths after the fix; the remaining resident pages had no overflow on the initial check. The 390 px menu opened and exposed navigation links. The staff map loaded basemap tiles, four saved fixture markers, layer controls, and the visible legend at both widths.

## Issue log

| ID | Defect and cause | Fix | Recheck |
| --- | --- | --- | --- |
| IA-01 | Resident dashboard and incident list overflowed horizontally at phone and desktop widths. The shared header closed `<main>`, `<body>`, and `<html>` before module content was included, leaving it outside the Bootstrap container. | Keep `<main>` open in `resources/views/components/header.php`; the shared footer closes it after module content. | Browser DOM showed each page heading inside `<main>` and `document.documentElement.scrollWidth === innerWidth` at 390 px and 1280 px. All ten admin navigation destinations also had no page-wide overflow. PHP lint and whitespace check passed. |

## Limits and follow-up

- The full 320-check suite was rerun after IA-01 from a freshly reset, guarded fictional fixture. Logs from that rerun are temporary and are not committed.
- Browser review confirmed tile and marker rendering but did not measure every table cell or every form control on every possible device. Responsive tables are intentionally scrollable within their own region.
- Browser geolocation permission and denial branches were exercised by the automated location tests and earlier phase acceptance; this run did not request the computer's real location.
- Irisan remains a configurable demo focus without an approved boundary or stakeholder validation. Real location and contact data must wait for that decision.
