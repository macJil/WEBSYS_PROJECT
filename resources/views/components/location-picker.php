<?php
require_once __DIR__ . '/../../../app/helpers.php';
session_checker();
$point = $point ?? [];
?>
<fieldset class="location-picker mb-3" data-address-url="<?= e($settings['address_lookup_url'] ?? '') ?>"
    ><legend>Location point (optional)</legend>
    <p>Keep the written address or landmark above. A point does not assess hazard risk.</p>
    <?php foreach (['latitude' => 90,'longitude' => 180] as $field => $limit): ?>
    <label
        ><?= e(ucfirst($field)) ?><input
            class="form-control"
            data-coordinate="<?= e($field) ?>"
            name="<?= e($field) ?>"
            type="number"
            step="any"
            min="<?= -$limit ?>"
            max="<?= $limit ?>"
            value="<?= e($point[$field] ?? '') ?>"
    /></label>
    <?php endforeach; ?>
    <button type="button" class="btn btn-secondary my-2" data-locate
        >Use my current location</button
    >
    <button type="button" class="btn btn-outline-secondary" data-clear>Clear pin</button>
    <div
        data-picker-map
        data-lat="<?= e($settings['map_latitude']) ?>"
        data-lng="<?= e($settings['map_longitude']) ?>"
        data-zoom="<?= e($settings['map_zoom']) ?>"
        data-min-zoom="<?= e($settings['map_min_zoom']) ?>"
    >
    </div>
    <p data-picker-availability class="small" role="status"
        >The map background needs internet. Written location and coordinate fields remain
        available.</p
    >
    <noscript
        >JavaScript is needed for map selection. You can still type the location and
        coordinates.</noscript
    >
    <p data-picker-status role="status"
        >Click the map or enter coordinates. Confirm the location before saving.</p
    >
</fieldset>
<link rel="stylesheet" href="<?= e(url('resources/assets/vendor/leaflet/leaflet.css')) ?>" />
<script src="<?= e(url('resources/assets/vendor/leaflet/leaflet.js')) ?>"></script>
<script src="<?= e(url('resources/assets/js/location-picker.js')) ?>" defer></script>
