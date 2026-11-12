<?php
require_once __DIR__ . '/../../../../app/controllers/ResponseController.php';
$controller = new ResponseController();
$id = $controller->id($_GET['id'] ?? null);
$incident = $controller->incident($id);
$errors = $controller->record($id);
$assignments = $controller->assignments($id);
$history = $controller->history($id);
$assignedTeamIds = array_column($assignments, 'responder_id');
$teams = array_filter($controller->teams(), fn ($team) => $team['is_active'] && !in_array($team['id'], $assignedTeamIds));
$open = in_array($incident['status'], ['verified','responding'], true);
$pageTitle = 'Response for incident #' . $id;
require __DIR__ . '/../../components/header.php';
require __DIR__ . '/../../components/errors.php';
?>
<h1><?= e($pageTitle) ?></h1>
<p><?= e($incident['incident_type']) ?> · <?= e($incident['location_description']) ?> · Status: <strong><?= e($incident['status']) ?></strong></p>
<a href="<?= e(url('resources/views/incidents/view.php?id=' . $id)) ?>">Incident details / staff review</a>
<p>Verify an incident before assigning teams. Dispatch changes it to responding. Complete or cancel all active assignments before resolving it in staff review.</p>
<?php if (!$open): ?><p class="alert alert-info">Verify or reopen this incident in staff review before adding response updates.</p><?php endif; ?>
<?php if ($open && $teams): ?>
<h2>Assign a team</h2>
<form method="post" class="mb-4">
<label class="d-block">Team<select class="form-select" name="team_id" required><option value="">Choose a team</option>
<?php foreach ($teams as $team): ?><option value="<?= (int)$team['id'] ?>" <?= input('team_id') === (string)$team['id'] ? 'selected' : '' ?>><?= e($team['team_name']) ?></option><?php endforeach; ?></select></label>
<label class="d-block my-2">Dispatch instructions / action taken<textarea class="form-control" name="notes" maxlength="2000" required><?= input('action') === 'assign_team' ? e(input('notes')) : '' ?></textarea></label>
<button class="btn btn-primary" name="action" value="assign_team">Assign team</button>
</form>
<?php elseif ($open): ?><p>No unassigned active teams. <a href="<?= e(url('resources/views/admin/response/teams.php')) ?>">Manage teams</a> or reactivate an existing assignment below.</p><?php endif; ?>
<h2>Assigned teams</h2>
<?php if (!$assignments): ?><p>No teams assigned.</p><?php endif; ?>
<?php foreach ($assignments as $assignment): ?>
<section class="border rounded p-3 mb-3">
<h3><?= e($assignment['team_name']) ?></h3>
<p><?= e($assignment['contact_phone']) ?> · <?= $assignment['is_active'] ? 'Active team' : 'Inactive team' ?> · Assignment: <?= e($assignment['status']) ?><br>Originally assigned by <?= e($assignment['assigned_by_name']) ?> at <?= e($assignment['assigned_at']) ?></p>
<?php if ($open): $retry = input('action') === 'update_assignment' && input('assignment_id') === (string)$assignment['id']; ?>
<form method="post">
<input type="hidden" name="assignment_id" value="<?= (int)$assignment['id'] ?>">
<label>Status<select class="form-select" name="assignment_status">
<?php foreach (ResponseController::STATUSES as $status): ?><option value="<?= e($status) ?>" <?= ($retry ? input('assignment_status') : $assignment['status']) === $status ? 'selected' : '' ?>><?= e(str_replace('_', ' ', ucfirst($status))) ?></option><?php endforeach; ?></select></label>
<label class="d-block my-2">Action taken<textarea class="form-control" name="notes" maxlength="2000" required><?= $retry ? e(input('notes')) : '' ?></textarea></label>
<button class="btn btn-secondary" name="action" value="update_assignment">Record update</button>
</form>
<?php endif; ?></section>
<?php endforeach; ?>
<h2>Response and review history</h2>
<?php if (!$history): ?><p>No updates yet.</p><?php endif; ?>
<?php foreach ($history as $entry): ?><article class="border-bottom py-2"><strong><?= e($entry['staff_name']) ?></strong> · <?= e($entry['created_at']) ?><br>Incident: <?= e($entry['from_status']) ?> → <?= e($entry['to_status']) ?>
<?php if ($entry['assignment_id']): ?> · Assignment #<?= (int)$entry['assignment_id'] ?>: <?= e($entry['assignment_status']) ?><?php endif; ?>
<p class="preserve-text"><?= e($entry['action_taken']) ?></p></article><?php endforeach; ?>
<?php require __DIR__ . '/../../components/footer.php'; ?>
