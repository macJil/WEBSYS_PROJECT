<nav class="navbar bg-light border-bottom"><div class="container gap-2">
<span class="navbar-brand">Barangay DRRM</span>
<?php if (!empty($_SESSION['ID'])): ?>
<a href="<?= e(url(dashboardPath())) ?>">Home</a>
<a href="<?= e(url('resources/views/maps/map.php')) ?>">Map</a>
<?php if ($_SESSION['ROLE'] === 'admin'): ?>
<a href="<?= e(url('resources/views/admin/households.php')) ?>">Households</a>
<a href="<?= e(url('resources/views/admin/reports/report.php')) ?>">Incidents</a>
<a href="<?= e(url('resources/views/admin/response/teams.php')) ?>">Response teams</a>
<a href="<?= e(url('resources/views/admin/evacuation/centers.php')) ?>">Evacuation centers</a>
<a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Relief goods</a>
<a href="<?= e(url('resources/views/admin/summaries/disaster.php')) ?>">Disaster summaries</a>
<a href="<?= e(url('resources/views/admin/announcement/announcement.php')) ?>">Announcements</a>
<?php else: ?>
<a href="<?= e(url('resources/views/user/report.incident.php')) ?>">Report incident</a>
<a href="<?= e(url('resources/views/user/reports.php')) ?>">My reports</a>
<a href="<?= e(url('resources/views/user/announcement.php')) ?>">Announcements</a>
<?php endif; ?>
<span><?= e($_SESSION['FULL_NAME']) ?></span>
<form method="post" action="<?= e(url(dashboardPath())) ?>"><button class="btn btn-outline-danger" name="action" value="logout">Logout</button></form>
<?php endif; ?></div></nav>
