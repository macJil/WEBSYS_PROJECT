<?php
require_once __DIR__ . "/helpers.php";
session_checker();
$boundary = atokBoundary();
$point = $point ?? [];
?>
<fieldset class="location-picker mb-3" data-address-url="<?= e(($settings['address_lookup_url'] ?? 'enabled') === '' ? '' : url('map/address.php')) ?>">
    <legend>Location point (optional)</legend>
    <p>Confirm the written address or landmark above. A point does not assess hazard risk.</p>
    <?php if (($settings['address_lookup_url'] ?? 'https://photon.komoot.io/reverse') !== ''): ?>
        <p class="small">Selecting or moving a pin, or using current location, sends the selected coordinates to <a href="https://photon.komoot.io/">Photon</a> for an address suggestion. Address data: <a href="https://www.openstreetmap.org/copyright">© OpenStreetMap contributors</a>. Nearby place names may be approximate; correct the address before saving.</p>
    <?php endif; ?>
    <p data-address-status role="status" class="small"></p>
    <?php foreach (['latitude' => 90,'longitude' => 180] as $field => $limit): ?>
        <label><?= e(ucfirst($field)) ?><input class="form-control" data-coordinate="<?= e($field) ?>" name="<?= e($field) ?>" type="number" step="any" min="<?= -$limit ?>" max="<?= $limit ?>" value="<?= e($point[$field] ?? '') ?>" /></label>
    <?php endforeach; ?>
    <button type="button" class="btn btn-outline-secondary my-2" data-locate>Use my current location</button>
    <button type="button" class="btn btn-outline-secondary" data-clear>Clear pin</button>
    <button type="button" class="btn btn-outline-secondary" data-show-atok disabled>Show all Atok</button>
    <p>Approximate Atok demo boundary, pending municipal confirmation. Choose a pin inside the outline.</p>
    <div data-picker-map data-boundary="<?= e(json_encode($boundary)) ?>" data-lat="<?= e($boundary['properties']['opening_view'][0]) ?>" data-lng="<?= e($boundary['properties']['opening_view'][1]) ?>" data-zoom="<?= e($settings['map_zoom']) ?>" data-min-zoom="8">
    </div>
    <p data-picker-availability class="small" role="status">The map background needs internet. Written location and coordinate fields remain available.</p>
    <noscript>JavaScript is needed for map selection. You can still type the location and coordinates.</noscript>
    <p data-picker-status role="status">Click the map or enter coordinates. Confirm the location before saving.</p>
</fieldset>
<link rel="stylesheet" href="<?= e(url('assets/vendor/leaflet/leaflet.css')) ?>" />
<script src="<?= e(url('assets/vendor/leaflet/leaflet.js')) ?>"></script>
<script src="<?= e(url('assets/js/atok-boundary.js')) ?>"></script>
<script src="<?= e(url('assets/js/location-picker.js?v=atok-boundary')) ?>" defer></script>
