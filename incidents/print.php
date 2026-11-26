<?php
require_once __DIR__ . '/incident_function.php';
requireCurrentPassword();
$id = reportIncidentId();
$owner = reportOwner();
$sql = 'SELECT id, incident_type, status, created_at, occurred_at, barangay, location_description,
               latitude, longitude, reporter_name, contact_phone, description FROM incident_reports WHERE id=?';
if ($owner !== null) {
    $sql .= ' AND reported_by=?';
}
$stmt = db()->prepare($sql);
$stmt->execute($owner === null ? [$id] : [$id, $owner]);
$report = $stmt->fetch();
if (!$report) {
    http_response_code(404);
    exit('Incident not found.');
}
header('Cache-Control: private, no-store');
$pageTitle = 'Incident report #' . $id;
require __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="<?= e(url('assets/css/disaster-summary.css')) ?>">
<div class="no-print mb-3">
    <a class="btn btn-outline-secondary" href="<?= e(url('incidents/view.php?id=' . $id)) ?>">Return to incident</a>
    <button type="button" id="print-summary" class="btn btn-primary">Print / Save as PDF</button>
</div>
<header class="report-heading">
    <p>Municipality of Atok · Benguet</p>
    <h1><?= e($pageTitle) ?></h1>
    <p><?= $owner === null ? 'Staff operational copy' : 'Resident submission copy' ?></p>
</header>
<p>Generated <?= e((new DateTimeImmutable('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d H:i:s')) ?> Asia/Manila.</p>
<table class="table">
    <tbody>
        <tr><th>Incident type</th><td><?= e(REPORT_TYPES[$report['incident_type']]) ?></td></tr>
        <?php foreach (['status'=>'Current status', 'created_at'=>'Submitted', 'occurred_at'=>'Occurred', 'reporter_name'=>'Reported contact', 'contact_phone'=>'Contact number', 'barangay'=>'Incident barangay', 'location_description'=>'Written location'] as $field => $label): ?>
            <tr><th><?= e($label) ?></th><td><?= e($report[$field] ?? 'Not recorded') ?></td></tr>
        <?php endforeach; ?>
        <tr><th>Reported point</th><td><?= hasMapPoint($report) ? e($report['latitude'] . ', ' . $report['longitude']) : 'No map point' ?></td></tr>
    </tbody>
</table>
<h2>Report description</h2>
<p class="preserve-text"><?= e($report['description']) ?></p>
<?php if ($owner === null): ?>
    <?php
    $stmt = db()->prepare('SELECT created_at, action_taken, to_status FROM response_updates WHERE incident_id=? ORDER BY id');
    $stmt->execute([$id]);
    $updates = $stmt->fetchAll();
    ?>
    <h2>Staff review and response history</h2>
    <p><?= count($updates) ?> recorded updates.</p>
    <?php foreach ($updates as $update): ?>
        <p><strong><?= e($update['created_at']) ?> · <?= e($update['to_status']) ?></strong><br><?= e($update['action_taken']) ?></p>
    <?php endforeach; ?>
<?php endif; ?>
<script src="<?= e(url('assets/js/print-summary.js')) ?>"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
