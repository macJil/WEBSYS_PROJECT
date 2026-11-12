<?php
require_once __DIR__ . '/../../../app/helpers.php';
session_checker();
requireRole($_SESSION['ROLE']);
header('Cache-Control: private, no-store');
$admin=$_SESSION['ROLE']==='admin';
$pageTitle='Community map';
require __DIR__ . '/../components/header.php';
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="<?= e(url('resources/assets/css/community-map.css')) ?>">
<h1><?= $admin?'Staff map':'Community map' ?></h1>
<p><?= e($settings['demo_barangay_name']) ?>. Markers show saved locations; they do not predict danger or guarantee safety. The map opens focused on Irisan; you can pan to review other saved points. No approved boundary is enforced.</p>
<?php if ($admin): ?><p>Staff layers include private incident points and manually recorded household concerns.</p>
<?php else: ?><p>Only approved community information is shown. Incident points are approximate. Your private submissions remain available in <a href="<?= e(url('resources/views/user/reports.php')) ?>">My reports</a>.</p><?php endif; ?>
<div class="map-layout">
<aside class="map-key border rounded p-3" aria-label="Map layers and legend">
<h2 class="h5">Layers and legend</h2>
<p>Uncheck a layer to hide its markers.</p>
<label class="map-layer"><input type="checkbox" data-layer="incidents" checked> <span class="map-symbol incident" aria-hidden="true">⚠</span> Incidents <span id="count-incidents"></span></label>
<label class="map-layer"><input type="checkbox" data-layer="centers" checked> <span class="map-symbol center-open" aria-hidden="true">⌂</span> Evacuation centers <span id="count-centers"></span></label>
<div class="small mb-2">Blue shelter: open<?php if ($admin): ?> · <span class="map-symbol center-closed" aria-hidden="true">⌂</span> Gray shelter: closed<?php endif; ?></div>
<label class="map-layer"><input type="checkbox" data-layer="relief_locations" checked> <span class="map-symbol relief" aria-hidden="true">▣</span> Relief locations <span id="count-relief_locations"></span></label>
<?php if ($admin): ?><label class="map-layer"><input type="checkbox" data-layer="households" checked> <span class="map-symbol household" aria-hidden="true">⌂</span> Household concerns <span id="count-households"></span></label><?php endif; ?>
<button type="button" id="map-home" class="btn btn-outline-secondary my-2">Return to demo area</button>
<button type="button" id="map-fit" class="btn btn-outline-secondary my-2" disabled>Show visible markers</button>
<a class="d-block" href="<?= e(url('resources/views/maps/map.php')) ?>">Refresh saved locations</a>
<p class="small mt-3">Refresh saved locations after adding or changing a report. Some points may be outside the current view. Counts represent mapped records only. Records without a valid point remain in their normal lists and totals.</p>
</aside>
<div>
<p id="community-map-status" role="status" aria-live="polite">Loading saved locations…</p>
<div id="community-map" data-admin="<?= $admin?'1':'0' ?>" data-url="<?= e(url('resources/views/maps/data.php')) ?>" data-base="<?= e(BASE_URL) ?>" data-lat="<?= e($settings['map_latitude']) ?>" data-lng="<?= e($settings['map_longitude']) ?>" data-zoom="<?= e($settings['map_zoom']) ?>" data-min-zoom="<?= e($settings['map_min_zoom']) ?>"></div>
<p id="community-tile-status" role="status" class="small">Map tiles require internet. The background map does not confirm the accuracy of a reported point.</p>
<noscript>Enable JavaScript to explore this map. Your regular record pages remain available through navigation.</noscript>
</div></div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= e(url('resources/assets/js/community-map.js')) ?>"></script>
<?php require __DIR__ . '/../components/footer.php'; ?>
