<?php
require_once __DIR__ . '/common.php';
requireRole('admin');

function reliefDbItem(int $id): array
{
    return reliefDbRows('SELECT * FROM relief_items WHERE id=?', [$id])[0] ?? [];
}

function reliefDbSaveItem(?int $id, string $name, string $unit): void
{
    db()->beginTransaction();
    try {
        if ($id) {
            $old = reliefDbRows('SELECT * FROM relief_items WHERE id=? FOR UPDATE', [$id])[0] ?? null;
            if (!$old || $old['archived_at'] !== null) {
                throw new DomainException('Item not found.');
            }
            if (
                $old['unit'] !== $unit &&
                reliefDbRows('SELECT id FROM relief_stock_movements WHERE item_id=? LIMIT 1', [$id])
            ) {
                throw new DomainException(
                    'An item with stock history must keep its unit. Create a separate item for a different unit.',
                );
            }
            $stmt = db()->prepare('UPDATE relief_items SET item_name=?,unit=? WHERE id=?');
            $stmt->execute([$name, $unit, $id]);
        } else {
            $stmt = db()->prepare('INSERT INTO relief_items (item_name,unit) VALUES (?,?)');
            $stmt->execute([$name, $unit]);
        }
        db()->commit();
    } catch (Throwable $e) {
        db()->rollBack();
        throw $e;
    }
}

function reliefItem(int $id): array
{
    $row = reliefDbItem($id);
    if (!$row) {
        http_response_code(404);
        exit('Item not found.');
    }
    return $row;
}

function reliefSaveItem(?int $id): array
{
    if (!isPost()) {
        return [];
    }
    if (input('action') !== 'save_item') {
        return ['Unknown form action.'];
    }
    $name = input('item_name');
    $unit = input('unit');
    if ($name === '' || mb_strlen($name) > 120 || $unit === '' || mb_strlen($unit) > 30) {
        return ['Enter an item name (up to 120 characters) and unit (up to 30 characters).'];
    }
    try {
        reliefDbSaveItem($id, $name, $unit);
    } catch (Throwable $e) {
        return reliefFailure($e);
    }
    flash('Relief item saved.');
    redirect('relief-inventory/index.php');
    return [];
}

$id = isset($_GET["edit"]) ? reliefRecordId($_GET["edit"]) : null;
$item = $id ? reliefItem($id) : ["item_name" => "", "unit" => "pieces"];
$errors = reliefSaveItem($id);
if (isPost()) {
    $item = ["item_name" => input("item_name"), "unit" => input("unit")];
}
$q = reliefQueryValue("q");
$items = reliefItems($q);
$pageTitle = "Relief inventory";
require __DIR__ . "/../includes/header.php";
require __DIR__ . "/../includes/errors.php";
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Relief inventory</h1>
<p><a class="btn btn-outline-secondary" href="<?= e(url('records/index.php?type=goods')) ?>">Manage records</a></p>
<p><?= count($items) ?> active goods.</p>
<p><a href="<?= e(url('relief-inventory/locations.php')) ?>">Manage relief locations</a></p>
<p><a href="<?= e(url('relief-inventory/index.php')) ?>">Inventory</a> · <a href="<?= e(url('relief-inventory/movements.php')) ?>">Stock movements</a> · <a href="<?= e(url('relief-distribution/index.php')) ?>">Household distributions</a></p>
<form method="get" class="filter-form mb-3">
    <label>Search name or unit <input class="form-control" placeholder="Item name or unit" maxlength="120" name="q" value="<?= e($q) ?>" /></label>
    <button class="btn btn-outline-secondary">Search</button> <a class="btn btn-outline-secondary" href="<?= e(url('relief-inventory/index.php')) ?>">Clear</a>
</form>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Good</th>
                <th>Unit</th>
                <th>Available stock</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $row): ?>
                <tr>
                    <td><?= e($row['item_name']) ?></td>
                    <td><?= e($row['unit']) ?></td>
                    <td>
                        <?php if ((int)$row['quantity_on_hand'] === 0): ?>
                            <span class="badge text-bg-danger">Out of stock</span>
                        <?php else: ?>
                            <?= (int)$row['quantity_on_hand'] ?>
                            <?php if ((int)$row['quantity_on_hand'] <= (int)($row['minimum_stock'] ?? 0)): ?>
                                <span class="badge text-bg-warning">Low stock</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td><a href="<?= e(url('relief-inventory/index.php?edit=' . $row['id'])) ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$items): ?>
                <tr>
                    <td colspan="4">No matching goods.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<h2><?= $id?'Edit good':'Add good' ?></h2>
<form method="post">
    <label class="d-block mb-3">Item name<input class="form-control" name="item_name" maxlength="120" value="<?= e($item['item_name']) ?>" required /></label>
    <label class="d-block mb-3">Unit (for example packs, bottles, boxes)<input class="form-control" name="unit" maxlength="30" value="<?= e($item['unit']) ?>" required /></label>
    <p>New goods start at zero stock. Record incoming supplies in Stock movements. Units cannot change after stock history exists.</p>
    <button class="btn btn-primary" name="action" value="save_item">Save good</button>
    <a href="<?= e(url('relief-inventory/index.php')) ?>">Cancel and return to relief inventory</a>
</form>
<?php
require __DIR__ . "/../includes/footer.php";
?>
