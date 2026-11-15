# Member 1 — Household save and shared PHP foundations

These are study notes for the current source, not application changes or a claim that a live demonstration has passed. Use fictional examples. The complete primary request is traced below, with exact source excerpts and explanations. Source links are relative to the repository and include line anchors. The main presentation should show only the marked 60–90 second segment; use the rest to prepare for questions.

All PHP save paths run on the server through MAMP. The browser sends form values, not PHP objects. A successful POST redirects to a new GET; validation errors remain on the form. `[]` means an empty array, `$this` means the current object, `->` accesses an object, `?int` accepts integer or null, and `: array` declares a returned array. `prepare()` creates a SQL statement and `execute()` supplies its values.
## Focus and example

Own login/session foundations and household/member CRUD. Present the household save on slide 8. Open the page, controller and model together. Example: staff create fictional household `DEMO-HH-A`, with a representative and purok, no map point and assistance category `unassessed`. A null `$id` means create; `?edit=12` means edit household 12. An ID in this guide is illustrative, never assumed to exist.

### Complete execution order

1. GET opens the household page and constructs its controller.
2. Constructor checks admin access and creates the model, which gets PDO.
3. GET uses defaults or loads the edit record; the save method returns immediately on GET.
4. POST collects known fields and validates the action, fields and coordinates.
5. Model inserts or updates with bound values and returns the household ID.
6. Shared success method stores a flash message and redirects.
7. A new GET of household.php loads the record and members; escaped markup displays them.


## 1. Page entry chooses create/edit and collects the request

Source: [resources/views/admin/households.php, lines 1–15](../../../resources/views/admin/households.php#L1). Exact excerpt, not pseudocode.

```php
<?php
require_once __DIR__ . '/../../../app/controllers/HouseholdController.php';
$controller = new HouseholdController();
$id = isset($_GET['edit']) ? $controller->recordId($_GET['edit']) : null;
$form = $id === null ? ['household_code' => '','representative_name' => '','contact_phone' => '','purok' => '','address_details' => '','registry_reason' => 'monitored','hazard_type' => 'unassessed','hazard_notes' => '','vulnerability_category' => 'unassessed','vulnerability_notes' => '','evacuation_status' => 'at_home'] : $controller->getHousehold($id);
if (isPost()) {
    $form = $controller->householdInput();
}
$errors = $controller->saveHousehold($id, $form);
$search = is_string($_GET['q'] ?? '') ? trim($_GET['q'] ?? '') : '';
$rows = $controller->listHouseholds(substr($search, 0, 255));
$pageTitle = 'Household registry';
require __DIR__ . '/../components/header.php';
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
```

`require_once` loads the controller class. `new` constructs it and therefore runs its access guard. `$id` comes from the validated edit query value, not from a hidden role field. `$form` starts with defaults or the existing household. On POST, `householdInput()` replaces it with posted values. `saveHousehold()` runs before header output, which permits an HTTP redirect. `$rows` are the list results, not the record being edited. The form has `create_household` or `update_household` as its action. If validation fails, `$errors` reaches the errors component while `$form` retains submitted values.

## 2. Guard and model construction

Source: [app/controllers/HouseholdController.php, lines 11–15](../../../app/controllers/HouseholdController.php#L11). Exact excerpt, not pseudocode.

```php
    public function __construct()
    {
        requireRole('admin');
        $this->households = new HouseholdModel();
    }
```

The constructor requires an admin session before this controller exposes household operations. `HouseholdModel` stores the PDO connection as an object property. Loading a class does not automatically run a query; the called method determines the operation.

## 3. Collect only known form keys

Source: [app/controllers/HouseholdController.php, lines 51–58](../../../app/controllers/HouseholdController.php#L51). Exact excerpt, not pseudocode.

```php
    public function householdInput(): array
    {
        $data = [];
        foreach (['household_code','representative_name','contact_phone','purok','address_details','registry_reason','hazard_type','hazard_notes','vulnerability_category','vulnerability_notes','evacuation_status','latitude','longitude'] as $field) {
            $data[$field] = input($field);
        }
        return $data;
    }
```

Each key names a supported input. `input()` returns a posted string or an empty string and normally trims whitespace. The browser cannot add an arbitrary database column through this field list. Classification values are checked against the controller constants later.

## 4. Validate and normalize before saving

Source: [app/controllers/HouseholdController.php, lines 59–109](../../../app/controllers/HouseholdController.php#L59). Exact excerpt, not pseudocode.

```php
    public function saveHousehold(?int $id, array $data): array
    {
        if (!isPost()) {
            return [];
        }
        if (input('action') !== ($id === null ? 'create_household' : 'update_household')) {
            return ['Invalid form action.'];
        }
        $old = $id === null ? null : $this->getHousehold($id);
        $errors = locationErrors($data['address_details'], $data['latitude'], $data['longitude']);
        foreach (['household_code' => 30,'representative_name' => 100,'purok' => 100] as $field => $max) {
            if ($data[$field] === '' || strlen($data[$field]) > $max) {
                $errors[] = str_replace('_', ' ', ucfirst($field)) . ' is required and must fit within ' . $max . ' bytes.';
            }
        }
        foreach (['address_details','hazard_notes','vulnerability_notes'] as $field) {
            if (strlen($data[$field]) > 255) {
                $errors[] = str_replace('_', ' ', ucfirst($field)) . ' must fit within 255 bytes.';
            }
        }
        if ($data['contact_phone'] !== '' && !validPhone($data['contact_phone'], 7)) {
            $errors[] = 'Enter a valid emergency contact number (7–20 characters), or leave it blank.';
        }
        foreach (['registry_reason' => self::REASONS,'hazard_type' => self::HAZARDS,'vulnerability_category' => self::VULNERABILITY,'evacuation_status' => self::EVACUATION] as $field => $options) {
            if (!array_key_exists($data[$field], $options)) {
                $errors[] = 'Choose a valid ' . str_replace('_', ' ', $field) . '.';
            }
        }
        if ($data['vulnerability_category'] !== 'unassessed' && $data['vulnerability_notes'] === '') {
            $errors[] = 'Record the assessment source and practical assistance notes.';
        }
        if ($errors) {
            return $errors;
        }
        foreach (['latitude','longitude'] as $field) {
            $data[$field] = $data[$field] === ''?null:$data[$field];
        }
        $assessed = $data['vulnerability_category'] !== 'unassessed';
        $changed = !$old || $data['vulnerability_category'] !== $old['vulnerability_category'] || $data['vulnerability_notes'] !== ($old['vulnerability_notes'] ?? '');
        $data['vulnerability_notes'] = $assessed ? $data['vulnerability_notes'] : null;
        $data['vulnerability_assessed_by'] = $assessed ? ($changed ? $_SESSION['ID'] : $old['vulnerability_assessed_by']) : null;
        $data['vulnerability_assessed_at'] = $assessed ? ($changed ? date('Y-m-d H:i:s') : $old['vulnerability_assessed_at']) : null;
        try {
            $savedId = $this->households->save($data, $id);
            $this->success('Household saved.', 'resources/views/admin/household.php?id=' . $savedId);
        } catch (DomainException $error) {
            return [$error->getMessage()];
        } catch (PDOException $error) {
            return [$error->getCode() === '23000' ? 'That household code is already used, or the record could not be linked. Choose a unique code.' : 'The household could not be saved. Please try again.'];
        }
    }
```

Read this method in order:

- GET returns an empty error list. POST must carry the action matching create or edit.
- Editing loads the old record, which is needed to preserve unchanged assessment metadata.
- `locationErrors()` requires both coordinates or neither, numeric ranges, and a description when a point exists. Required strings and maximum byte lengths are checked separately.
- Phone validation is conditional because this contact can be blank. The selected categories must be keys in the fixed option arrays. A recorded assessment needs explanatory notes.
- A nonempty `$errors` array returns before the model save. Blank coordinates become SQL null rather than empty text.
- `$assessed` and `$changed` decide whether staff/time assessment fields should be recorded, retained or cleared. These do not calculate hazard risk.
- The returned `$savedId` builds the destination URL. Business and database errors become user-facing messages rather than raw SQL.

An invalid category or one coordinate alone produces no household insert. A duplicate household code can fail at the database constraint and returns the conflict message.

## 5. PDO connection

Source: [config/db.php, lines 1–21](../../../config/db.php#L1). Exact excerpt, not pseudocode.

```php
<?php
$databaseConnection = null;

function db(): PDO
{
    global $databaseConnection;
    if ($databaseConnection === null) {
        if (!is_file(__DIR__ . '/config.php')) {
            http_response_code(503);
            exit('Setup required: copy config/config.example.php to config/config.php and enter your local settings.');
        }
        $settings = require __DIR__ . '/config.php';
        $dsn = "mysql:host={$settings['host']};port={$settings['port']};dbname={$settings['database']};charset=utf8mb4";
        $databaseConnection = new PDO($dsn, $settings['username'], $settings['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $databaseConnection;
}
```

`$databaseConnection` is reused within the PHP request. The ignored local configuration supplies credentials; never project it. The DSN selects MySQL and UTF-8. PDO exception mode makes failures catchable. Associative fetch mode returns fields by name. Models receive a PDO object or an exception, not an HTML error string. This is a small procedural helper used by ordinary model classes.

## 6. Insert or update the household

Source: [app/models/Household.php, lines 28–48](../../../app/models/Household.php#L28). Exact excerpt, not pseudocode.

```php
    public function save(array $data, ?int $id): int
    {
        if ($id !== null && !empty($this->findById($id)['archived_at'])) {
            throw new DomainException('Archived households cannot be edited.');
        }
        $fields = ['household_code','representative_name','contact_phone','purok','address_details','registry_reason','hazard_type','hazard_notes','vulnerability_category','vulnerability_notes','vulnerability_assessed_by','vulnerability_assessed_at','evacuation_status','latitude','longitude'];
        $values = [];
        $assignments = [];
        foreach ($fields as $field) {
            $values[] = $data[$field];
            $assignments[] = $field . '=?';
        }
        if ($id === null) {
            $stmt = $this->pdo->prepare('INSERT INTO households (' . implode(',', $fields) . ') VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')');
        } else {
            $stmt = $this->pdo->prepare('UPDATE households SET ' . implode(',', $assignments) . ' WHERE id=?');
            $values[] = $id;
        }
        $stmt->execute($values);
        return $id ?? (int) $this->pdo->lastInsertId();
    }
```

`$fields` is a fixed server-defined column list. `$values` is filled in exactly that order. `$assignments` becomes `column=?` entries for an update. A create builds one placeholder per value; an update adds its ID as the final bound value. The SQL identifiers come from this local list, while user values go through `execute()`. The method returns the existing ID or MySQL's newly inserted ID. Editing an archived household throws before writing. This operation changes one row and does not need a multi-write transaction of its own.

## 7. Success message and redirect

Source: [app/controllers/Controller.php, lines 5–9](../../../app/controllers/Controller.php#L5). Exact excerpt, not pseudocode.

```php
    protected function success(string $message, string $path): void
    {
        flash($message);
        redirect($path);
    }
```

`success()` calls the shared `flash()` and `redirect()` helpers. `flash()` stores a one-use session notice. `redirect()` builds a BASE_URL destination, sends HTTP 303 and exits, so PHP does not continue rendering the POST page. The browser then performs a GET of the household detail page. This reduces accidental resubmission on refresh; it is not a guarantee against double-clicked requests.

## 8. Reload the household and its calculated count

Source: [app/models/Household.php, lines 22–27](../../../app/models/Household.php#L22). Exact excerpt, not pseudocode.

```php
    public function findById(int $id)
    {
        $stmt = $this->pdo->prepare('SELECT h.*, (SELECT COUNT(*) FROM household_members m WHERE m.household_id=h.id AND m.archived_at IS NULL) AS member_count FROM households h WHERE h.id=?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
```

The main query selects household 12, for example. Its subquery counts only non-archived member rows. It does not read a duplicate household-size column. The model returns one associative row; the controller turns a missing result into a not-found response. The detail view separately loads members and escapes displayed names and fields.
## Supporting ownership: login and member editing

- Login: `index.php` calls `UserController::handlePost()`, which validates credentials and uses `UserModel::findByEmail()`. `password_verify()` checks the stored hash. A successful login regenerates the session ID, stores identity/role and redirects to the correct dashboard. Registration uses `password_hash()` and `createResident()`; public input does not grant admin access.
- Member save: `household.php` collects the member form and calls `saveMember()`. The controller verifies household and member association, checks name/age/notes, then `HouseholdModel::saveMember()` inserts or updates. The redirect reloads the household, whose member count is queried again.
- Shared output: `e()` escapes text at display time. It does not validate a business rule or prepare SQL. `url()` builds browser destinations; `__DIR__` builds server filesystem paths.

## Code speech (about 75 seconds)

“Here is the household page. Its opening PHP creates the controller and calls the save method before displaying the form. When the request is only a GET, the save method returns without writing. When staff submit, the controller reads known fields and checks the form action, required information and location pair. Here is the model: it prepares an insert or update and supplies the values separately. It returns the household ID. The controller then stores a message and redirects to the detail page, where the saved record is loaded again. This shows our procedural page setup working with controller and model objects. The member count comes from member records, so saving a household does not create a separate size value.”

## Questions to be ready for

Why prepare SQL? Values are bound separately from the query structure. Why check roles before rendering? A hidden menu does not prevent direct URL access. Why no stored size? The active members are the source for the count. What if the database fails? The request reports an error; do not claim a record was saved.
