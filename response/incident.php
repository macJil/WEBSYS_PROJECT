<?php
require_once __DIR__ . '/common.php';
requireRole('admin');

function responseDbIncident(int $id)
{
    $stmt = db()->prepare('SELECT * FROM incident_reports WHERE id=?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function responseDbAssignments(int $id): array
{
    $stmt = db()->prepare(
        'SELECT a.*, r.team_name, r.contact_phone, r.is_active, u.full_name AS assigned_by_name FROM incident_assignments a JOIN responders r ON r.id=a.responder_id JOIN users u ON u.id=a.assigned_by WHERE a.incident_id=? ORDER BY a.id',
    );
    $stmt->execute([$id]);
    return $stmt->fetchAll();
}

function responseDbHistory(int $id): array
{
    $stmt = db()->prepare(
        'SELECT h.*, u.full_name AS staff_name FROM response_updates h JOIN users u ON u.id=h.updated_by WHERE h.incident_id=? ORDER BY h.id DESC',
    );
    $stmt->execute([$id]);
    return $stmt->fetchAll();
}

function responseDbRecord(
    int $incidentId,
    ?int $teamId,
    ?int $assignmentId,
    string $status,
    string $notes,
    int $staff,
): void {
    db()->beginTransaction();
    try {
        $stmt = db()->prepare(
            'SELECT status FROM incident_reports WHERE id=? AND archived_at IS NULL FOR UPDATE',
        );
        $stmt->execute([$incidentId]);
        $old = $stmt->fetchColumn();
        if (!in_array($old, ['verified', 'responding', 'resolved'], true)) {
            throw new DomainException('Verify or reopen the incident before managing its response.');
        }
        if ($teamId !== null) {
            [$assignmentId, $teamName] = responseAssignTeam($incidentId, $teamId, $staff);
            $status = 'assigned';
            $action = 'Assigned team ' . $teamName . '. ' . $notes;
        } else {
            $stmt = db()->prepare(
                'SELECT a.*, r.team_name, r.is_active FROM incident_assignments a JOIN responders r ON r.id=a.responder_id WHERE a.id=? AND a.incident_id=? FOR UPDATE',
            );
            $stmt->execute([$assignmentId, $incidentId]);
            $assignment = $stmt->fetch();
            if (!$assignment) {
                throw new DomainException('Assignment does not belong to this incident.');
            }
            $allowed = [
                'assigned' => ['assigned', 'completed', 'cancelled'],
                'on_scene' => ['assigned', 'completed', 'cancelled'],
                'completed' => ['completed', 'assigned'],
                'cancelled' => ['cancelled', 'assigned'],
            ];
            if (!in_array($status, $allowed[$assignment['status']], true)) {
                throw new DomainException(
                    'Invalid assignment transition. Reactivate a closed assignment as assigned first.',
                );
            }
            if (
                $status === 'assigned' &&
                in_array($assignment['status'], ['completed', 'cancelled'], true) &&
                !$assignment['is_active']
            ) {
                throw new DomainException('Reactivate the response team before assigning it again.');
            }
            $stmt = db()->prepare(
                'UPDATE incident_assignments SET status=? WHERE id=? AND incident_id=?',
            );
            $stmt->execute([$status, $assignmentId, $incidentId]);
            $action =
                $assignment['team_name'] . ': ' . $assignment['status'] . ' → ' . $status . '. ' . $notes;
        }
        // One update saves both the team state and the incident's overall state.
        $next = responseDbAssignmentStatus($incidentId);
        $stmt = db()->prepare('UPDATE incident_reports SET status=? WHERE id=?');
        $stmt->execute([$next, $incidentId]);
        $stmt = db()->prepare(
            'INSERT INTO response_updates (incident_id,updated_by,action_taken,from_status,to_status,assignment_id,assignment_status) VALUES (?,?,?,?,?,?,?)',
        );
        $stmt->execute([$incidentId, $staff, $action, $old, $next, $assignmentId, $status]);
        db()->commit();
    } catch (Throwable $error) {
        db()->rollBack();
        throw $error;
    }
}

function responseIncident(int $id): array
{
    $row = responseDbIncident($id);
    if (!$row) {
        http_response_code(404);
        exit('Incident not found.');
    }
    return $row;
}



function responseRecord(int $id): array
{
    if (!isPost()) {
        return [];
    }
    $action = input('action');
    $notes = input('notes');
    if (!in_array($action, ['assign_team', 'update_assignment'], true)) {
        return ['Unknown form action.'];
    }
    if ($notes === '' || mb_strlen($notes) > 2000) {
        return ['Describe the action taken (maximum 2,000 characters).'];
    }
    $teamId = $action === 'assign_team' ? responseRecordId(input('team_id')) : null;
    $assignmentId = $action === 'update_assignment' ? responseRecordId(input('assignment_id')) : null;
    $status = $action === 'assign_team' ? 'assigned' : input('assignment_status');
    if (!in_array($status, RESPONSE_STATUSES, true)) {
        return ['Choose a valid assignment status.'];
    }
    try {
        responseDbRecord($id, $teamId, $assignmentId, $status, $notes, (int) $_SESSION['ID']);
    } catch (DomainException $error) {
        return [$error->getMessage()];
    } catch (Throwable $error) {
        return ['Response update could not be saved. Please try again.'];
    }
    flash('Response update recorded.');
    redirect('response/incident.php?id=' . $id);
    return [];
}

$id = responseRecordId($_GET["id"] ?? null);
$incident = responseIncident($id);
$errors = responseRecord($id);
$assignments = responseDbAssignments($id);
$history = responseDbHistory($id);
$assignedTeamIds = array_column($assignments, "responder_id");
$teams = array_filter(
    responseDbTeams(),
    fn($team) => $team["is_active"] && !in_array($team["id"], $assignedTeamIds),
);
$open = $incident['archived_at'] === null && in_array($incident["status"], ["verified", "responding", "resolved"], true);
$pageTitle = "Response for incident #" . $id;
require __DIR__ . "/../includes/header.php";
require __DIR__ . "/../includes/errors.php";
?>
<h1><?= e($pageTitle) ?></h1>
<p><?= e($incident['incident_type']) ?> · <?= e($incident['location_description']) ?> · Status: <strong><?= e($incident['status']) ?></strong></p>
<a class="btn btn-outline-secondary" href="<?= e(url('incidents/view.php?id=' . $id)) ?>">Back to incident details</a>
<p>Verify an incident before assigning teams. Dispatch changes it to responding. Completing every assignment resolves the incident automatically. A cancelled assignment does not count as completed. If no team is active and some work is cancelled, the incident returns to verified. Reactivating an assignment starts responding again.</p>
<?php if ($incident['status'] === 'verified' && in_array('cancelled', array_column($assignments, 'status'), true)): ?>
    <p class="alert alert-info">Staff decision needed after cancellation. Keep this incident verified while awaiting action, assign another team, or reactivate the cancelled assignment. Automatic resolution requires every assignment to be completed.</p>
<?php endif; ?>
<?php if (!$open): ?>
    <p class="alert alert-info">Verify or reopen this incident in staff review before adding response updates.</p>
<?php endif; ?>
<?php if ($open && $teams): ?>
    <h2>Assign a team</h2>
    <form method="post" class="mb-4">
        <label class="d-block">Team<select class="form-select" name="team_id" required>
                <option value="">Choose a team</option>
                <?php foreach ($teams as $team): ?>
                    <option value="<?= (int)$team['id'] ?>" <?= input('team_id') === (string)$team['id'] ? 'selected' : '' ?>><?= e($team['team_name']) ?> (active: <?= (int)$team['workload'] ?>)</option>
                <?php endforeach; ?>
            </select></label>
        <label class="d-block my-2">Dispatch instructions / action taken<textarea class="form-control" name="notes" maxlength="2000" required >
<?= input('action') === 'assign_team' ? e(input('notes')) : '' ?></textarea> </label>
        <button class="btn btn-primary" name="action" value="assign_team">Assign team</button>
        <a href="<?= e(url('incidents/view.php?id=' . $id)) ?>">Cancel and return to incident details</a>
    </form>
<?php elseif ($open): ?>
    <p>No unassigned active teams. <a href="<?= e(url('response/teams.php')) ?>">Manage teams</a> or reactivate an existing assignment below.</p>
<?php endif; ?>
<h2>Assigned teams</h2>
<?php if (!$assignments): ?>
    <p>No teams assigned.</p>
<?php endif; ?>
<?php foreach ($assignments as $assignment): ?>
    <?php $currentStatus = $assignment['status'] === 'on_scene' ? 'assigned' : $assignment['status']; ?>
    <section class="border rounded p-3 mb-3">
        <h3><?= e($assignment['team_name']) ?></h3>
        <p><?= e($assignment['contact_phone']) ?> · <?= $assignment['is_active'] ? 'Active team' : 'Inactive team' ?> · Assignment: <?= e($currentStatus) ?><br />Originally assigned by <?= e($assignment['assigned_by_name']) ?> at <?= e($assignment['assigned_at']) ?></p>
        <?php if ($open): $retry = input('action') === 'update_assignment' && input('assignment_id') === (string)$assignment['id']; ?>
        <form method="post">
            <input type="hidden" name="assignment_id" value="<?= (int)$assignment['id'] ?>" />
            <label>Status<select class="form-select" name="assignment_status">
                    <?php foreach (RESPONSE_STATUSES as $status): ?>
                        <option value="<?= e($status) ?>" <?= ($retry ? input('assignment_status') : $currentStatus) === $status ? 'selected' : '' ?>><?= e(str_replace('_', ' ', ucfirst($status))) ?></option>
                    <?php endforeach; ?>
                </select></label>
            <label class="d-block my-2">Action taken<textarea class="form-control" name="notes" maxlength="2000" required>
<?= $retry ? e(input('notes')) : '' ?></textarea> </label>
            <button class="btn btn-primary" name="action" value="update_assignment">Save team and incident status</button>
        </form>
    <?php endif; ?>
</section>
<?php endforeach; ?>
<h2>Response log</h2>
<?php if (!$history): ?>
    <p>No updates yet.</p>
<?php endif; ?>
<?php foreach ($history as $entry): ?>
    <article class="border-bottom py-2"><strong><?= e($entry['staff_name']) ?></strong> · <?= e($entry['created_at']) ?><br />Incident: <?= e($entry['from_status']) ?> → <?= e($entry['to_status']) ?>
        <?php if ($entry['assignment_id']): ?>
            · Assignment #<?= (int)$entry['assignment_id'] ?>: <?= e($entry['assignment_status']) ?>
        <?php endif; ?>
        <p class="preserve-text"><?= e($entry['action_taken']) ?></p>
    </article>
<?php endforeach; ?>
<?php
require __DIR__ . "/../includes/footer.php";
?>
