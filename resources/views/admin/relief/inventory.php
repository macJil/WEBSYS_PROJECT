<?php
require_once __DIR__ . '/../../../../app/controllers/ReliefController.php';
$controller=new ReliefController(); $id=isset($_GET['edit'])?$controller->id($_GET['edit']):null;
$item=$id?$controller->item($id):['item_name'=>'','unit'=>'pieces'];
$errors=$controller->saveItem($id);
if (isPost()) { $item=['item_name'=>input('item_name'),'unit'=>input('unit')]; }
$q=$controller->query('q'); $low=$controller->query('low')==='1'; $items=$controller->items($q,$low); $pageTitle='Relief inventory';
require __DIR__ . '/../../components/header.php'; require __DIR__ . '/../../components/errors.php';
?>
<h1>Relief inventory</h1>
<p><a href="<?= e(url('resources/views/admin/records.php?type=goods')) ?>">Select records / view archived</a></p><p><?= count($items) ?> <?= $low?'low-stock':'active' ?> goods.</p><p><a href="<?= e(url('resources/views/admin/relief/locations.php')) ?>">Manage relief locations</a></p>
<p><a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Inventory</a> · <a href="<?= e(url('resources/views/admin/relief/movements.php')) ?>">Stock movements</a> · <a href="<?= e(url('resources/views/admin/relief/distributions.php')) ?>">Household distributions</a></p>

<form method="get" class="filter-form mb-3"><label><input type="checkbox" name="low" value="1" <?= $low?'checked':'' ?>> Low stock only</label> <label>Search name or unit <input name="q" value="<?= e($q) ?>"></label> <button class="btn btn-secondary">Search</button> <a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Clear</a></form>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records"><table class="table"><thead><tr><th>Good</th><th>Unit</th><th>Available stock</th><th>Action</th></tr></thead><tbody>
<?php foreach ($items as $row): ?><tr><td><?= e($row['item_name']) ?></td><td><?= e($row['unit']) ?></td><td><?= (int)$row['quantity_on_hand'] ?></td><td><a href="<?= e(url('resources/views/admin/relief/inventory.php?edit='.$row['id'])) ?>">Edit</a></td></tr><?php endforeach; ?>
<?php if (!$items): ?><tr><td colspan="4">No matching goods.</td></tr><?php endif; ?></tbody></table></div>
<h2><?= $id?'Edit good':'Add good' ?></h2>
<form method="post">
<label class="d-block mb-3">Item name<input class="form-control" name="item_name" maxlength="120" value="<?= e($item['item_name']) ?>" required></label>
<label class="d-block mb-3">Unit (for example packs, bottles, boxes)<input class="form-control" name="unit" maxlength="30" value="<?= e($item['unit']) ?>" required></label>
<label class="d-block mb-3">Minimum stock<input class="form-control" type="number" name="minimum_stock" min="0" max="1000000000" value="<?= e(isPost()?input('minimum_stock'):($item['minimum_stock']??0)) ?>" required></label><p>Zero disables the warning. Stock below this number is low; the quantity uses this item's unit.</p>
<p>New goods start at zero stock. Record incoming supplies in Stock movements. Units cannot change after stock history exists.</p>
<button class="btn btn-primary" name="action" value="save_item">Save good</button> <a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">New good / cancel edit</a>
</form>
<?php require __DIR__ . '/../../components/footer.php'; ?>
