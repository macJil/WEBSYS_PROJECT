<?php
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
