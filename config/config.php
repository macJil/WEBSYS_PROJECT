<?php
// Each installation can supply its own database and upload settings.
if (is_file(__DIR__ . '/installation.php')) {
    return require __DIR__ . '/installation.php';
}

return require __DIR__ . '/../setup/config.example.php';
