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
    public function listing(string $type, string $search, int $page): array
    {
        $types = $type === 'all' ? self::TYPES : [$type => $this->spec($type)];
        $queries = [];
        $values = [];
        $term = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        foreach ($types as $key => $spec) {
            [$table, $label] = $spec;
            $queries[] = "SELECT id, $label AS label, '$key' AS record_type FROM $table WHERE $label LIKE ? ESCAPE '!' OR CAST(id AS CHAR) LIKE ? ESCAPE '!'";
            $values[] = $term;
            $values[] = $term;
        }
        $offset = ($page - 1) * 50;
        return $this->query(implode(' UNION ALL ', $queries) . " ORDER BY record_type, id LIMIT 50 OFFSET $offset", $values);
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
            foreach ($links as [$table,$column])if ($this->linked($table, $column, $id))$blocked = 'has linked records and cannot be deleted';
        }
        if ($type === 'goods' && (int)$r['quantity_on_hand'] !== 0)$blocked = 'still has stock; record the proper stock transaction first';
        if ($blocked !== '')throw new DomainException(self::TYPES[$type][2] . ' #' . $id . ' ' . $blocked . '. No selected records were changed.');
    }
    public function deleteSelected(array $selection): void
    {
        $this->pdo->beginTransaction();
        try {
            // Check the entire batch before deleting anything, including mixed types.
            ksort($selection);
            foreach ($selection as $type => $ids) {
                foreach ($this->selected($type, $ids, true) as $row) {
                    $this->check($type, $row, 'delete');
                }
            }
            foreach ($selection as $type => $ids) {
                [$table] = $this->spec($type);
                $stmt = $this->pdo->prepare("DELETE FROM $table WHERE id=?");
                foreach ($ids as $id) {
                    $stmt->execute([$id]);
                }
            }
            $this->pdo->commit();
        } catch (Throwable $error) {
            $this->pdo->rollBack();
            throw $error;
        }
    }
}
