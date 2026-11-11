<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Relief.php';
class ReliefController extends Controller
{
    private ReliefModel $model;
    public function __construct() { requireRole('admin'); $this->model=new ReliefModel(); }
    public function id($value): int
    {
        $id=is_scalar($value)?filter_var($value,FILTER_VALIDATE_INT):false;
        if (!$id || $id<1) { http_response_code(400); exit('Invalid ID.'); } return $id;
    }
    public function query(string $key): string { return is_string($_GET[$key]??null)?trim($_GET[$key]):''; }
    public function items(string $q=''): array { return $this->model->items($q); }
    public function item(int $id): array
    {
        $row=$this->model->item($id); if (!$row) { http_response_code(404); exit('Item not found.'); } return $row;
    }
    public function households(): array { return $this->model->households(); }
    public function distributions(string $q): array { return $this->model->distributions($q); }
    public function distribution(int $id): array
    {
        $row=$this->model->distribution($id); if (!$row) { http_response_code(404); exit('Distribution not found.'); } return $row;
    }
    public function distributedGoods(int $id): array { return $this->model->distributedGoods($id); }
    public function movements(): array
    {
        $type=$this->query('type'); $date=$this->query('date');
        if (!in_array($type,['','stock_in','stock_out','distribution'],true) || ($date!=='' && !validDateInput($date))) { http_response_code(400); exit('Invalid history filter.'); }
        return $this->model->movements($this->query('q'),$type,$date);
    }
    private function failure(Throwable $e): array
    {
        if ($e instanceof DomainException) { return [$e->getMessage()]; }
        error_log((string)$e);
        if ($e instanceof PDOException && ($e->errorInfo[1]??null)===1062) { return ['That item name and unit already exist.']; }
        return ['Change could not be saved. Please try again.'];
    }
    private function quantity($value)
    {
        return is_scalar($value)?filter_var($value,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>1000000000]]):false;
    }
    public function saveItem(?int $id): array
    {
        if (!isPost()) { return []; }
        if (input('action')!=='save_item') { return ['Unknown form action.']; }
        $name=input('item_name'); $unit=input('unit');
        if ($name==='' || strlen($name)>120 || $unit==='' || strlen($unit)>30) { return ['Enter an item name (up to 120 bytes) and unit (up to 30 bytes).']; }
        try { $this->model->saveItem($id,$name,$unit); } catch (Throwable $e) { return $this->failure($e); }
        $this->success('Relief item saved.','resources/views/admin/relief/inventory.php'); return [];
    }
    public function stock(): array
    {
        if (!isPost()) { return []; }
        $quantity=$this->quantity(input('quantity')); $type=input('movement_type'); $notes=input('notes');
        if (input('action')!=='stock' || !in_array($type,['stock_in','stock_out'],true) || $quantity===false || $notes==='' || strlen($notes)>255) { return ['Choose stock-in/out, enter a positive whole quantity (up to 1 billion), and notes/reason (up to 255 bytes).']; }
        $id=$this->id(input('item_id'));
        try { $this->model->stock($id,$type,$quantity,(int)$_SESSION['ID'],$notes); } catch (Throwable $e) { return $this->failure($e); }
        $this->success('Stock movement recorded.','resources/views/admin/relief/movements.php'); return [];
    }
    public function distribute(): array
    {
        if (!isPost()) { return []; }
        $receiver=input('received_by'); $notes=input('notes'); $raw=$_POST['goods']??null;
        if (input('action')!=='distribute' || $receiver==='' || strlen($receiver)>100 || strlen($notes)>255 || !is_array($raw)) { return ['Enter receiving representative (up to 100 bytes), notes (up to 255 bytes), and goods.']; }
        $goods=[];
        foreach ($raw as $key=>$value) {
            if ($value==='' || $value==='0') { continue; }
            $id=$this->id($key); $quantity=$this->quantity($value);
            if ($quantity===false) { return ['Goods quantities must be positive whole numbers (up to 1 billion), or blank/zero to omit.']; }
            $goods[$id]=$quantity;
        }
        if (!$goods) { return ['Select at least one good by entering its quantity.']; }
        $household=$this->id(input('household_id'));
        try { $id=$this->model->distribute($household,$receiver,$notes,$goods,(int)$_SESSION['ID']); } catch (Throwable $e) { return $this->failure($e); }
        $this->success('Distribution recorded and stock updated.','resources/views/admin/relief/distributions.php?id='.$id); return [];
    }
}
