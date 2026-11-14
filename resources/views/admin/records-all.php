<?php
require_once __DIR__ . '/../../../app/helpers.php';
requireRole('admin');
redirect('resources/views/admin/records.php?type=all');
