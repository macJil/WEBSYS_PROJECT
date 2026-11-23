<?php
require_once __DIR__ . "/../../../app/controllers/HouseholdController.php";
$controller = new HouseholdController();
$id = $controller->recordId($_GET["id"] ?? null);
$household = $controller->getHousehold($id);
$memberId = isset($_GET["member"]) ? $controller->recordId($_GET["member"]) : null;
$form =
    $memberId === null
        ? ["full_name" => "", "age" => "", "notes" => ""]
        : $controller->member($memberId, $id);
if (isPost()) {
    $form = ["full_name" => input("full_name"), "age" => input("age"), "notes" => input("notes")];
}
$errors = isPost() && input('action') === 'select_representative'
    ? $controller->selectRepresentative($id)
    : $controller->saveMember($id, $memberId, $form);
$members = $controller->members($id);
$pageTitle = "Household details";
require __DIR__ . "/../components/header.php";
?>

<a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/households.php')) ?>">Back to households</a>
<h1><?= e($household['household_label']) ?></h1>
<p>Barangay: <?= e($household['barangay'] ?? 'Awaiting confirmation') ?></p>
<p><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/records.php?type=members')) ?>">Manage records</a></p>
<a class="d-inline-block mb-3" href="<?= e(url('resources/views/admin/households.php?edit=' . $id)) ?>">Edit household</a>
<div class="table-responsive">
    <table class="table">
        <tbody>
            <tr>
                <th scope="row">Emergency contact number</th>
                <td><?= e($household['contact_phone'] ?: 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Purok / sitio</th>
                <td><?= e($household['purok'] ?: 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Address and location description</th>
                <td><?= e($household['address_details'] ?: 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Hazard notes</th>
                <td><?= e($household['hazard_notes'] ?: 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Assessment notes / source</th>
                <td><?= e($household['vulnerability_notes'] ?: 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Assessment date</th>
                <td><?= e($household['vulnerability_assessed_at'] ?: 'Not recorded') ?></td>
            </tr>
            <tr>
                <th scope="row">Registry reason</th>
                <td><?= e(HouseholdController::REASONS[$household['registry_reason']]) ?></td>
            </tr>
            <tr>
                <th scope="row">Hazard concern</th>
                <td><?= e(HouseholdController::HAZARDS[$household['hazard_type']]) ?></td>
            </tr>
            <tr>
                <th scope="row">Assistance classification</th>
                <td><?= e(HouseholdController::VULNERABILITY[$household['vulnerability_category']]) ?></td>
            </tr>
            <tr>
                <th scope="row">Household advisory status</th>
                <td><?= e(HouseholdController::EVACUATION[$household['evacuation_status']]) ?></td>
            </tr>
            <tr>
                <th scope="row">Members recorded</th>
                <td><?= (int)$household['member_count'] ?></td>
            </tr>
        </tbody>
    </table>
</div>
<?php if ($members && !$household['archived_at']): ?>
    <form method="post" class="mb-3">
        <label>Household representative
            <select class="form-select" name="member_id" required>
                <option value="">Choose a member</option>
                <?php foreach ($members as $member): ?>
                    <option value="<?= (int)$member['id'] ?>" <?= $member['id'] == $household['representative_member_id'] ? 'selected' : '' ?>><?= e($member['full_name']) ?> (#<?= (int)$member['id'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="btn btn-primary" name="action" value="select_representative">Save representative</button>
    </form>
<?php endif; ?>
<h2>Household members</h2>
<p>Add each person, including the representative if they live in this household. Member records do not create login accounts.</p>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Age</th>
                <th>Notes</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($members as $member): ?>
                <tr>
                    <td><?= e($member['full_name']) ?></td>
                    <td><?= e($member['age'] ?? 'Not recorded') ?></td>
                    <td><?= e($member['notes']) ?></td>
                    <td><a href="<?= e(url('resources/views/admin/household.php?id=' . $id . '&member=' . $member['id'])) ?>">View / edit</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$members): ?>
                <tr>
                    <td colspan="4">No members recorded.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<h2><?= $memberId === null ? 'Add member' : 'Edit member' ?></h2>
<?php
require __DIR__ . "/../components/errors.php";
?>
<form method="post" action="<?= e(url('resources/views/admin/household.php?id=' . $id . ($memberId === null ? '' : '&member=' . $memberId))) ?>">
    <input type="hidden" name="action" value="<?= $memberId === null ? 'create_member' : 'update_member' ?>" />
    <label for="full_name" class="form-label">Full name *</label>
    <input class="form-control mb-3" id="full_name" name="full_name" maxlength="100" required value="<?= e($form['full_name']) ?>" />
    <label for="age" class="form-label">Age (optional)</label>
    <input class="form-control mb-3" id="age" name="age" type="number" min="0" max="130" value="<?= e($form['age']) ?>" />
    <label for="notes" class="form-label">Notes</label>
    <textarea class="form-control mb-3" id="notes" name="notes" maxlength="255">
<?= e($form['notes']) ?></textarea> <button class="btn btn-primary">Save member</button>
    <a href="<?= e(url('resources/views/admin/household.php?id=' . $id)) ?>">Cancel and return to household</a>
</form>
<?php
require __DIR__ . "/../components/footer.php";
?>
