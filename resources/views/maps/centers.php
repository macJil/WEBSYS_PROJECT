<?php
require_once __DIR__ . '/../../../app/controllers/DashboardController.php';
session_checker();
$overview = (new DashboardController($_SESSION['ROLE']))->dashboardData();
$admin = $_SESSION['ROLE'] === 'admin';
$pageTitle = 'Open evacuation centers';
require __DIR__ . '/../components/header.php';
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Open evacuation centers</h1>
<p><?= count($overview['centers']) ?> centers. Centers without map points are still included. Available capacity is capacity minus active occupants. Closed and archived centers are excluded.</p>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <tr>
            <th>Center</th>
            <th>Address</th>
            <th>Capacity</th>
            <th>Occupancy</th>
            <th>Available capacity (people)</th>
        </tr>
        <?php foreach ($overview['centers'] as $row): ?><tr>
            <td
                ><?php if ($admin): ?><a href="<?= e(url('resources/views/admin/evacuation/center.php?id=' . $row['id'])) ?>"><?= e($row['name']) ?></a
                ><?php else: ?><?= e($row['name']) ?><?php endif; ?></td
            >
            <td><?= e($row['address_details']) ?></td>
            <td><?= (int)$row['capacity'] ?></td>
            <td><?= (int)$row['occupancy'] ?></td>
            <td><?= (int)$row['available'] ?></td> </tr
        ><?php endforeach; ?></table
    >
</div>
<?php require __DIR__ . '/../components/footer.php'; ?>
