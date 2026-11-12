<?php
require_once __DIR__ . '/app/controllers/UserController.php';
if (!empty($_SESSION['ID'])) {
    redirect(dashboardPath());
}
$errors = (new UserController())->handlePost();
$pageTitle = 'Login and registration';
require __DIR__ . '/resources/views/components/header.php';
?>
<div class="page-intro"><p class="eyebrow">Community preparedness &amp; response</p><h1><?= e($settings['app_name']) ?></h1><p>Report incidents, follow community notices, and coordinate help.</p>
</div>
<?php require __DIR__ . '/resources/views/components/errors.php'; ?>
<div class="row g-4">
<section class="col-lg-5">
<div class="panel h-100"><h2>Login</h2>
<form method="post" action="<?= e(url('index.php')) ?>">
<input type="hidden" name="action" value="login">
<label for="login-email" class="form-label">Email</label>
<input id="login-email" class="form-control mb-3" type="email" name="email" required maxlength="254" autocomplete="email" value="<?= input('action') === 'login' ? e(input('email')) : '' ?>">
<label for="login-password" class="form-label">Password</label>
<input id="login-password" class="form-control mb-3" type="password" name="password" required autocomplete="current-password">
<button class="btn btn-primary">Login</button>
</form>
</div></section>
<section class="col-lg-7">
<div class="panel h-100"><h2>Register as a resident</h2>
<form method="post" action="<?= e(url('index.php')) ?>">
<input type="hidden" name="action" value="register">
<?php foreach (['full_name' => ['Full name', 'text', 100], 'username' => ['Username', 'text', 50], 'email' => ['Email', 'email', 254], 'phone' => ['Contact number', 'tel', 20]] as $key => $field): ?>
<label for="register-<?= e($key) ?>" class="form-label"><?= e($field[0]) ?></label>
<input id="register-<?= e($key) ?>" class="form-control mb-3" name="<?= e($key) ?>" type="<?= e($field[1]) ?>" maxlength="<?= $field[2] ?>" required value="<?= input('action') === 'register' ? e(input($key)) : '' ?>">
<?php endforeach; ?>
<label for="register-password" class="form-label">Password (at least 8 characters)</label>
<input id="register-password" class="form-control mb-3" type="password" name="password" minlength="8" required autocomplete="new-password">
<label for="confirm-password" class="form-label">Confirm password</label>
<input id="confirm-password" class="form-control mb-3" type="password" name="password_confirmation" required autocomplete="new-password">
<button class="btn btn-success">Register</button>
</form>
</div></section>
</div>
<?php require __DIR__ . '/resources/views/components/footer.php'; ?>
