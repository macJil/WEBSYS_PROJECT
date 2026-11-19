<?php
require_once __DIR__ . '/../../config/db.php';
class HouseholdModel
{
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }

    public function getAll(string $search): array
    {
        $term = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search) . '%';
        $sql = "SELECT h.*, (SELECT COUNT(*) FROM household_members m WHERE m.household_id = h.id AND m.archived_at IS NULL) AS member_count
                FROM household_directory h WHERE h.archived_at IS NULL AND (EXISTS (SELECT 1 FROM household_members member_row WHERE member_row.household_id=h.id AND member_row.archived_at IS NULL AND member_row.full_name LIKE ? ESCAPE '!')
                OR h.household_label LIKE ? ESCAPE '!' OR h.purok LIKE ? ESCAPE '!'
                OR h.address_details LIKE ? ESCAPE '!' OR h.contact_phone LIKE ? ESCAPE '!')
                ORDER BY h.household_label,h.id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_fill(0, 5, $term));
        return $stmt->fetchAll();
    }

    public function findById(int $id)
    {
        $stmt = $this->pdo->prepare(
            'SELECT h.*, (SELECT COUNT(*) FROM household_members m WHERE m.household_id=h.id AND m.archived_at IS NULL) AS member_count FROM household_directory h WHERE h.id=?',
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function save(array $data, ?int $id): int
    {
        if ($id !== null && !empty($this->findById($id)['archived_at'])) {
            throw new DomainException('Archived households cannot be edited.');
        }
        $fields = [
            'barangay',
            'contact_phone',
            'purok',
            'address_details',
            'registry_reason',
            'hazard_type',
            'hazard_notes',
            'vulnerability_category',
            'vulnerability_notes',
            'vulnerability_assessed_by',
            'vulnerability_assessed_at',
            'evacuation_status',
            'latitude',
            'longitude',
        ];
        $values = [];
        $assignments = [];
        foreach ($fields as $field) {
            $values[] = $data[$field];
            $assignments[] = $field . '=?';
        }
        if ($id === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO households (' .
                    implode(',', $fields) .
                    ') VALUES (' .
                    implode(',', array_fill(0, count($fields), '?')) .
                    ')',
            );
        } else {
            $stmt = $this->pdo->prepare(
                'UPDATE households SET ' . implode(',', $assignments) . ' WHERE id=?',
            );
            $values[] = $id;
        }
        $stmt->execute($values);
        return $id ?? (int) $this->pdo->lastInsertId();
    }

    public function selectRepresentative(int $householdId, int $memberId): void
    {
        $this->pdo->beginTransaction();
        try {
            $member = $this->pdo->prepare(
                'SELECT id FROM household_members WHERE id=? AND household_id=? AND archived_at IS NULL FOR UPDATE'
            );
            $member->execute([$memberId, $householdId]);
            if (!$member->fetchColumn()) {
                throw new DomainException('Choose an active member of this household.');
            }
            $stmt = $this->pdo->prepare(
                'UPDATE households SET representative_member_id=? WHERE id=? AND archived_at IS NULL'
            );
            $stmt->execute([$memberId, $householdId]);
            if (!$stmt->rowCount() && !empty($this->findById($householdId)['archived_at'])) {
                throw new DomainException('Archived households cannot be edited.');
            }
            $this->pdo->commit();
        } catch (Throwable $error) {
            $this->pdo->rollBack();
            throw $error;
        }
    }

    public function members(int $householdId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM household_members WHERE household_id=? AND archived_at IS NULL ORDER BY full_name,id',
        );
        $stmt->execute([$householdId]);
        return $stmt->fetchAll();
    }

    public function findMember(int $id, int $householdId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM household_members WHERE id=? AND household_id=?');
        $stmt->execute([$id, $householdId]);
        return $stmt->fetch();
    }

    public function saveMember(int $householdId, ?int $id, array $data): void
    {
        if (
            !empty($this->findById($householdId)['archived_at']) ||
            ($id !== null && !empty($this->findMember($id, $householdId)['archived_at']))
        ) {
            throw new DomainException('Archived records cannot be edited.');
        }
        if ($id === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO household_members (household_id,full_name,age,notes) VALUES (?,?,?,?)',
            );
            $stmt->execute([$householdId, $data['full_name'], $data['age'], $data['notes']]);
        } else {
            $stmt = $this->pdo->prepare(
                'UPDATE household_members SET full_name=?,age=?,notes=? WHERE id=? AND household_id=?',
            );
            $stmt->execute([$data['full_name'], $data['age'], $data['notes'], $id, $householdId]);
        }
    }
}
