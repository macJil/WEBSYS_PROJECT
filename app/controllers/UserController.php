<?php

require_once __DIR__ . '/../models/User.php';

require_once __DIR__ . '/../controllers/Controller.php';
class UserController extends Controller
{
    private $users;
    function __construct()
    {
      $this->users = new UserModel();
     
      
    }
    
    public function login() 
    {
      if (isset($_POST['login'])) {
          $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
          $password = $_POST['password'] ?? '';

          if ($email === false || $password === '') {
              return parent::results_checker('Enter a valid email address and password.',0);
          }

          try {
              
            $user = $this->users->get_user_by_email($email);
          } catch (PDOException $e) {
            error_log($e->getMessage());
            return parent::results_checker('Login could not be completed. Please try again.',0);
          }

          if (!$user || !password_verify($password, $user['password'])) {
              return parent::results_checker('Invalid email or password.',0);
          }

          if (!in_array($user['role'], ['resident', 'admin'], true)) {
              return parent::results_checker('This account has an unsupported role.',0);
          }

          parent::start_session(
              $user['id'],
              $user['full_name'],
              $user['username'],
              $user['phone'],
              $email,
              $user['role']
          );
          $destination = $user['role'] === 'resident'
              ? BASE_URL . '/resources/views/user/user.index.php'
              : BASE_URL . '/resources/views/admin/admin.index.php';
          header('Location: ' . $destination);
          exit;
      }

      if (isset($_POST['register'])) {
          $fullName = trim($_POST['full_name'] ?? '');
          $userName = trim($_POST['username'] ?? '');
          $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
          $phone = trim($_POST['phone'] ?? '');
          $password = $_POST['password'] ?? '';

          if ($fullName === '' || $userName === '' || $phone === '' || $password === '' || $email === false) {
              return parent::results_checker('Complete all fields with a valid email address.',0);
          }

          try {
              $passwordHash = password_hash($password, PASSWORD_DEFAULT);
              $this->users->create_user($fullName, $userName, $email, $phone, $passwordHash);
              return parent::results_checker('Registration successful. You can now log in.',1);
              exit();
          } catch (PDOException $e) {
              error_log($e->getMessage());
              return parent::results_checker('Registration could not be completed. The email or username may already be in use.',0);
          }

          
          header('Location: ' . BASE_URL . '/index.php');
          exit;
      }

      return null;
    }
    public function logout(): void
    {
        if (isset($_POST['logout'])) {
            $_SESSION = [];
            session_destroy();

            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
    }
    
}
    


