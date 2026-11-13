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
        $data = ['incidents' => $this->model->incidents($admin?null:(int)$_SESSION['ID']),'centers' => $this->model->centers()];
        if ($admin) {
            $data['affected'] = $this->model->affected();
            $data['stays'] = $this->model->stays();
        } else {
            $data['notices'] = $this->model->notices();
        }return $data;
    }
}
