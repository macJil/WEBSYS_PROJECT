<?php
require_once __DIR__ . '/../../../app/helpers.php';
require_once __DIR__ . '/../../../app/controllers/AnnouncementController.php';

require __DIR__ . '/../components/header.php';

$AnnouncementController = new AnnouncementController();
$announcements = $AnnouncementController->get_announcements();
?>
  <div class="container">
    <h1>Announcement</h1>
    <?php if(!$announcements):?>
      <h3>No Announcement Available...</h3>
    <?php else:?>
      <table class="table">
        <tr>
          <th>Title</th>
          <th>Message</th>
          <th>Date</th>
        </tr>
        <?php foreach($announcements as $announcement):?>
          <?php if($announcement['is_published']):?>
            <tr>
              <td><?= htmlspecialchars($announcement['title']) ?></td>
              <td><?= htmlspecialchars($announcement['message']) ?></td>
              <td><?= htmlspecialchars($announcement['created_at']) ?></td>
            </tr>
          <?php else:?>
            <h3>No Announcement Available...</h3>
          <?php endif?>
        <?php endforeach?>
        
      </table>

    <?php endif?>
  </div>
<?php require __DIR__ . '/../components/footer.php'; ?>
