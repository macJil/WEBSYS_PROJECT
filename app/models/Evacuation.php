<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../evacuation-capacity.php';
class EvacuationModel
{
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }
    private function rows(string $sql, array $values = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
        return $stmt->fetchAll();
    }
    public function centers(string $search): array
    {
        $term = '%' . strtr($search, ['!' => '!!','%' => '!%','_' => '!_']) . '%';
        $centers = $this->rows("SELECT c.*, (SELECT COUNT(*) FROM evacuation_records e WHERE e.center_id=c.id AND e.checked_out_at IS NULL) AS occupancy FROM evacuation_centers c WHERE c.archived_at IS NULL AND (c.name LIKE ? ESCAPE '!' OR c.address_details LIKE ? ESCAPE '!') ORDER BY c.name", [$term,$term]);
        foreach ($centers as $index => $center) {
            $centers[$index]['available'] = availableCapacity($center);
        }
        return $centers;
    }
    public function center(int $id): array
    {
        $rows = $this->rows('SELECT c.*, (SELECT COUNT(*) FROM evacuation_records e WHERE e.center_id=c.id AND e.checked_out_at IS NULL) AS occupancy FROM evacuation_centers c WHERE c.id=?', [$id]);
        if (!$rows) {
            return [];
        }
        $center = $rows[0];
        $center['available'] = availableCapacity($center);
        return $center;
    }
    private function lockCenter(int $id): array
    {
        $rows = $this->rows('SELECT * FROM evacuation_centers WHERE id=? FOR UPDATE', [$id]);
        if (!$rows) {
            throw new DomainException('Center not found.');
        }
        return $rows[0];
    }
    private function occupancy(int $id): int
    {
        // Current locking read, including changes committed while waiting for the center lock.
        return count($this->rows('SELECT id FROM evacuation_records WHERE center_id=? AND checked_out_at IS NULL FOR UPDATE', [$id]));
    }
    public function saveCenter(?int $id, array $data): void
    {
        $this->pdo->beginTransaction();
        try {
            if ($id) {
                $editing = $this->lockCenter($id);
                if ($editing['archived_at'] !== null) {
                    throw new DomainException('Archived centers cannot be edited.');
                }
                $count = $this->occupancy($id);
                if ($data['capacity'] < $count) {
                    throw new DomainException('Capacity cannot be less than current occupancy.');
                }
                if ($data['status'] === 'closed' && $count > 0) {
                    throw new DomainException('Check out all evacuees before closing this center.');
                }
                $stmt = $this->pdo->prepare("UPDATE evacuation_centers SET latitude=?,longitude=?,name=?, address_details=?, capacity=?, status=? WHERE id=?");
                $stmt->execute([$data['latitude'],$data['longitude'],$data['name'],$data['address_details'],$data['capacity'],$data['status'],$id]);
            } else {
                $stmt = $this->pdo->prepare('INSERT INTO evacuation_centers (latitude,longitude,name,address_details,capacity,status) VALUES (?,?,?,?,?,?)');
                $stmt->execute([$data['latitude'],$data['longitude'],$data['name'],$data['address_details'],$data['capacity'],$data['status']]);
            }
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    public function households(): array
    {
        return $this->rows("SELECT h.id, h.household_code, h.representative_name,
            COUNT(m.id) AS member_count
            FROM households h
            LEFT JOIN household_members m ON m.household_id = h.id AND m.archived_at IS NULL
            WHERE h.archived_at IS NULL
            GROUP BY h.id, h.household_code, h.representative_name
            ORDER BY h.household_code");
    }

    public function householdMembers(int $householdId): array
    {
        return $this->rows('SELECT m.id, m.full_name FROM household_members m
            JOIN households h ON h.id = m.household_id
            WHERE h.id = ? AND h.archived_at IS NULL AND m.archived_at IS NULL
            ORDER BY m.id', [$householdId]);
    }

    public function checkinHousehold(int $centerId, int $householdId, array $memberIds, int $staff): void
    {
        if (!$memberIds) {
            throw new DomainException('Select at least one arriving member.');
        }
        foreach ($memberIds as $memberId) {
            if (!is_scalar($memberId) || !filter_var($memberId, FILTER_VALIDATE_INT) || $memberId < 1) {
                throw new DomainException('Invalid selected member.');
            }
        }
        $memberIds = array_map('intval', $memberIds);
        if (count($memberIds) !== count(array_unique($memberIds))) {
            throw new DomainException('A member was selected more than once.');
        }
        sort($memberIds);
        $this->pdo->beginTransaction();
        try {
            $center = $this->lockCenter($centerId);
            if ($center['status'] !== 'open' || $center['archived_at'] !== null) {
                throw new DomainException('This center is closed or archived.');
            }
            $household = $this->rows('SELECT id FROM households
                WHERE id = ? AND archived_at IS NULL FOR UPDATE', [$householdId]);
            if (!$household) {
                throw new DomainException('Active household not found.');
            }
            $occupancy = $this->occupancy($centerId);
            if ($occupancy + count($memberIds) > (int)$center['capacity']) {
                throw new DomainException('Not enough capacity for all selected members. Nobody was checked in.');
            }
            foreach ($memberIds as $memberId) {
                $member = $this->rows('SELECT id, full_name FROM household_members
                    WHERE id = ? AND household_id = ? AND archived_at IS NULL FOR UPDATE', [$memberId, $householdId]);
                if (!$member) {
                    throw new DomainException('A selected member is no longer active in this household. Nobody was checked in.');
                }
                $active = $this->rows('SELECT id FROM evacuation_records
                    WHERE household_member_id = ? AND checked_out_at IS NULL FOR UPDATE', [$memberId]);
                if ($active) {
                    throw new DomainException($member[0]['full_name'] . ' already has an active stay. Nobody was checked in.');
                }
            }
            $stmt = $this->pdo->prepare('INSERT INTO evacuation_records
                (center_id, household_member_id, recorded_by) VALUES (?, ?, ?)');
            foreach ($memberIds as $memberId) {
                $stmt->execute([$centerId, $memberId, $staff]);
            }
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function walkins(): array
    {
        return $this->rows('SELECT * FROM evacuation_walkins WHERE archived_at IS NULL ORDER BY full_name,id');
    }
    public function records(int $id): array
    {
        return $this->rows('SELECT e.*, COALESCE(m.full_name,e.walkin_name) AS person_name, h.household_code, w.age AS walkin_age,w.address_details AS walkin_address,u.full_name AS encoder, o.full_name AS checkout_staff FROM evacuation_records e LEFT JOIN household_members m ON m.id=e.household_member_id LEFT JOIN households h ON h.id=m.household_id LEFT JOIN evacuation_walkins w ON w.id=e.walkin_id JOIN users u ON u.id=e.recorded_by LEFT JOIN users o ON o.id=e.checked_out_by WHERE e.center_id=? ORDER BY (e.checked_out_at IS NULL) DESC,e.id DESC', [$id]);
    }
    public function checkin(int $centerId, string $kind, int $personId, array $data, int $staff): void
    {
        $this->pdo->beginTransaction();
        try {
            $center = $this->lockCenter($centerId);
            if ($center['status'] !== 'open' || $center['archived_at'] !== null) {
                throw new DomainException('This center is closed.');
            }
            if ($this->occupancy($centerId) >= (int)$center['capacity']) {
                throw new DomainException('This center is full.');
            }
            $memberId = null;
            $walkinId = null;
            $name = null;
            $phone = null;
            if ($kind === 'member') {
                $member = $this->rows('SELECT m.id FROM household_members m JOIN households h ON h.id=m.household_id WHERE m.id=? AND m.archived_at IS NULL AND h.archived_at IS NULL FOR UPDATE', [$personId]);
                if (!$member) {
                    throw new DomainException('Registered member not found.');
                } $memberId = $personId;
            } else {
                if ($kind === 'new_walkin') {
                    $normalized = array_map(fn ($value) => strtolower(preg_replace('/\s+/u', ' ', trim((string)$value))), [$data['full_name'],$data['age'],$data['address_details'],preg_replace('/[^0-9]/', '', $data['contact_phone'])]);
                    $key = hash('sha256', json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
                    $stmt = $this->pdo->prepare('INSERT INTO evacuation_walkins (full_name,age,address_details,contact_phone,identity_key) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)');
                    $stmt->execute([$data['full_name'],$data['age'],$data['address_details'],$data['contact_phone'],$key]);
                    $personId = (int)$this->pdo->lastInsertId();
                }
                $people = $this->rows('SELECT * FROM evacuation_walkins WHERE id=? AND archived_at IS NULL FOR UPDATE', [$personId]);
                if (!$people) {
                    throw new DomainException('Walk-in not found.');
                }
                $walkinId = $personId;
                $name = $people[0]['full_name'];
                $phone = $people[0]['contact_phone'];
                // Legacy walk-ins have no reusable ID; block matching active names/contacts for staff reconciliation.
                $legacy = $this->rows('SELECT id FROM evacuation_records WHERE household_member_id IS NULL AND walkin_id IS NULL AND checked_out_at IS NULL AND walkin_name=? AND walkin_contact=? FOR UPDATE', [$name,$phone]);
                if ($legacy) {
                    throw new DomainException('A matching legacy walk-in is already checked in. Review the existing stay first.');
                }
            }
            $stmt = $this->pdo->prepare('INSERT INTO evacuation_records (center_id,household_member_id,walkin_id,walkin_name,walkin_contact,recorded_by) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$centerId,$memberId,$walkinId,$name,$phone,$staff]);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    public function checkout(int $centerId, int $recordId, int $staff): void
    {
        $this->pdo->beginTransaction();
        try {
            $this->lockCenter($centerId);
            $stmt = $this->pdo->prepare('UPDATE evacuation_records SET checked_out_at=CURRENT_TIMESTAMP,checked_out_by=? WHERE id=? AND center_id=? AND checked_out_at IS NULL');
            $stmt->execute([$staff,$recordId,$centerId]);
            if ($stmt->rowCount() !== 1) {
                throw new DomainException('Stay not found in this center or already checked out.');
            }
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
