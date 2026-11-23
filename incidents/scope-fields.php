<?php if (!isset($scope)) { return; } ?>
<label class="col-md-3">Submission period
    <select class="form-select" name="period">
        <?php foreach (INCIDENT_PERIODS as $key => $label): ?>
            <option value="<?= e($key) ?>" <?= $scope['period'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>
</label>
<label class="col-md-3">Incident barangay
    <select class="form-select" name="barangay">
        <option value="">All barangays</option>
        <option value="unknown" <?= $scope['barangay'] === 'unknown' ? 'selected' : '' ?>>Awaiting confirmation</option>
        <?php foreach (ATOK_BARANGAYS as $area): ?>
            <option <?= $scope['barangay'] === $area ? 'selected' : '' ?>><?= e($area) ?></option>
        <?php endforeach; ?>
    </select>
</label>
