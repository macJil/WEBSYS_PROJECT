<?php 
class Controller{
  
protected function results_checker($result, $status){
    $result = htmlspecialchars($result);
    
  if ($result !== null && $status === 0){
    return "<p class='text-danger'>$result</p>";
  }
  if ($result !== null && $status === 1){
    return "<p class='text-success'>$result</p>";
  }
}
protected function start_session(int $id, string $fullName, string $userName, string $phone, string $email, string $role): void
    {
        start_app_session();
        $_SESSION['ID'] = $id;
        $_SESSION['FULL_NAME'] = $fullName;
        $_SESSION['USERNAME'] = $userName;
        $_SESSION['PHONE'] = $phone;
        $_SESSION['EMAIL'] = $email;
        $_SESSION['ROLE'] = $role;
        return;
    }




}



?>