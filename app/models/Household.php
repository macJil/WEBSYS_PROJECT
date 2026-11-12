<?php
require_once __DIR__ . '/../../config/db.php';
class HouseholdModel
{
    private PDO $pdo;
    public function __construct()
    {
        $this->pdo = (new DATABASE())->connect();
    }
    public function getAll(string $search): array
    {
        $term = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search) . '%';
        $sql = "SELECT h.*, (SELECT COUNT(*) FROM household_members m WHERE m.household_id = h.id AND m.archived_at IS NULL) AS member_count
                FROM households h WHERE h.archived_at IS NULL AND (h.household_code LIKE ? ESCAPE '!'
                OR h.representative_name LIKE ? ESCAPE '!' OR h.purok LIKE ? ESCAPE '!'
                OR h.address_details LIKE ? ESCAPE '!' OR h.contact_phone LIKE ? ESCAPE '!')
                ORDER BY h.household_code";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_fill(0, 5, $term));
        return $stmt->fetchAll();
    }
    public function findById(int $id)
    {
        $stmt = $this->pdo->prepare('SELECT h.*, (SELECT COUNT(*) FROM household_members m WHERE m.household_id=h.id AND m.archived_at IS NULL) AS member_count FROM households h WHERE h.id=?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function save(array $data, ?int $id): int
    {
        if ($id !== null && !empty($this->findById($id)['archived_at'])) {
            throw new DomainException('Archived households cannot be edited.');
        }
        $fields = ['household_code','representative_name','contact_phone','purok','address_details','registry_reason','hazard_type','hazard_notes','vulnerability_category','vulnerability_notes','vulnerability_assessed_by','vulnerability_assessed_at','evacuation_status','latitude','longitude'];
        $values = array_map(fn ($field) => $data[$field], $fields);
        if ($id === null) {
            $stmt = $this->pdo->prepare('INSERT INTO households (' . implode(',', $fields) . ') VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')');
        } else {
            $stmt = $this->pdo->prepare('UPDATE households SET ' . implode(',', array_map(fn ($field) => $field . '=?', $fields)) . ' WHERE id=?');
            $values[] = $id;
        }
        $stmt->execute($values);
        return $id ?? (int) $this->pdo->lastInsertId();
    }
    public function members(int $householdId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM household_members WHERE household_id=? AND archived_at IS NULL ORDER BY full_name,id');
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
        if (!empty($this->findById($householdId)['archived_at']) || ($id !== null && !empty($this->findMember($id, $householdId)['archived_at']))) {
            throw new DomainException('Archived records cannot be edited.');
        }
        if ($id === null) {
            $stmt = $this->pdo->prepare('INSERT INTO household_members (household_id,full_name,age,notes) VALUES (?,?,?,?)');
            $stmt->execute([$householdId,$data['full_name'],$data['age'],$data['notes']]);
        } else {
            $stmt = $this->pdo->prepare('UPDATE household_members SET full_name=?,age=?,notes=? WHERE id=? AND household_id=?');
            $stmt->execute([$data['full_name'],$data['age'],$data['notes'],$id,$householdId]);
        }
    }
}
