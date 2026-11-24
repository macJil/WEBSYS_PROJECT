<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/atok-boundary.php';

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
    global $settings;
    return rtrim($settings['base_url'], '/') . '/' . ltrim($path, '/');
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
    return is_string($value) ? ($trim ? trim($value) : $value) : '';
}
function validDateInput(string $value): bool
{
    // Reject malformed bytes before PHP's date parser, which can throw on null bytes.
    if (
        strlen($value) !== 10 ||
        $value[4] !== '-' ||
        $value[7] !== '-' ||
        !ctype_digit(str_replace('-', '', $value))
    ) {
        return false;
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}
function validPhone(string $value, int $minimum): bool
{
    $length = strlen($value);
    return $length >= $minimum && $length <= 20 && strspn($value, '0123456789+() .-') === $length;
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
function session_checker(): void
{
    requireLogin();
    if (!empty($_SESSION['CHANGE_PASSWORD'])) {
        redirect('change-password.php');
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
    if (!is_string($_POST['latitude'] ?? '') || !is_string($_POST['longitude'] ?? '')) {
        return ['Enter a numeric coordinate pair or clear both fields.'];
    }
    if (($lat === '') !== ($lng === '')) {
        return ['Enter both coordinates or clear both.'];
    }
    if (
        $lat !== '' &&
        (!is_numeric($lat) ||
            !is_numeric($lng) ||
            !is_finite((float) $lat) ||
            !is_finite((float) $lng) ||
            abs((float) $lat) > 90 ||
            abs((float) $lng) > 180 ||
            trim($description) === '')
    ) {
        return ['Enter valid coordinates and a written location.'];
    }
    if ($lat !== '' && !insideAtok(round((float) $lat, 7), round((float) $lng, 7))) {
        return ['Choose a point inside the approximate Atok demo boundary, or enter the address without a pin.'];
    }
    return [];
}

// Match the map's coordinate rules without treating zero as a missing value.
function hasMapPoint(array $record): bool
{
    $lat = $record['latitude'] ?? null;
    $lng = $record['longitude'] ?? null;
    return is_numeric($lat) &&
        is_numeric($lng) &&
        is_finite((float) $lat) &&
        is_finite((float) $lng) &&
        abs((float) $lat) <= 90 &&
        abs((float) $lng) <= 180;
}

function statusBadge(string $status): string
{
    return '<span class="status-badge" data-status="' . e($status) . '">' . e(ucfirst($status)) . '</span>';
}

function relativeTime(string $value): string
{
    // Compare timestamps in the database's clock, including on a differently configured PHP host.
    static $databaseNow;
    $databaseNow ??= strtotime(db()->query('SELECT NOW()')->fetchColumn());
    $stamp = strtotime($value);
    if ($stamp === false) {
        return e($value);
    }
    $seconds = $databaseNow - $stamp;
    $label = date('M j, Y', $stamp);
    if ($seconds >= 0 && $seconds < 60) {
        $label = 'just now';
    } elseif ($seconds >= 60 && $seconds < 2592000) {
        foreach ([86400 => 'day', 3600 => 'hour', 60 => 'minute'] as $unit => $name) {
            if ($seconds >= $unit) {
                $count = intdiv($seconds, $unit);
                $label = $count . ' ' . $name . ($count === 1 ? '' : 's') . ' ago';
                break;
            }
        }
    }
    return '<time title="' . e($value) . '">' . $label . '</time>';
}

function capacityBar(int $occupancy, int $capacity): string
{
    $percent = $capacity > 0 ? min(100, round(100 * $occupancy / $capacity)) : 0;
    $color = $percent >= 100 ? 'danger' : ($percent >= 85 ? 'warning' : 'success');
    return '<div class="progress" aria-hidden="true"><div class="progress-bar bg-' . $color .
        '" style="width:' . $percent . '%"></div></div>';
}
