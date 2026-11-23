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
    if (!in_array($action, ['login', 'register'], true)) {
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
    if ($action === 'register') {
        $data = [
            'full_name' => input('full_name'),
            'username' => input('username'),
            'email' => $email,
            'phone' => input('phone'),
        ];
        if ($data['full_name'] === '' || strlen($data['full_name']) > 100) {
            $errors[] = 'Enter a name of up to 100 bytes.';
        }
        $usernameLength = strlen($data['username']);
        if (
            $usernameLength < 3 ||
            $usernameLength > 50 ||
            strspn(
                $data['username'],
                'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_',
            ) !== $usernameLength
        ) {
            $errors[] = 'Use 3–50 letters, numbers or underscores for your username.';
        }
        if (!validPhone($data['phone'], 7)) {
            $errors[] = 'Enter a contact number of 7–20 characters.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Use a password of at least 8 characters.';
        }
        if ($password !== input('password_confirmation', false)) {
            $errors[] = 'The passwords do not match.';
        }
    }
    if ($errors) {
        return $errors;
    }
    try {
        if ($action === 'register') {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare(
                "INSERT INTO users (full_name, username, email, phone, password, role) VALUES (?, ?, ?, ?, ?, 'resident')",
            );
            $stmt->execute([
                $data['full_name'],
                $data['username'],
                $data['email'],
                $data['phone'],
                $data['password'],
            ]);
            flash('Registration successful. You can now log in.');
            redirect('index.php');
        }
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
        ];
        flash('You are now signed in.');
        redirect(dashboardPath());
    } catch (PDOException $error) {
        if ($action === 'register' && $error->getCode() === '23000') {
            return ['That email address or username is already registered.'];
        }
        return ['The database is unavailable. Please try again later.'];
    }
}

$errors = handlePost();
$pageTitle = "Login and registration";
require __DIR__ . "/includes/header.php";
?>
<div class="page-intro">
    <p class="eyebrow">Community preparedness &amp; response</p>
    <h1><?= e(APP_NAME) ?></h1>
    <p>Report incidents, follow community notices, and coordinate help.</p>
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
                <input id="login-password" class="form-control mb-3" type="password" name="password" required autocomplete="current-password" />
                <button class="btn btn-primary">Login</button>
            </form>
        </div>
    </section>
    <section class="col-lg-7">
        <div class="panel h-100">
            <h2>Register as a resident</h2>
            <form method="post" action="<?= e(url('index.php')) ?>">
                <input type="hidden" name="action" value="register" />
                <?php foreach (['full_name' => ['Full name', 'text', 100], 'username' => ['Username', 'text', 50], 'email' => ['Email', 'email', 254], 'phone' => ['Contact number', 'tel', 20]] as $key => $field): ?>
                    <label for="register-<?= e($key) ?>" class="form-label"><?= e($field[0]) ?></label>
                    <input id="register-<?= e($key) ?>" class="form-control mb-3" name="<?= e($key) ?>" type="<?= e($field[1]) ?>" maxlength="<?= $field[2] ?>" required value="<?= input('action') === 'register' ? e(input($key)) : '' ?>" />
                <?php endforeach; ?>
                <label for="register-password" class="form-label">Password (at least 8 characters)</label>
                <input id="register-password" class="form-control mb-3" type="password" name="password" minlength="8" required autocomplete="new-password" />
                <label for="confirm-password" class="form-label">Confirm password</label>
                <input id="confirm-password" class="form-control mb-3" type="password" name="password_confirmation" required autocomplete="new-password" />
                <button class="btn btn-primary">Register</button>
            </form>
        </div>
    </section>
</div>
<?php
require __DIR__ . "/includes/footer.php";
?>
