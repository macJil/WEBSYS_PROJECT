<?php
require_once __DIR__ . '/../../../../app/helpers.php';
require_once __DIR__ . '/../../../../app/controllers/ReportController.php';

require __DIR__ . '/../../components/header.php';

$ReportController = new ReportController();
$reports = $ReportController->get_all_reports();
$updateMessage = $_SESSION['FLASH'] ?? null;
$type = $ReportController->count_type();
echo $type['fire'];
unset($_SESSION['FLASH']);
?>
  <div class="container">
    <h1>Report Incidents</h1>
    <div class="container">
      <div class="card">
        <h3>Fire</h3>
        <span><?= $type['fire'] ?></span>
      </div>
    </div>
    <?= $ReportController->delete_reports() ?>
    <?php if ($updateMessage !== null): ?>
      <?= $updateMessage ?>
    <?php endif; ?>
    <form action="" method="post" onsubmit="return confirm('Permanently delete this record?');">
      <?php if(!$reports):?>
        <h3>No Reports Available...</h3>
      <?php else:?>
        <table class="table">
           <tr>
            <th>
              <button type="submit" name="delete_reports" value="1">Delete</button>
            </th>
            <th>Location Description</th>
            <th>Type</th>
            <th>Description</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
            
          </tr>
          <?php foreach($reports as $report):?>
          <tr>
            <td>
              <input type="checkbox" name="reports[]" value="<?= (int) $report['id'] ?>">
            </td>
            <td><?= $report['location_description'] ?></td>
            <td><?= $report['incident_type'] ?></td>
            <td><?= $report['description'] ?></td>
            <td><?= $report['status'] ?></td>
             <td><?= $report['updated_at'] ?></td>
            <td>
              <a href="<?= htmlspecialchars(
                  BASE_URL . '/resources/views/admin/reports/report.update.php?id='
                  . urlencode((string) $report['id']),
                  ENT_QUOTES,
                  'UTF-8'
              ) ?>">Update</a>
              
            </td>
            
          </tr>
          <?php endforeach?>
        </table>
      <?php endif?>
    </form>
  </div>
<?php require __DIR__ . '/../../components/footer.php'; ?>
