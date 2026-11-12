<?php
require_once __DIR__ . '/../../../app/helpers.php';
session_checker();
$point = $point ?? [];
?>
<fieldset class="location-picker mb-3"><legend>Location point (optional)</legend>
<p>Keep the written address or landmark above. A point does not assess hazard risk.</p>
<?php foreach (['latitude'=>90,'longitude'=>180] as $field=>$limit): ?>
<label><?= e(ucfirst($field)) ?><input class="form-control" data-coordinate="<?= e($field) ?>" name="<?= e($field) ?>" type="number" step="any" min="<?= -$limit ?>" max="<?= $limit ?>" value="<?= e($point[$field]??'') ?>"></label>
<?php endforeach; ?>
<button type="button" class="btn btn-secondary my-2" data-locate>Use my current location</button>
<button type="button" class="btn btn-outline-secondary" data-clear>Clear pin</button>
<div data-picker-map style="height:320px" data-lat="<?= e($settings['map_latitude']) ?>" data-lng="<?= e($settings['map_longitude']) ?>" data-zoom="<?= e($settings['map_zoom']) ?>" data-min-zoom="<?= e($settings['map_min_zoom']) ?>"></div>
<p data-picker-status role="status">Click the map or enter coordinates. Confirm the location before saving.</p>
</fieldset>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= e(url('resources/assets/js/location-picker.js')) ?>" defer></script>
