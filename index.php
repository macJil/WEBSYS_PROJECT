<?php
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/controllers/UserController.php';


$UserController = new UserController();


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="resources/assets/css/bootstrap.min.css">
  <title>Login / Register</title>
</head>
<body>
  <div class="container" style="max-width: 32rem; text-align: center">
    
    <?= $UserController->login()?>
    <form action="" method="post" class="card mb-3">
      <div class="card-header"><h3>Login</h3></div>
      <div class="card-body">
        <label for="login-email">Email</label>
        <input id="login-email" type="email" name="email" class="form-control" required>
        <label for="login-password">Password</label>
        <input id="login-password" type="password" name="password" class="form-control" required>
        <button type="submit" name="login" value="1" class="btn btn-primary mt-3">Login</button>
      </div>
    </form>

    <form action="" method="post" class="card">
      <div class="card-header"><h3>Register</h3></div>
      <div class="card-body">
        <label for="full-name">Full Name</label>
        <input id="full-name" type="text" name="full_name" class="form-control" required>
        <label for="user-name">User Name</label>
        <input id="user-name" type="text" name="username" class="form-control" required>
        <label for="register-email">Email</label>
        <input id="register-email" type="email" name="email" class="form-control" required>
        <label for="phone">Contact Number</label>
        <input id="phone" type="text" name="phone" class="form-control" required>
        <label for="register-password">Password</label>
        <input id="register-password" type="password" name="password" class="form-control" required>
        <button type="submit" name="register" value="1" class="btn btn-success mt-3">Register</button>
      </div>
    </form>
  </div>
  <script src="resources/assets/js/bootstrap.bundle.min.js"></script>

</body>
</html>