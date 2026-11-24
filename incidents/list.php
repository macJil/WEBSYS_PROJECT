<?php
require_once __DIR__ . '/common.php';
session_checker();

function reportDbListing(array $filters, ?int $owner): array
{
    $where = ['archived_at IS NULL'];
    $values = [];
    incidentScopeConditions($filters, $where, $values);
    if ($owner !== null) {
        $where[] = 'reported_by = ?';
        $values[] = $owner;
    }
    foreach (['incident_type', 'status'] as $field) {
        if ($filters[$field] !== '') {
            $where[] = "$field = ?";
            $values[] = $filters[$field];
        }
    }
    if ($filters['date'] !== '') {
        $where[] = 'DATE(created_at) = ?';
        $values[] = $filters['date'];
    }
    foreach (['location' => 'location_description', 'search' => 'description'] as $key => $field) {
        if ($filters[$key] !== '') {
            $where[] = "$field LIKE ? ESCAPE '!'";
            $values[] = '%' . strtr($filters[$key], ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        }
    }
    $stmt = db()->prepare(
        'SELECT * FROM incident_reports WHERE ' .
            implode(' AND ', $where) .
            ' ORDER BY created_at DESC, id DESC',
    );
    $stmt->execute($values);
    return $stmt->fetchAll();
}

function reportFilters(): array
{
    $filters = incidentScope();
    foreach (['incident_type', 'status', 'date', 'location', 'search'] as $key) {
        $filters[$key] = is_string($_GET[$key] ?? null) ? trim($_GET[$key]) : '';
    }
    if (
        ($filters['incident_type'] !== '' && !isset(REPORT_TYPES[$filters['incident_type']])) ||
        ($filters['status'] !== '' && !in_array($filters['status'], REPORT_STATUSES, true)) ||
        ($filters['date'] !== '' && !reportValidDate($filters['date']))
    ) {
        http_response_code(400);
        exit('Invalid search filter.');
    }
    return $filters;
}

function reportValidDate(string $date): bool
{
    return validDateInput($date);
}

function reportListIncidents(array $filters): array
{
    return reportDbListing($filters, reportOwner());
}

$filters = reportFilters();
$reports = reportListIncidents($filters);
$pageTitle = $_SESSION["ROLE"] === "admin" ? "Incident reports" : "My incident reports";
require __DIR__ . "/../includes/header.php";
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1><?= e($pageTitle) ?></h1>
<?php if ($_SESSION['ROLE'] === 'admin'): ?>
    <p><a class="btn btn-outline-secondary" href="<?= e(url('records/index.php?type=incidents')) ?>">Manage records</a></p>
<?php endif; ?>
<a class="btn btn-primary mb-3" href="<?= e(url('incidents/create.php')) ?>">Enter incident report</a>
<form method="get" class="filter-form row g-2 mb-4">
    <?php $scope = $filters; require __DIR__ . '/scope-fields.php'; ?>
    <label class="col-md-3">Type<select class="form-select" name="incident_type">
            <option value="">All types</option>
            <?php foreach (REPORT_TYPES as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= $filters['incident_type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select></label>
    <label class="col-md-3">Status<select class="form-select" name="status">
            <option value="">All statuses</option>
            <?php foreach (REPORT_STATUSES as $value): ?>
                <option <?= $filters['status'] === $value ? 'selected' : '' ?> value="<?= e($value) ?>"><?= e(ucfirst($value)) ?></option>
            <?php endforeach; ?>
        </select></label>
    <?php foreach (['date' => 'Submission date','location' => 'Location contains','search' => 'Description contains'] as $key => $label): ?>
        <label class="col-md-3"><?= e($label) ?><input class="form-control" type="<?= $key === 'date' ? 'date' : 'text' ?>" name="<?= e($key) ?>" value="<?= e($filters[$key]) ?>" /></label>
    <?php endforeach; ?>
    <div>
        <button class="btn btn-outline-secondary">Search</button>
        <a href="<?= e(url('incidents/list.php')) ?>">Clear filters</a>
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
                    <td><?= e(REPORT_TYPES[$report['incident_type']] ?? $report['incident_type']) ?></td>
                    <td><?= e($report['barangay'] ?? 'Awaiting confirmation') ?> · <?= e($report['location_description']) ?>
                        <?php if (!hasMapPoint($report)): ?>
                            <br /><small>No map point</small>
                        <?php endif; ?>
                    </td>
                    <td><?= statusBadge($report['status']) ?></td>
                    <td><?= relativeTime($report['created_at']) ?></td>
                    <td><a href="<?= e(url('incidents/view.php?id=' . $report['id'])) ?>">View</a></td>
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
require __DIR__ . "/../includes/footer.php";
?>
