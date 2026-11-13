<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Response.php';
class ResponseController extends Controller
{
    public const STATUSES = ['assigned','on_scene','completed','cancelled'];
    private ResponseModel $model;
    public function __construct()
    {
        requireRole('admin');
        $this->model = new ResponseModel();
    }
    public function recordId($value): int
    {
        $id = is_scalar($value) ? filter_var($value, FILTER_VALIDATE_INT) : false;
        if (!$id || $id < 1) {
            http_response_code(400);
            exit('Invalid record ID.');
        } return $id;
    }
    public function team(int $id): array
    {
        $row = $this->model->team($id);
        if (!$row) {
            http_response_code(404);
            exit('Team not found.');
        } return $row;
    }
    public function incident(int $id): array
    {
        $row = $this->model->incident($id);
        if (!$row) {
            http_response_code(404);
            exit('Incident not found.');
        } return $row;
    }
    public function teams(): array
    {
        return $this->model->teams();
    }
    public function assignments(int $id): array
    {
        return $this->model->assignments($id);
    }
    public function history(int $id): array
    {
        return $this->model->history($id);
    }
    public function saveTeam(?int $id): array
    {
        if (!isPost()) {
            return [];
        }
        if (input('action') !== 'save_team') {
            return ['Unknown form action.'];
        }
        $name = input('team_name');
        $phone = input('contact_phone');
        $active = input('is_active');
        if ($name === '' || strlen($name) > 120) {
            return ['Team name is required (maximum 120 bytes).'];
        }
        if ($phone !== '' && !preg_match('/^[0-9+() .-]{3,20}$/', $phone)) {
            return ['Enter a valid contact number, or leave it blank.'];
        }
        if (!in_array($active, ['0','1'], true)) {
            return ['Choose active or inactive.'];
        }
        try {
            $this->model->saveTeam($id, $name, $phone, (int)$active);
        } catch (PDOException $error) {
            return [($error->errorInfo[1] ?? null) === 1062 ? 'That team name is already in use.' : 'Team could not be saved. Please try again.'];
        }
        $this->success('Team saved.', 'resources/views/admin/response/teams.php');
        return [];
    }
    public function record(int $id): array
    {
        if (!isPost()) {
            return [];
        }
        $action = input('action');
        $notes = input('notes');
        if (!in_array($action, ['assign_team','update_assignment'], true)) {
            return ['Unknown form action.'];
        }
        if ($notes === '' || strlen($notes) > 2000) {
            return ['Describe the action taken (maximum 2,000 bytes).'];
        }
        $teamId = $action === 'assign_team' ? $this->recordId(input('team_id')) : null;
        $assignmentId = $action === 'update_assignment' ? $this->recordId(input('assignment_id')) : null;
        $status = $action === 'assign_team' ? 'assigned' : input('assignment_status');
        if (!in_array($status, self::STATUSES, true)) {
            return ['Choose a valid assignment status.'];
        }
        try {
            $this->model->record($id, $teamId, $assignmentId, $status, $notes, (int)$_SESSION['ID']);
        } catch (DomainException $error) {
            return [$error->getMessage()];
        } catch (Throwable $error) {
            return ['Response update could not be saved. Please try again.'];
        }
        $this->success('Response update recorded.', 'resources/views/admin/response/incident.php?id=' . $id);
        return [];
    }
}
