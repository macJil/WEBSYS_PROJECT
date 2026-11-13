<?php
require_once __DIR__ . '/../../../../app/controllers/ReliefLocationController.php';
$c = new ReliefLocationController();
$id = null;
if (isset($_GET['edit'])) {
    $id = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if (!$id || $id < 1) {
        http_response_code(400);
        exit('Invalid ID.');
    }
}
$d = $id ? $c->find($id) : ['name' => '', 'address_details' => '', 'location_type' => 'distribution'];
$errors = $c->save($id);
if (isPost()) foreach (['name', 'address_details', 'location_type', 'latitude', 'longitude'] as $k) $d[$k] = input($k);
$pageTitle = 'Relief locations';
require __DIR__ . '/../../components/header.php';
require __DIR__ . '/../../components/errors.php';
?>
<h1>Relief locations</h1>
<p><a href="<?= e(url('resources/views/admin/records.php?type=locations')) ?>">Select records / view archived</a></p>
<ul><?php foreach ($c->all() as $row): ?><li><?= e($row['name']) ?><?php if (!hasMapPoint($row)): ?> <small>(No map point)</small><?php endif; ?>
        — <a href="<?= e(url('resources/views/admin/relief/location.php?id=' . $row['id'])) ?>">View</a> ·
        <a href="?edit=<?= (int)$row['id'] ?>">Edit</a>
        </li><?php endforeach; ?></ul>
<h2><?= $id ? 'Edit' : 'Add' ?> location</h2>
<form method="post">
    <?php foreach (['name' => 'Name', 'address_details' => 'Address / landmark'] as $k => $label): ?><label class="d-block mb-3"><?= e($label) ?><input
                class="form-control"
                name="<?= e($k) ?>"
                value="<?= e($d[$k]) ?>"
                required /></label><?php endforeach; ?>
    <label>Type<select name="location_type"><?php foreach (['storage', 'distribution', 'both'] as $type): ?><option <?= $d['location_type'] === $type ? 'selected' : '' ?>><?= e($type) ?></option><?php endforeach; ?></select></label>
    <?php $point = $d;
    require __DIR__ . '/../../components/location-picker.php'; ?>
    <button class="btn btn-primary" name="action" value="save_location">Save location</button>
    <a href="locations.php">New location / cancel</a>
</form>
<?php require __DIR__ . '/../../components/footer.php'; ?>