<?php
require_once __DIR__ . '/../../../app/helpers.php';
requireRole('admin');
require_once __DIR__ . '/../../../app/controllers/DashboardController.php';
$overview = (new DashboardController('admin'))->dashboardData();
$pageTitle = 'Staff dashboard';
require __DIR__ . '/../components/header.php';
?>
<div class="page-intro">
    <p class="eyebrow">Your community overview</p>
    <h1><?= e($settings['app_name']) ?> — staff dashboard</h1>
    <p>A current overview of incidents, evacuation, and relief.</p>
</div>
<?php require __DIR__ . '/../components/dashboard-cards.php'; ?>
<p><a class="btn btn-primary" href="<?= e(url('resources/views/maps/map.php')) ?>">Explore saved locations on the map</a></p>
<p>Use the shared records to manage incidents, response teams, evacuation, and relief.</p>
<div class="row g-3">
    <?php foreach (
        [
            ['Household registry', 'Register households and members before evacuation and household relief distribution.', 'households.php'],
            ['Incidents', 'Review reports and assign response teams from an incident’s details.', 'reports/report.php'],
            ['Response teams', 'Maintain teams and their availability.', 'response/teams.php'],
            ['Evacuation centers', 'Manage capacity, check-ins, checkouts, and stay history.', 'evacuation/centers.php'],
            ['Relief goods', 'Record supplies and distribute goods to registered households.', 'relief/inventory.php'],
            ['Announcements', 'Prepare drafts and publish notices to residents.', 'announcement/announcement.php'],
            ['Disaster summaries', 'Review totals and print summaries without personal records.', 'summaries/disaster.php']
        ] as [$title, $description, $path]
    ): ?>
        <section class="col-md-6">
            <div class="module-card">
                <h2 class="h4"><a href="<?= e(url('resources/views/admin/' . $path)) ?>"><?= e($title) ?></a></h2>
                <p><?= e($description) ?></p>
            </div>
        </section>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../components/footer.php'; ?>