<?php
$settings = require __DIR__ . '/../config/settings.php';
define('BASE_URL', rtrim($settings['base_url'], '/'));

function start_app_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_start();
    }
}
start_app_session();

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function url(string $path): string { return BASE_URL . '/' . ltrim($path, '/'); }
function redirect(string $path): void
{
    header('Location: ' . url($path), true, 303);
    exit;
}
function isPost(): bool { return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'; }
function input(string $key, bool $trim = true): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? ($trim ? trim($value) : $value) : '';
}
function validDateInput(string $value): bool
{
    // Reject malformed bytes before PHP's date parser, which can throw on null bytes.
    if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $value)) { return false; }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}
function flash(string $message, string $type = 'success'): void
{
    $_SESSION['message'] = ['text' => $message, 'type' => $type === 'success' ? 'success' : 'danger'];
}
function dashboardPath(): string
{
    return ($_SESSION['ROLE'] ?? '') === 'admin'
        ? 'resources/views/admin/admin.index.php' : 'resources/views/user/user.index.php';
}
function session_checker(): void
{
    if (empty($_SESSION['ID']) || !in_array($_SESSION['ROLE'] ?? '', ['admin', 'resident'], true)) {
        redirect('index.php');
    }
}
function requireRole(string $role): void
{
    session_checker();
    if ($_SESSION['ROLE'] !== $role) {
        http_response_code(403);
        exit('Access denied. This page is not available for your account.');
    }
    if (isPost() && (input('action') === 'logout' || isset($_POST['logout']))) {
        $_SESSION = [];
        session_destroy();
        redirect('index.php');
    }
}
function content_checker($data): string
{
    return empty($data) ? '<p>No records found.</p>' : 'hidden';
}
// Last-resort handling for legacy pages; models always return data or throw.
set_exception_handler(function (Throwable $error): void {
    error_log((string) $error);
    http_response_code(500);
    echo '<p>The request could not be completed. Please try again later.</p>';
});

function locationErrors(string $description, string $lat, string $lng): array
{
    if (($lat === '') !== ($lng === '')) { return ['Enter both coordinates or clear both.']; }
    if ($lat !== '' && (!is_numeric($lat) || !is_numeric($lng) || !is_finite((float)$lat) || !is_finite((float)$lng) || abs((float)$lat)>90 || abs((float)$lng)>180 || trim($description)==='')) { return ['Enter valid coordinates and a written location.']; }
    return [];
}
