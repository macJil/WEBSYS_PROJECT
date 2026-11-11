<?php
require_once __DIR__ . '/../../config/db.php';
class AnnouncementModel{
  private $pdo;
  public function __construct(){
    $db = new DATABASE();
    $this->pdo = $db->connect();
  }
  public function store_announcement($title,$message,$created_by){
    return $this->pdo->prepare("INSERT INTO announcements(title,message,created_by)
    VALUES(?,?,?)")->execute([$title,$message,$created_by]);
  }
  public function get_all_announcemnt(bool $publishedOnly = false){
    $stmt = $this->pdo->prepare("SELECT * FROM announcements" . ($publishedOnly ? ' WHERE is_published = 1' : ''));
    $stmt->execute();
    return $stmt->fetchAll();

  }
  public function delete_announcement($id){
    return $this->pdo->prepare("DELETE FROM announcements WHERE id = ?")->execute([$id]);
  }
  public function get_by_id($id){
    $stmt = $this->pdo->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
  }
  public function update($title, $message, $created_at, $is_published,$id){
    return $this->pdo->prepare('UPDATE announcements SET title = :title, message = :message, created_at = :created_at, is_published = :is_published WHERE id = :id')
    ->execute(['title' => $title,'message' => $message,'created_at' => $created_at,'is_published' => $is_published, 'id'=>$id]);
  }
 

}

?>