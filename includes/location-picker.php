<?php
require_once __DIR__ . '/session.php';
requireCurrentPassword();
$boundary = atokBoundary();
$addressField = $pickerKind === 'incidents' ? 'location_description' : 'address_details';
?>
<fieldset class="location-picker mb-3" data-kind="<?= e($pickerKind) ?>" data-type="<?= e($point['incident_type'] ?? '') ?>" data-address-url="<?= e(($settings['address_lookup_url'] ?? 'enabled') === '' ? '' : url('map/address.php')) ?>">
    <legend>Location</legend>
    <div class="row g-3">
        <div class="col-md-5">
            <label class="d-block mb-2">Address, purok or nearby landmark
                <input class="form-control" name="<?= e($addressField) ?>" maxlength="255" value="<?= e($point[$addressField] ?? '') ?>" <?= $pickerKind === 'households' ? '' : 'required' ?> />
            </label>
            <p class="small">Type the address or place a pin inside Atok. Check the suggested address before saving.</p>
            <p data-address-status role="status" class="small"></p>
            <button type="button" class="btn btn-outline-secondary" data-locate>Use my current location</button>
            <button type="button" class="btn btn-outline-secondary" data-clear>Clear pin</button>
            <details>
                <summary>Enter coordinates manually</summary>
                <?php foreach (['latitude' => 90, 'longitude' => 180] as $field => $limit): ?>
                    <label class="d-block"><?= e(ucfirst($field)) ?>
                        <input class="form-control" data-coordinate="<?= e($field) ?>" name="<?= e($field) ?>" type="number" step="any" min="<?= -$limit ?>" max="<?= $limit ?>" value="<?= e($point[$field] ?? '') ?>" />
                    </label>
                <?php endforeach; ?>
            </details>
            <?php if (($settings['address_lookup_url'] ?? 'enabled') !== ''): ?>
                <details class="small mt-2">
                    <summary>About address suggestions</summary>
                    Selected coordinates are sent to Photon. Address data © <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>.
                </details>
            <?php endif; ?>
        </div>
        <div class="col-md-7">
            <div data-picker-map aria-label="Select a location in Atok" data-boundary="<?= e(json_encode($boundary)) ?>" data-lat="<?= e($boundary['properties']['opening_view'][0]) ?>" data-lng="<?= e($boundary['properties']['opening_view'][1]) ?>" data-zoom="<?= e($settings['map_zoom']) ?>" data-min-zoom="8"></div>
            <div class="d-flex align-items-center flex-wrap gap-2 mt-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-show-atok disabled>Show all Atok</button>
                <small>Approximate municipal boundary</small>
            </div>
            <p data-picker-status role="status" class="small"></p>
            <p data-picker-availability role="status" class="small"></p>
            <noscript>Type an address or enter coordinates manually.</noscript>
        </div>
    </div>
</fieldset>
<link rel="stylesheet" href="<?= e(url('assets/vendor/leaflet/leaflet.css')) ?>" />
<script src="<?= e(url('assets/vendor/leaflet/leaflet.js')) ?>"></script>
<script src="<?= e(url('assets/js/atok-boundary.js')) ?>"></script>
<script src="<?= e(url('assets/js/location-picker.js?v=record-icons')) ?>" defer></script>
