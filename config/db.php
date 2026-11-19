<?php
$databaseConnection = null;

function db(): PDO
{
    global $databaseConnection;
    if ($databaseConnection === null) {
        if (!is_file(__DIR__ . '/config.php')) {
            http_response_code(503);
            exit(
                'Setup required: copy config/config.example.php to config/config.php and enter your local settings.'
            );
        }
        require_once __DIR__ . '/config.php';
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $databaseConnection = new PDO($dsn, DB_USER, DB_PASSWORD, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $error) {
            http_response_code(503);
            exit('Database unavailable. Check your local database service and connection settings.');
        }
    }
    return $databaseConnection;
}
