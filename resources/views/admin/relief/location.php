<?php
require_once __DIR__ . "/../../../../app/controllers/ReliefLocationController.php";
$controller = new ReliefLocationController();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(400);
    exit('Invalid location ID.');
}
$location = $controller->find($id);
$pageTitle = "Relief location";
require __DIR__ . "/../../components/header.php";
?>

<p class="return-link"><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/relief/locations.php')) ?>">Back to relief locations</a></p>
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
        </tbody>
    </table>
</div>
<?php
require __DIR__ . "/../../components/footer.php";
?>
