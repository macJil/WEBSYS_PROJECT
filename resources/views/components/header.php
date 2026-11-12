<?php require_once __DIR__ . '/../../../app/helpers.php'; ?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($pageTitle ?? 'Home') . ' | ' . $settings['app_name']) ?></title>
<link rel="stylesheet" href="<?= e(url('resources/assets/css/bootstrap.min.css')) ?>"></head><body>
<?php require __DIR__ . '/navigation.php'; ?>
<main class="container py-4">
<?php require __DIR__ . '/messages.php'; ?>
