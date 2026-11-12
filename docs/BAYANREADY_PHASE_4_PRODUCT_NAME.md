# BayanReady Phase 4 — visible product name

Completed 12 November 2026 on `bayanready-map-responsive-frontend`.

The product name is defined once as `app_name` in `config/config.example.php`. The shared navigation, login/registration heading, dashboard headings and footer use this escaped value. The shared browser-title format is `Page name | BayanReady`, preserving each page's useful context. The README heading now uses BayanReady as well.

The shared footer retains the configured Irisan demo label and explicitly says the area is a demo, with no confirmed stakeholder or approved boundary. Existing map disclaimers remain. PHP classes, database/table names and URL paths retain their existing names; no migration is needed.

Verification used the existing isolated fictional Phase 3 database through a separate copied checkout. A read-only HTTP smoke check followed internal page links for admin and resident accounts: **55 rendered pages** passed (40 admin, 14 resident, plus login/registration). Each page included BayanReady in its browser title, navigation and footer, retained the demo disclaimer, and contained no old product title. Both dashboard headings were checked. Source search found no `Barangay DRRM` or `SmartSlope` product title in application PHP/views/config (excluding local machine settings). PHP syntax and diff checks passed.

This phase changes visible naming only. Responsive layout work remains in the following frontend phases.
