<?php
require_once __DIR__ . '/bootstrap.php';
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
