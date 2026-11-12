<?php
// Keep the legacy resident map link connected to the shared map.
require_once __DIR__ . '/../../../app/helpers.php';
requireRole('resident');
redirect('resources/views/maps/map.php');
