<?php
require_once __DIR__ . '/common.php';
if (!isset($form)) {
    http_response_code(404);
    exit;
}
$householdFields = [
    'household_code' => ['Household code', 30, true],
    'representative_name' => ['Representative / emergency contact name', 100, true],
    'contact_phone' => ['Emergency contact number', 13, false],
    'purok' => ['Purok / sitio', 100, true],
];
$householdCategories = [
    'registry_reason' => ['Registry reason', HOUSEHOLD_REASONS],
    'hazard_type' => ['Hazard concern', HOUSEHOLD_HAZARDS],
    'vulnerability_category' => ['Assistance classification', HOUSEHOLD_VULNERABILITY],
    'evacuation_status' => ['Household advisory status', HOUSEHOLD_EVACUATION],
];
$householdNotes = [
    'hazard_notes' => 'Hazard notes',
    'vulnerability_notes' => 'Assessment source and assistance notes',
];

$pageTitle = 'Household registry';
require __DIR__ . '/../includes/header.php';
?>
<h1><?= $id === null ? 'Add household' : 'Edit household' ?></h1>
<?php
require __DIR__ . "/../includes/errors.php";
?>
<form method="post" action="<?= e(url($id === null ? 'households/add.php' : 'households/edit.php?id=' . $id)) ?>">
    <input type="hidden" name="action" value="<?= $id === null ? 'create_household' : 'update_household' ?>" />
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="barangay" class="form-label">Barangay *</label>
            <select id="barangay" name="barangay" class="form-select" required>
                <option value="">Choose barangay</option>
                <?php foreach (ATOK_BARANGAYS as $barangay): ?>
                    <option <?= ($form['barangay'] ?? '') === $barangay ? 'selected' : '' ?>><?= e($barangay) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php foreach ($householdFields as $key => $field): ?>
            <div class="col-md-6 mb-3">
                <label for="<?= e($key) ?>" class="form-label"><?= e($field[0]) ?><?= $field[2] ? ' *' : '' ?></label>
                <?php $missing = !empty($errors) && $field[2] && $form[$key] === ''; ?>
                <input class="form-control<?= $missing ? ' is-invalid' : '' ?>" <?= $missing ? 'aria-invalid="true"' : '' ?> id="<?= e($key) ?>" name="<?= e($key) ?>" maxlength="<?= $field[1] ?>" <?= $field[2] ? 'required' : '' ?> value="<?= e($form[$key]) ?>" />
            </div>
        <?php endforeach; ?>
        <?php foreach ($householdCategories as $key => $field): ?>
            <div class="col-md-6 mb-3">
                <label for="<?= e($key) ?>" class="form-label"><?= e($field[0]) ?></label>
                <select class="form-select" id="<?= e($key) ?>" name="<?= e($key) ?>">
                    <?php foreach ($field[1] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $form[$key] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endforeach; ?>
    <?php
    $pickerKind = 'households';
    $point = $form;
    require __DIR__ . "/../includes/location-picker.php";
    ?>
        <?php foreach ($householdNotes as $key => $label): ?>
            <div class="col-md-6 mb-3">
                <label for="<?= e($key) ?>" class="form-label"><?= e($label) ?></label>
                <textarea class="form-control" id="<?= e($key) ?>"
                name="<?= e($key) ?>"
                maxlength="255"
            >
<?= e($form[$key]) ?></textarea>
            </div>
        <?php endforeach; ?>
    </div>
    <p>Not yet assessed: no assessment recorded. No additional assistance need identified: reviewed without an identified need; this does not mean disaster-safe. Additional assistance needed: record the practical help required. Choosing “Not yet assessed” clears prior assessment notes and metadata.</p>

    <button class="btn btn-primary">Save household</button>
    <a href="<?= e(url('households/index.php')) ?>">Cancel and return to households</a>
</form>
<?php
require __DIR__ . "/../includes/footer.php";
?>
