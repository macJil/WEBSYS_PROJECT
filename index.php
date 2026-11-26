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
    <p class="eyebrow">Municipality of Atok · Benguet</p>
    <h1><?= e($settings['app_name']) ?></h1>
    <?php if (!empty($settings['emergency_hotline'])): ?>
        <p>Emergency hotline: <strong><?= e($settings['emergency_hotline']) ?></strong></p>
    <?php endif; ?>
    <p>Incident reporting and evacuation information for Atok residents and staff.</p>
</div>
<?php
require __DIR__ . "/includes/errors.php";
?>
<div class="row g-4">
    <section class="col-lg-5">
        <div class="panel h-100">
            <h2>Login</h2>
            <form method="post" action="<?= e(url('index.php')) ?>">
                <input type="hidden" name="action" value="login" />
                <label for="login-email" class="form-label">Email</label>
                <input id="login-email" class="form-control mb-3" type="email" name="email" required maxlength="254" autocomplete="email" value="<?= input('action') === 'login' ? e(input('email')) : '' ?>" />
                <label for="login-password" class="form-label">Password</label>
                <input id="login-password" class="form-control mb-3" type="password" name="password" maxlength="72" required autocomplete="current-password" />
                <button class="btn btn-primary">Sign in</button>
            </form>
        </div>
    </section>
    <section class="col-lg-7" aria-labelledby="resident-guide">
        <div class="panel h-100">
            <h2 id="resident-guide">Resident guide</h2>
            <p>Sign in to report an incident, follow its status, read notices, or find an open evacuation center.</p>
            <ul>
                <li><strong>How to get an account</strong> · Ask MDRRM staff to verify your household record. Choose a password on your first sign-in.</li>
                <li><strong>Before you report an incident</strong> · Have your barangay, nearest landmark, phone number and a short description ready.</li>
                <li>You and MDRRM staff can open your full report. Eligible incident pins also appear on the community map.</li>
                <li>Forgot your password? Contact MDRRM staff for account assistance.</li>
            </ul>
            <?php if (!empty($settings['demo_logins'])): ?>
                <details><summary>Classroom demo logins</summary>
                    <p>Staff: staff@demo.example.invalid · AtokDemoStaff26!</p>
                    <p>Resident: resident@demo.example.invalid · AtokDemoResident26!</p>
                </details>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php
require __DIR__ . "/includes/footer.php";
?>
