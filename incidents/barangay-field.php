<?php if (!isset($selectedBarangay)) { return; } ?>
<label class="d-block mb-3">Incident barangay
    <select class="form-select" name="barangay">
        <option value="">Awaiting confirmation</option>
        <?php foreach (ATOK_BARANGAYS as $area): ?>
            <option <?= $selectedBarangay === $area ? 'selected' : '' ?>><?= e($area) ?></option>
        <?php endforeach; ?>
    </select>
</label>
