<?php
require_once __DIR__ . "/../../../app/helpers.php";
requireRole("admin");
require_once __DIR__ . "/../../../app/controllers/DashboardController.php";
$overview = (new DashboardController("admin"))->dashboardData();
$pageTitle = "Staff dashboard";
require __DIR__ . "/../components/header.php";
?>
<div class="page-intro">
    <p class="eyebrow">Your community overview</p>
    <h1><?= e(APP_NAME) ?> — staff dashboard</h1>
    <p>A current overview of incidents, evacuation, and relief.</p>
</div>
<?php
require __DIR__ . "/../components/dashboard-cards.php";
?>
<p><a class="btn btn-primary" href="<?= e(url('resources/views/maps/map.php')) ?>">Explore saved locations on the map</a></p>
<p>Use the shared records to manage incidents, response teams, evacuation, and relief.</p>
<div class="row g-3">
    <section class="col-md-6">
        <div class="module-card">
            <h2 class="h4"><a href="<?= e(url('resources/views/admin/households.php')) ?>">Household registry</a></h2>
            <p>Register households and members before evacuation and household relief distribution.</p>
        </div>
    </section>
    <section class="col-md-6">
        <div class="module-card">
            <h2 class="h4"><a href="<?= e(url('resources/views/incidents/list.php')) ?>">Incidents</a></h2>
            <p>Review reports and assign response teams from an incident’s details.</p>
        </div>
    </section>
    <section class="col-md-6">
        <div class="module-card">
            <h2 class="h4"><a href="<?= e(url('resources/views/admin/response/teams.php')) ?>">Response teams</a></h2>
            <p>Maintain teams and their availability.</p>
        </div>
    </section>
    <section class="col-md-6">
        <div class="module-card">
            <h2 class="h4"><a href="<?= e(url('resources/views/admin/evacuation/centers.php')) ?>">Evacuation centers</a></h2>
            <p>Manage capacity, check-ins, checkouts, and stay history.</p>
        </div>
    </section>
    <section class="col-md-6">
        <div class="module-card">
            <h2 class="h4"><a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Relief goods</a></h2>
            <p>Record supplies and distribute goods to registered households.</p>
        </div>
    </section>
    <section class="col-md-6">
        <div class="module-card">
            <h2 class="h4"><a href="<?= e(url('resources/views/admin/announcement/announcement.php')) ?>">Announcements</a></h2>
            <p>Prepare drafts and publish notices to residents.</p>
        </div>
    </section>
    <section class="col-md-6">
        <div class="module-card">
            <h2 class="h4"><a href="<?= e(url('resources/views/admin/summaries/disaster.php')) ?>">Disaster summaries</a></h2>
            <p>Review totals and print summaries without personal records.</p>
        </div>
    </section>
</div>
<?php
require __DIR__ . "/../components/footer.php";
?>
