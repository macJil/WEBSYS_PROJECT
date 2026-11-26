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
    <label>Search title <input class="form-control" placeholder="Search title" maxlength="150" name="q" value="<?= e(announcementQueryValue('q')) ?>" /></label>
    <label>Created on (exact date) <input class="form-control" type="date" name="date" value="<?= e(announcementQueryValue('date')) ?>" /></label>
    <button class="btn btn-outline-secondary">Search</button> <a class="btn btn-outline-secondary" href="<?= e(url('announcements/published.php')) ?>">Clear</a>
</form>
<?php if (!$announcements): ?>
    <p>No published notices match your search.</p>
<?php endif; ?>
<?php foreach ($announcements as $announcement): ?>
    <article class="notice-card">
        <h2><?= e($announcement['title']) ?></h2>
        <p class="small">Created <?= relativeTime($announcement['created_at']) ?></p>
        <button type="button" class="btn btn-outline-secondary btn-sm no-print" onclick="printNotice(this)">Print notice</button>
        <div class="preserve-text"><?= e($announcement['message']) ?></div>
    </article>
<?php endforeach; ?>
<script>
    function printNotice(button) {
        const notice = button.closest('article');
        notice.classList.add('printing-notice');
        window.addEventListener('afterprint', () => notice.classList.remove('printing-notice'), { once: true });
        window.print();
    }
</script>
<?php
require __DIR__ . "/../includes/footer.php";
?>
