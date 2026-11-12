<?php
require_once __DIR__ . '/../../../app/helpers.php';
session_checker();
$point = $point ?? [];
?>
<fieldset class="location-picker mb-3" data-address-url="<?= e($settings['address_lookup_url']??'') ?>"><legend>Location point (optional)</legend>
<p>Keep the written address or landmark above. A point does not assess hazard risk.</p>
<?php foreach (['latitude'=>90,'longitude'=>180] as $field=>$limit): ?>
<label><?= e(ucfirst($field)) ?><input class="form-control" data-coordinate="<?= e($field) ?>" name="<?= e($field) ?>" type="number" step="any" min="<?= -$limit ?>" max="<?= $limit ?>" value="<?= e($point[$field]??'') ?>"></label>
<?php endforeach; ?>
<button type="button" class="btn btn-secondary my-2" data-locate>Use my current location</button>
<button type="button" class="btn btn-outline-secondary" data-clear>Clear pin</button>
<div data-picker-map style="height:320px" data-lat="<?= e($settings['map_latitude']) ?>" data-lng="<?= e($settings['map_longitude']) ?>" data-zoom="<?= e($settings['map_zoom']) ?>" data-min-zoom="<?= e($settings['map_min_zoom']) ?>"></div>
<?php if (!empty($settings['address_suggestions_enabled'])): ?>
<div class="mt-3">
<label><input type="checkbox" data-address-enabled checked> Suggest an address when I choose a point</label>
<p class="small">Sends the selected coordinates to Photon. Use fictional demo points for now. You can turn this off and type a location yourself.</p>
<p data-address-status role="status" aria-live="polite">No address lookup until you choose a point. Suggestions may be nearby or incomplete; confirm the address.</p>
<button type="button" class="btn btn-outline-secondary" data-use-address hidden>Use suggested address</button>
<p class="small">Address suggestions: <a href="https://photon.komoot.io/" target="_blank" rel="noopener noreferrer">Photon</a> · Data © <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap contributors (ODbL)</a>.</p>
</div>
<?php endif; ?>
<p data-picker-status role="status">Click the map or enter coordinates. Confirm the location before saving.</p>
</fieldset>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= e(url('resources/assets/js/location-picker.js')) ?>" defer></script>

<script src="<?= e(url('resources/assets/js/address-suggestion.js')) ?>" defer></script>
