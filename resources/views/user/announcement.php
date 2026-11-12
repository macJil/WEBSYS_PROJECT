<?php
require_once __DIR__ . '/../../../app/controllers/AnnouncementController.php';
$controller=new AnnouncementController('resident');
if (isPost()) { http_response_code(405); exit('Announcements are read-only for residents.'); }
header('Cache-Control: private, no-store');
$announcements=$controller->listing(); $pageTitle='Emergency announcements';
require __DIR__ . '/../components/header.php';
?>
<h1>Emergency announcements</h1>
<form method="get" class="filter-form mb-4">
<label>Search title <input name="q" value="<?= e($controller->query('q')) ?>"></label>
<label>Created date <input type="date" name="date" value="<?= e($controller->query('date')) ?>"></label>
<button class="btn btn-secondary">Search</button> <a href="<?= e(url('resources/views/user/announcement.php')) ?>">Clear</a>
</form>
<?php if (!$announcements): ?><p>No published notices match your search.</p><?php endif; ?>
<?php foreach ($announcements as $announcement): ?>
<article class="notice-card"><h2><?= e($announcement['title']) ?></h2><p class="small">Published notice · Created <?= e($announcement['created_at']) ?></p><div class="preserve-text"><?= e($announcement['message']) ?></div></article>
<?php endforeach; ?>
<?php require __DIR__ . '/../components/footer.php'; ?>
