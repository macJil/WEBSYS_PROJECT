# Phase 3 — Shared PHP foundations

This branch retains root index.php and direct PHP view URLs. There is no route layer or public folder. Ordinary POST forms are used. Authentication uses PHP sessions, whose standard browser session cookie is necessary; there are no custom cookie features. CSRF tokens remain outside the agreed classroom scope.

## Local setup

1. Use PHP 8+ with pdo_mysql and MySQL compatible with the Phase 2 schema.
2. Copy config/config.example.php to config/config.php. Set host, port, database, username, password, and base_url for your computer. config.php is ignored by Git. The committed defaults contain no database password.
3. Import the fresh schema, or follow the Phase 2 upgrade instructions for an existing database.
4. Open root index.php. When using PHP's local server at the project root, base_url is an empty string; under MAMP's WEBSYS_PROJECT directory it is /WEBSYS_PROJECT.
5. Register a fictional account. For the initial admin, the developer can promote that specific account in their local database: `UPDATE users SET role = 'admin' WHERE username = 'your_chosen_username';`. Verify the username first, then log out and log in again. Public registration always creates residents even if a request includes a different role.

The database connection is created once per request and shared by models. db() returns PDO or throws PDOException. It never returns markup. Login/registration construct the model inside their error boundary, log details privately, and show a general database-unavailable message. An exception handler provides a controlled fallback for existing module pages.

## Shared request pattern

A direct protected page begins with helpers and requireRole before any controller work or output. requireRole validates the session/role and processes logout POSTs before HTML. Controllers handle input, models query the database, and components render escaped output.

```php
<?php
require_once __DIR__ . '/../../../app/helpers.php';
requireRole('admin');
require_once __DIR__ . '/../../../app/controllers/YourController.php';
$controller = new YourController();
$errors = $controller->handlePost(); // [] on GET; validates explicit actions on POST
$pageTitle = 'Your module';
require __DIR__ . '/../components/header.php';
require __DIR__ . '/../components/errors.php';
?>
<form method="post" action="">
    <input type="hidden" name="action" value="create_record">
    <label for="name">Name</label>
    <input id="name" name="name" value="<?= e(input('name')) ?>" required>
    <button type="submit">Save</button>
</form>
<?php require __DIR__ . '/../components/footer.php'; ?>
```

This is an illustration for a future controller, not an existing module to include verbatim. UserController::handlePost() is the runnable example. A successful mutation calls Controller::success(message, localPath): it stores a plain-text session message and redirects with HTTP 303. Reloading the next page does not repeat the POST. Validation errors stay on the form as an array, preserving ordinary fields but never passwords. Reject unknown actions; do not dispatch method names supplied by the browser.

- `input()` accepts only string fields and trims them by default; use input('password', false) for passwords. Check array/multi-select input separately.
- `e()` escapes text/attribute output. Use it for database and request values. Never treat untrusted text as HTML.
- `url()` builds links using configured base_url; `redirect()` accepts an application path chosen by the code, not an arbitrary user URL.
- `flash()` stores plain text and a success/danger type. messages.php consumes it once.
- Views contain no SQL; models contain no HTML. Existing report/announcement methods retain compatibility until their module phases.
- Resident report lists now query reported_by using the session account ID; resident announcement lists retrieve only published records.

## Account behavior

Registration validates field lengths, email, username characters, contact format, password length (8–72 bytes), and matching confirmation. PDO prepared statements save a password_hash() value and an explicit resident role. Login uses password_verify(), rejects inactive/unsupported roles, regenerates the session ID, and redirects to the matching dashboard. Session values preserve the existing uppercase keys used by current views. Logout is POST, clears session data, destroys the session, and redirects to login.

Role changes/deactivation apply at the next login in this foundation. Immediate revocation of existing sessions is not implemented. Shared header/navigation render only appropriate links; each protected direct page independently checks access before processing a request. The map page remains a placeholder for its later phase.

## Verification performed

- PHP syntax checks passed across all current PHP files.
- HTTP checks passed for login/registration rendering, escaped invalid-field redisplay, controlled unavailable-database response without SQL error disclosure, and anonymous redirects on protected direct pages.
- With an isolated SQLite PDO fixture, HTTP checks passed for resident-only registration despite a submitted admin role, stored password hash, success flash consumed once, session-ID rotation, resident/admin dashboard redirects, wrong-role GET/POST denial, logout, and inactive-account login rejection.
- The fixture changes no repository schema or runtime database configuration and is not part of the application. These checks do not prove MySQL compatibility: the isolated MySQL binary crashed during initialization, so a real MySQL import/login check remains outstanding.
- Existing incident and announcement CRUD workflows have not been fully accepted in this phase. Their module-specific validation, response audit transactions, and upload behavior remain later-phase work.
