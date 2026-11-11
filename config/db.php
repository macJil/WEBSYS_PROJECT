<?php

class DATABASE{
  private const HOST = '127.0.0.1';
  private const DB = 'barangay_drrm';
  private const USER = 'root';
  private const PASS = 'root';
  private const PORT = '8889';
  private const UTF = 'utf8mb4';
  private const ATT = [
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES=>false
  ];
  

  public function connect(){
    try{
      return new PDO("mysql:host=" . self::HOST . ";dbname=" . self::DB . ";port=" . self::PORT . ";charset=" . self::UTF . ";", self::USER, self::PASS, self::ATT);
    } catch (PDOException $e){
      return $this->results_checker("ERROR! Connection Failed {$e->getMessage()}", 0);
    }
  }
  private function results_checker($result, $status){
    $result = htmlspecialchars($result);
    
    if ($result !== null && $status === 0){
      return "<p class='text-danger'>$result</p>";
    }
    if ($result !== null && $status === 1){
      return "<p class='text-success'>$result</p>";
    }
  }
}


?>