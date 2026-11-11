<?php
require_once __DIR__ . '/../../../../app/controllers/DisasterSummaryController.php';
$controller=new DisasterSummaryController(); $period=$controller->period(); $summary=$controller->summary($period);
$matrix=[]; $statusTotals=array_fill_keys(DisasterSummaryController::STATUSES,0);
foreach (DisasterSummaryController::TYPES as $key=>$label) { $matrix[$key]=array_fill_keys(DisasterSummaryController::STATUSES,0); }
foreach ($summary['incidents'] as $row) { $matrix[$row['incident_type']][$row['status']]=(int)$row['total']; $statusTotals[$row['status']]+=(int)$row['total']; }
$capacity=0; $occupancy=0; $available=0;
foreach ($summary['centers'] as $center) { $capacity+=(int)$center['capacity']; $occupancy+=(int)$center['occupancy']; $available+=(int)$center['available']; }
$pageTitle='Disaster reports and summaries';
require __DIR__ . '/../../components/header.php';
?>
<link rel="stylesheet" href="<?= e(url('resources/assets/css/disaster-summary.css')) ?>">
<h1>Disaster reports and summaries</h1>
<form method="get" class="no-print mb-3">
<label>From <input type="date" name="from" value="<?= e($period['from']) ?>" required></label>
<label>Through <input type="date" name="to" value="<?= e($period['to']) ?>" required></label>
<button class="btn btn-primary">Apply period</button>
<a href="<?= e(url('resources/views/admin/summaries/disaster.php')) ?>">Current month</a>
<button class="btn btn-secondary" type="button" id="print-summary">Print summary</button>
</form>
<p>Selected period: <strong><?= e($period['from']) ?> through <?= e($period['to']) ?></strong> (both dates included).</p>
<p>Generated <?= e((new DateTimeImmutable('now',new DateTimeZone('Asia/Manila')))->format('Y-m-d H:i:s')) ?> Asia/Manila.</p>
<h2>Current affected households</h2>
<p><strong><?= $summary['affected'] ?></strong> households currently marked “affected” or “both” in the registry.</p>
<p>This is the current registry designation, not a count of households affected during the selected period. The date filter does not apply to this number.</p>
<h2>Current evacuation centers</h2>
<p>Occupancy counts individual stays without a checkout timestamp. These are current figures; the date filter does not apply.</p>
<div class="table-responsive"><table class="table"><thead><tr><th>Center</th><th>Status</th><th>Capacity (people)</th><th>Current occupants</th><th>Available in open centers</th></tr></thead><tbody>
<?php foreach ($summary['centers'] as $center): ?><tr><td><?= e($center['name']) ?></td><td><?= e($center['status']) ?></td><td><?= (int)$center['capacity'] ?></td><td><?= (int)$center['occupancy'] ?></td><td><?= (int)$center['available'] ?></td></tr><?php endforeach; ?>
<?php if (!$summary['centers']): ?><tr><td colspan="5">No centers recorded.</td></tr><?php endif; ?>
</tbody><tfoot><tr><th colspan="2">Total (capacity includes closed centers)</th><td><?= $capacity ?></td><td><?= $occupancy ?></td><td><?= $available ?></td></tr></tfoot></table></div>
<h2>Incident reports submitted in the selected period</h2>
<p>Grouped by type and their current status. Dates use submission time, not occurrence time or the time a status changed.</p>
<div class="table-responsive"><table class="table"><thead><tr><th>Incident type</th><?php foreach (DisasterSummaryController::STATUSES as $status): ?><th><?= e(ucfirst($status)) ?></th><?php endforeach; ?><th>Total</th></tr></thead><tbody>
<?php foreach (DisasterSummaryController::TYPES as $key=>$label): ?><tr><th><?= e($label) ?></th><?php foreach ($matrix[$key] as $count): ?><td><?= $count ?></td><?php endforeach; ?><td><?= array_sum($matrix[$key]) ?></td></tr><?php endforeach; ?>
</tbody><tfoot><tr><th>Total</th><?php foreach ($statusTotals as $count): ?><td><?= $count ?></td><?php endforeach; ?><td><?= array_sum($statusTotals) ?></td></tr></tfoot></table></div>
<h2>Relief distributed in the selected period</h2>
<p><?= (int)$summary['distributions']['events'] ?> distribution events to <?= (int)$summary['distributions']['households'] ?> distinct households. Repeated visits to a household count once in the household total.</p>
<table class="table"><thead><tr><th>Good</th><th>Unit</th><th>Distributed quantity</th></tr></thead><tbody>
<?php foreach ($summary['goods'] as $good): ?><tr><td><?= e($good['item_name']) ?></td><td><?= e($good['unit']) ?></td><td><?= (int)$good['quantity'] ?></td></tr><?php endforeach; ?>
<?php if (!$summary['goods']): ?><tr><td colspan="3">No goods distributed during this period.</td></tr><?php endif; ?></tbody></table>
<p>Quantities are kept separate by good and unit. Manual stock-out movements are excluded. Dates use the distribution timestamp.</p>
<p class="no-print">This staff-only page contains totals and center/item names, without household codes, personal names, contact details, incident descriptions, or resident locations.</p>
<script src="<?= e(url('resources/assets/js/print-summary.js')) ?>"></script>
<?php require __DIR__ . '/../../components/footer.php'; ?>
