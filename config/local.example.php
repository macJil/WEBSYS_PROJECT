<?php
// Copy to local.php and change values for your computer. Never commit local.php.
return [
    'base_url' => '/WEBSYS_PROJECT', // Use '' when served at localhost root.
    'host' => '127.0.0.1',
    'port' => '8889',
    'database' => 'barangay_drrm',
    'username' => 'root',
    'password' => '',
    // Absolute private folder OUTSIDE your web server document root; PHP must be able to write here.
    'incident_upload_dir' => '',
    // Irisan demo view only; no approved boundary or stakeholder identity.
    'demo_barangay_name' => 'Barangay Irisan, Baguio City (DEMO)',
    'map_boundary_approved' => false,
    'map_latitude' => 16.41792,
    'map_longitude' => 120.56091,
    'map_zoom' => 15,
];
