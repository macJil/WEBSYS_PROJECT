<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Map.php';
class MapController extends Controller
{
    public function mapData(): array
    {
        session_checker();
        $model = new MapModel();
        return $_SESSION['ROLE'] === 'admin' ? $model->admin() : $model->resident();
    }
    public function reliefLocation($value): array
    {
        requireRole('admin');
        $id = is_scalar($value) ? filter_var($value, FILTER_VALIDATE_INT) : false;
        if (!$id || $id < 1) {
            http_response_code(400);
            exit('Invalid location ID.');
        }
        $row = (new MapModel())->reliefLocation($id);
        if (!$row) {
            http_response_code(404);
            exit('Relief location not found.');
        }
        return $row;
    }
}
