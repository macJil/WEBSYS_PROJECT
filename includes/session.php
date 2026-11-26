<?php
$configFile = __DIR__ . '/../config/config.php';
if (is_file($configFile)) {
    require_once $configFile;
}
if (!isset($settings['base_url'], $settings['database'], $settings['app_name']) || !function_exists('db')) {
    http_response_code(503);
    exit('Setup required: check config/config.php and enter your local settings.');
}
require_once __DIR__ . '/form.php';
require_once __DIR__ . '/atok-boundary.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_name('ATOKMDRRM');
    session_start();
}

function url(string $path): string
{
    global $settings;
    return rtrim($settings['base_url'], '/') . '/' . ltrim($path, '/');
}
function redirect(string $path): void
{
    header('Location: ' . url($path), true, 303);
    exit();
}

function flash(string $message): void
{
    $_SESSION['message'] = ['text' => $message, 'type' => 'success'];
}
function dashboardPath(): string
{
    if (!empty($_SESSION['CHANGE_PASSWORD'])) {
        return 'change-password.php';
    }
    return ($_SESSION['ROLE'] ?? '') === 'admin'
        ? 'dashboard/staff.php'
        : 'dashboard/resident.php';
}
function requireLogin(): void
{
    if (empty($_SESSION['ID']) || !in_array($_SESSION['ROLE'] ?? '', ['admin', 'resident'], true)) {
        redirect('index.php');
    }
}
function requireCurrentPassword(): void
{
    requireLogin();
    if (!empty($_SESSION['CHANGE_PASSWORD'])) {
        redirect('change-password.php');
    }
}
function requireRole(string $role): void
{
    requireCurrentPassword();
    if ($_SESSION['ROLE'] !== $role) {
        http_response_code(403);
        exit('Access denied. This page is not available for your account.');
    }
}

set_exception_handler(function (Throwable $error): void {
    http_response_code(500);
    echo '<p>The request could not be completed. Please try again later.</p>';
});
