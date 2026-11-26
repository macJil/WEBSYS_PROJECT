<?php
require_once __DIR__ . '/household_function.php';
$id = recordId($_GET['member'] ?? null);
$pdo = db();
$errors = [];
$temporary = null;
header('Cache-Control: no-store');
try {
    if (isPost()) {
        $pdo->beginTransaction();
    }
    $sql = "SELECT m.full_name, m.household_id, h.barangay
            FROM household_members m
            JOIN households h ON h.id=m.household_id
            JOIN users staff ON staff.id=?
            WHERE m.id=? AND staff.role='admin' AND staff.is_active=1
              AND m.archived_at IS NULL AND h.archived_at IS NULL";
    $stmt = $pdo->prepare($sql . (isPost() ? ' FOR UPDATE' : ''));
    $stmt->execute([$_SESSION['ID'], $id]);
    $member = $stmt->fetch();
    if (!$member || !in_array($member['barangay'], ATOK_BARANGAYS, true)) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(403);
        exit('Enrollment requires active staff and an active member of a household with a confirmed Atok barangay.');
    }
    if (isPost()) {
        $email = input('email');
        $phone = input('phone');
        if (input('confirmed') !== 'yes') {
            $errors[] = 'Confirm residency before creating an account.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            $errors[] = 'Enter a valid resident email.';
        }
        if ($phone !== '' && !validPhone($phone)) {
            $errors[] = 'Use 09 followed by 9 digits or +639 followed by 9 digits or leave it blank.';
        }
        if ($errors) {
            $pdo->rollBack();
        } else {
            $password = bin2hex(random_bytes(10));
            $stmt = $pdo->prepare("INSERT INTO users
                (full_name, username, email, phone, password, role, household_member_id, must_change_password)
                VALUES (?, ?, ?, ?, ?, 'resident', ?, 1)");
            $stmt->execute([$member['full_name'], 'res_' . bin2hex(random_bytes(8)), $email, $phone, password_hash($password, PASSWORD_DEFAULT), $id]);
            $pdo->commit();
            $temporary = $password;
        }
    }
} catch (PDOException $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($error->getCode() !== '23000') {
        throw $error;
    }
    $errors[] = 'This member or email already has an account.';
}
$pageTitle = 'Create resident account';
require __DIR__ . '/../includes/header.php';
?>
<h1>Create resident account</h1>
<p><?= e($member['full_name']) ?> · <?= e($member['barangay']) ?></p>
<?php require __DIR__ . '/../includes/errors.php'; ?>
<?php if ($temporary !== null): ?>
    <p>Give this temporary password privately to the resident. It is shown once.</p>
    <p><strong><?= e($temporary) ?></strong></p>
<?php else: ?>
    <form method="post" class="panel">
        <label class="form-label" for="email">Resident email for login</label>
        <input class="form-control mb-3" id="email" type="email" name="email" maxlength="254" required value="<?= e(input('email')) ?>">
        <label class="form-label" for="phone">Resident contact number (optional)</label>
        <input class="form-control mb-3" id="phone" type="tel" name="phone" maxlength="13" value="<?= e(input('phone')) ?>">
        <label><input type="checkbox" name="confirmed" value="yes" required> I confirmed this member resides in this barangay.</label>
        <button class="btn btn-primary">Create resident account</button>
    </form>
<?php endif; ?>
<a class="btn btn-outline-secondary" href="<?= e(url('households/details.php?id=' . $member['household_id'])) ?>">Return to household</a>
<?php require __DIR__ . '/../includes/footer.php'; ?>
