<?php
require_once __DIR__ . '/../../../app/controllers/RecordActionsController.php';
$controller = new RecordActionsController();
$type = is_string($_GET['type'] ?? null) ? $_GET['type'] : 'all';
if ($type !== 'all' && !isset(RecordActionsModel::TYPES[$type])) {
    http_response_code(400);
    exit('Unsupported record type.');
}
$q = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
$page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000000]]);
if (!$page) {
    http_response_code(400);
    exit('Invalid page.');
}
$params = ['type' => $type, 'q' => $q, 'page' => $page];
$rows = $controller->listing($type, $q, $page);
$errors = [];
if (isPost()) {
    try {
        $controller->delete($rows);
        redirect('resources/views/admin/records.php?' . http_build_query($params));
    } catch (DomainException $error) {
        $errors[] = $error->getMessage();
    } catch (Throwable $error) {
        $errors[] = 'Deletion failed. No selected records were changed.';
    }
}
$pageTitle = 'Manage records';
require __DIR__ . '/../components/header.php';
require __DIR__ . '/../components/errors.php';
?>
<p><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Manage records</h1>
<p>Filter or search, then select records to delete. Linked records and goods with stock remain protected.</p>
<form method="get" class="filter-form mb-3">
    <label>Record type<select name="type">
        <option value="all">All record types</option>
        <?php foreach (RecordActionsModel::TYPES as $key => $spec): ?>
        <option value="<?= e($key) ?>" <?= $type === $key ? 'selected' : '' ?>><?= e($spec[2]) ?></option>
        <?php endforeach; ?>
    </select></label>
    <label>Search ID/name <input name="q" value="<?= e($q) ?>" /></label>
    <button class="btn btn-outline-secondary">Search</button>
</form>
<form method="post" id="record-selection">
    <div class="table-responsive" tabindex="0" aria-label="Records">
        <table class="table">
            <thead><tr>
                <th><button class="btn btn-danger" name="action" value="delete">Delete selected</button><br />
                    <label><input type="checkbox" id="select-page" /> Select all on this page</label>
                    <span id="selected-count" role="status">0 selected</span></th>
                <th>Record type</th><th>ID</th><th>Name / description</th>
            </tr></thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?= e($row['record_type'] . ':' . $row['id']) ?>" aria-label="<?= e('Select ' . RecordActionsModel::TYPES[$row['record_type']][2] . ' #' . $row['id']) ?>" /></td>
                    <td><?= e(RecordActionsModel::TYPES[$row['record_type']][2]) ?></td>
                    <td><?= (int)$row['id'] ?></td><td><?= e($row['label']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?><tr><td colspan="4">No matching records.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</form>
<p>Page <?= $page ?> · Up to 50 records.
<?php if ($page > 1): ?><a href="?<?= e(http_build_query(array_replace($params, ['page' => $page - 1]))) ?>">Previous</a><?php endif; ?>
<?php if (count($rows) === 50): ?><a href="?<?= e(http_build_query(array_replace($params, ['page' => $page + 1]))) ?>">Next</a><?php endif; ?></p>
<script src="<?= e(url('resources/assets/js/record-selection.js?v=delete-confirm')) ?>"></script>
<?php require __DIR__ . '/../components/footer.php'; ?>
