<?php
require_once __DIR__ . '/common.php';
requireRole('resident');
require_once __DIR__ . '/../incidents/common.php';

$reports = dashboardDbRows(
    'SELECT id, incident_type, status, created_at FROM incident_reports
     WHERE reported_by=? AND archived_at IS NULL ORDER BY created_at DESC, id DESC LIMIT 1',
    [(int) $_SESSION['ID']]
);
$latest = $reports[0] ?? null;

$overview = dashboardDashboardData();
$pageTitle = "Resident dashboard";
require __DIR__ . "/../includes/header.php";
?>
<div class="page-intro">
    <h1><?= e($settings['app_name']) ?> — resident dashboard</h1>
</div>
<p><a class="btn btn-primary" href="<?= e(url('incidents/create.php')) ?>">Report an incident</a></p>
<section class="panel mb-4">
    <h2>My latest report</h2>
    <?php if ($latest): ?>
        <p><?= e(REPORT_TYPES[$latest['incident_type']]) ?> · Submitted <?= e($latest['created_at']) ?></p>
        <p>Status: <strong><?= e(ucfirst($latest['status'])) ?></strong></p>
        <a class="btn btn-outline-secondary" href="<?= e(url('incidents/view.php?id=' . $latest['id'])) ?>">View report #<?= (int)$latest['id'] ?></a>
    <?php else: ?>
        <p>You haven’t submitted a report yet.</p>
    <?php endif; ?>
</section>
<?php
require __DIR__ . "/../includes/dashboard-cards.php";
require __DIR__ . "/../includes/weather.php";
?>
<p><a class="btn btn-primary" href="<?= e(url('map/map.php')) ?>">Open map</a></p>
<ul>
    <li><a href="<?= e(url('incidents/list.php')) ?>">View my incident reports</a></li>
    <li><a href="<?= e(url('announcements/published.php')) ?>">Read emergency announcements</a></li>
</ul>
<p>Submitting a report does not guarantee an immediate emergency response.</p>
<?php
require __DIR__ . "/../includes/footer.php";
?>
