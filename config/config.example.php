<?php
// Copy to config.php and change values for your computer. Never commit config.php.
const APP_NAME = 'BayanReady';
const BASE_URL = '/WEBSYS_PROJECT'; // Use '' when served at localhost root.
const DB_HOST = '127.0.0.1';
const DB_PORT = '8889';
const DB_NAME = 'barangay_drrm';
const DB_USER = 'root';
const DB_PASSWORD = '';
// Absolute private folder OUTSIDE your web server document root; PHP must be able to write here.
const INCIDENT_UPLOAD_DIR = '';
// Atok municipality demo view only; choose one barangay before deployment.
const DEMO_BARANGAY_NAME = 'Atok, Benguet (DEMO)';
const MAP_LATITUDE = 16.6272;
const MAP_LONGITUDE = 120.7672;
// Photon-compatible endpoint; empty string disables address suggestions.
const ADDRESS_LOOKUP_URL = 'https://photon.komoot.io/reverse';
const MAP_ZOOM = 14;
const MAP_MIN_ZOOM = 11; // Focused demo view; no official boundary restriction.
