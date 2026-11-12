# Phase 3 — Single local configuration

The application now reads one returned array from ignored `config/config.php`. The committed `config/config.example.php` contains all expected keys. `settings.php` and `local.example.php` have been removed; example defaults are no longer merged automatically at runtime.

## Fresh teammate setup

1. Copy `config/config.example.php` to `config/config.php`.
2. Edit the host, port, database, username, password, base URL, and private upload folder for your machine. Keep all other array keys.
3. Import the development schema according to the existing setup instructions and open root `index.php`.

For an existing checkout, copy the new example, then transfer your values from ignored `config/local.php` into the matching keys. Preserve your existing credentials, upload path, and map preferences. The old local file is no longer read, but remains ignored so it cannot accidentally enter Git. Do not commit either local configuration file.

Missing config returns a controlled setup message with HTTP 503, both for page initialization and direct database initialization. The database connection class is otherwise unchanged for the next phase.

## Checks

`python3 tests/simplification_config.py` creates a clean temporary checkout without credentials, checks both missing-config paths, copies only the example, verifies the returned array, renders login/registration, and confirms Git ignores the local config. It does not connect to MySQL or create real accounts. Existing public-map SQL and dashboard tests passed; changed PHP files passed lint. Full MySQL account workflows are deferred to the database-access phase.
