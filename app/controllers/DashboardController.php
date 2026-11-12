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
    public function data(): array
    {
        $admin = $_SESSION['ROLE'] === 'admin';
        $data = ['incidents' => $this->model->incidents($admin?null:(int)$_SESSION['ID']),'centers' => $this->model->centers(!$admin)];
        if ($admin) {
            $data['affected'] = $this->model->affected();
            $data['stays'] = $this->model->stays();
            $data['low_stock'] = $this->model->lowStock();
        } else {
            $data['notices'] = $this->model->notices();
        }return $data;
    }
}
