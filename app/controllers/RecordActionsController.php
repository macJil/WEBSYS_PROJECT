<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/RecordActions.php';
class RecordActionsController extends Controller
{
    private RecordActionsModel $model;
    public function __construct()
    {
        requireRole('admin');
        $this->model = new RecordActionsModel();
    }
    public function listing(string $type, string $search, int $page): array
    {
        return $this->model->listing($type, $search, $page);
    }
    public function delete(array $rows): void
    {
        requireRole('admin');
        $ids = $_POST['ids'] ?? [];
        if (input('action') !== 'delete' || !is_array($ids) || !$ids || count($ids) > 50) {
            throw new DomainException('Select 1–50 records on this page.');
        }
        $visible = [];
        foreach ($rows as $row) {
            $visible[$row['record_type'] . ':' . $row['id']] = $row;
        }
        $selection = [];
        foreach ($ids as $key) {
            if (!is_string($key) || !isset($visible[$key])) {
                throw new DomainException('Select records from the displayed page.');
            }
            $row = $visible[$key];
            $selection[$row['record_type']][(int)$row['id']] = (int)$row['id'];
        }
        foreach ($selection as $type => $records) {
            $selection[$type] = array_values($records);
        }
        $this->model->deleteSelected($selection);
        flash('Selected records permanently deleted.');
    }
}
