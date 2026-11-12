<?php
require_once __DIR__ . '/../../../app/controllers/MapPublicationController.php';
$c = new MapPublicationController();
$type = is_string($_GET['type'] ?? null)?$_GET['type']:'';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(400);
    exit('Invalid ID.');
}
try {
    $record = $c->find($type, $id);
} catch (DomainException $e) {
    http_response_code(404);
    exit('Record not found.');
}
$errors = $c->save($type, $id);
$pageTitle = 'Community map visibility';
require __DIR__ . '/../components/header.php';
require __DIR__ . '/../components/errors.php';
?>
<h1>Community map visibility</h1>
<p><?= e(ucfirst($type)) ?> #<?= (int)$id ?> · <?= $record['is_public']?'Approved':'Not approved' ?></p>
<p>Approval is separate from incident verification. Archived records, closed centers, and rejected incidents are hidden automatically.</p>
<form method="post">
<?php if ($type === 'incident'): ?>
<p>Use a general label and an approximate public point. Do not include names, contacts, household details, or the private report description. These fields never change the original report location.</p>
<?php foreach (['public_label' => 'Safe public location label','public_latitude' => 'Approximate public latitude','public_longitude' => 'Approximate public longitude'] as $key => $label): ?>
<label class="d-block mb-3"><?= e($label) ?><input class="form-control" name="<?= e($key) ?>" <?= $key === 'public_label'?'maxlength="150"':'type="number" step="any"' ?> value="<?= e(isPost()?input($key):($record[$key] ?? '')) ?>"></label>
<?php endforeach; ?>
<?php else: ?>
<p>Residents will see the saved facility name, address, and map point. Confirm these are suitable for community display.</p>
<p><?= e($record['name']) ?> · <?= e($record['address_details'] ?? '') ?></p>
<?php endif; ?>
<button class="btn btn-primary" name="action" value="publish">Approve for community map</button>
<button class="btn btn-outline-secondary" name="action" value="withdraw">Withdraw from community map</button>
</form>
<a href="<?= e(url('resources/views/maps/map.php')) ?>">Review map</a>
<?php require __DIR__ . '/../components/footer.php'; ?>
