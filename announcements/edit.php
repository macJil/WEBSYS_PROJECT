<?php
require_once __DIR__ . '/common.php';
requireRole('admin');

function announcementGetAnnouncement(): array
{
    requireRole('admin');
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

$announcement = announcementGetAnnouncement();
$errors = announcementSave((int) $announcement["id"]);
$pageTitle = "Edit announcement";
require __DIR__ . "/../includes/header.php";
require __DIR__ . "/../includes/errors.php";
?>
<h1>Edit announcement</h1>
<p>Current status: <strong><?= $announcement['is_published']?'Published':'Draft / withdrawn' ?></strong> · Created <?= e($announcement['created_at']) ?></p>
<p>Saving and withdrawing hides this notice from residents on their next page load. Saving while published updates the text residents see immediately.</p>
<?php
require __DIR__ . "/editor.php";
?>
<a class="d-block mt-3" href="<?= e(url('announcements/index.php')) ?>">Back to announcements</a>
<?php
require __DIR__ . "/../includes/footer.php";
?>
