<?php
require_once __DIR__ . '/../../config/db.php';
class DashboardModel
{
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }
    private function rows(string $sql, array $args = []): array
    {
        $s = $this->pdo->prepare($sql);
        $s->execute($args);
        return $s->fetchAll();
    }
    public function incidents(?int $owner): array
    {
        $s = 'SELECT status,COUNT(*) AS total FROM incident_reports WHERE archived_at IS NULL';
        $args = [];
        if ($owner !== null) {
            $s .= ' AND reported_by=?';
            $args[] = $owner;
        }
        $counts = array_fill_keys(['pending','verified','responding','resolved','rejected'], 0);
        foreach ($this->rows($s . ' GROUP BY status', $args) as $r)$counts[$r['status']] = (int)$r['total'];
        return $counts;
    }
    public function centers(): array
    {
        return $this->rows("SELECT c.id,c.name,c.address_details,c.capacity,(SELECT COUNT(*) FROM evacuation_records e WHERE e.center_id=c.id AND e.checked_out_at IS NULL) AS occupancy FROM evacuation_centers c WHERE c.archived_at IS NULL AND c.status='open' ORDER BY c.name");
    }
    public function affected(): array
    {
        return $this->rows("SELECT id,household_code,representative_name FROM households WHERE archived_at IS NULL AND registry_reason IN ('affected','both') ORDER BY household_code");
    }
    public function stays(): array
    {
        return $this->rows('SELECT e.id,e.center_id,c.name AS center_name,COALESCE(m.full_name,e.walkin_name) AS person_name,e.checked_in_at FROM evacuation_records e JOIN evacuation_centers c ON c.id=e.center_id LEFT JOIN household_members m ON m.id=e.household_member_id WHERE e.checked_out_at IS NULL ORDER BY e.id');
    }
    public function notices(): array
    {
        return $this->rows('SELECT id,title,created_at FROM announcements WHERE is_published=1 AND archived_at IS NULL ORDER BY created_at DESC,id DESC');
    }
}
