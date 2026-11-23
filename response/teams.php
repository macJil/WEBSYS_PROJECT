<?php
require_once __DIR__ . '/common.php';
requireRole('admin');

function responseDbTeam(int $id)
{
    $stmt = db()->prepare('SELECT * FROM responders WHERE id=?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function responseDbSaveTeam(?int $id, string $name, string $phone, int $active): void
{
    if ($id !== null) {
        $stmt = db()->prepare(
            'UPDATE responders SET team_name=?, contact_phone=?, is_active=? WHERE id=?',
        );
        $stmt->execute([$name, $phone ?: null, $active, $id]);
    } else {
        $stmt = db()->prepare(
            'INSERT INTO responders (team_name,contact_phone,is_active) VALUES (?,?,?)',
        );
        $stmt->execute([$name, $phone ?: null, $active]);
    }
}

function responseTeam(int $id): array
{
    $row = responseDbTeam($id);
    if (!$row) {
        http_response_code(404);
        exit('Team not found.');
    }
    return $row;
}

function responseSaveTeam(?int $id): array
{
    if (!isPost()) {
        return [];
    }
    if (input('action') !== 'save_team') {
        return ['Unknown form action.'];
    }
    $name = input('team_name');
    $phone = input('contact_phone');
    $active = input('is_active');
    if ($name === '' || strlen($name) > 120) {
        return ['Team name is required (maximum 120 bytes).'];
    }
    if ($phone !== '' && !validPhone($phone, 3)) {
        return ['Enter a valid contact number, or leave it blank.'];
    }
    if (!in_array($active, ['0', '1'], true)) {
        return ['Choose active or inactive.'];
    }
    try {
        responseDbSaveTeam($id, $name, $phone, (int) $active);
    } catch (PDOException $error) {
        return [
            ($error->errorInfo[1] ?? null) === 1062
                ? 'That team name is already in use.'
                : 'Team could not be saved. Please try again.',
        ];
    }
    flash('Team saved.');
    redirect('response/teams.php');
    return [];
}

$id = isset($_GET["edit"]) ? responseRecordId($_GET["edit"]) : null;
$team = $id ? responseTeam($id) : ["team_name" => "", "contact_phone" => "", "is_active" => 1];
$errors = responseSaveTeam($id);
if (isPost()) {
    $team = [
        "team_name" => input("team_name"),
        "contact_phone" => input("contact_phone"),
        "is_active" => input("is_active"),
    ];
}
$teams = responseDbTeams();
$pageTitle = "Response teams";
require __DIR__ . "/../includes/header.php";
require __DIR__ . "/../includes/errors.php";
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Response teams</h1>
<p><a class="btn btn-outline-secondary" href="<?= e(url('records/index.php?type=teams')) ?>">Manage records</a></p>
<p>Inactive teams cannot receive new assignments. Existing assignments remain available for follow-up and completion.</p>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Team</th>
                <th>Contact</th>
                <th>Availability</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($teams as $row): ?>
                <tr>
                    <td><?= e($row['team_name']) ?></td>
                    <td><?= e($row['contact_phone']) ?></td>
                    <td><?= $row['is_active'] ? 'Active' : 'Inactive' ?></td>
                    <td><a href="<?= e(url('response/teams.php?edit=' . $row['id'])) ?>">Edit / change availability</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$teams): ?>
                <tr>
                    <td colspan="4">No response teams yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<h2><?= $id ? 'Edit team' : 'Add team' ?></h2>
<form method="post">
    <label class="d-block mb-3">Team name<input class="form-control" name="team_name" maxlength="120" value="<?= e($team['team_name']) ?>" required /></label>
    <label class="d-block mb-3">Contact number<input class="form-control" name="contact_phone" maxlength="20" value="<?= e($team['contact_phone']) ?>" /></label>
    <label class="d-block mb-3">Availability<select class="form-select" name="is_active">
            <option value="1" <?= (string)$team['is_active'] === '1' ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= (string)$team['is_active'] === '0' ? 'selected' : '' ?>>Inactive</option>
        </select></label>
    <button class="btn btn-primary" name="action" value="save_team">Save team</button>
    <a href="<?= e(url('response/teams.php')) ?>">Cancel and return to response teams</a>
</form>
<?php
require __DIR__ . "/../includes/footer.php";
?>
