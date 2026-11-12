<?php
// Copy to local.php and change values for your computer. Never commit local.php.
return [
    'base_url' => '/WEBSYS_PROJECT', // Use '' when served at localhost root.
    'host' => '127.0.0.1',
    'port' => '8889',
    'database' => 'barangay_drrm',
    'username' => 'root',
    'password' => 'root',
    // Absolute private folder OUTSIDE your web server document root; PHP must be able to write here.
    'incident_upload_dir' => '',
    // Philippines overview until the team selects a barangay.
    'map_latitude' => 12.8797,
    'map_longitude' => 121.7740,
    'map_zoom' => 6,
];
