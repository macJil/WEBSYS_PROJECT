<?php
require_once __DIR__ . "/../../../../app/controllers/AnnouncementController.php";
$controller = new AnnouncementController("admin");
$errors = $controller->save(null);
$announcements = $controller->listAnnouncements();
$pageTitle = "Manage announcements";
require __DIR__ . "/../../components/header.php";
require __DIR__ . "/../../components/errors.php";
$filterQ = $controller->queryValue('q');
$filterStatus = $controller->queryValue('status');
$filterDate = $controller->queryValue('date');
?>
<p class="return-link"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Emergency announcements</h1>
<p><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/records.php?type=announcements')) ?>">Manage records</a></p>
<p>Use drafts while preparing a notice. Publishing makes the saved text visible to residents. During classroom development, use clearly labeled fictional contacts only.</p>
<h2>Create announcement</h2>
<?php
$announcement = ["title" => "", "message" => "", "is_published" => 0];
require __DIR__ . "/editor.php";
?>
<h2 class="mt-4">Announcement history</h2>
<form method="get" class="filter-form mb-3">
    <label>Title contains <input name="q" value="<?= e($filterQ) ?>" /></label>
    <label>Status <select name="status">
            <option value="" <?= $filterStatus === ''?'selected':'' ?>>All</option>
            <option value="draft" <?= $filterStatus === 'draft'?'selected':'' ?>>Draft / withdrawn</option>
            <option value="published" <?= $filterStatus === 'published'?'selected':'' ?>>Published</option>
        </select></label>
    <label>Created date <input type="date" name="date" value="<?= e($filterDate) ?>" /></label>
    <button class="btn btn-outline-secondary">Search</button> <a href="<?= e(url('resources/views/admin/announcement/announcement.php')) ?>">Clear</a>
</form>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($announcements as $row): ?>
                <tr>
                    <td><?= e($row['title']) ?></td>
                    <td><span class="status-badge" data-status="<?= $row['is_published']?'published':'draft' ?>"><?= $row['is_published']?'Published':'Draft / withdrawn' ?></span></td>
                    <td><?= e($row['created_at']) ?></td>
                    <td><a href="<?= e(url('resources/views/admin/announcement/announcement.update.php?id=' . $row['id'])) ?>">View / edit / publish / withdraw</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$announcements): ?>
                <tr>
                    <td colspan="4">No matching announcements.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
require __DIR__ . "/../../components/footer.php";
?>
