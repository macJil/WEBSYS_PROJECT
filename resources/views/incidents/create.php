<?php
require_once __DIR__ . "/../../../app/controllers/ReportController.php";
session_checker();
$controller = new ReportController($_SESSION["ROLE"]);
$data = $controller->values();
if (!isPost()) {
    $data["reporter_name"] = $_SESSION["FULL_NAME"] ?? "";
}
$errors = $controller->create($data);
$pageTitle = "Report incident";
require __DIR__ . "/../components/header.php";
require __DIR__ . "/../components/errors.php";
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/incidents/list.php')) ?>">Back to reports</a></p>
<h1>Report an incident</h1>
<p>Reports are reviewed by barangay staff. This website does not guarantee an immediate emergency response.</p>
<form method="post" enctype="multipart/form-data">
    <?php if ($_SESSION['ROLE'] === 'admin'): ?>
        <label class="d-block mb-3">Report received through <select name="report_source" class="form-select" required>
                <option value="walk_in" <?= $data['report_source'] === 'walk_in' ? 'selected' : '' ?>>In person</option>
                <option value="phone" <?= $data['report_source'] === 'phone' ? 'selected' : '' ?>>Phone</option>
                <option value="admin" <?= $data['report_source'] === 'admin' ? 'selected' : '' ?>>Staff observation</option>
            </select></label>
    <?php endif; ?>
    <label class="d-block mb-3">Incident type <select name="incident_type" class="form-select" required>
            <option value="">Choose type</option>
            <option value="flood" <?= $data['incident_type'] === 'flood' ? 'selected' : '' ?>>Flooding</option>
            <option value="fire" <?= $data['incident_type'] === 'fire' ? 'selected' : '' ?>>Fire</option>
            <option value="landslide" <?= $data['incident_type'] === 'landslide' ? 'selected' : '' ?>>Landslide</option>
            <option value="road_obstruction" <?= $data['incident_type'] === 'road_obstruction' ? 'selected' : '' ?>>Road obstruction</option>
            <option value="other" <?= $data['incident_type'] === 'other' ? 'selected' : '' ?>>Other emergency</option>
        </select></label>
    <label class="d-block mb-3">Reporter/contact name
        <input
            class="form-control"
            name="reporter_name"
            maxlength="100"
            value="<?= e($data['reporter_name']) ?>"
            required
        />
    </label>
    <label class="d-block mb-3">Contact number
        <input
            class="form-control"
            name="contact_phone"
            maxlength="20"
            value="<?= e($data['contact_phone']) ?>"
            required
        />
    </label>
    <label class="d-block mb-3">Location description / purok / nearby landmark
        <input
            class="form-control"
            name="location_description"
            maxlength="255"
            value="<?= e($data['location_description']) ?>"
            required
        />
    </label>
    <label class="d-block mb-3">Describe what happened<textarea class="form-control" name="description" maxlength="10000" required >
<?= e($data['description']) ?></textarea> </label>
    <?php
    $point = $data;
    require __DIR__ . "/../components/location-picker.php";
    ?>
    <label class="d-block mb-3">Incident image (optional; JPEG, PNG, WebP; maximum 5 MiB) <input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp" /></label>
    <p>If a form error occurs, choose the image again before submitting.</p>
    <button class="btn btn-primary" name="action" value="create_incident">Submit report</button>
    <a href="<?= e(url('resources/views/incidents/list.php')) ?>">Cancel and return to reports</a>
</form>
<?php
require __DIR__ . "/../components/footer.php";
?>
