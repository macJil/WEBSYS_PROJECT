<?php
require_once __DIR__ . '/../../config/db.php';
class AnnouncementModel
{
    private PDO $pdo;
    public function __construct() { $this->pdo=(new DATABASE())->connect(); }
    public function listing(array $filters,bool $resident): array
    {
        $where=['archived_at IS NULL']; $args=[];
        if ($resident) { $where[]='is_published=1'; }
        elseif ($filters['status']!=='') { $where[]='is_published=?'; $args[]=$filters['status']==='published'?1:0; }
        if ($filters['q']!=='') { $where[]="title LIKE ? ESCAPE '!'"; $args[]='%'.strtr($filters['q'],['!'=>'!!','%'=>'!%','_'=>'!_']).'%'; }
        if ($filters['date']!=='') { $where[]='DATE(created_at)=?'; $args[]=$filters['date']; }
        $stmt=$this->pdo->prepare('SELECT id,title,message,is_published,created_at FROM announcements'.($where?' WHERE '.implode(' AND ',$where):'').' ORDER BY created_at DESC,id DESC');
        $stmt->execute($args); return $stmt->fetchAll();
    }
    public function find(int $id): array
    {
        $stmt=$this->pdo->prepare('SELECT * FROM announcements WHERE id=?'); $stmt->execute([$id]); return $stmt->fetch()?:[];
    }
    public function save(?int $id,string $title,string $message,int $published,int $staff): void
    {
        if ($id===null) {
            $stmt=$this->pdo->prepare('INSERT INTO announcements (title,message,is_published,created_by,first_published_at) VALUES (?,?,?,?,CASE WHEN ?=1 THEN CURRENT_TIMESTAMP ELSE NULL END)');
            $stmt->execute([$title,$message,$published,$staff,$published]);
        } else {
            $stmt=$this->pdo->prepare('UPDATE announcements SET title=?,message=?,is_published=?,first_published_at=CASE WHEN ?=1 THEN COALESCE(first_published_at,CURRENT_TIMESTAMP) ELSE first_published_at END WHERE id=?');
            $stmt->execute([$title,$message,$published,$published,$id]);
        }
    }
}
