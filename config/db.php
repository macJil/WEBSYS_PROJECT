<?php
class DATABASE
{
    private static ?PDO $connection = null;

    public function connect(): PDO
    {
        if (self::$connection === null) {
            if (!is_file(__DIR__ . '/config.php')) {
                http_response_code(503);
                exit('Setup required: copy config/config.example.php to config/config.php and enter your local settings.');
            }
            $settings = require __DIR__ . '/config.php';
            $dsn = "mysql:host={$settings['host']};port={$settings['port']};dbname={$settings['database']};charset=utf8mb4";
            self::$connection = new PDO($dsn, $settings['username'], $settings['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }
        return self::$connection;
    }
}
