<?php
require_once __DIR__ . "/app/controllers/UserController.php";
if (!empty($_SESSION["ID"])) {
    redirect(dashboardPath());
}
$errors = (new UserController())->handlePost();
$action = input('action');
$loginEmail = $action === 'login' ? input('email') : '';
$registration = [];
if ($action === 'register') {
    $registration['full_name'] = input('full_name');
    $registration['username'] = input('username');
    $registration['email'] = input('email');
    $registration['phone'] = input('phone');
}
$pageTitle = "Login and registration";
require __DIR__ . "/resources/views/components/header.php";
?>
<div class="page-intro">
    <p class="eyebrow">Community preparedness &amp; response</p>
    <h1><?= e(APP_NAME) ?></h1>
    <p>Report incidents, follow community notices, and coordinate help.</p>
</div>
<?php
require __DIR__ . "/resources/views/components/errors.php";
?>
<div class="row g-4">
    <section class="col-lg-5">
        <div class="panel h-100">
            <h2>Login</h2>
            <form method="post" action="<?= e(url('index.php')) ?>">
                <input type="hidden" name="action" value="login" />
                <label for="login-email" class="form-label">Email</label>
                <input
                    id="login-email"
                    class="form-control mb-3"
                    type="email"
                    name="email"
                    required
                    maxlength="254"
                    autocomplete="email"
                    value="<?= e($loginEmail) ?>"
                />
                <label for="login-password" class="form-label">Password</label>
                <input
                    id="login-password"
                    class="form-control mb-3"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                />
                <button class="btn btn-primary">Login</button>
            </form>
        </div>
    </section>
    <section class="col-lg-7">
        <div class="panel h-100">
            <h2>Register as a resident</h2>
            <form method="post" action="<?= e(url('index.php')) ?>">
                <input type="hidden" name="action" value="register" />
                <label for="register-full_name" class="form-label">Full name</label>
                <input
                    id="register-full_name"
                    class="form-control mb-3"
                    name="full_name"
                    type="text"
                    maxlength="100"
                    required
                    value="<?= e($registration['full_name'] ?? '') ?>"
                />
                <label for="register-username" class="form-label">Username</label>
                <input
                    id="register-username"
                    class="form-control mb-3"
                    name="username"
                    type="text"
                    maxlength="50"
                    required
                    value="<?= e($registration['username'] ?? '') ?>"
                />
                <label for="register-email" class="form-label">Email</label>
                <input
                    id="register-email"
                    class="form-control mb-3"
                    name="email"
                    type="email"
                    maxlength="254"
                    required
                    value="<?= e($registration['email'] ?? '') ?>"
                />
                <label for="register-phone" class="form-label">Contact number</label>
                <input
                    id="register-phone"
                    class="form-control mb-3"
                    name="phone"
                    type="tel"
                    maxlength="20"
                    required
                    value="<?= e($registration['phone'] ?? '') ?>"
                />
                <label for="register-password" class="form-label">Password (at least 8 characters)</label>
                <input
                    id="register-password"
                    class="form-control mb-3"
                    type="password"
                    name="password"
                    minlength="8"
                    required
                    autocomplete="new-password"
                />
                <label for="confirm-password" class="form-label">Confirm password</label>
                <input
                    id="confirm-password"
                    class="form-control mb-3"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <button class="btn btn-primary">Register</button>
            </form>
        </div>
    </section>
</div>
<?php
require __DIR__ . "/resources/views/components/footer.php";
?>
