<?php
require_once __DIR__ . "/../../../../app/controllers/EvacuationController.php";
$controller = new EvacuationController();
$id = $controller->recordId($_GET["id"] ?? null);
$center = $controller->center($id);
$errors = $controller->handleStay($id);
$records = $controller->records($id);
$households = $controller->households();
$householdId = 0;
if (isset($_GET["household_id"]) && $_GET["household_id"] !== "") {
    $householdId = $controller->recordId($_GET["household_id"]);
}
if (input("action") === "checkin_household") {
    $householdId = $controller->recordId(input("household_id"));
}
$members = $householdId ? $controller->householdMembers($householdId) : [];
$selectedMembers =
    input("action") === "checkin_household" ? $_POST["member_ids"] ?? [] : array_column($members, "id");
if (!is_array($selectedMembers)) {
    $selectedMembers = [];
}
$arrivingCount = 0;
foreach ($members as $member) {
    if (in_array($member["id"], $selectedMembers)) {
        $arrivingCount++;
    }
}
$center = $controller->center($id);
$walkins = $controller->walkins();
$pageTitle = $center["name"];
require __DIR__ . "/../../components/header.php";
require __DIR__ . "/../../components/errors.php";
$formKind = input('kind');
$formWalkinId = input('walkin_id');
$formFullName = input('full_name');
$formAddressDetails = input('address_details');
$formContactPhone = input('contact_phone');
$formAge = input('age');
?>
<?php
$savedPoint = $center;
require __DIR__ . "/../../components/saved-point.php";
?>
<h1><?= e($center['name']) ?></h1>
<p>Barangay: <?= e($center['barangay'] ?? 'Awaiting confirmation') ?></p>
<p><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/records.php?type=walkins')) ?>">Manage records</a></p>
<p><?= e($center['address_details']) ?> · <?= e($center['status']) ?> · Occupancy: <?= (int)$center['occupancy'] ?> / <?= (int)$center['capacity'] ?> people</p>
<p>Available capacity (people): <strong><?= (int)$center['available'] ?></strong></p>
<a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/evacuation/centers.php')) ?>">Back to evacuation centers</a> · <a href="<?= e(url('resources/views/admin/evacuation/centers.php?edit=' . $id)) ?>">Edit / close center</a>
<?php if ($center['status'] === 'open' && $center['archived_at'] === null && $center['occupancy'] < $center['capacity']): ?>
    <h2 class="mt-4">Check in a household</h2>
    <form method="get" class="mb-3">
        <input type="hidden" name="id" value="<?= $id ?>">
        <label for="household_id" class="form-label">Registered household</label>
        <select id="household_id" name="household_id" class="form-select mb-2" required>
            <option value="">Choose a household</option>
            <?php foreach ($households as $household): ?>
                <option value="<?= (int)$household['id'] ?>" <?= $householdId === (int)$household['id'] ? 'selected' : '' ?>> <?= e($household['household_code'] . ' — ' . $household['representative_name']) ?> (<?= (int)$household['member_count'] ?> members) </option>
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
                        <input class="form-check-input" type="checkbox" name="member_ids[]" value="<?= (int)$member['id'] ?>" <?= in_array($member['id'], $selectedMembers) ? 'checked' : '' ?>> <?= e($member['full_name']) ?> </label>
                <?php endforeach; ?>
            </fieldset>
            <?php if (!$members): ?>
                <p>No active members found. Update the household registry first.</p>
            <?php endif; ?>
            <p>Current occupancy: <?= (int)$center['occupancy'] ?> / <?= (int)$center['capacity'] ?> people.</p>
            <p data-arrival-summary aria-live="polite">Arriving: <?= $arrivingCount ?> people. Remaining capacity after check-in: <?= (int)$center['capacity'] - (int)$center['occupancy'] - $arrivingCount ?> people.</p>
            <button class="btn btn-primary" name="action" value="checkin_household" <?= !$members ? 'disabled' : '' ?>>Check in household</button>
            <a href="<?= e(url('resources/views/admin/evacuation/center.php?id=' . $id)) ?>">Cancel household selection</a>
        </form>
    <?php endif; ?>
    <h2 class="mt-4">Check in a walk-in</h2>
    <p>Search the household registry and existing walk-ins before creating a new walk-in. Confirm identity with the person or guardian. To transfer centers, check out the previous stay first.</p>
    <form method="post">
        <label class="d-block mb-3">Record type<select class="form-select" name="kind">
                <option value="walkin" <?= $formKind === 'walkin'?'selected':'' ?>>Existing walk-in</option>
                <option value="new_walkin" <?= $formKind === 'new_walkin'?'selected':'' ?>>New walk-in</option>
            </select></label>
        <label class="d-block mb-3">Existing walk-in<select class="form-select" name="walkin_id">
                <option value="">Select for returning walk-in</option>
                <?php foreach ($walkins as $person): ?>
                    <option value="<?= (int)$person['id'] ?>" <?= $formWalkinId === (string)$person['id']?'selected':'' ?>><?= e('#' . $person['id'] . ' — ' . $person['full_name'] . ' — ' . $person['address_details'] . ' — ' . $person['contact_phone']) ?></option>
                <?php endforeach; ?>
            </select></label>
        <fieldset class="border p-3 mb-3">
            <legend>New walk-in details only</legend>
            <label class="d-block mb-2">Full name
                <input class="form-control" name="full_name" maxlength="100" value="<?= e($formFullName) ?>" />
            </label>
            <label class="d-block mb-2">Home address / purok
                <input
                    class="form-control"
                    name="address_details"
                    maxlength="255"
                    value="<?= e($formAddressDetails) ?>"
                />
            </label>
            <label class="d-block mb-2">Contact number (personal or guardian)
                <input
                    class="form-control"
                    name="contact_phone"
                    maxlength="20"
                    value="<?= e($formContactPhone) ?>"
                />
            </label>
            <label>Age in years<input class="form-control" name="age" type="number" min="0" max="130" value="<?= e($formAge) ?>" /></label>
        </fieldset>
        <button class="btn btn-primary" name="action" value="checkin">Check in walk-in</button>
        <a href="<?= e(url('resources/views/admin/evacuation/centers.php')) ?>">Cancel and return to evacuation centers</a>
    </form>
<?php else: ?>
    <p class="alert alert-info mt-3">Check-in unavailable: this center is closed or full.</p>
<?php endif; ?>
<h2 class="mt-4">Current evacuees and evacuation history</h2>
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
            <?php foreach ($records as $record): ?>
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
                                <button class="btn btn-outline-secondary" name="action" value="checkout">Check out</button>
                            </form>
                        <?php else: ?>
                            <?php
                            $historyType = 'evacuation';
                            $historyId = $record['id'];
                            require __DIR__ . '/../../components/history-action.php';
                            ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$records): ?>
                <tr>
                    <td colspan="5">No evacuation records.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<script src="<?= e(url('resources/assets/js/household-checkin.js')) ?>" defer></script>
<?php
require __DIR__ . "/../../components/footer.php";
?>
