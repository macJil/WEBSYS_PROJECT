# Atok local display and helper cleanup

The MAMP checkout was already on `bayanready-atok-simple`, but its ignored `config/config.php` still contained the Irisan demo label and coordinates. Only the local demo label, map center, and zoom settings were changed to Atok. Database settings and credentials were left intact. Ignored local settings are not committed, so each teammate must update their own `config/config.php` or copy the current example.

The shared helper now starts the session directly and no longer sets `session.use_only_cookies`. `session.use_strict_mode`, role guards, password handling, PDO prepared statements, upload checks, and multi-record transactions remain. Phone, username, date, and stored image-name checks use simple string operations instead of `preg_match()`; accepted formats were preserved.

Verification used an isolated fictional MySQL database: 200 core HTTP checks and 26 automatic-map checks passed. The incident location and picker checks passed, as did PHP syntax and whitespace checks. No real records or coordinates were changed.
