<?php
require_once __DIR__ . '/../../../app/helpers.php';
requireRole('resident');
$store_reportMessage = $ReportController->store_report();
require __DIR__ . '/../components/header.php';
?>
 <?php
require_once __DIR__ . '/../../../app/helpers.php';
require_once __DIR__ . '/../../../app/controllers/ReportController.php';



$ReportController = new ReportController();

 ?>
  <div class="container">
  <?= $store_reportMessage ?>
  <h1>Report Incident</h1>
  <form action="" method="post">

      <table>
        <tr>
          <th>Incident Type:</th>
          <td>
            <select name="incident_type" id="">
              <option value="landslide">Landslide</option>
              <option value="flood">Flood</option>
              <option value="fire">fire</option>
              <option value="road_obstruction">Road Obstruction</option>
              <option value="other">Others</option>
            </select>
          </td>
        </tr>
        <tr>
          <th>Description:</th>
          <td>
            <textarea name="description" id=""></textarea>
          </td>
        </tr>
        <tr>
          <th>
            <button name="store_report" type="submit">Submit Report</button>
          </th>
        </tr>
      </table>
    </form>
  </div>

<?php require __DIR__ . '/../components/footer.php'; ?>
