<?php
require_once __DIR__ . '/../../../../app/controllers/EvacuationController.php';
$controller=new EvacuationController();
$id=isset($_GET['edit'])?$controller->id($_GET['edit']):null;
$data=$id?$controller->center($id):['name'=>'','address_details'=>'','capacity'=>0,'status'=>'closed'];
$errors=$controller->saveCenter($id);
if (isPost()) { foreach (['name','address_details','capacity','status'] as $key) { $data[$key]=input($key); } }
$q=is_string($_GET['q']??null)?trim($_GET['q']):'';
$centers=$controller->centers($q); $pageTitle='Evacuation centers';
require __DIR__ . '/../../components/header.php'; require __DIR__ . '/../../components/errors.php';
?>
<h1>Evacuation centers</h1>
<form method="get" class="mb-3"><label>Search name or address <input name="q" value="<?= e($q) ?>"></label> <button class="btn btn-secondary">Search</button> <a href="<?= e(url('resources/views/admin/evacuation/centers.php')) ?>">Clear</a></form>
<div class="table-responsive"><table class="table"><thead><tr><th>Name</th><th>Address</th><th>Status</th><th>People / capacity</th><th>Available places</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($centers as $center): ?><tr><td><?= e($center['name']) ?></td><td><?= e($center['address_details']) ?></td><td><?= e($center['status']) ?></td><td><?= (int)$center['occupancy'] ?> / <?= (int)$center['capacity'] ?></td><td><?= $center['status']==='open'?max(0,(int)$center['capacity']-(int)$center['occupancy']):0 ?></td><td><a href="<?= e(url('resources/views/admin/evacuation/center.php?id='.$center['id'])) ?>">People / history</a> · <a href="<?= e(url('resources/views/admin/evacuation/centers.php?edit='.$center['id'])) ?>">Edit / close</a></td></tr><?php endforeach; ?>
<?php if (!$centers): ?><tr><td colspan="6">No matching centers.</td></tr><?php endif; ?></tbody></table></div>
<h2><?= $id?'Edit center':'Add center' ?></h2>
<form method="post">
<?php foreach (['name'=>['Center name',150],'address_details'=>['Address / location',255]] as $key=>$field): ?><label class="d-block mb-3"><?= e($field[0]) ?><input class="form-control" name="<?= e($key) ?>" maxlength="<?= $field[1] ?>" value="<?= e($data[$key]) ?>" required></label><?php endforeach; ?>
<label class="d-block mb-3">Capacity (people)<input class="form-control" type="number" min="0" max="1000000" name="capacity" value="<?= e($data['capacity']) ?>" required></label>
<label class="d-block mb-3">Status<select class="form-select" name="status"><?php foreach (['open','closed'] as $status): ?><option value="<?= e($status) ?>" <?= $data['status']===$status?'selected':'' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select></label>
<p>Check out all occupants before closing a center. Capacity cannot be reduced below current occupancy.</p>
<?php $point=$data; require __DIR__ . '/../../components/location-picker.php'; ?>
<button class="btn btn-primary" name="action" value="save_center">Save center</button> <a href="<?= e(url('resources/views/admin/evacuation/centers.php')) ?>">New center / cancel edit</a>
</form>
<?php require __DIR__ . '/../../components/footer.php'; ?>
