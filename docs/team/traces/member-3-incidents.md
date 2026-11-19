# Member 3 · Save a resident’s incident

Open the create form as a resident. Initial contact values come from the authenticated account. On a failed submission the form retains the report-specific edits. ReportController::create validates report and optional image data. ReportModel::create persists the incident and the image reference when supplied. The submitted name, email and phone describe this incident; reported_by comes from the signed-in account. The detail lookup restricts a resident by owner ID. Show the distinction between a contact value and an ownership decision. [Form](../../../resources/views/incidents/create.php) · [Controller](../../../app/controllers/ReportController.php) · [Model](../../../app/models/Report.php) · [Result and print](../../../resources/views/incidents/view.php)

## Initialize contact fields and process the form

[resources/views/incidents/create.php, lines 2–9](../../../resources/views/incidents/create.php#L2)

```php
require_once __DIR__ . "/../../../app/controllers/ReportController.php";
session_checker();
$controller = new ReportController($_SESSION["ROLE"]);
$staff = $_SESSION['ROLE'] === 'admin';
$assisted = $staff && ($_GET['intake'] ?? '') === 'assisted';
$data = $controller->values($assisted);
$errors = $controller->create($data, $assisted);
$pageTitle = "Report incident";
```

## Take ownership from the session and pass submitted values

[app/controllers/ReportController.php, lines 248–263](../../../app/controllers/ReportController.php#L248)

```php
        $data['barangay'] = $barangay === '' ? null : $barangay;
        $data['reported_by'] = (int) $_SESSION['ID'];
        $image = null;
        try {
            $image = $this->upload();
            $id = $this->model->create($data, $image);
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
```

## Begin saving the incident and its contact snapshot

[app/models/Report.php, lines 92–125](../../../app/models/Report.php#L92)

```php
    public function create(array $data, ?array $image): int
    {
        $this->pdo->beginTransaction();
        try {
            $fields = array_keys($data);
            $stmt = $this->pdo->prepare(
                'INSERT INTO incident_reports (' .
                    implode(',', $fields) .
                    ') VALUES (' .
                    implode(',', array_fill(0, count($fields), '?')) .
                    ')',
            );
            $stmt->execute(array_values($data));
            $id = (int) $this->pdo->lastInsertId();
            if ($image) {
                $stmt = $this->pdo->prepare(
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
            $this->pdo->commit();
            return $id;
        } catch (Throwable $error) {
            $this->pdo->rollBack();
            throw $error;
        }
    }
```

## Demonstrate it

Change a fictional callback number before submitting. Reopen the incident as its owner and point out the saved contact.
