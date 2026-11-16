<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/History.php';

class HistoryController extends Controller
{
    public function hide(): array
    {
        requireRole('admin');
        if (!isPost()) {
            http_response_code(405);
            header('Allow: POST');
            return ['Use the Hide history entry button on a history page.'];
        }
        $id = filter_var(input('history_id'), FILTER_VALIDATE_INT);
        try {
            $path = (new HistoryModel())->hide(input('history_type'), $id ?: 0);
        } catch (DomainException $error) {
            http_response_code(400);
            return [$error->getMessage()];
        } catch (PDOException $error) {
            http_response_code(500);
            return ['History could not be hidden. Please try again.'];
        }
        $this->success('History entry hidden. Stored records and totals are unchanged.', $path);
        return [];
    }
}
