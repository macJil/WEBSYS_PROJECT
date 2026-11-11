<?php
require_once __DIR__ . '/../../../app/helpers.php';
requireRole('resident');


require_once __DIR__ . '/../../../app/helpers.php';
require_once __DIR__ . '/../../../app/controllers/ReportController.php';



$ReportController = new ReportController();
$reports = $ReportController->get_my_reports();

require __DIR__ . '/../components/header.php';
?>
  <div class="container">
      <h1>My Reports</h1>

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
            <td><?= e($report['location_description']) ?></td>
            <td><?= e($report['incident_type']) ?></td>
            <td><?= e($report['description']) ?></td>
            <td><?= e($report['status']) ?></td>
          </tr>
          <?php endforeach?>
        </table>
      <?php endif ?>
  </div>
<?php require __DIR__ . '/../components/footer.php'; ?>
