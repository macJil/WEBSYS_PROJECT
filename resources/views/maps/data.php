<?php
require_once __DIR__ . '/../../../app/controllers/MapController.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
if (empty($_SESSION['ID']) || !in_array($_SESSION['ROLE'] ?? '', ['admin','resident'], true)) {
    http_response_code(401);
    echo json_encode(['error' => 'Sign in to view map data.']);
    exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['error' => 'Use GET.']);
    exit;
}
try {
    echo json_encode((new MapController())->data(), JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    error_log((string)$error);
    http_response_code(500);
    echo json_encode(['error' => 'Map data is unavailable.']);
}
