<?php require_once __DIR__.'/../../../app/helpers.php'; requireRole('admin'); ?>
<?php if(isset($savedPoint) && $savedPoint['latitude']!==null && $savedPoint['longitude']!==null): ?>
<p>Saved location: <?= e($savedPoint['address_details']) ?> — <?= e($savedPoint['latitude']) ?>, <?= e($savedPoint['longitude']) ?></p>
<div id="saved-incident-map" style="height:280px" data-lat="<?= e($savedPoint['latitude']) ?>" data-lng="<?= e($savedPoint['longitude']) ?>"></div><p id="saved-map-status">Reported location only; no automatic hazard assessment.</p>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"><script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script><script src="<?= e(url('resources/assets/js/incident-location.js')) ?>"></script>
<?php else: ?><p>No saved map point.</p><?php endif; ?>
