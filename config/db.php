<?php
// Load local settings once, whether pages or models are loaded first.
if (!is_file(__DIR__ . '/config.php')) {
    http_response_code(503);
    exit('Setup required: copy setup/config.example.php to config/installation.php and enter your local settings.');
}

$settings = require __DIR__ . '/config.php';
if (!is_array($settings) || !isset($settings['base_url'], $settings['database'], $settings['app_name'])) {
    http_response_code(503);
    exit('Setup required: use the settings array in setup/config.example.php.');
}

// Older local installations still have the previous working title in their settings.
if ($settings['app_name'] === 'BayanReady') {
    $settings['app_name'] = 'Atok MDRRM System';
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
