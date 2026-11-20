<?php
require_once __DIR__ . "/../../../../app/controllers/ReliefController.php";
$controller = new ReliefController();
$id = isset($_GET["id"]) ? $controller->recordId($_GET["id"]) : null;
$distribution = $id ? $controller->distribution($id) : null;
if ($id && isPost()) {
    http_response_code(405);
    exit("Distribution history cannot be edited.");
}
$errors = $id ? [] : $controller->distribute();
$items = $id ? $controller->distributedGoods($id) : $controller->items();
$households = $id ? [] : $controller->households();
$q = $controller->queryValue("q");
$history = $id ? [] : $controller->distributions($q);
$submitted = is_array($_POST['goods'] ?? null) ? $_POST['goods'] : [];
$pageTitle = "Relief distributions";
require __DIR__ . "/../../components/header.php";
require __DIR__ . "/../../components/errors.php";
$formHouseholdId = input('household_id');
$formReceivedBy = input('received_by');
$formNotes = input('notes');
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Back to relief inventory</a></p>
<h1>Relief distributions</h1>
<p><a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Inventory</a> · <a href="<?= e(url('resources/views/admin/relief/movements.php')) ?>">Stock movements</a> · <a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/relief/distributions.php')) ?>">Back to household distributions</a></p>
<?php if ($distribution): ?>
    <h2>Distribution #<?= (int)$id ?></h2>
    <div class="table-responsive">
        <table class="table">
            <tbody>
                <tr>
                    <th scope="row">Household</th>
                    <td><?= e($distribution['household_label']) ?></td>
                </tr>
                <tr>
                    <th scope="row">Receiving representative</th>
                    <td><?= e($distribution['received_by']) ?></td>
                </tr>
                <tr>
                    <th scope="row">Staff</th>
                    <td><?= e($distribution['full_name']) ?></td>
                </tr>
                <tr>
                    <th scope="row">Date/time</th>
                    <td><?= e($distribution['distributed_at']) ?></td>
                </tr>
                <tr>
                    <th scope="row">Notes</th>
                    <td><?= e($distribution['notes']) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
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
                    <option value="<?= (int)$household['id'] ?>" <?= $formHouseholdId === (string)$household['id']?'selected':'' ?>><?= e($household['household_label']) ?></option>
                <?php endforeach; ?>
            </select></label>
        <label class="d-block mb-3">Receiving representative<input class="form-control" name="received_by" maxlength="100" value="<?= e($formReceivedBy) ?>" required /></label>
        <label class="d-block mb-3">Notes<textarea class="form-control" name="notes" maxlength="255">
<?= e($formNotes) ?></textarea> </label>
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
                    <?php foreach ($items as $item):
                        $value = $submitted[$item['id']] ?? ''; ?>
                    <tr>
                        <td><?= e($item['item_name']) ?></td>
                        <td><?= e($item['unit']) ?></td>
                        <td><?= (int)$item['quantity_on_hand'] ?></td>
                        <td>
                            <input
                                aria-label="<?= e('Quantity of ' . $item['item_name']) ?>"
                                type="number"
                                min="0"
                                max="1000000000"
                                name="goods[<?= (int)$item['id'] ?>]"
                                value="<?= e(is_scalar($value)?$value:'') ?>"
                            />
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <button class="btn btn-primary" name="action" value="distribute">Save distribution and deduct stock</button>
    <a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Cancel and return to relief inventory</a>
</form>
<h2 class="mt-4">Distribution history</h2>
<form method="get" class="filter-form mb-3">
    <label>Search household or receiver <input name="q" value="<?= e($q) ?>" /></label>
    <button class="btn btn-outline-secondary">Search</button> <a href="<?= e(url('resources/views/admin/relief/distributions.php')) ?>">Clear</a>
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
                    <td><?= e($row['household_label']) ?></td>
                    <td><?= e($row['received_by']) ?></td>
                    <td><?= e($row['full_name']) ?></td>
                    <td><a href="<?= e(url('resources/views/admin/relief/distributions.php?id=' . $row['id'])) ?>">View #<?= (int)$row['id'] ?></a>

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
<?php endif; ?>
<?php
require __DIR__ . "/../../components/footer.php";
?>
