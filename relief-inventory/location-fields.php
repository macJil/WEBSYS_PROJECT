<?php
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../config/db.php';

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
