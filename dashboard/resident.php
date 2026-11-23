<?php
require_once __DIR__ . '/common.php';
requireRole('resident');



$overview = dashboardDashboardData();
$pageTitle = "Resident dashboard";
require __DIR__ . "/../includes/header.php";
?>
<div class="page-intro">
    <p class="eyebrow">Your community overview</p>
    <h1><?= e(APP_NAME) ?> — resident dashboard</h1>
    <p>Follow your reports and stay informed about community notices.</p>
</div>
<?php
require __DIR__ . "/../includes/dashboard-cards.php";
?>
<p><a class="btn btn-primary" href="<?= e(url('map/map.php')) ?>">Explore saved locations on the map</a></p>
<p>Submit incident details for staff review, follow your own reports, and read published barangay notices.</p>
<ul>
    <li><a href="<?= e(url('incidents/create.php')) ?>">Report an incident and select its location</a></li>
    <li><a href="<?= e(url('incidents/list.php')) ?>">View my incident reports</a></li>
    <li><a href="<?= e(url('announcements/published.php')) ?>">Read emergency announcements</a></li>
</ul>
<p>Submitting a report does not guarantee an immediate emergency response.</p>
<?php
require __DIR__ . "/../includes/footer.php";
?>
