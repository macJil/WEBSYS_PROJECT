<?php
require_once __DIR__ . "/helpers.php";
session_checker();
if (!isset($overview)) {
    http_response_code(404);
    exit();
}
$admin = $_SESSION["ROLE"] === "admin";
$cards = [];
foreach ($overview["incidents"] as $status => $count) {
    if (!$admin || in_array($status, ["pending", "verified", "responding"], true)) {
        $cards[] = [
            ucfirst($status) . " incidents",
            $count,
            "incidents/list.php?status=" . $status,
        ];
    }
}
$centerPath = "evacuation/open-centers.php";
$cards[] = ["Open centers", count($overview["centers"]), $centerPath];
if ($admin) {
    $available = 0;
    foreach ($overview["centers"] as $center) {
        $available += $center["available"];
    }
    $cards[] = ["Available capacity (people)", $available, $centerPath];
    $cards[] = [
        "Affected households",
        count($overview["affected"]),
        "disaster-reports/current.php?type=households",
    ];
    $cards[] = [
        "Active evacuees",
        count($overview["stays"]),
        "disaster-reports/current.php?type=evacuees",
    ];
} else {
    $cards[] = ["Published notices", count($overview["notices"]), "announcements/published.php"];
}
?>
<?php if ($admin): ?>
    <p class="small">Available capacity counts additional people that open, non-archived centers can accommodate. Centers without map points are included.</p>
<?php endif; ?>
<div class="row g-3 mb-4">
    <?php foreach ($cards as [$label,$value,$path]): ?>
        <div class="col-sm-6 col-lg-4"><a class="stat-card" href="<?= e(url($path)) ?>"><strong class="stat-value"><?= (int)$value ?></strong><span class="stat-label"><?= e($label) ?></span></a> </div>
    <?php endforeach; ?>
</div>
<?php if (!$admin): ?>
    <h2>Latest published notices</h2>
    <?php foreach (array_slice($overview['notices'], 0, 3) as $notice): ?>
        <p><a href="<?= e(url('announcements/published.php?q=' . urlencode($notice['title']))) ?>"><?= e($notice['title']) ?></a> · <?= e($notice['created_at']) ?></p>
    <?php endforeach; ?>
    <?php if (!$overview['notices']): ?>
        <p>No published notices.</p>
    <?php endif; ?>
<?php endif; ?>
