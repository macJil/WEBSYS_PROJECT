<?php
require_once __DIR__ . '/common.php';
session_checker();

function reportDbImage(int $id, ?int $owner)
{
    $stmt = db()->prepare(
        'SELECT i.* FROM incident_images i JOIN incident_reports r ON r.id = i.incident_id WHERE i.id = ?' .
            ($owner !== null ? ' AND r.reported_by = ?' : ''),
    );
    $stmt->execute($owner !== null ? [$id, $owner] : [$id]);
    return $stmt->fetch();
}

function reportServeImage(int $id): void
{
    $image = reportDbImage($id, reportOwner());
    $filename = $image['stored_filename'] ?? '';
    $parts = explode('.', $filename);
    if (
        count($parts) !== 2 ||
        strlen($parts[0]) !== 48 ||
        strspn($parts[0], 'abcdef0123456789') !== 48 ||
        !in_array($parts[1], ['jpg', 'png', 'webp'], true)
    ) {
        http_response_code(404);
        exit('Image not found.');
    }
    $path = reportStorage() . '/' . $image['stored_filename'];
    if (!is_file($path)) {
        http_response_code(404);
        exit('Image not found.');
    }
    header('Content-Type: ' . $image['mime_type']);
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, no-store');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit();
}

reportServeImage(reportIncidentId());
