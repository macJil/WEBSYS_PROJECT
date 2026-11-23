<?php
require_once __DIR__ . '/common.php';
requireRole('resident');



$overview = dashboardDashboardData();
$pageTitle = "Resident dashboard";
require __DIR__ . "/../resources/views/components/header.php";
?>
<div class="page-intro">
    <p class="eyebrow">Your community overview</p>
    <h1><?= e($settings['app_name']) ?> — resident dashboard</h1>
    <p>Follow your reports and stay informed about community notices.</p>
</div>
<?php
require __DIR__ . "/../resources/views/components/dashboard-cards.php";
?>
<p><a class="btn btn-primary" href="<?= e(url('resources/views/maps/map.php')) ?>">Explore saved locations on the map</a></p>
<p>Submit incident details for staff review, follow your own reports, and read published barangay notices.</p>
<ul>
    <li><a href="<?= e(url('incidents/create.php')) ?>">Report an incident and select its location</a></li>
    <li><a href="<?= e(url('incidents/list.php')) ?>">View my incident reports</a></li>
    <li><a href="<?= e(url('announcements/published.php')) ?>">Read emergency announcements</a></li>
</ul>
<p>Submitting a report does not guarantee an immediate emergency response.</p>
<?php
require __DIR__ . "/../resources/views/components/footer.php";
?>
