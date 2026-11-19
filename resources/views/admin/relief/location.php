<?php
require_once __DIR__ . "/../../../../app/controllers/MapController.php";
$location = (new MapController())->reliefLocation($_GET["id"] ?? null);
$pageTitle = "Relief location";
require __DIR__ . "/../../components/header.php";
?>
<?php
$savedPoint = $location;
require __DIR__ . "/../../components/saved-point.php";
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/relief/locations.php')) ?>">Back to relief locations</a></p>
<h1><?= e($location['name']) ?></h1>
<p>Barangay: <?= e($location['barangay'] ?? 'Awaiting confirmation') ?></p>
<dl>
    <dt>Type</dt>
    <dd><?= e($location['location_type'] ?? 'Not recorded') ?></dd>
    <dt>Address</dt>
    <dd><?= e($location['address_details'] ?? 'Not recorded') ?></dd>
    <dt>Latitude</dt>
    <dd><?= e($location['latitude'] ?? 'Not recorded') ?></dd>
    <dt>Longitude</dt>
    <dd><?= e($location['longitude'] ?? 'Not recorded') ?></dd>
    <dt>Archived</dt>
    <dd><?= $location['archived_at']?'Yes':'No' ?></dd>
    <dt>Community map</dt>
    <dd><?= !$location['archived_at'] && in_array($location['location_type'], ['distribution','both'], true) && $location['latitude'] !== null && $location['longitude'] !== null?'Shown automatically':'Not displayed' ?></dd>
</dl>
<a class="btn btn-outline-secondary" href="<?= e(url('resources/views/maps/map.php')) ?>">Back to map</a>
<?php
require __DIR__ . "/../../components/footer.php";
?>
