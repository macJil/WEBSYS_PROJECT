<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/MapPublication.php';
class MapPublicationController extends Controller
{
    private MapPublicationModel $model;
    public function __construct()
    {
        requireRole('admin');
        $this->model = new MapPublicationModel();
    }
    public function find(string $type, int $id): array
    {
        return $this->model->find($type, $id);
    }
    public function save(string $type, int $id): array
    {
        if (!isPost())return [];
        $action = input('action');
        if (!in_array($action, ['publish','withdraw'], true))return ['Invalid action.'];
        try {
            $this->model->save($type, $id, $action === 'publish');
        } catch (DomainException $e) {
            return [$e->getMessage()];
        } catch (Throwable $e) {
            error_log((string)$e);
            return ['Map visibility could not be saved.'];
        }
        $this->success('Community map visibility saved.', 'resources/views/admin/map-publication.php?type=' . $type . '&id=' . $id);
        return [];
    }
}
