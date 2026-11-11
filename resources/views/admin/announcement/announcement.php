<?php
require_once __DIR__ . '/../../../../app/controllers/AnnouncementController.php';
$controller=new AnnouncementController('admin'); $errors=$controller->save(null);
$announcements=$controller->listing(); $pageTitle='Manage announcements';
require __DIR__ . '/../../components/header.php'; require __DIR__ . '/../../components/errors.php';
?>
<h1>Emergency announcements</h1>
<p>Use drafts while preparing a notice. Publishing makes the saved text visible to residents. During classroom development, use clearly labeled fictional contacts only.</p>
<h2>Create announcement</h2>
<?php $announcement=['title'=>'','message'=>'','is_published'=>0]; require __DIR__ . '/editor.php'; ?>
<h2 class="mt-4">Announcement history</h2>
<form method="get" class="mb-3">
<label>Title contains <input name="q" value="<?= e($controller->query('q')) ?>"></label>
<label>Status <select name="status"><?php foreach ([''=>'All','draft'=>'Draft / withdrawn','published'=>'Published'] as $key=>$label): ?><option value="<?= e($key) ?>" <?= $controller->query('status')===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
<label>Created date <input type="date" name="date" value="<?= e($controller->query('date')) ?>"></label>
<button class="btn btn-secondary">Search</button> <a href="<?= e(url('resources/views/admin/announcement/announcement.php')) ?>">Clear</a>
</form>
<div class="table-responsive"><table class="table"><thead><tr><th>Title</th><th>Status</th><th>Created</th><th>Action</th></tr></thead><tbody>
<?php foreach ($announcements as $row): ?><tr><td><?= e($row['title']) ?></td><td><?= $row['is_published']?'Published':'Draft / withdrawn' ?></td><td><?= e($row['created_at']) ?></td><td><a href="<?= e(url('resources/views/admin/announcement/announcement.update.php?id='.$row['id'])) ?>">View / edit / publish / withdraw</a></td></tr><?php endforeach; ?>
<?php if (!$announcements): ?><tr><td colspan="4">No matching announcements.</td></tr><?php endif; ?></tbody></table></div>
<?php require __DIR__ . '/../../components/footer.php'; ?>
