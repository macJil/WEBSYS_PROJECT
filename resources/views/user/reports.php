<?php

require_once __DIR__ . '/../../../app/helpers.php';
require_once __DIR__ . '/../../../app/controllers/ReportController.php';

require __DIR__ . '/../components/header.php';

$ReportController = new ReportController();
$reports = $ReportController->get_report_by_id($_SESSION['ID']);
?>
  <div class="container">
      <h1>My Reports</h1>
      <?= $ReportController->delete_reports() ?>
      <?php if(count($reports)<1): ?>
        <h3>No Data Found...</h3>
      <?php else:?>
        <table class="table">
          <tr>
            <th>Location Description</th>
            <th>Type</th>
            <th>Description</th>
            <th>Status</th>
          </tr>
          <?php foreach($reports as $report):?>
          <tr>
            <td><?= $report['location_description'] ?></td>
            <td><?= $report['incident_type'] ?></td>
            <td><?= $report['description'] ?></td>
            <td><?= $report['status'] ?></td>
          </tr>
          <?php endforeach?>
        </table>
      <?php endif ?>
  </div>
<?php require __DIR__ . '/../components/footer.php'; ?>
