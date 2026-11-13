<?php require_once __DIR__ . '/../../../app/helpers.php'; ?> <!DOCTYPE html>
<html lang="en"
    ><head
        ><meta charset="UTF-8" /><meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        />
        <title><?= e(($pageTitle ?? 'Home') . ' | ' . $settings['app_name']) ?></title>
        <link rel="stylesheet" href="<?= e(url('resources/assets/css/bootstrap.min.css')) ?>" />
        <link rel="stylesheet" href="<?= e(url('resources/assets/css/bayanready.css')) ?>" /><noscript
            ><style>
                .navigation-collapse.collapse {
                    display: block;
                }
            </style></noscript
        > </head
    ><body>
        <a class="skip-link" href="#main-content">Skip to content</a>
        <?php require __DIR__ . '/navigation.php'; ?>
        <main id="main-content" class="container app-main">
            <?php require __DIR__ . '/messages.php'; ?>
