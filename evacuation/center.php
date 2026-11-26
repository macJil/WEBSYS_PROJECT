<?php
require_once __DIR__ . '/common.php';
requireRole('admin');

function evacuationDbHouseholds(string $search): array
{
    $term = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
    return evacuationDbRows("SELECT h.id, h.household_code, h.barangay, h.purok, h.representative_name,
        COUNT(m.id) AS member_count
        FROM households h
        LEFT JOIN household_members m ON m.household_id = h.id AND m.archived_at IS NULL
        WHERE h.archived_at IS NULL AND (h.household_code LIKE ? ESCAPE '!' OR h.representative_name LIKE ? ESCAPE '!' OR h.barangay LIKE ? ESCAPE '!')
        GROUP BY h.id, h.household_code, h.barangay, h.purok, h.representative_name
        ORDER BY h.representative_name, h.id LIMIT 50", [$term, $term, $term]);
}


function evacuationDbCheckinHousehold(int $centerId, int $householdId, array $memberIds, int $staff): void
{
    if (!$memberIds) {
        throw new DomainException('Select at least one arriving member.');
    }
    foreach ($memberIds as $memberId) {
        if (!is_scalar($memberId) || !filter_var($memberId, FILTER_VALIDATE_INT) || $memberId < 1) {
            throw new DomainException('Invalid selected member.');
        }
    }
    $memberIds = array_map('intval', $memberIds);
    if (count($memberIds) !== count(array_unique($memberIds))) {
        throw new DomainException('A member was selected more than once.');
    }
    sort($memberIds);
    db()->beginTransaction();
    try {
        $center = evacuationDbLockCenter($centerId);
        if ($center['status'] !== 'open' || $center['archived_at'] !== null) {
            throw new DomainException('This center is closed or archived.');
        }
        $household = evacuationDbRows(
            'SELECT id FROM households
            WHERE id = ? AND archived_at IS NULL FOR UPDATE',
            [$householdId],
        );
        if (!$household) {
            throw new DomainException('Active household not found.');
        }
        $occupancy = evacuationDbOccupancy($centerId);
        if ($occupancy + count($memberIds) > (int) $center['capacity']) {
            throw new DomainException(
                'Not enough capacity for all selected members. Nobody was checked in.',
            );
        }
        foreach ($memberIds as $memberId) {
            $member = evacuationDbRows(
                'SELECT id, full_name FROM household_members
                WHERE id = ? AND household_id = ? AND archived_at IS NULL FOR UPDATE',
                [$memberId, $householdId],
            );
            if (!$member) {
                throw new DomainException(
                    'A selected member is no longer active in this household. Nobody was checked in.',
                );
            }
            $active = evacuationDbRows(
                'SELECT id FROM evacuation_records
                WHERE household_member_id = ? AND checked_out_at IS NULL FOR UPDATE',
                [$memberId],
            );
            if ($active) {
                throw new DomainException(
                    $member[0]['full_name'] . ' already has an active stay. Nobody was checked in.',
                );
            }
        }
        $stmt = db()->prepare('INSERT INTO evacuation_records
            (center_id, household_member_id, recorded_by) VALUES (?, ?, ?)');
        foreach ($memberIds as $memberId) {
            $stmt->execute([$centerId, $memberId, $staff]);
        }
        db()->commit();
    } catch (Throwable $e) {
        db()->rollBack();
        throw $e;
    }
}


function evacuationDbRecords(int $id): array
{
    return evacuationDbRows(
        'SELECT e.*, COALESCE(m.full_name,e.walkin_name) AS person_name, h.household_code, w.age AS walkin_age,w.address_details AS walkin_address,u.full_name AS encoder, o.full_name AS checkout_staff FROM evacuation_records e LEFT JOIN household_members m ON m.id=e.household_member_id LEFT JOIN households h ON h.id=m.household_id LEFT JOIN evacuation_walkins w ON w.id=e.walkin_id JOIN users u ON u.id=e.recorded_by LEFT JOIN users o ON o.id=e.checked_out_by WHERE e.center_id=? ORDER BY (e.checked_out_at IS NULL) DESC,e.id DESC',
        [$id],
    );
}

function evacuationDbCheckin(int $centerId, string $kind, int $personId, array $data, int $staff): void
{
    db()->beginTransaction();
    try {
        $center = evacuationDbLockCenter($centerId);
        if ($center['status'] !== 'open' || $center['archived_at'] !== null) {
            throw new DomainException('This center is closed.');
        }
        if (evacuationDbOccupancy($centerId) >= (int) $center['capacity']) {
            throw new DomainException('This center is full.');
        }
        $memberId = null;
        $walkinId = null;
        $name = null;
        $phone = null;
        if ($kind === 'member') {
            $member = evacuationDbRows(
                'SELECT m.id FROM household_members m JOIN households h ON h.id=m.household_id WHERE m.id=? AND m.archived_at IS NULL AND h.archived_at IS NULL FOR UPDATE',
                [$personId],
            );
            if (!$member) {
                throw new DomainException('Registered member not found.');
            }
            $memberId = $personId;
        } else {
            if ($kind === 'new_walkin') {
                $normalized = array_map(
                    fn($value) => strtolower(preg_replace('/\s+/u', ' ', trim((string) $value))),
                    [
                        $data['full_name'],
                        $data['age'],
                        $data['address_details'],
                        preg_replace('/[^0-9]/', '', $data['contact_phone']),
                    ],
                );
                $key = hash(
                    'sha256',
                    json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                );
                $stmt = db()->prepare(
                    'INSERT INTO evacuation_walkins (full_name,age,address_details,contact_phone,identity_key) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)',
                );
                $stmt->execute([
                    $data['full_name'],
                    $data['age'],
                    $data['address_details'],
                    $data['contact_phone'],
                    $key,
                ]);
                $personId = (int) db()->lastInsertId();
            }
            $people = evacuationDbRows(
                'SELECT * FROM evacuation_walkins WHERE id=? AND archived_at IS NULL FOR UPDATE',
                [$personId],
            );
            if (!$people) {
                throw new DomainException('Walk-in not found.');
            }
            $walkinId = $personId;
            $name = $people[0]['full_name'];
            $phone = $people[0]['contact_phone'];
            // Legacy walk-ins have no reusable ID; block matching active names/contacts for staff reconciliation.
            $legacy = evacuationDbRows(
                'SELECT id FROM evacuation_records WHERE household_member_id IS NULL AND walkin_id IS NULL AND checked_out_at IS NULL AND walkin_name=? AND walkin_contact=? FOR UPDATE',
                [$name, $phone],
            );
            if ($legacy) {
                throw new DomainException(
                    'A matching legacy walk-in is already checked in. Review the existing stay first.',
                );
            }
        }
        $stmt = db()->prepare(
            'INSERT INTO evacuation_records (center_id,household_member_id,walkin_id,walkin_name,walkin_contact,recorded_by) VALUES (?,?,?,?,?,?)',
        );
        $stmt->execute([$centerId, $memberId, $walkinId, $name, $phone, $staff]);
        db()->commit();
    } catch (Throwable $e) {
        db()->rollBack();
        throw $e;
    }
}

function evacuationDbCheckout(int $centerId, int $recordId, int $staff): void
{
    db()->beginTransaction();
    try {
        evacuationDbLockCenter($centerId);
        $stmt = db()->prepare(
            'UPDATE evacuation_records SET checked_out_at=CURRENT_TIMESTAMP,checked_out_by=? WHERE id=? AND center_id=? AND checked_out_at IS NULL',
        );
        $stmt->execute([$staff, $recordId, $centerId]);
        if ($stmt->rowCount() !== 1) {
            throw new DomainException('Stay not found in this center or already checked out.');
        }
        db()->commit();
    } catch (Throwable $e) {
        db()->rollBack();
        throw $e;
    }
}


function evacuationDbHouseholdMembers(int $householdId): array
{
    return evacuationDbRows(
        'SELECT m.id, m.full_name, stay.id AS active_stay FROM household_members m
        LEFT JOIN evacuation_records stay ON stay.active_member_id=m.id
        JOIN households h ON h.id = m.household_id
        WHERE h.id = ? AND h.archived_at IS NULL AND m.archived_at IS NULL
        ORDER BY m.id',
        [$householdId],
    );
}

function evacuationDbWalkins(string $search): array
{
    if ($search === '') {
        return [];
    }
    $term = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
    return evacuationDbRows(
        "SELECT id, full_name, address_details, contact_phone FROM evacuation_walkins
         WHERE archived_at IS NULL AND (full_name LIKE ? ESCAPE '!' OR contact_phone LIKE ? ESCAPE '!')
         ORDER BY full_name,id LIMIT 50",
        [$term, $term],
    );
}


function evacuationHandleStay(int $id): array
{
    if (!isPost()) {
        return [];
    }
    try {
        if (input('action') === 'checkin_household') {
            $memberIds = $_POST['member_ids'] ?? [];
            if (!is_array($memberIds)) {
                return ['Choose arriving members.'];
            }
            evacuationDbCheckinHousehold(
                $id,
                evacuationRecordId(input('household_id')),
                $memberIds,
                (int) $_SESSION['ID'],
            );
        } elseif (input('action') === 'checkout') {
            evacuationDbCheckout($id, evacuationRecordId(input('record_id')), (int) $_SESSION['ID']);
        } elseif (input('action') === 'checkin') {
            $kind = input('kind');
            $data = [];
            $personId = 0;
            if (!in_array($kind, ['walkin', 'new_walkin'], true)) {
                return ['Choose a check-in type.'];
            }
            if ($kind === 'new_walkin') {
                $data = [
                    'full_name' => input('full_name'),
                    'age' => filter_var(input('age'), FILTER_VALIDATE_INT, [
                        'options' => ['min_range' => 0, 'max_range' => 130],
                    ]),
                    'address_details' => input('address_details'),
                    'contact_phone' => input('contact_phone'),
                ];
                if (
                    $data['full_name'] === '' ||
                    mb_strlen($data['full_name']) > 100 ||
                    $data['age'] === false ||
                    $data['address_details'] === '' ||
                    mb_strlen($data['address_details']) > 255 ||
                    !validPhone($data['contact_phone'])
                ) {
                    return [
                        'For a new walk-in, enter name (up to 100 characters), age 0–130, home address (up to 255 characters), and a phone number starting with 09 or +639 followed by 9 digits.',
                    ];
                }
            } else {
                $personId = evacuationRecordId(input('walkin_id'));
            }
            evacuationDbCheckin($id, $kind, $personId, $data, (int) $_SESSION['ID']);
        } else {
            return ['Unknown form action.'];
        }
    } catch (Throwable $e) {
        return evacuationFailure($e);
    }
    flash('Evacuation record saved.');
    redirect('evacuation/center.php?id=' . $id);
    return [];
}

$id = evacuationRecordId($_GET["id"] ?? null);
$center = evacuationCenter($id);
$errors = evacuationHandleStay($id);
$records = evacuationDbRecords($id);
$householdSearch = is_string($_GET['household_q'] ?? null) ? trim($_GET['household_q']) : '';
$households = evacuationDbHouseholds($householdSearch);
$householdId = 0;
if (isset($_GET["household_id"]) && $_GET["household_id"] !== "") {
    $householdId = evacuationRecordId($_GET["household_id"]);
}
if (input("action") === "checkin_household") {
    $householdId = evacuationRecordId(input("household_id"));
}
$members = $householdId ? evacuationDbHouseholdMembers($householdId) : [];
$selectedMembers =
    input("action") === "checkin_household" ? $_POST["member_ids"] ?? [] : array_column($members, "id");
if (!is_array($selectedMembers)) {
    $selectedMembers = [];
}
$arrivingCount = 0;
$eligibleCount = 0;
foreach ($members as $member) {
    if ($member["active_stay"] !== null) {
        continue;
    }
    $eligibleCount++;
    if (in_array($member["id"], $selectedMembers)) {
        $arrivingCount++;
    }
}
$center = evacuationCenter($id);
$search = is_string($_GET['walkin_search'] ?? null) ? trim($_GET['walkin_search']) : '';
$walkins = evacuationDbWalkins($search);
$pageTitle = $center["name"];
require __DIR__ . "/../includes/header.php";
require __DIR__ . "/../includes/errors.php";
?>
<h1><?= e($center['name']) ?></h1>
<p><a class="btn btn-outline-secondary" href="<?= e(url('records/index.php?type=walkins')) ?>">Manage walk-in records</a></p>
<p><?= e($center['address_details']) ?> · <?= e($center['status']) ?> · Occupancy: <?= (int)$center['occupancy'] ?> / <?= (int)$center['capacity'] ?> people</p>
<?= capacityBar((int)$center['occupancy'], (int)$center['capacity']) ?>
<p>Available capacity (people): <strong><?= (int)$center['available'] ?></strong></p>
<a class="btn btn-outline-secondary" href="<?= e(url('evacuation/centers.php')) ?>">Back to evacuation centers</a> · <a href="<?= e(url('evacuation/centers.php?edit=' . $id)) ?>">Edit / close center</a>
<?php if ($center['status'] === 'open' && $center['archived_at'] === null && $center['occupancy'] < $center['capacity']): ?>
    <nav class="my-3 d-flex flex-wrap gap-2" aria-label="Evacuation tasks">
        <a class="btn btn-outline-secondary" href="#household">Registered household</a>
        <a class="btn btn-outline-secondary" href="#walkin">Walk-in arrival</a>
        <a class="btn btn-outline-secondary" href="#stays">Find a stay or check out</a>
    </nav>
    <h2 id="household">Check in a household</h2>
    <form method="get" action="#household" class="filter-form">
        <input type="hidden" name="id" value="<?= $id ?>">
        <label>Find household<input class="form-control" name="household_q" maxlength="150" placeholder="Representative, code or barangay" value="<?= e($householdSearch) ?>"></label>
        <button class="btn btn-outline-secondary">Find</button>
        <?php if ($householdSearch !== ''): ?><a href="?id=<?= $id ?>#household">Clear search</a><?php endif; ?>
    </form>
    <?php if ($householdSearch !== '' && !$households): ?>
        <p>No matching household. <a href="<?= e(url('households/add.php')) ?>">Register a household</a> or <a href="#walkin">check in a walk-in</a>.</p>
    <?php endif; ?>
    <form method="get" action="#household" class="mb-3">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="household_q" value="<?= e($householdSearch) ?>">
        <label for="household_id" class="form-label">Registered household (first 50 matches)</label>
        <select id="household_id" name="household_id" class="form-select mb-2" required>
            <option value="">Choose a household</option>
            <?php foreach ($households as $household): ?>
                <option value="<?= (int)$household['id'] ?>" <?= $householdId === (int)$household['id'] ? 'selected' : '' ?>> <?= e($household['representative_name'] . ' — ' . ($household['barangay'] ?? 'Unconfirmed barangay') . ' — ' . $household['purok']) ?> (<?= (int)$household['member_count'] ?> members) </option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-outline-secondary">Show members</button>
    </form>
    <?php if ($householdId): ?>
        <form method="post" data-household-checkin data-occupancy="<?= (int)$center['occupancy'] ?>" data-capacity="<?= (int)$center['capacity'] ?>">
            <input type="hidden" name="household_id" value="<?= $householdId ?>">
            <fieldset>
                <legend>Arriving members (<?= count($members) ?> registered)</legend>
                <p>Uncheck anyone who is not arriving. Each selected person uses one place.</p>
                <?php foreach ($members as $member): ?>
                    <label class="d-block mb-2">
                        <input class="form-check-input" type="checkbox" name="member_ids[]" value="<?= (int)$member['id'] ?>" <?= $member['active_stay'] !== null ? 'disabled' : (in_array($member['id'], $selectedMembers) ? 'checked' : '') ?>>
                        <?= e($member['full_name']) ?>
                        <?php if ($member['active_stay'] !== null): ?>(already checked in)<?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </fieldset>
            <?php if (!$members): ?>
                <p>No active members found. Update the household registry first.</p>
            <?php endif; ?>
            <p>Current occupancy: <?= (int)$center['occupancy'] ?> / <?= (int)$center['capacity'] ?> people.</p>
            <p data-arrival-summary aria-live="polite">Arriving: <?= $arrivingCount ?> people. Remaining capacity after check-in: <?= (int)$center['capacity'] - (int)$center['occupancy'] - $arrivingCount ?> people.</p>
            <button class="btn btn-primary" name="action" value="checkin_household" <?= !$eligibleCount ? 'disabled' : '' ?>>Check in household</button>
            <a href="<?= e(url('evacuation/center.php?id=' . $id)) ?>">Cancel household selection</a>
        </form>
    <?php endif; ?>
    <h2 id="walkin">Check in walk-in</h2>
    <form method="get" action="#walkin" class="mb-3">
        <input type="hidden" name="id" value="<?= $id ?>">
        <label class="form-label">Search returning walk-ins by name or phone
            <input class="form-control" name="walkin_search" placeholder="Enter a name or phone number" maxlength="100" required value="<?= e($search) ?>">
        </label>
        <button class="btn btn-outline-secondary">Search walk-ins</button>
    </form>
    <?php if ($search !== ''): ?>
        <?php if ($walkins): ?>
            <form method="post" class="mb-3">
                <input type="hidden" name="kind" value="walkin">
                <label class="d-block">Matching walk-ins (up to 50). Confirm the person and home address.
                    <select class="form-select" name="walkin_id" required>
                        <option value="">Choose returning person</option>
                        <?php foreach ($walkins as $person): ?>
                            <option value="<?= (int)$person['id'] ?>"><?= e($person['full_name'] . ' · ' . $person['address_details'] . ' · ' . $person['contact_phone']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="btn btn-primary" name="action" value="checkin">Check in returning walk-in</button>
            </form>
        <?php else: ?>
            <p>No matching walk-ins found. Try another spelling or register a new person below.</p>
        <?php endif; ?>
    <?php endif; ?>
    <details <?= input('kind') === 'new_walkin' ? 'open' : '' ?>>
        <summary>Register a new walk-in</summary>
        <form method="post">
            <input type="hidden" name="kind" value="new_walkin">
            <fieldset class="border p-3 mb-3">
                <legend>New walk-in details only</legend>
                <?php foreach (['full_name' => ['Full name',100],'address_details' => ['Home address / purok',255],'contact_phone' => ['Contact number (personal or guardian)',13]] as $key => $field): ?>
                    <label class="d-block mb-2"><?= e($field[0]) ?><input class="form-control" name="<?= e($key) ?>" maxlength="<?= $field[1] ?>" value="<?= e(input($key)) ?>" /></label>
                <?php endforeach; ?>
                <label>Age in years<input class="form-control" name="age" type="number" min="0" max="130" value="<?= e(input('age')) ?>" /></label>
            </fieldset>
            <button class="btn btn-primary" name="action" value="checkin">Check in walk-in</button>
            <a href="<?= e(url('evacuation/centers.php')) ?>">Cancel and return to evacuation centers</a>
        </form>
    </details>
<?php else: ?>
    <p class="alert alert-info mt-3">Check-in unavailable: this center is closed or full.</p>
    <?php
    $others = evacuationDbRows("SELECT c.id, c.name, c.capacity - COUNT(r.id) AS remaining
        FROM evacuation_centers c LEFT JOIN evacuation_records r ON r.center_id=c.id AND r.checked_out_at IS NULL
        WHERE c.status='open' AND c.archived_at IS NULL AND c.id<>?
        GROUP BY c.id, c.name, c.capacity HAVING remaining > 0 ORDER BY c.name", [$id]);
    ?>
    <?php if ($others): ?>
        <h3>Other open centers with space</h3>
        <ul>
            <?php foreach ($others as $other): ?>
                <li><a href="<?= e(url('evacuation/center.php?id=' . $other['id'])) ?>"><?= e($other['name']) ?></a> · <?= (int)$other['remaining'] ?> places left</li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>
<div id="stays"></div>
<?php foreach ([true, false] as $current): ?>
<h2 class="mt-4"><?= $current ? 'Currently staying' : 'Completed stays' ?> (<?= $current ? (int)$center['occupancy'] : count(array_filter($records, fn($stay) => $stay['checked_out_at'] !== null)) ?>)</h2>
<?php $hasRows = false; ?>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Person</th>
                <th>Record / contact</th>
                <th>Checked in</th>
                <th>Checked out</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record):
                if (($record['checked_out_at'] === null) !== $current) {
                    continue;
                }
                $hasRows = true;
            ?>
                <tr>
                    <td><?= e($record['person_name']) ?>
                        <?php if ($record['walkin_age'] !== null): ?>
                            <br />Age: <?= (int)$record['walkin_age'] ?><br /><?= e($record['walkin_address']) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= e($record['household_code'] ?? ('Walk-in #' . ($record['walkin_id'] ?? 'legacy'))) ?><br /><?= e($record['walkin_contact']) ?></td>
                    <td><?= e($record['checked_in_at']) ?><br />By <?= e($record['encoder']) ?></td>
                    <td><?= e($record['checked_out_at'] ?? 'Currently staying') ?>
                        <?php if ($record['checkout_staff']): ?>
                            <br />By <?= e($record['checkout_staff']) ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($record['checked_out_at'] === null): ?>
                            <form method="post">
                                <input type="hidden" name="record_id" value="<?= (int)$record['id'] ?>" />
                                <button class="btn btn-primary" name="action" value="checkout">Check out</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$hasRows): ?>
                <tr>
                    <td colspan="5"><?= $current ? 'Nobody is currently staying here.' : 'No completed stays yet.' ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endforeach; ?>
<h2>Center location</h2>
<?php
$savedPoint = $center;
$savedKind = 'centers';
require __DIR__ . "/../includes/saved-point.php";
?>

<script src="<?= e(url('assets/js/household-checkin.js')) ?>" defer></script>
<?php
require __DIR__ . "/../includes/footer.php";
?>
