<?php
require_once __DIR__ . '/../../../../app/helpers.php';
requireRole('admin');

require_once __DIR__ . '/../../../../app/helpers.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
require_once __DIR__ . '/../../../../app/controllers/AnnouncementController.php';
$update_announcement = new AnnouncementController;
$announcement = $id ? $update_announcement->get_announcement_by_id($id) : null;
$updateMessage = $update_announcement->update_announcement();
?>

<?php $pageTitle = 'Update announcement'; require __DIR__ . '/../../components/header.php'; ?>
  <div class="container">
    <div class="card">
      <div class="card-header">Update announcement</div>
      <div class="card-body">
        <?php if ($updateMessage !== null): ?>
          <?= $updateMessage ?>
        <?php endif; ?>
        <?php if (!is_array($announcement)): ?>
          <p class="text-danger">Announcement not found.</p>
        <?php else: ?>
        <form action="" method="post">
          <table class="table">
            <tr>
              <th>Title</th>
              <td>
                <input type="text" name="title" value="<?= htmlspecialchars((string) ($announcement['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
              </td>
            </tr>
            <tr>
              <th>Message</th>
              <td>
                <input type="text" name="message" value="<?= htmlspecialchars((string) ($announcement['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
              </td>
            </tr>
            <tr>
              <th>Date updated</th>
              <td>
                <input type="datetime-local" name="created_at" value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($announcement['created_at'] ?? 'now')), ENT_QUOTES, 'UTF-8') ?>">
              </td>
            </tr>
            <tr>
              <th>Publish</th>
              <td>
                <select name="is_published" id="" >
                  <option value="1" <?= (string) ($announcement['is_published'] ?? '') === '1' ? 'selected' : '' ?>>Yes</option>
                  <option value="0" <?= (string) ($announcement['is_published'] ?? '') === '0' ? 'selected' : '' ?>>No</option>
                </select>
              </td>
            </tr>
            <tr>
              <input type="hidden" name="id" value="<?= (int) $id ?>">
              <th>
                <a class="btn btn-secondary"
                  href="<?= htmlspecialchars(
                      BASE_URL . '/resources/views/admin/announcement/announcement.php',
                      ENT_QUOTES,
                      'UTF-8'
                  ) ?>">
                  Cancel
                </a>
              </th>
              <td>
                <button type="submit" name="update" value="1" class="btn btn-danger">
                  Update
                </button>
              </td>

            </tr>
          </table>

        </form>
        <?php endif; ?>


      </div>
    </div>
  </div>

<?php require __DIR__ . '/../../components/footer.php'; ?>
