<?php
require_once __DIR__ . '/../../../app/controllers/ReportController.php';
session_checker();
$controller = new ReportController($_SESSION['ROLE']);
$data = $controller->values();
if (!isPost()) { $data['reporter_name'] = $_SESSION['FULL_NAME'] ?? ''; }
$errors = $controller->create($data);
$pageTitle = 'Report incident';
require __DIR__ . '/../components/header.php';
require __DIR__ . '/../components/errors.php';
?>
<h1>Report an incident</h1>
<p>Reports are reviewed by barangay staff. This website does not guarantee an immediate emergency response.</p>
<form method="post" enctype="multipart/form-data">
<?php if ($_SESSION['ROLE'] === 'admin'): ?>
<label class="d-block mb-3">Report received through
<select name="report_source" class="form-select" required>
<?php foreach (['walk_in'=>'In person','phone'=>'Phone','admin'=>'Staff observation'] as $value=>$label): ?>
<option value="<?= e($value) ?>" <?= $data['report_source'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
<?php endforeach; ?></select></label>
<?php endif; ?>
<label class="d-block mb-3">Incident type <select name="incident_type" class="form-select" required>
<option value="">Choose type</option>
<?php foreach (ReportController::TYPES as $value=>$label): ?><option value="<?= e($value) ?>" <?= $data['incident_type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
</select></label>
<?php foreach (['reporter_name'=>['Reporter/contact name',100], 'contact_phone'=>['Contact number',20], 'location_description'=>['Location description / purok / nearby landmark',255]] as $key=>$field): ?>
<label class="d-block mb-3"><?= e($field[0]) ?><input class="form-control" name="<?= e($key) ?>" maxlength="<?= $field[1] ?>" value="<?= e($data[$key]) ?>" required></label>
<?php endforeach; ?>
<label class="d-block mb-3">Describe what happened<textarea class="form-control" name="description" maxlength="10000" required><?= e($data['description']) ?></textarea></label>
<fieldset class="mb-3"><legend>Location pin (optional)</legend>
<p>Click the map or enter coordinates. Keep the written location above even when you select a pin.</p>
<div class="row">
<?php foreach (['latitude'=>90,'longitude'=>180] as $key=>$limit): ?>
<label class="col"><?= e(ucfirst($key)) ?><input id="<?= e($key) ?>" class="form-control" type="number" step="any" min="<?= -$limit ?>" max="<?= $limit ?>" name="<?= e($key) ?>" value="<?= e($data[$key]) ?>"></label>
<?php endforeach; ?></div>
<button class="btn btn-outline-secondary my-2" id="locate" type="button">Use my current location</button>
<button class="btn btn-outline-secondary my-2" id="clear-location" type="button">Clear pin</button>
<div id="incident-map" style="height:320px" data-lat="<?= e($settings['map_latitude']) ?>" data-lng="<?= e($settings['map_longitude']) ?>" data-zoom="<?= e($settings['map_zoom']) ?>"></div>
<p id="map-status" aria-live="polite">Map needs internet. You can still submit a written location or enter coordinates.</p>
</fieldset>
<label class="d-block mb-3">Incident image (optional; JPEG, PNG, WebP; maximum 5 MiB)
<input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
<p>If a form error occurs, choose the image again before submitting.</p>
<button class="btn btn-primary" name="action" value="create_incident">Submit report</button>
</form>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= e(url('resources/assets/js/incident-map.js')) ?>"></script>
<?php require __DIR__ . '/../components/footer.php'; ?>
