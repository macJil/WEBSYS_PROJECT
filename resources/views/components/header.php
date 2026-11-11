<?php require_once dirname(__DIR__, 3) . '/app/helpers.php'; 
require_once __DIR__ . '/../../../app/controllers/UserController.php';
$UserContoller = new UserController();
$UserContoller->logout(); 
session_checker();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
   <link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL . '/resources/assets/css/bootstrap.min.css', ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>

    <!-- As a link -->
    <nav class="navbar">
        
        <form action="" method="post" <?= $_SESSION['ROLE'] !== 'resident' ? 'hidden' : ''?>>
            <span>BayanReady</span>
            <a href="<?= BASE_URL . "/resources/views/user/user.index.php" ?>" class="btn btn-outline-primary">Home</a>
            <a href="<?= BASE_URL . "/resources/views/user/community.map.php" ?>" class="btn btn-outline-primary">Comunity Map</a>
            <a href="<?= BASE_URL . "/resources/views/user/report.incident.php" ?>" class="btn btn-outline-primary">Report Incident</a>
            <a href="<?= BASE_URL . "/resources/views/user/reports.php" ?>" class="btn btn-outline-primary">My Reports</a>
            <a href="<?= BASE_URL . "/resources/views/user/announcement.php" ?>" class="btn btn-outline-primary">Announcements</a>
            <button name="logout" class="btn btn-outline-danger">Log Out</button>
        </form>

         <form action="" method="post" <?= $_SESSION['ROLE'] !== 'admin' ? 'hidden' : ''?>>
            <span>BayanReady</span>
            <a href="" class="btn btn-outline-primary">Overview</a>
            <a href="<?= BASE_URL . "/resources/views/admin/reports/report.php" ?>" class="btn btn-outline-primary">Incedent Reports</a>
            <a href="" class="btn btn-outline-primary">Households</a>
            <a href="" class="btn btn-outline-primary">Evacuation Centers</a>
            <a href="" class="btn btn-outline-primary">Relief Goods</a>
            <a href="<?= BASE_URL . "/resources/views/admin/announcement/announcement.php" ?>" class="btn btn-outline-primary">Announcements</a>
            <a href="" class="btn btn-outline-primary">Disaster reports</a>
            <button name="logout" class="btn btn-outline-danger">Log Out</button>
        </form>

       
       
    </nav>

  

    <main >
