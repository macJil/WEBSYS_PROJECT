<?php
require_once __DIR__ . '/../../../app/controllers/ReportController.php';
session_checker();
$controller = new ReportController($_SESSION['ROLE']);
$id = $controller->id();
$report = $controller->find($id);
$admin = $_SESSION['ROLE'] === 'admin';
if (isPost() && !$admin) {
    http_response_code(403);
    exit('Only staff may review incidents.');
}
if ($admin && isPost() && input('action') === 'update_location') {
    $errors = $controller->updateLocation($id);
} else {
    $errors = $admin ? $controller->review($id) : [];
}
$images = $controller->images($id);
$history = $admin ? $controller->history($id) : [];
$pageTitle = 'Incident #' . $id;
require __DIR__ . '/../components/header.php';
require __DIR__ . '/../components/errors.php';
?>
<h1><?= e($pageTitle) ?></h1>
<dl>
<?php foreach (['incident_type' => 'Type','status' => 'Status','description' => 'Description','reporter_name' => 'Reporter/contact name','contact_phone' => 'Contact number','location_description' => 'Location','latitude' => 'Latitude','longitude' => 'Longitude','report_source' => 'Received through','created_at' => 'Submitted'] as $key => $label): ?>
<dt><?= e($label) ?></dt><dd class="preserve-text"><?= e($report[$key] ?? 'Not recorded') ?></dd>
<?php endforeach; ?></dl>
<?php if (!hasMapPoint($report)): ?><p>No map point. This report remains available in lists and totals.</p><?php endif; ?>
<?php if ((!$admin || $report['archived_at'] !== null) && hasMapPoint($report)): ?>
<div id="saved-incident-map" data-lat="<?= e($report['latitude']) ?>" data-lng="<?= e($report['longitude']) ?>">
</div>
<p id="saved-map-status">Saved coordinates are listed above. Map tiles need internet.</p>
<link rel="stylesheet" href="<?= e(url('resources/assets/vendor/leaflet/leaflet.css')) ?>">
<script src="<?= e(url('resources/assets/vendor/leaflet/leaflet.js')) ?>"></script>
<script src="<?= e(url('resources/assets/js/incident-location.js')) ?>"></script>
<?php endif; ?>
<p>Locations are reported points, not verified hazard assessments. No approved barangay boundary or location restriction is applied. DEMO ONLY locations are fictional examples.</p>
<h2>Incident images</h2>
<?php if (!$images): ?><p>No image attached.</p><?php endif; ?>
<?php foreach ($images as $image): ?>
<a href="<?= e(url('resources/views/incidents/image.php?id=' . $image['id'])) ?>"><img class="incident-image" src="<?= e(url('resources/views/incidents/image.php?id=' . $image['id'])) ?>" alt="Image attached to this incident"></a>
<?php endforeach; ?>
<?php if ($admin): ?>
<p><a href="<?= e(url('resources/views/admin/map-publication.php?type=incident&id=' . $id)) ?>">Manage community map visibility</a></p>
<p class="mt-3"><a class="btn btn-secondary" href="<?= e(url('resources/views/admin/response/incident.php?id=' . $id)) ?>">Manage response teams and actions</a></p>
<?php if ($report['archived_at'] === null): ?>
<h2>Correct incident location</h2>
<p>Confirm the written location and pin. A correction is recorded in staff history. The separately approved community-map point is unchanged.</p>
<form method="post">
<label class="d-block mb-3">Written location<input class="form-control" name="location_description" maxlength="255" required value="<?= e(isPost() && input('action') === 'update_location'?input('location_description'):$report['location_description']) ?>"></label>
<?php
$point = $report;
    if (isPost() && input('action') === 'update_location') {
        $point['latitude'] = input('latitude');
        $point['longitude'] = input('longitude');
    }
    require __DIR__ . '/../components/location-picker.php';
    ?>
<label class="d-block mb-3">Reason for correction<input class="form-control" name="location_notes" maxlength="255" required value="<?= e(input('location_notes')) ?>"></label>
<button class="btn btn-primary" name="action" value="update_location">Save location correction</button>
</form>
<?php endif; ?>
<h2 class="mt-4">Staff review</h2>
<p>Verify before assigning teams. Active assignments keep the incident responding. Complete or cancel them before resolving or rejecting the incident.</p>
<form method="post">
<label>Status <select class="form-select" name="status">
<?php foreach (ReportController::STATUSES as $status): ?><option value="<?= e($status) ?>" <?= (isPost() && input('action') === 'review_incident' ? input('status') : $report['status']) === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select></label>
<label class="d-block my-3">Review notes / action taken<textarea class="form-control" name="notes" maxlength="255" required><?= e(input('notes')) ?></textarea></label>
<button name="action" value="review_incident" class="btn btn-primary">Save review</button>
</form>
<h2 class="mt-4">Staff review history</h2>
<?php foreach ($history as $entry): ?><p><?= e($entry['created_at']) ?> · <?= e($entry['from_status']) ?> → <?= e($entry['to_status']) ?><br><?= e($entry['action_taken']) ?></p><?php endforeach; ?>
<?php endif; ?>
<a class="d-block mt-3" href="<?= e(url('resources/views/incidents/list.php')) ?>">Back to reports</a>
<?php require __DIR__ . '/../components/footer.php'; ?>
