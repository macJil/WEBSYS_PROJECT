<?php
require_once __DIR__ . '/../../../../app/helpers.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
require_once __DIR__ . '/../../../../app/controllers/ReportController.php';
$ReportController = new ReportController;
$report = $id ? $ReportController->get_report_by_id($id) : null;
$updateMessage = $ReportController->update_report();


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Delete Announcement</title>
  <link rel="stylesheet" href="<?= htmlspecialchars(
      BASE_URL . '/resources/assets/css/bootstrap.min.css',
      ENT_QUOTES,
      'UTF-8'
  ) ?>">
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="card-header">Update Announcement</div>
      <div class="card-body">
        <?php if ($updateMessage !== null): ?>
          <?= $updateMessage ?>
        <?php endif; ?>
        <?php if (!is_array($report)): ?>
          <p class="text-danger">Report not found.</p>
        <?php else: ?>
        <form action="" method="post">
          
          <table class="table">
            <tr>
              <th>Type</th>
              <td>
                <input type="text" name="incident_type" value="<?= htmlspecialchars((string) ($report['incident_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
              </td>
            </tr>
            <tr>
              <th>Description</th>
              <td>
                <input type="text" name="description" value="<?= htmlspecialchars((string) ($report['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
              </td>
            </tr>
            <tr>
              <tr>
              <th>Location description</th>
              <td>
                <input type="text" name="location_description" value="<?= htmlspecialchars((string) ($report['location_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
              </td>
            </tr>
            <tr>
              <tr>
              <th>Contact_phone</th>
              <td>
                <input type="text" name="contact_phone" value="<?= htmlspecialchars((string) ($report['contact_phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
              </td>
            </tr>
            <tr>
              <tr>
              <th>Status</th>
              <td>
                <select name="status" id="">
                  <option value="pending" <?= ($report['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                  <option value="responding" <?= ($report['status'] ?? '') === 'responding' ? 'selected' : '' ?>>Respond</option>
                  <option value="verified" <?= ($report['status'] ?? '') === 'verified' ? 'selected' : '' ?>>Verified</option>
                  <option value="resolved" <?= ($report['status'] ?? '') === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                  <option value="rejected" <?= ($report['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
              </td>
            </tr>
            <tr>
              <th>Date updated</th>
              <td>
                <input type="datetime-local" name="updated_at" value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($report['updated_at'] ?? 'now')), ENT_QUOTES, 'UTF-8') ?>">
              </td>
            </tr>
            
            <tr>
              <input type="hidden" name="id" value="<?= (int) $id ?>">
              <th>
                <a class="btn btn-secondary"
                  href="<?= htmlspecialchars(
                      BASE_URL . '/resources/views/admin/reports/report.php',
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

  <script src="<?= htmlspecialchars(
      BASE_URL . '/resources/assets/js/bootstrap.bundle.min.js',
      ENT_QUOTES,
      'UTF-8'
  ) ?>"></script>
</body>
</html>