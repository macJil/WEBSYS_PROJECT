<?php
require_once __DIR__ . '/../../../../app/helpers.php';
requireRole('admin');

require_once __DIR__ . '/../../../../app/helpers.php';

require_once __DIR__ . '/../../../../app/controllers/AnnouncementController.php';


$AnnouncementController = new AnnouncementController();
$announcements= $AnnouncementController->get_announcements();
$deleteMessage = $_SESSION['FLASH'] ?? null;
unset($_SESSION['FLASH']);
$store_announcementMessage = $AnnouncementController->store_announcement();
$delete_announcementsMessage = $AnnouncementController->delete_announcements();
require __DIR__ . '/../../components/header.php';
?>

  <div class="container">
    <h1>Announcement</h1>
    <h3>Create Announcemnt</h3>
    <?= $store_announcementMessage ?>
    <?= $delete_announcementsMessage ?>

    <?php if ($deleteMessage !== null): ?>
      <?= $deleteMessage ?>
    <?php endif; ?>
    <form action="" method="post">
      <table>
        <tr>
          <th>Title</th>
          <td>
            <input type="text" name="title">
          </td>
        </tr>
        <tr>
          <th>Message</th>
          <td>
            <textarea name="message" id=""></textarea>
          </td>
        </tr>
        <tr>
          <th>
            <button name="submit_message">Submit Message</button>
          </th>

        </tr>

      </table>
    </form>
    <div class="container">
      <form action="" method="post">
        <?php if(count($announcements)<1): ?>
          <h3>No Data Available...</h3>
        <?php else:?>
          <table class="table">
            <tr>
                <th><button type="submit" name="delete_announcements" value="1" onclick="return confirm('Permanently delete the selected announcements?');">Delete Selected</button></th>
                <th>Title</th>
                <th>Message</th>
                <th>Published</th>
                <th>Created</th>
                <th>Action</th>

            </tr>
            <?php foreach($announcements as $announcement):?>
              <tr>
                <td><input type="checkbox" name="announcements[]" value="<?= (int) $announcement['id'] ?>"></td>
                <td><?= htmlspecialchars($announcement['title']) ?></td>
                <td><?= htmlspecialchars($announcement['message']) ?></td>
                <td><?= htmlspecialchars($announcement['is_published']) ? "Published" : "Not Published" ?></td>
                <td><?= htmlspecialchars($announcement['created_at']) ?></td>
                <td>
                  <a href="<?= htmlspecialchars(
                      BASE_URL . '/resources/views/admin/announcement/announcement.update.php?id='
                      . urlencode((string) $announcement['id']),
                      ENT_QUOTES,
                      'UTF-8'
                  ) ?>">Update</a>
                </td>
              </tr>
            <?php endforeach ?>
          </table>
        <?php endif ?>
      </form>

    </div>
  </div>


<?php require __DIR__ . '/../../components/footer.php'; ?>
