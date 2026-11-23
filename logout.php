<?php
require_once __DIR__ . '/app/helpers.php';
session_checker();
if (!isPost()) {
    header('Allow: POST');
    http_response_code(405);
    exit('Use the Log out button.');
}
$_SESSION = [];
session_destroy();
redirect('index.php');
