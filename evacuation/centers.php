<?php
require_once __DIR__ . '/common.php';
requireRole('admin');
require_once __DIR__ . '/../includes/barangays.php';

function evacuationDbCenters(string $search): array
{
    $term = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
    $centers = evacuationDbRows(
        "SELECT c.*, (SELECT COUNT(*) FROM evacuation_records e WHERE e.center_id=c.id AND e.checked_out_at IS NULL) AS occupancy FROM evacuation_centers c WHERE c.archived_at IS NULL AND (c.name LIKE ? ESCAPE '!' OR c.address_details LIKE ? ESCAPE '!') ORDER BY c.name",
        [$term, $term],
    );
    foreach ($centers as $index => $center) {
        $centers[$index]['available'] = availableCapacity($center);
    }
    return $centers;
}

function evacuationDbSaveCenter(?int $id, array $data): void
{
    db()->beginTransaction();
    try {
        if ($id) {
            $editing = evacuationDbLockCenter($id);
            if ($editing['archived_at'] !== null) {
                throw new DomainException('Archived centers cannot be edited.');
            }
            $count = evacuationDbOccupancy($id);
            if ($data['capacity'] < $count) {
                throw new DomainException('Capacity cannot be less than current occupancy.');
            }
            if ($data['status'] === 'closed' && $count > 0) {
                throw new DomainException('Check out all evacuees before closing this center.');
            }
            $stmt = db()->prepare(
                'UPDATE evacuation_centers SET barangay=?,latitude=?,longitude=?,name=?, address_details=?, capacity=?, status=? WHERE id=?',
            );
            $stmt->execute([
                $data['barangay'],
                $data['latitude'],
                $data['longitude'],
                $data['name'],
                $data['address_details'],
                $data['capacity'],
                $data['status'],
                $id,
            ]);
        } else {
            $stmt = db()->prepare(
                'INSERT INTO evacuation_centers (barangay,latitude,longitude,name,address_details,capacity,status) VALUES (?,?,?,?,?,?,?)',
            );
            $stmt->execute([
                $data['barangay'],
                $data['latitude'],
                $data['longitude'],
                $data['name'],
                $data['address_details'],
                $data['capacity'],
                $data['status'],
            ]);
        }
        db()->commit();
    } catch (Throwable $e) {
        db()->rollBack();
        throw $e;
    }
}


function evacuationSaveCenter(?int $id): array
{
    if (!isPost()) {
        return [];
    }
    if (input('action') !== 'save_center') {
        return ['Unknown form action.'];
    }
    $capacity = filter_var(input('capacity'), FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 0, 'max_range' => 1000000],
    ]);
    $data = [
        'barangay' => input('barangay') ?: null,
        'name' => input('name'),
        'address_details' => input('address_details'),
        'capacity' => $capacity,
        'status' => input('status'),
    ];
    if (
        $data['name'] === '' ||
        strlen($data['name']) > 150 ||
        $data['address_details'] === '' ||
        strlen($data['address_details']) > 255 ||
        $capacity === false ||
        !in_array($data['status'], ['open', 'closed'], true)
    ) {
        return [
            'Enter name (up to 150 bytes), address (up to 255 bytes), whole-person capacity (0–1,000,000), and valid status.',
        ];
    }
    if ($data['barangay'] !== null && !in_array($data['barangay'], ATOK_BARANGAYS, true)) {
        return ['Choose a valid Atok barangay.'];
    }
    $errors = locationErrors($data['address_details'], input('latitude'), input('longitude'));
    if ($errors) {
        return $errors;
    }
    foreach (['latitude', 'longitude'] as $field) {
        $data[$field] = input($field) === '' ? null : input($field);
    }
    try {
        evacuationDbSaveCenter($id, $data);
    } catch (Throwable $e) {
        return evacuationFailure($e);
    }
    flash('Center saved.');
    redirect('evacuation/centers.php');
    return [];
}

$id = isset($_GET["edit"]) ? evacuationRecordId($_GET["edit"]) : null;
$data = $id
    ? evacuationCenter($id)
    : ["name" => "", "address_details" => "", "capacity" => 0, "status" => "closed"];
$errors = evacuationSaveCenter($id);
if (isPost()) {
    foreach (["barangay", "name", "address_details", "capacity", "status", "latitude", "longitude"] as $key) {
        $data[$key] = input($key);
    }
}
$q = is_string($_GET["q"] ?? null) ? trim($_GET["q"]) : "";
$centers = evacuationDbCenters($q);
$pageTitle = "Evacuation centers";
require __DIR__ . "/../includes/header.php";
require __DIR__ . "/../includes/errors.php";
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Evacuation centers</h1>
<p><a class="btn btn-outline-secondary" href="<?= e(url('records/index.php?type=centers')) ?>">Manage records</a></p>
<form method="get" class="filter-form mb-3">
    <label>Search name or address <input class="form-control" placeholder="Center name or address" maxlength="150" name="q" value="<?= e($q) ?>" /></label>
    <button class="btn btn-outline-secondary">Search</button> <a class="btn btn-outline-secondary" href="<?= e(url('evacuation/centers.php')) ?>">Clear</a>
</form>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Status</th>
                <th>People / capacity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($centers as $center): ?>
                <tr>
                    <td><?= e($center['name']) ?></td>
                    <td><?= e($center['address_details']) ?>
                        <?php if (!hasMapPoint($center)): ?>
                            <br /><small>No map point</small>
                        <?php endif; ?>
                    </td>
                    <td><span class="status-badge" data-status="<?= e($center['status']) ?>"><?= e($center['status']) ?></span></td>
                    <td><?= (int)$center['occupancy'] ?> / <?= (int)$center['capacity'] ?><?= capacityBar((int)$center['occupancy'], (int)$center['capacity']) ?></td>
                    <td><a href="<?= e(url('evacuation/center.php?id=' . $center['id'])) ?>">Manage check-ins</a> · <a href="<?= e(url('evacuation/centers.php?edit=' . $center['id'])) ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$centers): ?>
                <tr>
                    <td colspan="5">No matching centers.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<h2><?= $id?'Edit center':'Add center' ?></h2>
<form method="post">
    <label class="d-block mb-3">Center barangay
        <select class="form-select" name="barangay" class="form-select">
            <option value="">Awaiting confirmation</option>
            <?php foreach (ATOK_BARANGAYS as $area): ?>
                <option <?= ($data['barangay'] ?? '') === $area ? 'selected' : '' ?>><?= e($area) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <?php foreach (['name' => ['Center name',150],'address_details' => ['Address / location',255]] as $key => $field): ?>
        <label class="d-block mb-3"><?= e($field[0]) ?><input class="form-control" name="<?= e($key) ?>" maxlength="<?= $field[1] ?>" value="<?= e($data[$key]) ?>" required /></label>
    <?php endforeach; ?>
    <?php
    $point = $data;
    require __DIR__ . "/../includes/location-picker.php";
    ?>
    <label class="d-block mb-3">Capacity (people)<input class="form-control" type="number" min="0" max="1000000" name="capacity" value="<?= e($data['capacity']) ?>" required /></label>
    <label class="d-block mb-3">Status<select class="form-select" name="status">
            <?php foreach (['open','closed'] as $status): ?>
                <option value="<?= e($status) ?>" <?= $data['status'] === $status?'selected':'' ?>><?= e(ucfirst($status)) ?></option>
            <?php endforeach; ?>
        </select></label>
    <?php if ($id): ?><p>Check out all occupants before closing a center. Capacity cannot be reduced below current occupancy.</p><?php endif; ?>

    <button class="btn btn-primary" name="action" value="save_center">Save center</button>
    <a href="<?= e(url('evacuation/centers.php')) ?>">Cancel and return to evacuation centers</a>
</form>
<?php
require __DIR__ . "/../includes/footer.php";
?>
