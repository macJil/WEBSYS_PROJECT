<?php
require_once __DIR__ . '/../includes/session.php';

function reliefDbRows(string $sql, array $args = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($args);
    return $stmt->fetchAll();
}

function reliefDbTerm(string $q): string
{
    return '%' . strtr($q, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
}

function reliefItems(string $q = ''): array
{
    return reliefDbRows(
        "SELECT * FROM relief_items WHERE archived_at IS NULL AND (item_name LIKE ? ESCAPE '!' OR unit LIKE ? ESCAPE '!') ORDER BY item_name,unit",
        [reliefDbTerm($q), reliefDbTerm($q)],
    );
}

function reliefDbMovement(
    int $item,
    string $type,
    int $quantity,
    ?int $distribution,
    int $staff,
    string $notes,
): void {
    $stmt = db()->prepare(
        'INSERT INTO relief_stock_movements (item_id,movement_type,quantity,distribution_id,recorded_by,notes) VALUES (?,?,?,?,?,?)',
    );
    $stmt->execute([$item, $type, $quantity, $distribution, $staff, $notes]);
}

function reliefRecordId($value): int
{
    $id = is_scalar($value) ? filter_var($value, FILTER_VALIDATE_INT) : false;
    if (!$id || $id < 1) {
        http_response_code(400);
        exit('Invalid ID.');
    }
    return $id;
}

function reliefQueryValue(string $key): string
{
    return is_string($_GET[$key] ?? null) ? trim($_GET[$key]) : '';
}

function reliefFailure(Throwable $e): array
{
    if ($e instanceof DomainException) {
        return [$e->getMessage()];
    }
    if ($e instanceof PDOException && ($e->errorInfo[1] ?? null) === 1062) {
        return ['That item name and unit already exist.'];
    }
    return ['Change could not be saved. Please try again.'];
}

function reliefQuantity($value)
{
    return is_scalar($value)
        ? filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 1000000000],
        ])
        : false;
}

function reliefLocationDbFind(int $id): array
{
    $stmt = db()->prepare('SELECT * FROM relief_locations WHERE id=?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: [];
}

function reliefLocationFind(int $id): array
{
    $location = reliefLocationDbFind($id);
    if (!$location) {
        http_response_code(404);
        exit('Location not found.');
    }
    return $location;
}
