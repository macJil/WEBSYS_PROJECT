<?php
require_once __DIR__ . '/../../../../app/controllers/ReliefController.php';
$controller=new ReliefController(); $id=isset($_GET['id'])?$controller->id($_GET['id']):null;
$distribution=$id?$controller->distribution($id):null;
if ($id && isPost()) { http_response_code(405); exit('Distribution history cannot be edited.'); }
$errors=$id?[]:$controller->distribute();
$items=$id?$controller->distributedGoods($id):$controller->items();
$households=$id?[]:$controller->households(); $q=$controller->query('q'); $history=$id?[]:$controller->distributions($q);
$pageTitle='Relief distributions'; require __DIR__ . '/../../components/header.php'; require __DIR__ . '/../../components/errors.php';
?>
<h1>Relief distributions</h1>
<p><a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Inventory</a> · <a href="<?= e(url('resources/views/admin/relief/movements.php')) ?>">Stock movements</a> · <a href="<?= e(url('resources/views/admin/relief/distributions.php')) ?>">Household distributions</a></p>

<?php if ($distribution): ?>
<h2>Distribution #<?= (int)$id ?></h2>
<dl><?php foreach (['household_code'=>'Household','received_by'=>'Receiving representative','full_name'=>'Staff','distributed_at'=>'Date/time','notes'=>'Notes'] as $key=>$label): ?><dt><?= e($label) ?></dt><dd><?= e($distribution[$key]) ?></dd><?php endforeach; ?></dl>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records"><table class="table"><thead><tr><th>Good</th><th>Quantity</th><th>Unit</th></tr></thead><tbody><?php foreach ($items as $item): ?><tr><td><?= e($item['item_name']) ?></td><td><?= (int)$item['quantity'] ?></td><td><?= e($item['unit']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php else: ?>
<h2>Record household distribution</h2>
<form method="post">
<label class="d-block mb-3">Registered household<select class="form-select" name="household_id" required><option value="">Choose a household</option><?php foreach ($households as $household): ?><option value="<?= (int)$household['id'] ?>" <?= input('household_id')===(string)$household['id']?'selected':'' ?>><?= e($household['household_code'].' — '.$household['representative_name']) ?></option><?php endforeach; ?></select></label>
<label class="d-block mb-3">Receiving representative<input class="form-control" name="received_by" maxlength="100" value="<?= e(input('received_by')) ?>" required></label>
<label class="d-block mb-3">Notes<textarea class="form-control" name="notes" maxlength="255"><?= e(input('notes')) ?></textarea></label>
<p>Enter quantities for all goods received in this distribution. Leave other quantities blank or zero. Date/time and staff are recorded automatically.</p>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records"><table class="table"><thead><tr><th>Good</th><th>Unit</th><th>Available</th><th>Quantity to distribute</th></tr></thead><tbody>
<?php foreach ($items as $item): $submitted=is_array($_POST['goods']??null)?$_POST['goods']:[]; $value=$submitted[$item['id']]??''; ?><tr><td><?= e($item['item_name']) ?></td><td><?= e($item['unit']) ?></td><td><?= (int)$item['quantity_on_hand'] ?></td><td><input aria-label="<?= e('Quantity of '.$item['item_name']) ?>" type="number" min="0" max="1000000000" name="goods[<?= (int)$item['id'] ?>]" value="<?= e(is_scalar($value)?$value:'') ?>"></td></tr><?php endforeach; ?>
</tbody></table></div>
<button class="btn btn-primary" name="action" value="distribute">Save distribution and deduct stock</button>
</form>
<h2 class="mt-4">Distribution history</h2>
<form method="get" class="filter-form mb-3"><label>Search household code or receiver <input name="q" value="<?= e($q) ?>"></label> <button class="btn btn-secondary">Search</button> <a href="<?= e(url('resources/views/admin/relief/distributions.php')) ?>">Clear</a></form>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records"><table class="table"><thead><tr><th>Date/time</th><th>Household</th><th>Receiver</th><th>Staff</th><th>Goods</th></tr></thead><tbody>
<?php foreach ($history as $row): ?><tr><td><?= e($row['distributed_at']) ?></td><td><?= e($row['household_code']) ?></td><td><?= e($row['received_by']) ?></td><td><?= e($row['full_name']) ?></td><td><a href="<?= e(url('resources/views/admin/relief/distributions.php?id='.$row['id'])) ?>">View #<?= (int)$row['id'] ?></a></td></tr><?php endforeach; ?>
<?php if (!$history): ?><tr><td colspan="5">No matching distributions.</td></tr><?php endif; ?></tbody></table></div>
<?php endif; ?>
<?php require __DIR__ . '/../../components/footer.php'; ?>
