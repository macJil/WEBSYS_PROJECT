<?php
require_once __DIR__ . '/common.php';
requireRole('admin');

function disasterSummaryDbRows(string $sql, array $values = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($values);
    return $stmt->fetchAll();
}

function disasterSummaryDbSummarize(string $start, string $endExclusive): array
{
    // A single read transaction keeps the summaries on the same database snapshot.
    db()->beginTransaction();
    try {
        $affected = disasterSummaryDbRows(
            "SELECT COUNT(*) AS total FROM households WHERE archived_at IS NULL AND registry_reason IN ('affected','both')",
        )[0]['total'];
        $centers = disasterSummaryDbRows("SELECT c.id, c.name, c.status, c.archived_at, c.capacity,
            COUNT(e.id) AS occupancy
            FROM evacuation_centers c
            LEFT JOIN evacuation_records e ON e.center_id = c.id AND e.checked_out_at IS NULL
            GROUP BY c.id, c.name, c.status, c.archived_at, c.capacity ORDER BY c.name, c.id");
        foreach ($centers as $index => $center) {
            $centers[$index]['available'] = availableCapacity($center);
        }
        $incidents = disasterSummaryDbRows(
            'SELECT incident_type,status,COUNT(*) AS total FROM incident_reports WHERE created_at>=? AND created_at<? GROUP BY incident_type,status',
            [$start, $endExclusive],
        );
        $goods = disasterSummaryDbRows(
            'SELECT i.id,i.item_name,i.unit,SUM(d.quantity) AS quantity FROM relief_distributions r JOIN relief_distribution_items d ON d.distribution_id=r.id JOIN relief_items i ON i.id=d.item_id WHERE r.distributed_at>=? AND r.distributed_at<? GROUP BY i.id,i.item_name,i.unit ORDER BY i.item_name,i.unit,i.id',
            [$start, $endExclusive],
        );
        $distributions = disasterSummaryDbRows(
            'SELECT COUNT(*) AS events,COUNT(DISTINCT household_id) AS households FROM relief_distributions WHERE distributed_at>=? AND distributed_at<?',
            [$start, $endExclusive],
        )[0];
        db()->commit();
        return [
            'affected' => (int) $affected,
            'centers' => $centers,
            'incidents' => $incidents,
            'goods' => $goods,
            'distributions' => $distributions,
        ];
    } catch (Throwable $error) {
        db()->rollBack();
        throw $error;
    }
}

function disasterSummaryPeriod(): array
{
    $zone = new DateTimeZone('Asia/Manila');
    $today = new DateTimeImmutable('today', $zone);
    $values = [];
    $dates = [];
    foreach (['from' => $today->format('Y-m-01'), 'to' => $today->format('Y-m-d')] as $key => $default) {
        $value = $_GET[$key] ?? $default;
        $date =
            is_string($value) && validDateInput($value)
                ? DateTimeImmutable::createFromFormat('!Y-m-d', $value, $zone)
                : false;
        if (
            !$date ||
            $date->format('Y-m-d') !== $value ||
            $value < '1000-01-01' ||
            $value > '9999-12-30'
        ) {
            http_response_code(400);
            exit('Enter valid start and end dates in YYYY-MM-DD format.');
        }
        $values[$key] = $value;
        $dates[$key] = $date;
    }
    if ($dates['from'] > $dates['to']) {
        http_response_code(400);
        exit('Start date must be on or before end date.');
    }
    return $values + [
        'start' => $values['from'] . ' 00:00:00',
        'end_exclusive' => $dates['to']->modify('+1 day')->format('Y-m-d') . ' 00:00:00',
    ];
}

function disasterSummarySummary(array $period): array
{
    return disasterSummaryDbSummarize($period['start'], $period['end_exclusive']);
}

$period = disasterSummaryPeriod();
$summary = disasterSummarySummary($period);
$matrix = [];
$statusTotals = array_fill_keys(DISASTERSUMMARY_STATUSES, 0);
foreach (DISASTERSUMMARY_TYPES as $key => $label) {
    $matrix[$key] = array_fill_keys(DISASTERSUMMARY_STATUSES, 0);
}
foreach ($summary["incidents"] as $row) {
    $matrix[$row["incident_type"]][$row["status"]] = (int) $row["total"];
    $statusTotals[$row["status"]] += (int) $row["total"];
}
$capacity = 0;
$occupancy = 0;
$available = 0;
foreach ($summary["centers"] as $center) {
    $capacity += (int) $center["capacity"];
    $occupancy += (int) $center["occupancy"];
    $available += (int) $center["available"];
}
$pageTitle = "Disaster reports and summaries";
require __DIR__ . "/../resources/views/components/header.php";
?>
<link rel="stylesheet" href="<?= e(url('resources/assets/css/disaster-summary.css')) ?>" />
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Disaster reports and summaries</h1>
<form method="get" class="filter-form no-print mb-3">
    <label>From <input type="date" name="from" value="<?= e($period['from']) ?>" required /></label>
    <label>Through <input type="date" name="to" value="<?= e($period['to']) ?>" required /></label>
    <button class="btn btn-outline-secondary">Apply period</button>
    <a href="<?= e(url('disaster-reports/index.php')) ?>">Current month</a>
    <button class="btn btn-outline-secondary" type="button" id="print-summary">Print summary</button>
</form>
<p>Selected period: <strong><?= e($period['from']) ?> through <?= e($period['to']) ?></strong> (both dates included).</p>
<p>Generated <?= e((new DateTimeImmutable('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d H:i:s')) ?> Asia/Manila.</p>
<h2>Current affected households</h2>
<p><strong><?= $summary['affected'] ?></strong> households currently marked “affected” or “both” in the registry.</p>
<p>This is the current registry designation, not a count of households affected during the selected period. The date filter does not apply to this number.</p>
<h2>Current evacuation centers</h2>
<p>Occupancy counts individual stays without a checkout timestamp. These are current figures; the date filter does not apply. Available capacity includes only open, non-archived centers, including those without map points.</p>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Center</th>
                <th>Status</th>
                <th>Capacity (people)</th>
                <th>Current occupants</th>
                <th>Available capacity (people)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($summary['centers'] as $center): ?>
                <tr>
                    <td><?= e($center['name']) ?></td>
                    <td><?= e($center['status']) ?><?= $center['archived_at'] !== null ? ' (archived)' : '' ?></td>
                    <td><?= (int)$center['capacity'] ?></td>
                    <td><?= (int)$center['occupancy'] ?></td>
                    <td><?= (int)$center['available'] ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$summary['centers']): ?>
                <tr>
                    <td colspan="5">No centers recorded.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">Total (capacity includes closed and archived centers)</th>
                <td><?= $capacity ?></td>
                <td><?= $occupancy ?></td>
                <td><?= $available ?></td>
            </tr>
        </tfoot>
    </table>
</div>
<h2>Incident reports submitted in the selected period</h2>
<p>Grouped by type and their current status. Dates use submission time, not occurrence time or the time a status changed. Historical incident counts include archived reports; dashboard cards and their lists count non-archived reports.</p>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Incident type</th>
                <?php foreach (DISASTERSUMMARY_STATUSES as $status): ?>
                    <th><?= e(ucfirst($status)) ?></th>
                <?php endforeach; ?>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (DISASTERSUMMARY_TYPES as $key => $label): ?>
                <tr>
                    <th><?= e($label) ?></th>
                    <?php foreach ($matrix[$key] as $count): ?>
                        <td><?= $count ?></td>
                    <?php endforeach; ?>
                    <td><?= array_sum($matrix[$key]) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th>Total</th>
                <?php foreach ($statusTotals as $count): ?>
                    <td><?= $count ?></td>
                <?php endforeach; ?>
                <td><?= array_sum($statusTotals) ?></td>
            </tr>
        </tfoot>
    </table>
</div>
<h2>Relief distributed in the selected period</h2>
<p><?= (int)$summary['distributions']['events'] ?> distribution events to <?= (int)$summary['distributions']['households'] ?> distinct households. Repeated visits to a household count once in the household total.</p>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Good</th>
                <th>Unit</th>
                <th>Distributed quantity</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($summary['goods'] as $good): ?>
                <tr>
                    <td><?= e($good['item_name']) ?></td>
                    <td><?= e($good['unit']) ?></td>
                    <td><?= (int)$good['quantity'] ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$summary['goods']): ?>
                <tr>
                    <td colspan="3">No goods distributed during this period.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<p>Quantities are kept separate by good and unit. Manual stock-out movements are excluded. Dates use the distribution timestamp.</p>
<p class="no-print">This staff-only page contains totals and center/item names, without household codes, personal names, contact details, incident descriptions, or resident locations.</p>
<script src="<?= e(url('resources/assets/js/print-summary.js')) ?>"></script>
<?php
require __DIR__ . "/../resources/views/components/footer.php";
?>
