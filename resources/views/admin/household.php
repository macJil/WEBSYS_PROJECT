<?php
require_once __DIR__ . '/../../../app/controllers/HouseholdController.php';
$controller = new HouseholdController();
$id = $controller->recordId($_GET['id'] ?? null);
$household = $controller->getHousehold($id);
$memberId = isset($_GET['member']) ? $controller->recordId($_GET['member']) : null;
$form = $memberId === null ? ['full_name' => '','age' => '','notes' => ''] : $controller->member($memberId, $id);
if (isPost()) {
    $form = ['full_name' => input('full_name'),'age' => input('age'),'notes' => input('notes')];
}
$errors = $controller->saveMember($id, $memberId, $form);
$members = $controller->members($id);
$pageTitle = 'Household details';
require __DIR__ . '/../components/header.php';
?> <?php $savedPoint = $household;
require __DIR__ . '/../components/saved-point.php'; ?>
<a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/households.php')) ?>">Back to households</a>
<h1><?= e($household['household_code']) ?> — <?= e($household['representative_name']) ?></h1>
<p><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/records.php?type=members')) ?>">Manage records</a></p>
<a class="d-inline-block mb-3" href="<?= e(url('resources/views/admin/households.php?edit=' . $id)) ?>">Edit household</a>
<dl class="row">
    <?php foreach (['contact_phone' => 'Emergency contact number','purok' => 'Purok / sitio','address_details' => 'Address and location description','hazard_notes' => 'Hazard notes','vulnerability_notes' => 'Assessment notes / source','vulnerability_assessed_at' => 'Assessment date'] as $field => $label): ?><dt class="col-sm-4"><?= e($label) ?></dt
    ><dd class="col-sm-8"><?= e($household[$field] ?: 'Not recorded') ?></dd><?php endforeach; ?> <?php foreach (['registry_reason' => ['Registry reason',HouseholdController::REASONS],'hazard_type' => ['Hazard concern',HouseholdController::HAZARDS],'vulnerability_category' => ['Assistance classification',HouseholdController::VULNERABILITY],'evacuation_status' => ['Household advisory status',HouseholdController::EVACUATION]] as $field => $info): ?><dt
        class="col-sm-4"
        ><?= e($info[0]) ?></dt
    ><dd class="col-sm-8"><?= e($info[1][$household[$field]]) ?></dd><?php endforeach; ?>
    <dt class="col-sm-4">Members recorded</dt><dd class="col-sm-8"><?= (int)$household['member_count'] ?></dd></dl
>
<h2>Household members</h2
><p
    >Add each person, including the representative if they live in this household. Member records do
    not create login accounts.</p
>
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
            <?php foreach ($members as $member): ?><tr>
                <td><?= e($member['full_name']) ?></td>
                <td><?= e($member['age'] ?? 'Not recorded') ?></td>
                <td><?= e($member['notes']) ?></td>
                <td><a href="<?= e(url('resources/views/admin/household.php?id=' . $id . '&member=' . $member['id'])) ?>">View / edit</a></td> </tr
            ><?php endforeach; ?> <?php if (!$members): ?><tr>
                <td colspan="4">No members recorded.</td> </tr
            ><?php endif; ?></tbody
        >
    </table>
</div>
<h2><?= $memberId === null ? 'Add member' : 'Edit member' ?></h2>
<?php require __DIR__ . '/../components/errors.php'; ?>
<form method="post" action="<?= e(url('resources/views/admin/household.php?id=' . $id . ($memberId === null ? '' : '&member=' . $memberId))) ?>">
    <input type="hidden" name="action" value="<?= $memberId === null ? 'create_member' : 'update_member' ?>" />
    <label for="full_name" class="form-label">Full name *</label>
    <input
        class="form-control mb-3"
        id="full_name"
        name="full_name"
        maxlength="100"
        required
        value="<?= e($form['full_name']) ?>"
    />
    <label for="age" class="form-label">Age (optional)</label>
    <input
        class="form-control mb-3"
        id="age"
        name="age"
        type="number"
        min="0"
        max="130"
        value="<?= e($form['age']) ?>"
    />
    <label for="notes" class="form-label">Notes</label>
    <textarea class="form-control mb-3" id="notes" name="notes" maxlength="255">
<?= e($form['notes']) ?></textarea>
    <button class="btn btn-primary">Save member</button>
    <a href="<?= e(url('resources/views/admin/household.php?id=' . $id)) ?>">Cancel and return to household</a>
</form>
<?php require __DIR__ . '/../components/footer.php'; ?>
