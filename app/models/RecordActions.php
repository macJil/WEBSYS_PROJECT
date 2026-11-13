<?php
require_once __DIR__ . '/../../config/db.php';
class RecordActionsModel
{
    // SQL identifiers come exclusively from this fixed application allowlist.
    public const TYPES = [
        'households' => ['households','household_code','Households','resources/views/admin/household.php?id='],
        'members' => ['household_members','full_name','Household members',''],
        'incidents' => ['incident_reports','location_description','Incidents','resources/views/incidents/view.php?id='],
        'teams' => ['responders','team_name','Response teams','resources/views/admin/response/teams.php?edit='],
        'centers' => ['evacuation_centers','name','Evacuation centers','resources/views/admin/evacuation/center.php?id='],
        'walkins' => ['evacuation_walkins','full_name','Walk-ins',''],
        'goods' => ['relief_items','item_name','Relief goods','resources/views/admin/relief/inventory.php?edit='],
        'locations' => ['relief_locations','name','Relief locations','resources/views/admin/relief/location.php?id='],
        'announcements' => ['announcements','title','Announcements','resources/views/admin/announcement/announcement.update.php?id=']
    ];
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }
    private function query(string $sql, array $args = []): array
    {
        $s = $this->pdo->prepare($sql);
        $s->execute($args);
        return $s->fetchAll();
    }
    private function spec(string $type): array
    {
        if (!isset(self::TYPES[$type]))throw new DomainException('Unsupported record type.');
        return self::TYPES[$type];
    }
    public function listing(string $type, string $search, bool $archived, int $page): array
    {
        [$table,$label] = $this->spec($type);
        $state = $type === 'teams'?($archived?'is_active=0':'is_active=1'):($archived?'archived_at IS NOT NULL':'archived_at IS NULL');
        $term = '%' . strtr($search, ['!' => '!!','%' => '!%','_' => '!_']) . '%';
        $offset = ($page - 1) * 50;
        return $this->query("SELECT * FROM $table WHERE $state AND ($label LIKE ? ESCAPE '!' OR CAST(id AS CHAR) LIKE ? ESCAPE '!') ORDER BY id LIMIT 50 OFFSET $offset", [$term,$term]);
    }
    public function selected(string $type, array $ids, bool $lock = false): array
    {
        [$table] = $this->spec($type);
        sort($ids, SORT_NUMERIC);
        $rows = $this->query("SELECT * FROM $table WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ') ORDER BY id' . ($lock?' FOR UPDATE':''), $ids);
        if (count($rows) !== count($ids))throw new DomainException('A selected record no longer exists. Reload the list.');
        return $rows;
    }
    private function linked(string $table, string $column, int $id, string $extra = ''): bool
    {
        // All callers use literal identifiers and conditions defined below.
        return (bool)$this->query("SELECT id FROM $table WHERE $column=? $extra LIMIT 1 FOR UPDATE", [$id]);
    }
    private function check(string $type, array $r, string $action): void
    {
        $id = (int)$r['id'];
        $blocked = '';
        if ($action === 'delete') {
            $links = match ($type) {
                'households' => [['household_members','household_id'],['relief_distributions','household_id']],
                'members' => [['evacuation_records','household_member_id']],
                'incidents' => [['incident_images','incident_id'],['incident_assignments','incident_id'],['response_updates','incident_id']],
                'teams' => [['incident_assignments','responder_id']],
                'centers' => [['evacuation_records','center_id']],
                'walkins' => [['evacuation_records','walkin_id']],
                'goods' => [['relief_stock_movements','item_id'],['relief_distribution_items','item_id']],
                'locations' => [['relief_distributions','relief_location_id']],default => []
            };
            foreach ($links as [$table,$column])if ($this->linked($table, $column, $id))$blocked = 'has linked records; use archive/deactivate instead';
            if ($type === 'announcements' && ($r['is_published'] || $r['first_published_at'] !== null || $r['publication_history_unknown']))$blocked = 'has published or unknown publication history; archive it instead';
        } else {
            if ($type === 'households') {
                $members = $this->query('SELECT id FROM household_members WHERE household_id=? ORDER BY id FOR UPDATE', [$id]);
                foreach ($members as $member)if ($this->linked('evacuation_records', 'household_member_id', (int)$member['id'], 'AND checked_out_at IS NULL'))$blocked = 'has a member currently checked in';
            }
            if ($type === 'members' && $this->linked('evacuation_records', 'household_member_id', $id, 'AND checked_out_at IS NULL'))$blocked = 'is currently checked in';
            if ($type === 'walkins' && $this->linked('evacuation_records', 'walkin_id', $id, 'AND checked_out_at IS NULL'))$blocked = 'is currently checked in';
            if ($type === 'centers' && $this->linked('evacuation_records', 'center_id', $id, 'AND checked_out_at IS NULL'))$blocked = 'still has occupants';
            if ($type === 'incidents' && (!in_array($r['status'], ['resolved','rejected'], true) || $this->linked('incident_assignments', 'incident_id', $id, "AND status IN ('assigned','on_scene')")))$blocked = 'must be resolved/rejected with no active assignments';
        }
        if ($type === 'goods' && (int)$r['quantity_on_hand'] !== 0)$blocked = 'still has stock; record the proper stock transaction first';
        if ($blocked !== '')throw new DomainException(self::TYPES[$type][2] . ' #' . $id . ' ' . $blocked . '. No selected records were changed.');
    }
    public function apply(string $type, array $ids, string $action, array $expected): void
    {
        [$table] = $this->spec($type);
        if (!in_array($action, ['delete','archive'], true))throw new DomainException('Unsupported action.');
        $this->pdo->beginTransaction();
        try {
            $rows = $this->selected($type, $ids, true);
            foreach ($rows as $row) {
                if (($expected[(int)$row['id']] ?? '') !== hash('sha256', json_encode($row)))throw new DomainException('Record #' . $row['id'] . ' changed after review. Reload and review again. No records changed.');
                $this->check($type, $row, $action);
            }
            foreach ($rows as $row) {
                $id = (int)$row['id'];
                if ($action === 'delete') {
                    $s = $this->pdo->prepare("DELETE FROM $table WHERE id=?");
                } else {
                    $set = $type === 'teams'?'is_active=0':'archived_at=COALESCE(archived_at,CURRENT_TIMESTAMP)';
                    if ($type === 'centers')$set .= ",status='closed'";
                    if ($type === 'announcements')$set .= ',is_published=0';
                    $s = $this->pdo->prepare("UPDATE $table SET $set WHERE id=?");
                }
                $s->execute([$id]);
            }
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
