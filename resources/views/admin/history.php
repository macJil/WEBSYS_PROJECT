<?php
require_once __DIR__ . '/../../../app/controllers/HistoryController.php';
$errors = (new HistoryController())->hide();
$pageTitle = 'History action';
require __DIR__ . '/../components/header.php';
require __DIR__ . '/../components/errors.php';
?>
<a class="btn btn-outline-secondary" href="<?= e(url('dashboard/staff.php')) ?>">Return to dashboard</a>
<?php require __DIR__ . '/../components/footer.php'; ?>
