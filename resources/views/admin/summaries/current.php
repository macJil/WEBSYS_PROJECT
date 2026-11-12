<?php
require_once __DIR__.'/../../../../app/controllers/DashboardController.php';
$c=new DashboardController('admin');$type=$_GET['type']??'';
if(!in_array($type,['households','evacuees'],true)){http_response_code(400);exit('Invalid list.');}
$d=$c->data();$rows=$type==='households'?$d['affected']:$d['stays'];
$pageTitle=$type==='households'?'Currently affected households':'Active evacuees';require __DIR__.'/../../components/header.php';
?>
<h1><?= e($pageTitle) ?></h1><p><?= count($rows) ?> records.</p>
<table class="table"><tr><th>Record</th><th>Details</th></tr>
<?php foreach($rows as $row): ?><tr><td><?= e($type==='households'?$row['household_code']:$row['person_name']) ?></td><td><a href="<?= e(url($type==='households'?'resources/views/admin/household.php?id='.$row['id']:'resources/views/admin/evacuation/center.php?id='.$row['center_id'])) ?>"><?= e($type==='households'?$row['representative_name']:$row['center_name']) ?></a></td></tr><?php endforeach; ?></table>
<?php require __DIR__.'/../../components/footer.php'; ?>
