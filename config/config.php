<?php
// Change connection settings locally for your installation. Keep passwords out of Git.
return [
    'emergency_hotline' => '', // Enter the number confirmed by the office.
    'app_name' => 'Atok MDRRM System',
    'base_url' => '/WEBSYS_PROJECT', // Use '' when served at localhost root.
    'host' => '127.0.0.1',
    'port' => '8889',
    'database' => 'barangay_drrm',
    'username' => 'root',
    'password' => getenv('ATOK_DB_PASSWORD') ?: '',
    // Absolute private folder OUTSIDE your web server document root; PHP must be able to write here.
    'incident_upload_dir' => '',
    'map_latitude' => 16.6272,
    'map_longitude' => 120.7672,
    // Photon-compatible endpoint; empty string disables address suggestions.
    'address_lookup_url' => 'https://photon.komoot.io/reverse',
    'map_zoom' => 14,
    'map_min_zoom' => 11, // Legacy map setting. The shared picker uses the bundled Atok outline.
];
