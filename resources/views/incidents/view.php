<?php
require_once __DIR__ . "/../../../app/controllers/ReportController.php";
session_checker();
$controller = new ReportController($_SESSION["ROLE"]);
$id = $controller->incidentId();
$report = $controller->getIncident($id);
$admin = $_SESSION["ROLE"] === "admin";
if (isPost() && !$admin) {
    http_response_code(403);
    exit("Only staff may review incidents.");
}
if ($admin && isPost() && input("action") === "update_location") {
    $errors = $controller->updateLocation($id);
} else {
    $errors = $admin ? $controller->review($id) : [];
}
$images = $controller->images($id);
$history = $admin ? $controller->history($id) : [];
$reviewStatus = input('action') === 'review_incident' ? input('status') : $report['status'];
$pageTitle = "Incident #" . $id;
require __DIR__ . "/../components/header.php";
require __DIR__ . "/../components/errors.php";
?>
<h1>BayanReady incident report #<?= (int) $id ?></h1>
<p>Generated <?= e((new DateTimeImmutable('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d H:i:s')) ?> Asia/Manila.</p>
<p>Barangay: <?= e($report['barangay'] ?? 'Awaiting confirmation') ?></p>
<div class="table-responsive">
    <table class="table">
        <tbody>
            <tr>
                <th scope="row">Type</th>
                <td class="preserve-text"><?= e($report['incident_type'] ?? 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Status</th>
                <td class="preserve-text"><?= e($report['status'] ?? 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Description</th>
                <td class="preserve-text"><?= e($report['description'] ?? 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Reporter/contact name</th>
                <td class="preserve-text"><?= e($report['reporter_name'] ?? 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Contact email</th>
                <td><?= e($report['reporter_email'] ?? 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Contact number</th>
                <td class="preserve-text"><?= e($report['contact_phone'] ?? 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Location</th>
                <td class="preserve-text"><?= e($report['location_description'] ?? 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Latitude</th>
                <td class="preserve-text"><?= e($report['latitude'] ?? 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Longitude</th>
                <td class="preserve-text"><?= e($report['longitude'] ?? 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Received through</th>
                <td class="preserve-text"><?= e($report['report_source'] ?? 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Occurred</th>
                <td><?= e($report['occurred_at'] ?? 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Submitted</th>
                <td class="preserve-text"><?= e($report['created_at'] ?? 'Not recorded') ?></td>
            </tr>
        </tbody>
    </table>
</div>
<h2>Incident images</h2>
<?php if (!$images): ?>
    <p>No image attached.</p>
<?php endif; ?>
<?php foreach ($images as $image): ?>
    <a href="<?= e(url('resources/views/incidents/image.php?id=' . $image['id'])) ?>"><img class="incident-image" src="<?= e(url('resources/views/incidents/image.php?id=' . $image['id'])) ?>" alt="Image attached to this incident" /></a>
<?php endforeach; ?>
<?php if ($admin): ?>
    <div class="mb-3">
    <p class="mt-3"><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/response/incident.php?id=' . $id)) ?>">Manage response teams and actions</a></p>
    <?php if ($report['archived_at'] === null): ?>
        <h2>Correct incident location</h2>
        <p>Confirm the written location. A correction is recorded in staff history.</p>
        <form method="post">
    <?php
    $barangayPrompt = 'Awaiting confirmation';
    $selectedBarangay = isPost() ? input('barangay') : ($report['barangay'] ?? '');
    require __DIR__ . '/../components/barangay.php';
    ?>
            <label class="d-block mb-3">Written location<input class="form-control" name="location_description" maxlength="255" required value="<?= e(isPost() && input('action') === 'update_location'?input('location_description'):$report['location_description']) ?>" /></label>
            <?php
            $point = $report;
            if (isPost() && input("action") === "update_location") {
                $point["latitude"] = input("latitude");
                $point["longitude"] = input("longitude");
            }
            require __DIR__ . "/../components/location-fields.php";
            ?>
            <label class="d-block mb-3">Reason for correction<input class="form-control" name="location_notes" maxlength="255" required value="<?= e(input('location_notes')) ?>" /></label>
            <button class="btn btn-primary" name="action" value="update_location">Save location correction</button>
        </form>
    <?php endif; ?>
    <h2 class="mt-4">Staff review</h2>
    <p>You can assign a team directly to a pending incident. Active assignments keep the incident responding. Once teams are assigned, update their status through Manage response teams and actions. Completing every assignment resolves the incident automatically; cancelled work does not count as completed.</p>
    <form method="post">
        <label>Status <select class="form-select" name="status">
                <option value="pending" <?= $reviewStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="verified" <?= $reviewStatus === 'verified' ? 'selected' : '' ?>>Verified</option>
                <option value="responding" <?= $reviewStatus === 'responding' ? 'selected' : '' ?>>Responding</option>
                <option value="resolved" <?= $reviewStatus === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                <option value="rejected" <?= $reviewStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select></label>
        <label class="d-block my-3">Review notes / action taken<textarea class="form-control" name="notes" maxlength="255" required >
<?= e(input('notes')) ?></textarea> </label>
        <button name="action" value="review_incident" class="btn btn-primary">Save review</button>
    </form>
    </div>
    <h2 class="mt-4">Staff review history</h2>
    <?php foreach ($history as $entry): ?>
        <p><?= e($entry['created_at']) ?> · <?= e($entry['from_status']) ?> → <?= e($entry['to_status']) ?><br /><?= e($entry['action_taken']) ?></p>

    <?php endforeach; ?>
<?php endif; ?>
<a class="d-block mt-3" href="<?= e(url('resources/views/incidents/list.php')) ?>">Back to reports</a>
<?php
require __DIR__ . "/../components/footer.php";
?>
