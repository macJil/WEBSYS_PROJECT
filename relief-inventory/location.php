<?php
require_once __DIR__ . '/common.php';
requireRole('admin');



$location = reliefLocationFind(reliefRecordId($_GET['id'] ?? null));
$pageTitle = "Relief location";
require __DIR__ . "/../includes/header.php";
?>

<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url('relief-inventory/locations.php')) ?>">Back to relief locations</a></p>
<h1><?= e($location['name']) ?></h1>
<dl>
    <?php foreach (['location_type' => 'Type','address_details' => 'Address','latitude' => 'Latitude','longitude' => 'Longitude'] as $key => $label): ?>
        <dt><?= e($label) ?></dt>
        <dd><?= e($location[$key] ?? 'Not recorded') ?></dd>
    <?php endforeach; ?>
    <dt>Archived</dt>
    <dd><?= $location['archived_at']?'Yes':'No' ?></dd>
    <dt>Community map</dt>
    <dd><?= !$location['archived_at'] && in_array($location['location_type'], ['distribution','both'], true) && $location['latitude'] !== null && $location['longitude'] !== null?'Shown automatically':'Not displayed' ?></dd>
</dl>
<?php
$savedPoint = $location;
$savedKind = 'relief_locations';
require __DIR__ . "/../includes/saved-point.php";
?>
<a class="btn btn-outline-secondary" href="<?= e(url('map/map.php')) ?>">Back to map</a>
<?php
require __DIR__ . "/../includes/footer.php";
?>
