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
    public function listing(string $type, string $q, bool $archived, int $page): array
    {
        return $this->model->listing($type, $q, $archived, $page);
    }
    public function review(string $type, array $visibleIds): array
    {
        $ids = $_POST['ids'] ?? [];
        if (!is_array($ids) || !$ids || count($ids) > 50) throw new DomainException('Select 1–50 records on this page.');
        $clean = [];
        foreach ($ids as $id) {
            $valid = is_scalar($id) ? filter_var($id, FILTER_VALIDATE_INT) : false;
            if (!$valid || $valid < 1 || !in_array($valid, $visibleIds, true)) throw new DomainException('Selection must belong to this displayed page.');
            $clean[] = $valid;
        }
        $clean = array_values(array_unique($clean));
        $action = input('operation');
        if (!in_array($action, ['delete', 'archive'], true)) throw new DomainException('Choose delete or archive/deactivate.');
        $rows = $this->model->selected($type, $clean);
        $expected = [];
        foreach ($rows as $row) $expected[(int)$row['id']] = hash('sha256', json_encode($row));
        // The one-use review ticket binds confirmation to the server-reviewed batch.
        $ticket = bin2hex(random_bytes(24));
        $_SESSION['record_batch'] = ['ticket' => $ticket, 'type' => $type, 'ids' => $clean, 'action' => $action, 'expected' => $expected, 'expires' => time() + 900];
        return ['rows' => $rows, 'ticket' => $ticket, 'action' => $action];
    }
    public function confirm(string $type): void
    {
        requireRole('admin');
        $batch = $_SESSION['record_batch'] ?? null;
        unset($_SESSION['record_batch']);
        if (!$batch || $batch['type'] !== $type || $batch['expires'] < time() || !hash_equals($batch['ticket'], input('ticket'))) throw new DomainException('Review expired or already submitted. Select records again.');
        $this->model->apply($type, $batch['ids'], $batch['action'], $batch['expected']);
        $this->success('Selected records processed. History was preserved.', 'resources/views/admin/records.php?type=' . urlencode($type));
    }
}
