<?php
require_once __DIR__ . "/../../../app/helpers.php";
session_checker();
if (!isset($overview)) {
    http_response_code(404);
    exit();
}
$admin = $_SESSION["ROLE"] === "admin";
$barangay = $overview['barangay'];
$filter = $barangay === '' ? '' : 'barangay=' . urlencode($barangay);
$extraFilter = $filter === '' ? '' : '&' . $filter;
$cards = [];
foreach ($overview["incidents"] as $status => $count) {
    if (!$admin || in_array($status, ["pending", "verified", "responding"], true)) {
        $cards[] = [
            ucfirst($status) . " incidents",
            $count,
            "resources/views/incidents/list.php?status=" . $status . $extraFilter,
        ];
    }
}
$centerPath = "resources/views/centers/list.php";
if ($filter !== '') {
    $centerPath .= '?' . $filter;
}
if ($admin) {
    $cards[] = ['Active evacuees', count($overview['stays']), 'resources/views/admin/summaries/current.php?type=evacuees' . $extraFilter];
    $available = array_sum(array_column($overview['centers'], 'available'));
    $cards[] = ['Available capacity (people)', $available, $centerPath];
    $cards[] = ['Open centers', count($overview['centers']), $centerPath];
    $cards[] = ['Affected households', count($overview['affected']), 'resources/views/admin/summaries/current.php?type=households' . $extraFilter];
    $cards[] = ['Municipal inventory items', $overview['inventory'], 'resources/views/admin/relief/inventory.php'];
} else {
    $cards[] = ['Open centers', count($overview['centers']), $centerPath];
    $cards[] = ["Published notices", count($overview["notices"]), "resources/views/user/announcement.php"];
}
?>
<?php if ($admin): ?>
    <p class="small">Available capacity counts additional people that open, non-archived centers can accommodate. Evacuees are grouped by center location. Municipal inventory counts active catalog items across Atok.</p>
<?php endif; ?>
<div class="row g-3 mb-4">
    <?php foreach ($cards as [$label,$value,$path]): ?>
        <div class="col-sm-6 col-lg-4"><a class="stat-card" href="<?= e(url($path)) ?>"><strong class="stat-value"><?= (int)$value ?></strong><span class="stat-label"><?= e($label) ?></span></a> </div>
    <?php endforeach; ?>
</div>
<?php if (!$admin): ?>
    <h2>Latest published notices</h2>
    <?php foreach (array_slice($overview['notices'], 0, 3) as $notice): ?>
        <p><a href="<?= e(url('resources/views/user/announcement.php?q=' . urlencode($notice['title']))) ?>"><?= e($notice['title']) ?></a> · <?= e($notice['created_at']) ?></p>
    <?php endforeach; ?>
    <?php if (!$overview['notices']): ?>
        <p>No published notices.</p>
    <?php endif; ?>
<?php endif; ?>
