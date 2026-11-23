<?php
if (isset($_GET['edit'])) {
    $_GET['id'] = $_GET['edit'];
    require __DIR__ . '/../../../households/edit.php';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/../../../households/add.php';
} else {
    require __DIR__ . '/../../../households/index.php';
}
