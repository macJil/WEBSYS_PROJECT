<?php require_once __DIR__ . '/../../../app/helpers.php';
requireRole('admin'); ?> <?php if (isset($savedPoint) && hasMapPoint($savedPoint)): ?>
    <p>Saved location: <?= e($savedPoint['address_details']) ?> — <?= e($savedPoint['latitude']) ?>, <?= e($savedPoint['longitude']) ?></p>
    <div id="saved-incident-map" data-lat="<?= e($savedPoint['latitude']) ?>" data-lng="<?= e($savedPoint['longitude']) ?>"> </div>
    <p id="saved-map-status">Reported location only; no automatic hazard assessment.</p>
    <link rel="stylesheet" href="<?= e(url('resources/assets/vendor/leaflet/leaflet.css')) ?>" />
    <script src="<?= e(url('resources/assets/vendor/leaflet/leaflet.js')) ?>"></script>
    <script src="<?= e(url('resources/assets/js/incident-location.js')) ?>"></script>
<?php else: ?><p>No map point. This record remains available in lists and totals.</p><?php endif; ?>