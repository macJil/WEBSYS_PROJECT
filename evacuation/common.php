<?php
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/evacuation-capacity.php';


function evacuationDbRows(string $sql, array $values = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($values);
    return $stmt->fetchAll();
}

function evacuationDbCenter(int $id): array
{
    $rows = evacuationDbRows(
        'SELECT c.*, (SELECT COUNT(*) FROM evacuation_records e WHERE e.center_id=c.id AND e.checked_out_at IS NULL) AS occupancy FROM evacuation_centers c WHERE c.id=?',
        [$id],
    );
    if (!$rows) {
        return [];
    }
    $center = $rows[0];
    $center['available'] = availableCapacity($center);
    return $center;
}

function evacuationDbLockCenter(int $id): array
{
    $rows = evacuationDbRows('SELECT * FROM evacuation_centers WHERE id=? FOR UPDATE', [$id]);
    if (!$rows) {
        throw new DomainException('Center not found.');
    }
    return $rows[0];
}

function evacuationDbOccupancy(int $id): int
{
    // Current locking read, including changes committed while waiting for the center lock.
    return count(
        evacuationDbRows(
            'SELECT id FROM evacuation_records WHERE center_id=? AND checked_out_at IS NULL FOR UPDATE',
            [$id],
        ),
    );
}

function evacuationRecordId($value): int
{
    $id = is_scalar($value) ? filter_var($value, FILTER_VALIDATE_INT) : false;
    if (!$id || $id < 1) {
        http_response_code(400);
        exit('Invalid ID.');
    }
    return $id;
}

function evacuationCenter(int $id): array
{
    $row = evacuationDbCenter($id);
    if (!$row) {
        http_response_code(404);
        exit('Center not found.');
    }
    return $row;
}

function evacuationFailure(Throwable $e): array
{
    if ($e instanceof DomainException) {
        return [$e->getMessage()];
    }
    if ($e instanceof PDOException && ($e->errorInfo[1] ?? null) === 1062) {
        return ['This person already has an active check-in. Check out the existing stay first.'];
    }
    return ['Could not save the change. Please try again.'];
}
