<?php
require_once __DIR__ . '/common.php';
requireRole('admin');



$errors = announcementSave(null);
$announcements = announcementListAnnouncements();
$pageTitle = "Manage announcements";
require __DIR__ . "/../includes/header.php";
require __DIR__ . "/../includes/errors.php";
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Emergency announcements</h1>
<p><a class="btn btn-outline-secondary" href="<?= e(url('records/index.php?type=announcements')) ?>">Manage records</a></p>
<p>Save a draft to finish later. Publishing immediately shows the notice to all signed-in residents.</p>
<h2>Create announcement</h2>
<?php
$announcement = ["title" => "", "message" => "", "is_published" => 0];
require __DIR__ . "/editor.php";
?>
<h2 class="mt-4">Announcement history</h2>
<form method="get" class="filter-form mb-3">
    <label>Title contains <input class="form-control" placeholder="Title contains" maxlength="150" name="q" value="<?= e(announcementQueryValue('q')) ?>" /></label>
    <label>Status <select class="form-select" name="status">
            <?php foreach (['' => 'All','draft' => 'Draft / withdrawn','published' => 'Published'] as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= announcementQueryValue('status') === $key?'selected':'' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select></label>
    <label>Created on (exact date) <input class="form-control" type="date" name="date" value="<?= e(announcementQueryValue('date')) ?>" /></label>
    <button class="btn btn-outline-secondary">Search</button> <a class="btn btn-outline-secondary" href="<?= e(url('announcements/index.php')) ?>">Clear</a>
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
                    <td><a href="<?= e(url('announcements/edit.php?id=' . $row['id'])) ?>">Open</a></td>
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
require __DIR__ . "/../includes/footer.php";
?>
