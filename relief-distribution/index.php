<?php
require_once __DIR__ . '/../relief-inventory/relief_inventory_function.php';
requireRole('admin');

function reliefHouseholds(): array
{
    return reliefDbRows(
        'SELECT id,barangay,purok,representative_name FROM households WHERE archived_at IS NULL ORDER BY household_code',
    );
}

function reliefDbDistribute(int $household, string $receiver, string $notes, array $goods, int $staff, ?int $location): int
{
    if (!$goods) {
        throw new DomainException('Choose at least one good.');
    }
    foreach ($goods as $id => $quantity) {
        if (!is_int($id) || $id < 1 || !is_int($quantity) || $quantity < 1 || $quantity > 1000000000) {
            throw new DomainException('Every good must have a valid ID and positive whole quantity.');
        }
    }
    db()->beginTransaction();
    try {
        if (
            !reliefDbRows('SELECT id FROM households WHERE id=? AND archived_at IS NULL FOR UPDATE', [
                $household,
            ])
        ) {
            throw new DomainException('Select a registered household.');
        }
        if ($location !== null && !reliefDbRows("SELECT id FROM relief_locations WHERE id=? AND archived_at IS NULL AND location_type IN ('both','distribution') FOR UPDATE", [$location])) {
            throw new DomainException('Choose an active distribution point.');
        }
        // Lock in the same item order for every distribution to reduce deadlocks.
        ksort($goods, SORT_NUMERIC);
        foreach ($goods as $id => $quantity) {
            $item = reliefDbRows('SELECT * FROM relief_items WHERE id=? FOR UPDATE', [$id])[0] ?? null;
            if (!$item || $item['archived_at'] !== null) {
                throw new DomainException('One selected item no longer exists.');
            }
            if ($quantity > (int) $item['quantity_on_hand']) {
                throw new DomainException(
                    'Insufficient stock for ' . $item['item_name'] . '. No goods were distributed.',
                );
            }
        }
        $stmt = db()->prepare(
            'INSERT INTO relief_distributions (household_id,received_by,distributed_by,notes,relief_location_id) VALUES (?,?,?,?,?)',
        );
        $stmt->execute([$household, $receiver, $staff, $notes, $location]);
        $distribution = (int) db()->lastInsertId();
        foreach ($goods as $id => $quantity) {
            $stmt = db()->prepare(
                'UPDATE relief_items SET quantity_on_hand=quantity_on_hand-? WHERE id=?',
            );
            $stmt->execute([$quantity, $id]);
            $stmt = db()->prepare(
                'INSERT INTO relief_distribution_items (distribution_id,item_id,quantity) VALUES (?,?,?)',
            );
            $stmt->execute([$distribution, $id, $quantity]);
            reliefDbMovement($id, 'distribution', $quantity, $distribution, $staff, $notes);
        }
        db()->commit();
        return $distribution;
    } catch (Throwable $e) {
        db()->rollBack();
        throw $e;
    }
}

function reliefDbDistributions(string $q, int $page): array
{
    $offset = ($page - 1) * 50;
    return reliefDbRows(
        "SELECT d.*,h.household_code,u.full_name FROM relief_distributions d JOIN households h ON h.id=d.household_id JOIN users u ON u.id=d.distributed_by WHERE (h.household_code LIKE ? ESCAPE '!' OR d.received_by LIKE ? ESCAPE '!') ORDER BY d.id DESC LIMIT 51 OFFSET $offset",
        [reliefDbTerm($q), reliefDbTerm($q)],
    );
}

function reliefDbDistribution(int $id): array
{
    return reliefDbRows(
        'SELECT d.*,h.household_code,u.full_name,l.name AS location FROM relief_distributions d JOIN households h ON h.id=d.household_id JOIN users u ON u.id=d.distributed_by LEFT JOIN relief_locations l ON l.id=d.relief_location_id WHERE d.id=?',
        [$id],
    )[0] ?? [];
}

function reliefDistributedGoods(int $id): array
{
    return reliefDbRows(
        'SELECT d.quantity,i.item_name,i.unit FROM relief_distribution_items d JOIN relief_items i ON i.id=d.item_id WHERE d.distribution_id=? ORDER BY d.id',
        [$id],
    );
}

function reliefDistribution(int $id): array
{
    $row = reliefDbDistribution($id);
    if (!$row) {
        http_response_code(404);
        exit('Distribution not found.');
    }
    return $row;
}

function reliefDistribute(): array
{
    if (!isPost()) {
        return [];
    }
    $receiver = input('received_by');
    $notes = input('notes');
    $raw = $_POST['goods'] ?? null;
    if (
        input('action') !== 'distribute' ||
        $receiver === '' ||
        mb_strlen($receiver) > 100 ||
        mb_strlen($notes) > 255 ||
        !is_array($raw)
    ) {
        return ['Enter receiving representative (up to 100 characters), notes (up to 255 characters), and goods.'];
    }
    $goods = [];
    foreach ($raw as $key => $value) {
        if ($value === '' || $value === '0') {
            continue;
        }
        $id = reliefRecordId($key);
        $quantity = reliefQuantity($value);
        if ($quantity === false) {
            return [
                'Goods quantities must be positive whole numbers (up to 1 billion), or blank/zero to omit.',
            ];
        }
        $goods[$id] = $quantity;
    }
    if (!$goods) {
        return ['Select at least one good by entering its quantity.'];
    }
    $household = reliefRecordId(input('household_id'));
    $location = input('location_id') === '' ? null : reliefRecordId(input('location_id'));
    try {
        $id = reliefDbDistribute($household, $receiver, $notes, $goods, (int) $_SESSION['ID'], $location);
    } catch (Throwable $e) {
        return reliefFailure($e);
    }
    flash('Distribution recorded and stock updated.');
    redirect('relief-distribution/index.php?id=' . $id);
    return [];
}

$id = isset($_GET["id"]) ? reliefRecordId($_GET["id"]) : null;
$distribution = $id ? reliefDistribution($id) : null;
if ($id && isPost()) {
    http_response_code(405);
    exit("Distribution history cannot be edited.");
}
$errors = $id ? [] : reliefDistribute();
$items = $id ? reliefDistributedGoods($id) : reliefItems();
$households = $id ? [] : reliefHouseholds();
$locations = $id ? [] : reliefDbRows("SELECT id,name FROM relief_locations WHERE archived_at IS NULL AND location_type IN ('both','distribution') ORDER BY name");
$q = reliefQueryValue("q");
$page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000000]]);
if (!$page) {
    http_response_code(400);
    exit('Invalid page.');
}
$history = $id ? [] : reliefDbDistributions($q, $page);
$hasNext = count($history) > 50;
if ($hasNext) {
    array_pop($history);
}
$pageTitle = "Relief distributions";
require __DIR__ . "/../includes/header.php";
require __DIR__ . "/../includes/errors.php";
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url('relief-inventory/index.php')) ?>">Back to relief inventory</a></p>
<h1>Relief distributions</h1>
<p><a href="<?= e(url('relief-inventory/index.php')) ?>">Inventory</a> · <a href="<?= e(url('relief-inventory/movements.php')) ?>">Stock movements</a> · <a class="btn btn-outline-secondary" href="<?= e(url('relief-distribution/index.php')) ?>">Back to household distributions</a></p>
<?php if ($distribution): ?>
    <h2>Distribution #<?= (int)$id ?></h2>
    <dl>
        <?php foreach (['household_code' => 'Household','location' => 'Distribution point','received_by' => 'Receiving representative','full_name' => 'Staff','distributed_at' => 'Date/time','notes' => 'Notes'] as $key => $label): ?>
            <dt><?= e($label) ?></dt>
            <dd><?= e($distribution[$key] ?? 'Not recorded') ?></dd>
        <?php endforeach; ?>
    </dl>
    <div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
        <table class="table">
            <thead>
                <tr>
                    <th>Good</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= e($item['item_name']) ?></td>
                        <td><?= (int)$item['quantity'] ?></td>
                        <td><?= e($item['unit']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <h2>Record household distribution</h2>
    <form method="post">
        <label class="d-block mb-3">Registered household<select class="form-select" name="household_id" required>
                <option value="">Choose a household</option>
                <?php foreach ($households as $household): ?>
                    <option value="<?= (int)$household['id'] ?>" <?= input('household_id') === (string)$household['id']?'selected':'' ?>><?= e($household['representative_name'] . ' — ' . ($household['barangay'] ?? 'Unconfirmed barangay') . ' — ' . $household['purok']) ?></option>
                <?php endforeach; ?>
            </select></label>
        <label class="d-block mb-3">Distribution point (optional)
            <select class="form-select" name="location_id">
                <option value="">No point recorded</option>
                <?php foreach ($locations as $location): ?>
                    <option value="<?= (int)$location['id'] ?>" <?= input('location_id') === (string)$location['id'] ? 'selected' : '' ?>><?= e($location['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="d-block mb-3">Receiving representative<input class="form-control" name="received_by" maxlength="100" value="<?= e(input('received_by')) ?>" required /></label>
        <label class="d-block mb-3">Notes<textarea class="form-control" name="notes" maxlength="255">
<?= e(input('notes')) ?></textarea> </label>
        <p>Enter quantities for all goods received in this distribution. Leave other quantities blank or zero. Date/time and staff are recorded automatically.</p>
        <div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
            <table class="table">
                <thead>
                    <tr>
                        <th>Good</th>
                        <th>Unit</th>
                        <th>Available</th>
                        <th>Quantity to distribute</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): $submitted = is_array($_POST['goods'] ?? null)?$_POST['goods']:[];
                        $value = $submitted[$item['id']] ?? ''; ?>
                    <tr>
                        <td><?= e($item['item_name']) ?></td>
                        <td><?= e($item['unit']) ?></td>
                        <td><?= (int)$item['quantity_on_hand'] ?></td>
                        <td>
                            <input aria-label="<?= e('Quantity of ' . $item['item_name']) ?>" type="number" min="0" max="<?= min(1000000000, (int)$item['quantity_on_hand']) ?>" placeholder="0" data-good-quantity="<?= e($item['item_name']) ?>" name="goods[<?= (int)$item['id'] ?>]" value="<?= e(is_scalar($value)?$value:'') ?>" />
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p id="goods-selected" aria-live="polite"></p>
    <script>
        const quantities = document.querySelectorAll('[data-good-quantity]');
        function showSelectedGoods() {
            const selected = Array.from(quantities).filter(input => input.valueAsNumber > 0);
            const lines = selected.map(input => input.dataset.goodQuantity + ' × ' + input.value);
            document.getElementById('goods-selected').textContent = 'Goods selected: ' + (lines.join(', ') || 'none');
        }
        quantities.forEach(input => input.addEventListener('input', showSelectedGoods));
        showSelectedGoods();
    </script>
    <button class="btn btn-primary" name="action" value="distribute">Save distribution and deduct stock</button>
    <a href="<?= e(url('relief-inventory/index.php')) ?>">Cancel and return to relief inventory</a>
</form>
<h2 class="mt-4">Distribution log</h2>
<form method="get" class="filter-form mb-3">
    <label>Search household code or receiver <input name="q" value="<?= e($q) ?>" /></label>
    <button class="btn btn-outline-secondary">Search</button> <a href="<?= e(url('relief-distribution/index.php')) ?>">Clear</a>
</form>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Date/time</th>
                <th>Household</th>
                <th>Receiver</th>
                <th>Staff</th>
                <th>Goods</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $row): ?>
                <tr>
                    <td><?= e($row['distributed_at']) ?></td>
                    <td><?= e($row['household_code']) ?></td>
                    <td><?= e($row['received_by']) ?></td>
                    <td><?= e($row['full_name']) ?></td>
                    <td><a href="<?= e(url('relief-distribution/index.php?id=' . $row['id'])) ?>">View #<?= (int)$row['id'] ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$history): ?>
                <tr>
                    <td colspan="5">No matching distributions.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php if ($page > 1 || $hasNext): ?>
    <nav aria-label="Distribution history pages">
        <?php if ($page > 1): ?><a href="?<?= e(http_build_query(['q' => $q, 'page' => $page - 1])) ?>">Previous</a><?php endif; ?>
        Page <?= $page ?>
        <?php if ($hasNext): ?><a href="?<?= e(http_build_query(['q' => $q, 'page' => $page + 1])) ?>">Next</a><?php endif; ?>
    </nav>
<?php endif; ?>
<?php endif; ?>
<?php
require __DIR__ . "/../includes/footer.php";
?>
