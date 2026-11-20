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
<div class="table-responsive">
    <table class="table">
        <tbody>
            <tr>
                <th scope="row">Type</th>
                <td><?= e($location['location_type'] ?? 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Address</th>
                <td><?= e($location['address_details'] ?? 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Latitude</th>
                <td><?= e($location['latitude'] ?? 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Longitude</th>
                <td><?= e($location['longitude'] ?? 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Archived</th>
                <td><?= $location['archived_at']?'Yes':'No' ?></td>
            </tr>
            <tr>
                <th scope="row">Community map</th>
                <td><?= !$location['archived_at'] && in_array($location['location_type'], ['distribution','both'], true) && $location['latitude'] !== null && $location['longitude'] !== null?'Shown automatically':'Not displayed' ?></td>
            </tr>
        </tbody>
    </table>
</div>
<a class="btn btn-outline-secondary" href="<?= e(url('resources/views/maps/map.php')) ?>">Back to map</a>
<?php
require __DIR__ . "/../../components/footer.php";
?>
