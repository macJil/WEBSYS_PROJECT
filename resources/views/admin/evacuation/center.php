<?php
require_once __DIR__ . '/../../../../app/controllers/EvacuationController.php';
$controller=new EvacuationController(); $id=$controller->id($_GET['id']??null);
$center=$controller->center($id); $errors=$controller->handleStay($id);
$records=$controller->records($id); $members=$controller->members(); $walkins=$controller->walkins();
$pageTitle=$center['name'];
require __DIR__ . '/../../components/header.php'; require __DIR__ . '/../../components/errors.php';
?>
<?php $savedPoint=$center; require __DIR__ . '/../../components/saved-point.php'; ?>
<h1><?= e($center['name']) ?></h1>
<p><a href="<?= e(url('resources/views/admin/map-publication.php?type=center&id='.$id)) ?>">Manage community map visibility</a></p>
<p><a href="<?= e(url('resources/views/admin/records.php?type=walkins')) ?>">Select records / view archived</a></p>
<p><?= e($center['address_details']) ?> · <?= e($center['status']) ?> · Occupancy: <?= (int)$center['occupancy'] ?> / <?= (int)$center['capacity'] ?> people</p>
<a href="<?= e(url('resources/views/admin/evacuation/centers.php')) ?>">All centers</a> · <a href="<?= e(url('resources/views/admin/evacuation/centers.php?edit='.$id)) ?>">Edit / close center</a>
<?php if ($center['status']==='open' && $center['occupancy']<$center['capacity']): ?>
<h2 class="mt-4">Check in one person</h2>
<p>Search the household registry and existing walk-ins before creating a new walk-in. Confirm identity with the person or guardian. To transfer centers, check out the previous stay first.</p>
<form method="post">
<label class="d-block mb-3">Record type<select class="form-select" name="kind"><?php foreach (['member'=>'Registered household member','walkin'=>'Existing walk-in','new_walkin'=>'New walk-in'] as $key=>$label): ?><option value="<?= e($key) ?>" <?= input('kind')===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
<label class="d-block mb-3">Registered member<select class="form-select" name="member_id"><option value="">Select for registered member check-in</option><?php foreach ($members as $member): ?><option value="<?= (int)$member['id'] ?>" <?= input('member_id')===(string)$member['id']?'selected':'' ?>><?= e($member['household_code'].' — '.$member['full_name']) ?></option><?php endforeach; ?></select></label>
<label class="d-block mb-3">Existing walk-in<select class="form-select" name="walkin_id"><option value="">Select for returning walk-in</option><?php foreach ($walkins as $person): ?><option value="<?= (int)$person['id'] ?>" <?= input('walkin_id')===(string)$person['id']?'selected':'' ?>><?= e('#'.$person['id'].' — '.$person['full_name'].' — '.$person['address_details'].' — '.$person['contact_phone']) ?></option><?php endforeach; ?></select></label>
<fieldset class="border p-3 mb-3"><legend>New walk-in details only</legend>
<?php foreach (['full_name'=>['Full name',100],'address_details'=>['Home address / purok',255],'contact_phone'=>['Contact number (personal or guardian)',20]] as $key=>$field): ?><label class="d-block mb-2"><?= e($field[0]) ?><input class="form-control" name="<?= e($key) ?>" maxlength="<?= $field[1] ?>" value="<?= e(input($key)) ?>"></label><?php endforeach; ?>
<label>Age in years<input class="form-control" name="age" type="number" min="0" max="130" value="<?= e(input('age')) ?>"></label>
</fieldset><button class="btn btn-primary" name="action" value="checkin">Check in</button></form>
<?php else: ?><p class="alert alert-info mt-3">Check-in unavailable: this center is closed or full.</p><?php endif; ?>
<h2 class="mt-4">Current evacuees and evacuation history</h2>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records"><table class="table"><thead><tr><th>Person</th><th>Record / contact</th><th>Checked in</th><th>Checked out</th><th>Action</th></tr></thead><tbody>
<?php foreach ($records as $record): ?><tr><td><?= e($record['person_name']) ?><?php if ($record['walkin_age']!==null): ?><br>Age: <?= (int)$record['walkin_age'] ?><br><?= e($record['walkin_address']) ?><?php endif; ?></td><td><?= e($record['household_code']??('Walk-in #'.($record['walkin_id']??'legacy'))) ?><br><?= e($record['walkin_contact']) ?></td><td><?= e($record['checked_in_at']) ?><br>By <?= e($record['encoder']) ?></td><td><?= e($record['checked_out_at']??'Currently staying') ?><?php if ($record['checkout_staff']): ?><br>By <?= e($record['checkout_staff']) ?><?php endif; ?></td><td><?php if ($record['checked_out_at']===null): ?><form method="post"><input type="hidden" name="record_id" value="<?= (int)$record['id'] ?>"><button class="btn btn-outline-secondary" name="action" value="checkout">Check out</button></form><?php endif; ?></td></tr><?php endforeach; ?>
<?php if (!$records): ?><tr><td colspan="5">No evacuation records.</td></tr><?php endif; ?></tbody></table></div>
<?php require __DIR__ . '/../../components/footer.php'; ?>
