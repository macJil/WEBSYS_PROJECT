<?php
require_once __DIR__ . '/common.php';
$search = is_string($_GET['q'] ?? '') ? trim($_GET['q'] ?? '') : '';
$search = substr($search, 0, 255);
$term = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search) . '%';
$sql = "SELECT h.*, (SELECT COUNT(*) FROM household_members m WHERE m.household_id = h.id AND m.archived_at IS NULL) AS member_count
        FROM households h WHERE h.archived_at IS NULL AND (h.household_code LIKE ? ESCAPE '!'
        OR h.representative_name LIKE ? ESCAPE '!' OR h.purok LIKE ? ESCAPE '!'
        OR h.address_details LIKE ? ESCAPE '!' OR h.contact_phone LIKE ? ESCAPE '!')
        ORDER BY h.household_code";
$stmt = db()->prepare($sql);
$stmt->execute(array_fill(0, 5, $term));
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
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Representative</th>
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
                    <td colspan="5">No matching households.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<a class="btn btn-primary" href="<?= e(url('households/add.php')) ?>">Add household</a>
<?php require __DIR__ . '/../includes/footer.php'; ?>
