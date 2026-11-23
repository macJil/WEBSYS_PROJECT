<?php
require_once __DIR__ . "/../../../app/helpers.php";
session_checker();
$point = $point ?? [];
?>
<fieldset class="mb-3">
    <legend>Coordinates (optional)</legend>
    <p>Enter coordinates only if known. The written address or landmark is sufficient.</p>
    <label>Latitude
        <input
            class="form-control"
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
            name="longitude"
            type="number"
            step="any"
            min="-180"
            max="180"
            value="<?= e($point['longitude'] ?? '') ?>"
        />
    </label>
</fieldset>
