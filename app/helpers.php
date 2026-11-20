<?php
require_once __DIR__ . '/barangays.php';
if (!is_file(__DIR__ . '/../config/config.php')) {
    http_response_code(503);
    exit(
        'Setup required: copy config/config.example.php to config/config.php and enter your local settings.'
    );
}
require_once __DIR__ . '/../config/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_start();
}

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path), true, 303);
    exit();
}

function isPost(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
}

function input(string $key, bool $trim = true): string
{
    $value = $_POST[$key] ?? '';
    if (!is_string($value)) {
        return '';
    }
    if ($trim) {
        return trim($value);
    }
    return $value;
}

function validDateInput(string $value): bool
{
    if (strlen($value) !== 10 || $value[4] !== '-' || $value[7] !== '-') {
        return false;
    }
    if (strspn($value, '0123456789-') !== 10) {
        return false;
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

function validPhone(string $value): bool
{
    // Both formats have nine digits after the mobile prefix.
    if (str_starts_with($value, '09') && strlen($value) === 11) {
        return strspn($value, '0123456789', 2) === 9;
    }
    if (str_starts_with($value, '+639') && strlen($value) === 13) {
        return strspn($value, '0123456789', 4) === 9;
    }
    return false;
}

function flash(string $message): void
{
    $_SESSION['message'] = ['text' => $message, 'type' => 'success'];
}

function dashboardPath(): string
{
    return ($_SESSION['ROLE'] ?? '') === 'admin'
        ? 'resources/views/admin/admin.index.php'
        : 'resources/views/user/user.index.php';
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
// Last-resort handling for legacy pages; models always return data or throw.
set_exception_handler(function (Throwable $error): void {
    http_response_code(500);
    echo '<p>The request could not be completed. Please try again later.</p>';
});

function locationErrors(string $description, string $lat, string $lng): array
{
    if ($lat === '' && $lng === '') {
        return [];
    }
    if ($lat === '' || $lng === '') {
        return ['Enter both coordinates or clear both.'];
    }
    $point = ['latitude' => $lat, 'longitude' => $lng];
    if (!hasMapPoint($point) || trim($description) === '') {
        return ['Enter valid coordinates and a written location.'];
    }
    return [];
}

// Match the map's coordinate rules without treating zero as a missing value.
function hasMapPoint(array $record): bool
{
    $lat = $record['latitude'] ?? null;
    $lng = $record['longitude'] ?? null;
    if (!is_numeric($lat) || !is_numeric($lng)) {
        return false;
    }
    return abs((float) $lat) <= 90 && abs((float) $lng) <= 180;
}
