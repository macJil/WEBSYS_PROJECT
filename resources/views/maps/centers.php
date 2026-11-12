<?php
require_once __DIR__.'/../../../app/controllers/DashboardController.php';session_checker();
$overview=(new DashboardController($_SESSION['ROLE']))->data();$admin=$_SESSION['ROLE']==='admin';
$pageTitle='Open evacuation centers';require __DIR__.'/../components/header.php';
?>
<h1>Open <?= $admin?'':'approved ' ?>evacuation centers</h1>
<p><?= count($overview['centers']) ?> centers. Centers without map points are still included.</p>
<table class="table"><tr><th>Center</th><th>Address</th><th>Capacity</th><th>Occupancy</th><th>Available</th></tr>
<?php foreach($overview['centers'] as $row): ?><tr><td><?php if($admin): ?><a href="<?= e(url('resources/views/admin/evacuation/center.php?id='.$row['id'])) ?>"><?= e($row['name']) ?></a><?php else: ?><?= e($row['name']) ?><?php endif; ?></td><td><?= e($row['address_details']) ?></td><td><?= (int)$row['capacity'] ?></td><td><?= (int)$row['occupancy'] ?></td><td><?= max(0,(int)$row['capacity']-(int)$row['occupancy']) ?></td></tr><?php endforeach; ?></table>
<?php require __DIR__.'/../components/footer.php'; ?>
