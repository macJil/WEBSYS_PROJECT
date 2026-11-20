<?php
require_once __DIR__ . '/../../config/db.php';
class RecordActionsModel
{
    // SQL identifiers come exclusively from this fixed application allowlist.
    public const TYPES = [
        'households' => [
            'households',
            'household_code',
            'Households',
            'resources/views/admin/household.php?id=',
        ],
        'members' => ['household_members', 'full_name', 'Household members', ''],
        'incidents' => [
            'incident_reports',
            'location_description',
            'Incidents',
            'resources/views/incidents/view.php?id=',
        ],
        'teams' => [
            'responders',
            'team_name',
            'Response teams',
            'resources/views/admin/response/teams.php?edit=',
        ],
        'centers' => [
            'evacuation_centers',
            'name',
            'Evacuation centers',
            'resources/views/admin/evacuation/center.php?id=',
        ],
        'walkins' => ['evacuation_walkins', 'full_name', 'Walk-ins', ''],
        'goods' => [
            'relief_items',
            'item_name',
            'Relief goods',
            'resources/views/admin/relief/inventory.php?edit=',
        ],
        'locations' => [
            'relief_locations',
            'name',
            'Relief locations',
            'resources/views/admin/relief/location.php?id=',
        ],
        'announcements' => [
            'announcements',
            'title',
            'Announcements',
            'resources/views/admin/announcement/announcement.update.php?id=',
        ],
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
        if (!isset(self::TYPES[$type])) {
            throw new DomainException('Unsupported record type.');
        }
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
            if ($key === 'households') {
                $table = 'household_directory';
                $label = 'household_label';
            }
            $state = $key === 'teams' ? 'is_active=1' : 'archived_at IS NULL';
            $queries[] = "SELECT id, $label AS label, '$key' AS record_type FROM $table WHERE $state AND ($label LIKE ? ESCAPE '!' OR CAST(id AS CHAR) LIKE ? ESCAPE '!')";
            $values[] = $term;
            $values[] = $term;
        }
        $offset = ($page - 1) * 50;
        return $this->query(
            implode(' UNION ALL ', $queries) . " ORDER BY record_type, id LIMIT 50 OFFSET $offset",
            $values,
        );
    }

    public function selected(string $type, array $ids, bool $lock = false): array
    {
        [$table] = $this->spec($type);
        sort($ids, SORT_NUMERIC);
        $rows = $this->query(
            "SELECT * FROM $table WHERE id IN (" .
                implode(',', array_fill(0, count($ids), '?')) .
                ') ORDER BY id' .
                ($lock ? ' FOR UPDATE' : ''),
            $ids,
        );
        if (count($rows) !== count($ids)) {
            throw new DomainException('A selected record no longer exists. Reload the list.');
        }
        return $rows;
    }

    private function linked(string $table, string $column, int $id, string $extra = ''): bool
    {
        // All callers use literal identifiers and conditions defined below.
        return (bool) $this->query("SELECT id FROM $table WHERE $column=? $extra LIMIT 1 FOR UPDATE", [$id]);
    }

    private function checkRemoval(string $type, array $row): void
    {
        $id = (int) $row['id'];
        $guide = '';
        if ($type === 'goods' && (int) $row['quantity_on_hand'] !== 0) {
            $guide =
                'Open Relief goods → Stock movements. Record the actual distribution or stock-out of remaining supplies, then retry when stock is zero.';
        }
        $stayColumn = [
            'members' => 'household_member_id',
            'walkins' => 'walkin_id',
            'centers' => 'center_id',
        ];
        if (
            isset($stayColumn[$type]) &&
            $this->linked('evacuation_records', $stayColumn[$type], $id, 'AND checked_out_at IS NULL')
        ) {
            $guide =
                'Open Evacuation centers → the occupied center. Check out the affected people when they have actually left, then retry.';
        }
        if ($type === 'households') {
            $members = $this->query('SELECT id FROM household_members WHERE household_id=? FOR UPDATE', [
                $id,
            ]);
            foreach ($members as $member) {
                if (
                    $this->linked(
                        'evacuation_records',
                        'household_member_id',
                        (int) $member['id'],
                        'AND checked_out_at IS NULL',
                    )
                ) {
                    $guide =
                        'Open Evacuation centers and check out this household’s active occupants when they have left, then retry.';
                }
            }
        }
        if (
            $type === 'incidents' &&
            (in_array($row['status'], ['verified', 'responding'], true) ||
                $this->linked(
                    'incident_assignments',
                    'incident_id',
                    $id,
                    "AND status IN ('assigned','on_scene')",
                ))
        ) {
            $guide =
                'Open the incident → Response management. Complete or cancel active assignments, then resolve or reject the incident before retrying.';
        }
        if (
            $type === 'teams' &&
            $this->linked(
                'incident_assignments',
                'responder_id',
                $id,
                "AND status IN ('assigned','on_scene')",
            )
        ) {
            $guide =
                'Open the assigned incidents → Response management. Complete or cancel this team’s active assignments, then retry.';
        }
        if ($type === 'members' && $this->query(
            'SELECT id FROM households WHERE representative_member_id=? FOR UPDATE', [$id]
        )) {
            $guide = 'Select a replacement representative on the household details page first.';
        }
        if ($guide !== '') {
            throw new DomainException(
                self::TYPES[$type][2] . ' #' . $id . ': ' . $guide . ' No selected records were changed.',
            );
        }
        $dependencies = [
            'households' => [['relief_distributions', 'household_id', 'This household has a shared relief distribution.']],
            'members' => [['evacuation_records', 'household_member_id', 'This member has center stay records.']],
            'walkins' => [['evacuation_records', 'walkin_id', 'This person has center stay records.']],
            'teams' => [['incident_assignments', 'responder_id', 'An incident still references this team.']],
            'goods' => [['relief_stock_movements', 'item_id', 'Stock history depends on this good.'],
                        ['relief_distribution_items', 'item_id', 'A distribution depends on this good.']],
            'locations' => [['relief_distributions', 'relief_location_id', 'A distribution depends on this location.']],
        ];
        foreach ($dependencies[$type] ?? [] as [$table, $column, $reason]) {
            if ($this->linked($table, $column, $id)) {
                throw new DomainException(self::TYPES[$type][2] . ' #' . $id . ': ' . $reason . ' No selected records were changed.');
            }
        }
    }

    public function deleteSelected(array $selection): void
    {
        $files = [];
        $this->pdo->beginTransaction();
        try {
            ksort($selection);
            foreach ($selection as $type => $ids) {
                [$table] = $this->spec($type);
                foreach ($this->selected($type, $ids, true) as $row) {
                    $id = (int) $row['id'];
                    $this->checkRemoval($type, $row);
                    if ($type === 'incidents') {
                        foreach ($this->query('SELECT stored_filename FROM incident_images WHERE incident_id=?', [$id]) as $image) {
                            $name = $image['stored_filename'];
                            if (INCIDENT_UPLOAD_DIR === '' || basename($name) !== $name || !in_array(pathinfo($name, PATHINFO_EXTENSION), ['jpg','png','webp'], true)) {
                                throw new DomainException('Check the incident image storage configuration before deleting.');
                            }
                            $files[] = INCIDENT_UPLOAD_DIR . '/' . $name;
                        }
                        foreach (['response_updates', 'incident_assignments', 'incident_images'] as $child) {
                            $this->pdo->prepare("DELETE FROM $child WHERE incident_id=?")->execute([$id]);
                        }
                    }
                    if ($type === 'households') {
                        if ($this->query('SELECT e.id FROM evacuation_records e JOIN household_members m ON m.id=e.household_member_id WHERE m.household_id=? LIMIT 1 FOR UPDATE', [$id])) {
                            throw new DomainException('This household has linked center stays. No selected records were changed.');
                        }
                        $this->pdo->prepare('UPDATE households SET representative_member_id=NULL WHERE id=?')->execute([$id]);
                        $this->pdo->prepare('DELETE FROM household_members WHERE household_id=?')->execute([$id]);
                    }
                    if ($type === 'centers') {
                        $this->pdo->prepare('DELETE FROM evacuation_records WHERE center_id=?')->execute([$id]);
                    }
                    $this->pdo->prepare("DELETE FROM $table WHERE id=?")->execute([$id]);
                }
            }
            $this->pdo->commit();
        } catch (Throwable $error) {
            $this->pdo->rollBack();
            throw $error;
        }
        foreach ($files as $path) {
            if (is_file($path) && !unlink($path)) {
                throw new DomainException('Records were deleted. Ask the administrator to clean up the remaining private image file.');
            }
        }
    }
}
