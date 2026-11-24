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
        'SELECT id, action_taken, from_status, to_status, created_at FROM response_updates WHERE incident_id = ? ORDER BY id DESC',
    );
    $stmt->execute([$id]);
    return $stmt->fetchAll();
}

function reportDbUpdateLocation(
    int $id,
    ?string $barangay,
    string $description,
    ?string $lat,
    ?string $lng,
    string $notes,
    int $staff,
): void {
    db()->beginTransaction();
    try {
        $stmt = db()->prepare(
            'SELECT barangay,location_description,latitude,longitude,status,archived_at FROM incident_reports WHERE id=? FOR UPDATE',
        );
        $stmt->execute([$id]);
        $old = $stmt->fetch();
        if (!$old || $old['archived_at'] !== null) {
            throw new DomainException('Archived or missing incidents cannot be edited.');
        }
        $stmt = db()->prepare(
            'UPDATE incident_reports SET barangay=?,location_description=?,latitude=?,longitude=? WHERE id=?',
        );
        $stmt->execute([$barangay, $description, $lat, $lng, $id]);
        $previousPoint = '[' . ($old['latitude'] ?? 'none') . ', ' . ($old['longitude'] ?? 'none') . ']';
        $updatedPoint = '[' . ($lat ?? 'none') . ', ' . ($lng ?? 'none') . ']';
        $action = 'Location correction: ' . $notes;
        $action .= ' | Barangay: ' . ($old['barangay'] ?? 'Unconfirmed') . ' → ' . ($barangay ?? 'Unconfirmed');
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

function reportDbReview(int $id, string $action, string $notes, int $staff, ?int $teamId): void
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
        $allowed = [
            'pending' => ['verify_assign', 'verify_only', 'reject'],
            'verified' => ['resolve'],
            'resolved' => ['reopen'],
            'rejected' => ['reopen'],
        ];
        if (!in_array($action, $allowed[$old] ?? [], true)) {
            throw new DomainException('This incident has already changed. Review its current status.');
        }
        if (responseDbAssignmentStatus($id) !== null) {
            throw new DomainException('Use the team assignments to update this incident.');
        }
        $status = 'verified';
        if ($action === 'reject') {
            $status = 'rejected';
        } elseif ($action === 'resolve') {
            $status = 'resolved';
        }
        $stmt = db()->prepare(
            'UPDATE incident_reports SET status=?, reviewed_by=?, reviewed_at=CURRENT_TIMESTAMP, review_notes=? WHERE id=?',
        );
        $stmt->execute([$status, $staff, $notes, $id]);
        $stmt = db()->prepare(
            'INSERT INTO response_updates (incident_id,updated_by,action_taken,from_status,to_status) VALUES (?,?,?,?,?)',
        );
        $stmt->execute([$id, $staff, $notes, $old, $status]);
        if ($action === 'verify_assign') {
            [$assignmentId, $teamName] = responseAssignTeam($id, $teamId, $staff);
            $stmt = db()->prepare("UPDATE incident_reports SET status='responding' WHERE id=?");
            $stmt->execute([$id]);
            $stmt = db()->prepare("INSERT INTO response_updates
                (incident_id, updated_by, action_taken, from_status, to_status, assignment_id, assignment_status)
                VALUES (?, ?, ?, 'verified', 'responding', ?, 'assigned')");
            $stmt->execute([$id, $staff, 'Dispatched ' . $teamName . '.', $assignmentId]);
        }
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
    $barangay = isset($_POST['barangay']) ? input('barangay') : (reportGetIncident($id)['barangay'] ?? '');
    if ($barangay !== '' && !in_array($barangay, ATOK_BARANGAYS, true)) {
        $errors[] = 'Choose an Atok barangay or leave it awaiting confirmation.';
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
            $barangay === '' ? null : $barangay,
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
    $action = input('action');
    $notes = input('notes');
    if ($notes === '' || strlen($notes) > 255) {
        return ['Enter review notes (maximum 255 bytes).'];
    }
    $teamId = null;
    if ($action === 'verify_assign') {
        $teamId = filter_var(input('team_id'), FILTER_VALIDATE_INT);
        if (!$teamId || $teamId < 1) {
            return ['Choose an active team to verify and assign.'];
        }
    }
    try {
        reportDbReview($id, $action, $notes, (int) $_SESSION['ID'], $teamId);
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
require __DIR__ . "/../includes/header.php";
require __DIR__ . "/../includes/errors.php";
?>
<h1><?= e($pageTitle) ?></h1>
<a class="btn btn-outline-secondary no-print" href="<?= e(url('incidents/print.php?id=' . $id)) ?>">Printable incident report</a>
<dl>
    <?php foreach (['incident_type' => 'Type','status' => 'Status','description' => 'Description','reporter_name' => 'Reporter/contact name','contact_phone' => 'Contact number','barangay' => 'Incident barangay','location_description' => 'Location','latitude' => 'Latitude','longitude' => 'Longitude','report_source' => 'Received through','created_at' => 'Submitted'] as $key => $label): ?>
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
    <link rel="stylesheet" href="<?= e(url('assets/vendor/leaflet/leaflet.css')) ?>" />
    <script src="<?= e(url('assets/vendor/leaflet/leaflet.js')) ?>"></script>
    <script src="<?= e(url('assets/js/incident-location.js')) ?>"></script>
<?php endif; ?>

<h2>Incident images</h2>
<?php if (!$images): ?>
    <p>No image attached.</p>
<?php endif; ?>
<?php foreach ($images as $image): ?>
    <a href="<?= e(url('incidents/image.php?id=' . $image['id'])) ?>"><img class="incident-image" src="<?= e(url('incidents/image.php?id=' . $image['id'])) ?>" alt="Image attached to this incident" /></a>
<?php endforeach; ?>
<?php if ($admin): ?>
    <p class="mt-3"><a class="btn btn-outline-secondary" href="<?= e(url('response/incident.php?id=' . $id)) ?>">Manage team assignments</a></p>
    <?php if ($report['archived_at'] === null): ?>
        <h2>Correct incident location</h2>
        <p>Confirm the written location and pin. A correction is recorded in staff history. The shared map uses this saved point when the incident is eligible.</p>
        <form method="post">
            <?php
            $selectedBarangay = input('action') === 'update_location' ? input('barangay') : ($report['barangay'] ?? '');
            require __DIR__ . '/barangay-field.php';
            ?>
            <label class="d-block mb-3">Written location<input class="form-control" name="location_description" maxlength="255" required value="<?= e(isPost() && input('action') === 'update_location'?input('location_description'):$report['location_description']) ?>" /></label>
            <?php
            $point = $report;
            if (isPost() && input("action") === "update_location") {
                $point["latitude"] = input("latitude");
                $point["longitude"] = input("longitude");
            }
            require __DIR__ . "/../includes/location-picker.php";
            ?>
            <label class="d-block mb-3">Reason for correction<input class="form-control" name="location_notes" maxlength="255" required value="<?= e(input('location_notes')) ?>" /></label>
            <button class="btn btn-primary" name="action" value="update_location">Save location correction</button>
        </form>
    <?php endif; ?>
    <h2 class="mt-4">Staff review</h2>
    <?php if ($report['archived_at'] === null && $report['status'] === 'pending'): ?>
        <form method="post">
            <label class="d-block">Response team for dispatch
                <select class="form-select" name="team_id">
                    <option value="">Choose a team for Verify and assign</option>
                    <?php foreach (responseDbTeams() as $team): ?>
                        <option value="<?= (int) $team['id'] ?>" <?= input('team_id') === (string) $team['id'] ? 'selected' : '' ?>><?= e($team['team_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="d-block my-3">Review notes
                <textarea class="form-control" name="notes" maxlength="255" required><?= e(input('notes')) ?></textarea>
            </label>
            <button name="action" value="verify_assign" class="btn btn-primary">Verify and assign</button>
            <button name="action" value="verify_only" class="btn btn-outline-secondary">Verify only</button>
            <button name="action" value="reject" class="btn btn-outline-danger">Reject</button>
        </form>
    <?php elseif ($report['archived_at'] === null && responseDbAssignmentStatus($id) !== null): ?>
        <p>Team updates determine the incident status.</p>
    <?php elseif ($report['archived_at'] === null && in_array($report['status'], ['verified', 'resolved', 'rejected'], true)): ?>
        <form method="post">
            <label class="d-block my-3">Reason
                <textarea class="form-control" name="notes" maxlength="255" required><?= e(input('notes')) ?></textarea>
            </label>
            <?php if ($report['status'] === 'verified'): ?>
                <button class="btn btn-outline-secondary" name="action" value="resolve">Resolve without dispatch</button>
            <?php else: ?>
                <button class="btn btn-outline-secondary" name="action" value="reopen">Reopen as verified</button>
            <?php endif; ?>
        </form>
    <?php endif; ?>
    <h2 class="mt-4">Staff review log</h2>
    <?php if (!$history): ?><p>No staff updates yet.</p><?php endif; ?>
    <?php foreach ($history as $entry): ?>
        <article class="border-bottom py-2">
            <strong><?= e(ucfirst($entry['to_status'] ?? 'Update')) ?></strong> · <?= e($entry['created_at']) ?>
            <?php if ($entry['from_status'] && $entry['from_status'] !== $entry['to_status']): ?>
                <small>(previously <?= e($entry['from_status']) ?>)</small>
            <?php endif; ?>
            <p class="preserve-text"><?= e($entry['action_taken']) ?></p>
        </article>
    <?php endforeach; ?>
<?php endif; ?>
<a class="d-block mt-3" href="<?= e(url('incidents/list.php')) ?>">Back to reports</a>
<?php
require __DIR__ . "/../includes/footer.php";
?>
