<?php
$settings = require __DIR__ . '/local.example.php';
if (is_file(__DIR__ . '/local.php')) {
    $settings = array_replace($settings, require __DIR__ . '/local.php');
}
return $settings;
