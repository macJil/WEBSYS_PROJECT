<?php
require_once __DIR__ . '/../../../../app/controllers/AnnouncementController.php';
$controller = new AnnouncementController('admin');
$announcement = $controller->find();
$errors = $controller->save((int)$announcement['id']);
$pageTitle = 'Edit announcement';
require __DIR__ . '/../../components/header.php';
require __DIR__ . '/../../components/errors.php';
?>
<h1>Edit announcement</h1>
<p>Current status: <strong><?= $announcement['is_published']?'Published':'Draft / withdrawn' ?></strong> · Created <?= e($announcement['created_at']) ?></p>
<p>Saving and withdrawing hides this notice from residents on their next page load. Saving while published updates the text residents see immediately.</p>
<?php require __DIR__ . '/editor.php'; ?>
<a class="d-block mt-3" href="<?= e(url('resources/views/admin/announcement/announcement.php')) ?>">Back to announcements</a>
<?php require __DIR__ . '/../../components/footer.php'; ?>
