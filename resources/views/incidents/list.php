<?php
require_once __DIR__ . '/../../../app/controllers/ReportController.php';
session_checker();
$controller = new ReportController($_SESSION['ROLE']);
$filters = $controller->filters(); $reports = $controller->listing($filters);
$pageTitle = $_SESSION['ROLE'] === 'admin' ? 'Incident reports' : 'My incident reports';
require __DIR__ . '/../components/header.php';
?>
<h1><?= e($pageTitle) ?></h1>
<?php if($_SESSION['ROLE']==='admin'): ?><p><a href="<?= e(url('resources/views/admin/records.php?type=incidents')) ?>">Select records / view archived</a></p><?php endif; ?>
<a class="btn btn-primary mb-3" href="<?= e(url('resources/views/incidents/create.php')) ?>">Enter incident report</a>
<form method="get" class="row g-2 mb-4">
<label class="col-md-3">Type<select class="form-select" name="incident_type"><option value="">All types</option>
<?php foreach (ReportController::TYPES as $value=>$label): ?><option value="<?= e($value) ?>" <?= $filters['incident_type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
<label class="col-md-3">Status<select class="form-select" name="status"><option value="">All statuses</option>
<?php foreach (ReportController::STATUSES as $value): ?><option <?= $filters['status'] === $value ? 'selected' : '' ?> value="<?= e($value) ?>"><?= e(ucfirst($value)) ?></option><?php endforeach; ?></select></label>
<?php foreach (['date'=>'Submission date','location'=>'Location contains','search'=>'Description contains'] as $key=>$label): ?>
<label class="col-md-3"><?= e($label) ?><input class="form-control" type="<?= $key === 'date' ? 'date' : 'text' ?>" name="<?= e($key) ?>" value="<?= e($filters[$key]) ?>"></label>
<?php endforeach; ?>
<div><button class="btn btn-secondary">Search</button> <a href="<?= e(url('resources/views/incidents/list.php')) ?>">Clear filters</a></div>
</form>
<div class="table-responsive"><table class="table"><thead><tr><th>ID</th><th>Type</th><th>Location</th><th>Status</th><th>Submitted</th><th>Details</th></tr></thead><tbody>
<?php foreach ($reports as $report): ?>
<tr><td><?= (int)$report['id'] ?></td><td><?= e(ReportController::TYPES[$report['incident_type']] ?? $report['incident_type']) ?></td><td><?= e($report['location_description']) ?><?php if (!hasMapPoint($report)): ?><br><small>No map point</small><?php endif; ?></td><td><?= e($report['status']) ?></td><td><?= e($report['created_at']) ?></td><td><a href="<?= e(url('resources/views/incidents/view.php?id=' . $report['id'])) ?>">View</a></td></tr>
<?php endforeach; ?>
<?php if (!$reports): ?><tr><td colspan="6">No matching reports.</td></tr><?php endif; ?>
</tbody></table></div>
<?php require __DIR__ . '/../components/footer.php'; ?>
