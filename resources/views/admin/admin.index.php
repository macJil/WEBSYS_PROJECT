<?php
require_once __DIR__ . '/../../../app/helpers.php';
requireRole('admin');
$pageTitle='Staff dashboard';
require __DIR__ . '/../components/header.php';
?>
<h1>Staff dashboard</h1>
<p>Use the shared records to manage incidents, response teams, evacuation, and relief.</p>
<div class="row g-3">
<?php foreach ([
    ['Household registry','Register households and members before evacuation and household relief distribution.','households.php'],
    ['Incidents','Review reports and assign response teams from an incident’s details.','reports/report.php'],
    ['Response teams','Maintain teams and their availability.','response/teams.php'],
    ['Evacuation centers','Manage capacity, check-ins, checkouts, and stay history.','evacuation/centers.php'],
    ['Relief goods','Record supplies and distribute goods to registered households.','relief/inventory.php'],
    ['Announcements','Prepare drafts and publish notices to residents.','announcement/announcement.php'],
    ['Disaster summaries','Review totals and print summaries without personal records.','summaries/disaster.php']
] as [$title,$description,$path]): ?>
<section class="col-md-6"><div class="border rounded p-3"><h2 class="h4"><a href="<?= e(url('resources/views/admin/'.$path)) ?>"><?= e($title) ?></a></h2><p><?= e($description) ?></p></div></section>
<?php endforeach; ?></div>
<?php require __DIR__ . '/../components/footer.php'; ?>
