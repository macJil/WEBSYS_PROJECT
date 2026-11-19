<?php
require_once __DIR__ . "/../../../app/helpers.php";
session_checker();
$point = $point ?? [];
?>
<fieldset class="location-picker mb-3" data-address-url="<?= e((ADDRESS_LOOKUP_URL) === '' ? '' : url('resources/views/maps/address.php')) ?>">
    <legend>Location point (optional)</legend>
    <p>Confirm the written address or landmark above. A point does not assess hazard risk.</p>
    <?php if ((ADDRESS_LOOKUP_URL) !== ''): ?>
        <p class="small">Selecting or moving a pin, or using current location, sends the selected coordinates to <a href="https://photon.komoot.io/">Photon</a> for an address suggestion. Address data: <a href="https://www.openstreetmap.org/copyright">© OpenStreetMap contributors</a>. Nearby place names may be approximate; correct the address before saving.</p>
    <?php endif; ?>
    <p data-address-status role="status" class="small"></p>
    <label>Latitude
        <input
            class="form-control"
            data-coordinate="latitude"
            name="latitude"
            type="number"
            step="any"
            min="-90"
            max="90"
            value="<?= e($point['latitude'] ?? '') ?>"
        />
    </label>
    <label>Longitude
        <input
            class="form-control"
            data-coordinate="longitude"
            name="longitude"
            type="number"
            step="any"
            min="-180"
            max="180"
            value="<?= e($point['longitude'] ?? '') ?>"
        />
    </label>
    <button type="button" class="btn btn-outline-secondary my-2" data-locate>Use my current location</button>
    <button type="button" class="btn btn-outline-secondary" data-clear>Clear pin</button>
    <div data-picker-map data-lat="<?= e(MAP_LATITUDE) ?>" data-lng="<?= e(MAP_LONGITUDE) ?>" data-zoom="<?= e(MAP_ZOOM) ?>" data-min-zoom="<?= e(MAP_MIN_ZOOM) ?>">
    </div>
    <p data-picker-availability class="small" role="status">The map background needs internet. Written location and coordinate fields remain available.</p>
    <noscript>JavaScript is needed for map selection. You can still type the location and coordinates.</noscript>
    <p data-picker-status role="status">Click the map or enter coordinates. Confirm the location before saving.</p>
</fieldset>
<link rel="stylesheet" href="<?= e(url('resources/assets/vendor/leaflet/leaflet.css')) ?>" />
<script src="<?= e(url('resources/assets/vendor/leaflet/leaflet.js')) ?>"></script>
<script src="<?= e(url('resources/assets/js/location-picker.js?v=20261114-address')) ?>" defer></script>
