<?php
require_once __DIR__ . '/../../../app/controllers/ReportController.php';
session_checker();
$controller = new ReportController($_SESSION['ROLE']);
$data = $controller->values();
if (!isPost()) {
    $data['reporter_name'] = $_SESSION['FULL_NAME'] ?? '';
}
$errors = $controller->create($data);
$pageTitle = 'Report incident';
require __DIR__ . '/../components/header.php';
require __DIR__ . '/../components/errors.php';
?>
<h1>Report an incident</h1>
<p>Reports are reviewed by barangay staff. This website does not guarantee an immediate emergency
    response.</p>
<form method="post" enctype="multipart/form-data">
    <?php if ($_SESSION['ROLE'] === 'admin'): ?>
        <label class="d-block mb-3">Report received through
            <select name="report_source" class="form-select" required>
                <?php foreach (['walk_in' => 'In person', 'phone' => 'Phone', 'admin' => 'Staff observation'] as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $data['report_source'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select></label>
    <?php endif; ?>
    <label class="d-block mb-3">Incident type
        <select name="incident_type" class="form-select" required>
            <option value="">Choose type</option>
            <?php foreach (ReportController::TYPES as $value => $label): ?><option value="<?= e($value) ?>" <?= $data['incident_type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
        </select></label>
    <?php foreach (['reporter_name' => ['Reporter/contact name', 100], 'contact_phone' => ['Contact number', 20], 'location_description' => ['Location description / purok / nearby landmark', 255]] as $key => $field): ?>
        <label class="d-block mb-3"><?= e($field[0]) ?><input
                class="form-control"
                name="<?= e($key) ?>"
                maxlength="<?= $field[1] ?>"
                value="<?= e($data[$key]) ?>"
                required /></label>
    <?php endforeach; ?>
    <label class="d-block mb-3">Describe what happened<textarea
            class="form-control"
            name="description"
            maxlength="10000"
            required>
<?= e($data['description']) ?></textarea>
    </label>
    <?php $point = $data;
    require __DIR__ . '/../components/location-picker.php'; ?>
    <label class="d-block mb-3">Incident image (optional; JPEG, PNG, WebP; maximum 5 MiB)
        <input
            class="form-control"
            type="file"
            name="image"
            accept="image/jpeg,image/png,image/webp" /></label>
    <p>If a form error occurs, choose the image again before submitting.</p>
    <button class="btn btn-primary" name="action" value="create_incident">Submit report</button>
</form>
<?php require __DIR__ . '/../components/footer.php'; ?>