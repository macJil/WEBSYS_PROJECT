<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/db.php';

const RESPONSE_STATUSES = ['assigned', 'on_scene', 'completed', 'cancelled'];

function responseDbTeams(): array
{
    return db()->query('SELECT * FROM responders WHERE is_active=1 ORDER BY team_name')->fetchAll();
}

function responseDbAssignmentStatus(int $incidentId): ?string
{
    $stmt = db()->prepare('SELECT status FROM incident_assignments WHERE incident_id=?');
    $stmt->execute([$incidentId]);
    $statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!$statuses) {
        return null;
    }
    if (in_array('assigned', $statuses, true) || in_array('on_scene', $statuses, true)) {
        return 'responding';
    }
    foreach ($statuses as $status) {
        if ($status !== 'completed') {
            // Cancelled work is not evidence that the incident was resolved.
            return 'verified';
        }
    }
    return 'resolved';
}

function responseRecordId($value): int
{
    $id = is_scalar($value) ? filter_var($value, FILTER_VALIDATE_INT) : false;
    if (!$id || $id < 1) {
        http_response_code(400);
        exit('Invalid record ID.');
    }
    return $id;
}

// Caller holds the incident row lock and commits the assignment with its status history.
function responseAssignTeam(int $incidentId, int $teamId, int $staff): array
{
    $stmt = db()->prepare('SELECT * FROM responders WHERE id=? FOR UPDATE');
    $stmt->execute([$teamId]);
    $team = $stmt->fetch();
    if (!$team || !$team['is_active']) {
        throw new DomainException('Choose an active response team.');
    }
    $stmt = db()->prepare(
        'SELECT id FROM incident_assignments WHERE incident_id=? AND responder_id=?',
    );
    $stmt->execute([$incidentId, $teamId]);
    if ($stmt->fetchColumn()) {
        throw new DomainException(
            'This team already has an assignment. Update or reactivate that assignment below.',
        );
    }
    $stmt = db()->prepare(
        'INSERT INTO incident_assignments (incident_id,responder_id,assigned_by) VALUES (?,?,?)',
    );
    $stmt->execute([$incidentId, $teamId, $staff]);
    return [(int) db()->lastInsertId(), $team['team_name']];
}
