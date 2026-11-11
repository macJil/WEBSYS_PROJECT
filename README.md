# Barangay DRRM — WEBSYS1

Official project repository: https://github.com/macJil/WEBSYS_PROJECT

A one-barangay classroom project using simple PHP MVC, PDO/MySQL, sessions, and direct PHP pages. Development is in progress.

## Project documents

- [Phase 1 baseline and five-member review assignments](docs/PHASE_1_PROJECT_BASELINE.md)
- [Phase 2 database installation, relationships, and implementation rules](docs/PHASE_2_DATABASE_SCHEMA.md)
- [Field dictionary](docs/PHASE_2_FIELD_DICTIONARY.md)
- [Fresh-install schema v1.1](barangay_drrm.sql)
- [One-time upgrade from the original v1.0 schema](database/migrations/001_phase_2.sql)
- [Read-only disaster summary query examples](database/report_queries.sql)

Read the Phase 2 installation instructions before importing SQL. Use fictional development data; do not commit real household records, contacts, uploaded images, or local credentials. The schema and application are separate deliverables: permissions, file handling, and transaction workflows still need PHP implementation.

## PHP foundation setup

See [Phase 3 setup and reusable page pattern](docs/PHASE_3_PHP_FOUNDATIONS.md). Copy `config/local.example.php` to the ignored `config/local.php`, configure your local connection/base URL, and open root `index.php`. The account screens use ordinary forms and PHP sessions.
