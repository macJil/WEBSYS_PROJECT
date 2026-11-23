<?php
// Copy to config.php and change values for your computer. Never commit config.php.
return [
    'app_name' => 'BayanReady',
    'base_url' => '/WEBSYS_PROJECT', // Use '' when served at localhost root.
    'host' => '127.0.0.1',
    'port' => '8889',
    'database' => 'barangay_drrm',
    'username' => 'root',
    'password' => '',
    // Absolute private folder OUTSIDE your web server document root; PHP must be able to write here.
    'incident_upload_dir' => '',
    // Atok municipality demo view only; choose one barangay before deployment.
    'demo_barangay_name' => 'Atok, Benguet (DEMO)',
    'map_latitude' => 16.6272,
    'map_longitude' => 120.7672,
    // Photon-compatible endpoint; empty string disables address suggestions.
    'address_lookup_url' => 'https://photon.komoot.io/reverse',
    'map_zoom' => 14,
    'map_min_zoom' => 11, // Focused demo view; no official boundary restriction.
];
