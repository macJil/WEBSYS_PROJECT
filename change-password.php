<?php
require_once __DIR__ . '/includes/helpers.php';
requireLogin();
if (empty($_SESSION['CHANGE_PASSWORD'])) {
    redirect(dashboardPath());
}
$errors = [];
if (isPost()) {
    $password = input('password', false);
    if (strlen($password) < 8 || strlen($password) > 72 || strpos($password, "\0") !== false) {
        $errors[] = 'Use a password of 8–72 bytes.';
    } elseif ($password !== input('confirmation', false)) {
        $errors[] = 'The passwords do not match.';
    } else {
        $stmt = db()->prepare('SELECT password FROM users WHERE id=? AND is_active=1 AND must_change_password=1');
        $stmt->execute([$_SESSION['ID']]);
        $oldHash = $stmt->fetchColumn();
        if (!$oldHash || !password_verify(input('temporary', false), $oldHash)) {
            $errors[] = 'Check your temporary password.';
        } elseif (password_verify($password, $oldHash)) {
            $errors[] = 'Choose a different password.';
        } else {
            $stmt = db()->prepare('UPDATE users SET password=?, must_change_password=0 WHERE id=? AND password=? AND must_change_password=1 AND is_active=1');
            $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $_SESSION['ID'], $oldHash]);
            if ($stmt->rowCount() === 1) {
                session_regenerate_id(true);
                unset($_SESSION['CHANGE_PASSWORD']);
                redirect(dashboardPath());
            }
            $errors[] = 'Your credentials have changed. Log out and sign in again.';
        }
    }
}
$pageTitle = 'Choose your password';
require __DIR__ . '/includes/header.php';
?>
<h1>Choose your password</h1>
<?php require __DIR__ . '/includes/errors.php'; ?>
<form method="post" class="panel">
    <label class="form-label" for="temporary">Temporary password</label>
    <input class="form-control mb-3" type="password" id="temporary" name="temporary" autocomplete="current-password" required>
    <label class="form-label" for="password">New password</label>
    <input class="form-control mb-3" type="password" id="password" name="password" autocomplete="new-password" minlength="8" required>
    <label class="form-label" for="confirmation">Confirm new password</label>
    <input class="form-control mb-3" type="password" id="confirmation" name="confirmation" autocomplete="new-password" required>
    <button class="btn btn-primary">Save password</button>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
