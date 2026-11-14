<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/ReliefLocation.php';
class ReliefLocationController extends Controller
{
    private ReliefLocationModel $model;
    public function __construct()
    {
        requireRole('admin');
        $this->model = new ReliefLocationModel();
    }
    public function all(): array
    {
        return $this->model->all();
    }
    public function find(int $id): array
    {
        $location = $this->model->find($id);
        if (!$location) {
            http_response_code(404);
            exit('Location not found.');
        }
        return $location;
    }
    public function save(?int $id): array
    {
        if (!isPost()) {
            return [];
        }
        if ($id !== null && !empty($this->model->find($id)['archived_at'])) {
            return ['Archived locations cannot be edited.'];
        }
        $data = [];
        foreach (['name','location_type','address_details','latitude','longitude'] as $field) {
            $data[$field] = input($field);
        }
        if (input('action') !== 'save_location' || $data['name'] === '' || strlen($data['name']) > 150 || $data['address_details'] === '' || strlen($data['address_details']) > 255 || !in_array($data['location_type'], ['storage','distribution','both'], true)) {
            return ['Enter name, address, and valid location type.'];
        }
        $errors = locationErrors($data['address_details'], $data['latitude'], $data['longitude']);
        if ($errors) {
            return $errors;
        }
        foreach (['latitude','longitude'] as $field) {
            if ($data[$field] === '') {
                $data[$field] = null;
            }
        }
        try {
            $this->model->save($id, $data);
        } catch (PDOException $e) {
            return ['Location could not be saved.'];
        }
        $this->success('Location saved.', 'resources/views/admin/relief/locations.php');
        return [];
    }
}
