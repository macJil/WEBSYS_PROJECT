<?php
require_once __DIR__ . '/common.php';
function saveMember(int $householdId, ?int $memberId, array $data)
{
    if (!isPost()) {
        return [];
    }
    if (input('action') !== ($memberId === null ? 'create_member' : 'update_member')) {
        return ['Invalid form action.'];
    }
    getHousehold($householdId);
    if ($memberId !== null) {
        member($memberId, $householdId);
    }
    $errors = [];
    if ($data['full_name'] === '' || strlen($data['full_name']) > 100) {
        $errors[] = 'Enter a member name of up to 100 bytes.';
    }
    if (strlen($data['notes']) > 255) {
        $errors[] = 'Member notes must fit within 255 bytes.';
    }
    $age =
        $data['age'] === ''
            ? null
            : filter_var($data['age'], FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 0, 'max_range' => 130],
            ]);
    if ($age === false) {
        $errors[] = 'Enter a whole-number age from 0 to 130, or leave it blank.';
    }
    if ($errors) {
        return $errors;
    }
    $data['age'] = $age;
    try {
        if (
            !empty(getHousehold($householdId)['archived_at']) ||
            ($memberId !== null && !empty(member($memberId, $householdId)['archived_at']))
        ) {
            throw new DomainException('Archived records cannot be edited.');
        }
        if ($memberId === null) {
            $stmt = db()->prepare(
                'INSERT INTO household_members (household_id,full_name,age,notes) VALUES (?,?,?,?)',
            );
            $stmt->execute([$householdId, $data['full_name'], $data['age'], $data['notes']]);
        } else {
            $stmt = db()->prepare(
                'UPDATE household_members SET full_name=?,age=?,notes=? WHERE id=? AND household_id=?',
            );
            $stmt->execute([$data['full_name'], $data['age'], $data['notes'], $memberId, $householdId]);
        }
        flash('Household member saved.');
        redirect('households/details.php?id=' . $householdId);
    } catch (DomainException $error) {
        return [$error->getMessage()];
    } catch (PDOException $error) {
        return ['The member could not be saved. Please try again.'];
    }
}

$id = recordId($_GET["id"] ?? null);
$household = getHousehold($id);
$memberId = isset($_GET["member"]) ? recordId($_GET["member"]) : null;
$form =
    $memberId === null
        ? ["full_name" => "", "age" => "", "notes" => ""]
        : member($memberId, $id);
if (isPost()) {
    $form = ["full_name" => input("full_name"), "age" => input("age"), "notes" => input("notes")];
}
$errors = saveMember($id, $memberId, $form);
$stmt = db()->prepare(
    'SELECT * FROM household_members WHERE household_id=? AND archived_at IS NULL ORDER BY full_name,id',
);
$stmt->execute([$id]);
$members = $stmt->fetchAll();
$pageTitle = "Household details";
require __DIR__ . "/../includes/header.php";
?>
<?php
$savedPoint = $household;
require __DIR__ . "/../includes/saved-point.php";
?>
<a class="btn btn-outline-secondary" href="<?= e(url('households/index.php')) ?>">Back to households</a>
<h1><?= e($household['household_code']) ?> — <?= e($household['representative_name']) ?></h1>
<p><a class="btn btn-outline-secondary" href="<?= e(url('records/index.php?type=members')) ?>">Manage records</a></p>
<a class="d-inline-block mb-3" href="<?= e(url('households/edit.php?id=' . $id)) ?>">Edit household</a>
<dl class="row">
    <?php foreach (['contact_phone' => 'Emergency contact number','purok' => 'Purok / sitio','address_details' => 'Address and location description','hazard_notes' => 'Hazard notes','vulnerability_notes' => 'Assessment notes / source','vulnerability_assessed_at' => 'Assessment date'] as $field => $label): ?>
        <dt class="col-sm-4"><?= e($label) ?></dt>
        <dd class="col-sm-8"><?= e($household[$field] ?: 'Not recorded') ?></dd>
    <?php endforeach; ?>
    <?php foreach (['registry_reason' => ['Registry reason',HOUSEHOLD_REASONS],'hazard_type' => ['Hazard concern',HOUSEHOLD_HAZARDS],'vulnerability_category' => ['Assistance classification',HOUSEHOLD_VULNERABILITY],'evacuation_status' => ['Household advisory status',HOUSEHOLD_EVACUATION]] as $field => $info): ?>
        <dt class="col-sm-4"><?= e($info[0]) ?></dt>
        <dd class="col-sm-8"><?= e($info[1][$household[$field]]) ?></dd>
    <?php endforeach; ?>
    <dt class="col-sm-4">Members recorded</dt>
    <dd class="col-sm-8"><?= (int)$household['member_count'] ?></dd>
</dl>
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
                    <td><a href="<?= e(url('households/details.php?id=' . $id . '&member=' . $member['id'])) ?>">View / edit</a></td>
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
require __DIR__ . "/../includes/errors.php";
?>
<form method="post" action="<?= e(url('households/details.php?id=' . $id . ($memberId === null ? '' : '&member=' . $memberId))) ?>">
    <input type="hidden" name="action" value="<?= $memberId === null ? 'create_member' : 'update_member' ?>" />
    <label for="full_name" class="form-label">Full name *</label>
    <input class="form-control mb-3" id="full_name" name="full_name" maxlength="100" required value="<?= e($form['full_name']) ?>" />
    <label for="age" class="form-label">Age (optional)</label>
    <input class="form-control mb-3" id="age" name="age" type="number" min="0" max="130" value="<?= e($form['age']) ?>" />
    <label for="notes" class="form-label">Notes</label>
    <textarea class="form-control mb-3" id="notes" name="notes" maxlength="255">
<?= e($form['notes']) ?></textarea> <button class="btn btn-primary">Save member</button>
    <a href="<?= e(url('households/details.php?id=' . $id)) ?>">Cancel and return to household</a>
</form>
<?php
require __DIR__ . "/../includes/footer.php";
?>
