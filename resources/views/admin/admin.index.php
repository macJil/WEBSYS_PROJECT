<?php
require_once __DIR__ . '/../../../app/helpers.php';
requireRole('admin');

require_once __DIR__ . '/../../../app/helpers.php';


require __DIR__ . '/../components/header.php';
?>
  <h1>Staff dashboard</h1>
  <p>Manage household records and members through the household registry.</p>
  <a class="btn btn-primary" href="<?= e(url('resources/views/admin/households.php')) ?>">Open household registry</a>
<?php require __DIR__ . '/../components/footer.php'; ?>
