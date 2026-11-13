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
        $r = $this->model->find($id);
        if (!$r) {
            http_response_code(404);
            exit('Location not found.');
        }
        return $r;
    }
    public function save(?int $id): array
    {
        if (!isPost()) return [];
        if ($id !== null && !empty($this->model->find($id)['archived_at'])) return ['Archived locations cannot be edited.'];
        $d = [];
        foreach (['name', 'location_type', 'address_details', 'latitude', 'longitude'] as $k) $d[$k] = input($k);
        if (input('action') !== 'save_location' || $d['name'] === '' || strlen($d['name']) > 150 || $d['address_details'] === '' || strlen($d['address_details']) > 255 || !in_array($d['location_type'], ['storage', 'distribution', 'both'], true)) return ['Enter name, address, and valid location type.'];
        $errors = locationErrors($d['address_details'], $d['latitude'], $d['longitude']);
        if ($errors) return $errors;
        foreach (['latitude', 'longitude'] as $k) $d[$k] = $d[$k] === '' ? null : $d[$k];
        try {
            $this->model->save($id, $d);
        } catch (PDOException $e) {
            error_log((string)$e);
            return ['Location could not be saved.'];
        }
        $this->success('Location saved.', 'resources/views/admin/relief/locations.php');
        return [];
    }
}
