<?php
require_once __DIR__ . '/household_function.php';
$search = is_string($_GET['q'] ?? '') ? trim($_GET['q'] ?? '') : '';
$search = substr($search, 0, 255);
$term = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search) . '%';
$page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000000]]);
if (!$page) {
    http_response_code(400);
    exit('Invalid page.');
}
$perPage = 50;
$offset = ($page - 1) * $perPage;
$filter = "FROM households h WHERE h.archived_at IS NULL AND (h.household_code LIKE ? ESCAPE '!'
        OR h.representative_name LIKE ? ESCAPE '!' OR h.purok LIKE ? ESCAPE '!'
        OR h.address_details LIKE ? ESCAPE '!' OR h.contact_phone LIKE ? ESCAPE '!')";
$params = array_fill(0, 5, $term);
$count = db()->prepare("SELECT COUNT(*) $filter");
$count->execute($params);
$total = (int)$count->fetchColumn();
$sql = "SELECT h.*, (SELECT COUNT(*) FROM household_members m WHERE m.household_id=h.id AND m.archived_at IS NULL) AS member_count
        $filter ORDER BY h.household_code, h.id LIMIT $perPage OFFSET $offset";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
$pageTitle = 'Household registry';
require __DIR__ . '/../includes/header.php';
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Household registry</h1>
<p><a class="btn btn-outline-secondary" href="<?= e(url('records/index.php?type=households')) ?>">Manage records</a></p>
<form method="get" class="filter-form d-flex gap-2 mb-3">
    <label for="search" class="visually-hidden">Search households</label>
    <input id="search" class="form-control" name="q" maxlength="255" value="<?= e($search) ?>" placeholder="Code, representative, purok, address or contact" />
    <button class="btn btn-outline-secondary">Search</button><a class="btn btn-outline-secondary" href="<?= e(url('households/index.php')) ?>">Clear</a>
</form>
<?php if ($page > 1 || $total > $perPage): ?>
    <nav aria-label="Household pages" class="mb-3">Page <?= $page ?> · <?= $total ?> households
        <?php if ($page > 1): ?><a href="?<?= e(http_build_query(['q' => $search, 'page' => $page - 1])) ?>">Previous</a><?php endif; ?>
        <?php if ($offset + $perPage < $total): ?><a href="?<?= e(http_build_query(['q' => $search, 'page' => $page + 1])) ?>">Next</a><?php endif; ?>
    </nav>
<?php endif; ?>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Representative</th>
                <th>Barangay</th>
                <th>Purok</th>
                <th>Members</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= e($row['household_code']) ?></td>
                    <td><?= e($row['representative_name']) ?></td>
                    <td><?= e($row['barangay'] ?? 'Unconfirmed') ?></td>
                    <td><?= e($row['purok']) ?>
                        <?php if (!hasMapPoint($row)): ?>
                            <br /><small>No map point</small>
                        <?php endif; ?>
                    </td>
                    <td><?= (int)$row['member_count'] ?></td>
                    <td><a href="<?= e(url('households/details.php?id=' . $row['id'])) ?>">View / members</a> · <a href="<?= e(url('households/edit.php?id=' . $row['id'])) ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?>
                <tr>
                    <td colspan="6">No matching households.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<a class="btn btn-primary" href="<?= e(url('households/add.php')) ?>">Add household</a>
<?php require __DIR__ . '/../includes/footer.php'; ?>
