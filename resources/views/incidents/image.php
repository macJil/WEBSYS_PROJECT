<?php
require_once __DIR__ . '/../../../app/controllers/ReportController.php';
session_checker();
$controller = new ReportController($_SESSION['ROLE']);
$controller->serveImage($controller->id());
