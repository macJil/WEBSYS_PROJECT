<?php
require_once __DIR__ . '/../includes/barangays.php';

const INCIDENT_PERIODS = ['all' => 'All dates', 'today' => 'Today', 'week' => 'Last 7 days', 'month' => 'Last 30 days'];

function incidentScope(): array
{
    $area = $_GET['barangay'] ?? '';
    $period = $_GET['period'] ?? 'all';
    if (!in_array($period, array_keys(INCIDENT_PERIODS), true) ||
        !in_array($area, array_merge(['', 'unknown'], ATOK_BARANGAYS), true)) {
        http_response_code(400);
        exit('Invalid incident period or barangay.');
    }
    return ['period' => $period, 'barangay' => $area];
}

function incidentScopeConditions(array $scope, array &$where, array &$values): void
{
    if ($scope['barangay'] === 'unknown') {
        $where[] = 'barangay IS NULL';
    } elseif ($scope['barangay'] !== '') {
        $where[] = 'barangay = ?';
        $values[] = $scope['barangay'];
    }
    if ($scope['period'] !== 'all') {
        $daysBack = ['today' => 0, 'week' => 6, 'month' => 29][$scope['period']];
        $where[] = "created_at >= CURRENT_DATE - INTERVAL $daysBack DAY AND created_at < CURRENT_DATE + INTERVAL 1 DAY";
    }
}
