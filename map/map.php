<?php
require_once __DIR__ . "/../includes/helpers.php";
session_checker();
$boundary = atokBoundary();
requireRole($_SESSION["ROLE"]);
header("Cache-Control: private, no-store");
$admin = $_SESSION["ROLE"] === "admin";
$pageTitle = "Community map";
require __DIR__ . "/../includes/header.php";
?>
<link rel="stylesheet" href="<?= e(url('assets/vendor/leaflet/leaflet.css')) ?>" />
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1><?= $admin?'Staff map':'Community map' ?></h1>
<p><?= e($settings['demo_barangay_name']) ?>. The outline is approximate. Pins mark recorded locations.</p>
<p>Verified incidents, open centers, and distribution points with saved coordinates appear automatically. Household locations are visible only to staff.</p>
<div class="map-layout">
    <aside class="map-key border rounded p-3" aria-label="Map layers and legend">
        <h2 class="h5">Layers and legend</h2>
        <p>Uncheck a layer to hide its markers. Show all locations turns every layer back on.</p>
        <label class="map-layer">
            <input type="checkbox" data-layer="incidents" checked />
            <span class="map-symbol incident" aria-hidden="true">⚠</span> Incidents <span id="count-incidents"></span></label>
        <p class="small">≈ Flooding · 🔥 Fire · ◩ Landslide · ⛔ Road obstruction · ⚠ Other emergency</p>
        <?php if ($admin): ?>
            <label class="map-layer"><input type="checkbox" data-layer="households" checked>
                <span aria-hidden="true">🏠</span> Household registry (staff only) <span id="count-households"></span>
            </label>
        <?php endif; ?>
        <label class="map-layer">
            <input type="checkbox" data-layer="centers" checked />
            <span class="map-symbol center-open" aria-hidden="true">⌂</span> Evacuation centers <span id="count-centers"></span></label>
        <div class="small mb-2">Blue shelter: open</div>
        <label class="map-layer">
            <input type="checkbox" data-layer="relief_locations" checked />
            <span class="map-symbol relief" aria-hidden="true">▣</span> Relief locations <span id="count-relief_locations"></span></label>
        <button type="button" id="map-home" class="btn btn-outline-secondary my-2">Return to Sayangan</button>
        <button type="button" id="show-atok" class="btn btn-outline-secondary" disabled>Show all Atok</button>
        <button type="button" id="map-fit" class="btn btn-outline-secondary my-2" disabled>Show all locations</button>
        <a class="d-block" href="<?= e(url('map/map.php')) ?>">Refresh saved locations</a>
        <p class="small mt-3">Locations update when you return to this tab. Counts include only records with a pin.</p>
    </aside>
    <div>
        <p class="map-status" id="community-map-status" role="status" aria-live="polite">Loading saved locations…</p>
        <div id="community-map" data-admin="<?= $admin?'1':'0' ?>" data-url="<?= e(url('map/data.php')) ?>" data-base="<?= e($settings['base_url']) ?>" data-boundary="<?= e(json_encode($boundary)) ?>" data-lat="<?= e($boundary['properties']['opening_view'][0]) ?>" data-lng="<?= e($boundary['properties']['opening_view'][1]) ?>" data-zoom="<?= e($settings['map_zoom']) ?>" data-min-zoom="8">
        </div>
        <p id="community-tile-status" role="status" class="small">The map background requires internet.</p>
        <noscript>Enable JavaScript to explore this map. Your regular record pages remain available through navigation.</noscript>
    </div>
</div>
<script src="<?= e(url('assets/vendor/leaflet/leaflet.js')) ?>"></script>
<script src="<?= e(url('assets/js/atok-boundary.js')) ?>"></script>
<script src="<?= e(url('assets/js/community-map.js?v=atok-layers')) ?>"></script>
<?php
require __DIR__ . "/../includes/footer.php";
?>
