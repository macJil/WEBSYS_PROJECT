> Current behavior update: team statuses now determine the incident status; all assignments must be completed to resolve it. History can be hidden without deleting stored records. See [response/history update](../RESPONSE_HISTORY_UPDATE.md). Code excerpts below retain their original snapshot.

# Member 2 — Incident submission, image storage and response

These are study notes for the pre-formatting source at commit 954885b, not application changes or a claim that a live demonstration has passed. Use fictional examples. The complete primary request is traced below, with exact source excerpts and explanations. Source links are relative to the repository and include line anchors. The main presentation should show only the marked 60–90 second segment; use the rest to prepare for questions.

All PHP save paths run on the server through MAMP. The browser sends form values, not PHP objects. A successful POST redirects to a new GET; validation errors remain on the form. `[]` means an empty array, `$this` means the current object, `->` accesses an object, `?int` accepts integer or null, and `: array` declares a returned array. `prepare()` creates a SQL statement and `execute()` supplies its values.
## Focus and example

Own resident/staff reports, private images and response teams/actions. Present the incident save trace at slide 12, then the response feature on slide 13. Example: a signed-in resident submits a fictional flooding report with a written landmark, optional coordinates and a small PNG.

### Complete execution order

GET form; check session; construct controller; collect fields; return without saving. POST multipart form; validate; set reporter from the session; check/store optional image; transaction inserts incident and image reference; commit; redirect; detail query rechecks ownership. A failed SQL transaction also triggers cleanup of the newly moved image file.

## 1. The multipart form and page entry

Source: [resources/views/incidents/create.php, lines 1–20](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/resources/views/incidents/create.php#L1). Exact excerpt, not pseudocode.

```php
<?php
require_once __DIR__ . '/../../../app/controllers/ReportController.php';
session_checker();
$controller = new ReportController($_SESSION['ROLE']);
$data = $controller->values();
if (!isPost()) {
    $data['reporter_name'] = $_SESSION['FULL_NAME'] ?? '';
}
$errors = $controller->create($data);
$pageTitle = 'Report incident';
require __DIR__ . '/../components/header.php';
require __DIR__ . '/../components/errors.php';
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/incidents/list.php')) ?>">Back to reports</a></p>
<h1>Report an incident</h1>
<p
    >Reports are reviewed by barangay staff. This website does not guarantee an immediate emergency
    response.</p
>
<form method="post" enctype="multipart/form-data">
```

The controller uses the current session role, not a posted role. On GET the reporter name is prefilled. `$data` contains form values and `$errors` contains returned validation messages. The `multipart/form-data` encoding allows file bytes to reach PHP's `$_FILES`; ordinary fields arrive in `$_POST`. Calls run before the shared header is included.

## 2. Read the allowed values

Source: [app/controllers/ReportController.php, lines 68–75](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/app/controllers/ReportController.php#L68). Exact excerpt, not pseudocode.

```php
    public function values(): array
    {
        $data = [];
        foreach (['incident_type','description','reporter_name','contact_phone','location_description','latitude','longitude','report_source'] as $key) {
            $data[$key] = input($key);
        }
        return $data;
    }
```

This list establishes the expected report fields. The staff source selector is visible only to staff, but the server also checks it. `reported_by` is not taken from this form; `create()` sets it from the signed-in account.

## 3. Validate and coordinate the save

Source: [app/controllers/ReportController.php, lines 133–185](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/app/controllers/ReportController.php#L133). Exact excerpt, not pseudocode.

```php
    public function create(array $data): array
    {
        if (!isPost()) {
            return [];
        }
        if (input('action') !== 'create_incident') {
            return ['The form was incomplete or exceeded the server upload limit. Please try again.'];
        }
        $errors = [];
        foreach (['description' => 10000,'reporter_name' => 100,'contact_phone' => 20,'location_description' => 255] as $key => $max) {
            if ($data[$key] === '' || strlen($data[$key]) > $max) {
                $errors[] = ucfirst(str_replace('_', ' ', $key)) . " is required (maximum $max bytes).";
            }
        }
        if (!isset(self::TYPES[$data['incident_type']])) {
            $errors[] = 'Choose an incident type.';
        }
        if ($data['contact_phone'] !== '' && !validPhone($data['contact_phone'], 3)) {
            $errors[] = 'Enter a valid contact number.';
        }
        foreach (['latitude' => 90,'longitude' => 180] as $key => $limit) {
            if ($data[$key] !== '' && (!is_numeric($data[$key]) || !is_finite((float)$data[$key]) || abs((float)$data[$key]) > $limit)) {
                $errors[] = 'Enter valid coordinates.';
            }
        }
        if (($data['latitude'] === '') !== ($data['longitude'] === '')) {
            $errors[] = 'Provide both coordinates, or leave both blank.';
        }
        $admin = $_SESSION['ROLE'] === 'admin';
        if ($admin && !in_array($data['report_source'], ['walk_in','phone','admin'], true)) {
            $errors[] = 'Choose how the report was received.';
        }
        if ($errors) {
            return $errors;
        }
        $data['report_source'] = $admin ? $data['report_source'] : 'resident';
        foreach (['latitude','longitude'] as $key) {
            $data[$key] = $data[$key] === '' ? null : $data[$key];
        }
        $data['reported_by'] = (int) $_SESSION['ID'];
        $image = null;
        try {
            $image = $this->upload();
            $id = $this->model->create($data, $image);
        } catch (Throwable $error) {
            if ($image && is_file($image['path'])) {
                unlink($image['path']);
            }
            return [$error instanceof InvalidArgumentException ? $error->getMessage() : 'Report could not be saved. Please try again; reselect your image.'];
        }
        $this->success('Incident submitted for staff review.', 'resources/views/incidents/view.php?id=' . $id);
        return [];
    }
```

The method checks action, required strings, type, phone and paired coordinates. Maximum lengths here are bytes. Staff must use a known source; residents are forced to source `resident`. Empty coordinate fields become null. `$data['reported_by']` is the session account ID.

`$image` starts null. Upload may return metadata or no image. The model returns the incident ID on success. The catch removes a file already moved by this request if the database operation failed. A database rollback cannot undo a filesystem move, so this explicit cleanup matters. A validation/upload error does not reach the successful redirect. A valid report redirects to its private detail.

## 4. Validate the image itself

Source: [app/controllers/ReportController.php, lines 97–132](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/app/controllers/ReportController.php#L97). Exact excerpt, not pseudocode.

```php
    private function upload(): ?array
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
            throw new InvalidArgumentException('Image upload failed or exceeded the server limit. Choose the image again.');
        }
        if (!is_string($file['tmp_name'] ?? null) || !is_uploaded_file($file['tmp_name'])) {
            throw new InvalidArgumentException('Invalid image upload.');
        }
        $size = filesize($file['tmp_name']);
        if (!$size || $size > 5242880) {
            throw new InvalidArgumentException('Image must be between 1 byte and 5 MiB.');
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $types = ['image/jpeg' => 'jpg','image/png' => 'png','image/webp' => 'webp'];
        $info = @getimagesize($file['tmp_name']);
        if (!isset($types[$mime]) || !$info || $info['mime'] !== $mime) {
            throw new InvalidArgumentException('Choose a valid JPEG, PNG, or WebP image.');
        }
        $name = bin2hex(random_bytes(24)) . '.' . $types[$mime];
        $path = $this->storage() . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $path)) {
            throw new RuntimeException('Could not store image.');
        }
        chmod($path, 0600);
        return ['stored_filename' => $name,'original_filename' => substr(basename((string) $file['name']), 0, 255),'mime_type' => $mime,'file_size' => $size,'path' => $path];
    }
```

No supplied file returns null. Other upload errors, a fake uploaded-file path, empty/over-5-MiB content, disallowed MIME or invalid image structure cause exceptions. The checks use actual temporary-file bytes rather than trusting the filename extension. A random 48-character hexadecimal basename avoids using a user-controlled path. `move_uploaded_file()` writes to private storage and the returned metadata includes that new path for possible cleanup. The original name is metadata only. The PHP/web-server upload limit may reject a request before this method receives a usable file.

## 5. Private storage location

Source: [app/controllers/ReportController.php, lines 76–96](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/app/controllers/ReportController.php#L76). Exact excerpt, not pseudocode.

```php
    private function storage(): string
    {
        global $settings;
        $path = $settings['incident_upload_dir'] ?? '';
        $root = realpath(__DIR__ . '/../..');
        if ($path === '' || $path[0] !== '/') {
            throw new RuntimeException('Configure an absolute private upload directory.');
        }
        if (!is_dir($path) && !mkdir($path, 0700, true)) {
            throw new RuntimeException('Cannot create upload directory.');
        }
        $real = realpath($path);
        if (!$real || $real === $root || str_starts_with($real, $root . DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Uploads must be outside the website directory.');
        }
        $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
        if ($documentRoot && ($real === $documentRoot || str_starts_with($real, $documentRoot . DIRECTORY_SEPARATOR))) {
            throw new RuntimeException('Uploads must be outside the web server document root.');
        }
        return $real;
    }
```

The configured path must be absolute and outside both the project and document root. The folder can be created with restrictive permissions. It must still be writable by the server process. This is why a valid image can fail when the local upload configuration is wrong. Do not expose the local configuration during the presentation.

## 6. Save the incident and optional image reference

Source: [app/models/Report.php, lines 62–80](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/app/models/Report.php#L62). Exact excerpt, not pseudocode.

```php
    public function create(array $data, ?array $image): int
    {
        $this->pdo->beginTransaction();
        try {
            $fields = array_keys($data);
            $stmt = $this->pdo->prepare('INSERT INTO incident_reports (' . implode(',', $fields) . ') VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')');
            $stmt->execute(array_values($data));
            $id = (int) $this->pdo->lastInsertId();
            if ($image) {
                $stmt = $this->pdo->prepare('INSERT INTO incident_images (incident_id,stored_filename,original_filename,mime_type,file_size,uploaded_by) VALUES (?,?,?,?,?,?)');
                $stmt->execute([$id, $image['stored_filename'], $image['original_filename'], $image['mime_type'], $image['file_size'], $data['reported_by']]);
            }
            $this->pdo->commit();
            return $id;
        } catch (Throwable $error) {
            $this->pdo->rollBack();
            throw $error;
        }
    }
```

The transaction begins before the incident insert. `$data` comes from the controller's known fields; this model does not independently allowlist arbitrary column keys. Preserve that caller contract. `lastInsertId()` supplies the incident ID for the image row. The image bytes are not stored in MySQL. The incident's initial `pending` status comes from the schema default because this insert does not set status. Commit completes both related writes. On error, rollback removes database writes and rethrows to the controller for file cleanup.

## 7. Ownership on the detail reload

Source: [app/controllers/ReportController.php, lines 14–17](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/app/controllers/ReportController.php#L14). Exact excerpt, not pseudocode.

```php
    private function owner(): ?int
    {
        return $_SESSION['ROLE'] === 'admin' ? null : (int) $_SESSION['ID'];
    }
```

For admins, null means staff-wide access in the model's ownership condition. For residents, the current session ID limits private results. A posted resident ID does not replace this scope.

## 8. Query the saved private report

Source: [app/models/Report.php, lines 38–43](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/app/models/Report.php#L38). Exact excerpt, not pseudocode.

```php
    public function find(int $id, ?int $owner)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM incident_reports WHERE id = ?' . ($owner !== null ? ' AND reported_by = ?' : ''));
        $stmt->execute($owner !== null ? [$id, $owner] : [$id]);
        return $stmt->fetch();
    }
```

The resident lookup requires both incident ID and matching `reported_by`. Another user's ID produces no row; `getIncident()` returns a not-found response. Admins omit the ownership clause after role/session checks. Image access uses a similar owner condition through a join. A community map marker does not grant permission to this private detail.
## Response trace to explain after submission

1. Staff submit review notes/status on `incidents/view.php`. `ReportController::review()` checks admin role, action, status and notes.
2. `ReportModel::review()` locks the incident, checks active assignments and saves review fields with a `response_updates` history entry. Active assignments prevent moving the incident out of responding until completed/cancelled.
3. `admin/response/incident.php` uses `ResponseController::record()` to validate assignment/action input.
4. `ResponseModel::record()` locks and checks the incident and team/assignment, writes assignment state and appends the action in one transaction. A report must be verified/responding for assignment.
5. Assigned/on_scene changes the incident to responding. Completing an assignment does not automatically resolve the overall incident.
6. Redirect reloads assignments and history. Earlier actions remain separate rows.

## Code speech (about 75 seconds)

“This page accepts an ordinary multipart form so PHP receives both the fields and the uploaded file. The controller checks the incident type, written details and coordinate pair. It assigns the reporter ID from the session. The upload method checks the file's real content and gives it a generated private filename. In the model, one transaction saves the incident and its image reference using the new incident ID. If saving fails, the database rolls back and the controller removes the newly stored file. On success, the redirect opens the detail page. That query also checks the resident's ownership, so knowing another report ID does not grant access to its private information.”

## Common questions

The image is optional, but image-upload support is implemented. File validation cannot guarantee that an image depicts a truthful event; staff still review the report. Pending reports do not appear on the shared map. SQL rollback does not delete files by itself. Shared map eligibility is a separate read rule, not a second incident save.
