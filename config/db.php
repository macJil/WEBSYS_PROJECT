<?php
// Load local settings once, whether pages or models are loaded first.
if (!is_file(__DIR__ . '/config.php')) {
    http_response_code(503);
    exit('Setup required: copy setup/config.example.php to config/config.php and enter your local settings.');
}

$settings = require_once __DIR__ . '/config.php';

// This branch uses an array. Newer branches use the equivalent constants.
if (!is_array($settings)) {
    $settingNames = [
        'app_name' => 'APP_NAME',
        'base_url' => 'BASE_URL',
        'host' => 'DB_HOST',
        'port' => 'DB_PORT',
        'database' => 'DB_NAME',
        'username' => 'DB_USER',
        'password' => 'DB_PASSWORD',
        'incident_upload_dir' => 'INCIDENT_UPLOAD_DIR',
        'demo_barangay_name' => 'DEMO_BARANGAY_NAME',
        'map_latitude' => 'MAP_LATITUDE',
        'map_longitude' => 'MAP_LONGITUDE',
        'map_zoom' => 'MAP_ZOOM',
        'map_min_zoom' => 'MAP_MIN_ZOOM',
        'address_lookup_url' => 'ADDRESS_LOOKUP_URL',
    ];
    $settings = [];
    foreach ($settingNames as $key => $constantName) {
        if (defined($constantName)) {
            $settings[$key] = constant($constantName);
        }
    }
}

foreach (['app_name', 'base_url', 'host', 'port', 'database', 'username', 'password'] as $key) {
    if (!isset($settings[$key]) || !is_scalar($settings[$key])) {
        http_response_code(503);
        exit('Setup required: check the application and database settings in config/config.php.');
    }
}

// Optional settings retain the defaults used by this branch's example.
$settings += [
    'incident_upload_dir' => '',
    'demo_barangay_name' => 'Atok, Benguet (DEMO)',
    'map_latitude' => 16.6272,
    'map_longitude' => 120.7672,
    'map_zoom' => 14,
    'map_min_zoom' => 11,
    'address_lookup_url' => 'https://photon.komoot.io/reverse',
];

if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim((string) $settings['base_url'], '/'));
}

$databaseConnection = null;

function db(): PDO
{
    global $databaseConnection, $settings;
    if ($databaseConnection === null) {
        $dsn = "mysql:host={$settings['host']};port={$settings['port']};dbname={$settings['database']};charset=utf8mb4";
        $databaseConnection = new PDO($dsn, $settings['username'], $settings['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $databaseConnection;
}
