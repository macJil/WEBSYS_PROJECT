<?php
require_once __DIR__ . "/helpers.php";
requireRole("admin");
?>
<?php if (isset($savedPoint) && hasMapPoint($savedPoint)): ?>
    <p>Saved location: <?= e($savedPoint['address_details']) ?> — <?= e($savedPoint['latitude']) ?>, <?= e($savedPoint['longitude']) ?></p>
    <div id="saved-incident-map" data-kind="<?= e($savedKind) ?>" data-type="<?= e($savedPoint['hazard_type'] ?? $savedPoint['location_type'] ?? $savedPoint['status'] ?? '') ?>" data-lat="<?= e($savedPoint['latitude']) ?>" data-lng="<?= e($savedPoint['longitude']) ?>"> </div>
    <p id="saved-map-status">Saved map point.</p>
    <link rel="stylesheet" href="<?= e(url('assets/vendor/leaflet/leaflet.css')) ?>" />
    <script src="<?= e(url('assets/vendor/leaflet/leaflet.js')) ?>"></script>
    <script src="<?= e(url('assets/js/incident-location.js')) ?>"></script>
<?php else: ?>
    <p>No map point saved.</p>
<?php endif; ?>
