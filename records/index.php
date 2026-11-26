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

function recordListing(string $type, string $search, int $page, bool $archived): array
{
    $types = $type === 'all' ? RECORD_TYPES : [$type => recordSpec($type)];
    $queries = [];
    $values = [];
    $term = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
    foreach ($types as $key => $spec) {
        [$table, $label] = $spec;
        $state = $archived ? 'archived_at IS NOT NULL' : 'archived_at IS NULL';
        if ($key === 'teams') {
            $state = $archived ? 'is_active=0' : 'is_active=1';
        }
        if ($key === 'members' && !$archived) {
            $state .= ' AND EXISTS (SELECT 1 FROM households h WHERE h.id=household_members.household_id AND h.archived_at IS NULL)';
        }
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

function checkOperationalUse(string $type, array $row): void
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
}

function checkDeletionLinks(string $type, array $row): void
{
    $id = (int) $row['id'];
    $links = [
        'households' => [['household_members', 'household_id'], ['relief_distributions', 'household_id']],
        'members' => [['evacuation_records', 'household_member_id'], ['users', 'household_member_id']],
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
    $descriptions = [
        'household_members' => 'household member',
        'relief_distributions' => 'relief distribution',
        'evacuation_records' => 'evacuation stay',
        'users' => 'resident account',
        'incident_images' => 'incident image',
        'incident_assignments' => 'team assignment',
        'response_updates' => 'response update',
        'relief_stock_movements' => 'stock movement',
        'relief_distribution_items' => 'distribution line',
    ];
    $linked = [];
    foreach ($links[$type] ?? [] as [$table, $column]) {
        $matches = recordQuery("SELECT id FROM $table WHERE $column=? ORDER BY id LIMIT 2 FOR UPDATE", [$id]);
        if ($matches) {
            $linked[] = $descriptions[$table] . ' #' . implode(', #', array_column($matches, 'id'));
        }
    }
    if (
        $type === 'announcements' &&
        ($row['first_published_at'] !== null ||
            $row['is_published'] ||
            $row['publication_history_unknown'])
    ) {
        $linked[] = 'announcement publication history';
    }
    if ($linked) {
        throw new DomainException(RECORD_TYPES[$type][2] . ' #' . $id . ' has linked records: ' .
            implode('; ', $linked) . '. Permanent deletion is blocked. No selected records were changed.');
    }
}

function applyRecordAction(array $selection, string $action, bool $archived): void
{
    db()->beginTransaction();
    try {
        ksort($selection);
        foreach ($selection as $type => $ids) {
            [$table] = recordSpec($type);
            foreach (recordSelected($type, $ids, true) as $row) {
                $id = (int) $row['id'];
                $currentlyArchived = $type === 'teams' ? !$row['is_active'] : $row['archived_at'] !== null;
                if ($currentlyArchived !== $archived) {
                    throw new DomainException("Record #$id changed. Refresh the list. No selected records were changed.");
                }
                if ($action === 'delete') {
                    checkDeletionLinks($type, $row);
                }
                if ($action !== 'restore') {
                    checkOperationalUse($type, $row);
                }
                if ($type === 'members' && $action === 'restore' &&
                    recordLinked('households', 'id', (int) $row['household_id'], 'AND archived_at IS NOT NULL')) {
                    throw new DomainException("Restore the household before restoring member #$id. No selected records were changed.");
                }
                $sql = "DELETE FROM $table WHERE id=?";
                if ($action !== 'delete') {
                    $set = $action === 'archive' ? 'archived_at=CURRENT_TIMESTAMP' : 'archived_at=NULL';
                    if ($type === 'teams') {
                        $set = $action === 'archive' ? 'is_active=0' : 'is_active=1';
                    }
                    if ($action === 'archive' && $type === 'centers') {
                        $set .= ", status='closed'";
                    }
                    if ($action === 'archive' && $type === 'announcements') {
                        $set .= ', is_published=0';
                    }
                    $sql = "UPDATE $table SET $set WHERE id=?";
                }
                db()->prepare($sql)->execute([$id]);
            }
        }
        db()->commit();
    } catch (Throwable $error) {
        db()->rollBack();
        throw $error;
    }
}

function batchAction(array $rows, bool $archived): void
{
    requireRole('admin');
    $ids = $_POST['ids'] ?? [];
    $action = input('action');
    $allowed = $archived ? ['restore', 'delete'] : ['archive', 'delete'];
    if (!in_array($action, $allowed, true)) {
        throw new DomainException('Choose an action available for this list.');
    }
    if (!is_array($ids) || !$ids || count($ids) > 50) {
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
    applyRecordAction($selection, $action, $archived);
    flash(['archive' => 'Selected records archived.', 'restore' => 'Selected records restored.', 'delete' => 'Selected records permanently deleted.'][$action]);
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
$state = $_GET['state'] ?? 'active';
if (!in_array($state, ['active', 'archived'], true)) {
    http_response_code(400);
    exit('Unsupported record state.');
}
$archived = $state === 'archived';
if ($archived) {
    $params['state'] = $state;
}
$rows = recordListing($type, $q, $page, $archived);
$errors = [];
if (isPost()) {
    try {
        batchAction($rows, $archived);
        redirect("records/index.php?" . http_build_query($params));
    } catch (DomainException $error) {
        $errors[] = $error->getMessage();
    } catch (Throwable $error) {
        $errors[] = "Record action failed. No selected records were changed.";
    }
}
$pageTitle = "Manage records";
require __DIR__ . "/../includes/header.php";
require __DIR__ . "/../includes/errors.php";
?>
<p><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Manage records</h1>
<p>Archive to remove a record from active lists and keep its history. Only unused records can be permanently deleted.</p>
<details class="mb-3"><summary>How to restore a record</summary><p>Choose Archived, select the record, then Restore. Reopen a restored center or publish a restored notice when it is ready.</p></details>
<form method="get" class="filter-form mb-3">
    <label>Record type<select class="form-select" name="type">
            <option value="all">All record types</option>
            <?php foreach (RECORD_TYPES as $key => $spec): ?>
                <option value="<?= e($key) ?>" <?= $type === $key ? 'selected' : '' ?>><?= e($spec[2]) ?></option>
            <?php endforeach; ?>
        </select></label>
    <label>Records<select class="form-select" name="state">
        <option value="active" <?= !$archived ? 'selected' : '' ?>>Active</option>
        <option value="archived" <?= $archived ? 'selected' : '' ?>>Archived</option>
    </select></label>
    <label>Search ID/name <input class="form-control" placeholder="ID or name" maxlength="255" name="q" value="<?= e($q) ?>" /></label>
    <button class="btn btn-outline-secondary">Search</button> <a class="btn btn-outline-secondary" href="<?= e(url('records/index.php')) ?>">Clear</a>
</form>
<form method="post" id="record-selection">
    <div class="table-responsive" tabindex="0" aria-label="Records">
        <table class="table">
            <thead>
                <tr>
                    <th>
                        <?php if ($archived): ?>
                            <button class="btn btn-outline-primary" name="action" value="restore">Restore</button>
                        <?php else: ?>
                            <button class="btn btn-outline-secondary" name="action" value="archive">Archive</button>
                        <?php endif; ?>
                        <button class="btn btn-danger" name="action" value="delete">Permanently delete</button><br />
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
