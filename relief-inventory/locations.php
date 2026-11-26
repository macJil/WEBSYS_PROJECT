<?php
require_once __DIR__ . '/location-fields.php';
requireRole('admin');

function reliefLocationDbAll(): array
{
    return db()
        ->query('SELECT * FROM relief_locations WHERE archived_at IS NULL ORDER BY name')
        ->fetchAll();
}

function reliefLocationDbSave(?int $id, array $data): void
{
    $values = [
        $data['name'],
        $data['location_type'],
        $data['address_details'],
        $data['latitude'],
        $data['longitude'],
    ];
    if ($id) {
        $stmt = db()->prepare(
            'UPDATE relief_locations SET name=?,location_type=?,address_details=?,latitude=?,longitude=? WHERE id=? AND archived_at IS NULL',
        );
        $values[] = $id;
    } else {
        $stmt = db()->prepare(
            'INSERT INTO relief_locations(name,location_type,address_details,latitude,longitude) VALUES(?,?,?,?,?)',
        );
    }
    $stmt->execute($values);
}

function reliefLocationAll(): array
{
    return reliefLocationDbAll();
}

function reliefLocationSave(?int $id): array
{
    if (!isPost()) {
        return [];
    }
    if ($id !== null && !empty(reliefLocationDbFind($id)['archived_at'])) {
        return ['Archived locations cannot be edited.'];
    }
    $data = [];
    foreach (['name', 'location_type', 'address_details', 'latitude', 'longitude'] as $field) {
        $data[$field] = input($field);
    }
    if (
        input('action') !== 'save_location' ||
        $data['name'] === '' ||
        mb_strlen($data['name']) > 150 ||
        $data['address_details'] === '' ||
        mb_strlen($data['address_details']) > 255 ||
        !in_array($data['location_type'], ['storage', 'distribution', 'both'], true)
    ) {
        return ['Enter name, address, and valid location type.'];
    }
    $errors = locationErrors($data['address_details'], $data['latitude'], $data['longitude']);
    if ($errors) {
        return $errors;
    }
    foreach (['latitude', 'longitude'] as $field) {
        if ($data[$field] === '') {
            $data[$field] = null;
        }
    }
    try {
        reliefLocationDbSave($id, $data);
    } catch (PDOException $e) {
        return ['Location could not be saved.'];
    }
    flash('Location saved.');
    redirect('relief-inventory/locations.php');
    return [];
}

$id = null;
if (isset($_GET["edit"])) {
    $id = filter_var($_GET["edit"], FILTER_VALIDATE_INT);
    if (!$id || $id < 1) {
        http_response_code(400);
        exit("Invalid ID.");
    }
}
$d = $id ? reliefLocationFind($id) : ["name" => "", "address_details" => "", "location_type" => "distribution"];
$errors = reliefLocationSave($id);
if (isPost()) {
    foreach (["name", "address_details", "location_type", "latitude", "longitude"] as $k) {
        $d[$k] = input($k);
    };
}
$pageTitle = "Relief locations";
require __DIR__ . "/../includes/header.php";
require __DIR__ . "/../includes/errors.php";
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url('relief-inventory/index.php')) ?>">Back to relief inventory</a></p>
<h1>Relief locations</h1>
<?php if (!$id): ?>
<p><a class="btn btn-primary" href="#record-form">Add location</a></p>
<p><a class="btn btn-outline-secondary" href="<?= e(url('records/index.php?type=locations')) ?>">Manage records</a></p>
<ul>
    <?php foreach (reliefLocationAll() as $row): ?>
        <li><?= e($row['name']) ?>
            <?php if (!hasMapPoint($row)): ?>
                <small>(No map point)</small>
            <?php endif; ?>
            — <a href="<?= e(url('relief-inventory/location.php?id=' . $row['id'])) ?>">View</a> · <a href="?edit=<?= (int)$row['id'] ?>">Edit</a></li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>
<h2 id="record-form"><?= $id?'Edit':'Add' ?> location</h2>
<form method="post">
    <label class="d-block mb-3">Location name<input class="form-control" name="name" maxlength="150" value="<?= e($d['name']) ?>" required /></label>
    <label class="d-block mb-3">Type<select class="form-select" name="location_type">
            <?php foreach (['storage','distribution','both'] as $type): ?>
                <option <?= $d['location_type'] === $type?'selected':'' ?>><?= e($type) ?></option>
            <?php endforeach; ?>
        </select></label>
    <?php
    $pickerKind = 'relief_locations';
    $point = $d;
    require __DIR__ . "/../includes/location-picker.php";
    ?>


    <button class="btn btn-primary" name="action" value="save_location">Save location</button>
    <a href="<?= e(url('relief-inventory/locations.php')) ?>">Cancel and return to relief locations</a>
</form>
<?php
require __DIR__ . "/../includes/footer.php";
?>
