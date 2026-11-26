<?php
require_once __DIR__ . '/common.php';
session_checker();

function reportDbCreate(array $data, ?array $image): int
{
    db()->beginTransaction();
    try {
        $fields = array_keys($data);
        $stmt = db()->prepare(
            'INSERT INTO incident_reports (' .
                implode(',', $fields) .
                ') VALUES (' .
                implode(',', array_fill(0, count($fields), '?')) .
                ')',
        );
        $stmt->execute(array_values($data));
        $id = (int) db()->lastInsertId();
        if ($image) {
            $stmt = db()->prepare(
                'INSERT INTO incident_images (incident_id,stored_filename,original_filename,mime_type,file_size,uploaded_by) VALUES (?,?,?,?,?,?)',
            );
            $stmt->execute([
                $id,
                $image['stored_filename'],
                $image['original_filename'],
                $image['mime_type'],
                $image['file_size'],
                $data['reported_by'],
            ]);
        }
        db()->commit();
        return $id;
    } catch (Throwable $error) {
        db()->rollBack();
        throw $error;
    }
}

function reportValues(): array
{
    $data = [];
    foreach (
        [
            'barangay',
            'incident_type',
            'description',
            'reporter_name',
            'contact_phone',
            'location_description',
            'latitude',
            'longitude',
            'report_source',
        ]
        as $key
    ) {
        $data[$key] = input($key);
    }
    return $data;
}

function reportUpload(): ?array
{
    if (!isset($_FILES['image'])) {
        return null;
    }
    $file = $_FILES['image'];
    if (!is_array($file) || !isset($file['error']) || !is_int($file['error'])) {
        throw new InvalidArgumentException('Invalid image upload.');
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException(
            'Image upload failed or exceeded the server limit. Choose the image again.',
        );
    }
    if (!is_string($file['tmp_name'] ?? null) || !is_uploaded_file($file['tmp_name'])) {
        throw new InvalidArgumentException('Invalid image upload.');
    }
    $size = filesize($file['tmp_name']);
    if (!$size || $size > 5242880) {
        throw new InvalidArgumentException('Image must be between 1 byte and 5 MiB.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $info = @getimagesize($file['tmp_name']);
    if (!isset($types[$mime]) || !$info || $info['mime'] !== $mime) {
        throw new InvalidArgumentException('Choose a valid JPEG, PNG, or WebP image.');
    }
    $name = bin2hex(random_bytes(24)) . '.' . $types[$mime];
    $path = reportStorage() . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $path)) {
        throw new RuntimeException('Could not store image.');
    }
    chmod($path, 0600);
    return [
        'stored_filename' => $name,
        'original_filename' => substr(basename((string) $file['name']), 0, 255),
        'mime_type' => $mime,
        'file_size' => $size,
        'path' => $path,
    ];
}

function reportCreate(array $data): array
{
    if (!isPost()) {
        return [];
    }
    if (input('action') !== 'create_incident') {
        return ['The form was incomplete or exceeded the server upload limit. Please try again.'];
    }
    $errors = [];
    if ($data['barangay'] !== '' && !in_array($data['barangay'], ATOK_BARANGAYS, true)) {
        $errors[] = 'Choose an Atok barangay or leave it awaiting confirmation.';
    }
    if ($data['barangay'] === '') {
        $data['barangay'] = null;
    }
    foreach (
        [
            'description' => 10000,
            'reporter_name' => 100,
            'contact_phone' => 20,
            'location_description' => 255,
        ]
        as $key => $max
    ) {
        if ($data[$key] === '' || mb_strlen($data[$key]) > $max) {
            $errors[] = ucfirst(str_replace('_', ' ', $key)) . " is required (maximum $max characters).";
        }
    }
    if (!isset(REPORT_TYPES[$data['incident_type']])) {
        $errors[] = 'Choose an incident type.';
    }
    if ($data['contact_phone'] !== '' && !validPhone($data['contact_phone'])) {
        $errors[] = 'Use 09 followed by 9 digits or +639 followed by 9 digits.';
    }
    $errors = array_merge($errors, locationErrors($data['location_description'], $data['latitude'], $data['longitude']));
    $admin = $_SESSION['ROLE'] === 'admin';
    if ($admin && !in_array($data['report_source'], ['walk_in', 'phone', 'admin'], true)) {
        $errors[] = 'Choose how the report was received.';
    }
    if ($errors) {
        return $errors;
    }
    $data['report_source'] = $admin ? $data['report_source'] : 'resident';
    foreach (['latitude', 'longitude'] as $key) {
        $data[$key] = $data[$key] === '' ? null : $data[$key];
    }
    $data['reported_by'] = (int) $_SESSION['ID'];
    $image = null;
    try {
        $image = reportUpload();
        $id = reportDbCreate($data, $image);
    } catch (Throwable $error) {
        if ($image && is_file($image['path'])) {
            unlink($image['path']);
        }
        return [
            $error instanceof InvalidArgumentException
                ? $error->getMessage()
                : 'Report could not be saved. Please try again; reselect your image.',
        ];
    }
    flash('Incident submitted for staff review.');
    redirect('incidents/view.php?id=' . $id);
    return [];
}

$data = reportValues();
if (!isPost() && $_SESSION['ROLE'] === 'resident') {
    $data["contact_phone"] = $_SESSION["PHONE"] ?? "";
    $data["reporter_name"] = $_SESSION["FULL_NAME"] ?? "";
}
$errors = reportCreate($data);
$pageTitle = "Report incident";
require __DIR__ . "/../includes/header.php";
require __DIR__ . "/../includes/errors.php";
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url('incidents/list.php')) ?>">Back to reports</a></p>
<h1>Report an incident</h1>
<p>Reports are reviewed by barangay staff. This website does not guarantee an immediate emergency response.</p>
<form id="incident-form" method="post" enctype="multipart/form-data">
    <?php if ($_SESSION['ROLE'] === 'admin'): ?>
        <label class="d-block mb-3">Report received through <select name="report_source" class="form-select" required>
                <?php foreach (['walk_in' => 'In person','phone' => 'Phone','admin' => 'Staff observation'] as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $data['report_source'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select></label>
    <?php endif; ?>
    <?php $selectedBarangay = $data['barangay']; require __DIR__ . '/barangay-field.php'; ?>
    <label class="d-block mb-3">Incident type <select name="incident_type" class="form-select" required>
            <option value="">Choose type</option>
            <?php foreach (REPORT_TYPES as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= $data['incident_type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select></label>
    <?php foreach (['reporter_name' => ['Reporter/contact name',100], 'contact_phone' => ['Contact number',13]] as $key => $field): ?>
        <label class="d-block mb-3"><?= e($field[0]) ?><input class="form-control" name="<?= e($key) ?>" maxlength="<?= $field[1] ?>" value="<?= e($data[$key]) ?>" required /></label>
    <?php endforeach; ?>
    <label class="d-block mb-3">Describe what happened<textarea class="form-control" name="description" maxlength="10000" required >
<?= e($data['description']) ?></textarea> </label>
    <?php
    $pickerKind = 'incidents';
    $point = $data;
    require __DIR__ . "/../includes/location-picker.php";
    ?>
    <label class="d-block mb-3">Incident image (optional; JPEG, PNG, WebP; maximum 5 MiB) <input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp" /></label>
    <p class="alert alert-warning">If a form error occurs, choose the image again before submitting.</p>
    <button class="btn btn-primary" name="action" value="create_incident">Submit report</button>
    <a href="<?= e(url('incidents/list.php')) ?>">Cancel and return to reports</a>
</form>
<script>
    document.getElementById('incident-form').addEventListener('submit', function (event) {
        for (const field of this.querySelectorAll('[required]')) {
            if (!field.value.trim()) {
                event.preventDefault();
                field.setCustomValidity('Please complete this field.');
                field.reportValidity();
                field.setCustomValidity('');
                break;
            }
        }
    });
</script>
<?php
require __DIR__ . "/../includes/footer.php";
?>
