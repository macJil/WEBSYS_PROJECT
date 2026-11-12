<?php
require_once __DIR__.'/../../../app/controllers/RecordActionsController.php';
$c=new RecordActionsController();$type=is_string($_GET['type']??null)?$_GET['type']:'households';
if(!isset(RecordActionsModel::TYPES[$type])){http_response_code(400);exit('Unsupported record type.');}
$q=is_string($_GET['q']??null)?trim($_GET['q']):'';$archived=($_GET['archived']??'')==='1';
$page=filter_var($_GET['page']??1,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>1000000]]);if(!$page){http_response_code(400);exit('Invalid page.');}
$errors=[];$review=null;
try{
 if(isPost()&&input('action')==='confirm')$c->confirm($type);
 $rows=$c->listing($type,$q,$archived,$page);
 if(isPost()&&input('action')==='review')$review=$c->review($type,array_map('intval',array_column($rows,'id')));
}catch(DomainException $e){$errors[]=$e->getMessage();}catch(Throwable $e){error_log((string)$e);$errors[]='The batch could not be completed. No selected records were changed. Reload and review again.';}
$rows=$rows??[];[$table,$label,$title,$path]=RecordActionsModel::TYPES[$type];$pageTitle='Manage '.$title;
require __DIR__.'/../components/header.php';require __DIR__.'/../components/errors.php';
?>
<h1><?= e($pageTitle) ?></h1>
<p>Delete unused records or archive/deactivate records that must retain history. Stock movements, distributions, stays, assignments, and response updates cannot be deleted here. Use checkout/completion or a documented correcting stock movement where appropriate.</p>
<form method="get" class="filter-form mb-3"><label>Records<select name="type"><?php foreach(RecordActionsModel::TYPES as $key=>$spec): ?><option value="<?= e($key) ?>" <?= $type===$key?'selected':'' ?>><?= e($spec[2]) ?></option><?php endforeach; ?></select></label>
<label>Search ID/name <input name="q" value="<?= e($q) ?>"></label><label><input type="checkbox" name="archived" value="1" <?= $archived?'checked':'' ?>> Show archived / inactive only</label><button>Search</button></form>
<?php if($review): ?>
<h2>Review: <?= $review['action']==='delete'?'permanent deletion':'archive / deactivate' ?></h2>
<p>If any selected record is ineligible or has changed, the entire batch will be cancelled.</p>
<ul><?php foreach($review['rows'] as $row): ?><li>#<?= (int)$row['id'] ?> — <?= e($row[$label]??'Unnamed') ?></li><?php endforeach; ?></ul>
<form method="post"><input type="hidden" name="ticket" value="<?= e($review['ticket']) ?>"><button class="btn btn-danger" name="action" value="confirm">Confirm selected action</button> <a href="<?= e(url('resources/views/admin/records.php?type='.$type)) ?>">Cancel</a></form>
<?php else: ?>
<form method="post" id="record-selection">
<label><input type="checkbox" id="select-page"> Select all on this page</label> <span id="selected-count" role="status">0 selected</span>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records"><table class="table"><thead><tr><th>Select</th><th>ID</th><th>Name / description</th><th>Record</th></tr></thead><tbody>
<?php foreach($rows as $row): ?>
<tr><td><input type="checkbox" name="ids[]" value="<?= (int)$row['id'] ?>" aria-label="<?= e('Select '.$title.' #'.$row['id']) ?>"></td><td><?= (int)$row['id'] ?></td><td><?= e($row[$label]??'Unnamed') ?></td><td>
<?php $link=$path?(url($path.$row['id'])):($type==='members'?url('resources/views/admin/household.php?id='.$row['household_id'].'&member='.$row['id']):url('resources/views/admin/records.php?type=walkins&q='.$row['id'].'&archived='.($archived?'1':'0'))); ?>
<a href="<?= e($link) ?>">View record</a></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="4">No matching records.</td></tr><?php endif; ?></tbody></table></div>
<label>Action<select name="operation"><option value="archive">Archive / deactivate</option><option value="delete">Permanently delete unused records</option></select></label><button name="action" value="review" class="btn btn-primary">Review selected</button>
</form>
<?php $params=['type'=>$type,'q'=>$q,'archived'=>$archived?'1':'0']; ?>
<p>Page <?= $page ?> (up to 50 records). <?php if($page>1): ?><a href="?<?= e(http_build_query($params+['page'=>$page-1])) ?>">Previous</a><?php endif; ?> <?php if(count($rows)===50): ?><a href="?<?= e(http_build_query($params+['page'=>$page+1])) ?>">Next</a><?php endif; ?></p>
<script src="<?= e(url('resources/assets/js/record-selection.js')) ?>"></script>
<?php endif; ?>
<?php require __DIR__.'/../components/footer.php'; ?>
