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
$pending = dashboardDbRows("SELECT id, incident_type, barangay, created_at
    FROM incident_reports WHERE status='pending' AND archived_at IS NULL
    ORDER BY created_at, id LIMIT 5");
$pageTitle = "Staff dashboard";
require __DIR__ . "/../includes/header.php";
?>
<div class="page-intro">
    <p class="eyebrow">Your community overview</p>
    <h1><?= e($settings['app_name']) ?> — staff dashboard</h1>
    <p>A current overview of incidents, evacuation, and relief.</p>
</div>
<h2>Current municipal overview</h2>
<?php require __DIR__ . '/../includes/dashboard-cards.php'; ?>
<h2>Awaiting review</h2>
<p>Five oldest pending reports across Atok.</p>
<?php if ($pending): ?>
    <div class="table-responsive mb-3">
        <table class="table">
            <thead><tr><th>Incident</th><th>Barangay</th><th>Submitted</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($pending as $report): ?>
                    <tr>
                        <td>#<?= (int)$report['id'] ?> · <?= e(REPORT_TYPES[$report['incident_type']]) ?></td>
                        <td><?= e($report['barangay'] ?? 'Awaiting confirmation') ?></td>
                        <td><?= e($report['created_at']) ?></td>
                        <td><a class="btn btn-primary btn-sm" href="<?= e(url('incidents/view.php?id=' . $report['id'])) ?>" aria-label="Review incident #<?= (int)$report['id'] ?>">Review</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p>No reports awaiting review.</p>
<?php endif; ?>
<p><a class="btn btn-outline-secondary" href="<?= e(url('incidents/list.php?status=pending')) ?>">View all pending reports</a></p>
<h2>Open center occupancy</h2>
<?php if ($overview['centers']): ?>
    <div class="table-responsive mb-3">
        <table class="table">
            <thead><tr><th>Center</th><th>People staying</th><th>Capacity (people)</th><th>Places remaining</th></tr></thead>
            <tbody>
                <?php foreach ($overview['centers'] as $center): ?>
                    <tr>
                        <td><a href="<?= e(url('evacuation/center.php?id=' . $center['id'])) ?>"><?= e($center['name']) ?></a></td>
                        <td><?= (int)$center['occupancy'] ?></td>
                        <td><?= (int)$center['capacity'] ?></td>
                        <td><?= (int)$center['available'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p>No open evacuation centers.</p>
<?php endif; ?>
<h2>Incident reports for the selected period</h2>
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
<?php require __DIR__ . '/../includes/weather.php'; ?>
<p><a class="btn btn-primary" href="<?= e(url('map/map.php')) ?>">Explore saved locations on the map</a></p>
<p>Use the shared records to manage incidents, response teams, evacuation, and relief.</p>
<div class="row g-3">
    <?php foreach ([
        ['Household registry','Register households and members before evacuation and household relief distribution.','households/index.php'],
        ['Incidents','Review reports and assign response teams from an incident’s details.','incidents/list.php'],
        ['Response teams','Maintain teams and their availability.','response/teams.php'],
        ['Evacuation centers','Manage capacity, check-ins, checkouts, and stay history.','evacuation/centers.php'],
        ['Relief goods','Manage relief goods and record stock movements.','relief-inventory/index.php'],
        ['Relief distribution','Record goods issued to a household and its receiving representative.','relief-distribution/index.php'],
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
