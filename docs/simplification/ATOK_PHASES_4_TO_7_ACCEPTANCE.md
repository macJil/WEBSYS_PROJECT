# Atok simplification review and acceptance

Date: 2026-11-13. Branch: `bayanready-atok-simple`. Tests used a guarded, disposable MySQL database on loopback port 18960, fictional accounts/records, private temporary image storage, and a localhost PHP server. No real resident data was used.

## Code and schema decisions

- The publication page, controller, model, and `is_public` columns were removed in the preceding commit. Fresh schema has no visibility columns. Migration `007_automatic_map.sql` is the upgrade step after 005 and 006.
- The shared helpers all have callers. `input()` needs its trim option because password inputs must preserve spaces. Model PDO constructor options are used by isolated tests. No helper or parameter was removed merely to shorten code at the cost of behavior.
- The eight `preg_match()` uses were reviewed. They validate dates, contact numbers, usernames, or stored image names; replacing them with simpler expressions would change accepted input or require more code. They remain.
- `occurred_at` and `uploaded_at` are not currently shown in forms, but may hold historical values in existing databases. Generated active-stay keys enforce duplicate check-in rules. These columns were retained to avoid losing history or protections. No further table removal was justified.
- Announcement edit is a real form, not a duplicate redirect page. Bulk record actions remain the current guarded delete/archive path.

## Checks

| Check | Result |
| --- | --- |
| Core HTTP suite on fresh fictional schema | 200 passed |
| Automatic map suite on same schema | 26 passed |
| Summary, incident-location, and location-picker standalone checks | Passed |
| Literal PHP page references | 124 checked; 0 missing |
| PHP syntax, map JavaScript syntax, and whitespace | Passed |
| Migration 007 on a separate disposable copy of the preceding schema | Applied; 0 `is_public` columns remained |
| Resident browser navigation at 390 and 1280 px | 5 destinations opened; no page-wide overflow |
| Admin browser navigation at 390 and 1280 px | 10 destinations opened; no page-wide overflow |

The core suite exercises public registration as resident, login sessions, anonymous and wrong-role GET/POST denial, household/member CRUD, incident submission and images, response team/action history, evacuation check-in/out and capacity, stock movements and multi-good distribution rollback, announcement draft/publication, summaries, foreign keys, and navigation links. The map suite checks automatic visibility by status/type for both roles and confirms household and private incident fields are absent.

## Defect log

| ID | Finding | Fix | Recheck |
| --- | --- | --- | --- |
| AS-01 | Resident dashboard and center list still called centers “approved” after manual map approval was removed. | Changed both labels to “Open centers.” | PHP syntax passed; both pages had already passed desktop/phone navigation and layout checks. The wording was checked by source scan after the fix. |

## Remaining decisions

Atok is a municipality demo focus. A final barangay, boundary, and real locations need stakeholder confirmation. Existing saved records were not moved. Before applying migration 007 to a database with real reports, review which eligible incidents have coordinates: they will appear automatically after the migration.
