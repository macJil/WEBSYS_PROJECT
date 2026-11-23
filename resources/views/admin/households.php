<?php
require_once __DIR__ . '/../../../app/controllers/HouseholdController.php';
$controller = new HouseholdController();
$id = isset($_GET['edit']) ? $controller->recordId($_GET['edit']) : null;
$form =
    $id === null
        ? [
            'contact_phone' => '',
            'purok' => '',
            'address_details' => '',
            'registry_reason' => 'monitored',
            'hazard_type' => 'unassessed',
            'hazard_notes' => '',
            'vulnerability_category' => 'unassessed',
            'vulnerability_notes' => '',
            'evacuation_status' => 'at_home',
        ]
        : $controller->getHousehold($id);
if (isPost()) {
    $form = $controller->householdInput();
}
$errors = $controller->saveHousehold($id, $form);
$search = is_string($_GET['q'] ?? '') ? trim($_GET['q'] ?? '') : '';
$rows = $controller->listHouseholds(substr($search, 0, 255));

$rows = filterBarangay($rows);
$pageTitle = 'Household registry';
require __DIR__ . '/../components/header.php';
?>
<p class="return-link"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Household registry</h1>
<p><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/records.php?type=households')) ?>">Manage records</a></p>
<form method="get" class="filter-form d-flex gap-2 mb-3">
    <?php
    $barangayPrompt = 'All barangays';
    $selectedBarangay = $_GET['barangay'] ?? '';
    require __DIR__ . '/../components/barangay.php';
    ?>
    <label for="search" class="visually-hidden">Search households</label>
    <input
        id="search"
        class="form-control"
        name="q"
        maxlength="255"
        value="<?= e($search) ?>"
        placeholder="Member, representative, purok, address or contact"
    />
    <button class="btn btn-outline-secondary">Search</button><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/households.php')) ?>">Clear</a>
</form>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Representative</th>
                <th>Purok</th>
                <th>Members</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= e($row['household_label']) ?></td>
                    <td><?= e($row['purok']) ?>

                    </td>
                    <td><?= (int)$row['member_count'] ?></td>
                    <td><a href="<?= e(url('resources/views/admin/household.php?id=' . $row['id'])) ?>">View / members</a> · <a href="<?= e(url('resources/views/admin/households.php?edit=' . $row['id'])) ?>">Edit</a></td>
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
<h2><?= $id === null ? 'Add household' : 'Edit household' ?></h2>
<?php
require __DIR__ . "/../components/errors.php";
?>
<form method="post" action="<?= e(url('resources/views/admin/households.php' . ($id === null ? '' : '?edit=' . $id))) ?>">
    <?php
    $barangayPrompt = 'Choose barangay';
    $selectedBarangay = isPost() ? input('barangay') : ($form['barangay'] ?? '');
    require __DIR__ . '/../components/barangay.php';
    ?>
    <input type="hidden" name="action" value="<?= $id === null ? 'create_household' : 'update_household' ?>" />
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="contact_phone" class="form-label">Emergency contact number</label>
            <input
                class="form-control"
                id="contact_phone"
                name="contact_phone"
                type="tel" maxlength="13" minlength="11" placeholder="09xxxxxxxxx or +639xxxxxxxxx"
                value="<?= e($form['contact_phone']) ?>"
            />
        </div>
        <div class="col-md-6 mb-3">
            <label for="purok" class="form-label">Purok / sitio *</label>
            <input
                class="form-control"
                id="purok"
                name="purok"
                maxlength="100"
                required
                value="<?= e($form['purok']) ?>"
            />
        </div>
        <div class="col-md-6 mb-3">
            <label for="address_details" class="form-label">Address and location description / landmark</label>
            <input
                class="form-control"
                id="address_details"
                name="address_details"
                maxlength="255"
                value="<?= e($form['address_details']) ?>"
            />
        </div>
        <div class="col-md-6 mb-3">
            <label for="registry_reason" class="form-label">Registry reason</label>
            <select class="form-select" id="registry_reason" name="registry_reason">
                <option value="monitored" <?= $form['registry_reason'] === 'monitored' ? 'selected' : '' ?>>Monitored</option>
                <option value="affected" <?= $form['registry_reason'] === 'affected' ? 'selected' : '' ?>>Affected</option>
                <option value="both" <?= $form['registry_reason'] === 'both' ? 'selected' : '' ?>>Monitored and affected</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="hazard_type" class="form-label">Hazard concern</label>
            <select class="form-select" id="hazard_type" name="hazard_type">
                <option value="unassessed" <?= $form['hazard_type'] === 'unassessed' ? 'selected' : '' ?>>Not assessed</option>
                <option value="landslide" <?= $form['hazard_type'] === 'landslide' ? 'selected' : '' ?>>Landslide</option>
                <option value="flood" <?= $form['hazard_type'] === 'flood' ? 'selected' : '' ?>>Flood</option>
                <option value="fire" <?= $form['hazard_type'] === 'fire' ? 'selected' : '' ?>>Fire</option>
                <option value="other" <?= $form['hazard_type'] === 'other' ? 'selected' : '' ?>>Other</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="vulnerability_category" class="form-label">Assistance classification</label>
            <select class="form-select" id="vulnerability_category" name="vulnerability_category">
                <option value="unassessed" <?= $form['vulnerability_category'] === 'unassessed' ? 'selected' : '' ?>>Not yet assessed</option>
                <option value="no_additional_need_identified" <?= $form['vulnerability_category'] === 'no_additional_need_identified' ? 'selected' : '' ?>>No additional assistance need identified</option>
                <option value="assistance_needed" <?= $form['vulnerability_category'] === 'assistance_needed' ? 'selected' : '' ?>>Additional assistance needed</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="evacuation_status" class="form-label">Household advisory status</label>
            <select class="form-select" id="evacuation_status" name="evacuation_status">
                <option value="at_home" <?= $form['evacuation_status'] === 'at_home' ? 'selected' : '' ?>>At home</option>
                <option value="advised" <?= $form['evacuation_status'] === 'advised' ? 'selected' : '' ?>>Advised to evacuate</option>
                <option value="evacuated" <?= $form['evacuation_status'] === 'evacuated' ? 'selected' : '' ?>>Evacuated</option>
                <option value="returned" <?= $form['evacuation_status'] === 'returned' ? 'selected' : '' ?>>Returned</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="hazard_notes" class="form-label">Hazard notes</label>
            <textarea class="form-control" id="hazard_notes" name="hazard_notes" maxlength="255"><?= e($form['hazard_notes']) ?></textarea>
        </div>
        <div class="col-md-6 mb-3">
            <label for="vulnerability_notes" class="form-label">Assessment source and assistance notes</label>
            <textarea class="form-control" id="vulnerability_notes" name="vulnerability_notes" maxlength="255"><?= e($form['vulnerability_notes']) ?></textarea>
        </div>
    </div>
    <p>Not yet assessed: no assessment recorded. No additional assistance need identified: reviewed without an identified need; this does not mean disaster-safe. Additional assistance needed: record the practical help required. Choosing “Not yet assessed” clears prior assessment notes and metadata.</p>
    <?php
    $point = $form;
    require __DIR__ . "/../components/location-fields.php";
    ?>
    <button class="btn btn-primary">Save household</button>
    <a href="<?= e(url('resources/views/admin/households.php')) ?>">Cancel and return to households</a>
</form>
<?php
require __DIR__ . "/../components/footer.php";
?>
