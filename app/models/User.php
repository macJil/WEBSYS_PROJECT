<?php
require_once __DIR__ . '/../../config/db.php';

class UserModel{
  private $pdo;
  public function __construct(){
    $db = new DATABASE();
    $this->pdo = $db->connect();
  }

  public function get_user_by_email($email){
    $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
  }

  public function create_user($full_name, $username, $email, $phone, $password){
    $stmt = $this->pdo->prepare("INSERT INTO users(full_name, username, email, phone, password)
      VALUES (:full_name, :username, :email, :phone, :password)");
    return $stmt->execute([
      'full_name' => $full_name,
      'username' => $username,
      'email' => $email,
      'phone' => $phone,
      'password' => $password
    ]);
  }


}



?>