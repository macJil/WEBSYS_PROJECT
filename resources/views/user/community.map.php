<?php
// Legacy link: point selection is part of the resident incident form.
require_once __DIR__ . '/../../../app/helpers.php';
requireRole('resident');
redirect('resources/views/user/report.incident.php');
