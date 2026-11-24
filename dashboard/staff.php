<?php
require_once __DIR__ . '/common.php';
requireRole('admin');



require_once __DIR__ . '/../incidents/common.php';
$scope = incidentScope();
$where = ['archived_at IS NULL'];
$values = [];
incidentScopeConditions($scope, $where, $values);
$sql = 'SELECT incident_type, COUNT(*) AS total FROM incident_reports WHERE ' . implode(' AND ', $where) . ' GROUP BY incident_type';
$typeCounts = array_column(dashboardDbRows($sql, $values), 'total', 'incident_type');
$overview = dashboardDashboardData();
$pageTitle = "Staff dashboard";
require __DIR__ . "/../includes/header.php";
?>
<div class="page-intro">
    <p class="eyebrow">Your community overview</p>
    <h1><?= e($settings['app_name']) ?> — staff dashboard</h1>
    <p>A current overview of incidents, evacuation, and relief.</p>
</div>
<?php
require __DIR__ . "/../includes/dashboard-cards.php";
require __DIR__ . "/../includes/weather.php";
?>
<h2>Reports by incident type</h2>
<form class="row g-2 mb-3" method="get">
    <?php require __DIR__ . '/../incidents/scope-fields.php'; ?>
    <div><button class="btn btn-primary">Filter incident counts</button></div>
</form>
<div class="row g-3 mb-4">
    <?php foreach (REPORT_TYPES as $type => $label): ?>
        <div class="col-sm-6 col-lg-4">
            <a class="stat-card" href="<?= e(url('incidents/list.php?' . http_build_query($scope + ['incident_type' => $type]))) ?>">
                <strong class="stat-value"><?= (int) ($typeCounts[$type] ?? 0) ?></strong>
                <span class="stat-label"><?= e($label) ?> reports</span>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<p><a class="btn btn-primary" href="<?= e(url('map/map.php')) ?>">Explore saved locations on the map</a></p>
<p>Use the shared records to manage incidents, response teams, evacuation, and relief.</p>
<div class="row g-3">
    <?php foreach ([
        ['Household registry','Register households and members before evacuation and household relief distribution.','households/index.php'],
        ['Incidents','Review reports and assign response teams from an incident’s details.','incidents/list.php'],
        ['Response teams','Maintain teams and their availability.','response/teams.php'],
        ['Evacuation centers','Manage capacity, check-ins, checkouts, and stay history.','evacuation/centers.php'],
        ['Relief goods','Record supplies and distribute goods to registered households.','relief-inventory/index.php'],
        ['Announcements','Prepare drafts and publish notices to residents.','announcements/index.php'],
        ['Disaster summaries','Review totals and print summaries without personal records.','disaster-reports/index.php']
    ] as [$title,$description,$path]): ?>
        <section class="col-md-6">
            <div class="module-card">
                <h2 class="h4"><a href="<?= e(url($path)) ?>"><?= e($title) ?></a></h2>
                <p><?= e($description) ?></p>
            </div>
        </section>
    <?php endforeach; ?>
</div>
<?php
require __DIR__ . "/../includes/footer.php";
?>
