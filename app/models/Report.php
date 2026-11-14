<?php
require_once __DIR__ . '/../../config/db.php';
class ReportModel
{
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }
    public function listing(array $filters, ?int $owner): array
    {
        $where = ['archived_at IS NULL'];
        $values = [];
        if ($owner !== null) {
            $where[] = 'reported_by = ?';
            $values[] = $owner;
        }
        foreach (['incident_type', 'status'] as $field) {
            if ($filters[$field] !== '') {
                $where[] = "$field = ?";
                $values[] = $filters[$field];
            }
        }
        if ($filters['date'] !== '') {
            $where[] = 'DATE(created_at) = ?';
            $values[] = $filters['date'];
        }
        foreach (['location' => 'location_description', 'search' => 'description'] as $key => $field) {
            if ($filters[$key] !== '') {
                $where[] = "$field LIKE ? ESCAPE '!'";
                $values[] = '%' . strtr($filters[$key], ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            }
        }
        $stmt = $this->pdo->prepare('SELECT * FROM incident_reports WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC, id DESC');
        $stmt->execute($values);
        return $stmt->fetchAll();
    }
    public function find(int $id, ?int $owner)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM incident_reports WHERE id = ?' . ($owner !== null ? ' AND reported_by = ?' : ''));
        $stmt->execute($owner !== null ? [$id, $owner] : [$id]);
        return $stmt->fetch();
    }
    public function images(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT id FROM incident_images WHERE incident_id = ? ORDER BY id');
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }
    public function image(int $id, ?int $owner)
    {
        $stmt = $this->pdo->prepare('SELECT i.* FROM incident_images i JOIN incident_reports r ON r.id = i.incident_id WHERE i.id = ?' . ($owner !== null ? ' AND r.reported_by = ?' : ''));
        $stmt->execute($owner !== null ? [$id, $owner] : [$id]);
        return $stmt->fetch();
    }
    public function history(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT action_taken, from_status, to_status, created_at FROM response_updates WHERE incident_id = ? ORDER BY id');
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }
    public function create(array $data, ?array $image): int
    {
        $this->pdo->beginTransaction();
        try {
            $fields = array_keys($data);
            $stmt = $this->pdo->prepare('INSERT INTO incident_reports (' . implode(',', $fields) . ') VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')');
            $stmt->execute(array_values($data));
            $id = (int) $this->pdo->lastInsertId();
            if ($image) {
                $stmt = $this->pdo->prepare('INSERT INTO incident_images (incident_id,stored_filename,original_filename,mime_type,file_size,uploaded_by) VALUES (?,?,?,?,?,?)');
                $stmt->execute([$id, $image['stored_filename'], $image['original_filename'], $image['mime_type'], $image['file_size'], $data['reported_by']]);
            }
            $this->pdo->commit();
            return $id;
        } catch (Throwable $error) {
            $this->pdo->rollBack();
            throw $error;
        }
    }
    public function updateLocation(int $id, string $description, ?string $lat, ?string $lng, string $notes, int $staff): void
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('SELECT location_description,latitude,longitude,status,archived_at FROM incident_reports WHERE id=? FOR UPDATE');
            $stmt->execute([$id]);
            $old = $stmt->fetch();
            if (!$old || $old['archived_at'] !== null) {
                throw new DomainException('Archived or missing incidents cannot be edited.');
            }
            $stmt = $this->pdo->prepare('UPDATE incident_reports SET location_description=?,latitude=?,longitude=? WHERE id=?');
            $stmt->execute([$description,$lat,$lng,$id]);
            $previousPoint = '[' . ($old['latitude'] ?? 'none') . ', ' . ($old['longitude'] ?? 'none') . ']';
            $updatedPoint = '[' . ($lat ?? 'none') . ', ' . ($lng ?? 'none') . ']';
            $action = 'Location correction: ' . $notes;
            $action .= ' | Previous: ' . $old['location_description'] . ' ' . $previousPoint;
            $action .= ' | Updated: ' . $description . ' ' . $updatedPoint;
            $stmt = $this->pdo->prepare('INSERT INTO response_updates(incident_id,updated_by,action_taken,from_status,to_status) VALUES(?,?,?,?,?)');
            $stmt->execute([$id,$staff,$action,$old['status'],$old['status']]);
            $this->pdo->commit();
        } catch (Throwable $error) {
            $this->pdo->rollBack();
            throw $error;
        }
    }
    public function review(int $id, string $status, string $notes, int $staff): void
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('SELECT status FROM incident_reports WHERE id = ? AND archived_at IS NULL FOR UPDATE');
            $stmt->execute([$id]);
            $old = $stmt->fetchColumn();
            if ($old === false) {
                throw new RuntimeException('Incident not found.');
            }
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM incident_assignments WHERE incident_id=? AND status IN ('assigned','on_scene')");
            $stmt->execute([$id]);
            $active = (int)$stmt->fetchColumn();
            if ($active > 0 && $status !== 'responding') {
                throw new DomainException('Complete or cancel all active team assignments before changing the incident out of responding.');
            }
            if ($active === 0 && $status === 'responding' && $old !== 'responding') {
                throw new DomainException('Verify the incident, then assign a team to start responding.');
            }
            $stmt = $this->pdo->prepare("UPDATE incident_reports SET status=?, reviewed_by=?, reviewed_at=CURRENT_TIMESTAMP, review_notes=? WHERE id=?");
            $stmt->execute([$status, $staff, $notes, $id]);
            $stmt = $this->pdo->prepare('INSERT INTO response_updates (incident_id,updated_by,action_taken,from_status,to_status) VALUES (?,?,?,?,?)');
            $stmt->execute([$id, $staff, $notes, $old, $status]);
            $this->pdo->commit();
        } catch (Throwable $error) {
            $this->pdo->rollBack();
            throw $error;
        }
    }
}
