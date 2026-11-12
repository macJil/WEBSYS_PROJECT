<?php
$databaseConnection = null;

function db(): PDO
{
    global $databaseConnection;
    if ($databaseConnection === null) {
        if (!is_file(__DIR__ . '/config.php')) {
            http_response_code(503);
            exit('Setup required: copy config/config.example.php to config/config.php and enter your local settings.');
        }
        $settings = require __DIR__ . '/config.php';
        $dsn = "mysql:host={$settings['host']};port={$settings['port']};dbname={$settings['database']};charset=utf8mb4";
        $databaseConnection = new PDO($dsn, $settings['username'], $settings['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $databaseConnection;
}
