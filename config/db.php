<?php
// Load local settings once, whether pages or models are loaded first.
if (!is_file(__DIR__ . '/config.php')) {
    http_response_code(503);
    exit('Setup required: copy setup/config.example.php to config/config.php and enter your local settings.');
}

require_once __DIR__ . '/config.php';

if (!defined('DB_HOST') || !defined('BASE_URL') || !defined('APP_NAME')) {
    http_response_code(503);
    exit('Setup required: use the constants in setup/config.example.php and keep your local values.');
}

$databaseConnection = null;

function db(): PDO
{
    global $databaseConnection;
    if ($databaseConnection === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $databaseConnection = new PDO($dsn, DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $databaseConnection;
}
