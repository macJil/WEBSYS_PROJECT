<?php
require_once __DIR__ . '/common.php';
session_checker();
require_once __DIR__ . '/../response/common.php';

function reportDbFind(int $id, ?int $owner)
{
    $stmt = db()->prepare(
        'SELECT * FROM incident_reports WHERE id = ?' . ($owner !== null ? ' AND reported_by = ?' : ''),
    );
    $stmt->execute($owner !== null ? [$id, $owner] : [$id]);
    return $stmt->fetch();
}

function reportDbImages(int $id): array
{
    $stmt = db()->prepare('SELECT id FROM incident_images WHERE incident_id = ? ORDER BY id');
    $stmt->execute([$id]);
    return $stmt->fetchAll();
}

function reportDbHistory(int $id): array
{
    $stmt = db()->prepare(
        'SELECT id, action_taken, from_status, to_status, created_at FROM response_updates WHERE incident_id = ? AND history_hidden_at IS NULL ORDER BY id',
    );
    $stmt->execute([$id]);
    return $stmt->fetchAll();
}

function reportDbUpdateLocation(
    int $id,
    string $description,
    ?string $lat,
    ?string $lng,
    string $notes,
    int $staff,
): void {
    db()->beginTransaction();
    try {
        $stmt = db()->prepare(
            'SELECT location_description,latitude,longitude,status,archived_at FROM incident_reports WHERE id=? FOR UPDATE',
        );
        $stmt->execute([$id]);
        $old = $stmt->fetch();
        if (!$old || $old['archived_at'] !== null) {
            throw new DomainException('Archived or missing incidents cannot be edited.');
        }
        $stmt = db()->prepare(
            'UPDATE incident_reports SET location_description=?,latitude=?,longitude=? WHERE id=?',
        );
        $stmt->execute([$description, $lat, $lng, $id]);
        $previousPoint = '[' . ($old['latitude'] ?? 'none') . ', ' . ($old['longitude'] ?? 'none') . ']';
        $updatedPoint = '[' . ($lat ?? 'none') . ', ' . ($lng ?? 'none') . ']';
        $action = 'Location correction: ' . $notes;
        $action .= ' | Previous: ' . $old['location_description'] . ' ' . $previousPoint;
        $action .= ' | Updated: ' . $description . ' ' . $updatedPoint;
        $stmt = db()->prepare(
            'INSERT INTO response_updates(incident_id,updated_by,action_taken,from_status,to_status) VALUES(?,?,?,?,?)',
        );
        $stmt->execute([$id, $staff, $action, $old['status'], $old['status']]);
        db()->commit();
    } catch (Throwable $error) {
        db()->rollBack();
        throw $error;
    }
}

function reportDbReview(int $id, string $status, string $notes, int $staff): void
{
    db()->beginTransaction();
    try {
        $stmt = db()->prepare(
            'SELECT status FROM incident_reports WHERE id = ? AND archived_at IS NULL FOR UPDATE',
        );
        $stmt->execute([$id]);
        $old = $stmt->fetchColumn();
        if ($old === false) {
            throw new RuntimeException('Incident not found.');
        }
        $assignmentStatus = responseDbAssignmentStatus($id);
        if ($assignmentStatus !== null && $status !== $assignmentStatus) {
            throw new DomainException('Update the assigned teams to change this incident status. All assignments must be completed to resolve it.');
        }
        if ($assignmentStatus === null && $status === 'responding') {
            throw new DomainException('Verify the incident, then assign a team to start responding.');
        }
        $stmt = db()->prepare(
            'UPDATE incident_reports SET status=?, reviewed_by=?, reviewed_at=CURRENT_TIMESTAMP, review_notes=? WHERE id=?',
        );
        $stmt->execute([$status, $staff, $notes, $id]);
        $stmt = db()->prepare(
            'INSERT INTO response_updates (incident_id,updated_by,action_taken,from_status,to_status) VALUES (?,?,?,?,?)',
        );
        $stmt->execute([$id, $staff, $notes, $old, $status]);
        db()->commit();
    } catch (Throwable $error) {
        db()->rollBack();
        throw $error;
    }
}

function reportGetIncident(int $id): array
{
    $row = reportDbFind($id, reportOwner());
    if (!$row) {
        http_response_code(404);
        exit('Incident not found.');
    }
    return $row;
}

function reportImages(int $id): array
{
    reportGetIncident($id);
    return reportDbImages($id);
}

function reportHistory(int $id): array
{
    requireRole('admin');
    return reportDbHistory($id);
}

function reportUpdateLocation(int $id): array
{
    requireRole('admin');
    $description = input('location_description');
    $lat = input('latitude');
    $lng = input('longitude');
    $errors = locationErrors($description, $lat, $lng);
    if ($description === '' || strlen($description) > 255) {
        $errors[] = 'Enter a written location of up to 255 bytes.';
    }
    $notes = input('location_notes');
    if ($notes === '' || strlen($notes) > 255) {
        $errors[] = 'Enter a correction reason of up to 255 bytes.';
    }
    if ($errors) {
        return $errors;
    }
    try {
        reportDbUpdateLocation(
            $id,
            $description,
            $lat === '' ? null : $lat,
            $lng === '' ? null : $lng,
            $notes,
            (int) $_SESSION['ID'],
        );
    } catch (DomainException $error) {
        return [$error->getMessage()];
    } catch (Throwable $error) {
        return ['Location could not be saved. Please try again.'];
    }
    flash('Incident location updated.');
    redirect('incidents/view.php?id=' . $id);
    return [];
}

function reportReview(int $id): array
{
    requireRole('admin');
    if (!isPost()) {
        return [];
    }
    if (
        input('action') !== 'review_incident' ||
        !in_array(input('status'), REPORT_STATUSES, true) ||
        input('notes') === '' ||
        strlen(input('notes')) > 255
    ) {
        return ['Choose a status and enter review notes (maximum 255 bytes).'];
    }
    try {
        reportDbReview($id, input('status'), input('notes'), (int) $_SESSION['ID']);
    } catch (DomainException $error) {
        return [$error->getMessage()];
    } catch (Throwable $error) {
        return ['Review could not be saved. Please try again.'];
    }
    flash('Review saved.');
    redirect('incidents/view.php?id=' . $id);
    return [];
}

$id = reportIncidentId();
$report = reportGetIncident($id);
$admin = $_SESSION["ROLE"] === "admin";
if (isPost() && !$admin) {
    http_response_code(403);
    exit("Only staff may review incidents.");
}
if ($admin && isPost() && input("action") === "update_location") {
    $errors = reportUpdateLocation($id);
} else {
    $errors = $admin ? reportReview($id) : [];
}
$images = reportImages($id);
$history = $admin ? reportHistory($id) : [];
$pageTitle = "Incident #" . $id;
require __DIR__ . "/../resources/views/components/header.php";
require __DIR__ . "/../resources/views/components/errors.php";
?>
<h1><?= e($pageTitle) ?></h1>
<dl>
    <?php foreach (['incident_type' => 'Type','status' => 'Status','description' => 'Description','reporter_name' => 'Reporter/contact name','contact_phone' => 'Contact number','location_description' => 'Location','latitude' => 'Latitude','longitude' => 'Longitude','report_source' => 'Received through','created_at' => 'Submitted'] as $key => $label): ?>
        <dt><?= e($label) ?></dt>
        <dd class="preserve-text"><?= e($report[$key] ?? 'Not recorded') ?></dd>
    <?php endforeach; ?>
</dl>
<?php if (!hasMapPoint($report)): ?>
    <p>No map point. This report remains available in lists and totals.</p>
<?php endif; ?>
<?php if ((!$admin || $report['archived_at'] !== null) && hasMapPoint($report)): ?>
    <div id="saved-incident-map" data-lat="<?= e($report['latitude']) ?>" data-lng="<?= e($report['longitude']) ?>">
    </div>
    <p id="saved-map-status">Saved coordinates are listed above. Map tiles need internet.</p>
    <link rel="stylesheet" href="<?= e(url('resources/assets/vendor/leaflet/leaflet.css')) ?>" />
    <script src="<?= e(url('resources/assets/vendor/leaflet/leaflet.js')) ?>"></script>
    <script src="<?= e(url('resources/assets/js/incident-location.js')) ?>"></script>
<?php endif; ?>
<p>Locations are reported points, not verified hazard assessments. No approved barangay boundary or location restriction is applied. DEMO ONLY locations are fictional examples.</p>
<h2>Incident images</h2>
<?php if (!$images): ?>
    <p>No image attached.</p>
<?php endif; ?>
<?php foreach ($images as $image): ?>
    <a href="<?= e(url('incidents/image.php?id=' . $image['id'])) ?>"><img class="incident-image" src="<?= e(url('incidents/image.php?id=' . $image['id'])) ?>" alt="Image attached to this incident" /></a>
<?php endforeach; ?>
<?php if ($admin): ?>
    <p class="mt-3"><a class="btn btn-outline-secondary" href="<?= e(url('response/incident.php?id=' . $id)) ?>">Manage response teams and actions</a></p>
    <?php if ($report['archived_at'] === null): ?>
        <h2>Correct incident location</h2>
        <p>Confirm the written location and pin. A correction is recorded in staff history. The shared map uses this saved point when the incident is eligible.</p>
        <form method="post">
            <label class="d-block mb-3">Written location<input class="form-control" name="location_description" maxlength="255" required value="<?= e(isPost() && input('action') === 'update_location'?input('location_description'):$report['location_description']) ?>" /></label>
            <?php
            $point = $report;
            if (isPost() && input("action") === "update_location") {
                $point["latitude"] = input("latitude");
                $point["longitude"] = input("longitude");
            }
            require __DIR__ . "/../resources/views/components/location-picker.php";
            ?>
            <label class="d-block mb-3">Reason for correction<input class="form-control" name="location_notes" maxlength="255" required value="<?= e(input('location_notes')) ?>" /></label>
            <button class="btn btn-primary" name="action" value="update_location">Save location correction</button>
        </form>
    <?php endif; ?>
    <h2 class="mt-4">Staff review</h2>
    <p>Verify before assigning teams. Active assignments keep the incident responding. Once teams are assigned, update their status through Manage response teams and actions. Completing every assignment resolves the incident automatically; cancelled work does not count as completed.</p>
    <form method="post">
        <label>Status <select class="form-select" name="status">
                <?php foreach (REPORT_STATUSES as $status): ?>
                    <option value="<?= e($status) ?>" <?= (isPost() && input('action') === 'review_incident' ? input('status') : $report['status']) === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                <?php endforeach; ?>
            </select></label>
        <label class="d-block my-3">Review notes / action taken<textarea class="form-control" name="notes" maxlength="255" required >
<?= e(input('notes')) ?></textarea> </label>
        <button name="action" value="review_incident" class="btn btn-primary">Save review</button>
    </form>
    <h2 class="mt-4">Staff review history</h2>
    <?php foreach ($history as $entry): ?>
        <p><?= e($entry['created_at']) ?> · <?= e($entry['from_status']) ?> → <?= e($entry['to_status']) ?><br /><?= e($entry['action_taken']) ?></p>
        <?php
        $historyType = 'response';
        $historyId = $entry['id'];
        require __DIR__ . '/../resources/views/components/history-action.php';
        ?>
    <?php endforeach; ?>
<?php endif; ?>
<a class="d-block mt-3" href="<?= e(url('incidents/list.php')) ?>">Back to reports</a>
<?php
require __DIR__ . "/../resources/views/components/footer.php";
?>
