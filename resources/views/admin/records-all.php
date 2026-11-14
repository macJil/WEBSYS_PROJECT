<?php
require_once __DIR__ . '/../../../app/controllers/RecordActionsController.php';
requireRole('admin');
$c = new RecordActionsController();
if (isPost()) {
    http_response_code(400);
    exit('Choose a record type before selecting records to delete.');
}
$q = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
$pageTitle = 'Manage all records';
require __DIR__ . '/../components/header.php';
?>
<p class="return-link"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Manage all records</h1>
<p>Review all record types below. Choose a type to select records for permanent deletion. Linked records remain protected.</p>
<form method="get" action="<?= e(url('resources/views/admin/records.php')) ?>" class="filter-form">
    <label>Records<select name="type">
        <option value="all">All record types</option>
        <?php foreach (RecordActionsModel::TYPES as $key => $spec): ?>
        <option value="<?= e($key) ?>"><?= e($spec[2]) ?></option>
        <?php endforeach; ?>
    </select></label>
    <label>Search ID/name <input name="q" value="<?= e($q) ?>" /></label>
    <button class="btn btn-outline-secondary">Search</button>
</form>
<?php foreach (RecordActionsModel::TYPES as $key => $spec): ?>
<section class="mb-4">
    <h2><?= e($spec[2]) ?></h2>
    <a href="<?= e(url('resources/views/admin/records.php?' . http_build_query(['type' => $key, 'q' => $q]))) ?>">Manage <?= e($spec[2]) ?></a>
    <?php $records = $c->listing($key, $q, false, 1); ?>
    <p>Showing up to 50 matching records. Open this type for selection and more pages.</p>
    <div class="table-responsive" tabindex="0" aria-label="<?= e($spec[2]) ?>">
        <table class="table"><thead><tr><th>ID</th><th>Name / description</th></tr></thead><tbody>
            <?php foreach ($records as $record): ?>
            <tr><td><?= (int)$record['id'] ?></td><td><?= e($record[$spec[1]]) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$records): ?><tr><td colspan="2">No matching records.</td></tr><?php endif; ?>
        </tbody></table>
    </div>
</section>
<?php endforeach; ?>
<?php require __DIR__ . '/../components/footer.php'; ?>
