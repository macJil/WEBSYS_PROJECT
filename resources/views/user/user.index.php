<?php
require_once __DIR__ . '/../../../app/helpers.php';
requireRole('resident');
$pageTitle='Resident dashboard';
require __DIR__ . '/../components/header.php';
?>
<h1>Resident dashboard</h1>
<p>Submit incident details for staff review, follow your own reports, and read published barangay notices.</p>
<ul>
<li><a href="<?= e(url('resources/views/user/report.incident.php')) ?>">Report an incident and select its location</a></li>
<li><a href="<?= e(url('resources/views/user/reports.php')) ?>">View my incident reports</a></li>
<li><a href="<?= e(url('resources/views/user/announcement.php')) ?>">Read emergency announcements</a></li>
</ul>
<p>Submitting a report does not guarantee an immediate emergency response.</p>
<?php require __DIR__ . '/../components/footer.php'; ?>
