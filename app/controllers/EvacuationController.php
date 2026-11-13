<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Evacuation.php';
class EvacuationController extends Controller
{
    private EvacuationModel $model;
    public function __construct()
    {
        requireRole('admin');
        $this->model = new EvacuationModel();
    }
    public function recordId($value): int
    {
        $id = is_scalar($value)?filter_var($value, FILTER_VALIDATE_INT):false;
        if (!$id || $id < 1) {
            http_response_code(400);
            exit('Invalid ID.');
        } return $id;
    }
    public function center(int $id): array
    {
        $row = $this->model->center($id);
        if (!$row) {
            http_response_code(404);
            exit('Center not found.');
        } return $row;
    }
    public function centers(string $q): array
    {
        return $this->model->centers($q);
    }
    public function members(): array
    {
        return $this->model->members();
    }
    public function walkins(): array
    {
        return $this->model->walkins();
    }
    public function records(int $id): array
    {
        return $this->model->records($id);
    }
    private function failure(Throwable $e): array
    {
        if ($e instanceof DomainException) {
            return [$e->getMessage()];
        }
        if ($e instanceof PDOException && ($e->errorInfo[1] ?? null) === 1062) {
            return ['This person already has an active check-in. Check out the existing stay first.'];
        }
        return ['Could not save the change. Please try again.'];
    }
    public function saveCenter(?int $id): array
    {
        if (!isPost()) {
            return [];
        }
        if (input('action') !== 'save_center') {
            return ['Unknown form action.'];
        }
        $capacity = filter_var(input('capacity'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0,'max_range' => 1000000]]);
        $data = ['name' => input('name'),'address_details' => input('address_details'),'capacity' => $capacity,'status' => input('status')];
        if ($data['name'] === '' || strlen($data['name']) > 150 || $data['address_details'] === '' || strlen($data['address_details']) > 255 || $capacity === false || !in_array($data['status'], ['open','closed'], true)) {
            return ['Enter name (up to 150 bytes), address (up to 255 bytes), whole-person capacity (0–1,000,000), and valid status.'];
        }
        $errors = locationErrors($data['address_details'], input('latitude'), input('longitude'));
        if ($errors) {
            return $errors;
        }
        foreach (['latitude','longitude'] as $field) {
            $data[$field] = input($field) === ''?null:input($field);
        }
        try {
            $this->model->saveCenter($id, $data);
        } catch (Throwable $e) {
            return $this->failure($e);
        }
        $this->success('Center saved.', 'resources/views/admin/evacuation/centers.php');
        return [];
    }
    public function handleStay(int $id): array
    {
        if (!isPost()) {
            return [];
        }
        try {
            if (input('action') === 'checkout') {
                $this->model->checkout($id, $this->recordId(input('record_id')), (int)$_SESSION['ID']);
            } elseif (input('action') === 'checkin') {
                $kind = input('kind');
                $data = [];
                $personId = 0;
                if (!in_array($kind, ['member','walkin','new_walkin'], true)) {
                    return ['Choose a check-in type.'];
                }
                if ($kind === 'new_walkin') {
                    $data = ['full_name' => input('full_name'),'age' => filter_var(input('age'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0,'max_range' => 130]]),'address_details' => input('address_details'),'contact_phone' => input('contact_phone')];
                    if ($data['full_name'] === '' || strlen($data['full_name']) > 100 || $data['age'] === false || $data['address_details'] === '' || strlen($data['address_details']) > 255 || !preg_match('/^[0-9+() .-]{3,20}$/', $data['contact_phone'])) {
                        return ['For a new walk-in, enter name (up to 100 bytes), age 0–130, home address (up to 255 bytes), and contact number (personal or guardian).'];
                    }
                } else {
                    $personId = $this->recordId(input($kind === 'member'?'member_id':'walkin_id'));
                }
                $this->model->checkin($id, $kind, $personId, $data, (int)$_SESSION['ID']);
            } else {
                return ['Unknown form action.'];
            }
        } catch (Throwable $e) {
            return $this->failure($e);
        }
        $this->success('Evacuation record saved.', 'resources/views/admin/evacuation/center.php?id=' . $id);
        return [];
    }
}
