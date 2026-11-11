<?php
require_once __DIR__ . '/../../config/db.php';

class ReportModel{
  private $pdo;
  public function __construct(){
    $db = new DATABASE();
    $this->pdo = $db->connect();
  }

  

  public function create_user($full_name, $user_name, $email, $phone, $password){
    $stmt = $this->pdo->prepare("INSERT INTO users(full_name, user_name, email, phone, password)
      VALUES (:full_name, :username, :email, :phone, :password)");
    return $stmt->execute([
      'full_name' => $full_name,
      'username' => $user_name,
      'email' => $email,
      'phone' => $phone,
      'password' => $password
    ]);
  }

  public function add_report($incident_type, $description, $reported_by){
    $stmt = $this->pdo->prepare("INSERT INTO incident_reports(incident_type,description,reported_by)
    VALUES (:incident_type, :description, :reported_by)");
    return $stmt->execute([
      'incident_type'=> $incident_type,
      'description'=> $description,
      'reported_by'=> $reported_by,
    ]);
      
  }
  public function get_report($id){
    $stmt = $this->pdo->prepare("SELECT * FROM incident_reports WHERE reported_by = ?");
    $stmt->execute([$id]);
    return $stmt->fetchAll();
  }

  public function get_report_by_id($id){
    $stmt = $this->pdo->prepare("SELECT * FROM incident_reports WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  public function get_all(){
    $stmt = $this->pdo->prepare("SELECT * FROM incident_reports");
    $stmt->execute([]);
    return $stmt->fetchAll();
  }

  public function delete($id){
    return $this->pdo->prepare("DELETE FROM incident_reports WHERE id = ?")->execute([$id]);
  }
  public function update($incident_type, $description,$location_description,$contact_phone,$status,$created_at,$id){
    $stmt = $this->pdo->prepare("UPDATE incident_reports SET incident_type = :incident_type, description = :description,
    location_description = :location_description, contact_phone = :contact_phone, status = :status, updated_at = :updated_at WHERE id = :id")

    ->execute(["incident_type" => $incident_type, "description" => $description, "location_description" => $location_description, "contact_phone" => $contact_phone,
    "status" => $status, "updated_at" => $created_at,"id"=>$id]);
  }

  
  

}



?>