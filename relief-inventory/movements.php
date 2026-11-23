<?php
require_once __DIR__ . '/common.php';
requireRole('admin');

function reliefDbStock(int $id, string $type, int $quantity, int $staff, string $notes): void
{
    if (!in_array($type, ['stock_in', 'stock_out'], true) || $quantity < 1 || $quantity > 1000000000) {
        throw new DomainException('Choose a valid movement and positive whole quantity.');
    }
    db()->beginTransaction();
    try {
        $item = reliefDbRows('SELECT * FROM relief_items WHERE id=? FOR UPDATE', [$id])[0] ?? null;
        if (!$item || $item['archived_at'] !== null) {
            throw new DomainException('Item not found.');
        }
        $next = (int) $item['quantity_on_hand'];
        if ($type === 'stock_in') {
            $next += $quantity;
        } else {
            $next -= $quantity;
        }
        if ($next < 0) {
            throw new DomainException('Stock-out exceeds available stock.');
        }
        if ($next > 4294967295) {
            throw new DomainException('Resulting stock exceeds the supported quantity.');
        }
        $stmt = db()->prepare('UPDATE relief_items SET quantity_on_hand=? WHERE id=?');
        $stmt->execute([$next, $id]);
        reliefDbMovement($id, $type, $quantity, null, $staff, $notes);
        db()->commit();
    } catch (Throwable $e) {
        db()->rollBack();
        throw $e;
    }
}

function reliefDbMovements(string $q, string $type, string $date): array
{
    $sql =
        "SELECT m.*,i.item_name,i.unit,u.full_name FROM relief_stock_movements m JOIN relief_items i ON i.id=m.item_id JOIN users u ON u.id=m.recorded_by WHERE m.history_hidden_at IS NULL AND (i.item_name LIKE ? ESCAPE '!' OR m.notes LIKE ? ESCAPE '!' OR u.full_name LIKE ? ESCAPE '!')";
    $args = array_fill(0, 3, reliefDbTerm($q));
    if ($type !== '') {
        $sql .= ' AND m.movement_type=?';
        $args[] = $type;
    }
    if ($date !== '') {
        $sql .= ' AND DATE(m.created_at)=?';
        $args[] = $date;
    }
    return reliefDbRows($sql . ' ORDER BY m.id DESC', $args);
}

function reliefMovements(): array
{
    $type = reliefQueryValue('type');
    $date = reliefQueryValue('date');
    if (
        !in_array($type, ['', 'stock_in', 'stock_out', 'distribution'], true) ||
        ($date !== '' && !validDateInput($date))
    ) {
        http_response_code(400);
        exit('Invalid history filter.');
    }
    return reliefDbMovements(reliefQueryValue('q'), $type, $date);
}

function reliefStock(): array
{
    if (!isPost()) {
        return [];
    }
    $quantity = reliefQuantity(input('quantity'));
    $type = input('movement_type');
    $notes = input('notes');
    if (
        input('action') !== 'stock' ||
        !in_array($type, ['stock_in', 'stock_out'], true) ||
        $quantity === false ||
        $notes === '' ||
        strlen($notes) > 255
    ) {
        return [
            'Choose stock-in/out, enter a positive whole quantity (up to 1 billion), and notes/reason (up to 255 bytes).',
        ];
    }
    $id = reliefRecordId(input('item_id'));
    try {
        reliefDbStock($id, $type, $quantity, (int) $_SESSION['ID'], $notes);
    } catch (Throwable $e) {
        return reliefFailure($e);
    }
    flash('Stock movement recorded.');
    redirect('relief-inventory/movements.php');
    return [];
}

$errors = reliefStock();
$items = reliefItems();
$movements = reliefMovements();
$pageTitle = "Stock movements";
require __DIR__ . "/../resources/views/components/header.php";
require __DIR__ . "/../resources/views/components/errors.php";
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url('relief-inventory/index.php')) ?>">Back to relief inventory</a></p>
<h1>Stock movements</h1>
<p><a href="<?= e(url('relief-inventory/index.php')) ?>">Inventory</a> · <a href="<?= e(url('relief-inventory/movements.php')) ?>">Stock movements</a> · <a href="<?= e(url('relief-distribution/index.php')) ?>">Household distributions</a></p>
<h2>Record stock-in or stock-out</h2>
<p>Use household distributions for goods given to households. Stock-out here is for other reasons, such as damage or expiry. Date/time and staff are recorded automatically when saved.</p>
<form method="post" class="mb-4">
    <label class="d-block mb-2">Good<select class="form-select" name="item_id" required>
            <option value="">Choose a good</option>
            <?php foreach ($items as $item): ?>
                <option value="<?= (int)$item['id'] ?>" <?= input('item_id') === (string)$item['id']?'selected':'' ?>><?= e($item['item_name'] . ' — ' . $item['unit'] . ' — available: ' . $item['quantity_on_hand']) ?></option>
            <?php endforeach; ?>
        </select></label>
    <label>Movement<select class="form-select" name="movement_type">
            <?php foreach (['stock_in' => 'Stock-in','stock_out' => 'Stock-out'] as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= input('movement_type') === $key?'selected':'' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select></label>
    <label>Quantity<input class="form-control" type="number" min="1" max="1000000000" name="quantity" value="<?= e(input('quantity')) ?>" required /></label>
    <label class="d-block my-2">Notes / reason<textarea class="form-control" name="notes" maxlength="255" required>
<?= e(input('notes')) ?></textarea> </label>
    <button class="btn btn-primary" name="action" value="stock">Record movement</button>
    <a href="<?= e(url('relief-inventory/index.php')) ?>">Cancel and return to relief inventory</a>
</form>
<h2>Movement history</h2>
<form method="get" class="filter-form mb-3">
    <label>Search good, notes, staff<input class="form-control" name="q" value="<?= e(reliefQueryValue('q')) ?>" /></label>
    <label>Type<select class="form-select" name="type">
            <?php foreach (['' => 'All','stock_in' => 'Stock-in','stock_out' => 'Stock-out','distribution' => 'Distribution'] as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= reliefQueryValue('type') === $key?'selected':'' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select></label>
    <label>Recorded date<input class="form-control" type="date" name="date" value="<?= e(reliefQueryValue('date')) ?>" /></label>
    <button class="btn btn-outline-secondary">Search</button> <a href="<?= e(url('relief-inventory/movements.php')) ?>">Clear</a>
</form>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Good / unit</th>
                <th>Type</th>
                <th>Quantity</th>
                <th>Staff</th>
                <th>Notes / distribution</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($movements as $row): ?>
                <tr>
                    <td><?= e($row['created_at']) ?></td>
                    <td><?= e($row['item_name'] . ' / ' . $row['unit']) ?></td>
                    <td><?= e($row['movement_type']) ?></td>
                    <td><?= (int)$row['quantity'] ?></td>
                    <td><?= e($row['full_name']) ?></td>
                    <td><?= e($row['notes']) ?>
                        <?php if ($row['distribution_id']): ?>
                            <br /><a href="<?= e(url('relief-distribution/index.php?id=' . $row['distribution_id'])) ?>">Distribution #<?= (int)$row['distribution_id'] ?></a>
                        <?php endif; ?>
                        <?php
                        $historyType = 'stock';
                        $historyId = $row['id'];
                        require __DIR__ . '/../resources/views/components/history-action.php';
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$movements): ?>
                <tr>
                    <td colspan="6">No matching movements.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
require __DIR__ . "/../resources/views/components/footer.php";
?>
