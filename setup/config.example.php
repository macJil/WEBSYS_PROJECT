<?php
// Copy to config/installation.php for machine-specific settings. Keep installation.php private.
return [
    'app_name' => 'Atok MDRRM System',
    'base_url' => '/WEBSYS_PROJECT', // Use '' when served at localhost root.
    'host' => '127.0.0.1',
    'port' => '8889',
    'database' => 'barangay_drrm',
    'username' => 'root',
    'password' => '',
    // Absolute private folder OUTSIDE your web server document root; PHP must be able to write here.
    'incident_upload_dir' => '',
    // Municipal demo label. The approximate outline needs office confirmation.
    'demo_barangay_name' => 'Atok, Benguet (DEMO)',
    'map_latitude' => 16.6272,
    'map_longitude' => 120.7672,
    // Photon-compatible endpoint; empty string disables address suggestions.
    'address_lookup_url' => 'https://photon.komoot.io/reverse',
    'map_zoom' => 14,
    'map_min_zoom' => 11, // Legacy map setting. The shared picker uses the bundled Atok outline.
];
