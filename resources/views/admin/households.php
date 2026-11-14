<?php
require_once __DIR__ . '/../../../app/controllers/HouseholdController.php';
$controller = new HouseholdController();
$id = isset($_GET['edit']) ? $controller->recordId($_GET['edit']) : null;
$form = $id === null ? ['household_code' => '','representative_name' => '','contact_phone' => '','purok' => '','address_details' => '','registry_reason' => 'monitored','hazard_type' => 'unassessed','hazard_notes' => '','vulnerability_category' => 'unassessed','vulnerability_notes' => '','evacuation_status' => 'at_home'] : $controller->getHousehold($id);
if (isPost()) {
    $form = $controller->householdInput();
}
$errors = $controller->saveHousehold($id, $form);
$search = is_string($_GET['q'] ?? '') ? trim($_GET['q'] ?? '') : '';
$rows = $controller->listHouseholds(substr($search, 0, 255));
$pageTitle = 'Household registry';
require __DIR__ . '/../components/header.php';
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Household registry</h1>
<p><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/records.php?type=households')) ?>">Manage records</a></p>
<form method="get" class="filter-form d-flex gap-2 mb-3">
    <label for="search" class="visually-hidden">Search households</label>
    <input
        id="search"
        class="form-control"
        name="q"
        maxlength="255"
        value="<?= e($search) ?>"
        placeholder="Code, representative, purok, address or contact"
    />
    <button class="btn btn-outline-secondary">Search</button
    ><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/households.php')) ?>">Clear</a>
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
            <?php foreach ($rows as $row): ?><tr>
                <td><?= e($row['household_code']) ?></td>
                <td><?= e($row['representative_name']) ?></td>
                <td
                    ><?= e($row['purok']) ?> <?php if (!hasMapPoint($row)): ?><br /><small>No map point</small
                    ><?php endif; ?></td
                >
                <td><?= (int)$row['member_count'] ?></td>
                <td
                    ><a href="<?= e(url('resources/views/admin/household.php?id=' . $row['id'])) ?>">View / members</a> ·
                    <a href="<?= e(url('resources/views/admin/households.php?edit=' . $row['id'])) ?>">Edit</a></td
                > </tr
            ><?php endforeach; ?> <?php if (!$rows): ?><tr>
                <td colspan="5">No matching households.</td> </tr
            ><?php endif; ?></tbody
        >
    </table>
</div>
<h2><?= $id === null ? 'Add household' : 'Edit household' ?></h2>
<?php require __DIR__ . '/../components/errors.php'; ?>
<form method="post" action="<?= e(url('resources/views/admin/households.php' . ($id === null ? '' : '?edit=' . $id))) ?>">
    <input type="hidden" name="action" value="<?= $id === null ? 'create_household' : 'update_household' ?>" />
    <div class="row">
        <?php foreach (['household_code' => ['Household code',30,true],'representative_name' => ['Representative / emergency contact name',100,true],'contact_phone' => ['Emergency contact number',20,false],'purok' => ['Purok / sitio',100,true],'address_details' => ['Address and location description / landmark',255,false]] as $key => $field): ?>
        <div class="col-md-6 mb-3">
            <label for="<?= e($key) ?>" class="form-label"
                ><?= e($field[0]) ?><?= $field[2] ? ' *' : '' ?></label
            >
            <input
                class="form-control"
                id="<?= e($key) ?>"
                name="<?= e($key) ?>"
                maxlength="<?= $field[1] ?>"
                <?= $field[2] ? 'required' : '' ?>
                value="<?= e($form[$key]) ?>"
            />
        </div>
        <?php endforeach; ?> <?php foreach (['registry_reason' => ['Registry reason',HouseholdController::REASONS],'hazard_type' => ['Hazard concern',HouseholdController::HAZARDS],'vulnerability_category' => ['Assistance classification',HouseholdController::VULNERABILITY],'evacuation_status' => ['Household advisory status',HouseholdController::EVACUATION]] as $key => $field): ?>
        <div class="col-md-6 mb-3">
            <label for="<?= e($key) ?>" class="form-label"><?= e($field[0]) ?></label>
            <select class="form-select" id="<?= e($key) ?>" name="<?= e($key) ?>"
                ><?php foreach ($field[1] as $value => $label): ?><option value="<?= e($value) ?>" <?= $form[$key] === $value ? 'selected' : '' ?>
                    ><?= e($label) ?></option
                ><?php endforeach; ?></select
            >
        </div>
        <?php endforeach; ?> <?php foreach (['hazard_notes' => 'Hazard notes','vulnerability_notes' => 'Assessment source and assistance notes'] as $key => $label): ?>
        <div class="col-md-6 mb-3">
            <label for="<?= e($key) ?>" class="form-label"><?= e($label) ?></label>
            <textarea
                class="form-control"
                id="<?= e($key) ?>"
                name="<?= e($key) ?>"
                maxlength="255"
            >
<?= e($form[$key]) ?></textarea>
        </div>
        <?php endforeach; ?></div
    >
    <p
        >Not yet assessed: no assessment recorded. No additional assistance need identified:
        reviewed without an identified need; this does not mean disaster-safe. Additional assistance
        needed: record the practical help required. Choosing “Not yet assessed” clears prior
        assessment notes and metadata.</p
    >
    <?php $point = $form;
require __DIR__ . '/../components/location-picker.php'; ?>
    <button class="btn btn-primary">Save household</button>
    <a href="<?= e(url('resources/views/admin/households.php')) ?>">Cancel and return to households</a>
</form>
<?php require __DIR__ . '/../components/footer.php'; ?>
