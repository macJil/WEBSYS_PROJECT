<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Dashboard.php';
class DashboardController extends Controller
{
    private DashboardModel $model;
    public function __construct(string $role)
    {
        requireRole($role);
        $this->model = new DashboardModel();
    }

    public function dashboardData(): array
    {
        $admin = $_SESSION['ROLE'] === 'admin';
        $barangay = $admin ? ($_GET['barangay'] ?? '') : '';
        if ($barangay !== '' && !in_array($barangay, ATOK_BARANGAYS, true)) {
            http_response_code(400);
            exit('Choose a barangay in Atok.');
        }
        $data = [
            'barangay' => $barangay,
            'incidents' => $this->model->incidents($admin ? null : (int) $_SESSION['ID'], $barangay),
            'centers' => $this->model->centers(),
        ];
        if ($admin) {
            $data['centers'] = filterBarangay($data['centers']);
            $data['affected'] = filterBarangay($this->model->affected());
            $data['stays'] = filterBarangay($this->model->stays());
            $data['inventory'] = $this->model->inventoryItemCount();
        } else {
            $data['notices'] = $this->model->notices();
        }
        return $data;
    }
}
