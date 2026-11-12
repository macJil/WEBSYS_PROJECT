<?php
$signedIn=!empty($_SESSION['ID']);
$links=[];
if ($signedIn) {
    $links=['Home'=>dashboardPath(),'Map'=>'resources/views/maps/map.php'];
    $links += $_SESSION['ROLE']==='admin' ? [
        'Households'=>'resources/views/admin/households.php',
        'Incidents'=>'resources/views/incidents/list.php',
        'Response teams'=>'resources/views/admin/response/teams.php',
        'Evacuation centers'=>'resources/views/admin/evacuation/centers.php',
        'Relief goods'=>'resources/views/admin/relief/inventory.php',
        'Announcements'=>'resources/views/admin/announcement/announcement.php',
        'Disaster summaries'=>'resources/views/admin/summaries/disaster.php',
        'Manage records'=>'resources/views/admin/records.php'
    ] : [
        'Report incident'=>'resources/views/incidents/create.php',
        'My reports'=>'resources/views/incidents/list.php',
        'Announcements'=>'resources/views/user/announcement.php'
    ];
}
?>
<header class="site-header"><div class="container">
<div class="brand-row">
<a class="brand" href="<?= e(url($signedIn?dashboardPath():'index.php')) ?>"><span class="brand-mark" aria-hidden="true">BR</span><?= e($settings['app_name']) ?></a>
<?php if ($signedIn): ?><div class="account-menu"><span class="account-name"><?= e($_SESSION['FULL_NAME']) ?> · <?= $_SESSION['ROLE']==='admin'?'Staff':'Resident' ?></span><form method="post" action="<?= e(url(dashboardPath())) ?>"><button class="btn btn-outline-secondary btn-sm" name="action" value="logout">Log out</button></form></div>
<?php else: ?><span class="small">Community preparedness &amp; response</span><?php endif; ?>
</div>
<?php if ($signedIn): ?><nav class="nav-links" aria-label="Main navigation">
<?php foreach ($links as $label=>$path): ?><a href="<?= e(url($path)) ?>" <?= parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH)===url($path)?'aria-current="page"':'' ?>><?= e($label) ?></a><?php endforeach; ?>
</nav><?php endif; ?>
</div></header>
