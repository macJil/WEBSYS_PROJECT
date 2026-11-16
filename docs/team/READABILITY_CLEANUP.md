# Readable-code branch

16 November 2026. Branch: `bayanready-readable-code`.

The team-maintained PHP controllers/models/helpers, direct view pages and custom JavaScript have been reviewed for readable formatting. Existing readable CSS was retained. Bootstrap and Leaflet vendor code, local configuration, schema and historical migrations were not rewritten.

## Style

Use four spaces for each nesting level, including PHP control blocks embedded in HTML. A select inside a div uses eight or more spaces where its nesting requires it; foreach and its option use their own additional levels. Keep a simple label or option on one line. Separate foreach/if opening and closing tags from surrounding markup. Expand long arrays and calls over readable lines. Do not indent textarea content merely for appearance: whitespace there can become an actual input value.

The household form now defines householdFields, householdCategories and householdNotes above the markup rather than placing long literal arrays inside foreach tags. Controllers/models retain ordinary classes, PDO, role checks, validation and transaction behavior. This is a readability cleanup, not a removal of required features or production safeguards.

## Verification

- PHP 8.3 syntax checks passed for application PHP, root entry and configuration examples.
- Full fictional MySQL/HTTP suite passed: record creation/edit/reload, dashboard totals, role guards, incident ownership, map field/status rules, response saves, partial arrivals, duplicate/capacity rejection, insufficient-stock rollback, protected removal/history and actual PNG upload/retrieval/rejection.
- Action coverage passed for 35 rendered page states and 62 local destinations.
- Address suggestion JavaScript: 23 checks passed. Picker checks passed with and without Leaflet; shared map checks passed for admin and resident.
- PHP structure comparison was reviewed: formatting adds braces to single-statement bodies and removes redundant parentheses; the household option-array extraction is intentional.

The usual MAMP MySQL socket was unavailable. The same disposable test runner was executed against the working `/tmp/mysql.sock` by replacing the socket string in memory. No private application configuration or persistent test source was changed. The initial blocked test attempts were not counted as passes. No manual phone/desktop browser visual review was performed for this cleanup.

The detailed code-study excerpts and source companion are pre-formatting snapshots. Their source links now point to commit 954885b so line numbers remain meaningful. Open the actual files on this branch when showing the newly indented code. Method responsibilities and the member presentation assignments remain the same.
