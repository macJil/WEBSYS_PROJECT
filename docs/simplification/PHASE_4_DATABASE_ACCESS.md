# Phase 4 — Database access and static code

`config/db.php` now provides `db(): PDO`. It keeps one connection in the request's `$databaseConnection` variable, preserving shared transaction behavior without a static class or method. It reads the single local config on first connection, preserves PDO exception/fetch/prepared-statement options, and retains the controlled missing-config message.

All 13 models accept an optional PDO argument: `new MapModel($pdo)` uses that connection; ordinary pages use `new MapModel()` and obtain `db()`. Fictional tests now inject their SQLite/MySQL PDO directly instead of changing a private static property with reflection. CLI/HTTP suite setup code also uses `db()`.

The former static map validation method had only test callers. Its rules are now the ordinary `publicationErrors()` function in `app/map-validation.php`, shared by the tests and the publication model. Validation runs after the model locks the current record and checks the proposed safe public fields. Existing controller validation and operational status protections remain in effect.

Verification: the isolated marked MySQL suites passed 222 + 29 + 47 + 22 = 320 checks. Repeated `db()` calls returned the identical connection. Summary, coordinate, public-map, dashboard and fresh-config checks passed. PHP lint passed across application/config/tests. No database schema or vendor code changed.
