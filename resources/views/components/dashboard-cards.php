<?php
require_once __DIR__.'/../../../app/helpers.php'; session_checker();
if(!isset($overview)){http_response_code(404);exit;}
$admin=$_SESSION['ROLE']==='admin';$cards=[];
foreach($overview['incidents'] as $status=>$count){if(!$admin||in_array($status,['pending','verified','responding'],true))$cards[]=[ucfirst($status).' incidents',$count,'resources/views/incidents/list.php?status='.$status];}
$centerPath='resources/views/maps/centers.php';
$cards[]=['Open '.($admin?'':'approved ').'centers',count($overview['centers']),$centerPath];
if($admin){
 $available=0;foreach($overview['centers'] as $center)$available+=max(0,(int)$center['capacity']-(int)$center['occupancy']);
 $cards[]=['Available places',$available,$centerPath];
 $cards[]=['Affected households',count($overview['affected']),'resources/views/admin/summaries/current.php?type=households'];
 $cards[]=['Active evacuees',count($overview['stays']),'resources/views/admin/summaries/current.php?type=evacuees'];
 $cards[]=['Low-stock goods',$overview['low_stock'],'resources/views/admin/relief/inventory.php?low=1'];
}else{$cards[]=['Published notices',count($overview['notices']),'resources/views/user/announcement.php'];}
?>
<div class="row g-3 mb-4"><?php foreach($cards as [$label,$value,$path]): ?><div class="col-md-4"><a class="d-block border rounded p-3" href="<?= e(url($path)) ?>"><strong class="fs-3"><?= (int)$value ?></strong><br><?= e($label) ?></a></div><?php endforeach; ?></div>
<?php if(!$admin): ?><h2>Latest published notices</h2><?php foreach(array_slice($overview['notices'],0,3) as $notice): ?><p><a href="<?= e(url('resources/views/user/announcement.php?q='.urlencode($notice['title']))) ?>"><?= e($notice['title']) ?></a> · <?= e($notice['created_at']) ?></p><?php endforeach; ?><?php if(!$overview['notices']): ?><p>No published notices.</p><?php endif; ?><?php endif; ?>
