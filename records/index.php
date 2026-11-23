<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/db.php';
requireRole('admin');
const RECORD_TYPES = [
        'households' => [
            'households',
            'household_code',
            'Households',
            'households/details.php?id=',
        ],
        'members' => ['household_members', 'full_name', 'Household members', ''],
        'incidents' => [
            'incident_reports',
            'location_description',
            'Incidents',
            'incidents/view.php?id=',
        ],
        'teams' => [
            'responders',
            'team_name',
            'Response teams',
            'response/teams.php?edit=',
        ],
        'centers' => [
            'evacuation_centers',
            'name',
            'Evacuation centers',
            'evacuation/center.php?id=',
        ],
        'walkins' => ['evacuation_walkins', 'full_name', 'Walk-ins', ''],
        'goods' => [
            'relief_items',
            'item_name',
            'Relief goods',
            'relief-inventory/index.php?edit=',
        ],
        'locations' => [
            'relief_locations',
            'name',
            'Relief locations',
            'relief-inventory/location.php?id=',
        ],
        'announcements' => [
            'announcements',
            'title',
            'Announcements',
            'announcements/edit.php?id=',
        ],
    ];
function recordQuery(string $sql, array $args = []): array
{
    $s = db()->prepare($sql);
    $s->execute($args);
    return $s->fetchAll();
}

function recordSpec(string $type): array
{
    if (!isset(RECORD_TYPES[$type])) {
        throw new DomainException('Unsupported record type.');
    }
    return RECORD_TYPES[$type];
}

function recordListing(string $type, string $search, int $page): array
{
    $types = $type === 'all' ? RECORD_TYPES : [$type => recordSpec($type)];
    $queries = [];
    $values = [];
    $term = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
    foreach ($types as $key => $spec) {
        [$table, $label] = $spec;
        $state = $key === 'teams' ? 'is_active=1' : 'archived_at IS NULL';
        $queries[] = "SELECT id, $label AS label, '$key' AS record_type FROM $table WHERE $state AND ($label LIKE ? ESCAPE '!' OR CAST(id AS CHAR) LIKE ? ESCAPE '!')";
        $values[] = $term;
        $values[] = $term;
    }
    $offset = ($page - 1) * 50;
    return recordQuery(
        implode(' UNION ALL ', $queries) . " ORDER BY record_type, id LIMIT 50 OFFSET $offset",
        $values,
    );
}

function recordSelected(string $type, array $ids, bool $lock = false): array
{
    [$table] = recordSpec($type);
    sort($ids, SORT_NUMERIC);
    $rows = recordQuery(
        "SELECT * FROM $table WHERE id IN (" .
            implode(',', array_fill(0, count($ids), '?')) .
            ') ORDER BY id' .
            ($lock ? ' FOR UPDATE' : ''),
        $ids,
    );
    if (count($rows) !== count($ids)) {
        throw new DomainException('A selected record no longer exists. Reload the list.');
    }
    return $rows;
}

function recordLinked(string $table, string $column, int $id, string $extra = ''): bool
{
    // All callers use literal identifiers and conditions defined below.
    return (bool) recordQuery("SELECT id FROM $table WHERE $column=? $extra LIMIT 1 FOR UPDATE", [$id]);
}

function recordRemovalMode(string $type, array $row): string
{
    $id = (int) $row['id'];
    $guide = '';
    if ($type === 'goods' && (int) $row['quantity_on_hand'] !== 0) {
        $guide =
            'Open Relief goods → Stock movements. Record the actual distribution or stock-out of remaining supplies, then retry when stock is zero.';
    }
    $stayColumn = [
        'members' => 'household_member_id',
        'walkins' => 'walkin_id',
        'centers' => 'center_id',
    ];
    if (
        isset($stayColumn[$type]) &&
        recordLinked('evacuation_records', $stayColumn[$type], $id, 'AND checked_out_at IS NULL')
    ) {
        $guide =
            'Open Evacuation centers → the occupied center. Check out the affected people when they have actually left, then retry.';
    }
    if ($type === 'households') {
        $members = recordQuery('SELECT id FROM household_members WHERE household_id=? FOR UPDATE', [
            $id,
        ]);
        foreach ($members as $member) {
            if (
                recordLinked(
                    'evacuation_records',
                    'household_member_id',
                    (int) $member['id'],
                    'AND checked_out_at IS NULL',
                )
            ) {
                $guide =
                    'Open Evacuation centers and check out this household’s active occupants when they have left, then retry.';
            }
        }
    }
    if (
        $type === 'incidents' &&
        (in_array($row['status'], ['verified', 'responding'], true) ||
            recordLinked(
                'incident_assignments',
                'incident_id',
                $id,
                "AND status IN ('assigned','on_scene')",
            ))
    ) {
        $guide =
            'Open the incident → Response management. Complete or cancel active assignments, then resolve or reject the incident before retrying.';
    }
    if (
        $type === 'teams' &&
        recordLinked(
            'incident_assignments',
            'responder_id',
            $id,
            "AND status IN ('assigned','on_scene')",
        )
    ) {
        $guide =
            'Open the assigned incidents → Response management. Complete or cancel this team’s active assignments, then retry.';
    }
    if ($guide !== '') {
        throw new DomainException(
            RECORD_TYPES[$type][2] . ' #' . $id . ': ' . $guide . ' No selected records were changed.',
        );
    }
    $links = [
        'households' => [['household_members', 'household_id'], ['relief_distributions', 'household_id']],
        'members' => [['evacuation_records', 'household_member_id']],
        'incidents' => [
            ['incident_images', 'incident_id'],
            ['incident_assignments', 'incident_id'],
            ['response_updates', 'incident_id'],
        ],
        'teams' => [['incident_assignments', 'responder_id']],
        'centers' => [['evacuation_records', 'center_id']],
        'walkins' => [['evacuation_records', 'walkin_id']],
        'goods' => [['relief_stock_movements', 'item_id'], ['relief_distribution_items', 'item_id']],
        'locations' => [['relief_distributions', 'relief_location_id']],
    ];
    foreach ($links[$type] ?? [] as [$table, $column]) {
        if (recordLinked($table, $column, $id)) {
            return 'hide';
        }
    }
    if (
        $type === 'announcements' &&
        ($row['first_published_at'] !== null ||
            $row['is_published'] ||
            $row['publication_history_unknown'])
    ) {
        return 'hide';
    }
    return 'delete';
}

function recordDeleteSelected(array $selection): void
{
    db()->beginTransaction();
    try {
        // Check the entire batch before deleting anything, including mixed types.
        $modes = [];
        ksort($selection);
        foreach ($selection as $type => $ids) {
            foreach (recordSelected($type, $ids, true) as $row) {
                $modes[$type][(int) $row['id']] = recordRemovalMode($type, $row);
            }
        }
        foreach ($selection as $type => $ids) {
            [$table] = recordSpec($type);
            foreach ($ids as $id) {
                $sql = "DELETE FROM $table WHERE id=?";
                if ($modes[$type][$id] === 'hide') {
                    $set = $type === 'teams' ? 'is_active=0' : 'archived_at=CURRENT_TIMESTAMP';
                    if ($type === 'centers') {
                        $set .= ",status='closed'";
                    }
                    if ($type === 'announcements') {
                        $set .= ',is_published=0';
                    }
                    $sql = "UPDATE $table SET $set WHERE id=?";
                    if ($type === 'households') {
                        $stmt = db()->prepare(
                            'UPDATE household_members SET archived_at=COALESCE(archived_at,CURRENT_TIMESTAMP) WHERE household_id=?',
                        );
                        $stmt->execute([$id]);
                    }
                }
                $stmt = db()->prepare($sql);
                $stmt->execute([$id]);
            }
        }
        db()->commit();
    } catch (Throwable $error) {
        db()->rollBack();
        throw $error;
    }
}

function batchDelete(array $rows): void
{
    requireRole('admin');
    $ids = $_POST['ids'] ?? [];
    if (input('action') !== 'delete' || !is_array($ids) || !$ids || count($ids) > 50) {
        throw new DomainException('Select 1–50 records on this page.');
    }
    $visible = [];
    foreach ($rows as $row) {
        $visible[$row['record_type'] . ':' . $row['id']] = $row;
    }
    $selection = [];
    foreach ($ids as $key) {
        if (!is_string($key) || !isset($visible[$key])) {
            throw new DomainException('Select records from the displayed page.');
        }
        $row = $visible[$key];
        $selection[$row['record_type']][(int) $row['id']] = (int) $row['id'];
    }
    foreach ($selection as $type => $records) {
        $selection[$type] = array_values($records);
    }
    recordDeleteSelected($selection);
    flash('Selected records removed. Linked history was preserved.');
}


$type = is_string($_GET["type"] ?? null) ? $_GET["type"] : "all";
if ($type !== "all" && !isset(RECORD_TYPES[$type])) {
    http_response_code(400);
    exit("Unsupported record type.");
}
$q = is_string($_GET["q"] ?? null) ? trim($_GET["q"]) : "";
$page = filter_var($_GET["page"] ?? 1, FILTER_VALIDATE_INT, [
    "options" => ["min_range" => 1, "max_range" => 1000000],
]);
if (!$page) {
    http_response_code(400);
    exit("Invalid page.");
}
$params = ["type" => $type, "q" => $q, "page" => $page];
$rows = recordListing($type, $q, $page);
$errors = [];
if (isPost()) {
    try {
        batchDelete($rows);
        redirect("records/index.php?" . http_build_query($params));
    } catch (DomainException $error) {
        $errors[] = $error->getMessage();
    } catch (Throwable $error) {
        $errors[] = "Deletion failed. No selected records were changed.";
    }
}
$pageTitle = "Manage records";
require __DIR__ . "/../includes/header.php";
require __DIR__ . "/../includes/errors.php";
?>
<p><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Manage records</h1>
<p>Filter or search, then select records to delete. Unused records are permanently deleted. Linked records are removed from active lists while their history is kept. Records still in use show the steps needed first.</p>
<form method="get" class="filter-form mb-3">
    <label>Record type<select name="type">
            <option value="all">All record types</option>
            <?php foreach (RECORD_TYPES as $key => $spec): ?>
                <option value="<?= e($key) ?>" <?= $type === $key ? 'selected' : '' ?>><?= e($spec[2]) ?></option>
            <?php endforeach; ?>
        </select></label>
    <label>Search ID/name <input name="q" value="<?= e($q) ?>" /></label>
    <button class="btn btn-outline-secondary">Search</button>
</form>
<form method="post" id="record-selection">
    <div class="table-responsive" tabindex="0" aria-label="Records">
        <table class="table">
            <thead>
                <tr>
                    <th><button class="btn btn-danger" name="action" value="delete">Delete selected</button><br />
                        <label><input type="checkbox" id="select-page" /> Select all on this page</label>
                        <span id="selected-count" role="status">0 selected</span>
                    </th>
                    <th>Record type</th>
                    <th>ID</th>
                    <th>Name / description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="<?= e($row['record_type'] . ':' . $row['id']) ?>" aria-label="<?= e('Select ' . RECORD_TYPES[$row['record_type']][2] . ' #' . $row['id']) ?>" /></td>
                        <td><?= e(RECORD_TYPES[$row['record_type']][2]) ?></td>
                        <td><?= (int)$row['id'] ?></td>
                        <td><?= e($row['label']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="4">No matching records.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</form>
<p>Page <?= $page ?> · Up to 50 records.
    <?php if ($page > 1): ?>
        <a href="?<?= e(http_build_query(array_replace($params, ['page' => $page - 1]))) ?>">Previous</a>
    <?php endif; ?>
    <?php if (count($rows) === 50): ?>
        <a href="?<?= e(http_build_query(array_replace($params, ['page' => $page + 1]))) ?>">Next</a>
    <?php endif; ?>
</p>
<script src="<?= e(url('assets/js/record-selection.js?v=delete-confirm')) ?>"></script>
<?php
require __DIR__ . "/../includes/footer.php";
?>
