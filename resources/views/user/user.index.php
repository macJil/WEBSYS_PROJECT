<?php
require_once __DIR__ . '/../../../app/helpers.php';
requireRole('resident');
require_once __DIR__ . '/../../../app/controllers/DashboardController.php';
$overview = (new DashboardController('resident'))->dashboardData();
$pageTitle = 'Resident dashboard';
require __DIR__ . '/../components/header.php';
?>
<div class="page-intro"
    ><p class="eyebrow">Your community overview</p><h1><?= e($settings['app_name']) ?> — resident dashboard</h1
    ><p>Follow your reports and stay informed about community notices.</p>
</div>
<?php require __DIR__ . '/../components/dashboard-cards.php'; ?>
<p><a class="btn btn-primary" href="<?= e(url('resources/views/maps/map.php')) ?>">Explore saved locations on the map</a></p>
<p
    >Submit incident details for staff review, follow your own reports, and read published barangay
    notices.</p
>
<ul>
    <li><a href="<?= e(url('resources/views/user/report.incident.php')) ?>">Report an incident and select its location</a></li>
    <li><a href="<?= e(url('resources/views/user/reports.php')) ?>">View my incident reports</a></li>
    <li><a href="<?= e(url('resources/views/user/announcement.php')) ?>">Read emergency announcements</a></li>
</ul>
<p>Submitting a report does not guarantee an immediate emergency response.</p>
<?php require __DIR__ . '/../components/footer.php'; ?>
