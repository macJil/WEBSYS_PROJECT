<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/db.php';

require_once __DIR__ . '/area-period.php';

const REPORT_TYPES = [
    'flood' => 'Flooding',
    'fire' => 'Fire',
    'landslide' => 'Landslide',
    'road_obstruction' => 'Road obstruction',
    'other' => 'Other emergency',
];
const REPORT_STATUSES = ['pending', 'verified', 'responding', 'resolved', 'rejected'];

function reportOwner(): ?int
{
    return $_SESSION['ROLE'] === 'admin' ? null : (int) $_SESSION['ID'];
}

function reportIncidentId(): int
{
    $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id || $id < 1) {
        http_response_code(400);
        exit('Invalid ID.');
    }
    return $id;
}

function reportStorage(): string
{
    global $settings;
    $path = $settings['incident_upload_dir'] ?? '';
    $root = dirname(__DIR__);
    if ($path === '' || $path[0] !== '/') {
        throw new RuntimeException('Configure an absolute private upload directory.');
    }
    if (!is_dir($path) && !mkdir($path, 0700, true)) {
        throw new RuntimeException('Cannot create upload directory.');
    }
    $real = realpath($path);
    if (!$real || $real === $root || str_starts_with($real, $root . DIRECTORY_SEPARATOR)) {
        throw new RuntimeException('Uploads must be outside the website directory.');
    }
    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    if (
        $documentRoot &&
        ($real === $documentRoot || str_starts_with($real, $documentRoot . DIRECTORY_SEPARATOR))
    ) {
        throw new RuntimeException('Uploads must be outside the web server document root.');
    }
    return $real;
}
