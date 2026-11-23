<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/evacuation-capacity.php';

function dashboardDbRows(string $sql, array $args = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($args);
    return $stmt->fetchAll();
}

function dashboardDbIncidents(?int $owner): array
{
    $sql = 'SELECT status, COUNT(*) AS total FROM incident_reports WHERE archived_at IS NULL';
    $args = [];
    if ($owner !== null) {
        $sql .= ' AND reported_by = ?';
        $args[] = $owner;
    }
    $counts = array_fill_keys(['pending', 'verified', 'responding', 'resolved', 'rejected'], 0);
    foreach (dashboardDbRows($sql . ' GROUP BY status', $args) as $row) {
        $counts[$row['status']] = (int) $row['total'];
    }
    return $counts;
}

function dashboardDbCenters(): array
{
    $centers = dashboardDbRows("SELECT c.id, c.name, c.address_details, c.status, c.capacity,
        (SELECT COUNT(*) FROM evacuation_records e
         WHERE e.center_id = c.id AND e.checked_out_at IS NULL) AS occupancy
        FROM evacuation_centers c
        WHERE c.archived_at IS NULL AND c.status = 'open'
        ORDER BY c.name, c.id");
    foreach ($centers as $index => $center) {
        $centers[$index]['available'] = availableCapacity($center);
    }
    return $centers;
}

function dashboardDbAffected(): array
{
    return dashboardDbRows(
        "SELECT id,household_code,representative_name FROM households WHERE archived_at IS NULL AND registry_reason IN ('affected','both') ORDER BY household_code",
    );
}

function dashboardDbStays(): array
{
    return dashboardDbRows(
        'SELECT e.id,e.center_id,c.name AS center_name,COALESCE(m.full_name,e.walkin_name) AS person_name,e.checked_in_at FROM evacuation_records e JOIN evacuation_centers c ON c.id=e.center_id LEFT JOIN household_members m ON m.id=e.household_member_id WHERE e.checked_out_at IS NULL ORDER BY e.id',
    );
}

function dashboardDbNotices(): array
{
    return dashboardDbRows(
        'SELECT id,title,created_at FROM announcements WHERE is_published=1 AND archived_at IS NULL ORDER BY created_at DESC,id DESC',
    );
}

function dashboardDashboardData(): array
{
    $admin = $_SESSION['ROLE'] === 'admin';
    $data = [
        'incidents' => dashboardDbIncidents($admin ? null : (int) $_SESSION['ID']),
        'centers' => dashboardDbCenters(),
    ];
    if ($admin) {
        $data['affected'] = dashboardDbAffected();
        $data['stays'] = dashboardDbStays();
    } else {
        $data['notices'] = dashboardDbNotices();
    }
    return $data;
}
