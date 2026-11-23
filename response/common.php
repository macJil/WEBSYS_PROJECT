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
