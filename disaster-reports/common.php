<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/evacuation-capacity.php';
const DISASTERSUMMARY_TYPES = [
    'flood' => 'Flooding',
    'fire' => 'Fire',
    'landslide' => 'Landslide',
    'road_obstruction' => 'Road obstruction',
    'other' => 'Other emergency',
];
const DISASTERSUMMARY_STATUSES = ['pending', 'verified', 'responding', 'resolved', 'rejected'];
