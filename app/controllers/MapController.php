<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Map.php';
class MapController extends Controller
{
    public function data(): array
    {
        session_checker();
        $model = new MapModel();
        return $_SESSION['ROLE'] === 'admin'?$model->admin():$model->resident();
    }
    public function reliefLocation($value): array
    {
        requireRole('admin');
        $id = is_scalar($value)?filter_var($value, FILTER_VALIDATE_INT):false;
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
    // Reusable by the publication editor in the next revision. No private-point fallback.
    public static function publicationErrors(array $data): array
    {
        if (!in_array($data['is_public'] ?? null, [0,1,'0','1'], true)) {
            return ['Choose a valid visibility value.'];
        }
        $lat = $data['public_latitude'] ?? '';
        $lng = $data['public_longitude'] ?? '';
        $emptyLat = $lat === '' || $lat === null;
        $emptyLng = $lng === '' || $lng === null;
        if ($emptyLat !== $emptyLng || (!$emptyLat && (!is_numeric($lat) || !is_numeric($lng) || !is_finite((float)$lat) || !is_finite((float)$lng) || abs((float)$lat) > 90 || abs((float)$lng) > 180))) {
            return ['Enter both valid public coordinates, or leave both blank.'];
        }
        if ((int)$data['is_public'] === 1) {
            $label = $data['public_label'] ?? null;
            if (!is_string($label) || trim($label) === '' || strlen($label) > 150 || $emptyLat || !in_array($data['status'] ?? '', ['verified','responding','resolved'], true) || !empty($data['archived_at'])) {
                return ['Publication requires an eligible incident, safe label, and separate public point.'];
            }
        }
        return [];
    }
}
