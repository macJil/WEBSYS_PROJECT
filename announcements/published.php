<?php
require_once __DIR__ . '/common.php';
requireRole('resident');



if (isPost()) {
    http_response_code(405);
    exit("Announcements are read-only for residents.");
}
header("Cache-Control: private, no-store");
$announcements = announcementListAnnouncements();
$pageTitle = "Emergency announcements";
require __DIR__ . "/../includes/header.php";
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Emergency announcements</h1>
<form method="get" class="filter-form mb-4">
    <label>Search title <input name="q" value="<?= e(announcementQueryValue('q')) ?>" /></label>
    <label>Created date <input type="date" name="date" value="<?= e(announcementQueryValue('date')) ?>" /></label>
    <button class="btn btn-outline-secondary">Search</button> <a href="<?= e(url('announcements/published.php')) ?>">Clear</a>
</form>
<?php if (!$announcements): ?>
    <p>No published notices match your search.</p>
<?php endif; ?>
<?php foreach ($announcements as $announcement): ?>
    <article class="notice-card">
        <h2><?= e($announcement['title']) ?></h2>
        <p class="small">Published notice · Created <?= e($announcement['created_at']) ?></p>
        <div class="preserve-text"><?= e($announcement['message']) ?></div>
    </article>
<?php endforeach; ?>
<?php
require __DIR__ . "/../includes/footer.php";
?>
