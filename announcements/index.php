<?php
require_once __DIR__ . '/common.php';
requireRole('admin');

function announcementDbFind(int $id): array
{
    $stmt = db()->prepare('SELECT * FROM announcements WHERE id=?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: [];
}

function announcementDbSave(?int $id, string $title, string $message, int $published, int $staff): void
{
    if ($id === null) {
        $stmt = db()->prepare(
            'INSERT INTO announcements (title,message,is_published,created_by,first_published_at) VALUES (?,?,?,?,CASE WHEN ?=1 THEN CURRENT_TIMESTAMP ELSE NULL END)',
        );
        $stmt->execute([$title, $message, $published, $staff, $published]);
    } else {
        $stmt = db()->prepare(
            'UPDATE announcements SET title=?,message=?,is_published=?,first_published_at=CASE WHEN ?=1 THEN COALESCE(first_published_at,CURRENT_TIMESTAMP) ELSE first_published_at END WHERE id=? AND archived_at IS NULL',
        );
        $stmt->execute([$title, $message, $published, $published, $id]);
    }
}

function announcementSave(?int $id): array
{
    requireRole('admin');
    if (!isPost()) {
        return [];
    }
    if ($id !== null && !empty(announcementDbFind($id)['archived_at'])) {
        return ['Archived notices cannot be edited or republished.'];
    }
    $action = input('action');
    if (
        $id !== null &&
        $action === 'save_draft' &&
        announcementDbFind($id)['is_published'] &&
        input('confirm_withdraw') !== '1'
    ) {
        return ['Confirm withdrawal before saving this published notice as a draft.'];
    }
    $title = input('title');
    $message = input('message');
    if (!in_array($action, ['save_draft', 'publish'], true)) {
        return ['Choose Save draft / withdraw or Publish.'];
    }
    if ($title === '' || mb_strlen($title) > 150 || $message === '' || mb_strlen($message) > 10000) {
        return ['Enter a title (up to 150 characters) and message (up to 10,000 characters).'];
    }
    try {
        announcementDbSave($id, $title, $message, $action === 'publish' ? 1 : 0, (int) $_SESSION['ID']);
    } catch (PDOException $error) {
        return ['Announcement could not be saved. Please try again.'];
    }
    flash($action === 'publish'
            ? 'Announcement published to residents.'
            : 'Announcement saved privately as a draft.');
    redirect('announcements/index.php');
    return [];
}

function announcementGetAnnouncement(): array
{
    $id = is_scalar($_GET['id'] ?? null) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : false;
    if (!$id || $id < 1) {
        http_response_code(400);
        exit('Invalid announcement ID.');
    }
    $row = announcementDbFind($id);
    if (!$row) {
        http_response_code(404);
        exit('Announcement not found.');
    }
    return $row;
}

$editing = array_key_exists('id', $_GET);
$announcement = $editing ? announcementGetAnnouncement() : ['title' => '', 'message' => '', 'is_published' => 0];
$errors = announcementSave($editing ? (int) $announcement['id'] : null);
$announcements = announcementListAnnouncements();
$pageTitle = "Manage announcements";
require __DIR__ . "/../includes/header.php";
require __DIR__ . "/../includes/errors.php";
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Emergency announcements</h1>
<p><a class="btn btn-outline-secondary" href="<?= e(url('records/index.php?type=announcements')) ?>">Manage records</a></p>
<p>Save a draft to finish later. Publishing immediately shows the notice to all signed-in residents.</p>
<h2><?= $editing ? 'Edit announcement' : 'Create announcement' ?></h2>
<?php if ($editing): ?>
    <p>Current status: <?= $announcement['is_published'] ? 'Published' : 'Draft / withdrawn' ?> · Created <?= e($announcement['created_at']) ?></p>
<?php endif; ?>
<form method="post">
    <label class="d-block mb-3">Title<input class="form-control" name="title" maxlength="150" value="<?= e(isPost()?input('title'):$announcement['title']) ?>" required /></label>
    <label class="d-block mb-3">Notice text<textarea class="form-control" name="message" rows="9" maxlength="10000" required >
<?= e(isPost()?input('message'):$announcement['message']) ?></textarea> </label>
    <p>Include the affected area, what residents should do, and the relevant date/time.</p>
    <?php if ($announcement['is_published']): ?>
        <label class="d-block mb-3">
            <input type="checkbox" name="confirm_withdraw" value="1" <?= input('confirm_withdraw') === '1' ? 'checked' : '' ?> /> I confirm that saving as a draft will withdraw this notice from residents. </label>
    <?php endif; ?>
    <button class="btn <?= $announcement['is_published'] ? 'btn-outline-danger' : 'btn-primary' ?>" name="action" value="save_draft"><?= $announcement['is_published']?'Save and withdraw':'Save draft' ?></button>
    <button class="btn btn-primary" name="action" value="publish"><?= $announcement['is_published']?'Save and keep published':'Publish' ?></button>
    <a href="<?= e(url('announcements/index.php')) ?>">Cancel and return to announcements</a>
</form>
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
                    <td><a href="<?= e(url('announcements/index.php?id=' . $row['id'])) ?>">Open</a></td>
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
