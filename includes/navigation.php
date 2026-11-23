<?php
$signedIn = !empty($_SESSION["ID"]);
$links = [];
if ($signedIn) {
    $links = ["Home" => dashboardPath(), "Map" => "map/map.php"];
    $links +=
        $_SESSION["ROLE"] === "admin"
            ? [
                "Households" => "households/index.php",
                "Incidents" => "incidents/list.php",
                "Response teams" => "response/teams.php",
                "Evacuation centers" => "evacuation/centers.php",
                "Relief goods" => "relief-inventory/index.php",
                "Announcements" => "announcements/index.php",
                "Disaster summaries" => "disaster-reports/index.php",
                "Manage records" => "records/index.php",
            ]
            : [
                "Report incident" => "incidents/create.php",
                "My reports" => "incidents/list.php",
                "Announcements" => "announcements/published.php",
            ];
}
?>
<header class="site-header">
    <div class="container">
        <div class="brand-row">
            <a class="brand" href="<?= e(url($signedIn?dashboardPath():'index.php')) ?>"><span class="brand-mark" aria-hidden="true">BR</span><?= e($settings['app_name']) ?></a>
            <?php if ($signedIn): ?>
                <div class="account-menu"><span class="account-name"><?= e($_SESSION['FULL_NAME']) ?> · <?= $_SESSION['ROLE'] === 'admin'?'Staff':'Resident' ?></span>
                    <form method="post" action="<?= e(url('logout.php')) ?>">
                        <button class="btn btn-outline-secondary btn-sm" name="action" value="logout">Log out</button>
                    </form>
                </div>
            <?php else: ?>
                <span class="small">Community preparedness &amp; response</span>
            <?php endif; ?>
        </div>
        <?php if ($signedIn): ?>
            <button class="btn btn-outline-secondary navigation-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#main-navigation" aria-controls="main-navigation" aria-expanded="false">Menu</button>
            <div class="collapse navigation-collapse" id="main-navigation">
                <nav class="nav-links" aria-label="Main navigation">
                    <?php foreach ($links as $navLabel => $navPath): ?>
                        <a href="<?= e(url($navPath)) ?>" <?= parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) === url($navPath)?'aria-current="page"':'' ?>><?= e($navLabel) ?></a>
                    <?php endforeach; ?>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</header>
