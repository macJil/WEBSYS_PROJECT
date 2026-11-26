<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/evacuation-capacity.php';
function mapRows(string $sql): array
{
    return db()->query($sql)->fetchAll();
}

function mapPoint(string $lat, string $lng): string
{
    return "$lat IS NOT NULL AND $lng IS NOT NULL AND $lat BETWEEN -90 AND 90 AND $lng BETWEEN -180 AND 180";
}

function mapPublicMap(): array
{
    // Explicit allowlists: never select private incident fields for the resident response.
    return [
        'incidents' => mapRows(
            "SELECT id,incident_type,latitude,longitude,status,DATE(created_at) AS report_date FROM incident_reports WHERE archived_at IS NULL AND status IN ('verified','responding','resolved') AND " .
                mapPoint('latitude', 'longitude'),
        ),
        'centers' => mapCenters(),
        'relief_locations' => mapRows(
            "SELECT id,name,address_details,latitude,longitude,location_type FROM relief_locations WHERE archived_at IS NULL AND location_type IN ('distribution','both') AND " .
                mapPoint('latitude', 'longitude'),
        ),
    ];
}

function mapCenters(): array
{
    $centers = mapRows(
        "SELECT c.id, c.name, c.address_details, c.latitude, c.longitude,
        c.status, c.capacity, COUNT(e.id) AS occupancy
        FROM evacuation_centers c
        LEFT JOIN evacuation_records e ON e.center_id = c.id AND e.checked_out_at IS NULL
        WHERE c.archived_at IS NULL AND c.status = 'open' AND " .
            mapPoint('c.latitude', 'c.longitude') .
            "
        GROUP BY c.id, c.name, c.address_details, c.latitude, c.longitude, c.status, c.capacity",
    );
    foreach ($centers as $index => $center) {
        $centers[$index]['available'] = availableCapacity($center);
    }
    return $centers;
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
if (empty($_SESSION['ID']) || !in_array($_SESSION['ROLE'] ?? '', ['admin', 'resident'], true)) {
    http_response_code(401);
    echo json_encode(['error' => 'Sign in to view map data.']);
    exit();
}
if (!empty($_SESSION['CHANGE_PASSWORD'])) {
    http_response_code(403);
    exit(json_encode(['error' => 'Choose your password before using the map.']));
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['error' => 'Use GET.']);
    exit();
}
try {
    $data = mapPublicMap();
    if ($_SESSION['ROLE'] === 'admin') {
        $data['households'] = mapRows(
            'SELECT id, latitude, longitude, registry_reason, hazard_type, evacuation_status
             FROM households WHERE archived_at IS NULL AND ' . mapPoint('latitude', 'longitude')
        );
    }
    echo json_encode($data, JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['error' => 'Map data is unavailable.']);
}
