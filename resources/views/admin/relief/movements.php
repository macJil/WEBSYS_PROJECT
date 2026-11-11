<?php
require_once __DIR__ . '/../../../../app/controllers/ReliefController.php';
$controller=new ReliefController(); $errors=$controller->stock(); $items=$controller->items(); $movements=$controller->movements();
$pageTitle='Stock movements'; require __DIR__ . '/../../components/header.php'; require __DIR__ . '/../../components/errors.php';
?>
<h1>Stock movements</h1>
<p><a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Inventory</a> · <a href="<?= e(url('resources/views/admin/relief/movements.php')) ?>">Stock movements</a> · <a href="<?= e(url('resources/views/admin/relief/distributions.php')) ?>">Household distributions</a></p>

<h2>Record stock-in or stock-out</h2>
<p>Use household distributions for goods given to households. Stock-out here is for other reasons, such as damage or expiry. Date/time and staff are recorded automatically when saved.</p>
<form method="post" class="mb-4">
<label class="d-block mb-2">Good<select class="form-select" name="item_id" required><option value="">Choose a good</option><?php foreach ($items as $item): ?><option value="<?= (int)$item['id'] ?>" <?= input('item_id')===(string)$item['id']?'selected':'' ?>><?= e($item['item_name'].' — '.$item['unit'].' — available: '.$item['quantity_on_hand']) ?></option><?php endforeach; ?></select></label>
<label>Movement<select class="form-select" name="movement_type"><?php foreach (['stock_in'=>'Stock-in','stock_out'=>'Stock-out'] as $key=>$label): ?><option value="<?= e($key) ?>" <?= input('movement_type')===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
<label>Quantity<input class="form-control" type="number" min="1" max="1000000000" name="quantity" value="<?= e(input('quantity')) ?>" required></label>
<label class="d-block my-2">Notes / reason<textarea class="form-control" name="notes" maxlength="255" required><?= e(input('notes')) ?></textarea></label>
<button class="btn btn-primary" name="action" value="stock">Record movement</button></form>
<h2>Movement history</h2>
<form method="get" class="mb-3">
<label>Search good, notes, staff<input class="form-control" name="q" value="<?= e($controller->query('q')) ?>"></label>
<label>Type<select class="form-select" name="type"><?php foreach ([''=>'All','stock_in'=>'Stock-in','stock_out'=>'Stock-out','distribution'=>'Distribution'] as $key=>$label): ?><option value="<?= e($key) ?>" <?= $controller->query('type')===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
<label>Recorded date<input class="form-control" type="date" name="date" value="<?= e($controller->query('date')) ?>"></label>
<button class="btn btn-secondary">Search</button> <a href="<?= e(url('resources/views/admin/relief/movements.php')) ?>">Clear</a></form>
<div class="table-responsive"><table class="table"><thead><tr><th>Date</th><th>Good / unit</th><th>Type</th><th>Quantity</th><th>Staff</th><th>Notes / distribution</th></tr></thead><tbody>
<?php foreach ($movements as $row): ?><tr><td><?= e($row['created_at']) ?></td><td><?= e($row['item_name'].' / '.$row['unit']) ?></td><td><?= e($row['movement_type']) ?></td><td><?= (int)$row['quantity'] ?></td><td><?= e($row['full_name']) ?></td><td><?= e($row['notes']) ?><?php if ($row['distribution_id']): ?><br><a href="<?= e(url('resources/views/admin/relief/distributions.php?id='.$row['distribution_id'])) ?>">Distribution #<?= (int)$row['distribution_id'] ?></a><?php endif; ?></td></tr><?php endforeach; ?>
<?php if (!$movements): ?><tr><td colspan="6">No matching movements.</td></tr><?php endif; ?></tbody></table></div>
<?php require __DIR__ . '/../../components/footer.php'; ?>
