<label class="d-block mb-3">Barangay
    <select class="form-select" name="barangay" <?= $barangayPrompt === 'Choose barangay' ? 'required' : '' ?>>
        <option value=""><?= e($barangayPrompt) ?></option>
        <?php foreach (ATOK_BARANGAYS as $name): ?>
            <option value="<?= e($name) ?>" <?= $selectedBarangay === $name ? 'selected' : '' ?>><?= e($name) ?></option>
        <?php endforeach; ?>
    </select>
</label>
