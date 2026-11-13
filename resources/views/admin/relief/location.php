<?php
require_once __DIR__ . '/../../../../app/controllers/MapController.php';
$location = (new MapController())->reliefLocation($_GET['id'] ?? null);
$pageTitle = 'Relief location';
require __DIR__ . '/../../components/header.php';
?> <?php $savedPoint = $location;
require __DIR__ . '/../../components/saved-point.php'; ?>
<h1><?= e($location['name']) ?></h1>
<dl>
    <?php foreach (['location_type' => 'Type','address_details' => 'Address','latitude' => 'Latitude','longitude' => 'Longitude'] as $key => $label): ?>
    <dt><?= e($label) ?></dt><dd><?= e($location[$key] ?? 'Not recorded') ?></dd>
    <?php endforeach; ?>
    <dt>Archived</dt><dd><?= $location['archived_at']?'Yes':'No' ?></dd> <dt>Community map</dt
    ><dd><?= !$location['archived_at'] && in_array($location['location_type'], ['distribution','both'], true) && $location['latitude'] !== null && $location['longitude'] !== null?'Shown automatically':'Not displayed' ?></dd>
</dl>
<a href="<?= e(url('resources/views/maps/map.php')) ?>">Back to map</a>
<?php require __DIR__ . '/../../components/footer.php'; ?>
