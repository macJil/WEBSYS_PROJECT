<?php
require_once __DIR__ . '/../../config/db.php';
class ResponseModel
{
    private PDO $pdo;
    public function __construct() { $this->pdo = (new DATABASE())->connect(); }
    public function teams(): array
    {
        return $this->pdo->query('SELECT * FROM responders ORDER BY is_active DESC, team_name')->fetchAll();
    }
    public function team(int $id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM responders WHERE id=?');
        $stmt->execute([$id]); return $stmt->fetch();
    }
    public function saveTeam(?int $id, string $name, string $phone, int $active): void
    {
        if ($id !== null) {
            $stmt = $this->pdo->prepare('UPDATE responders SET team_name=?, contact_phone=?, is_active=? WHERE id=?');
            $stmt->execute([$name, $phone ?: null, $active, $id]);
        } else {
            $stmt = $this->pdo->prepare('INSERT INTO responders (team_name,contact_phone,is_active) VALUES (?,?,?)');
            $stmt->execute([$name, $phone ?: null, $active]);
        }
    }
    public function incident(int $id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM incident_reports WHERE id=?');
        $stmt->execute([$id]); return $stmt->fetch();
    }
    public function assignments(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT a.*, r.team_name, r.contact_phone, r.is_active, u.full_name AS assigned_by_name FROM incident_assignments a JOIN responders r ON r.id=a.responder_id JOIN users u ON u.id=a.assigned_by WHERE a.incident_id=? ORDER BY a.id');
        $stmt->execute([$id]); return $stmt->fetchAll();
    }
    public function history(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT h.*, u.full_name AS staff_name FROM response_updates h JOIN users u ON u.id=h.updated_by WHERE h.incident_id=? ORDER BY h.id');
        $stmt->execute([$id]); return $stmt->fetchAll();
    }
    // All response writes lock the incident first, just like staff reviews.
    public function record(int $incidentId, ?int $teamId, ?int $assignmentId, string $status, string $notes, int $staff): void
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('SELECT status FROM incident_reports WHERE id=? FOR UPDATE');
            $stmt->execute([$incidentId]); $old = $stmt->fetchColumn();
            if (!in_array($old, ['verified','responding'], true)) {
                throw new DomainException('Verify or reopen the incident before managing its response.');
            }
            if ($teamId !== null) {
                $stmt = $this->pdo->prepare('SELECT * FROM responders WHERE id=? FOR UPDATE');
                $stmt->execute([$teamId]); $team = $stmt->fetch();
                if (!$team || !$team['is_active']) { throw new DomainException('Choose an active response team.'); }
                $stmt = $this->pdo->prepare('SELECT id FROM incident_assignments WHERE incident_id=? AND responder_id=?');
                $stmt->execute([$incidentId,$teamId]);
                if ($stmt->fetchColumn()) { throw new DomainException('This team already has an assignment. Update or reactivate that assignment below.'); }
                $stmt = $this->pdo->prepare('INSERT INTO incident_assignments (incident_id,responder_id,assigned_by) VALUES (?,?,?)');
                $stmt->execute([$incidentId,$teamId,$staff]);
                $assignmentId = (int)$this->pdo->lastInsertId(); $status = 'assigned';
                $action = 'Assigned team ' . $team['team_name'] . '. ' . $notes;
            } else {
                $stmt = $this->pdo->prepare('SELECT a.*, r.team_name, r.is_active FROM incident_assignments a JOIN responders r ON r.id=a.responder_id WHERE a.id=? AND a.incident_id=? FOR UPDATE');
                $stmt->execute([$assignmentId,$incidentId]); $assignment = $stmt->fetch();
                if (!$assignment) { throw new DomainException('Assignment does not belong to this incident.'); }
                $allowed = [
                    'assigned'=>['assigned','on_scene','completed','cancelled'],
                    'on_scene'=>['on_scene','completed','cancelled'],
                    'completed'=>['completed','assigned'],
                    'cancelled'=>['cancelled','assigned']
                ];
                if (!in_array($status, $allowed[$assignment['status']], true)) { throw new DomainException('Invalid assignment transition. Reactivate a closed assignment as assigned first.'); }
                if ($status === 'assigned' && in_array($assignment['status'], ['completed','cancelled'], true) && !$assignment['is_active']) {
                    throw new DomainException('Reactivate the response team before assigning it again.');
                }
                $stmt = $this->pdo->prepare('UPDATE incident_assignments SET status=? WHERE id=? AND incident_id=?');
                $stmt->execute([$status,$assignmentId,$incidentId]);
                $action = $assignment['team_name'] . ': ' . $assignment['status'] . ' → ' . $status . '. ' . $notes;
            }
            // Dispatch/on-scene starts response. Completion never automatically resolves an emergency.
            $next = in_array($status, ['assigned','on_scene'], true) ? 'responding' : $old;
            $stmt = $this->pdo->prepare('UPDATE incident_reports SET status=? WHERE id=?');
            $stmt->execute([$next,$incidentId]);
            $stmt = $this->pdo->prepare('INSERT INTO response_updates (incident_id,updated_by,action_taken,from_status,to_status,assignment_id,assignment_status) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute([$incidentId,$staff,$action,$old,$next,$assignmentId,$status]);
            $this->pdo->commit();
        } catch (Throwable $error) { $this->pdo->rollBack(); throw $error; }
    }
}
