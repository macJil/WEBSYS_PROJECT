<?php
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/config/db.php";
if (!empty($_SESSION["ID"])) {
    redirect(dashboardPath());
}
function handlePost()
{
    if (!isPost()) {
        return [];
    }
    $action = input('action');
    if ($action !== 'login') {
        return ['Invalid form action.'];
    }
    $email = input('email');
    $password = input('password', false);
    $errors = [];
    if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if ($password === '') {
        $errors[] = 'Enter your password.';
    }
    if (strlen($password) > 72) {
        $errors[] = 'Use a password of at most 72 bytes.';
    }
    if ($errors) {
        return $errors;
    }
    try {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (
            !$user ||
            !password_verify($password, $user['password']) ||
            !$user['is_active'] ||
            !in_array($user['role'], ['admin', 'resident'], true)
        ) {
            return ['Invalid email or password, or the account is inactive.'];
        }
        session_regenerate_id(true);
        $_SESSION = [
            'ID' => (int) $user['id'],
            'FULL_NAME' => $user['full_name'],
            'USERNAME' => $user['username'],
            'PHONE' => $user['phone'] ?? '',
            'EMAIL' => $user['email'],
            'ROLE' => $user['role'],
            'CHANGE_PASSWORD' => (bool) $user['must_change_password'],
        ];
        flash('You are now signed in.');
        redirect(dashboardPath());
    } catch (PDOException $error) {
        return ['The database is unavailable. Please try again later.'];
    }
}

$errors = handlePost();
$pageTitle = "Login";
require __DIR__ . "/includes/header.php";
?>
<div class="page-intro">
    <p class="eyebrow">Community preparedness &amp; response</p>
    <h1><?= e($settings['app_name']) ?></h1>
    <p>Report incidents, follow community notices, and coordinate help.</p>
</div>
<?php
require __DIR__ . "/includes/errors.php";
?>
<div class="row g-4">
    <section class="col-lg-5">
        <div class="panel h-100">
            <h2>Login</h2>
            <p>For a resident account, contact your barangay official.</p>
            <form method="post" action="<?= e(url('index.php')) ?>">
                <input type="hidden" name="action" value="login" />
                <label for="login-email" class="form-label">Email</label>
                <input id="login-email" class="form-control mb-3" type="email" name="email" required maxlength="254" autocomplete="email" value="<?= input('action') === 'login' ? e(input('email')) : '' ?>" />
                <label for="login-password" class="form-label">Password</label>
                <input id="login-password" class="form-control mb-3" type="password" name="password" required autocomplete="current-password" />
                <button class="btn btn-primary">Login</button>
            </form>
        </div>
    </section>
</div>
<?php
require __DIR__ . "/includes/footer.php";
?>
