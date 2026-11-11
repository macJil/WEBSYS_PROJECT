<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Announcement.php';
class AnnouncementController extends Controller
{
    private AnnouncementModel $model;
    public function __construct(string $role) { requireRole($role); $this->model=new AnnouncementModel(); }
    public function query(string $key): string { return is_string($_GET[$key]??null)?trim($_GET[$key]):''; }
    public function listing(): array
    {
        $resident=$_SESSION['ROLE']==='resident';
        $filters=['q'=>$this->query('q'),'date'=>$this->query('date'),'status'=>$resident?'':$this->query('status')];
        $date=$filters['date']!==''?DateTime::createFromFormat('!Y-m-d',$filters['date']):null;
        if (!in_array($filters['status'],['','published','draft'],true) || ($filters['date']!=='' && (!$date || $date->format('Y-m-d')!==$filters['date']))) { http_response_code(400); exit('Invalid announcement filter.'); }
        return $this->model->listing($filters,$resident);
    }
    public function find(): array
    {
        requireRole('admin');
        $id=is_scalar($_GET['id']??null)?filter_var($_GET['id'],FILTER_VALIDATE_INT):false;
        if (!$id || $id<1) { http_response_code(400); exit('Invalid announcement ID.'); }
        $row=$this->model->find($id);
        if (!$row) { http_response_code(404); exit('Announcement not found.'); } return $row;
    }
    public function save(?int $id): array
    {
        requireRole('admin');
        if (!isPost()) { return []; }
        $action=input('action'); $title=input('title'); $message=input('message');
        if (!in_array($action,['save_draft','publish'],true)) { return ['Choose Save draft / withdraw or Publish.']; }
        if ($title==='' || strlen($title)>150 || $message==='' || strlen($message)>10000) { return ['Enter a title (up to 150 bytes) and message (up to 10,000 bytes).']; }
        try { $this->model->save($id,$title,$message,$action==='publish'?1:0,(int)$_SESSION['ID']); }
        catch (PDOException $error) { error_log((string)$error); return ['Announcement could not be saved. Please try again.']; }
        $this->success($action==='publish'?'Announcement published to residents.':'Announcement saved privately as a draft.','resources/views/admin/announcement/announcement.php'); return [];
    }
}
