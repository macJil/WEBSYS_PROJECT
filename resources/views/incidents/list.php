<?php
require_once __DIR__ . "/../../../app/controllers/ReportController.php";
session_checker();
$controller = new ReportController($_SESSION["ROLE"]);
$filters = $controller->filters();
$reports = $controller->listIncidents($filters);
$pageTitle = $_SESSION["ROLE"] === "admin" ? "Incident reports" : "My incident reports";
require __DIR__ . "/../components/header.php";
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1><?= e($pageTitle) ?></h1>
<?php if ($_SESSION['ROLE'] === 'admin'): ?>
    <p><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/records.php?type=incidents')) ?>">Manage records</a></p>
<?php endif; ?>
<a class="btn btn-primary mb-3" href="<?= e(url('resources/views/incidents/create.php')) ?>">Enter incident report</a>
<form method="get" class="filter-form row g-2 mb-4">
    <label class="col-md-3">Type<select class="form-select" name="incident_type">
            <option value="">All types</option>
            <option value="flood" <?= $filters['incident_type'] === 'flood' ? 'selected' : '' ?>>Flooding</option>
            <option value="fire" <?= $filters['incident_type'] === 'fire' ? 'selected' : '' ?>>Fire</option>
            <option value="landslide" <?= $filters['incident_type'] === 'landslide' ? 'selected' : '' ?>>Landslide</option>
            <option value="road_obstruction" <?= $filters['incident_type'] === 'road_obstruction' ? 'selected' : '' ?>>Road obstruction</option>
            <option value="other" <?= $filters['incident_type'] === 'other' ? 'selected' : '' ?>>Other emergency</option>
        </select></label>
    <label class="col-md-3">Status<select class="form-select" name="status">
            <option value="">All statuses</option>
            <option <?= $filters['status'] === 'pending' ? 'selected' : '' ?> value="pending">Pending</option>
            <option <?= $filters['status'] === 'verified' ? 'selected' : '' ?> value="verified">Verified</option>
            <option <?= $filters['status'] === 'responding' ? 'selected' : '' ?> value="responding">Responding</option>
            <option <?= $filters['status'] === 'resolved' ? 'selected' : '' ?> value="resolved">Resolved</option>
            <option <?= $filters['status'] === 'rejected' ? 'selected' : '' ?> value="rejected">Rejected</option>
        </select></label>
    <label class="col-md-3">Submission date
        <input class="form-control" type="date" name="date" value="<?= e($filters['date']) ?>" />
    </label>
    <label class="col-md-3">Location contains
        <input class="form-control" type="text" name="location" value="<?= e($filters['location']) ?>" />
    </label>
    <label class="col-md-3">Description contains
        <input class="form-control" type="text" name="search" value="<?= e($filters['search']) ?>" />
    </label>
    <div>
        <button class="btn btn-outline-secondary">Search</button>
        <a href="<?= e(url('resources/views/incidents/list.php')) ?>">Clear filters</a>
    </div>
</form>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Location</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reports as $report): ?>
                <tr>
                    <td><?= (int)$report['id'] ?></td>
                    <td><?= e(ReportController::TYPES[$report['incident_type']] ?? $report['incident_type']) ?></td>
                    <td><?= e($report['location_description']) ?>
                        <?php if (!hasMapPoint($report)): ?>
                            <br /><small>No map point</small>
                        <?php endif; ?>
                    </td>
                    <td><span class="status-badge" data-status="<?= e($report['status']) ?>"><?= e($report['status']) ?></span></td>
                    <td><?= e($report['created_at']) ?></td>
                    <td><a href="<?= e(url('resources/views/incidents/view.php?id=' . $report['id'])) ?>">View</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$reports): ?>
                <tr>
                    <td colspan="6">No matching reports.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
require __DIR__ . "/../components/footer.php";
?>
