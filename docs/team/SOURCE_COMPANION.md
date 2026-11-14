# Complete maintained-source companion

Snapshot of the working application, 2026-11-14. Read CODE_WALKTHROUGH.md for explanations by module and method. This is source reference, not another runnable application. Third-party libraries, private config, images and historical migrations are intentionally not duplicated.

## File index

- [index.php](#file-1)
- [config/config.example.php](#file-2)
- [config/db.php](#file-3)
- [app/controllers/AnnouncementController.php](#file-4)
- [app/controllers/Controller.php](#file-5)
- [app/controllers/DashboardController.php](#file-6)
- [app/controllers/DisasterSummaryController.php](#file-7)
- [app/controllers/EvacuationController.php](#file-8)
- [app/controllers/HouseholdController.php](#file-9)
- [app/controllers/MapController.php](#file-10)
- [app/controllers/RecordActionsController.php](#file-11)
- [app/controllers/ReliefController.php](#file-12)
- [app/controllers/ReliefLocationController.php](#file-13)
- [app/controllers/ReportController.php](#file-14)
- [app/controllers/ResponseController.php](#file-15)
- [app/controllers/UserController.php](#file-16)
- [app/evacuation-capacity.php](#file-17)
- [app/helpers.php](#file-18)
- [app/models/Announcement.php](#file-19)
- [app/models/Dashboard.php](#file-20)
- [app/models/DisasterSummary.php](#file-21)
- [app/models/Evacuation.php](#file-22)
- [app/models/Household.php](#file-23)
- [app/models/Map.php](#file-24)
- [app/models/RecordActions.php](#file-25)
- [app/models/Relief.php](#file-26)
- [app/models/ReliefLocation.php](#file-27)
- [app/models/Report.php](#file-28)
- [app/models/Response.php](#file-29)
- [app/models/User.php](#file-30)
- [resources/views/admin/admin.index.php](#file-31)
- [resources/views/admin/announcement/announcement.php](#file-32)
- [resources/views/admin/announcement/announcement.update.php](#file-33)
- [resources/views/admin/announcement/editor.php](#file-34)
- [resources/views/admin/evacuation/center.php](#file-35)
- [resources/views/admin/evacuation/centers.php](#file-36)
- [resources/views/admin/household.php](#file-37)
- [resources/views/admin/households.php](#file-38)
- [resources/views/admin/records-all.php](#file-39)
- [resources/views/admin/records.php](#file-40)
- [resources/views/admin/relief/distributions.php](#file-41)
- [resources/views/admin/relief/inventory.php](#file-42)
- [resources/views/admin/relief/location.php](#file-43)
- [resources/views/admin/relief/locations.php](#file-44)
- [resources/views/admin/relief/movements.php](#file-45)
- [resources/views/admin/response/incident.php](#file-46)
- [resources/views/admin/response/teams.php](#file-47)
- [resources/views/admin/summaries/current.php](#file-48)
- [resources/views/admin/summaries/disaster.php](#file-49)
- [resources/views/components/dashboard-cards.php](#file-50)
- [resources/views/components/errors.php](#file-51)
- [resources/views/components/footer.php](#file-52)
- [resources/views/components/header.php](#file-53)
- [resources/views/components/location-picker.php](#file-54)
- [resources/views/components/messages.php](#file-55)
- [resources/views/components/navigation.php](#file-56)
- [resources/views/components/saved-point.php](#file-57)
- [resources/views/incidents/create.php](#file-58)
- [resources/views/incidents/image.php](#file-59)
- [resources/views/incidents/list.php](#file-60)
- [resources/views/incidents/view.php](#file-61)
- [resources/views/maps/address.php](#file-62)
- [resources/views/maps/centers.php](#file-63)
- [resources/views/maps/data.php](#file-64)
- [resources/views/maps/map.php](#file-65)
- [resources/views/user/announcement.php](#file-66)
- [resources/views/user/user.index.php](#file-67)
- [resources/assets/js/community-map.js](#file-68)
- [resources/assets/js/household-checkin.js](#file-69)
- [resources/assets/js/incident-location.js](#file-70)
- [resources/assets/js/location-picker.js](#file-71)
- [resources/assets/js/print-summary.js](#file-72)
- [resources/assets/js/record-selection.js](#file-73)
- [resources/assets/css/bayanready.css](#file-74)
- [resources/assets/css/disaster-summary.css](#file-75)
- [barangay_drrm.sql](#file-76)

<a id="file-1"></a>

## index.php

[Open actual source](../../../index.php)

```php
<?php
require_once __DIR__ . '/app/controllers/UserController.php';
if (!empty($_SESSION['ID'])) {
    redirect(dashboardPath());
}
$errors = (new UserController())->handlePost();
$pageTitle = 'Login and registration';
require __DIR__ . '/resources/views/components/header.php';
?>
<div class="page-intro"
    ><p class="eyebrow">Community preparedness &amp; response</p><h1><?= e($settings['app_name']) ?></h1
    ><p>Report incidents, follow community notices, and coordinate help.</p>
</div>
<?php require __DIR__ . '/resources/views/components/errors.php'; ?>
<div class="row g-4">
    <section class="col-lg-5">
        <div class="panel h-100"
            ><h2>Login</h2>
            <form method="post" action="<?= e(url('index.php')) ?>">
                <input type="hidden" name="action" value="login" />
                <label for="login-email" class="form-label">Email</label>
                <input
                    id="login-email"
                    class="form-control mb-3"
                    type="email"
                    name="email"
                    required
                    maxlength="254"
                    autocomplete="email"
                    value="<?= input('action') === 'login' ? e(input('email')) : '' ?>"
                />
                <label for="login-password" class="form-label">Password</label>
                <input
                    id="login-password"
                    class="form-control mb-3"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                />
                <button class="btn btn-primary">Login</button>
            </form>
        </div></section
    >
    <section class="col-lg-7">
        <div class="panel h-100"
            ><h2>Register as a resident</h2>
            <form method="post" action="<?= e(url('index.php')) ?>">
                <input type="hidden" name="action" value="register" />
                <?php foreach (['full_name' => ['Full name', 'text', 100], 'username' => ['Username', 'text', 50], 'email' => ['Email', 'email', 254], 'phone' => ['Contact number', 'tel', 20]] as $key => $field): ?>
                <label for="register-<?= e($key) ?>" class="form-label"
                    ><?= e($field[0]) ?></label
                >
                <input
                    id="register-<?= e($key) ?>"
                    class="form-control mb-3"
                    name="<?= e($key) ?>"
                    type="<?= e($field[1]) ?>"
                    maxlength="<?= $field[2] ?>"
                    required
                    value="<?= input('action') === 'register' ? e(input($key)) : '' ?>"
                />
                <?php endforeach; ?>
                <label for="register-password" class="form-label"
                    >Password (at least 8 characters)</label
                >
                <input
                    id="register-password"
                    class="form-control mb-3"
                    type="password"
                    name="password"
                    minlength="8"
                    required
                    autocomplete="new-password"
                />
                <label for="confirm-password" class="form-label">Confirm password</label>
                <input
                    id="confirm-password"
                    class="form-control mb-3"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <button class="btn btn-primary">Register</button>
            </form>
        </div></section
    >
</div>
<?php require __DIR__ . '/resources/views/components/footer.php'; ?>
```

<a id="file-2"></a>

## config/config.example.php

[Open actual source](../../../config/config.example.php)

```php
<?php
// Copy to config.php and change values for your computer. Never commit config.php.
return [
    'app_name' => 'BayanReady',
    'base_url' => '/WEBSYS_PROJECT', // Use '' when served at localhost root.
    'host' => '127.0.0.1',
    'port' => '8889',
    'database' => 'barangay_drrm',
    'username' => 'root',
    'password' => '',
    // Absolute private folder OUTSIDE your web server document root; PHP must be able to write here.
    'incident_upload_dir' => '',
    // Atok municipality demo view only; choose one barangay before deployment.
    'demo_barangay_name' => 'Atok, Benguet (DEMO)',
    'map_latitude' => 16.6272,
    'map_longitude' => 120.7672,
    // Photon-compatible endpoint; empty string disables address suggestions.
    'address_lookup_url' => 'https://photon.komoot.io/reverse',
    'map_zoom' => 14,
    'map_min_zoom' => 11, // Focused demo view; no official boundary restriction.
];
```

<a id="file-3"></a>

## config/db.php

[Open actual source](../../../config/db.php)

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

<a id="file-4"></a>

## app/controllers/AnnouncementController.php

[Open actual source](../../../app/controllers/AnnouncementController.php)

Methods in source order:

- `__construct(string $role)`
- `queryValue(string $key): string`
- `listAnnouncements(): array`
- `getAnnouncement(): array`
- `save(?int $id): array`

```php
<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Announcement.php';
class AnnouncementController extends Controller
{
    private AnnouncementModel $model;
    public function __construct(string $role)
    {
        requireRole($role);
        $this->model = new AnnouncementModel();
    }
    public function queryValue(string $key): string
    {
        return is_string($_GET[$key] ?? null)?trim($_GET[$key]):'';
    }
    public function listAnnouncements(): array
    {
        $resident = $_SESSION['ROLE'] === 'resident';
        $filters = ['q' => $this->queryValue('q'),'date' => $this->queryValue('date'),'status' => $resident?'':$this->queryValue('status')];
        if (!in_array($filters['status'], ['','published','draft'], true) || ($filters['date'] !== '' && !validDateInput($filters['date']))) {
            http_response_code(400);
            exit('Invalid announcement filter.');
        }
        return $this->model->listing($filters, $resident);
    }
    public function getAnnouncement(): array
    {
        requireRole('admin');
        $id = is_scalar($_GET['id'] ?? null)?filter_var($_GET['id'], FILTER_VALIDATE_INT):false;
        if (!$id || $id < 1) {
            http_response_code(400);
            exit('Invalid announcement ID.');
        }
        $row = $this->model->find($id);
        if (!$row) {
            http_response_code(404);
            exit('Announcement not found.');
        }
        return $row;
    }
    public function save(?int $id): array
    {
        requireRole('admin');
        if (!isPost()) {
            return [];
        }
        if ($id !== null && !empty($this->model->find($id)['archived_at'])) {
            return ['Archived notices cannot be edited or republished.'];
        }
        $action = input('action');
        if ($id !== null && $action === 'save_draft' && $this->model->find($id)['is_published'] && input('confirm_withdraw') !== '1') {
            return ['Confirm withdrawal before saving this published notice as a draft.'];
        }
        $title = input('title');
        $message = input('message');
        if (!in_array($action, ['save_draft','publish'], true)) {
            return ['Choose Save draft / withdraw or Publish.'];
        }
        if ($title === '' || strlen($title) > 150 || $message === '' || strlen($message) > 10000) {
            return ['Enter a title (up to 150 bytes) and message (up to 10,000 bytes).'];
        }
        try {
            $this->model->save($id, $title, $message, $action === 'publish'?1:0, (int)$_SESSION['ID']);
        } catch (PDOException $error) {
            return ['Announcement could not be saved. Please try again.'];
        }
        $this->success($action === 'publish'?'Announcement published to residents.':'Announcement saved privately as a draft.', 'resources/views/admin/announcement/announcement.php');
        return [];
    }
}
```

<a id="file-5"></a>

## app/controllers/Controller.php

[Open actual source](../../../app/controllers/Controller.php)

Methods in source order:

- `success(string $message, string $path): void`

```php
<?php
require_once __DIR__ . '/../helpers.php';
class Controller
{
    protected function success(string $message, string $path): void
    {
        flash($message);
        redirect($path);
    }
}
```

<a id="file-6"></a>

## app/controllers/DashboardController.php

[Open actual source](../../../app/controllers/DashboardController.php)

Methods in source order:

- `__construct(string $role)`
- `dashboardData(): array`

```php
<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Dashboard.php';
class DashboardController extends Controller
{
    private DashboardModel $model;
    public function __construct(string $role)
    {
        requireRole($role);
        $this->model = new DashboardModel();
    }
    public function dashboardData(): array
    {
        $admin = $_SESSION['ROLE'] === 'admin';
        $data = ['incidents' => $this->model->incidents($admin?null:(int)$_SESSION['ID']),'centers' => $this->model->centers()];
        if ($admin) {
            $data['affected'] = $this->model->affected();
            $data['stays'] = $this->model->stays();
        } else {
            $data['notices'] = $this->model->notices();
        }
        return $data;
    }
}
```

<a id="file-7"></a>

## app/controllers/DisasterSummaryController.php

[Open actual source](../../../app/controllers/DisasterSummaryController.php)

Methods in source order:

- `__construct()`
- `period(): array`
- `summary(array $period): array`

```php
<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/DisasterSummary.php';
class DisasterSummaryController extends Controller
{
    public const TYPES = ['flood' => 'Flooding','fire' => 'Fire','landslide' => 'Landslide','road_obstruction' => 'Road obstruction','other' => 'Other emergency'];
    public const STATUSES = ['pending','verified','responding','resolved','rejected'];
    private DisasterSummaryModel $model;
    public function __construct()
    {
        requireRole('admin');
        $this->model = new DisasterSummaryModel();
    }
    public function period(): array
    {
        $zone = new DateTimeZone('Asia/Manila');
        $today = new DateTimeImmutable('today', $zone);
        $values = [];
        $dates = [];
        foreach (['from' => $today->format('Y-m-01'),'to' => $today->format('Y-m-d')] as $key => $default) {
            $value = $_GET[$key] ?? $default;
            $date = is_string($value) && validDateInput($value)?DateTimeImmutable::createFromFormat('!Y-m-d', $value, $zone):false;
            if (!$date || $date->format('Y-m-d') !== $value || $value < '1000-01-01' || $value > '9999-12-30') {
                http_response_code(400);
                exit('Enter valid start and end dates in YYYY-MM-DD format.');
            }
            $values[$key] = $value;
            $dates[$key] = $date;
        }
        if ($dates['from'] > $dates['to']) {
            http_response_code(400);
            exit('Start date must be on or before end date.');
        }
        return $values + ['start' => $values['from'] . ' 00:00:00','end_exclusive' => $dates['to']->modify('+1 day')->format('Y-m-d') . ' 00:00:00'];
    }
    public function summary(array $period): array
    {
        return $this->model->summarize($period['start'], $period['end_exclusive']);
    }
}
```

<a id="file-8"></a>

## app/controllers/EvacuationController.php

[Open actual source](../../../app/controllers/EvacuationController.php)

Methods in source order:

- `__construct()`
- `recordId($value): int`
- `center(int $id): array`
- `centers(string $q): array`
- `households(): array`
- `householdMembers(int $householdId): array`
- `walkins(): array`
- `records(int $id): array`
- `failure(Throwable $e): array`
- `saveCenter(?int $id): array`
- `handleStay(int $id): array`

```php
<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Evacuation.php';
class EvacuationController extends Controller
{
    private EvacuationModel $model;
    public function __construct()
    {
        requireRole('admin');
        $this->model = new EvacuationModel();
    }
    public function recordId($value): int
    {
        $id = is_scalar($value)?filter_var($value, FILTER_VALIDATE_INT):false;
        if (!$id || $id < 1) {
            http_response_code(400);
            exit('Invalid ID.');
        }
        return $id;
    }
    public function center(int $id): array
    {
        $row = $this->model->center($id);
        if (!$row) {
            http_response_code(404);
            exit('Center not found.');
        }
        return $row;
    }
    public function centers(string $q): array
    {
        return $this->model->centers($q);
    }
    public function households(): array
    {
        return $this->model->households();
    }

    public function householdMembers(int $householdId): array
    {
        return $this->model->householdMembers($householdId);
    }

    public function walkins(): array
    {
        return $this->model->walkins();
    }
    public function records(int $id): array
    {
        return $this->model->records($id);
    }
    private function failure(Throwable $e): array
    {
        if ($e instanceof DomainException) {
            return [$e->getMessage()];
        }
        if ($e instanceof PDOException && ($e->errorInfo[1] ?? null) === 1062) {
            return ['This person already has an active check-in. Check out the existing stay first.'];
        }
        return ['Could not save the change. Please try again.'];
    }
    public function saveCenter(?int $id): array
    {
        if (!isPost()) {
            return [];
        }
        if (input('action') !== 'save_center') {
            return ['Unknown form action.'];
        }
        $capacity = filter_var(input('capacity'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0,'max_range' => 1000000]]);
        $data = ['name' => input('name'),'address_details' => input('address_details'),'capacity' => $capacity,'status' => input('status')];
        if ($data['name'] === '' || strlen($data['name']) > 150 || $data['address_details'] === '' || strlen($data['address_details']) > 255 || $capacity === false || !in_array($data['status'], ['open','closed'], true)) {
            return ['Enter name (up to 150 bytes), address (up to 255 bytes), whole-person capacity (0–1,000,000), and valid status.'];
        }
        $errors = locationErrors($data['address_details'], input('latitude'), input('longitude'));
        if ($errors) {
            return $errors;
        }
        foreach (['latitude','longitude'] as $field) {
            $data[$field] = input($field) === ''?null:input($field);
        }
        try {
            $this->model->saveCenter($id, $data);
        } catch (Throwable $e) {
            return $this->failure($e);
        }
        $this->success('Center saved.', 'resources/views/admin/evacuation/centers.php');
        return [];
    }
    public function handleStay(int $id): array
    {
        if (!isPost()) {
            return [];
        }
        try {
            if (input('action') === 'checkin_household') {
                $memberIds = $_POST['member_ids'] ?? [];
                if (!is_array($memberIds)) {
                    return ['Choose arriving members.'];
                }
                $this->model->checkinHousehold($id, $this->recordId(input('household_id')), $memberIds, (int)$_SESSION['ID']);
            } elseif (input('action') === 'checkout') {
                $this->model->checkout($id, $this->recordId(input('record_id')), (int)$_SESSION['ID']);
            } elseif (input('action') === 'checkin') {
                $kind = input('kind');
                $data = [];
                $personId = 0;
                if (!in_array($kind, ['walkin','new_walkin'], true)) {
                    return ['Choose a check-in type.'];
                }
                if ($kind === 'new_walkin') {
                    $data = ['full_name' => input('full_name'),'age' => filter_var(input('age'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0,'max_range' => 130]]),'address_details' => input('address_details'),'contact_phone' => input('contact_phone')];
                    if ($data['full_name'] === '' || strlen($data['full_name']) > 100 || $data['age'] === false || $data['address_details'] === '' || strlen($data['address_details']) > 255 || !validPhone($data['contact_phone'], 3)) {
                        return ['For a new walk-in, enter name (up to 100 bytes), age 0–130, home address (up to 255 bytes), and contact number (personal or guardian).'];
                    }
                } else {
                    $personId = $this->recordId(input('walkin_id'));
                }
                $this->model->checkin($id, $kind, $personId, $data, (int)$_SESSION['ID']);
            } else {
                return ['Unknown form action.'];
            }
        } catch (Throwable $e) {
            return $this->failure($e);
        }
        $this->success('Evacuation record saved.', 'resources/views/admin/evacuation/center.php?id=' . $id);
        return [];
    }
}
```

<a id="file-9"></a>

## app/controllers/HouseholdController.php

[Open actual source](../../../app/controllers/HouseholdController.php)

Methods in source order:

- `__construct()`
- `recordId($value): int`
- `getHousehold(int $id): array`
- `member(int $id, int $householdId): array`
- `listHouseholds(string $search): array`
- `members(int $id): array`
- `householdInput(): array`
- `saveHousehold(?int $id, array $data): array`
- `saveMember(int $householdId, ?int $memberId, array $data): array`

```php
<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Household.php';
class HouseholdController extends Controller
{
    public const VULNERABILITY = ['unassessed' => 'Not yet assessed','no_additional_need_identified' => 'No additional assistance need identified','assistance_needed' => 'Additional assistance needed'];
    public const HAZARDS = ['unassessed' => 'Not assessed','landslide' => 'Landslide','flood' => 'Flood','fire' => 'Fire','other' => 'Other'];
    public const REASONS = ['monitored' => 'Monitored','affected' => 'Affected','both' => 'Monitored and affected'];
    public const EVACUATION = ['at_home' => 'At home','advised' => 'Advised to evacuate','evacuated' => 'Evacuated','returned' => 'Returned'];
    private HouseholdModel $households;
    public function __construct()
    {
        requireRole('admin');
        $this->households = new HouseholdModel();
    }
    public function recordId($value): int
    {
        $id = is_scalar($value) ? filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
        if ($id === false) {
            http_response_code(400);
            exit('Invalid record ID.');
        }
        return $id;
    }
    public function getHousehold(int $id): array
    {
        $row = $this->households->findById($id);
        if (!$row) {
            http_response_code(404);
            exit('Household not found.');
        }
        return $row;
    }
    public function member(int $id, int $householdId): array
    {
        $row = $this->households->findMember($id, $householdId);
        if (!$row) {
            http_response_code(404);
            exit('Member not found in this household.');
        }
        return $row;
    }
    public function listHouseholds(string $search): array
    {
        return $this->households->getAll($search);
    }
    public function members(int $id): array
    {
        return $this->households->members($id);
    }
    public function householdInput(): array
    {
        $data = [];
        foreach (['household_code','representative_name','contact_phone','purok','address_details','registry_reason','hazard_type','hazard_notes','vulnerability_category','vulnerability_notes','evacuation_status','latitude','longitude'] as $field) {
            $data[$field] = input($field);
        }
        return $data;
    }
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
    public function saveMember(int $householdId, ?int $memberId, array $data): array
    {
        if (!isPost()) {
            return [];
        }
        if (input('action') !== ($memberId === null ? 'create_member' : 'update_member')) {
            return ['Invalid form action.'];
        }
        $this->getHousehold($householdId);
        if ($memberId !== null) {
            $this->member($memberId, $householdId);
        }
        $errors = [];
        if ($data['full_name'] === '' || strlen($data['full_name']) > 100) {
            $errors[] = 'Enter a member name of up to 100 bytes.';
        }
        if (strlen($data['notes']) > 255) {
            $errors[] = 'Member notes must fit within 255 bytes.';
        }
        $age = $data['age'] === '' ? null : filter_var($data['age'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0,'max_range' => 130]]);
        if ($age === false) {
            $errors[] = 'Enter a whole-number age from 0 to 130, or leave it blank.';
        }
        if ($errors) {
            return $errors;
        }
        $data['age'] = $age;
        try {
            $this->households->saveMember($householdId, $memberId, $data);
            $this->success('Household member saved.', 'resources/views/admin/household.php?id=' . $householdId);
        } catch (DomainException $error) {
            return [$error->getMessage()];
        } catch (PDOException $error) {
            return ['The member could not be saved. Please try again.'];
        }
    }
}
```

<a id="file-10"></a>

## app/controllers/MapController.php

[Open actual source](../../../app/controllers/MapController.php)

Methods in source order:

- `mapData(): array`
- `reliefLocation($value): array`

```php
<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Map.php';
class MapController extends Controller
{
    public function mapData(): array
    {
        session_checker();
        $model = new MapModel();
        return $model->publicMap();
    }
    public function reliefLocation($value): array
    {
        requireRole('admin');
        $id = is_scalar($value)?filter_var($value, FILTER_VALIDATE_INT):false;
        if (!$id || $id < 1) {
            http_response_code(400);
            exit('Invalid location ID.');
        }
        $row = (new MapModel())->reliefLocation($id);
        if (!$row) {
            http_response_code(404);
            exit('Relief location not found.');
        }
        return $row;
    }
}
```

<a id="file-11"></a>

## app/controllers/RecordActionsController.php

[Open actual source](../../../app/controllers/RecordActionsController.php)

Methods in source order:

- `__construct()`
- `listing(string $type, string $search, int $page): array`
- `delete(array $rows): void`

```php
<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/RecordActions.php';
class RecordActionsController extends Controller
{
    private RecordActionsModel $model;
    public function __construct()
    {
        requireRole('admin');
        $this->model = new RecordActionsModel();
    }
    public function listing(string $type, string $search, int $page): array
    {
        return $this->model->listing($type, $search, $page);
    }
    public function delete(array $rows): void
    {
        requireRole('admin');
        $ids = $_POST['ids'] ?? [];
        if (input('action') !== 'delete' || !is_array($ids) || !$ids || count($ids) > 50) {
            throw new DomainException('Select 1–50 records on this page.');
        }
        $visible = [];
        foreach ($rows as $row) {
            $visible[$row['record_type'] . ':' . $row['id']] = $row;
        }
        $selection = [];
        foreach ($ids as $key) {
            if (!is_string($key) || !isset($visible[$key])) {
                throw new DomainException('Select records from the displayed page.');
            }
            $row = $visible[$key];
            $selection[$row['record_type']][(int)$row['id']] = (int)$row['id'];
        }
        foreach ($selection as $type => $records) {
            $selection[$type] = array_values($records);
        }
        $this->model->deleteSelected($selection);
        flash('Selected records removed. Linked history was preserved.');
    }
}
```

<a id="file-12"></a>

## app/controllers/ReliefController.php

[Open actual source](../../../app/controllers/ReliefController.php)

Methods in source order:

- `__construct()`
- `recordId($value): int`
- `queryValue(string $key): string`
- `items(string $q = ''): array`
- `item(int $id): array`
- `households(): array`
- `distributions(string $q): array`
- `distribution(int $id): array`
- `distributedGoods(int $id): array`
- `movements(): array`
- `failure(Throwable $e): array`
- `quantity($value)`
- `saveItem(?int $id): array`
- `stock(): array`
- `distribute(): array`

```php
<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Relief.php';
class ReliefController extends Controller
{
    private ReliefModel $model;
    public function __construct()
    {
        requireRole('admin');
        $this->model = new ReliefModel();
    }
    public function recordId($value): int
    {
        $id = is_scalar($value)?filter_var($value, FILTER_VALIDATE_INT):false;
        if (!$id || $id < 1) {
            http_response_code(400);
            exit('Invalid ID.');
        }
        return $id;
    }
    public function queryValue(string $key): string
    {
        return is_string($_GET[$key] ?? null)?trim($_GET[$key]):'';
    }
    public function items(string $q = ''): array
    {
        return $this->model->items($q);
    }
    public function item(int $id): array
    {
        $row = $this->model->item($id);
        if (!$row) {
            http_response_code(404);
            exit('Item not found.');
        }
        return $row;
    }
    public function households(): array
    {
        return $this->model->households();
    }
    public function distributions(string $q): array
    {
        return $this->model->distributions($q);
    }
    public function distribution(int $id): array
    {
        $row = $this->model->distribution($id);
        if (!$row) {
            http_response_code(404);
            exit('Distribution not found.');
        }
        return $row;
    }
    public function distributedGoods(int $id): array
    {
        return $this->model->distributedGoods($id);
    }
    public function movements(): array
    {
        $type = $this->queryValue('type');
        $date = $this->queryValue('date');
        if (!in_array($type, ['','stock_in','stock_out','distribution'], true) || ($date !== '' && !validDateInput($date))) {
            http_response_code(400);
            exit('Invalid history filter.');
        }
        return $this->model->movements($this->queryValue('q'), $type, $date);
    }
    private function failure(Throwable $e): array
    {
        if ($e instanceof DomainException) {
            return [$e->getMessage()];
        }
        if ($e instanceof PDOException && ($e->errorInfo[1] ?? null) === 1062) {
            return ['That item name and unit already exist.'];
        }
        return ['Change could not be saved. Please try again.'];
    }
    private function quantity($value)
    {
        return is_scalar($value)?filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1,'max_range' => 1000000000]]):false;
    }
    public function saveItem(?int $id): array
    {
        if (!isPost()) {
            return [];
        }
        if (input('action') !== 'save_item') {
            return ['Unknown form action.'];
        }
        $name = input('item_name');
        $unit = input('unit');
        if ($name === '' || strlen($name) > 120 || $unit === '' || strlen($unit) > 30) {
            return ['Enter an item name (up to 120 bytes) and unit (up to 30 bytes).'];
        }
        try {
            $this->model->saveItem($id, $name, $unit);
        } catch (Throwable $e) {
            return $this->failure($e);
        }
        $this->success('Relief item saved.', 'resources/views/admin/relief/inventory.php');
        return [];
    }
    public function stock(): array
    {
        if (!isPost()) {
            return [];
        }
        $quantity = $this->quantity(input('quantity'));
        $type = input('movement_type');
        $notes = input('notes');
        if (input('action') !== 'stock' || !in_array($type, ['stock_in','stock_out'], true) || $quantity === false || $notes === '' || strlen($notes) > 255) {
            return ['Choose stock-in/out, enter a positive whole quantity (up to 1 billion), and notes/reason (up to 255 bytes).'];
        }
        $id = $this->recordId(input('item_id'));
        try {
            $this->model->stock($id, $type, $quantity, (int)$_SESSION['ID'], $notes);
        } catch (Throwable $e) {
            return $this->failure($e);
        }
        $this->success('Stock movement recorded.', 'resources/views/admin/relief/movements.php');
        return [];
    }
    public function distribute(): array
    {
        if (!isPost()) {
            return [];
        }
        $receiver = input('received_by');
        $notes = input('notes');
        $raw = $_POST['goods'] ?? null;
        if (input('action') !== 'distribute' || $receiver === '' || strlen($receiver) > 100 || strlen($notes) > 255 || !is_array($raw)) {
            return ['Enter receiving representative (up to 100 bytes), notes (up to 255 bytes), and goods.'];
        }
        $goods = [];
        foreach ($raw as $key => $value) {
            if ($value === '' || $value === '0') {
                continue;
            }
            $id = $this->recordId($key);
            $quantity = $this->quantity($value);
            if ($quantity === false) {
                return ['Goods quantities must be positive whole numbers (up to 1 billion), or blank/zero to omit.'];
            }
            $goods[$id] = $quantity;
        }
        if (!$goods) {
            return ['Select at least one good by entering its quantity.'];
        }
        $household = $this->recordId(input('household_id'));
        try {
            $id = $this->model->distribute($household, $receiver, $notes, $goods, (int)$_SESSION['ID']);
        } catch (Throwable $e) {
            return $this->failure($e);
        }
        $this->success('Distribution recorded and stock updated.', 'resources/views/admin/relief/distributions.php?id=' . $id);
        return [];
    }
}
```

<a id="file-13"></a>

## app/controllers/ReliefLocationController.php

[Open actual source](../../../app/controllers/ReliefLocationController.php)

Methods in source order:

- `__construct()`
- `all(): array`
- `find(int $id): array`
- `save(?int $id): array`

```php
<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/ReliefLocation.php';
class ReliefLocationController extends Controller
{
    private ReliefLocationModel $model;
    public function __construct()
    {
        requireRole('admin');
        $this->model = new ReliefLocationModel();
    }
    public function all(): array
    {
        return $this->model->all();
    }
    public function find(int $id): array
    {
        $location = $this->model->find($id);
        if (!$location) {
            http_response_code(404);
            exit('Location not found.');
        }
        return $location;
    }
    public function save(?int $id): array
    {
        if (!isPost()) {
            return [];
        }
        if ($id !== null && !empty($this->model->find($id)['archived_at'])) {
            return ['Archived locations cannot be edited.'];
        }
        $data = [];
        foreach (['name','location_type','address_details','latitude','longitude'] as $field) {
            $data[$field] = input($field);
        }
        if (input('action') !== 'save_location' || $data['name'] === '' || strlen($data['name']) > 150 || $data['address_details'] === '' || strlen($data['address_details']) > 255 || !in_array($data['location_type'], ['storage','distribution','both'], true)) {
            return ['Enter name, address, and valid location type.'];
        }
        $errors = locationErrors($data['address_details'], $data['latitude'], $data['longitude']);
        if ($errors) {
            return $errors;
        }
        foreach (['latitude','longitude'] as $field) {
            if ($data[$field] === '') {
                $data[$field] = null;
            }
        }
        try {
            $this->model->save($id, $data);
        } catch (PDOException $e) {
            return ['Location could not be saved.'];
        }
        $this->success('Location saved.', 'resources/views/admin/relief/locations.php');
        return [];
    }
}
```

<a id="file-14"></a>

## app/controllers/ReportController.php

[Open actual source](../../../app/controllers/ReportController.php)

Methods in source order:

- `__construct(string $role)`
- `owner(): ?int`
- `incidentId(): int`
- `getIncident(int $id): array`
- `filters(): array`
- `validDate(string $date): bool`
- `listIncidents(array $filters): array`
- `images(int $id): array`
- `history(int $id): array`
- `values(): array`
- `storage(): string`
- `upload(): ?array`
- `create(array $data): array`
- `updateLocation(int $id): array`
- `review(int $id): array`
- `serveImage(int $id): void`

```php
<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Report.php';
class ReportController extends Controller
{
    public const TYPES = ['flood' => 'Flooding','fire' => 'Fire','landslide' => 'Landslide','road_obstruction' => 'Road obstruction','other' => 'Other emergency'];
    public const STATUSES = ['pending','verified','responding','resolved','rejected'];
    private ReportModel $model;
    public function __construct(string $role)
    {
        requireRole($role);
        $this->model = new ReportModel();
    }
    private function owner(): ?int
    {
        return $_SESSION['ROLE'] === 'admin' ? null : (int) $_SESSION['ID'];
    }
    public function incidentId(): int
    {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            http_response_code(400);
            exit('Invalid ID.');
        }
        return $id;
    }
    public function getIncident(int $id): array
    {
        $row = $this->model->find($id, $this->owner());
        if (!$row) {
            http_response_code(404);
            exit('Incident not found.');
        }
        return $row;
    }
    public function filters(): array
    {
        $filters = [];
        foreach (['incident_type','status','date','location','search'] as $key) {
            $filters[$key] = is_string($_GET[$key] ?? null) ? trim($_GET[$key]) : '';
        }
        if (($filters['incident_type'] !== '' && !isset(self::TYPES[$filters['incident_type']])) ||
            ($filters['status'] !== '' && !in_array($filters['status'], self::STATUSES, true)) ||
            ($filters['date'] !== '' && !$this->validDate($filters['date']))) {
            http_response_code(400);
            exit('Invalid search filter.');
        }
        return $filters;
    }
    private function validDate(string $date): bool
    {
        return validDateInput($date);
    }
    public function listIncidents(array $filters): array
    {
        return $this->model->listing($filters, $this->owner());
    }
    public function images(int $id): array
    {
        $this->getIncident($id);
        return $this->model->images($id);
    }
    public function history(int $id): array
    {
        requireRole('admin');
        return $this->model->history($id);
    }
    public function values(): array
    {
        $data = [];
        foreach (['incident_type','description','reporter_name','contact_phone','location_description','latitude','longitude','report_source'] as $key) {
            $data[$key] = input($key);
        }
        return $data;
    }
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
    public function updateLocation(int $id): array
    {
        requireRole('admin');
        $description = input('location_description');
        $lat = input('latitude');
        $lng = input('longitude');
        $errors = locationErrors($description, $lat, $lng);
        if ($description === '' || strlen($description) > 255) {
            $errors[] = 'Enter a written location of up to 255 bytes.';
        }
        $notes = input('location_notes');
        if ($notes === '' || strlen($notes) > 255) {
            $errors[] = 'Enter a correction reason of up to 255 bytes.';
        }
        if ($errors) {
            return $errors;
        }
        try {
            $this->model->updateLocation($id, $description, $lat === ''?null:$lat, $lng === ''?null:$lng, $notes, (int)$_SESSION['ID']);
        } catch (DomainException $error) {
            return [$error->getMessage()];
        } catch (Throwable $error) {
            return ['Location could not be saved. Please try again.'];
        }
        $this->success('Incident location updated.', 'resources/views/incidents/view.php?id=' . $id);
        return [];
    }
    public function review(int $id): array
    {
        requireRole('admin');
        if (!isPost()) {
            return [];
        }
        if (input('action') !== 'review_incident' || !in_array(input('status'), self::STATUSES, true) || input('notes') === '' || strlen(input('notes')) > 255) {
            return ['Choose a status and enter review notes (maximum 255 bytes).'];
        }
        try {
            $this->model->review($id, input('status'), input('notes'), (int)$_SESSION['ID']);
        } catch (DomainException $error) {
            return [$error->getMessage()];
        } catch (Throwable $error) {
            return ['Review could not be saved. Please try again.'];
        }
        $this->success('Review saved.', 'resources/views/incidents/view.php?id=' . $id);
        return [];
    }
    public function serveImage(int $id): void
    {
        $image = $this->model->image($id, $this->owner());
        $filename = $image['stored_filename'] ?? '';
        $parts = explode('.', $filename);
        if (count($parts) !== 2 || strlen($parts[0]) !== 48
            || strspn($parts[0], 'abcdef0123456789') !== 48
            || !in_array($parts[1], ['jpg', 'png', 'webp'], true)) {
            http_response_code(404);
            exit('Image not found.');
        }
        $path = $this->storage() . '/' . $image['stored_filename'];
        if (!is_file($path)) {
            http_response_code(404);
            exit('Image not found.');
        }
        header('Content-Type: ' . $image['mime_type']);
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}
```

<a id="file-15"></a>

## app/controllers/ResponseController.php

[Open actual source](../../../app/controllers/ResponseController.php)

Methods in source order:

- `__construct()`
- `recordId($value): int`
- `team(int $id): array`
- `incident(int $id): array`
- `teams(): array`
- `assignments(int $id): array`
- `history(int $id): array`
- `saveTeam(?int $id): array`
- `record(int $id): array`

```php
<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Response.php';
class ResponseController extends Controller
{
    public const STATUSES = ['assigned','on_scene','completed','cancelled'];
    private ResponseModel $model;
    public function __construct()
    {
        requireRole('admin');
        $this->model = new ResponseModel();
    }
    public function recordId($value): int
    {
        $id = is_scalar($value) ? filter_var($value, FILTER_VALIDATE_INT) : false;
        if (!$id || $id < 1) {
            http_response_code(400);
            exit('Invalid record ID.');
        }
        return $id;
    }
    public function team(int $id): array
    {
        $row = $this->model->team($id);
        if (!$row) {
            http_response_code(404);
            exit('Team not found.');
        }
        return $row;
    }
    public function incident(int $id): array
    {
        $row = $this->model->incident($id);
        if (!$row) {
            http_response_code(404);
            exit('Incident not found.');
        }
        return $row;
    }
    public function teams(): array
    {
        return $this->model->teams();
    }
    public function assignments(int $id): array
    {
        return $this->model->assignments($id);
    }
    public function history(int $id): array
    {
        return $this->model->history($id);
    }
    public function saveTeam(?int $id): array
    {
        if (!isPost()) {
            return [];
        }
        if (input('action') !== 'save_team') {
            return ['Unknown form action.'];
        }
        $name = input('team_name');
        $phone = input('contact_phone');
        $active = input('is_active');
        if ($name === '' || strlen($name) > 120) {
            return ['Team name is required (maximum 120 bytes).'];
        }
        if ($phone !== '' && !validPhone($phone, 3)) {
            return ['Enter a valid contact number, or leave it blank.'];
        }
        if (!in_array($active, ['0','1'], true)) {
            return ['Choose active or inactive.'];
        }
        try {
            $this->model->saveTeam($id, $name, $phone, (int)$active);
        } catch (PDOException $error) {
            return [($error->errorInfo[1] ?? null) === 1062 ? 'That team name is already in use.' : 'Team could not be saved. Please try again.'];
        }
        $this->success('Team saved.', 'resources/views/admin/response/teams.php');
        return [];
    }
    public function record(int $id): array
    {
        if (!isPost()) {
            return [];
        }
        $action = input('action');
        $notes = input('notes');
        if (!in_array($action, ['assign_team','update_assignment'], true)) {
            return ['Unknown form action.'];
        }
        if ($notes === '' || strlen($notes) > 2000) {
            return ['Describe the action taken (maximum 2,000 bytes).'];
        }
        $teamId = $action === 'assign_team' ? $this->recordId(input('team_id')) : null;
        $assignmentId = $action === 'update_assignment' ? $this->recordId(input('assignment_id')) : null;
        $status = $action === 'assign_team' ? 'assigned' : input('assignment_status');
        if (!in_array($status, self::STATUSES, true)) {
            return ['Choose a valid assignment status.'];
        }
        try {
            $this->model->record($id, $teamId, $assignmentId, $status, $notes, (int)$_SESSION['ID']);
        } catch (DomainException $error) {
            return [$error->getMessage()];
        } catch (Throwable $error) {
            return ['Response update could not be saved. Please try again.'];
        }
        $this->success('Response update recorded.', 'resources/views/admin/response/incident.php?id=' . $id);
        return [];
    }
}
```

<a id="file-16"></a>

## app/controllers/UserController.php

[Open actual source](../../../app/controllers/UserController.php)

Methods in source order:

- `handlePost(): array`

```php
<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/User.php';
class UserController extends Controller
{
    public function handlePost(): array
    {
        if (!isPost()) {
            return [];
        }
        $action = input('action');
        if (!in_array($action, ['login', 'register'], true)) {
            return ['Invalid form action.'];
        }
        $email = input('email');
        $password = input('password', false);
        $errors = [];
        if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        }
        if ($password === '') {
            $errors[] = 'Enter your password.';
        }
        if (strlen($password) > 72) {
            $errors[] = 'Use a password of at most 72 bytes.';
        }
        if ($action === 'register') {
            $data = ['full_name' => input('full_name'), 'username' => input('username'), 'email' => $email, 'phone' => input('phone')];
            if ($data['full_name'] === '' || strlen($data['full_name']) > 100) {
                $errors[] = 'Enter a name of up to 100 bytes.';
            }
            $usernameLength = strlen($data['username']);
            if ($usernameLength < 3 || $usernameLength > 50
                || strspn($data['username'], 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_') !== $usernameLength) {
                $errors[] = 'Use 3–50 letters, numbers or underscores for your username.';
            }
            if (!validPhone($data['phone'], 7)) {
                $errors[] = 'Enter a contact number of 7–20 characters.';
            }
            if (strlen($password) < 8) {
                $errors[] = 'Use a password of at least 8 characters.';
            }
            if ($password !== input('password_confirmation', false)) {
                $errors[] = 'The passwords do not match.';
            }
        }
        if ($errors) {
            return $errors;
        }
        try {
            $users = new UserModel(); // Connection failure is inside the catch boundary.
            if ($action === 'register') {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
                $users->createResident($data);
                $this->success('Registration successful. You can now log in.', 'index.php');
            }
            $user = $users->findByEmail($email);
            if (!$user || !password_verify($password, $user['password']) || !$user['is_active'] || !in_array($user['role'], ['admin', 'resident'], true)) {
                return ['Invalid email or password, or the account is inactive.'];
            }
            session_regenerate_id(true);
            $_SESSION = ['ID' => (int) $user['id'], 'FULL_NAME' => $user['full_name'], 'USERNAME' => $user['username'], 'PHONE' => $user['phone'] ?? '', 'EMAIL' => $user['email'], 'ROLE' => $user['role']];
            $this->success('You are now signed in.', dashboardPath());
        } catch (PDOException $error) {
            if ($action === 'register' && $error->getCode() === '23000') {
                return ['That email address or username is already registered.'];
            }
            return ['The database is unavailable. Please try again later.'];
        }
    }
}
```

<a id="file-17"></a>

## app/evacuation-capacity.php

[Open actual source](../../../app/evacuation-capacity.php)

```php
<?php
// Capacity is measured in people; occupancy counts stays without a checkout.
function availableCapacity(array $center): int
{
    if ($center['status'] !== 'open' || !empty($center['archived_at'])) {
        return 0;
    }
    return max(0, (int)$center['capacity'] - (int)$center['occupancy']);
}
```

<a id="file-18"></a>

## app/helpers.php

[Open actual source](../../../app/helpers.php)

```php
<?php
if (!is_file(__DIR__ . '/../config/config.php')) {
    http_response_code(503);
    exit('Setup required: copy config/config.example.php to config/config.php and enter your local settings.');
}
$settings = require __DIR__ . '/../config/config.php';
define('BASE_URL', rtrim($settings['base_url'], '/'));

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_start();
}

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function url(string $path): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}
function redirect(string $path): void
{
    header('Location: ' . url($path), true, 303);
    exit;
}
function isPost(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
}
function input(string $key, bool $trim = true): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? ($trim ? trim($value) : $value) : '';
}
function validDateInput(string $value): bool
{
    // Reject malformed bytes before PHP's date parser, which can throw on null bytes.
    if (strlen($value) !== 10 || $value[4] !== '-' || $value[7] !== '-' || !ctype_digit(str_replace('-', '', $value))) {
        return false;
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}
function validPhone(string $value, int $minimum): bool
{
    $length = strlen($value);
    return $length >= $minimum && $length <= 20
        && strspn($value, '0123456789+() .-') === $length;
}
function flash(string $message): void
{
    $_SESSION['message'] = ['text' => $message, 'type' => 'success'];
}
function dashboardPath(): string
{
    return ($_SESSION['ROLE'] ?? '') === 'admin'
        ? 'resources/views/admin/admin.index.php' : 'resources/views/user/user.index.php';
}
function session_checker(): void
{
    if (empty($_SESSION['ID']) || !in_array($_SESSION['ROLE'] ?? '', ['admin', 'resident'], true)) {
        redirect('index.php');
    }
}
function requireRole(string $role): void
{
    session_checker();
    if ($_SESSION['ROLE'] !== $role) {
        http_response_code(403);
        exit('Access denied. This page is not available for your account.');
    }
    if (isPost() && (input('action') === 'logout' || isset($_POST['logout']))) {
        $_SESSION = [];
        session_destroy();
        redirect('index.php');
    }
}
// Last-resort handling for legacy pages; models always return data or throw.
set_exception_handler(function (Throwable $error): void {
    http_response_code(500);
    echo '<p>The request could not be completed. Please try again later.</p>';
});

function locationErrors(string $description, string $lat, string $lng): array
{
    if (($lat === '') !== ($lng === '')) {
        return ['Enter both coordinates or clear both.'];
    }
    if ($lat !== '' && (!is_numeric($lat) || !is_numeric($lng) || !is_finite((float)$lat) || !is_finite((float)$lng) || abs((float)$lat) > 90 || abs((float)$lng) > 180 || trim($description) === '')) {
        return ['Enter valid coordinates and a written location.'];
    }
    return [];
}

// Match the map's coordinate rules without treating zero as a missing value.
function hasMapPoint(array $record): bool
{
    $lat = $record['latitude'] ?? null;
    $lng = $record['longitude'] ?? null;
    return is_numeric($lat) && is_numeric($lng) && is_finite((float)$lat) && is_finite((float)$lng)
        && abs((float)$lat) <= 90 && abs((float)$lng) <= 180;
}
```

<a id="file-19"></a>

## app/models/Announcement.php

[Open actual source](../../../app/models/Announcement.php)

Methods in source order:

- `__construct(?PDO $pdo = null)`
- `listing(array $filters, bool $resident): array`
- `find(int $id): array`
- `save(?int $id, string $title, string $message, int $published, int $staff): void`

```php
<?php
require_once __DIR__ . '/../../config/db.php';
class AnnouncementModel
{
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }
    public function listing(array $filters, bool $resident): array
    {
        $where = ['archived_at IS NULL'];
        $args = [];
        if ($resident) {
            $where[] = 'is_published=1';
        } elseif ($filters['status'] !== '') {
            $where[] = 'is_published=?';
            $args[] = $filters['status'] === 'published'?1:0;
        }
        if ($filters['q'] !== '') {
            $where[] = "title LIKE ? ESCAPE '!'";
            $args[] = '%' . strtr($filters['q'], ['!' => '!!','%' => '!%','_' => '!_']) . '%';
        }
        if ($filters['date'] !== '') {
            $where[] = 'DATE(created_at)=?';
            $args[] = $filters['date'];
        }
        $stmt = $this->pdo->prepare('SELECT id,title,message,is_published,created_at FROM announcements WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC,id DESC');
        $stmt->execute($args);
        return $stmt->fetchAll();
    }
    public function find(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM announcements WHERE id=?');
        $stmt->execute([$id]);
        return $stmt->fetch()?:[];
    }
    public function save(?int $id, string $title, string $message, int $published, int $staff): void
    {
        if ($id === null) {
            $stmt = $this->pdo->prepare('INSERT INTO announcements (title,message,is_published,created_by,first_published_at) VALUES (?,?,?,?,CASE WHEN ?=1 THEN CURRENT_TIMESTAMP ELSE NULL END)');
            $stmt->execute([$title,$message,$published,$staff,$published]);
        } else {
            $stmt = $this->pdo->prepare('UPDATE announcements SET title=?,message=?,is_published=?,first_published_at=CASE WHEN ?=1 THEN COALESCE(first_published_at,CURRENT_TIMESTAMP) ELSE first_published_at END WHERE id=? AND archived_at IS NULL');
            $stmt->execute([$title,$message,$published,$published,$id]);
        }
    }
}
```

<a id="file-20"></a>

## app/models/Dashboard.php

[Open actual source](../../../app/models/Dashboard.php)

Methods in source order:

- `__construct(?PDO $pdo = null)`
- `rows(string $sql, array $args = []): array`
- `incidents(?int $owner): array`
- `centers(): array`
- `affected(): array`
- `stays(): array`
- `notices(): array`

```php
<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../evacuation-capacity.php';
class DashboardModel
{
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }
    private function rows(string $sql, array $args = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll();
    }
    public function incidents(?int $owner): array
    {
        $sql = 'SELECT status, COUNT(*) AS total FROM incident_reports WHERE archived_at IS NULL';
        $args = [];
        if ($owner !== null) {
            $sql .= ' AND reported_by = ?';
            $args[] = $owner;
        }
        $counts = array_fill_keys(['pending','verified','responding','resolved','rejected'], 0);
        foreach ($this->rows($sql . ' GROUP BY status', $args) as $row) {
            $counts[$row['status']] = (int)$row['total'];
        }
        return $counts;
    }
    public function centers(): array
    {
        $centers = $this->rows("SELECT c.id, c.name, c.address_details, c.status, c.capacity,
            (SELECT COUNT(*) FROM evacuation_records e
             WHERE e.center_id = c.id AND e.checked_out_at IS NULL) AS occupancy
            FROM evacuation_centers c
            WHERE c.archived_at IS NULL AND c.status = 'open'
            ORDER BY c.name, c.id");
        foreach ($centers as $index => $center) {
            $centers[$index]['available'] = availableCapacity($center);
        }
        return $centers;
    }
    public function affected(): array
    {
        return $this->rows("SELECT id,household_code,representative_name FROM households WHERE archived_at IS NULL AND registry_reason IN ('affected','both') ORDER BY household_code");
    }
    public function stays(): array
    {
        return $this->rows('SELECT e.id,e.center_id,c.name AS center_name,COALESCE(m.full_name,e.walkin_name) AS person_name,e.checked_in_at FROM evacuation_records e JOIN evacuation_centers c ON c.id=e.center_id LEFT JOIN household_members m ON m.id=e.household_member_id WHERE e.checked_out_at IS NULL ORDER BY e.id');
    }
    public function notices(): array
    {
        return $this->rows('SELECT id,title,created_at FROM announcements WHERE is_published=1 AND archived_at IS NULL ORDER BY created_at DESC,id DESC');
    }
}
```

<a id="file-21"></a>

## app/models/DisasterSummary.php

[Open actual source](../../../app/models/DisasterSummary.php)

Methods in source order:

- `__construct(?PDO $pdo = null)`
- `rows(string $sql, array $values = []): array`
- `summarize(string $start, string $endExclusive): array`

```php
<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../evacuation-capacity.php';
class DisasterSummaryModel
{
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }
    private function rows(string $sql, array $values = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
        return $stmt->fetchAll();
    }
    public function summarize(string $start, string $endExclusive): array
    {
        // A single read transaction keeps the summaries on the same database snapshot.
        $this->pdo->beginTransaction();
        try {
            $affected = $this->rows("SELECT COUNT(*) AS total FROM households WHERE archived_at IS NULL AND registry_reason IN ('affected','both')")[0]['total'];
            $centers = $this->rows("SELECT c.id, c.name, c.status, c.archived_at, c.capacity,
                COUNT(e.id) AS occupancy
                FROM evacuation_centers c
                LEFT JOIN evacuation_records e ON e.center_id = c.id AND e.checked_out_at IS NULL
                GROUP BY c.id, c.name, c.status, c.archived_at, c.capacity ORDER BY c.name, c.id");
            foreach ($centers as $index => $center) {
                $centers[$index]['available'] = availableCapacity($center);
            }
            $incidents = $this->rows('SELECT incident_type,status,COUNT(*) AS total FROM incident_reports WHERE created_at>=? AND created_at<? GROUP BY incident_type,status', [$start,$endExclusive]);
            $goods = $this->rows('SELECT i.id,i.item_name,i.unit,SUM(d.quantity) AS quantity FROM relief_distributions r JOIN relief_distribution_items d ON d.distribution_id=r.id JOIN relief_items i ON i.id=d.item_id WHERE r.distributed_at>=? AND r.distributed_at<? GROUP BY i.id,i.item_name,i.unit ORDER BY i.item_name,i.unit,i.id', [$start,$endExclusive]);
            $distributions = $this->rows('SELECT COUNT(*) AS events,COUNT(DISTINCT household_id) AS households FROM relief_distributions WHERE distributed_at>=? AND distributed_at<?', [$start,$endExclusive])[0];
            $this->pdo->commit();
            return [
                'affected' => (int)$affected,
                'centers' => $centers,
                'incidents' => $incidents,
                'goods' => $goods,
                'distributions' => $distributions
            ];
        } catch (Throwable $error) {
            $this->pdo->rollBack();
            throw $error;
        }
    }
}
```

<a id="file-22"></a>

## app/models/Evacuation.php

[Open actual source](../../../app/models/Evacuation.php)

Methods in source order:

- `__construct(?PDO $pdo = null)`
- `rows(string $sql, array $values = []): array`
- `centers(string $search): array`
- `center(int $id): array`
- `lockCenter(int $id): array`
- `occupancy(int $id): int`
- `saveCenter(?int $id, array $data): void`
- `households(): array`
- `householdMembers(int $householdId): array`
- `checkinHousehold(int $centerId, int $householdId, array $memberIds, int $staff): void`
- `walkins(): array`
- `records(int $id): array`
- `checkin(int $centerId, string $kind, int $personId, array $data, int $staff): void`
- `checkout(int $centerId, int $recordId, int $staff): void`

```php
<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../evacuation-capacity.php';
class EvacuationModel
{
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }
    private function rows(string $sql, array $values = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
        return $stmt->fetchAll();
    }
    public function centers(string $search): array
    {
        $term = '%' . strtr($search, ['!' => '!!','%' => '!%','_' => '!_']) . '%';
        $centers = $this->rows("SELECT c.*, (SELECT COUNT(*) FROM evacuation_records e WHERE e.center_id=c.id AND e.checked_out_at IS NULL) AS occupancy FROM evacuation_centers c WHERE c.archived_at IS NULL AND (c.name LIKE ? ESCAPE '!' OR c.address_details LIKE ? ESCAPE '!') ORDER BY c.name", [$term,$term]);
        foreach ($centers as $index => $center) {
            $centers[$index]['available'] = availableCapacity($center);
        }
        return $centers;
    }
    public function center(int $id): array
    {
        $rows = $this->rows('SELECT c.*, (SELECT COUNT(*) FROM evacuation_records e WHERE e.center_id=c.id AND e.checked_out_at IS NULL) AS occupancy FROM evacuation_centers c WHERE c.id=?', [$id]);
        if (!$rows) {
            return [];
        }
        $center = $rows[0];
        $center['available'] = availableCapacity($center);
        return $center;
    }
    private function lockCenter(int $id): array
    {
        $rows = $this->rows('SELECT * FROM evacuation_centers WHERE id=? FOR UPDATE', [$id]);
        if (!$rows) {
            throw new DomainException('Center not found.');
        }
        return $rows[0];
    }
    private function occupancy(int $id): int
    {
        // Current locking read, including changes committed while waiting for the center lock.
        return count($this->rows('SELECT id FROM evacuation_records WHERE center_id=? AND checked_out_at IS NULL FOR UPDATE', [$id]));
    }
    public function saveCenter(?int $id, array $data): void
    {
        $this->pdo->beginTransaction();
        try {
            if ($id) {
                $editing = $this->lockCenter($id);
                if ($editing['archived_at'] !== null) {
                    throw new DomainException('Archived centers cannot be edited.');
                }
                $count = $this->occupancy($id);
                if ($data['capacity'] < $count) {
                    throw new DomainException('Capacity cannot be less than current occupancy.');
                }
                if ($data['status'] === 'closed' && $count > 0) {
                    throw new DomainException('Check out all evacuees before closing this center.');
                }
                $stmt = $this->pdo->prepare("UPDATE evacuation_centers SET latitude=?,longitude=?,name=?, address_details=?, capacity=?, status=? WHERE id=?");
                $stmt->execute([$data['latitude'],$data['longitude'],$data['name'],$data['address_details'],$data['capacity'],$data['status'],$id]);
            } else {
                $stmt = $this->pdo->prepare('INSERT INTO evacuation_centers (latitude,longitude,name,address_details,capacity,status) VALUES (?,?,?,?,?,?)');
                $stmt->execute([$data['latitude'],$data['longitude'],$data['name'],$data['address_details'],$data['capacity'],$data['status']]);
            }
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    public function households(): array
    {
        return $this->rows("SELECT h.id, h.household_code, h.representative_name,
            COUNT(m.id) AS member_count
            FROM households h
            LEFT JOIN household_members m ON m.household_id = h.id AND m.archived_at IS NULL
            WHERE h.archived_at IS NULL
            GROUP BY h.id, h.household_code, h.representative_name
            ORDER BY h.household_code");
    }

    public function householdMembers(int $householdId): array
    {
        return $this->rows('SELECT m.id, m.full_name FROM household_members m
            JOIN households h ON h.id = m.household_id
            WHERE h.id = ? AND h.archived_at IS NULL AND m.archived_at IS NULL
            ORDER BY m.id', [$householdId]);
    }

    public function checkinHousehold(int $centerId, int $householdId, array $memberIds, int $staff): void
    {
        if (!$memberIds) {
            throw new DomainException('Select at least one arriving member.');
        }
        foreach ($memberIds as $memberId) {
            if (!is_scalar($memberId) || !filter_var($memberId, FILTER_VALIDATE_INT) || $memberId < 1) {
                throw new DomainException('Invalid selected member.');
            }
        }
        $memberIds = array_map('intval', $memberIds);
        if (count($memberIds) !== count(array_unique($memberIds))) {
            throw new DomainException('A member was selected more than once.');
        }
        sort($memberIds);
        $this->pdo->beginTransaction();
        try {
            $center = $this->lockCenter($centerId);
            if ($center['status'] !== 'open' || $center['archived_at'] !== null) {
                throw new DomainException('This center is closed or archived.');
            }
            $household = $this->rows('SELECT id FROM households
                WHERE id = ? AND archived_at IS NULL FOR UPDATE', [$householdId]);
            if (!$household) {
                throw new DomainException('Active household not found.');
            }
            $occupancy = $this->occupancy($centerId);
            if ($occupancy + count($memberIds) > (int)$center['capacity']) {
                throw new DomainException('Not enough capacity for all selected members. Nobody was checked in.');
            }
            foreach ($memberIds as $memberId) {
                $member = $this->rows('SELECT id, full_name FROM household_members
                    WHERE id = ? AND household_id = ? AND archived_at IS NULL FOR UPDATE', [$memberId, $householdId]);
                if (!$member) {
                    throw new DomainException('A selected member is no longer active in this household. Nobody was checked in.');
                }
                $active = $this->rows('SELECT id FROM evacuation_records
                    WHERE household_member_id = ? AND checked_out_at IS NULL FOR UPDATE', [$memberId]);
                if ($active) {
                    throw new DomainException($member[0]['full_name'] . ' already has an active stay. Nobody was checked in.');
                }
            }
            $stmt = $this->pdo->prepare('INSERT INTO evacuation_records
                (center_id, household_member_id, recorded_by) VALUES (?, ?, ?)');
            foreach ($memberIds as $memberId) {
                $stmt->execute([$centerId, $memberId, $staff]);
            }
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function walkins(): array
    {
        return $this->rows('SELECT * FROM evacuation_walkins WHERE archived_at IS NULL ORDER BY full_name,id');
    }
    public function records(int $id): array
    {
        return $this->rows('SELECT e.*, COALESCE(m.full_name,e.walkin_name) AS person_name, h.household_code, w.age AS walkin_age,w.address_details AS walkin_address,u.full_name AS encoder, o.full_name AS checkout_staff FROM evacuation_records e LEFT JOIN household_members m ON m.id=e.household_member_id LEFT JOIN households h ON h.id=m.household_id LEFT JOIN evacuation_walkins w ON w.id=e.walkin_id JOIN users u ON u.id=e.recorded_by LEFT JOIN users o ON o.id=e.checked_out_by WHERE e.center_id=? ORDER BY (e.checked_out_at IS NULL) DESC,e.id DESC', [$id]);
    }
    public function checkin(int $centerId, string $kind, int $personId, array $data, int $staff): void
    {
        $this->pdo->beginTransaction();
        try {
            $center = $this->lockCenter($centerId);
            if ($center['status'] !== 'open' || $center['archived_at'] !== null) {
                throw new DomainException('This center is closed.');
            }
            if ($this->occupancy($centerId) >= (int)$center['capacity']) {
                throw new DomainException('This center is full.');
            }
            $memberId = null;
            $walkinId = null;
            $name = null;
            $phone = null;
            if ($kind === 'member') {
                $member = $this->rows('SELECT m.id FROM household_members m JOIN households h ON h.id=m.household_id WHERE m.id=? AND m.archived_at IS NULL AND h.archived_at IS NULL FOR UPDATE', [$personId]);
                if (!$member) {
                    throw new DomainException('Registered member not found.');
                } $memberId = $personId;
            } else {
                if ($kind === 'new_walkin') {
                    $normalized = array_map(fn ($value) => strtolower(preg_replace('/\s+/u', ' ', trim((string)$value))), [$data['full_name'],$data['age'],$data['address_details'],preg_replace('/[^0-9]/', '', $data['contact_phone'])]);
                    $key = hash('sha256', json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
                    $stmt = $this->pdo->prepare('INSERT INTO evacuation_walkins (full_name,age,address_details,contact_phone,identity_key) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)');
                    $stmt->execute([$data['full_name'],$data['age'],$data['address_details'],$data['contact_phone'],$key]);
                    $personId = (int)$this->pdo->lastInsertId();
                }
                $people = $this->rows('SELECT * FROM evacuation_walkins WHERE id=? AND archived_at IS NULL FOR UPDATE', [$personId]);
                if (!$people) {
                    throw new DomainException('Walk-in not found.');
                }
                $walkinId = $personId;
                $name = $people[0]['full_name'];
                $phone = $people[0]['contact_phone'];
                // Legacy walk-ins have no reusable ID; block matching active names/contacts for staff reconciliation.
                $legacy = $this->rows('SELECT id FROM evacuation_records WHERE household_member_id IS NULL AND walkin_id IS NULL AND checked_out_at IS NULL AND walkin_name=? AND walkin_contact=? FOR UPDATE', [$name,$phone]);
                if ($legacy) {
                    throw new DomainException('A matching legacy walk-in is already checked in. Review the existing stay first.');
                }
            }
            $stmt = $this->pdo->prepare('INSERT INTO evacuation_records (center_id,household_member_id,walkin_id,walkin_name,walkin_contact,recorded_by) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$centerId,$memberId,$walkinId,$name,$phone,$staff]);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    public function checkout(int $centerId, int $recordId, int $staff): void
    {
        $this->pdo->beginTransaction();
        try {
            $this->lockCenter($centerId);
            $stmt = $this->pdo->prepare('UPDATE evacuation_records SET checked_out_at=CURRENT_TIMESTAMP,checked_out_by=? WHERE id=? AND center_id=? AND checked_out_at IS NULL');
            $stmt->execute([$staff,$recordId,$centerId]);
            if ($stmt->rowCount() !== 1) {
                throw new DomainException('Stay not found in this center or already checked out.');
            }
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
```

<a id="file-23"></a>

## app/models/Household.php

[Open actual source](../../../app/models/Household.php)

Methods in source order:

- `__construct(?PDO $pdo = null)`
- `getAll(string $search): array`
- `findById(int $id)`
- `save(array $data, ?int $id): int`
- `members(int $householdId): array`
- `findMember(int $id, int $householdId)`
- `saveMember(int $householdId, ?int $id, array $data): void`

```php
<?php
require_once __DIR__ . '/../../config/db.php';
class HouseholdModel
{
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }
    public function getAll(string $search): array
    {
        $term = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search) . '%';
        $sql = "SELECT h.*, (SELECT COUNT(*) FROM household_members m WHERE m.household_id = h.id AND m.archived_at IS NULL) AS member_count
                FROM households h WHERE h.archived_at IS NULL AND (h.household_code LIKE ? ESCAPE '!'
                OR h.representative_name LIKE ? ESCAPE '!' OR h.purok LIKE ? ESCAPE '!'
                OR h.address_details LIKE ? ESCAPE '!' OR h.contact_phone LIKE ? ESCAPE '!')
                ORDER BY h.household_code";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_fill(0, 5, $term));
        return $stmt->fetchAll();
    }
    public function findById(int $id)
    {
        $stmt = $this->pdo->prepare('SELECT h.*, (SELECT COUNT(*) FROM household_members m WHERE m.household_id=h.id AND m.archived_at IS NULL) AS member_count FROM households h WHERE h.id=?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
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
    public function members(int $householdId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM household_members WHERE household_id=? AND archived_at IS NULL ORDER BY full_name,id');
        $stmt->execute([$householdId]);
        return $stmt->fetchAll();
    }
    public function findMember(int $id, int $householdId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM household_members WHERE id=? AND household_id=?');
        $stmt->execute([$id, $householdId]);
        return $stmt->fetch();
    }
    public function saveMember(int $householdId, ?int $id, array $data): void
    {
        if (!empty($this->findById($householdId)['archived_at']) || ($id !== null && !empty($this->findMember($id, $householdId)['archived_at']))) {
            throw new DomainException('Archived records cannot be edited.');
        }
        if ($id === null) {
            $stmt = $this->pdo->prepare('INSERT INTO household_members (household_id,full_name,age,notes) VALUES (?,?,?,?)');
            $stmt->execute([$householdId,$data['full_name'],$data['age'],$data['notes']]);
        } else {
            $stmt = $this->pdo->prepare('UPDATE household_members SET full_name=?,age=?,notes=? WHERE id=? AND household_id=?');
            $stmt->execute([$data['full_name'],$data['age'],$data['notes'],$id,$householdId]);
        }
    }
}
```

<a id="file-24"></a>

## app/models/Map.php

[Open actual source](../../../app/models/Map.php)

Methods in source order:

- `__construct(?PDO $pdo = null)`
- `rows(string $sql): array`
- `point(string $lat, string $lng): string`
- `publicMap(): array`
- `reliefLocation(int $id): array`
- `centers(): array`

```php
<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../evacuation-capacity.php';
class MapModel
{
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }
    private function rows(string $sql): array
    {
        return $this->pdo->query($sql)->fetchAll();
    }
    private function point(string $lat, string $lng): string
    {
        return "$lat IS NOT NULL AND $lng IS NOT NULL AND $lat BETWEEN -90 AND 90 AND $lng BETWEEN -180 AND 180";
    }
    public function publicMap(): array
    {
        // Explicit allowlists: never select private incident fields for the resident response.
        return [
            'incidents' => $this->rows("SELECT id,incident_type,latitude,longitude,status,DATE(created_at) AS report_date FROM incident_reports WHERE archived_at IS NULL AND status IN ('verified','responding','resolved') AND " . $this->point('latitude', 'longitude')),
            'centers' => $this->centers(),
            'relief_locations' => $this->rows("SELECT id,name,address_details,latitude,longitude,location_type FROM relief_locations WHERE archived_at IS NULL AND location_type IN ('distribution','both') AND " . $this->point('latitude', 'longitude'))
        ];
    }
    public function reliefLocation(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT id,name,address_details,latitude,longitude,location_type,archived_at FROM relief_locations WHERE id=?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: [];
    }
    private function centers(): array
    {
        $centers = $this->rows("SELECT c.id, c.name, c.address_details, c.latitude, c.longitude,
            c.status, c.capacity, COUNT(e.id) AS occupancy
            FROM evacuation_centers c
            LEFT JOIN evacuation_records e ON e.center_id = c.id AND e.checked_out_at IS NULL
            WHERE c.archived_at IS NULL AND c.status = 'open' AND " . $this->point('c.latitude', 'c.longitude') . "
            GROUP BY c.id, c.name, c.address_details, c.latitude, c.longitude, c.status, c.capacity");
        foreach ($centers as $index => $center) {
            $centers[$index]['available'] = availableCapacity($center);
        }
        return $centers;
    }
}
```

<a id="file-25"></a>

## app/models/RecordActions.php

[Open actual source](../../../app/models/RecordActions.php)

Methods in source order:

- `__construct(?PDO $pdo = null)`
- `query(string $sql, array $args = []): array`
- `spec(string $type): array`
- `listing(string $type, string $search, int $page): array`
- `selected(string $type, array $ids, bool $lock = false): array`
- `linked(string $table, string $column, int $id, string $extra = ''): bool`
- `removalMode(string $type, array $row): string`
- `deleteSelected(array $selection): void`

```php
<?php
require_once __DIR__ . '/../../config/db.php';
class RecordActionsModel
{
    // SQL identifiers come exclusively from this fixed application allowlist.
    public const TYPES = [
        'households' => ['households','household_code','Households','resources/views/admin/household.php?id='],
        'members' => ['household_members','full_name','Household members',''],
        'incidents' => ['incident_reports','location_description','Incidents','resources/views/incidents/view.php?id='],
        'teams' => ['responders','team_name','Response teams','resources/views/admin/response/teams.php?edit='],
        'centers' => ['evacuation_centers','name','Evacuation centers','resources/views/admin/evacuation/center.php?id='],
        'walkins' => ['evacuation_walkins','full_name','Walk-ins',''],
        'goods' => ['relief_items','item_name','Relief goods','resources/views/admin/relief/inventory.php?edit='],
        'locations' => ['relief_locations','name','Relief locations','resources/views/admin/relief/location.php?id='],
        'announcements' => ['announcements','title','Announcements','resources/views/admin/announcement/announcement.update.php?id=']
    ];
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }
    private function query(string $sql, array $args = []): array
    {
        $s = $this->pdo->prepare($sql);
        $s->execute($args);
        return $s->fetchAll();
    }
    private function spec(string $type): array
    {
        if (!isset(self::TYPES[$type]))throw new DomainException('Unsupported record type.');
        return self::TYPES[$type];
    }
    public function listing(string $type, string $search, int $page): array
    {
        $types = $type === 'all' ? self::TYPES : [$type => $this->spec($type)];
        $queries = [];
        $values = [];
        $term = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        foreach ($types as $key => $spec) {
            [$table, $label] = $spec;
            $state = $key === 'teams' ? 'is_active=1' : 'archived_at IS NULL';
            $queries[] = "SELECT id, $label AS label, '$key' AS record_type FROM $table WHERE $state AND ($label LIKE ? ESCAPE '!' OR CAST(id AS CHAR) LIKE ? ESCAPE '!')";
            $values[] = $term;
            $values[] = $term;
        }
        $offset = ($page - 1) * 50;
        return $this->query(implode(' UNION ALL ', $queries) . " ORDER BY record_type, id LIMIT 50 OFFSET $offset", $values);
    }
    public function selected(string $type, array $ids, bool $lock = false): array
    {
        [$table] = $this->spec($type);
        sort($ids, SORT_NUMERIC);
        $rows = $this->query("SELECT * FROM $table WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ') ORDER BY id' . ($lock?' FOR UPDATE':''), $ids);
        if (count($rows) !== count($ids))throw new DomainException('A selected record no longer exists. Reload the list.');
        return $rows;
    }
    private function linked(string $table, string $column, int $id, string $extra = ''): bool
    {
        // All callers use literal identifiers and conditions defined below.
        return (bool)$this->query("SELECT id FROM $table WHERE $column=? $extra LIMIT 1 FOR UPDATE", [$id]);
    }
    private function removalMode(string $type, array $row): string
    {
        $id = (int)$row['id'];
        $guide = '';
        if ($type === 'goods' && (int)$row['quantity_on_hand'] !== 0) {
            $guide = 'Open Relief goods → Stock movements. Record the actual distribution or stock-out of remaining supplies, then retry when stock is zero.';
        }
        $stayColumn = ['members' => 'household_member_id', 'walkins' => 'walkin_id', 'centers' => 'center_id'];
        if (isset($stayColumn[$type]) && $this->linked('evacuation_records', $stayColumn[$type], $id, 'AND checked_out_at IS NULL')) {
            $guide = 'Open Evacuation centers → the occupied center. Check out the affected people when they have actually left, then retry.';
        }
        if ($type === 'households') {
            $members = $this->query('SELECT id FROM household_members WHERE household_id=? FOR UPDATE', [$id]);
            foreach ($members as $member) {
                if ($this->linked('evacuation_records', 'household_member_id', (int)$member['id'], 'AND checked_out_at IS NULL')) {
                    $guide = 'Open Evacuation centers and check out this household’s active occupants when they have left, then retry.';
                }
            }
        }
        if ($type === 'incidents' && (in_array($row['status'], ['verified','responding'], true) || $this->linked('incident_assignments', 'incident_id', $id, "AND status IN ('assigned','on_scene')"))) {
            $guide = 'Open the incident → Response management. Complete or cancel active assignments, then resolve or reject the incident before retrying.';
        }
        if ($type === 'teams' && $this->linked('incident_assignments', 'responder_id', $id, "AND status IN ('assigned','on_scene')")) {
            $guide = 'Open the assigned incidents → Response management. Complete or cancel this team’s active assignments, then retry.';
        }
        if ($guide !== '') {
            throw new DomainException(self::TYPES[$type][2] . ' #' . $id . ': ' . $guide . ' No selected records were changed.');
        }
        $links = [
            'households' => [['household_members','household_id'],['relief_distributions','household_id']],
            'members' => [['evacuation_records','household_member_id']],
            'incidents' => [['incident_images','incident_id'],['incident_assignments','incident_id'],['response_updates','incident_id']],
            'teams' => [['incident_assignments','responder_id']],
            'centers' => [['evacuation_records','center_id']],
            'walkins' => [['evacuation_records','walkin_id']],
            'goods' => [['relief_stock_movements','item_id'],['relief_distribution_items','item_id']],
            'locations' => [['relief_distributions','relief_location_id']]
        ];
        foreach ($links[$type] ?? [] as [$table, $column]) {
            if ($this->linked($table, $column, $id)) return 'hide';
        }
        if ($type === 'announcements' && ($row['first_published_at'] !== null || $row['is_published'] || $row['publication_history_unknown'])) return 'hide';
        return 'delete';
    }
    public function deleteSelected(array $selection): void
    {
        $this->pdo->beginTransaction();
        try {
            // Check the entire batch before deleting anything, including mixed types.
            $modes = [];
            ksort($selection);
            foreach ($selection as $type => $ids) {
                foreach ($this->selected($type, $ids, true) as $row) {
                    $modes[$type][(int)$row['id']] = $this->removalMode($type, $row);
                }
            }
            foreach ($selection as $type => $ids) {
                [$table] = $this->spec($type);
                foreach ($ids as $id) {
                    $sql = "DELETE FROM $table WHERE id=?";
                    if ($modes[$type][$id] === 'hide') {
                        $set = $type === 'teams' ? 'is_active=0' : 'archived_at=CURRENT_TIMESTAMP';
                        if ($type === 'centers') $set .= ",status='closed'";
                        if ($type === 'announcements') $set .= ',is_published=0';
                        $sql = "UPDATE $table SET $set WHERE id=?";
                        if ($type === 'households') {
                            $stmt = $this->pdo->prepare('UPDATE household_members SET archived_at=COALESCE(archived_at,CURRENT_TIMESTAMP) WHERE household_id=?');
                            $stmt->execute([$id]);
                        }
                    }
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([$id]);
                }
            }
            $this->pdo->commit();
        } catch (Throwable $error) {
            $this->pdo->rollBack();
            throw $error;
        }
    }
}
```

<a id="file-26"></a>

## app/models/Relief.php

[Open actual source](../../../app/models/Relief.php)

Methods in source order:

- `__construct(?PDO $pdo = null)`
- `rows(string $sql, array $args = []): array`
- `term(string $q): string`
- `items(string $q = ''): array`
- `item(int $id): array`
- `saveItem(?int $id, string $name, string $unit): void`
- `movement(int $item, string $type, int $quantity, ?int $distribution, int $staff, string $notes): void`
- `stock(int $id, string $type, int $quantity, int $staff, string $notes): void`
- `movements(string $q, string $type, string $date): array`
- `households(): array`
- `distribute(int $household, string $receiver, string $notes, array $goods, int $staff): int`
- `distributions(string $q): array`
- `distribution(int $id): array`
- `distributedGoods(int $id): array`

```php
<?php
require_once __DIR__ . '/../../config/db.php';
class ReliefModel
{
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }
    private function rows(string $sql, array $args = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll();
    }
    private function term(string $q): string
    {
        return '%' . strtr($q, ['!' => '!!','%' => '!%','_' => '!_']) . '%';
    }
    public function items(string $q = ''): array
    {
        return $this->rows("SELECT * FROM relief_items WHERE archived_at IS NULL AND (item_name LIKE ? ESCAPE '!' OR unit LIKE ? ESCAPE '!') ORDER BY item_name,unit", [$this->term($q),$this->term($q)]);
    }
    public function item(int $id): array
    {
        return $this->rows('SELECT * FROM relief_items WHERE id=?', [$id])[0] ?? [];
    }
    public function saveItem(?int $id, string $name, string $unit): void
    {
        $this->pdo->beginTransaction();
        try {
            if ($id) {
                $old = $this->rows('SELECT * FROM relief_items WHERE id=? FOR UPDATE', [$id])[0] ?? null;
                if (!$old || $old['archived_at'] !== null) {
                    throw new DomainException('Item not found.');
                }
                if ($old['unit'] !== $unit && $this->rows('SELECT id FROM relief_stock_movements WHERE item_id=? LIMIT 1', [$id])) {
                    throw new DomainException('An item with stock history must keep its unit. Create a separate item for a different unit.');
                }
                $stmt = $this->pdo->prepare('UPDATE relief_items SET item_name=?,unit=? WHERE id=?');
                $stmt->execute([$name,$unit,$id]);
            } else {
                $stmt = $this->pdo->prepare('INSERT INTO relief_items (item_name,unit) VALUES (?,?)');
                $stmt->execute([$name,$unit]);
            }
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    private function movement(int $item, string $type, int $quantity, ?int $distribution, int $staff, string $notes): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO relief_stock_movements (item_id,movement_type,quantity,distribution_id,recorded_by,notes) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$item,$type,$quantity,$distribution,$staff,$notes]);
    }
    public function stock(int $id, string $type, int $quantity, int $staff, string $notes): void
    {
        if (!in_array($type, ['stock_in','stock_out'], true) || $quantity < 1 || $quantity > 1000000000) {
            throw new DomainException('Choose a valid movement and positive whole quantity.');
        }
        $this->pdo->beginTransaction();
        try {
            $item = $this->rows('SELECT * FROM relief_items WHERE id=? FOR UPDATE', [$id])[0] ?? null;
            if (!$item || $item['archived_at'] !== null) {
                throw new DomainException('Item not found.');
            }
            $next = (int)$item['quantity_on_hand'];
            if ($type === 'stock_in') {
                $next += $quantity;
            } else {
                $next -= $quantity;
            }
            if ($next < 0) {
                throw new DomainException('Stock-out exceeds available stock.');
            }
            if ($next > 4294967295) {
                throw new DomainException('Resulting stock exceeds the supported quantity.');
            }
            $stmt = $this->pdo->prepare('UPDATE relief_items SET quantity_on_hand=? WHERE id=?');
            $stmt->execute([$next,$id]);
            $this->movement($id, $type, $quantity, null, $staff, $notes);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    public function movements(string $q, string $type, string $date): array
    {
        $sql = "SELECT m.*,i.item_name,i.unit,u.full_name FROM relief_stock_movements m JOIN relief_items i ON i.id=m.item_id JOIN users u ON u.id=m.recorded_by WHERE (i.item_name LIKE ? ESCAPE '!' OR m.notes LIKE ? ESCAPE '!' OR u.full_name LIKE ? ESCAPE '!')";
        $args = array_fill(0, 3, $this->term($q));
        if ($type !== '') {
            $sql .= ' AND m.movement_type=?';
            $args[] = $type;
        }
        if ($date !== '') {
            $sql .= ' AND DATE(m.created_at)=?';
            $args[] = $date;
        }
        return $this->rows($sql . ' ORDER BY m.id DESC', $args);
    }
    public function households(): array
    {
        return $this->rows('SELECT id,household_code,representative_name FROM households WHERE archived_at IS NULL ORDER BY household_code');
    }
    public function distribute(int $household, string $receiver, string $notes, array $goods, int $staff): int
    {
        if (!$goods) {
            throw new DomainException('Choose at least one good.');
        }
        foreach ($goods as $id => $quantity) {
            if (!is_int($id) || $id < 1 || !is_int($quantity) || $quantity < 1 || $quantity > 1000000000) {
                throw new DomainException('Every good must have a valid ID and positive whole quantity.');
            }
        }
        $this->pdo->beginTransaction();
        try {
            if (!$this->rows('SELECT id FROM households WHERE id=? AND archived_at IS NULL FOR UPDATE', [$household])) {
                throw new DomainException('Select a registered household.');
            }
            // Lock in the same item order for every distribution to reduce deadlocks.
            ksort($goods, SORT_NUMERIC);
            foreach ($goods as $id => $quantity) {
                $item = $this->rows('SELECT * FROM relief_items WHERE id=? FOR UPDATE', [$id])[0] ?? null;
                if (!$item || $item['archived_at'] !== null) {
                    throw new DomainException('One selected item no longer exists.');
                }
                if ($quantity > (int)$item['quantity_on_hand']) {
                    throw new DomainException('Insufficient stock for ' . $item['item_name'] . '. No goods were distributed.');
                }
            }
            $stmt = $this->pdo->prepare('INSERT INTO relief_distributions (household_id,received_by,distributed_by,notes) VALUES (?,?,?,?)');
            $stmt->execute([$household,$receiver,$staff,$notes]);
            $distribution = (int)$this->pdo->lastInsertId();
            foreach ($goods as $id => $quantity) {
                $stmt = $this->pdo->prepare('UPDATE relief_items SET quantity_on_hand=quantity_on_hand-? WHERE id=?');
                $stmt->execute([$quantity,$id]);
                $stmt = $this->pdo->prepare('INSERT INTO relief_distribution_items (distribution_id,item_id,quantity) VALUES (?,?,?)');
                $stmt->execute([$distribution,$id,$quantity]);
                $this->movement($id, 'distribution', $quantity, $distribution, $staff, $notes);
            }
            $this->pdo->commit();
            return $distribution;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    public function distributions(string $q): array
    {
        return $this->rows("SELECT d.*,h.household_code,u.full_name FROM relief_distributions d JOIN households h ON h.id=d.household_id JOIN users u ON u.id=d.distributed_by WHERE h.household_code LIKE ? ESCAPE '!' OR d.received_by LIKE ? ESCAPE '!' ORDER BY d.id DESC", [$this->term($q),$this->term($q)]);
    }
    public function distribution(int $id): array
    {
        return $this->rows('SELECT d.*,h.household_code,u.full_name FROM relief_distributions d JOIN households h ON h.id=d.household_id JOIN users u ON u.id=d.distributed_by WHERE d.id=?', [$id])[0] ?? [];
    }
    public function distributedGoods(int $id): array
    {
        return $this->rows('SELECT d.quantity,i.item_name,i.unit FROM relief_distribution_items d JOIN relief_items i ON i.id=d.item_id WHERE d.distribution_id=? ORDER BY d.id', [$id]);
    }
}
```

<a id="file-27"></a>

## app/models/ReliefLocation.php

[Open actual source](../../../app/models/ReliefLocation.php)

Methods in source order:

- `__construct(?PDO $pdo = null)`
- `all(): array`
- `find(int $id): array`
- `save(?int $id, array $data): void`

```php
<?php
require_once __DIR__ . '/../../config/db.php';
class ReliefLocationModel
{
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }
    public function all(): array
    {
        return $this->pdo->query('SELECT * FROM relief_locations WHERE archived_at IS NULL ORDER BY name')->fetchAll();
    }
    public function find(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM relief_locations WHERE id=?');
        $stmt->execute([$id]);
        return $stmt->fetch()?:[];
    }
    public function save(?int $id, array $data): void
    {
        $values = [$data['name'],$data['location_type'],$data['address_details'],$data['latitude'],$data['longitude']];
        if ($id) {
            $stmt = $this->pdo->prepare("UPDATE relief_locations SET name=?,location_type=?,address_details=?,latitude=?,longitude=? WHERE id=? AND archived_at IS NULL");
            $values[] = $id;
        } else {
            $stmt = $this->pdo->prepare('INSERT INTO relief_locations(name,location_type,address_details,latitude,longitude) VALUES(?,?,?,?,?)');
        }
        $stmt->execute($values);
    }
}
```

<a id="file-28"></a>

## app/models/Report.php

[Open actual source](../../../app/models/Report.php)

Methods in source order:

- `__construct(?PDO $pdo = null)`
- `listing(array $filters, ?int $owner): array`
- `find(int $id, ?int $owner)`
- `images(int $id): array`
- `image(int $id, ?int $owner)`
- `history(int $id): array`
- `create(array $data, ?array $image): int`
- `updateLocation(int $id, string $description, ?string $lat, ?string $lng, string $notes, int $staff): void`
- `review(int $id, string $status, string $notes, int $staff): void`

```php
<?php
require_once __DIR__ . '/../../config/db.php';
class ReportModel
{
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }
    public function listing(array $filters, ?int $owner): array
    {
        $where = ['archived_at IS NULL'];
        $values = [];
        if ($owner !== null) {
            $where[] = 'reported_by = ?';
            $values[] = $owner;
        }
        foreach (['incident_type', 'status'] as $field) {
            if ($filters[$field] !== '') {
                $where[] = "$field = ?";
                $values[] = $filters[$field];
            }
        }
        if ($filters['date'] !== '') {
            $where[] = 'DATE(created_at) = ?';
            $values[] = $filters['date'];
        }
        foreach (['location' => 'location_description', 'search' => 'description'] as $key => $field) {
            if ($filters[$key] !== '') {
                $where[] = "$field LIKE ? ESCAPE '!'";
                $values[] = '%' . strtr($filters[$key], ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            }
        }
        $stmt = $this->pdo->prepare('SELECT * FROM incident_reports WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC, id DESC');
        $stmt->execute($values);
        return $stmt->fetchAll();
    }
    public function find(int $id, ?int $owner)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM incident_reports WHERE id = ?' . ($owner !== null ? ' AND reported_by = ?' : ''));
        $stmt->execute($owner !== null ? [$id, $owner] : [$id]);
        return $stmt->fetch();
    }
    public function images(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT id FROM incident_images WHERE incident_id = ? ORDER BY id');
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }
    public function image(int $id, ?int $owner)
    {
        $stmt = $this->pdo->prepare('SELECT i.* FROM incident_images i JOIN incident_reports r ON r.id = i.incident_id WHERE i.id = ?' . ($owner !== null ? ' AND r.reported_by = ?' : ''));
        $stmt->execute($owner !== null ? [$id, $owner] : [$id]);
        return $stmt->fetch();
    }
    public function history(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT action_taken, from_status, to_status, created_at FROM response_updates WHERE incident_id = ? ORDER BY id');
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }
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
    public function updateLocation(int $id, string $description, ?string $lat, ?string $lng, string $notes, int $staff): void
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('SELECT location_description,latitude,longitude,status,archived_at FROM incident_reports WHERE id=? FOR UPDATE');
            $stmt->execute([$id]);
            $old = $stmt->fetch();
            if (!$old || $old['archived_at'] !== null) {
                throw new DomainException('Archived or missing incidents cannot be edited.');
            }
            $stmt = $this->pdo->prepare('UPDATE incident_reports SET location_description=?,latitude=?,longitude=? WHERE id=?');
            $stmt->execute([$description,$lat,$lng,$id]);
            $previousPoint = '[' . ($old['latitude'] ?? 'none') . ', ' . ($old['longitude'] ?? 'none') . ']';
            $updatedPoint = '[' . ($lat ?? 'none') . ', ' . ($lng ?? 'none') . ']';
            $action = 'Location correction: ' . $notes;
            $action .= ' | Previous: ' . $old['location_description'] . ' ' . $previousPoint;
            $action .= ' | Updated: ' . $description . ' ' . $updatedPoint;
            $stmt = $this->pdo->prepare('INSERT INTO response_updates(incident_id,updated_by,action_taken,from_status,to_status) VALUES(?,?,?,?,?)');
            $stmt->execute([$id,$staff,$action,$old['status'],$old['status']]);
            $this->pdo->commit();
        } catch (Throwable $error) {
            $this->pdo->rollBack();
            throw $error;
        }
    }
    public function review(int $id, string $status, string $notes, int $staff): void
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('SELECT status FROM incident_reports WHERE id = ? AND archived_at IS NULL FOR UPDATE');
            $stmt->execute([$id]);
            $old = $stmt->fetchColumn();
            if ($old === false) {
                throw new RuntimeException('Incident not found.');
            }
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM incident_assignments WHERE incident_id=? AND status IN ('assigned','on_scene')");
            $stmt->execute([$id]);
            $active = (int)$stmt->fetchColumn();
            if ($active > 0 && $status !== 'responding') {
                throw new DomainException('Complete or cancel all active team assignments before changing the incident out of responding.');
            }
            if ($active === 0 && $status === 'responding' && $old !== 'responding') {
                throw new DomainException('Verify the incident, then assign a team to start responding.');
            }
            $stmt = $this->pdo->prepare("UPDATE incident_reports SET status=?, reviewed_by=?, reviewed_at=CURRENT_TIMESTAMP, review_notes=? WHERE id=?");
            $stmt->execute([$status, $staff, $notes, $id]);
            $stmt = $this->pdo->prepare('INSERT INTO response_updates (incident_id,updated_by,action_taken,from_status,to_status) VALUES (?,?,?,?,?)');
            $stmt->execute([$id, $staff, $notes, $old, $status]);
            $this->pdo->commit();
        } catch (Throwable $error) {
            $this->pdo->rollBack();
            throw $error;
        }
    }
}
```

<a id="file-29"></a>

## app/models/Response.php

[Open actual source](../../../app/models/Response.php)

Methods in source order:

- `__construct(?PDO $pdo = null)`
- `teams(): array`
- `team(int $id)`
- `saveTeam(?int $id, string $name, string $phone, int $active): void`
- `incident(int $id)`
- `assignments(int $id): array`
- `history(int $id): array`
- `record(int $incidentId, ?int $teamId, ?int $assignmentId, string $status, string $notes, int $staff): void`

```php
<?php
require_once __DIR__ . '/../../config/db.php';
class ResponseModel
{
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }
    public function teams(): array
    {
        return $this->pdo->query('SELECT * FROM responders WHERE is_active=1 ORDER BY team_name')->fetchAll();
    }
    public function team(int $id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM responders WHERE id=?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function saveTeam(?int $id, string $name, string $phone, int $active): void
    {
        if ($id !== null) {
            $stmt = $this->pdo->prepare('UPDATE responders SET team_name=?, contact_phone=?, is_active=? WHERE id=?');
            $stmt->execute([$name, $phone ?: null, $active, $id]);
        } else {
            $stmt = $this->pdo->prepare('INSERT INTO responders (team_name,contact_phone,is_active) VALUES (?,?,?)');
            $stmt->execute([$name, $phone ?: null, $active]);
        }
    }
    public function incident(int $id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM incident_reports WHERE id=?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function assignments(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT a.*, r.team_name, r.contact_phone, r.is_active, u.full_name AS assigned_by_name FROM incident_assignments a JOIN responders r ON r.id=a.responder_id JOIN users u ON u.id=a.assigned_by WHERE a.incident_id=? ORDER BY a.id');
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }
    public function history(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT h.*, u.full_name AS staff_name FROM response_updates h JOIN users u ON u.id=h.updated_by WHERE h.incident_id=? ORDER BY h.id');
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }
    // All response writes lock the incident first, just like staff reviews.
    public function record(int $incidentId, ?int $teamId, ?int $assignmentId, string $status, string $notes, int $staff): void
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('SELECT status FROM incident_reports WHERE id=? AND archived_at IS NULL FOR UPDATE');
            $stmt->execute([$incidentId]);
            $old = $stmt->fetchColumn();
            if (!in_array($old, ['verified','responding'], true)) {
                throw new DomainException('Verify or reopen the incident before managing its response.');
            }
            if ($teamId !== null) {
                $stmt = $this->pdo->prepare('SELECT * FROM responders WHERE id=? FOR UPDATE');
                $stmt->execute([$teamId]);
                $team = $stmt->fetch();
                if (!$team || !$team['is_active']) {
                    throw new DomainException('Choose an active response team.');
                }
                $stmt = $this->pdo->prepare('SELECT id FROM incident_assignments WHERE incident_id=? AND responder_id=?');
                $stmt->execute([$incidentId,$teamId]);
                if ($stmt->fetchColumn()) {
                    throw new DomainException('This team already has an assignment. Update or reactivate that assignment below.');
                }
                $stmt = $this->pdo->prepare('INSERT INTO incident_assignments (incident_id,responder_id,assigned_by) VALUES (?,?,?)');
                $stmt->execute([$incidentId,$teamId,$staff]);
                $assignmentId = (int)$this->pdo->lastInsertId();
                $status = 'assigned';
                $action = 'Assigned team ' . $team['team_name'] . '. ' . $notes;
            } else {
                $stmt = $this->pdo->prepare('SELECT a.*, r.team_name, r.is_active FROM incident_assignments a JOIN responders r ON r.id=a.responder_id WHERE a.id=? AND a.incident_id=? FOR UPDATE');
                $stmt->execute([$assignmentId,$incidentId]);
                $assignment = $stmt->fetch();
                if (!$assignment) {
                    throw new DomainException('Assignment does not belong to this incident.');
                }
                $allowed = [
                    'assigned' => ['assigned','on_scene','completed','cancelled'],
                    'on_scene' => ['on_scene','completed','cancelled'],
                    'completed' => ['completed','assigned'],
                    'cancelled' => ['cancelled','assigned']
                ];
                if (!in_array($status, $allowed[$assignment['status']], true)) {
                    throw new DomainException('Invalid assignment transition. Reactivate a closed assignment as assigned first.');
                }
                if ($status === 'assigned' && in_array($assignment['status'], ['completed','cancelled'], true) && !$assignment['is_active']) {
                    throw new DomainException('Reactivate the response team before assigning it again.');
                }
                $stmt = $this->pdo->prepare('UPDATE incident_assignments SET status=? WHERE id=? AND incident_id=?');
                $stmt->execute([$status,$assignmentId,$incidentId]);
                $action = $assignment['team_name'] . ': ' . $assignment['status'] . ' → ' . $status . '. ' . $notes;
            }
            // Dispatch/on-scene starts response. Completion never automatically resolves an emergency.
            $next = in_array($status, ['assigned','on_scene'], true) ? 'responding' : $old;
            $stmt = $this->pdo->prepare('UPDATE incident_reports SET status=? WHERE id=?');
            $stmt->execute([$next,$incidentId]);
            $stmt = $this->pdo->prepare('INSERT INTO response_updates (incident_id,updated_by,action_taken,from_status,to_status,assignment_id,assignment_status) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute([$incidentId,$staff,$action,$old,$next,$assignmentId,$status]);
            $this->pdo->commit();
        } catch (Throwable $error) {
            $this->pdo->rollBack();
            throw $error;
        }
    }
}
```

<a id="file-30"></a>

## app/models/User.php

[Open actual source](../../../app/models/User.php)

Methods in source order:

- `__construct(?PDO $pdo = null)`
- `findByEmail(string $email)`
- `createResident(array $data): void`

```php
<?php
require_once __DIR__ . '/../../config/db.php';
class UserModel
{
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }
    public function findByEmail(string $email)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    public function createResident(array $data): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO users (full_name, username, email, phone, password, role) VALUES (?, ?, ?, ?, ?, 'resident')");
        $stmt->execute([$data['full_name'], $data['username'], $data['email'], $data['phone'], $data['password']]);
    }
}
```

<a id="file-31"></a>

## resources/views/admin/admin.index.php

[Open actual source](../../../resources/views/admin/admin.index.php)

```php
<?php
require_once __DIR__ . '/../../../app/helpers.php';
requireRole('admin');
require_once __DIR__ . '/../../../app/controllers/DashboardController.php';
$overview = (new DashboardController('admin'))->dashboardData();
$pageTitle = 'Staff dashboard';
require __DIR__ . '/../components/header.php';
?>
<div class="page-intro"
    ><p class="eyebrow">Your community overview</p><h1><?= e($settings['app_name']) ?> — staff dashboard</h1
    ><p>A current overview of incidents, evacuation, and relief.</p>
</div>
<?php require __DIR__ . '/../components/dashboard-cards.php'; ?>
<p><a class="btn btn-primary" href="<?= e(url('resources/views/maps/map.php')) ?>">Explore saved locations on the map</a></p>
<p>Use the shared records to manage incidents, response teams, evacuation, and relief.</p>
<div class="row g-3">
    <?php foreach ([
    ['Household registry','Register households and members before evacuation and household relief distribution.','households.php'],
    ['Incidents','Review reports and assign response teams from an incident’s details.','../incidents/list.php'],
    ['Response teams','Maintain teams and their availability.','response/teams.php'],
    ['Evacuation centers','Manage capacity, check-ins, checkouts, and stay history.','evacuation/centers.php'],
    ['Relief goods','Record supplies and distribute goods to registered households.','relief/inventory.php'],
    ['Announcements','Prepare drafts and publish notices to residents.','announcement/announcement.php'],
    ['Disaster summaries','Review totals and print summaries without personal records.','summaries/disaster.php']
] as [$title,$description,$path]): ?>
    <section class="col-md-6">
        <div class="module-card"
            ><h2 class="h4"><a href="<?= e(url('resources/views/admin/' . $path)) ?>"><?= e($title) ?></a></h2
            ><p><?= e($description) ?></p>
        </div></section
    >
    <?php endforeach; ?></div
>
<?php require __DIR__ . '/../components/footer.php'; ?>
```

<a id="file-32"></a>

## resources/views/admin/announcement/announcement.php

[Open actual source](../../../resources/views/admin/announcement/announcement.php)

```php
<?php
require_once __DIR__ . '/../../../../app/controllers/AnnouncementController.php';
$controller = new AnnouncementController('admin');
$errors = $controller->save(null);
$announcements = $controller->listAnnouncements();
$pageTitle = 'Manage announcements';
require __DIR__ . '/../../components/header.php';
require __DIR__ . '/../../components/errors.php';
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Emergency announcements</h1>
<p><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/records.php?type=announcements')) ?>">Manage records</a></p>
<p
    >Use drafts while preparing a notice. Publishing makes the saved text visible to residents.
    During classroom development, use clearly labeled fictional contacts only.</p
>
<h2>Create announcement</h2>
<?php $announcement = ['title' => '','message' => '','is_published' => 0];
require __DIR__ . '/editor.php'; ?>
<h2 class="mt-4">Announcement history</h2>
<form method="get" class="filter-form mb-3">
    <label>Title contains <input name="q" value="<?= e($controller->queryValue('q')) ?>" /></label>
    <label
        >Status
        <select name="status"
            ><?php foreach (['' => 'All','draft' => 'Draft / withdrawn','published' => 'Published'] as $key => $label): ?><option value="<?= e($key) ?>" <?= $controller->queryValue('status') === $key?'selected':'' ?>
                ><?= e($label) ?></option
            ><?php endforeach; ?></select
        ></label
    >
    <label>Created date <input type="date" name="date" value="<?= e($controller->queryValue('date')) ?>" /></label>
    <button class="btn btn-outline-secondary">Search</button> <a href="<?= e(url('resources/views/admin/announcement/announcement.php')) ?>">Clear</a>
</form>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($announcements as $row): ?><tr>
                <td><?= e($row['title']) ?></td>
                <td
                    ><span class="status-badge" data-status="<?= $row['is_published']?'published':'draft' ?>"
                        ><?= $row['is_published']?'Published':'Draft / withdrawn' ?></span
                    ></td
                >
                <td><?= e($row['created_at']) ?></td>
                <td><a href="<?= e(url('resources/views/admin/announcement/announcement.update.php?id=' . $row['id'])) ?>">View / edit / publish / withdraw</a></td> </tr
            ><?php endforeach; ?> <?php if (!$announcements): ?><tr>
                <td colspan="4">No matching announcements.</td> </tr
            ><?php endif; ?></tbody
        >
    </table>
</div>
<?php require __DIR__ . '/../../components/footer.php'; ?>
```

<a id="file-33"></a>

## resources/views/admin/announcement/announcement.update.php

[Open actual source](../../../resources/views/admin/announcement/announcement.update.php)

```php
<?php
require_once __DIR__ . '/../../../../app/controllers/AnnouncementController.php';
$controller = new AnnouncementController('admin');
$announcement = $controller->getAnnouncement();
$errors = $controller->save((int)$announcement['id']);
$pageTitle = 'Edit announcement';
require __DIR__ . '/../../components/header.php';
require __DIR__ . '/../../components/errors.php';
?>
<h1>Edit announcement</h1>
<p>Current status: <strong><?= $announcement['is_published']?'Published':'Draft / withdrawn' ?></strong> · Created <?= e($announcement['created_at']) ?></p>
<p
    >Saving and withdrawing hides this notice from residents on their next page load. Saving while
    published updates the text residents see immediately.</p
>
<?php require __DIR__ . '/editor.php'; ?>
<a class="d-block mt-3" href="<?= e(url('resources/views/admin/announcement/announcement.php')) ?>">Back to announcements</a>
<?php require __DIR__ . '/../../components/footer.php'; ?>
```

<a id="file-34"></a>

## resources/views/admin/announcement/editor.php

[Open actual source](../../../resources/views/admin/announcement/editor.php)

```php
<?php
require_once __DIR__ . '/../../../../app/helpers.php';
requireRole('admin');
if (!isset($announcement)) {
    http_response_code(404);
    exit('Open the announcement editor from the announcement list.');
}
?>
<form method="post">
    <label class="d-block mb-3"
        >Title<input
            class="form-control"
            name="title"
            maxlength="150"
            value="<?= e(isPost()?input('title'):$announcement['title']) ?>"
            required
    /></label>
    <label class="d-block mb-3"
        >Notice text<textarea
            class="form-control"
            name="message"
            rows="9"
            maxlength="10000"
            required
        >
<?= e(isPost()?input('message'):$announcement['message']) ?></textarea>
    </label>
    <p
        >Include the affected area, what residents should do, and the relevant date/time. Classroom
        example contact: “DEMO ONLY — Sample DRRM Desk, demo@example.invalid.”</p
    >
    <?php if ($announcement['is_published']): ?>
    <label class="d-block mb-3">
        <input type="checkbox" name="confirm_withdraw" value="1" <?= input('confirm_withdraw') === '1' ? 'checked' : '' ?> />
        I confirm that saving as a draft will withdraw this notice from residents.
    </label>
    <?php endif; ?>
    <button class="btn <?= $announcement['is_published'] ? 'btn-outline-danger' : 'btn-primary' ?>" name="action" value="save_draft"><?= $announcement['is_published']?'Save and withdraw':'Save draft' ?></button>
    <button class="btn btn-primary" name="action" value="publish"><?= $announcement['is_published']?'Save and keep published':'Publish' ?></button>
    <a href="<?= e(url('resources/views/admin/announcement/announcement.php')) ?>">Cancel and return to announcements</a>
</form>
```

<a id="file-35"></a>

## resources/views/admin/evacuation/center.php

[Open actual source](../../../resources/views/admin/evacuation/center.php)

```php
<?php
require_once __DIR__ . '/../../../../app/controllers/EvacuationController.php';
$controller = new EvacuationController();
$id = $controller->recordId($_GET['id'] ?? null);
$center = $controller->center($id);
$errors = $controller->handleStay($id);
$records = $controller->records($id);
$households = $controller->households();
$householdId = 0;
if (isset($_GET['household_id']) && $_GET['household_id'] !== '') {
    $householdId = $controller->recordId($_GET['household_id']);
}
if (input('action') === 'checkin_household') {
    $householdId = $controller->recordId(input('household_id'));
}
$members = $householdId ? $controller->householdMembers($householdId) : [];
$selectedMembers = input('action') === 'checkin_household' ? ($_POST['member_ids'] ?? []) : array_column($members, 'id');
if (!is_array($selectedMembers)) {
    $selectedMembers = [];
}
$arrivingCount = 0;
foreach ($members as $member) {
    if (in_array($member['id'], $selectedMembers)) {
        $arrivingCount++;
    }
}
$center = $controller->center($id);
$walkins = $controller->walkins();
$pageTitle = $center['name'];
require __DIR__ . '/../../components/header.php';
require __DIR__ . '/../../components/errors.php';
?> <?php $savedPoint = $center;
require __DIR__ . '/../../components/saved-point.php'; ?>
<h1><?= e($center['name']) ?></h1>
<p><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/records.php?type=walkins')) ?>">Manage records</a></p>
<p
    ><?= e($center['address_details']) ?> · <?= e($center['status']) ?> · Occupancy: <?= (int)$center['occupancy'] ?> /
    <?= (int)$center['capacity'] ?> people</p
>
<p>Available capacity (people): <strong><?= (int)$center['available'] ?></strong></p>
<a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/evacuation/centers.php')) ?>">Back to evacuation centers</a> ·
<a href="<?= e(url('resources/views/admin/evacuation/centers.php?edit=' . $id)) ?>">Edit / close center</a>
<?php if ($center['status'] === 'open' && $center['archived_at'] === null && $center['occupancy'] < $center['capacity']): ?>
<h2 class="mt-4">Check in a household</h2>
<form method="get" class="mb-3">
    <input type="hidden" name="id" value="<?= $id ?>">
    <label for="household_id" class="form-label">Registered household</label>
    <select id="household_id" name="household_id" class="form-select mb-2" required>
        <option value="">Choose a household</option>
        <?php foreach ($households as $household): ?>
            <option value="<?= (int)$household['id'] ?>" <?= $householdId === (int)$household['id'] ? 'selected' : '' ?>>
                <?= e($household['household_code'] . ' — ' . $household['representative_name']) ?>
                (<?= (int)$household['member_count'] ?> members)
            </option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-outline-secondary">Show members</button>
</form>
<?php if ($householdId): ?>
    <form method="post" data-household-checkin data-occupancy="<?= (int)$center['occupancy'] ?>" data-capacity="<?= (int)$center['capacity'] ?>">
        <input type="hidden" name="household_id" value="<?= $householdId ?>">
        <fieldset>
            <legend>Arriving members (<?= count($members) ?> registered)</legend>
            <p>Uncheck anyone who is not arriving. Each selected person uses one place.</p>
            <?php foreach ($members as $member): ?>
                <label class="d-block mb-2">
                    <input class="form-check-input" type="checkbox" name="member_ids[]" value="<?= (int)$member['id'] ?>" <?= in_array($member['id'], $selectedMembers) ? 'checked' : '' ?>>
                    <?= e($member['full_name']) ?>
                </label>
            <?php endforeach; ?>
        </fieldset>
        <?php if (!$members): ?><p>No active members found. Update the household registry first.</p><?php endif; ?>
        <p>Current occupancy: <?= (int)$center['occupancy'] ?> / <?= (int)$center['capacity'] ?> people.</p>
        <p data-arrival-summary aria-live="polite">Arriving: <?= $arrivingCount ?> people. Remaining capacity after check-in: <?= (int)$center['capacity'] - (int)$center['occupancy'] - $arrivingCount ?> people.</p>
        <button class="btn btn-primary" name="action" value="checkin_household" <?= !$members ? 'disabled' : '' ?>>Check in household</button>
        <a href="<?= e(url('resources/views/admin/evacuation/center.php?id=' . $id)) ?>">Cancel household selection</a>
    </form>
<?php endif; ?>
<h2 class="mt-4">Check in a walk-in</h2>
<p
    >Search the household registry and existing walk-ins before creating a new walk-in. Confirm
    identity with the person or guardian. To transfer centers, check out the previous stay first.</p
>
<form method="post">
    <label class="d-block mb-3"
        >Record type<select class="form-select" name="kind"
            ><?php foreach (['walkin' => 'Existing walk-in','new_walkin' => 'New walk-in'] as $key => $label): ?><option value="<?= e($key) ?>" <?= input('kind') === $key?'selected':'' ?>
                ><?= e($label) ?></option
            ><?php endforeach; ?></select
        ></label
    >
    <label class="d-block mb-3"
        >Existing walk-in<select class="form-select" name="walkin_id"
            ><option value="">Select for returning walk-in</option
            ><?php foreach ($walkins as $person): ?><option value="<?= (int)$person['id'] ?>" <?= input('walkin_id') === (string)$person['id']?'selected':'' ?>
                ><?= e('#' . $person['id'] . ' — ' . $person['full_name'] . ' — ' . $person['address_details'] . ' — ' . $person['contact_phone']) ?></option
            ><?php endforeach; ?></select
        ></label
    >
    <fieldset class="border p-3 mb-3"
        ><legend>New walk-in details only</legend> <?php foreach (['full_name' => ['Full name',100],'address_details' => ['Home address / purok',255],'contact_phone' => ['Contact number (personal or guardian)',20]] as $key => $field): ?><label class="d-block mb-2"
            ><?= e($field[0]) ?><input
                class="form-control"
                name="<?= e($key) ?>"
                maxlength="<?= $field[1] ?>"
                value="<?= e(input($key)) ?>" /></label
        ><?php endforeach; ?>
        <label
            >Age in years<input
                class="form-control"
                name="age"
                type="number"
                min="0"
                max="130"
                value="<?= e(input('age')) ?>"
        /></label>
    </fieldset>
    <button class="btn btn-primary" name="action" value="checkin">Check in walk-in</button>
    <a href="<?= e(url('resources/views/admin/evacuation/centers.php')) ?>">Cancel and return to evacuation centers</a>
</form>
<?php else: ?><p class="alert alert-info mt-3"
    >Check-in unavailable: this center is closed or full.</p
><?php endif; ?>
<h2 class="mt-4">Current evacuees and evacuation history</h2>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Person</th>
                <th>Record / contact</th>
                <th>Checked in</th>
                <th>Checked out</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): ?><tr>
                <td
                    ><?= e($record['person_name']) ?> <?php if ($record['walkin_age'] !== null): ?><br />Age: <?= (int)$record['walkin_age'] ?><br /><?= e($record['walkin_address']) ?>
                    <?php endif; ?></td
                >
                <td><?= e($record['household_code'] ?? ('Walk-in #' . ($record['walkin_id'] ?? 'legacy'))) ?><br /><?= e($record['walkin_contact']) ?></td>
                <td><?= e($record['checked_in_at']) ?><br />By <?= e($record['encoder']) ?></td>
                <td
                    ><?= e($record['checked_out_at'] ?? 'Currently staying') ?> <?php if ($record['checkout_staff']): ?><br />By <?= e($record['checkout_staff']) ?>
                    <?php endif; ?></td
                >
                <td
                    ><?php if ($record['checked_out_at'] === null): ?><form method="post">
                        <input type="hidden" name="record_id" value="<?= (int)$record['id'] ?>" />
                        <button class="btn btn-outline-secondary" name="action" value="checkout"
                            >Check out</button
                        > </form
                    ><?php endif; ?></td
                > </tr
            ><?php endforeach; ?> <?php if (!$records): ?><tr>
                <td colspan="5">No evacuation records.</td> </tr
            ><?php endif; ?></tbody
        >
    </table>
</div>
<script src="<?= e(url('resources/assets/js/household-checkin.js')) ?>" defer></script>
<?php require __DIR__ . '/../../components/footer.php'; ?>
```

<a id="file-36"></a>

## resources/views/admin/evacuation/centers.php

[Open actual source](../../../resources/views/admin/evacuation/centers.php)

```php
<?php
require_once __DIR__ . '/../../../../app/controllers/EvacuationController.php';
$controller = new EvacuationController();
$id = isset($_GET['edit'])?$controller->recordId($_GET['edit']):null;
$data = $id?$controller->center($id):['name' => '','address_details' => '','capacity' => 0,'status' => 'closed'];
$errors = $controller->saveCenter($id);
if (isPost()) {
    foreach (['name','address_details','capacity','status','latitude','longitude'] as $key) {
        $data[$key] = input($key);
    }
}
$q = is_string($_GET['q'] ?? null)?trim($_GET['q']):'';
$centers = $controller->centers($q);
$pageTitle = 'Evacuation centers';
require __DIR__ . '/../../components/header.php';
require __DIR__ . '/../../components/errors.php';
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Evacuation centers</h1>
<p><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/records.php?type=centers')) ?>">Manage records</a></p>
<form method="get" class="filter-form mb-3">
    <label>Search name or address <input name="q" value="<?= e($q) ?>" /></label>
    <button class="btn btn-outline-secondary">Search</button> <a href="<?= e(url('resources/views/admin/evacuation/centers.php')) ?>">Clear</a>
</form>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Status</th>
                <th>People / capacity</th>
                <th>Available capacity (people)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($centers as $center): ?><tr>
                <td><?= e($center['name']) ?></td>
                <td
                    ><?= e($center['address_details']) ?> <?php if (!hasMapPoint($center)): ?><br /><small>No map point</small
                    ><?php endif; ?></td
                >
                <td
                    ><span class="status-badge" data-status="<?= e($center['status']) ?>"
                        ><?= e($center['status']) ?></span
                    ></td
                >
                <td><?= (int)$center['occupancy'] ?> / <?= (int)$center['capacity'] ?></td>
                <td><?= (int)$center['available'] ?></td>
                <td
                    ><a href="<?= e(url('resources/views/admin/evacuation/center.php?id=' . $center['id'])) ?>">People / history</a> ·
                    <a href="<?= e(url('resources/views/admin/evacuation/centers.php?edit=' . $center['id'])) ?>">Edit / close</a></td
                > </tr
            ><?php endforeach; ?> <?php if (!$centers): ?><tr>
                <td colspan="6">No matching centers.</td> </tr
            ><?php endif; ?></tbody
        >
    </table>
</div>
<h2><?= $id?'Edit center':'Add center' ?></h2>
<form method="post">
    <?php foreach (['name' => ['Center name',150],'address_details' => ['Address / location',255]] as $key => $field): ?><label class="d-block mb-3"
        ><?= e($field[0]) ?><input
            class="form-control"
            name="<?= e($key) ?>"
            maxlength="<?= $field[1] ?>"
            value="<?= e($data[$key]) ?>"
            required /></label
    ><?php endforeach; ?>
    <label class="d-block mb-3"
        >Capacity (people)<input
            class="form-control"
            type="number"
            min="0"
            max="1000000"
            name="capacity"
            value="<?= e($data['capacity']) ?>"
            required
    /></label>
    <label class="d-block mb-3"
        >Status<select class="form-select" name="status"
            ><?php foreach (['open','closed'] as $status): ?><option value="<?= e($status) ?>" <?= $data['status'] === $status?'selected':'' ?>
                ><?= e(ucfirst($status)) ?></option
            ><?php endforeach; ?></select
        ></label
    >
    <p
        >Check out all occupants before closing a center. Capacity cannot be reduced below current
        occupancy.</p
    >
    <?php $point = $data;
require __DIR__ . '/../../components/location-picker.php'; ?>
    <button class="btn btn-primary" name="action" value="save_center">Save center</button>
    <a href="<?= e(url('resources/views/admin/evacuation/centers.php')) ?>">Cancel and return to evacuation centers</a>
</form>
<?php require __DIR__ . '/../../components/footer.php'; ?>
```

<a id="file-37"></a>

## resources/views/admin/household.php

[Open actual source](../../../resources/views/admin/household.php)

```php
<?php
require_once __DIR__ . '/../../../app/controllers/HouseholdController.php';
$controller = new HouseholdController();
$id = $controller->recordId($_GET['id'] ?? null);
$household = $controller->getHousehold($id);
$memberId = isset($_GET['member']) ? $controller->recordId($_GET['member']) : null;
$form = $memberId === null ? ['full_name' => '','age' => '','notes' => ''] : $controller->member($memberId, $id);
if (isPost()) {
    $form = ['full_name' => input('full_name'),'age' => input('age'),'notes' => input('notes')];
}
$errors = $controller->saveMember($id, $memberId, $form);
$members = $controller->members($id);
$pageTitle = 'Household details';
require __DIR__ . '/../components/header.php';
?> <?php $savedPoint = $household;
require __DIR__ . '/../components/saved-point.php'; ?>
<a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/households.php')) ?>">Back to households</a>
<h1><?= e($household['household_code']) ?> — <?= e($household['representative_name']) ?></h1>
<p><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/records.php?type=members')) ?>">Manage records</a></p>
<a class="d-inline-block mb-3" href="<?= e(url('resources/views/admin/households.php?edit=' . $id)) ?>">Edit household</a>
<dl class="row">
    <?php foreach (['contact_phone' => 'Emergency contact number','purok' => 'Purok / sitio','address_details' => 'Address and location description','hazard_notes' => 'Hazard notes','vulnerability_notes' => 'Assessment notes / source','vulnerability_assessed_at' => 'Assessment date'] as $field => $label): ?><dt class="col-sm-4"><?= e($label) ?></dt
    ><dd class="col-sm-8"><?= e($household[$field] ?: 'Not recorded') ?></dd><?php endforeach; ?> <?php foreach (['registry_reason' => ['Registry reason',HouseholdController::REASONS],'hazard_type' => ['Hazard concern',HouseholdController::HAZARDS],'vulnerability_category' => ['Assistance classification',HouseholdController::VULNERABILITY],'evacuation_status' => ['Household advisory status',HouseholdController::EVACUATION]] as $field => $info): ?><dt
        class="col-sm-4"
        ><?= e($info[0]) ?></dt
    ><dd class="col-sm-8"><?= e($info[1][$household[$field]]) ?></dd><?php endforeach; ?>
    <dt class="col-sm-4">Members recorded</dt><dd class="col-sm-8"><?= (int)$household['member_count'] ?></dd></dl
>
<h2>Household members</h2
><p
    >Add each person, including the representative if they live in this household. Member records do
    not create login accounts.</p
>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Age</th>
                <th>Notes</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($members as $member): ?><tr>
                <td><?= e($member['full_name']) ?></td>
                <td><?= e($member['age'] ?? 'Not recorded') ?></td>
                <td><?= e($member['notes']) ?></td>
                <td><a href="<?= e(url('resources/views/admin/household.php?id=' . $id . '&member=' . $member['id'])) ?>">View / edit</a></td> </tr
            ><?php endforeach; ?> <?php if (!$members): ?><tr>
                <td colspan="4">No members recorded.</td> </tr
            ><?php endif; ?></tbody
        >
    </table>
</div>
<h2><?= $memberId === null ? 'Add member' : 'Edit member' ?></h2>
<?php require __DIR__ . '/../components/errors.php'; ?>
<form method="post" action="<?= e(url('resources/views/admin/household.php?id=' . $id . ($memberId === null ? '' : '&member=' . $memberId))) ?>">
    <input type="hidden" name="action" value="<?= $memberId === null ? 'create_member' : 'update_member' ?>" />
    <label for="full_name" class="form-label">Full name *</label>
    <input
        class="form-control mb-3"
        id="full_name"
        name="full_name"
        maxlength="100"
        required
        value="<?= e($form['full_name']) ?>"
    />
    <label for="age" class="form-label">Age (optional)</label>
    <input
        class="form-control mb-3"
        id="age"
        name="age"
        type="number"
        min="0"
        max="130"
        value="<?= e($form['age']) ?>"
    />
    <label for="notes" class="form-label">Notes</label>
    <textarea class="form-control mb-3" id="notes" name="notes" maxlength="255">
<?= e($form['notes']) ?></textarea>
    <button class="btn btn-primary">Save member</button>
    <a href="<?= e(url('resources/views/admin/household.php?id=' . $id)) ?>">Cancel and return to household</a>
</form>
<?php require __DIR__ . '/../components/footer.php'; ?>
```

<a id="file-38"></a>

## resources/views/admin/households.php

[Open actual source](../../../resources/views/admin/households.php)

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
<h1>Household registry</h1>
<p><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/records.php?type=households')) ?>">Manage records</a></p>
<form method="get" class="filter-form d-flex gap-2 mb-3">
    <label for="search" class="visually-hidden">Search households</label>
    <input
        id="search"
        class="form-control"
        name="q"
        maxlength="255"
        value="<?= e($search) ?>"
        placeholder="Code, representative, purok, address or contact"
    />
    <button class="btn btn-outline-secondary">Search</button
    ><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/households.php')) ?>">Clear</a>
</form>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Representative</th>
                <th>Purok</th>
                <th>Members</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?><tr>
                <td><?= e($row['household_code']) ?></td>
                <td><?= e($row['representative_name']) ?></td>
                <td
                    ><?= e($row['purok']) ?> <?php if (!hasMapPoint($row)): ?><br /><small>No map point</small
                    ><?php endif; ?></td
                >
                <td><?= (int)$row['member_count'] ?></td>
                <td
                    ><a href="<?= e(url('resources/views/admin/household.php?id=' . $row['id'])) ?>">View / members</a> ·
                    <a href="<?= e(url('resources/views/admin/households.php?edit=' . $row['id'])) ?>">Edit</a></td
                > </tr
            ><?php endforeach; ?> <?php if (!$rows): ?><tr>
                <td colspan="5">No matching households.</td> </tr
            ><?php endif; ?></tbody
        >
    </table>
</div>
<h2><?= $id === null ? 'Add household' : 'Edit household' ?></h2>
<?php require __DIR__ . '/../components/errors.php'; ?>
<form method="post" action="<?= e(url('resources/views/admin/households.php' . ($id === null ? '' : '?edit=' . $id))) ?>">
    <input type="hidden" name="action" value="<?= $id === null ? 'create_household' : 'update_household' ?>" />
    <div class="row">
        <?php foreach (['household_code' => ['Household code',30,true],'representative_name' => ['Representative / emergency contact name',100,true],'contact_phone' => ['Emergency contact number',20,false],'purok' => ['Purok / sitio',100,true],'address_details' => ['Address and location description / landmark',255,false]] as $key => $field): ?>
        <div class="col-md-6 mb-3">
            <label for="<?= e($key) ?>" class="form-label"
                ><?= e($field[0]) ?><?= $field[2] ? ' *' : '' ?></label
            >
            <input
                class="form-control"
                id="<?= e($key) ?>"
                name="<?= e($key) ?>"
                maxlength="<?= $field[1] ?>"
                <?= $field[2] ? 'required' : '' ?>
                value="<?= e($form[$key]) ?>"
            />
        </div>
        <?php endforeach; ?> <?php foreach (['registry_reason' => ['Registry reason',HouseholdController::REASONS],'hazard_type' => ['Hazard concern',HouseholdController::HAZARDS],'vulnerability_category' => ['Assistance classification',HouseholdController::VULNERABILITY],'evacuation_status' => ['Household advisory status',HouseholdController::EVACUATION]] as $key => $field): ?>
        <div class="col-md-6 mb-3">
            <label for="<?= e($key) ?>" class="form-label"><?= e($field[0]) ?></label>
            <select class="form-select" id="<?= e($key) ?>" name="<?= e($key) ?>"
                ><?php foreach ($field[1] as $value => $label): ?><option value="<?= e($value) ?>" <?= $form[$key] === $value ? 'selected' : '' ?>
                    ><?= e($label) ?></option
                ><?php endforeach; ?></select
            >
        </div>
        <?php endforeach; ?> <?php foreach (['hazard_notes' => 'Hazard notes','vulnerability_notes' => 'Assessment source and assistance notes'] as $key => $label): ?>
        <div class="col-md-6 mb-3">
            <label for="<?= e($key) ?>" class="form-label"><?= e($label) ?></label>
            <textarea
                class="form-control"
                id="<?= e($key) ?>"
                name="<?= e($key) ?>"
                maxlength="255"
            >
<?= e($form[$key]) ?></textarea>
        </div>
        <?php endforeach; ?></div
    >
    <p
        >Not yet assessed: no assessment recorded. No additional assistance need identified:
        reviewed without an identified need; this does not mean disaster-safe. Additional assistance
        needed: record the practical help required. Choosing “Not yet assessed” clears prior
        assessment notes and metadata.</p
    >
    <?php $point = $form;
require __DIR__ . '/../components/location-picker.php'; ?>
    <button class="btn btn-primary">Save household</button>
    <a href="<?= e(url('resources/views/admin/households.php')) ?>">Cancel and return to households</a>
</form>
<?php require __DIR__ . '/../components/footer.php'; ?>
```

<a id="file-39"></a>

## resources/views/admin/records-all.php

[Open actual source](../../../resources/views/admin/records-all.php)

```php
<?php
require_once __DIR__ . '/../../../app/helpers.php';
requireRole('admin');
redirect('resources/views/admin/records.php?type=all');
```

<a id="file-40"></a>

## resources/views/admin/records.php

[Open actual source](../../../resources/views/admin/records.php)

```php
<?php
require_once __DIR__ . '/../../../app/controllers/RecordActionsController.php';
$controller = new RecordActionsController();
$type = is_string($_GET['type'] ?? null) ? $_GET['type'] : 'all';
if ($type !== 'all' && !isset(RecordActionsModel::TYPES[$type])) {
    http_response_code(400);
    exit('Unsupported record type.');
}
$q = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
$page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000000]]);
if (!$page) {
    http_response_code(400);
    exit('Invalid page.');
}
$params = ['type' => $type, 'q' => $q, 'page' => $page];
$rows = $controller->listing($type, $q, $page);
$errors = [];
if (isPost()) {
    try {
        $controller->delete($rows);
        redirect('resources/views/admin/records.php?' . http_build_query($params));
    } catch (DomainException $error) {
        $errors[] = $error->getMessage();
    } catch (Throwable $error) {
        $errors[] = 'Deletion failed. No selected records were changed.';
    }
}
$pageTitle = 'Manage records';
require __DIR__ . '/../components/header.php';
require __DIR__ . '/../components/errors.php';
?>
<p><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Manage records</h1>
<p>Filter or search, then select records to delete. Unused records are permanently deleted. Linked records are removed from active lists while their history is kept. Records still in use show the steps needed first.</p>
<form method="get" class="filter-form mb-3">
    <label>Record type<select name="type">
        <option value="all">All record types</option>
        <?php foreach (RecordActionsModel::TYPES as $key => $spec): ?>
        <option value="<?= e($key) ?>" <?= $type === $key ? 'selected' : '' ?>><?= e($spec[2]) ?></option>
        <?php endforeach; ?>
    </select></label>
    <label>Search ID/name <input name="q" value="<?= e($q) ?>" /></label>
    <button class="btn btn-outline-secondary">Search</button>
</form>
<form method="post" id="record-selection">
    <div class="table-responsive" tabindex="0" aria-label="Records">
        <table class="table">
            <thead><tr>
                <th><button class="btn btn-danger" name="action" value="delete">Delete selected</button><br />
                    <label><input type="checkbox" id="select-page" /> Select all on this page</label>
                    <span id="selected-count" role="status">0 selected</span></th>
                <th>Record type</th><th>ID</th><th>Name / description</th>
            </tr></thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?= e($row['record_type'] . ':' . $row['id']) ?>" aria-label="<?= e('Select ' . RecordActionsModel::TYPES[$row['record_type']][2] . ' #' . $row['id']) ?>" /></td>
                    <td><?= e(RecordActionsModel::TYPES[$row['record_type']][2]) ?></td>
                    <td><?= (int)$row['id'] ?></td><td><?= e($row['label']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?><tr><td colspan="4">No matching records.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</form>
<p>Page <?= $page ?> · Up to 50 records.
<?php if ($page > 1): ?><a href="?<?= e(http_build_query(array_replace($params, ['page' => $page - 1]))) ?>">Previous</a><?php endif; ?>
<?php if (count($rows) === 50): ?><a href="?<?= e(http_build_query(array_replace($params, ['page' => $page + 1]))) ?>">Next</a><?php endif; ?></p>
<script src="<?= e(url('resources/assets/js/record-selection.js?v=delete-confirm')) ?>"></script>
<?php require __DIR__ . '/../components/footer.php'; ?>
```

<a id="file-41"></a>

## resources/views/admin/relief/distributions.php

[Open actual source](../../../resources/views/admin/relief/distributions.php)

```php
<?php
require_once __DIR__ . '/../../../../app/controllers/ReliefController.php';
$controller = new ReliefController();
$id = isset($_GET['id'])?$controller->recordId($_GET['id']):null;
$distribution = $id?$controller->distribution($id):null;
if ($id && isPost()) {
    http_response_code(405);
    exit('Distribution history cannot be edited.');
}
$errors = $id?[]:$controller->distribute();
$items = $id?$controller->distributedGoods($id):$controller->items();
$households = $id?[]:$controller->households();
$q = $controller->queryValue('q');
$history = $id?[]:$controller->distributions($q);
$pageTitle = 'Relief distributions';
require __DIR__ . '/../../components/header.php';
require __DIR__ . '/../../components/errors.php';
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Back to relief inventory</a></p>
<h1>Relief distributions</h1>
<p
    ><a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Inventory</a> ·
    <a href="<?= e(url('resources/views/admin/relief/movements.php')) ?>">Stock movements</a> ·
    <a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/relief/distributions.php')) ?>">Back to household distributions</a></p
>

<?php if ($distribution): ?>
<h2>Distribution #<?= (int)$id ?></h2>
<dl
    ><?php foreach (['household_code' => 'Household','received_by' => 'Receiving representative','full_name' => 'Staff','distributed_at' => 'Date/time','notes' => 'Notes'] as $key => $label): ?><dt><?= e($label) ?></dt
    ><dd><?= e($distribution[$key]) ?></dd><?php endforeach; ?></dl
>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Good</th>
                <th>Quantity</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody
            ><?php foreach ($items as $item): ?><tr>
                <td><?= e($item['item_name']) ?></td>
                <td><?= (int)$item['quantity'] ?></td>
                <td><?= e($item['unit']) ?></td> </tr
            ><?php endforeach; ?></tbody
        >
    </table>
</div>
<?php else: ?>
<h2>Record household distribution</h2>
<form method="post">
    <label class="d-block mb-3"
        >Registered household<select class="form-select" name="household_id" required
            ><option value="">Choose a household</option
            ><?php foreach ($households as $household): ?><option value="<?= (int)$household['id'] ?>" <?= input('household_id') === (string)$household['id']?'selected':'' ?>
                ><?= e($household['household_code'] . ' — ' . $household['representative_name']) ?></option
            ><?php endforeach; ?></select
        ></label
    >
    <label class="d-block mb-3"
        >Receiving representative<input
            class="form-control"
            name="received_by"
            maxlength="100"
            value="<?= e(input('received_by')) ?>"
            required
    /></label>
    <label class="d-block mb-3"
        >Notes<textarea class="form-control" name="notes" maxlength="255">
<?= e(input('notes')) ?></textarea>
    </label>
    <p
        >Enter quantities for all goods received in this distribution. Leave other quantities blank
        or zero. Date/time and staff are recorded automatically.</p
    >
    <div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
        <table class="table">
            <thead>
                <tr>
                    <th>Good</th>
                    <th>Unit</th>
                    <th>Available</th>
                    <th>Quantity to distribute</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): $submitted = is_array($_POST['goods'] ?? null)?$_POST['goods']:[];
    $value = $submitted[$item['id']] ?? ''; ?><tr>
                    <td><?= e($item['item_name']) ?></td>
                    <td><?= e($item['unit']) ?></td>
                    <td><?= (int)$item['quantity_on_hand'] ?></td>
                    <td>
                        <input
                            aria-label="<?= e('Quantity of ' . $item['item_name']) ?>"
                            type="number"
                            min="0"
                            max="1000000000"
                            name="goods[<?= (int)$item['id'] ?>]"
                            value="<?= e(is_scalar($value)?$value:'') ?>"
                    /></td> </tr
                ><?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <button class="btn btn-primary" name="action" value="distribute"
        >Save distribution and deduct stock</button
    >
    <a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Cancel and return to relief inventory</a>
</form>
<h2 class="mt-4">Distribution history</h2>
<form method="get" class="filter-form mb-3">
    <label
        >Search household code or receiver <input name="q" value="<?= e($q) ?>"
    /></label>
    <button class="btn btn-outline-secondary">Search</button> <a href="<?= e(url('resources/views/admin/relief/distributions.php')) ?>">Clear</a>
</form>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Date/time</th>
                <th>Household</th>
                <th>Receiver</th>
                <th>Staff</th>
                <th>Goods</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $row): ?><tr>
                <td><?= e($row['distributed_at']) ?></td>
                <td><?= e($row['household_code']) ?></td>
                <td><?= e($row['received_by']) ?></td>
                <td><?= e($row['full_name']) ?></td>
                <td><a href="<?= e(url('resources/views/admin/relief/distributions.php?id=' . $row['id'])) ?>">View #<?= (int)$row['id'] ?></a></td> </tr
            ><?php endforeach; ?> <?php if (!$history): ?><tr>
                <td colspan="5">No matching distributions.</td> </tr
            ><?php endif; ?></tbody
        >
    </table>
</div>
<?php endif; ?> <?php require __DIR__ . '/../../components/footer.php'; ?>
```

<a id="file-42"></a>

## resources/views/admin/relief/inventory.php

[Open actual source](../../../resources/views/admin/relief/inventory.php)

```php
<?php
require_once __DIR__ . '/../../../../app/controllers/ReliefController.php';
$controller = new ReliefController();
$id = isset($_GET['edit'])?$controller->recordId($_GET['edit']):null;
$item = $id?$controller->item($id):['item_name' => '','unit' => 'pieces'];
$errors = $controller->saveItem($id);
if (isPost()) {
    $item = ['item_name' => input('item_name'),'unit' => input('unit')];
}
$q = $controller->queryValue('q');
$items = $controller->items($q);
$pageTitle = 'Relief inventory';
require __DIR__ . '/../../components/header.php';
require __DIR__ . '/../../components/errors.php';
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Relief inventory</h1>
<p><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/records.php?type=goods')) ?>">Manage records</a></p
><p><?= count($items) ?> active goods.</p
><p><a href="<?= e(url('resources/views/admin/relief/locations.php')) ?>">Manage relief locations</a></p>
<p
    ><a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Inventory</a> ·
    <a href="<?= e(url('resources/views/admin/relief/movements.php')) ?>">Stock movements</a> ·
    <a href="<?= e(url('resources/views/admin/relief/distributions.php')) ?>">Household distributions</a></p
>

<form method="get" class="filter-form mb-3">
    <label>Search name or unit <input name="q" value="<?= e($q) ?>" /></label>
    <button class="btn btn-outline-secondary">Search</button> <a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Clear</a>
</form>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Good</th>
                <th>Unit</th>
                <th>Available stock</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $row): ?><tr>
                <td><?= e($row['item_name']) ?></td>
                <td><?= e($row['unit']) ?></td>
                <td><?= (int)$row['quantity_on_hand'] ?></td>
                <td><a href="<?= e(url('resources/views/admin/relief/inventory.php?edit=' . $row['id'])) ?>">Edit</a></td> </tr
            ><?php endforeach; ?> <?php if (!$items): ?><tr>
                <td colspan="4">No matching goods.</td> </tr
            ><?php endif; ?></tbody
        >
    </table>
</div>
<h2><?= $id?'Edit good':'Add good' ?></h2>
<form method="post">
    <label class="d-block mb-3"
        >Item name<input
            class="form-control"
            name="item_name"
            maxlength="120"
            value="<?= e($item['item_name']) ?>"
            required
    /></label>
    <label class="d-block mb-3"
        >Unit (for example packs, bottles, boxes)<input
            class="form-control"
            name="unit"
            maxlength="30"
            value="<?= e($item['unit']) ?>"
            required
    /></label>
    <p
        >New goods start at zero stock. Record incoming supplies in Stock movements. Units cannot
        change after stock history exists.</p
    >
    <button class="btn btn-primary" name="action" value="save_item">Save good</button>
    <a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Cancel and return to relief inventory</a>
</form>
<?php require __DIR__ . '/../../components/footer.php'; ?>
```

<a id="file-43"></a>

## resources/views/admin/relief/location.php

[Open actual source](../../../resources/views/admin/relief/location.php)

```php
<?php
require_once __DIR__ . '/../../../../app/controllers/MapController.php';
$location = (new MapController())->reliefLocation($_GET['id'] ?? null);
$pageTitle = 'Relief location';
require __DIR__ . '/../../components/header.php';
?> <?php $savedPoint = $location;
require __DIR__ . '/../../components/saved-point.php'; ?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/relief/locations.php')) ?>">Back to relief locations</a></p>
<h1><?= e($location['name']) ?></h1>
<dl>
    <?php foreach (['location_type' => 'Type','address_details' => 'Address','latitude' => 'Latitude','longitude' => 'Longitude'] as $key => $label): ?>
    <dt><?= e($label) ?></dt><dd><?= e($location[$key] ?? 'Not recorded') ?></dd>
    <?php endforeach; ?>
    <dt>Archived</dt><dd><?= $location['archived_at']?'Yes':'No' ?></dd> <dt>Community map</dt
    ><dd><?= !$location['archived_at'] && in_array($location['location_type'], ['distribution','both'], true) && $location['latitude'] !== null && $location['longitude'] !== null?'Shown automatically':'Not displayed' ?></dd>
</dl>
<a class="btn btn-outline-secondary" href="<?= e(url('resources/views/maps/map.php')) ?>">Back to map</a>
<?php require __DIR__ . '/../../components/footer.php'; ?>
```

<a id="file-44"></a>

## resources/views/admin/relief/locations.php

[Open actual source](../../../resources/views/admin/relief/locations.php)

```php
<?php
require_once __DIR__ . '/../../../../app/controllers/ReliefLocationController.php';
$c = new ReliefLocationController();
$id = null;
if (isset($_GET['edit'])) {
    $id = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if (!$id || $id < 1) {
        http_response_code(400);
        exit('Invalid ID.');
    }
}
$d = $id?$c->find($id):['name' => '','address_details' => '','location_type' => 'distribution'];
$errors = $c->save($id);
if (isPost())foreach (['name','address_details','location_type','latitude','longitude'] as $k)$d[$k] = input($k);
$pageTitle = 'Relief locations';
require __DIR__ . '/../../components/header.php';
require __DIR__ . '/../../components/errors.php';
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Back to relief inventory</a></p>
<h1>Relief locations</h1>
<p><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/records.php?type=locations')) ?>">Manage records</a></p>
<ul
    ><?php foreach ($c->all() as $row): ?><li
        ><?= e($row['name']) ?><?php if (!hasMapPoint($row)): ?> <small>(No map point)</small><?php endif; ?>
        — <a href="<?= e(url('resources/views/admin/relief/location.php?id=' . $row['id'])) ?>">View</a> ·
        <a href="?edit=<?= (int)$row['id'] ?>">Edit</a></li
    ><?php endforeach; ?></ul
>
<h2><?= $id?'Edit':'Add' ?> location</h2>
<form method="post">
    <?php foreach (['name' => 'Name','address_details' => 'Address / landmark'] as $k => $label): ?><label class="d-block mb-3"
        ><?= e($label) ?><input
            class="form-control"
            name="<?= e($k) ?>"
            value="<?= e($d[$k]) ?>"
            required /></label
    ><?php endforeach; ?>
    <label
        >Type<select name="location_type"
            ><?php foreach (['storage','distribution','both'] as $type): ?><option <?= $d['location_type'] === $type?'selected':'' ?>><?= e($type) ?></option
            ><?php endforeach; ?></select
        ></label
    >
    <?php $point = $d;
require __DIR__ . '/../../components/location-picker.php'; ?>
    <button class="btn btn-primary" name="action" value="save_location">Save location</button>
    <a href="<?= e(url('resources/views/admin/relief/locations.php')) ?>">Cancel and return to relief locations</a>
</form>
<?php require __DIR__ . '/../../components/footer.php'; ?>
```

<a id="file-45"></a>

## resources/views/admin/relief/movements.php

[Open actual source](../../../resources/views/admin/relief/movements.php)

```php
<?php
require_once __DIR__ . '/../../../../app/controllers/ReliefController.php';
$controller = new ReliefController();
$errors = $controller->stock();
$items = $controller->items();
$movements = $controller->movements();
$pageTitle = 'Stock movements';
require __DIR__ . '/../../components/header.php';
require __DIR__ . '/../../components/errors.php';
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Back to relief inventory</a></p>
<h1>Stock movements</h1>
<p
    ><a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Inventory</a> ·
    <a href="<?= e(url('resources/views/admin/relief/movements.php')) ?>">Stock movements</a> ·
    <a href="<?= e(url('resources/views/admin/relief/distributions.php')) ?>">Household distributions</a></p
>

<h2>Record stock-in or stock-out</h2>
<p
    >Use household distributions for goods given to households. Stock-out here is for other reasons,
    such as damage or expiry. Date/time and staff are recorded automatically when saved.</p
>
<form method="post" class="mb-4">
    <label class="d-block mb-2"
        >Good<select class="form-select" name="item_id" required
            ><option value="">Choose a good</option
            ><?php foreach ($items as $item): ?><option value="<?= (int)$item['id'] ?>" <?= input('item_id') === (string)$item['id']?'selected':'' ?>
                ><?= e($item['item_name'] . ' — ' . $item['unit'] . ' — available: ' . $item['quantity_on_hand']) ?></option
            ><?php endforeach; ?></select
        ></label
    >
    <label
        >Movement<select class="form-select" name="movement_type"
            ><?php foreach (['stock_in' => 'Stock-in','stock_out' => 'Stock-out'] as $key => $label): ?><option value="<?= e($key) ?>" <?= input('movement_type') === $key?'selected':'' ?>
                ><?= e($label) ?></option
            ><?php endforeach; ?></select
        ></label
    >
    <label
        >Quantity<input
            class="form-control"
            type="number"
            min="1"
            max="1000000000"
            name="quantity"
            value="<?= e(input('quantity')) ?>"
            required
    /></label>
    <label class="d-block my-2"
        >Notes / reason<textarea class="form-control" name="notes" maxlength="255" required>
<?= e(input('notes')) ?></textarea>
    </label>
    <button class="btn btn-primary" name="action" value="stock">Record movement</button>
    <a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Cancel and return to relief inventory</a>
</form>
<h2>Movement history</h2>
<form method="get" class="filter-form mb-3">
    <label
        >Search good, notes, staff<input
            class="form-control"
            name="q"
            value="<?= e($controller->queryValue('q')) ?>"
    /></label>
    <label
        >Type<select class="form-select" name="type"
            ><?php foreach (['' => 'All','stock_in' => 'Stock-in','stock_out' => 'Stock-out','distribution' => 'Distribution'] as $key => $label): ?><option value="<?= e($key) ?>" <?= $controller->queryValue('type') === $key?'selected':'' ?>
                ><?= e($label) ?></option
            ><?php endforeach; ?></select
        ></label
    >
    <label
        >Recorded date<input
            class="form-control"
            type="date"
            name="date"
            value="<?= e($controller->queryValue('date')) ?>"
    /></label>
    <button class="btn btn-outline-secondary">Search</button> <a href="<?= e(url('resources/views/admin/relief/movements.php')) ?>">Clear</a>
</form>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Good / unit</th>
                <th>Type</th>
                <th>Quantity</th>
                <th>Staff</th>
                <th>Notes / distribution</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($movements as $row): ?><tr>
                <td><?= e($row['created_at']) ?></td>
                <td><?= e($row['item_name'] . ' / ' . $row['unit']) ?></td>
                <td><?= e($row['movement_type']) ?></td>
                <td><?= (int)$row['quantity'] ?></td>
                <td><?= e($row['full_name']) ?></td>
                <td
                    ><?= e($row['notes']) ?> <?php if ($row['distribution_id']): ?><br /><a
                        href="<?= e(url('resources/views/admin/relief/distributions.php?id=' . $row['distribution_id'])) ?>"
                        >Distribution #<?= (int)$row['distribution_id'] ?></a
                    ><?php endif; ?></td
                > </tr
            ><?php endforeach; ?> <?php if (!$movements): ?><tr>
                <td colspan="6">No matching movements.</td> </tr
            ><?php endif; ?></tbody
        >
    </table>
</div>
<?php require __DIR__ . '/../../components/footer.php'; ?>
```

<a id="file-46"></a>

## resources/views/admin/response/incident.php

[Open actual source](../../../resources/views/admin/response/incident.php)

```php
<?php
require_once __DIR__ . '/../../../../app/controllers/ResponseController.php';
$controller = new ResponseController();
$id = $controller->recordId($_GET['id'] ?? null);
$incident = $controller->incident($id);
$errors = $controller->record($id);
$assignments = $controller->assignments($id);
$history = $controller->history($id);
$assignedTeamIds = array_column($assignments, 'responder_id');
$teams = array_filter($controller->teams(), fn ($team) => $team['is_active'] && !in_array($team['id'], $assignedTeamIds));
$open = in_array($incident['status'], ['verified','responding'], true);
$pageTitle = 'Response for incident #' . $id;
require __DIR__ . '/../../components/header.php';
require __DIR__ . '/../../components/errors.php';
?>
<h1><?= e($pageTitle) ?></h1>
<p><?= e($incident['incident_type']) ?> · <?= e($incident['location_description']) ?> · Status: <strong><?= e($incident['status']) ?></strong></p>
<a class="btn btn-outline-secondary" href="<?= e(url('resources/views/incidents/view.php?id=' . $id)) ?>">Back to incident details</a>
<p
    >Verify an incident before assigning teams. Dispatch changes it to responding. Complete or
    cancel all active assignments before resolving it in staff review.</p
>
<?php if (!$open): ?><p class="alert alert-info"
    >Verify or reopen this incident in staff review before adding response updates.</p
><?php endif; ?> <?php if ($open && $teams): ?>
<h2>Assign a team</h2>
<form method="post" class="mb-4">
    <label class="d-block"
        >Team<select class="form-select" name="team_id" required
            ><option value="">Choose a team</option>
            <?php foreach ($teams as $team): ?><option value="<?= (int)$team['id'] ?>" <?= input('team_id') === (string)$team['id'] ? 'selected' : '' ?>
                ><?= e($team['team_name']) ?></option
            ><?php endforeach; ?></select
        ></label
    >
    <label class="d-block my-2"
        >Dispatch instructions / action taken<textarea
            class="form-control"
            name="notes"
            maxlength="2000"
            required
        >
<?= input('action') === 'assign_team' ? e(input('notes')) : '' ?></textarea>
    </label>
    <button class="btn btn-primary" name="action" value="assign_team">Assign team</button>
    <a href="<?= e(url('resources/views/incidents/view.php?id=' . $id)) ?>">Cancel and return to incident details</a>
</form>
<?php elseif ($open): ?><p
    >No unassigned active teams. <a href="<?= e(url('resources/views/admin/response/teams.php')) ?>">Manage teams</a> or reactivate an
    existing assignment below.</p
><?php endif; ?>
<h2>Assigned teams</h2>
<?php if (!$assignments): ?><p>No teams assigned.</p><?php endif; ?> <?php foreach ($assignments as $assignment): ?>
<section class="border rounded p-3 mb-3">
    <h3><?= e($assignment['team_name']) ?></h3>
    <p
        ><?= e($assignment['contact_phone']) ?> · <?= $assignment['is_active'] ? 'Active team' : 'Inactive team' ?> · Assignment: <?= e($assignment['status']) ?><br />Originally
        assigned by <?= e($assignment['assigned_by_name']) ?> at <?= e($assignment['assigned_at']) ?></p
    >
    <?php if ($open): $retry = input('action') === 'update_assignment' && input('assignment_id') === (string)$assignment['id']; ?>
    <form method="post">
        <input type="hidden" name="assignment_id" value="<?= (int)$assignment['id'] ?>" />
        <label
            >Status<select class="form-select" name="assignment_status">
                <?php foreach (ResponseController::STATUSES as $status): ?><option value="<?= e($status) ?>" <?= ($retry ? input('assignment_status') : $assignment['status']) === $status ? 'selected' : '' ?>
                    ><?= e(str_replace('_', ' ', ucfirst($status))) ?></option
                ><?php endforeach; ?></select
            ></label
        >
        <label class="d-block my-2"
            >Action taken<textarea class="form-control" name="notes" maxlength="2000" required>
<?= $retry ? e(input('notes')) : '' ?></textarea>
        </label>
        <button class="btn btn-primary" name="action" value="update_assignment"
            >Record update</button
        >
    </form>
    <?php endif; ?></section
>
<?php endforeach; ?>
<h2>Response and review history</h2>
<?php if (!$history): ?><p>No updates yet.</p><?php endif; ?> <?php foreach ($history as $entry): ?><article
    class="border-bottom py-2"
    ><strong><?= e($entry['staff_name']) ?></strong> · <?= e($entry['created_at']) ?><br />Incident:
    <?= e($entry['from_status']) ?> → <?= e($entry['to_status']) ?> <?php if ($entry['assignment_id']): ?> · Assignment
    #<?= (int)$entry['assignment_id'] ?>: <?= e($entry['assignment_status']) ?> <?php endif; ?>
    <p class="preserve-text"><?= e($entry['action_taken']) ?></p></article
><?php endforeach; ?> <?php require __DIR__ . '/../../components/footer.php'; ?>
```

<a id="file-47"></a>

## resources/views/admin/response/teams.php

[Open actual source](../../../resources/views/admin/response/teams.php)

```php
<?php
require_once __DIR__ . '/../../../../app/controllers/ResponseController.php';
$controller = new ResponseController();
$id = isset($_GET['edit']) ? $controller->recordId($_GET['edit']) : null;
$team = $id ? $controller->team($id) : ['team_name' => '','contact_phone' => '','is_active' => 1];
$errors = $controller->saveTeam($id);
if (isPost()) {
    $team = ['team_name' => input('team_name'),'contact_phone' => input('contact_phone'),'is_active' => input('is_active')];
}
$teams = $controller->teams();
$pageTitle = 'Response teams';
require __DIR__ . '/../../components/header.php';
require __DIR__ . '/../../components/errors.php';
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Response teams</h1>
<p><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/records.php?type=teams')) ?>">Manage records</a></p>
<p
    >Inactive teams cannot receive new assignments. Existing assignments remain available for
    follow-up and completion.</p
>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Team</th>
                <th>Contact</th>
                <th>Availability</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($teams as $row): ?><tr>
                <td><?= e($row['team_name']) ?></td>
                <td><?= e($row['contact_phone']) ?></td>
                <td><?= $row['is_active'] ? 'Active' : 'Inactive' ?></td>
                <td><a href="<?= e(url('resources/views/admin/response/teams.php?edit=' . $row['id'])) ?>">Edit / change availability</a></td> </tr
            ><?php endforeach; ?> <?php if (!$teams): ?><tr>
                <td colspan="4">No response teams yet.</td> </tr
            ><?php endif; ?>
        </tbody>
    </table>
</div>
<h2><?= $id ? 'Edit team' : 'Add team' ?></h2>
<form method="post">
    <label class="d-block mb-3"
        >Team name<input
            class="form-control"
            name="team_name"
            maxlength="120"
            value="<?= e($team['team_name']) ?>"
            required
    /></label>
    <label class="d-block mb-3"
        >Contact number<input
            class="form-control"
            name="contact_phone"
            maxlength="20"
            value="<?= e($team['contact_phone']) ?>"
    /></label>
    <label class="d-block mb-3"
        >Availability<select class="form-select" name="is_active"
            ><option value="1" <?= (string)$team['is_active'] === '1' ? 'selected' : '' ?>>Active</option
            ><option value="0" <?= (string)$team['is_active'] === '0' ? 'selected' : '' ?>>Inactive</option></select
        ></label
    >
    <button class="btn btn-primary" name="action" value="save_team">Save team</button>
    <a href="<?= e(url('resources/views/admin/response/teams.php')) ?>">Cancel and return to response teams</a>
</form>
<?php require __DIR__ . '/../../components/footer.php'; ?>
```

<a id="file-48"></a>

## resources/views/admin/summaries/current.php

[Open actual source](../../../resources/views/admin/summaries/current.php)

```php
<?php
require_once __DIR__ . '/../../../../app/controllers/DashboardController.php';
$c = new DashboardController('admin');
$type = $_GET['type'] ?? '';
if (!in_array($type, ['households','evacuees'], true)) {
    http_response_code(400);
    exit('Invalid list.');
}
$d = $c->dashboardData();
$rows = $type === 'households'?$d['affected']:$d['stays'];
$pageTitle = $type === 'households'?'Currently affected households':'Active evacuees';
require __DIR__ . '/../../components/header.php';
?> <p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1><?= e($pageTitle) ?></h1><p><?= count($rows) ?> records.</p>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <tr>
            <th>Record</th>
            <th>Details</th>
        </tr>
        <?php foreach ($rows as $row): ?><tr>
            <td><?= e($type === 'households'?$row['household_code']:$row['person_name']) ?></td>
            <td><a href="<?= e(url($type === 'households'?'resources/views/admin/household.php?id=' . $row['id']:'resources/views/admin/evacuation/center.php?id=' . $row['center_id'])) ?>"><?= e($type === 'households'?$row['representative_name']:$row['center_name']) ?></a></td> </tr
        ><?php endforeach; ?></table
    >
</div>
<?php require __DIR__ . '/../../components/footer.php'; ?>
```

<a id="file-49"></a>

## resources/views/admin/summaries/disaster.php

[Open actual source](../../../resources/views/admin/summaries/disaster.php)

```php
<?php
require_once __DIR__ . '/../../../../app/controllers/DisasterSummaryController.php';
$controller = new DisasterSummaryController();
$period = $controller->period();
$summary = $controller->summary($period);
$matrix = [];
$statusTotals = array_fill_keys(DisasterSummaryController::STATUSES, 0);
foreach (DisasterSummaryController::TYPES as $key => $label) {
    $matrix[$key] = array_fill_keys(DisasterSummaryController::STATUSES, 0);
}
foreach ($summary['incidents'] as $row) {
    $matrix[$row['incident_type']][$row['status']] = (int)$row['total'];
    $statusTotals[$row['status']] += (int)$row['total'];
}
$capacity = 0;
$occupancy = 0;
$available = 0;
foreach ($summary['centers'] as $center) {
    $capacity += (int)$center['capacity'];
    $occupancy += (int)$center['occupancy'];
    $available += (int)$center['available'];
}
$pageTitle = 'Disaster reports and summaries';
require __DIR__ . '/../../components/header.php';
?>
<link rel="stylesheet" href="<?= e(url('resources/assets/css/disaster-summary.css')) ?>" />
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Disaster reports and summaries</h1>
<form method="get" class="filter-form no-print mb-3">
    <label>From <input type="date" name="from" value="<?= e($period['from']) ?>" required /></label>
    <label>Through <input type="date" name="to" value="<?= e($period['to']) ?>" required /></label>
    <button class="btn btn-outline-secondary">Apply period</button>
    <a href="<?= e(url('resources/views/admin/summaries/disaster.php')) ?>">Current month</a>
    <button class="btn btn-outline-secondary" type="button" id="print-summary">Print summary</button>
</form>
<p
    >Selected period: <strong><?= e($period['from']) ?> through <?= e($period['to']) ?></strong> (both dates
    included).</p
>
<p>Generated <?= e((new DateTimeImmutable('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d H:i:s')) ?> Asia/Manila.</p>
<h2>Current affected households</h2>
<p
    ><strong><?= $summary['affected'] ?></strong> households currently marked “affected” or “both” in the
    registry.</p
>
<p
    >This is the current registry designation, not a count of households affected during the
    selected period. The date filter does not apply to this number.</p
>
<h2>Current evacuation centers</h2>
<p
    >Occupancy counts individual stays without a checkout timestamp. These are current figures; the
    date filter does not apply. Available capacity includes only open, non-archived centers, including those without map points.</p
>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Center</th>
                <th>Status</th>
                <th>Capacity (people)</th>
                <th>Current occupants</th>
                <th>Available capacity (people)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($summary['centers'] as $center): ?><tr>
                <td><?= e($center['name']) ?></td>
                <td><?= e($center['status']) ?><?= $center['archived_at'] !== null ? ' (archived)' : '' ?></td>
                <td><?= (int)$center['capacity'] ?></td>
                <td><?= (int)$center['occupancy'] ?></td>
                <td><?= (int)$center['available'] ?></td> </tr
            ><?php endforeach; ?> <?php if (!$summary['centers']): ?><tr>
                <td colspan="5">No centers recorded.</td> </tr
            ><?php endif; ?> </tbody
        ><tfoot>
            <tr>
                <th colspan="2">Total (capacity includes closed and archived centers)</th>
                <td><?= $capacity ?></td>
                <td><?= $occupancy ?></td>
                <td><?= $available ?></td>
            </tr></tfoot
        >
    </table>
</div>
<h2>Incident reports submitted in the selected period</h2>
<p
    >Grouped by type and their current status. Dates use submission time, not occurrence time or the
    time a status changed. Historical incident counts include archived reports; dashboard cards and their lists count non-archived reports.</p
>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Incident type</th
                ><?php foreach (DisasterSummaryController::STATUSES as $status): ?><th><?= e(ucfirst($status)) ?></th><?php endforeach; ?><th
                    >Total</th
                >
            </tr>
        </thead>
        <tbody>
            <?php foreach (DisasterSummaryController::TYPES as $key => $label): ?><tr>
                <th><?= e($label) ?></th
                ><?php foreach ($matrix[$key] as $count): ?><td><?= $count ?></td><?php endforeach; ?><td
                    ><?= array_sum($matrix[$key]) ?></td
                > </tr
            ><?php endforeach; ?> </tbody
        ><tfoot>
            <tr>
                <th>Total</th
                ><?php foreach ($statusTotals as $count): ?><td><?= $count ?></td><?php endforeach; ?><td
                    ><?= array_sum($statusTotals) ?></td
                >
            </tr></tfoot
        >
    </table>
</div>
<h2>Relief distributed in the selected period</h2>
<p
    ><?= (int)$summary['distributions']['events'] ?> distribution events to <?= (int)$summary['distributions']['households'] ?> distinct households.
    Repeated visits to a household count once in the household total.</p
>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>Good</th>
                <th>Unit</th>
                <th>Distributed quantity</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($summary['goods'] as $good): ?><tr>
                <td><?= e($good['item_name']) ?></td>
                <td><?= e($good['unit']) ?></td>
                <td><?= (int)$good['quantity'] ?></td> </tr
            ><?php endforeach; ?> <?php if (!$summary['goods']): ?><tr>
                <td colspan="3">No goods distributed during this period.</td> </tr
            ><?php endif; ?></tbody
        >
    </table>
</div>
<p
    >Quantities are kept separate by good and unit. Manual stock-out movements are excluded. Dates
    use the distribution timestamp.</p
>
<p class="no-print"
    >This staff-only page contains totals and center/item names, without household codes, personal
    names, contact details, incident descriptions, or resident locations.</p
>
<script src="<?= e(url('resources/assets/js/print-summary.js')) ?>"></script>
<?php require __DIR__ . '/../../components/footer.php'; ?>
```

<a id="file-50"></a>

## resources/views/components/dashboard-cards.php

[Open actual source](../../../resources/views/components/dashboard-cards.php)

```php
<?php
require_once __DIR__ . '/../../../app/helpers.php';
session_checker();
if (!isset($overview)) {
    http_response_code(404);
    exit;
}
$admin = $_SESSION['ROLE'] === 'admin';
$cards = [];
foreach ($overview['incidents'] as $status => $count) {
    if (!$admin || in_array($status, ['pending','verified','responding'], true))$cards[] = [ucfirst($status) . ' incidents',$count,'resources/views/incidents/list.php?status=' . $status];
}
$centerPath = 'resources/views/maps/centers.php';
$cards[] = ['Open centers',count($overview['centers']),$centerPath];
if ($admin) {
    $available = 0;
    foreach ($overview['centers'] as $center) {
        $available += $center['available'];
    }
    $cards[] = ['Available capacity (people)',$available,$centerPath];
    $cards[] = ['Affected households',count($overview['affected']),'resources/views/admin/summaries/current.php?type=households'];
    $cards[] = ['Active evacuees',count($overview['stays']),'resources/views/admin/summaries/current.php?type=evacuees'];
} else {
    $cards[] = ['Published notices',count($overview['notices']),'resources/views/user/announcement.php'];
}
?>
<?php if ($admin): ?>
<p class="small">Available capacity counts additional people that open, non-archived centers can accommodate.
    Centers without map points are included.</p>
<?php endif; ?>
<div class="row g-3 mb-4"
    ><?php foreach ($cards as [$label,$value,$path]): ?><div class="col-sm-6 col-lg-4"
        ><a class="stat-card" href="<?= e(url($path)) ?>"
            ><strong class="stat-value"><?= (int)$value ?></strong
            ><span class="stat-label"><?= e($label) ?></span></a
        > </div
    ><?php endforeach; ?></div
>
<?php if (!$admin): ?><h2>Latest published notices</h2><?php foreach (array_slice($overview['notices'], 0, 3) as $notice): ?><p
    ><a href="<?= e(url('resources/views/user/announcement.php?q=' . urlencode($notice['title']))) ?>"><?= e($notice['title']) ?></a> · <?= e($notice['created_at']) ?></p
><?php endforeach; ?> <?php if (!$overview['notices']): ?><p>No published notices.</p><?php endif; ?>
<?php endif; ?>
```

<a id="file-51"></a>

## resources/views/components/errors.php

[Open actual source](../../../resources/views/components/errors.php)

```php
<?php if (!empty($errors)): ?><div class="alert alert-danger" role="alert"
    ><ul class="mb-0">
        <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
    </ul> </div
><?php endif; ?>
```

<a id="file-52"></a>

## resources/views/components/footer.php

[Open actual source](../../../resources/views/components/footer.php)

```php
</main><footer class="site-footer">
<div class="container"><p class="footer-brand"><?= e($settings['app_name']) ?> · <?= date('Y') ?></p><p class="small"><?= e($settings['demo_barangay_name']) ?>. Demo area only; no confirmed stakeholder or approved boundary.</p>
</div></footer>
<script src="<?= e(url('resources/assets/js/bootstrap.bundle.min.js')) ?>"></script></body></html>
```

<a id="file-53"></a>

## resources/views/components/header.php

[Open actual source](../../../resources/views/components/header.php)

```php
<?php require_once __DIR__ . '/../../../app/helpers.php'; ?> <!DOCTYPE html>
<html lang="en"
    ><head
        ><meta charset="UTF-8" /><meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        />
        <title><?= e(($pageTitle ?? 'Home') . ' | ' . $settings['app_name']) ?></title>
        <link rel="stylesheet" href="<?= e(url('resources/assets/css/bootstrap.min.css')) ?>" />
        <link rel="stylesheet" href="<?= e(url('resources/assets/css/bayanready.css?v=20261114-buttons')) ?>" /><noscript
            ><style>
                .navigation-collapse.collapse {
                    display: block;
                }
            </style></noscript
        > </head
    ><body>
        <a class="skip-link" href="#main-content">Skip to content</a>
        <?php require __DIR__ . '/navigation.php'; ?>
        <main id="main-content" class="container app-main">
            <?php require __DIR__ . '/messages.php'; ?>
```

<a id="file-54"></a>

## resources/views/components/location-picker.php

[Open actual source](../../../resources/views/components/location-picker.php)

```php
<?php
require_once __DIR__ . '/../../../app/helpers.php';
session_checker();
$point = $point ?? [];
?>
<fieldset class="location-picker mb-3" data-address-url="<?= e(($settings['address_lookup_url'] ?? 'enabled') === '' ? '' : url('resources/views/maps/address.php')) ?>"
    ><legend>Location point (optional)</legend>
    <p>Confirm the written address or landmark above. A point does not assess hazard risk.</p>
    <?php if (($settings['address_lookup_url'] ?? 'https://photon.komoot.io/reverse') !== ''): ?>
    <p class="small">Selecting or moving a pin, or using current location, sends the selected coordinates
        to <a href="https://photon.komoot.io/">Photon</a> for an address suggestion.
        Address data: <a href="https://www.openstreetmap.org/copyright">© OpenStreetMap contributors</a>.
        Nearby place names may be approximate; correct the address before saving.</p>
    <?php endif; ?>
    <p data-address-status role="status" class="small"></p>
    <?php foreach (['latitude' => 90,'longitude' => 180] as $field => $limit): ?>
    <label
        ><?= e(ucfirst($field)) ?><input
            class="form-control"
            data-coordinate="<?= e($field) ?>"
            name="<?= e($field) ?>"
            type="number"
            step="any"
            min="<?= -$limit ?>"
            max="<?= $limit ?>"
            value="<?= e($point[$field] ?? '') ?>"
    /></label>
    <?php endforeach; ?>
    <button type="button" class="btn btn-outline-secondary my-2" data-locate
        >Use my current location</button
    >
    <button type="button" class="btn btn-outline-secondary" data-clear>Clear pin</button>
    <div
        data-picker-map
        data-lat="<?= e($settings['map_latitude']) ?>"
        data-lng="<?= e($settings['map_longitude']) ?>"
        data-zoom="<?= e($settings['map_zoom']) ?>"
        data-min-zoom="<?= e($settings['map_min_zoom']) ?>"
    >
    </div>
    <p data-picker-availability class="small" role="status"
        >The map background needs internet. Written location and coordinate fields remain
        available.</p
    >
    <noscript
        >JavaScript is needed for map selection. You can still type the location and
        coordinates.</noscript
    >
    <p data-picker-status role="status"
        >Click the map or enter coordinates. Confirm the location before saving.</p
    >
</fieldset>
<link rel="stylesheet" href="<?= e(url('resources/assets/vendor/leaflet/leaflet.css')) ?>" />
<script src="<?= e(url('resources/assets/vendor/leaflet/leaflet.js')) ?>"></script>
<script src="<?= e(url('resources/assets/js/location-picker.js?v=20261114-address')) ?>" defer></script>
```

<a id="file-55"></a>

## resources/views/components/messages.php

[Open actual source](../../../resources/views/components/messages.php)

```php
<?php if (isset($_SESSION['message'])): $message = $_SESSION['message'];
    unset($_SESSION['message']); ?>
<div class="alert alert-<?= e($message['type']) ?>" role="status"><?= e($message['text']) ?></div>
<?php endif; ?>
```

<a id="file-56"></a>

## resources/views/components/navigation.php

[Open actual source](../../../resources/views/components/navigation.php)

```php
<?php
$signedIn = !empty($_SESSION['ID']);
$links = [];
if ($signedIn) {
    $links = ['Home' => dashboardPath(),'Map' => 'resources/views/maps/map.php'];
    $links += $_SESSION['ROLE'] === 'admin' ? [
        'Households' => 'resources/views/admin/households.php',
        'Incidents' => 'resources/views/incidents/list.php',
        'Response teams' => 'resources/views/admin/response/teams.php',
        'Evacuation centers' => 'resources/views/admin/evacuation/centers.php',
        'Relief goods' => 'resources/views/admin/relief/inventory.php',
        'Announcements' => 'resources/views/admin/announcement/announcement.php',
        'Disaster summaries' => 'resources/views/admin/summaries/disaster.php',
        'Manage records' => 'resources/views/admin/records.php'
    ] : [
        'Report incident' => 'resources/views/incidents/create.php',
        'My reports' => 'resources/views/incidents/list.php',
        'Announcements' => 'resources/views/user/announcement.php'
    ];
}
?>
<header class="site-header">
    <div class="container">
        <div class="brand-row">
            <a class="brand" href="<?= e(url($signedIn?dashboardPath():'index.php')) ?>"
                ><span class="brand-mark" aria-hidden="true">BR</span><?= e($settings['app_name']) ?></a
            >
            <?php if ($signedIn): ?><div class="account-menu"
                ><span class="account-name"><?= e($_SESSION['FULL_NAME']) ?> · <?= $_SESSION['ROLE'] === 'admin'?'Staff':'Resident' ?></span>
                <form method="post" action="<?= e(url(dashboardPath())) ?>">
                    <button class="btn btn-outline-secondary btn-sm" name="action" value="logout"
                        >Log out</button
                    >
                </form>
            </div>
            <?php else: ?><span class="small">Community preparedness &amp; response</span
            ><?php endif; ?>
        </div>
        <?php if ($signedIn): ?><button
            class="btn btn-outline-secondary navigation-toggle"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#main-navigation"
            aria-controls="main-navigation"
            aria-expanded="false"
            >Menu</button
        >
        <div class="collapse navigation-collapse" id="main-navigation"
            ><nav class="nav-links" aria-label="Main navigation">
                <?php foreach ($links as $navLabel => $navPath): ?><a href="<?= e(url($navPath)) ?>" <?= parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) === url($navPath)?'aria-current="page"':'' ?>
                    ><?= e($navLabel) ?></a
                ><?php endforeach; ?>
            </nav> </div
        ><?php endif; ?>
    </div></header
>
```

<a id="file-57"></a>

## resources/views/components/saved-point.php

[Open actual source](../../../resources/views/components/saved-point.php)

```php
<?php require_once __DIR__ . '/../../../app/helpers.php';
requireRole('admin'); ?> <?php if (isset($savedPoint) && hasMapPoint($savedPoint)): ?>
<p>Saved location: <?= e($savedPoint['address_details']) ?> — <?= e($savedPoint['latitude']) ?>, <?= e($savedPoint['longitude']) ?></p>
<div id="saved-incident-map" data-lat="<?= e($savedPoint['latitude']) ?>" data-lng="<?= e($savedPoint['longitude']) ?>"> </div
><p id="saved-map-status">Reported location only; no automatic hazard assessment.</p>
<link rel="stylesheet" href="<?= e(url('resources/assets/vendor/leaflet/leaflet.css')) ?>" /><script src="<?= e(url('resources/assets/vendor/leaflet/leaflet.js')) ?>"></script
><script src="<?= e(url('resources/assets/js/incident-location.js')) ?>"></script>
<?php else: ?><p>No map point. This record remains available in lists and totals.</p
><?php endif; ?>
```

<a id="file-58"></a>

## resources/views/incidents/create.php

[Open actual source](../../../resources/views/incidents/create.php)

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
    <?php if ($_SESSION['ROLE'] === 'admin'): ?>
    <label class="d-block mb-3"
        >Report received through
        <select name="report_source" class="form-select" required>
            <?php foreach (['walk_in' => 'In person','phone' => 'Phone','admin' => 'Staff observation'] as $value => $label): ?>
            <option value="<?= e($value) ?>" <?= $data['report_source'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?></select
        ></label
    >
    <?php endif; ?>
    <label class="d-block mb-3"
        >Incident type
        <select name="incident_type" class="form-select" required>
            <option value="">Choose type</option>
            <?php foreach (ReportController::TYPES as $value => $label): ?><option value="<?= e($value) ?>" <?= $data['incident_type'] === $value ? 'selected' : '' ?>
                ><?= e($label) ?></option
            ><?php endforeach; ?>
        </select></label
    >
    <?php foreach (['reporter_name' => ['Reporter/contact name',100], 'contact_phone' => ['Contact number',20], 'location_description' => ['Location description / purok / nearby landmark',255]] as $key => $field): ?>
    <label class="d-block mb-3"
        ><?= e($field[0]) ?><input
            class="form-control"
            name="<?= e($key) ?>"
            maxlength="<?= $field[1] ?>"
            value="<?= e($data[$key]) ?>"
            required
    /></label>
    <?php endforeach; ?>
    <label class="d-block mb-3"
        >Describe what happened<textarea
            class="form-control"
            name="description"
            maxlength="10000"
            required
        >
<?= e($data['description']) ?></textarea>
    </label>
    <?php $point = $data;
require __DIR__ . '/../components/location-picker.php'; ?>
    <label class="d-block mb-3"
        >Incident image (optional; JPEG, PNG, WebP; maximum 5 MiB)
        <input
            class="form-control"
            type="file"
            name="image"
            accept="image/jpeg,image/png,image/webp"
    /></label>
    <p>If a form error occurs, choose the image again before submitting.</p>
    <button class="btn btn-primary" name="action" value="create_incident">Submit report</button>
    <a href="<?= e(url('resources/views/incidents/list.php')) ?>">Cancel and return to reports</a>
</form>
<?php require __DIR__ . '/../components/footer.php'; ?>
```

<a id="file-59"></a>

## resources/views/incidents/image.php

[Open actual source](../../../resources/views/incidents/image.php)

```php
<?php
require_once __DIR__ . '/../../../app/controllers/ReportController.php';
session_checker();
$controller = new ReportController($_SESSION['ROLE']);
$controller->serveImage($controller->incidentId());
```

<a id="file-60"></a>

## resources/views/incidents/list.php

[Open actual source](../../../resources/views/incidents/list.php)

```php
<?php
require_once __DIR__ . '/../../../app/controllers/ReportController.php';
session_checker();
$controller = new ReportController($_SESSION['ROLE']);
$filters = $controller->filters();
$reports = $controller->listIncidents($filters);
$pageTitle = $_SESSION['ROLE'] === 'admin' ? 'Incident reports' : 'My incident reports';
require __DIR__ . '/../components/header.php';
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1><?= e($pageTitle) ?></h1>
<?php if ($_SESSION['ROLE'] === 'admin'): ?><p><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/records.php?type=incidents')) ?>">Manage records</a></p
><?php endif; ?>
<a class="btn btn-primary mb-3" href="<?= e(url('resources/views/incidents/create.php')) ?>">Enter incident report</a>
<form method="get" class="filter-form row g-2 mb-4">
    <label class="col-md-3"
        >Type<select class="form-select" name="incident_type"
            ><option value="">All types</option>
            <?php foreach (ReportController::TYPES as $value => $label): ?><option value="<?= e($value) ?>" <?= $filters['incident_type'] === $value ? 'selected' : '' ?>
                ><?= e($label) ?></option
            ><?php endforeach; ?></select
        ></label
    >
    <label class="col-md-3"
        >Status<select class="form-select" name="status"
            ><option value="">All statuses</option>
            <?php foreach (ReportController::STATUSES as $value): ?><option <?= $filters['status'] === $value ? 'selected' : '' ?> value="<?= e($value) ?>"
                ><?= e(ucfirst($value)) ?></option
            ><?php endforeach; ?></select
        ></label
    >
    <?php foreach (['date' => 'Submission date','location' => 'Location contains','search' => 'Description contains'] as $key => $label): ?>
    <label class="col-md-3"
        ><?= e($label) ?><input
            class="form-control"
            type="<?= $key === 'date' ? 'date' : 'text' ?>"
            name="<?= e($key) ?>"
            value="<?= e($filters[$key]) ?>"
    /></label>
    <?php endforeach; ?>
    <div>
        <button class="btn btn-outline-secondary">Search</button>
        <a href="<?= e(url('resources/views/incidents/list.php')) ?>">Clear filters</a>
    </div>
</form>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Location</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reports as $report): ?>
            <tr>
                <td><?= (int)$report['id'] ?></td>
                <td><?= e(ReportController::TYPES[$report['incident_type']] ?? $report['incident_type']) ?></td>
                <td
                    ><?= e($report['location_description']) ?> <?php if (!hasMapPoint($report)): ?><br /><small>No map point</small
                    ><?php endif; ?></td
                >
                <td
                    ><span class="status-badge" data-status="<?= e($report['status']) ?>"
                        ><?= e($report['status']) ?></span
                    ></td
                >
                <td><?= e($report['created_at']) ?></td>
                <td><a href="<?= e(url('resources/views/incidents/view.php?id=' . $report['id'])) ?>">View</a></td>
            </tr>
            <?php endforeach; ?> <?php if (!$reports): ?><tr>
                <td colspan="6">No matching reports.</td> </tr
            ><?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../components/footer.php'; ?>
```

<a id="file-61"></a>

## resources/views/incidents/view.php

[Open actual source](../../../resources/views/incidents/view.php)

```php
<?php
require_once __DIR__ . '/../../../app/controllers/ReportController.php';
session_checker();
$controller = new ReportController($_SESSION['ROLE']);
$id = $controller->incidentId();
$report = $controller->getIncident($id);
$admin = $_SESSION['ROLE'] === 'admin';
if (isPost() && !$admin) {
    http_response_code(403);
    exit('Only staff may review incidents.');
}
if ($admin && isPost() && input('action') === 'update_location') {
    $errors = $controller->updateLocation($id);
} else {
    $errors = $admin ? $controller->review($id) : [];
}
$images = $controller->images($id);
$history = $admin ? $controller->history($id) : [];
$pageTitle = 'Incident #' . $id;
require __DIR__ . '/../components/header.php';
require __DIR__ . '/../components/errors.php';
?>
<h1><?= e($pageTitle) ?></h1>
<dl>
    <?php foreach (['incident_type' => 'Type','status' => 'Status','description' => 'Description','reporter_name' => 'Reporter/contact name','contact_phone' => 'Contact number','location_description' => 'Location','latitude' => 'Latitude','longitude' => 'Longitude','report_source' => 'Received through','created_at' => 'Submitted'] as $key => $label): ?>
    <dt><?= e($label) ?></dt
    ><dd class="preserve-text"><?= e($report[$key] ?? 'Not recorded') ?></dd> <?php endforeach; ?></dl
>
<?php if (!hasMapPoint($report)): ?><p>No map point. This report remains available in lists and totals.</p
><?php endif; ?> <?php if ((!$admin || $report['archived_at'] !== null) && hasMapPoint($report)): ?>
<div id="saved-incident-map" data-lat="<?= e($report['latitude']) ?>" data-lng="<?= e($report['longitude']) ?>">
</div>
<p id="saved-map-status">Saved coordinates are listed above. Map tiles need internet.</p>
<link rel="stylesheet" href="<?= e(url('resources/assets/vendor/leaflet/leaflet.css')) ?>" />
<script src="<?= e(url('resources/assets/vendor/leaflet/leaflet.js')) ?>"></script>
<script src="<?= e(url('resources/assets/js/incident-location.js')) ?>"></script>
<?php endif; ?>
<p
    >Locations are reported points, not verified hazard assessments. No approved barangay boundary
    or location restriction is applied. DEMO ONLY locations are fictional examples.</p
>
<h2>Incident images</h2>
<?php if (!$images): ?><p>No image attached.</p><?php endif; ?> <?php foreach ($images as $image): ?>
<a href="<?= e(url('resources/views/incidents/image.php?id=' . $image['id'])) ?>"
    ><img class="incident-image" src="<?= e(url('resources/views/incidents/image.php?id=' . $image['id'])) ?>" alt="Image attached to this incident"
/></a>
<?php endforeach; ?> <?php if ($admin): ?>
<p class="mt-3"
    ><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/response/incident.php?id=' . $id)) ?>"
        >Manage response teams and actions</a
    ></p
>
<?php if ($report['archived_at'] === null): ?>
<h2>Correct incident location</h2>
<p
    >Confirm the written location and pin. A correction is recorded in staff history. The separately
    approved community-map point is unchanged.</p
>
<form method="post">
    <label class="d-block mb-3"
        >Written location<input
            class="form-control"
            name="location_description"
            maxlength="255"
            required
            value="<?= e(isPost() && input('action') === 'update_location'?input('location_description'):$report['location_description']) ?>"
    /></label>
    <?php
$point = $report;
    if (isPost() && input('action') === 'update_location') {
        $point['latitude'] = input('latitude');
        $point['longitude'] = input('longitude');
    }
    require __DIR__ . '/../components/location-picker.php';
    ?>
    <label class="d-block mb-3"
        >Reason for correction<input
            class="form-control"
            name="location_notes"
            maxlength="255"
            required
            value="<?= e(input('location_notes')) ?>"
    /></label>
    <button class="btn btn-primary" name="action" value="update_location"
        >Save location correction</button
    >
</form>
<?php endif; ?>
<h2 class="mt-4">Staff review</h2>
<p
    >Verify before assigning teams. Active assignments keep the incident responding. Complete or
    cancel them before resolving or rejecting the incident.</p
>
<form method="post">
    <label
        >Status
        <select class="form-select" name="status">
            <?php foreach (ReportController::STATUSES as $status): ?><option value="<?= e($status) ?>" <?= (isPost() && input('action') === 'review_incident' ? input('status') : $report['status']) === $status ? 'selected' : '' ?>
                ><?= e(ucfirst($status)) ?></option
            ><?php endforeach; ?></select
        ></label
    >
    <label class="d-block my-3"
        >Review notes / action taken<textarea
            class="form-control"
            name="notes"
            maxlength="255"
            required
        >
<?= e(input('notes')) ?></textarea>
    </label>
    <button name="action" value="review_incident" class="btn btn-primary">Save review</button>
</form>
<h2 class="mt-4">Staff review history</h2>
<?php foreach ($history as $entry): ?><p
    ><?= e($entry['created_at']) ?> · <?= e($entry['from_status']) ?> → <?= e($entry['to_status']) ?><br /><?= e($entry['action_taken']) ?></p
><?php endforeach; ?> <?php endif; ?>
<a class="d-block mt-3" href="<?= e(url('resources/views/incidents/list.php')) ?>">Back to reports</a>
<?php require __DIR__ . '/../components/footer.php'; ?>
```

<a id="file-62"></a>

## resources/views/maps/address.php

[Open actual source](../../../resources/views/maps/address.php)

```php
<?php
require_once __DIR__ . '/../../../app/helpers.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (empty($_SESSION['ID'])) {
    http_response_code(401);
    exit(json_encode(['error' => 'Sign in first.']));
}
$lat = filter_var($_GET['lat'] ?? '', FILTER_VALIDATE_FLOAT);
$lng = filter_var($_GET['lon'] ?? '', FILTER_VALIDATE_FLOAT);
if ($lat === false || $lng === false || abs($lat) > 90 || abs($lng) > 180) {
    http_response_code(400);
    exit(json_encode(['error' => 'Invalid coordinates.']));
}
$now = time();
$requests = $_SESSION['address_requests'] ?? [];
$recent = [];
foreach ($requests as $time) {
    if ($now - $time < 60) $recent[] = $time;
}
if (count($recent) >= 10) {
    http_response_code(429);
    exit(json_encode(['error' => 'Try again in a minute or type the address.']));
}
$recent[] = $now;
$_SESSION['address_requests'] = $recent;
session_write_close();
$url = 'https://photon.komoot.io/reverse?' . http_build_query(['lat' => $lat, 'lon' => $lng, 'limit' => 1]);
$curl = curl_init($url);
curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 7]);
$body = curl_exec($curl);
$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);
if ($body === false || $status !== 200 || !is_array(json_decode($body, true))) {
    http_response_code(502);
    exit(json_encode(['error' => 'Address unavailable. Please type it manually.']));
}
echo $body;
```

<a id="file-63"></a>

## resources/views/maps/centers.php

[Open actual source](../../../resources/views/maps/centers.php)

```php
<?php
require_once __DIR__ . '/../../../app/controllers/DashboardController.php';
session_checker();
$overview = (new DashboardController($_SESSION['ROLE']))->dashboardData();
$admin = $_SESSION['ROLE'] === 'admin';
$pageTitle = 'Open evacuation centers';
require __DIR__ . '/../components/header.php';
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Open evacuation centers</h1>
<p><?= count($overview['centers']) ?> centers. Centers without map points are still included. Available capacity is capacity minus active occupants. Closed and archived centers are excluded.</p>
<div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records">
    <table class="table">
        <tr>
            <th>Center</th>
            <th>Address</th>
            <th>Capacity</th>
            <th>Occupancy</th>
            <th>Available capacity (people)</th>
        </tr>
        <?php foreach ($overview['centers'] as $row): ?><tr>
            <td
                ><?php if ($admin): ?><a href="<?= e(url('resources/views/admin/evacuation/center.php?id=' . $row['id'])) ?>"><?= e($row['name']) ?></a
                ><?php else: ?><?= e($row['name']) ?><?php endif; ?></td
            >
            <td><?= e($row['address_details']) ?></td>
            <td><?= (int)$row['capacity'] ?></td>
            <td><?= (int)$row['occupancy'] ?></td>
            <td><?= (int)$row['available'] ?></td> </tr
        ><?php endforeach; ?></table
    >
</div>
<?php require __DIR__ . '/../components/footer.php'; ?>
```

<a id="file-64"></a>

## resources/views/maps/data.php

[Open actual source](../../../resources/views/maps/data.php)

```php
<?php
require_once __DIR__ . '/../../../app/controllers/MapController.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
if (empty($_SESSION['ID']) || !in_array($_SESSION['ROLE'] ?? '', ['admin','resident'], true)) {
    http_response_code(401);
    echo json_encode(['error' => 'Sign in to view map data.']);
    exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['error' => 'Use GET.']);
    exit;
}
try {
    echo json_encode((new MapController())->mapData(), JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['error' => 'Map data is unavailable.']);
}
```

<a id="file-65"></a>

## resources/views/maps/map.php

[Open actual source](../../../resources/views/maps/map.php)

```php
<?php
require_once __DIR__ . '/../../../app/helpers.php';
session_checker();
requireRole($_SESSION['ROLE']);
header('Cache-Control: private, no-store');
$admin = $_SESSION['ROLE'] === 'admin';
$pageTitle = 'Community map';
require __DIR__ . '/../components/header.php';
?>
<link rel="stylesheet" href="<?= e(url('resources/assets/vendor/leaflet/leaflet.css')) ?>" />

<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1><?= $admin?'Staff map':'Community map' ?></h1>
<p
    ><?= e($settings['demo_barangay_name']) ?>. Markers show saved locations; they do not predict danger or guarantee
    safety. The map opens focused on Atok; you can pan to review other saved points. No approved
    boundary is enforced.</p
>
<p>Verified incidents, open centers, and distribution points with saved coordinates appear automatically. Pending reports and household records stay off this map.</p>
<div class="map-layout">
    <aside class="map-key border rounded p-3" aria-label="Map layers and legend">
        <h2 class="h5">Layers and legend</h2>
        <p>Uncheck a layer to hide its markers. Show all locations turns every layer back on.</p>
        <label class="map-layer">
            <input type="checkbox" data-layer="incidents" checked />
            <span class="map-symbol incident" aria-hidden="true">⚠</span> Incidents
            <span id="count-incidents"></span
        ></label>
        <label class="map-layer">
            <input type="checkbox" data-layer="centers" checked />
            <span class="map-symbol center-open" aria-hidden="true">⌂</span> Evacuation centers
            <span id="count-centers"></span
        ></label>
        <div class="small mb-2">Blue shelter: open</div>
        <label class="map-layer">
            <input type="checkbox" data-layer="relief_locations" checked />
            <span class="map-symbol relief" aria-hidden="true">▣</span> Relief locations
            <span id="count-relief_locations"></span
        ></label>
        <button type="button" id="map-home" class="btn btn-outline-secondary my-2"
            >Return to demo area</button
        >
        <button type="button" id="map-fit" class="btn btn-outline-secondary my-2" disabled
            >Show all locations</button
        >
        <a class="d-block" href="<?= e(url('resources/views/maps/map.php')) ?>">Refresh saved locations</a>
        <p class="small mt-3"
            >Refresh saved locations after adding or changing a report. Some points may be outside
            the current view. Counts represent mapped records only. Records without a valid point
            remain in their normal lists and totals.</p
        >
    </aside>
    <div>
        <p class="map-status" id="community-map-status" role="status" aria-live="polite"
            >Loading saved locations…</p
        >
        <div
            id="community-map"
            data-admin="<?= $admin?'1':'0' ?>"
            data-url="<?= e(url('resources/views/maps/data.php')) ?>"
            data-base="<?= e(BASE_URL) ?>"
            data-lat="<?= e($settings['map_latitude']) ?>"
            data-lng="<?= e($settings['map_longitude']) ?>"
            data-zoom="<?= e($settings['map_zoom']) ?>"
            data-min-zoom="<?= e($settings['map_min_zoom']) ?>"
        >
        </div>
        <p id="community-tile-status" role="status" class="small"
            >Map tiles require internet. The background map does not confirm the accuracy of a
            reported point.</p
        >
        <noscript
            >Enable JavaScript to explore this map. Your regular record pages remain available
            through navigation.</noscript
        >
    </div>
</div>
<script src="<?= e(url('resources/assets/vendor/leaflet/leaflet.js')) ?>"></script>
<script src="<?= e(url('resources/assets/js/community-map.js')) ?>"></script>
<?php require __DIR__ . '/../components/footer.php'; ?>
```

<a id="file-66"></a>

## resources/views/user/announcement.php

[Open actual source](../../../resources/views/user/announcement.php)

```php
<?php
require_once __DIR__ . '/../../../app/controllers/AnnouncementController.php';
$controller = new AnnouncementController('resident');
if (isPost()) {
    http_response_code(405);
    exit('Announcements are read-only for residents.');
}
header('Cache-Control: private, no-store');
$announcements = $controller->listAnnouncements();
$pageTitle = 'Emergency announcements';
require __DIR__ . '/../components/header.php';
?>
<p class="return-link no-print"><a class="btn btn-outline-secondary" href="<?= e(url(dashboardPath())) ?>">Back to dashboard</a></p>
<h1>Emergency announcements</h1>
<form method="get" class="filter-form mb-4">
    <label>Search title <input name="q" value="<?= e($controller->queryValue('q')) ?>" /></label>
    <label>Created date <input type="date" name="date" value="<?= e($controller->queryValue('date')) ?>" /></label>
    <button class="btn btn-outline-secondary">Search</button> <a href="<?= e(url('resources/views/user/announcement.php')) ?>">Clear</a>
</form>
<?php if (!$announcements): ?><p>No published notices match your search.</p><?php endif; ?>
<?php foreach ($announcements as $announcement): ?>
<article class="notice-card"
    ><h2><?= e($announcement['title']) ?></h2
    ><p class="small">Published notice · Created <?= e($announcement['created_at']) ?></p>
    <div class="preserve-text"><?= e($announcement['message']) ?></div></article
>
<?php endforeach; ?> <?php require __DIR__ . '/../components/footer.php'; ?>
```

<a id="file-67"></a>

## resources/views/user/user.index.php

[Open actual source](../../../resources/views/user/user.index.php)

```php
<?php
require_once __DIR__ . '/../../../app/helpers.php';
requireRole('resident');
require_once __DIR__ . '/../../../app/controllers/DashboardController.php';
$overview = (new DashboardController('resident'))->dashboardData();
$pageTitle = 'Resident dashboard';
require __DIR__ . '/../components/header.php';
?>
<div class="page-intro"
    ><p class="eyebrow">Your community overview</p><h1><?= e($settings['app_name']) ?> — resident dashboard</h1
    ><p>Follow your reports and stay informed about community notices.</p>
</div>
<?php require __DIR__ . '/../components/dashboard-cards.php'; ?>
<p><a class="btn btn-primary" href="<?= e(url('resources/views/maps/map.php')) ?>">Explore saved locations on the map</a></p>
<p
    >Submit incident details for staff review, follow your own reports, and read published barangay
    notices.</p
>
<ul>
    <li><a href="<?= e(url('resources/views/incidents/create.php')) ?>">Report an incident and select its location</a></li>
    <li><a href="<?= e(url('resources/views/incidents/list.php')) ?>">View my incident reports</a></li>
    <li><a href="<?= e(url('resources/views/user/announcement.php')) ?>">Read emergency announcements</a></li>
</ul>
<p>Submitting a report does not guarantee an immediate emergency response.</p>
<?php require __DIR__ . '/../components/footer.php'; ?>
```

<a id="file-68"></a>

## resources/assets/js/community-map.js

[Open actual source](../../../resources/assets/js/community-map.js)

```javascript
(() => {
    const element = document.getElementById("community-map");
    if (!element) return;
    const status = document.getElementById("community-map-status");
    if (typeof L === "undefined") {
        status.textContent =
            "Map files could not load. Refresh the page; record pages remain available.";
        return;
    }
    const admin = element.dataset.admin === "1";
    const home = [Number(element.dataset.lat), Number(element.dataset.lng)];
    const zoom = Number(element.dataset.zoom);
    const minimumZoom = Number(element.dataset.minZoom || 11);
    const map = L.map(element, { minZoom: minimumZoom }).setView(
        home,
        zoom,
    );
    L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    })
        .on("tileerror", () => {
            document.getElementById("community-tile-status").textContent =
                "Some background tiles are unavailable. Saved markers can still be selected; refresh when internet is available.";
        })
        .addTo(map);
    const groups = {};
    const types = {
        flood: "Flooding",
        fire: "Fire",
        landslide: "Landslide",
        road_obstruction: "Road obstruction",
        other: "Other emergency",
    };
    const symbols = {
        incidents: ["incident", "⚠"],
        centers: ["center-open", "⌂"],
        relief_locations: ["relief", "▣"],
    };
    const boxes = document.querySelectorAll("[data-layer]");
    boxes.forEach((box) => {
        groups[box.dataset.layer] = L.featureGroup().addTo(map);
        box.addEventListener("change", () => {
            const group = groups[box.dataset.layer];
            if (box.checked) group.addTo(map);
            else map.removeLayer(group);
        });
    });
    function line(parent, label, value) {
        if (value === undefined || value === null || value === "") return;
        const p = document.createElement("p");
        p.textContent = label + String(value);
        parent.appendChild(p);
    }
    function popup(layer, row) {
        const node = document.createElement("div");
        node.className = "map-popup";
        let title = "",
            path = "";
        if (layer === "incidents") {
            title = types[row.incident_type] || "Incident";
            line(node, "Status: ", row.status);
            line(node, "Reported: ", row.report_date);
            line(node, "", "Saved report location.");
            path = "resources/views/incidents/view.php?id=";
        } else if (layer === "centers") {
            title = row.name;
            line(node, "Address: ", row.address_details);
            line(node, "Status: ", row.status);
            line(node, "Capacity: ", row.capacity);
            line(node, "Occupants: ", row.occupancy);
            line(node, "Available capacity (people): ", row.available);
            path = "resources/views/admin/evacuation/center.php?id=";
        } else {
            title = row.name;
            line(node, "Address: ", row.address_details);
            line(node, "Type: ", row.location_type);
            path = "resources/views/admin/relief/location.php?id=";
        }
        const heading = document.createElement("strong");
        heading.textContent = title;
        node.prepend(heading);
        if (admin && Number.isInteger(Number(row.id)) && Number(row.id) > 0) {
            const link = document.createElement("a");
            link.textContent = "Open staff record";
            link.href = element.dataset.base + "/" + path + encodeURIComponent(row.id);
            node.appendChild(link);
        }
        return node;
    }
    document.getElementById("map-home").addEventListener("click", () => {
        map.setMinZoom(minimumZoom);
        map.setView(home, zoom);
        status.textContent = "Atok demo view. Use Show all locations to see saved points outside this view.";
    });
    document.getElementById("map-fit").addEventListener("click", () => {
        const bounds = L.latLngBounds([]);
        boxes.forEach((box) => {
            box.checked = true;
            groups[box.dataset.layer].addTo(map);
            groups[box.dataset.layer].eachLayer((marker) => bounds.extend(marker.getLatLng()));
        });
        if (bounds.isValid()) {
            // Saved points can be anywhere; fit them without imposing a demo boundary.
            map.setMinZoom(1);
            map.fitBounds(bounds, { padding: [30, 30], maxZoom: 16 });
            status.textContent = "Showing all saved locations. Select a marker for details.";
        } else {
            status.textContent = "No saved locations have map points yet. The demo view is unchanged.";
        }
    });
    fetch(element.dataset.url, { credentials: "same-origin", cache: "no-store" })
        .then((response) => {
            if (!response.ok)
                throw new Error(
                    response.status === 401
                        ? "Please sign in again to view saved locations."
                        : "Saved locations could not be loaded. Please refresh.",
                );
            return response.json();
        })
        .then((data) => {
            let total = 0;
            boxes.forEach((box) => {
                const layer = box.dataset.layer,
                    group = groups[layer];
                let count = 0;
                (data[layer] || []).forEach((row) => {
                    const latValue = row.latitude;
                    const lngValue = row.longitude;
                    const lat = Number(latValue),
                        lng = Number(lngValue);
                    if (
                        latValue === null ||
                        lngValue === null ||
                        latValue === "" ||
                        lngValue === "" ||
                        !Number.isFinite(lat) ||
                        !Number.isFinite(lng) ||
                        Math.abs(lat) > 90 ||
                        Math.abs(lng) > 180
                    )
                        return;
                    let [color, symbol] = symbols[layer];
                    // Only fixed icon constants enter HTML; database strings use textContent in popup().
                    const icon = L.divIcon({
                        className: "map-marker-icon",
                        html:
                            '<span class="map-symbol ' +
                            color +
                            '" aria-hidden="true">' +
                            symbol +
                            "</span>",
                        iconSize: [28, 28],
                        iconAnchor: [14, 14],
                    });
                    L.marker([lat, lng], {
                        icon,
                        title: layer === "centers" ? "Evacuation center: " + row.status : layer,
                        keyboard: true,
                    })
                        .bindPopup(popup(layer, row))
                        .addTo(group);
                    count++;
                });
                document.getElementById("count-" + layer).textContent = "(" + count + ")";
                total += count;
            });
            status.textContent = total
                ? total + " saved locations loaded. Select a marker for details."
                : admin
                  ? "No active saved locations have map points yet. Add a point using a record form."
                  : "No verified incidents, open centers, or distribution points have map coordinates yet. Your submissions remain in My reports.";
            document.getElementById("map-fit").disabled = false;
        })
        .catch((error) => {
            status.textContent =
                error.message || "Saved locations could not be loaded. Please refresh.";
        });
})();
```

<a id="file-69"></a>

## resources/assets/js/household-checkin.js

[Open actual source](../../../resources/assets/js/household-checkin.js)

```javascript
document.querySelectorAll('[data-household-checkin]').forEach(function (form) {
    function updateSummary() {
        const arriving = form.querySelectorAll('input[name="member_ids[]"]:checked').length;
        const occupancy = Number(form.dataset.occupancy);
        const capacity = Number(form.dataset.capacity);
        const remaining = capacity - occupancy - arriving;
        form.querySelector('[data-arrival-summary]').textContent =
            'Arriving: ' + arriving + ' people. Occupancy after check-in: ' +
            (occupancy + arriving) + '. Remaining capacity: ' + remaining +
            ' people.' + (remaining < 0 ? ' Not enough capacity.' : '');
    }
    form.addEventListener('change', updateSummary);
    updateSummary();
});
```

<a id="file-70"></a>

## resources/assets/js/incident-location.js

[Open actual source](../../../resources/assets/js/incident-location.js)

```javascript
(() => {
    const element = document.getElementById("saved-incident-map");
    if (!element) return;
    if (typeof L === "undefined") {
        document.getElementById("saved-map-status").textContent =
            "Map files could not load. Saved coordinates and the written location remain available above.";
        return;
    }
    const lat = Number(element.dataset.lat),
        lng = Number(element.dataset.lng);
    if (!Number.isFinite(lat) || !Number.isFinite(lng) || Math.abs(lat) > 90 || Math.abs(lng) > 180)
        return;
    const map = L.map(element).setView([lat, lng], 16);
    L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    })
        .on("tileerror", () => {
            document.getElementById("saved-map-status").textContent =
                "Map tiles unavailable. Use the saved coordinates and location description above.";
        })
        .addTo(map);
    L.circleMarker([lat, lng], { radius: 8 }).addTo(map);
})();
```

<a id="file-71"></a>

## resources/assets/js/location-picker.js

[Open actual source](../../../resources/assets/js/location-picker.js)

```javascript
document.querySelectorAll(".location-picker").forEach((root) => {
    if (root.dataset.ready) return;
    root.dataset.ready = "1";
    const lat = root.querySelector('[data-coordinate="latitude"]'),
        lng = root.querySelector('[data-coordinate="longitude"]'),
        status = root.querySelector("[data-picker-status]"),
        el = root.querySelector("[data-picker-map]");
    const form = root.closest("form");
    const address = form.querySelector('[name="location_description"], [name="address_details"]');
    const addressStatus = root.querySelector("[data-address-status]");
    let addressTimer = null;
    let addressRequest = null;
    let addressVersion = 0;
    let lastLookup = 0;
    let lookupTimes = [];
    const addressCache = new Map();

    function cancelAddress() {
        addressVersion++;
        clearTimeout(addressTimer);
        if (addressRequest) addressRequest.abort();
    }

    // Only an explicit point choice calls this; displaying a saved pin does not.
    function suggestAddress() {
        cancelAddress();
        if (!address || !addressStatus || !root.dataset.addressUrl) return;
        if (typeof fetch === "undefined" || typeof AbortController === "undefined") {
            addressStatus.textContent = "Address lookup unavailable. Enter the address manually.";
            return;
        }
        const version = addressVersion;
        const previousText = address.value;
        const latitude = lat.value;
        const longitude = lng.value;
        const key = latitude + "," + longitude;
        addressStatus.textContent = "Looking up an address…";
        addressTimer = setTimeout(async function () {
            let timeout = null;
            try {
                let label = addressCache.get(key);
                if (!label) {
                    const now = Date.now();
                    lookupTimes = lookupTimes.filter(function (time) { return now - time < 60000; });
                    if (lookupTimes.length >= 10) {
                        addressStatus.textContent = "Address lookup limit reached. Enter the address manually or try again in a minute.";
                        return;
                    }
                    lookupTimes.push(now);
                    lastLookup = now;
                    const controller = new AbortController();
                    addressRequest = controller;
                    timeout = setTimeout(function () { controller.abort(); }, 8000);
                    const url = new URL(root.dataset.addressUrl, window.location.href);
                    url.searchParams.set("lat", latitude);
                    url.searchParams.set("lon", longitude);
                    url.searchParams.set("limit", "1");
                    url.searchParams.set("radius", "1");
                    const response = await fetch(url, {
                        signal: controller.signal,
                        credentials: url.origin === window.location.origin ? "same-origin" : "omit",
                        referrerPolicy: "no-referrer"
                    });
                    if (!response.ok) throw new Error("Address lookup failed");
                    const data = await response.json();
                    const properties = data.features && data.features[0] && data.features[0].properties;
                    if (!properties) throw new Error("No address found");
                    const parts = [];
                    for (const field of ["street", "name", "district", "city", "county", "state"]) {
                        const text = properties[field];
                        if (typeof text === "string" && text.trim() && !parts.includes(text.trim())) {
                            parts.push(text.trim());
                        }
                    }
                    label = parts.join(", ");
                    if (!label) throw new Error("No readable address");
                    // All current written-location columns accept up to 255 bytes.
                    if (new TextEncoder().encode(label).length > 255) throw new Error("Address too long");
                    if (controller.signal.aborted) throw new Error("Lookup cancelled");
                    addressCache.set(key, label);
                }
                if (version !== addressVersion || address.value !== previousText) return;
                address.value = label;
                addressStatus.textContent = "Address suggested; please confirm.";
            } catch (error) {
                if (version === addressVersion) {
                    addressStatus.textContent = "Address unavailable. Keep or enter the address manually.";
                }
            } finally {
                clearTimeout(timeout);
            }
        }, Math.max(700, 2000 - (Date.now() - lastLookup)));
    }

    if (address) {
        address.addEventListener("input", function () {
            cancelAddress();
            if (addressStatus) addressStatus.textContent = "Your written address is kept. Confirm it before saving.";
        });
    }
    form.addEventListener("submit", cancelAddress);
    let map = null,
        marker = null,
        request = 0;
    const availability = root.querySelector("[data-picker-availability]");
    if (typeof L === "undefined" && availability)
        availability.textContent =
            "Map files could not load. Refresh the page or type the location and coordinates manually.";
    if (typeof L !== "undefined") {
        map = L.map(el, { minZoom: Number(el.dataset.minZoom || 14) }).setView(
            [Number(el.dataset.lat), Number(el.dataset.lng)],
            Number(el.dataset.zoom),
        );
        L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 19,
            attribution:
                '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        })
            .on(
                "tileerror",
                () => (status.textContent = "Tiles unavailable. Manual coordinates still work."),
            )
            .addTo(map);
        map.on("click", (e) => {
            request++;
            setPoint(e.latlng.lat, e.latlng.lng);
        });
    }
    function show() {
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }
        const a = Number(lat.value),
            b = Number(lng.value);
        if (lat.value === "" && lng.value === "") {
            status.textContent = "No point selected.";
            return;
        }
        if (
            lat.value === "" ||
            lng.value === "" ||
            !Number.isFinite(a) ||
            !Number.isFinite(b) ||
            Math.abs(a) > 90 ||
            Math.abs(b) > 180
        ) {
            status.textContent = "Enter both valid coordinates.";
            return;
        }
        if (map) {
            marker = L.marker([a, b], { draggable: true }).addTo(map);
            map.setView([a, b], 16);
            marker.on("dragend", () => {
                request++;
                const p = marker.getLatLng();
                setPoint(p.lat, p.lng);
            });
        }
        status.textContent =
            "Selected: " +
            lat.value +
            ", " +
            lng.value +
            ". Confirm the point and written location before saving.";
    }
    function setPoint(a, b) {
        lat.value = a.toFixed(7);
        lng.value = b.toFixed(7);
        show();
        suggestAddress();
    }
    [lat, lng].forEach((input) =>
        input.addEventListener("input", () => {
            request++;
            cancelAddress();
            if (addressStatus) addressStatus.textContent = "Confirm the written address for these coordinates.";
            show();
        }),
    );
    root.querySelector("[data-clear]").addEventListener("click", () => {
        request++;
        cancelAddress();
        if (addressStatus) addressStatus.textContent = "Pin cleared. Written address kept.";
        lat.value = "";
        lng.value = "";
        show();
    });
    root.querySelector("[data-locate]").addEventListener("click", () => {
        cancelAddress();
        if (addressStatus) addressStatus.textContent = "";
        if (!navigator.geolocation) {
            status.textContent =
                "Current location unavailable. Click the map or enter coordinates.";
            return;
        }
        const current = ++request;
        const addressBeforeLocation = addressVersion;
        status.textContent = "Requesting your location…";
        navigator.geolocation.getCurrentPosition(
            (p) => {
                if (current !== request) return;
                const addressWasEdited = addressBeforeLocation !== addressVersion;
                setPoint(p.coords.latitude, p.coords.longitude);
                if (addressWasEdited) {
                    cancelAddress();
                    if (addressStatus) addressStatus.textContent = "Your written address is kept. Confirm it before saving.";
                }
                status.textContent +=
                    " Estimated accuracy: " +
                    Math.round(p.coords.accuracy) +
                    " meters." +
                    (p.coords.accuracy > 100 ? " Accuracy is low; adjust the pin." : "");
            },
            (e) => {
                if (current !== request) return;
                status.textContent =
                    ({
                        1: "Location permission denied.",
                        2: "Location unavailable.",
                        3: "Location request timed out.",
                    }[e.code] || "Location failed.") + " Click the map or enter coordinates.";
            },
            { timeout: 10000, maximumAge: 0, enableHighAccuracy: true },
        );
    });
    show();
});
```

<a id="file-72"></a>

## resources/assets/js/print-summary.js

[Open actual source](../../../resources/assets/js/print-summary.js)

```javascript
document.getElementById("print-summary").addEventListener("click", () => window.print());
```

<a id="file-73"></a>

## resources/assets/js/record-selection.js

[Open actual source](../../../resources/assets/js/record-selection.js)

```javascript
(() => {
    const form = document.getElementById("record-selection");
    if (!form) return;
    const boxes = Array.from(form.querySelectorAll('[name="ids[]"]')),
        all = document.getElementById("select-page"),
        count = document.getElementById("selected-count");
    function update() {
        const n = boxes.filter((b) => b.checked).length;
        count.textContent = n + " selected";
        all.checked = boxes.length > 0 && n === boxes.length;
        all.indeterminate = n > 0 && n < boxes.length;
    }
    all.addEventListener("change", () => {
        boxes.forEach((b) => (b.checked = all.checked));
        update();
    });
    boxes.forEach((b) => b.addEventListener("change", update));
    form.addEventListener("submit", (event) => {
        const selected = boxes.filter((box) => box.checked).length;
        if (!selected) {
            event.preventDefault();
            alert("Select at least one record to delete.");
        } else if (!confirm("Remove " + selected + " selected record(s)? Unused records will be permanently deleted. Linked history will be kept.")) {
            event.preventDefault();
        }
    });
    update();
})();
```

<a id="file-74"></a>

## resources/assets/css/bayanready.css

[Open actual source](../../../resources/assets/css/bayanready.css)

```css
/* BayanReady: shared styles on top of bundled Bootstrap. */
:root {
    --br-ink: #172b42;
    --br-muted: #52657a;
    --br-primary: #12675e;
    --br-dark: #0d4d46;
    --br-line: #d5dfe8;
    --br-paper: #fff;
    --br-bg: #f3f6fa;
    --bs-primary: #12675e;
    --bs-link-color: #12675e;
    --bs-link-hover-color: #0d4d46;
}
body {
    background: var(--br-bg);
    color: var(--br-ink);
    font-family:
        system-ui,
        -apple-system,
        "Segoe UI",
        sans-serif;
    line-height: 1.6;
}
a {
    color: var(--br-primary);
    text-underline-offset: 0.2em;
}
a:hover {
    color: var(--br-dark);
}
h1,
h2,
h3 {
    color: var(--br-ink);
    font-weight: 700;
    line-height: 1.25;
    overflow-wrap: anywhere;
}
h1 {
    font-size: clamp(1.7rem, 3vw, 2.3rem);
    margin-bottom: 1rem;
}
h2 {
    font-size: 1.4rem;
    margin-top: 1.6rem;
}
h3 {
    font-size: 1.15rem;
}
p {
    max-width: 85ch;
}
.small,
small,
.text-muted {
    color: var(--br-muted) !important;
}
label {
    font-weight: 600;
}
legend {
    font-size: 1.15rem;
    font-weight: 700;
}
:focus-visible {
    outline: 3px solid #a95d00 !important;
    outline-offset: 3px;
}
.skip-link {
    position: absolute;
    top: -5rem;
    left: 1rem;
    z-index: 2000;
    padding: 0.7rem;
    background: white;
}
.skip-link:focus {
    top: 1rem;
}
.site-header {
    background: white;
    border-bottom: 1px solid var(--br-line);
}
.brand-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding-block: 1rem;
    flex-wrap: wrap;
}
.brand {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    font-weight: 800;
    font-size: 1.4rem;
    text-decoration: none;
    color: var(--br-ink);
}
.brand-mark {
    background: var(--br-primary);
    color: white;
    display: grid;
    place-items: center;
    width: 2.4rem;
    height: 2.4rem;
    border-radius: 0.65rem;
    font-size: 0.8rem;
    letter-spacing: 0.05em;
}
.account-menu {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    flex-wrap: wrap;
}
.account-menu form {
    margin: 0;
}
.account-name {
    font-size: 0.9rem;
    overflow-wrap: anywhere;
}
.nav-links {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    padding-bottom: 0.75rem;
}
.nav-links a {
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    padding: 0.5rem 0.7rem;
    border-radius: 0.4rem;
}
.nav-links a:hover,
.nav-links a[aria-current="page"] {
    background: #e5f2ee;
    color: var(--br-dark);
}
.app-main {
    min-height: 65vh;
    padding-block: 2rem 3rem;
}
.page-intro {
    border-bottom: 1px solid var(--br-line);
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
}
.eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.1em;
    font-weight: 700;
    font-size: 0.75rem;
    color: var(--br-primary);
    margin-bottom: 0.4rem;
}
.page-intro p:last-child {
    margin-bottom: 0;
}
.panel,
.module-card,
.notice-card,
.map-key {
    background: var(--br-paper);
    border: 1px solid var(--br-line);
    border-radius: 0.75rem;
    padding: 1.5rem;
}
.panel h2:first-child,
.module-card h2:first-child,
.notice-card h2:first-child {
    margin-top: 0;
}
.module-card {
    height: 100%;
}
.module-card p:last-child {
    margin-bottom: 0;
}
.stat-card {
    display: block;
    height: 100%;
    background: white;
    border: 1px solid var(--br-line);
    border-top: 3px solid var(--br-primary);
    border-radius: 0.65rem;
    padding: 1.1rem 1.25rem;
    text-decoration: none;
    color: var(--br-ink);
}
.stat-card:hover {
    border-color: var(--br-primary);
    box-shadow: 0 3px 12px #172b4210;
}
.stat-value {
    display: block;
    font-size: 2rem;
    font-weight: 750;
    line-height: 1.2;
    margin-bottom: 0.4rem;
}
.stat-label {
    font-size: 0.95rem;
    color: var(--br-muted);
}
.btn {
    font-weight: 600;
    border-radius: 0.45rem;
    padding: 0.55rem 0.9rem;
}
.btn-primary,
.btn-success {
    --bs-btn-bg: var(--br-primary);
    --bs-btn-border-color: var(--br-primary);
    --bs-btn-hover-bg: var(--br-dark);
    --bs-btn-hover-border-color: var(--br-dark);
    --bs-btn-active-bg: var(--br-dark);
    --bs-btn-active-border-color: var(--br-dark);
    --bs-btn-disabled-bg: var(--br-primary);
    --bs-btn-disabled-border-color: var(--br-primary);
}
.btn-outline-primary {
    --bs-btn-color: var(--br-primary);
    --bs-btn-border-color: var(--br-primary);
    --bs-btn-hover-bg: var(--br-primary);
    --bs-btn-hover-border-color: var(--br-primary);
}
.btn-secondary {
    --bs-btn-bg: #42576b;
    --bs-btn-border-color: #42576b;
    --bs-btn-hover-bg: #2b4055;
    --bs-btn-hover-border-color: #2b4055;
}
.app-main
    input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not([type="submit"]):not(
        [type="button"]
    ),
.app-main select,
.app-main textarea {
    max-width: 100%;
    border: 1px solid #a9bac9;
    border-radius: 0.4rem;
    padding: 0.55rem 0.7rem;
    color: var(--br-ink);
    background: white;
    font: inherit;
}
.app-main textarea {
    min-height: 7rem;
}
.app-main input[type="checkbox"],
.app-main input[type="radio"] {
    accent-color: var(--br-primary);
    width: 1.1rem;
    height: 1.1rem;
    vertical-align: middle;
}
.form-control:focus,
.form-select:focus {
    border-color: var(--br-primary);
    box-shadow: 0 0 0 0.2rem #12675e25;
}
.app-main > form[method="post"] {
    background: white;
    border: 1px solid var(--br-line);
    border-radius: 0.75rem;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
}
.table {
    --bs-table-bg: white;
    --bs-table-color: var(--br-ink);
    --bs-table-border-color: var(--br-line);
    vertical-align: middle;
}
.table th {
    background: #eaf0f5;
    color: #354a60;
    font-size: 0.85rem;
}
.table td,
.table th {
    padding: 0.8rem;
}
.table-responsive {
    border: 1px solid var(--br-line);
    border-radius: 0.6rem;
    margin-bottom: 1.5rem;
}
.table-responsive .table {
    margin-bottom: 0;
}
.status-badge {
    display: inline-block;
    border: 1px solid #b1becb;
    border-radius: 2rem;
    padding: 0.15rem 0.6rem;
    background: #edf1f5;
    color: #354a60;
    font-size: 0.82rem;
    font-weight: 700;
    white-space: nowrap;
}
.status-badge[data-status="pending"],
.status-badge[data-status="responding"] {
    background: #fff4d9;
    border-color: #d5a239;
    color: #704900;
}
.status-badge[data-status="verified"],
.status-badge[data-status="open"],
.status-badge[data-status="published"],
.status-badge[data-status="active"],
.status-badge[data-status="resolved"] {
    background: #e3f3eb;
    border-color: #69a98a;
    color: #15583a;
}
.status-badge[data-status="rejected"] {
    background: #fbe8e7;
    border-color: #d79c98;
    color: #862e27;
}
.alert {
    border-radius: 0.6rem;
    border-width: 1px;
    border-left-width: 4px;
}
.alert-danger {
    color: #802d28;
    background: #fff0ee;
}
.alert-success {
    color: #17583c;
    background: #ebf7f0;
}
.notice-card {
    border-left: 4px solid var(--br-primary);
    margin-bottom: 1rem;
}
.preserve-text {
    white-space: pre-wrap;
    overflow-wrap: anywhere;
}
.incident-image {
    max-width: 100%;
    max-height: 360px;
    border-radius: 0.5rem;
}
.site-footer {
    border-top: 1px solid var(--br-line);
    padding-block: 1.5rem;
    background: white;
}
.site-footer p {
    margin: 0;
}
.footer-brand {
    font-weight: 700;
}
/* Explicit heights keep every Leaflet container visible. */
.map-layout {
    display: grid;
    grid-template-columns: 250px minmax(0, 1fr);
    gap: 1rem;
}
.map-layout > div {
    min-width: 0;
}
.map-key {
    align-self: start;
    padding: 1rem;
}
.map-key h2 {
    margin-top: 0;
}
.map-layer {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.85rem;
    cursor: pointer;
    font-size: 0.9rem;
}
.map-symbol {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    width: 28px;
    height: 28px;
    border-radius: 5px;
    color: white;
    font-size: 22px;
    font-weight: bold;
    line-height: 1;
    border: 2px solid white;
    box-shadow: 0 0 0 1px #333;
    flex-shrink: 0;
}
.incident {
    background: #a64200;
}
.center-open {
    background: #175ba3;
}
.center-closed {
    background: #555;
}
.relief {
    background: #246637;
}
.household {
    background: #733a95;
    border-radius: 50%;
}
.map-marker-icon {
    background: none;
    border: none;
}
.map-popup p {
    margin: 0.35rem 0;
    overflow-wrap: anywhere;
}
#community-map,
[data-picker-map],
#saved-incident-map {
    width: 100%;
    height: 360px;
    min-height: 280px;
    border: 1px solid #a9bac9;
    border-radius: 0.6rem;
    background: #e5ebed;
    z-index: 0;
}
#community-map {
    height: 65vh;
    min-height: 360px;
}
.location-picker {
    padding: 1rem;
    background: #f7f9fc;
    border: 1px solid var(--br-line);
    border-radius: 0.6rem;
}
.location-picker > label {
    display: inline-block;
    width: calc(50% - 0.4rem);
    margin-bottom: 0.75rem;
}
.location-picker input[data-coordinate] {
    display: block;
    width: 100%;
}
.location-picker [role="status"] {
    font-weight: 400;
    margin: 0.8rem 0 0;
}
.map-status {
    padding: 0.75rem 1rem;
    background: #eaf0f5;
    border-radius: 0.5rem;
}
@media (max-width: 767px) {
    .brand-row {
        gap: 0.6rem;
    }
    .account-menu {
        width: 100%;
        justify-content: space-between;
    }
    .nav-links {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .nav-links a {
        padding: 0.45rem;
    }
    .app-main {
        padding-top: 1.25rem;
    }
    .panel,
    .module-card,
    .notice-card {
        padding: 1rem;
    }
    .map-layout {
        grid-template-columns: 1fr;
    }
    #community-map {
        height: 55vh;
        min-height: 320px;
    }
    .location-picker > label {
        width: 100%;
    }
    .site-footer {
        font-size: 0.85rem;
    }
}
@media print {
    body {
        background: white;
    }
    .site-header,
    .skip-link,
    .btn,
    .site-footer {
        display: none;
    }
    .app-main {
        padding: 0;
    }
    .stat-card,
    .panel,
    .notice-card {
        box-shadow: none;
        break-inside: avoid;
    }
}

/* Responsive module patterns: forms stay ordinary PHP submissions. */
.navigation-toggle {
    margin-bottom: 0.75rem;
}
.filter-form {
    display: flex;
    flex-wrap: wrap;
    align-items: end;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
}
.filter-form > label {
    flex: 1 1 12rem;
    min-width: 0;
}
.filter-form input:not([type="checkbox"]),
.filter-form select {
    display: block;
    width: 100%;
}
.app-main form label {
    max-width: 100%;
    overflow-wrap: anywhere;
}
.app-main form > label {
    margin-bottom: 0.75rem;
}
.app-main form > label > select,
.app-main form > label > input:not([type="checkbox"]):not([type="radio"]) {
    display: block;
    width: 100%;
}
.app-main form button {
    max-width: 100%;
    white-space: normal;
}
.app-main p,
.app-main dd,
.app-main li {
    overflow-wrap: anywhere;
}
.table-responsive {
    max-width: 100%;
    overscroll-behavior-x: contain;
}
.table-responsive .table {
    min-width: 36rem;
}
.table td {
    overflow-wrap: anywhere;
    max-width: 28rem;
}
.table input[type="number"] {
    min-width: 7rem;
}
.table td a {
    display: inline-block;
    padding-block: 0.25rem;
}
.location-picker {
    min-width: 0;
}
.location-picker button {
    margin-bottom: 0.5rem;
}
.map-layer {
    min-height: 2.75rem;
}
.map-layer input {
    flex-shrink: 0;
}
@media (min-width: 992px) {
    .navigation-toggle {
        display: none;
    }
    .navigation-collapse.collapse {
        display: block;
    }
}
@media (max-width: 575px) {
    .filter-form {
        align-items: stretch;
    }
    .filter-form > label {
        flex-basis: 100%;
    }
    .filter-form > button,
    .filter-form > a {
        align-self: start;
    }
    .app-main form > label {
        display: block;
        width: 100%;
    }
    .app-main > form[method="post"] {
        padding: 1rem;
    }
    .app-main .btn {
        min-height: 44px;
    }
    .table td,
    .table th {
        padding: 0.65rem;
    }
    .location-picker {
        padding: 0.75rem;
    }
    .location-picker > button {
        display: block;
        width: 100%;
    }
    .nav-links a {
        min-height: 44px;
    }
}
@media print {
    .no-print {
        display: none !important;
    }
    .table-responsive {
        overflow: visible;
        border: 0;
    }
    .table-responsive .table {
        min-width: 0;
    }
    .table th,
    .table td {
        padding: 0.3rem;
        font-size: 0.8rem;
    }
}

/* Make page actions recognizable while keeping normal link navigation. */
.app-main a:not(.btn):not(.stat-card):not(.leaflet-container a) {
    display: inline-block;
    padding: 0.5rem 0.75rem;
    margin-block: 0.25rem;
    border: 1px solid var(--br-primary);
    border-radius: 0.375rem;
    background: #fff;
    text-decoration: none;
    line-height: 1.5;
    max-width: 100%;
    overflow-wrap: anywhere;
}
.app-main a:not(.btn):not(.stat-card):not(.leaflet-container a):hover {
    background: var(--br-primary);
    color: #fff;
}
.app-main a:focus-visible {
    outline: 3px solid var(--br-primary);
    outline-offset: 3px;
}
.return-link {
    margin-bottom: 1rem;
}
.app-main .btn {
    margin-block: 0.25rem;
    white-space: normal;
    overflow-wrap: anywhere;
    max-width: 100%;
}
.app-main form > .btn + a {
    display: inline-block;
    padding: 0.5rem;
}
@media (max-width: 575px) {
    .app-main form > .btn + a,
    .return-link a {
        display: inline-block;
        min-height: 44px;
        padding-block: 0.6rem;
    }
}
```

<a id="file-75"></a>

## resources/assets/css/disaster-summary.css

[Open actual source](../../../resources/assets/css/disaster-summary.css)

```css
@media print {
    @page {
        margin: 15mm;
    }
    nav,
    footer,
    .no-print {
        display: none !important;
    }
    main.container {
        width: 100%;
        max-width: none;
        padding: 0 !important;
    }
    body {
        color: #000;
        background: #fff;
        font-size: 10pt;
    }
    h1 {
        font-size: 19pt;
    }
    h2 {
        font-size: 14pt;
        margin-top: 18pt;
        break-after: avoid;
    }
    .table-responsive {
        overflow: visible;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th,
    td {
        border: 1px solid #999 !important;
        padding: 5pt !important;
    }
    thead {
        display: table-header-group;
    }
    tfoot {
        display: table-row-group;
    }
    tr {
        break-inside: avoid;
    }
}
```

<a id="file-76"></a>

## barangay_drrm.sql

[Open actual source](../../../barangay_drrm.sql)

```sql
-- BARANGAY DRRM: SIMPLE DEVELOPMENT DATABASE (v1.4)
-- MySQL 8.0.16+; new development database ONLY.
-- No sample accounts/data. Never import over SmartSlope's existing database.
-- One barangay, admin and resident roles; incident images stored separately from MySQL.
-- Fresh installation only. Existing databases: apply missing migrations in order: 001, 002, 003, 004.
-- Existing v1.2 databases: run database/migrations/003_phase_7.sql.

CREATE DATABASE IF NOT EXISTS barangay_drrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE barangay_drrm;

-- 1. Users: public registration must always force the resident role in PHP.
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(254) NOT NULL UNIQUE,
  phone VARCHAR(20) NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','resident') NOT NULL DEFAULT 'resident',
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Households: monitored OR newly affected (registered before relief distribution).
CREATE TABLE households (
  archived_at DATETIME NULL,
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  household_code VARCHAR(30) NOT NULL UNIQUE,
  representative_name VARCHAR(100) NOT NULL,
  contact_phone VARCHAR(20) NULL,
  purok VARCHAR(100) NOT NULL,
  address_details VARCHAR(255) NULL,
  registry_reason ENUM('monitored','affected','both') NOT NULL DEFAULT 'monitored',
  hazard_type ENUM('unassessed','landslide','flood','fire','other') NOT NULL DEFAULT 'unassessed',
  hazard_notes VARCHAR(255) NULL,
  vulnerability_category ENUM('unassessed','no_additional_need_identified','assistance_needed') NOT NULL DEFAULT 'unassessed',
  vulnerability_notes VARCHAR(255) NULL,
  vulnerability_assessed_by INT UNSIGNED NULL,
  vulnerability_assessed_at DATETIME NULL,
  evacuation_status ENUM('at_home','advised','evacuated','returned') NOT NULL DEFAULT 'at_home',
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (vulnerability_assessed_by) REFERENCES users(id) ON DELETE RESTRICT,
  INDEX idx_households_purok (purok),
  INDEX idx_households_representative (representative_name)
) ENGINE=InnoDB;

-- Household size is COUNT(household_members.id); do not store a second count.

-- 3. Members: linked household members; no login account required.
CREATE TABLE household_members (
  archived_at DATETIME NULL,
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  household_id INT UNSIGNED NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  age SMALLINT UNSIGNED NULL,
  notes VARCHAR(255) NULL,
  FOREIGN KEY (household_id) REFERENCES households(id) ON DELETE RESTRICT,
  INDEX idx_members_household (household_id)
) ENGINE=InnoDB;

-- 4. Incidents: private to staff and the submitting account; verification does not publish.
-- reported_by is always the logged-in creator, including admin-entered walk-in reports.
CREATE TABLE incident_reports (
  archived_at DATETIME NULL,
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reported_by INT UNSIGNED NOT NULL,
  report_source ENUM('resident','walk_in','phone','admin') NOT NULL DEFAULT 'resident',
  reporter_name VARCHAR(100) NULL,
  occurred_at DATETIME NULL,
  incident_type ENUM('landslide','flood','fire','road_obstruction','other') NOT NULL,
  description TEXT NOT NULL,
  location_description VARCHAR(255) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  contact_phone VARCHAR(20) NULL,
  status ENUM('pending','verified','responding','resolved','rejected') NOT NULL DEFAULT 'pending',
  reviewed_by INT UNSIGNED NULL,
  reviewed_at DATETIME NULL,
  review_notes VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE RESTRICT,
  FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE RESTRICT,
  INDEX idx_incidents_status_date (status, created_at)
) ENGINE=InnoDB;

-- 17. Image metadata only. File contents are kept separately on disk.
-- PHP generates stored_filename; never use the original filename as a path.
CREATE TABLE incident_images (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  incident_id INT UNSIGNED NOT NULL,
  stored_filename VARCHAR(100) NOT NULL UNIQUE,
  original_filename VARCHAR(255) NOT NULL,
  mime_type ENUM('image/jpeg','image/png','image/webp') NOT NULL,
  file_size INT UNSIGNED NOT NULL,
  uploaded_by INT UNSIGNED NOT NULL,
  uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (incident_id) REFERENCES incident_reports(id) ON DELETE RESTRICT,
  FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT,
  CHECK (file_size > 0 AND file_size <= 5242880),
  INDEX idx_images_incident (incident_id)
) ENGINE=InnoDB;

-- 5. Directory of response teams (no separate responder login role).
CREATE TABLE responders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  team_name VARCHAR(120) NOT NULL UNIQUE,
  contact_phone VARCHAR(20) NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB;

-- 6. Several teams may be assigned to one incident.
CREATE TABLE incident_assignments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  incident_id INT UNSIGNED NOT NULL,
  responder_id INT UNSIGNED NOT NULL,
  assigned_by INT UNSIGNED NOT NULL,
  assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status ENUM('assigned','on_scene','completed','cancelled') NOT NULL DEFAULT 'assigned',
  UNIQUE KEY uq_incident_team (incident_id, responder_id),
  FOREIGN KEY (incident_id) REFERENCES incident_reports(id) ON DELETE RESTRICT,
  FOREIGN KEY (responder_id) REFERENCES responders(id) ON DELETE RESTRICT,
  FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 7. Admin actions/notes for each incident.
CREATE TABLE response_updates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  incident_id INT UNSIGNED NOT NULL,
  updated_by INT UNSIGNED NOT NULL,
  action_taken TEXT NOT NULL,
  from_status ENUM('pending','verified','responding','resolved','rejected') NULL,
  to_status ENUM('pending','verified','responding','resolved','rejected') NULL,
  assignment_id INT UNSIGNED NULL,
  assignment_status ENUM('assigned','on_scene','completed','cancelled') NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (incident_id) REFERENCES incident_reports(id) ON DELETE RESTRICT,
  FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE RESTRICT,
  FOREIGN KEY (assignment_id) REFERENCES incident_assignments(id) ON DELETE RESTRICT,
  INDEX idx_response_incident_date (incident_id, created_at)
) ENGINE=InnoDB;

-- 8. Admin-written emergency announcements.
CREATE TABLE announcements (
  archived_at DATETIME NULL,
  first_published_at DATETIME NULL,
  publication_history_unknown BOOLEAN NOT NULL DEFAULT FALSE,
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  is_published BOOLEAN NOT NULL DEFAULT FALSE,
  created_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
  INDEX idx_announcements_public (is_published, created_at)
) ENGINE=InnoDB;

-- 9. Centers: capacity is in individuals; occupancy comes from active evacuation_records.
CREATE TABLE evacuation_centers (
  archived_at DATETIME NULL,
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  address_details VARCHAR(255) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  capacity INT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('open','closed') NOT NULL DEFAULT 'closed',
  CHECK (capacity >= 0)
) ENGINE=InnoDB;

-- 10. One record per individual (registered member OR walk-in).
-- checked_out_at IS NULL means currently checked in.
CREATE TABLE evacuation_walkins (
  archived_at DATETIME NULL,
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  age SMALLINT UNSIGNED NOT NULL,
  address_details VARCHAR(255) NOT NULL,
  contact_phone VARCHAR(20) NOT NULL,
  identity_key CHAR(64) NOT NULL UNIQUE,
  CHECK (age <= 130)
) ENGINE=InnoDB;

CREATE TABLE evacuation_records (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  center_id INT UNSIGNED NOT NULL,
  household_member_id INT UNSIGNED NULL,
  walkin_name VARCHAR(100) NULL,
  walkin_contact VARCHAR(20) NULL,
  checked_in_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  checked_out_at DATETIME NULL,
  recorded_by INT UNSIGNED NOT NULL,
  walkin_id INT UNSIGNED NULL,
  checked_out_by INT UNSIGNED NULL,
  active_member_id INT UNSIGNED GENERATED ALWAYS AS (CASE WHEN checked_out_at IS NULL THEN household_member_id ELSE NULL END) STORED,
  active_walkin_id INT UNSIGNED GENERATED ALWAYS AS (CASE WHEN checked_out_at IS NULL THEN walkin_id ELSE NULL END) STORED,
  UNIQUE KEY uq_evac_active_member (active_member_id),
  UNIQUE KEY uq_evac_active_walkin (active_walkin_id),
  FOREIGN KEY (walkin_id) REFERENCES evacuation_walkins(id) ON DELETE RESTRICT,
  FOREIGN KEY (checked_out_by) REFERENCES users(id) ON DELETE RESTRICT,
  CHECK (walkin_id IS NULL OR household_member_id IS NULL),
  FOREIGN KEY (center_id) REFERENCES evacuation_centers(id) ON DELETE RESTRICT,
  FOREIGN KEY (household_member_id) REFERENCES household_members(id) ON DELETE RESTRICT,
  FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE RESTRICT,
  CHECK (
    (household_member_id IS NOT NULL AND walkin_name IS NULL)
    OR (household_member_id IS NULL AND walkin_name IS NOT NULL)
  ),
  CHECK (checked_out_at IS NULL OR checked_out_at >= checked_in_at),
  INDEX idx_evac_active (center_id, checked_out_at),
  INDEX idx_evac_member_active (household_member_id, checked_out_at)
) ENGINE=InnoDB;

-- 11. Relief item catalog and current stock quantity.
CREATE TABLE relief_items (
  archived_at DATETIME NULL,
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  item_name VARCHAR(120) NOT NULL,
  unit VARCHAR(30) NOT NULL DEFAULT 'pieces',
  quantity_on_hand INT UNSIGNED NOT NULL DEFAULT 0,
  UNIQUE KEY uq_relief_item_unit (item_name, unit)
) ENGINE=InnoDB;

-- 12. Warehouse/storage/distribution points shown on the staff map.
CREATE TABLE relief_locations (
  archived_at DATETIME NULL,
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  location_type ENUM('storage','distribution','both') NOT NULL DEFAULT 'distribution',
  address_details VARCHAR(255) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL
) ENGINE=InnoDB;

-- 13. One distribution event to one registered household, possibly at a relief point.
CREATE TABLE relief_distributions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  household_id INT UNSIGNED NOT NULL,
  relief_location_id INT UNSIGNED NULL,
  received_by VARCHAR(100) NOT NULL,
  distributed_by INT UNSIGNED NOT NULL,
  distributed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  notes VARCHAR(255) NULL,
  FOREIGN KEY (household_id) REFERENCES households(id) ON DELETE RESTRICT,
  FOREIGN KEY (relief_location_id) REFERENCES relief_locations(id) ON DELETE RESTRICT,
  FOREIGN KEY (distributed_by) REFERENCES users(id) ON DELETE RESTRICT,
  INDEX idx_distribution_household_date (household_id, distributed_at)
) ENGINE=InnoDB;

-- 14. Many items per distribution; every quantity must be positive.
CREATE TABLE relief_distribution_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  distribution_id INT UNSIGNED NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  UNIQUE KEY uq_distribution_item (distribution_id, item_id),
  FOREIGN KEY (distribution_id) REFERENCES relief_distributions(id) ON DELETE RESTRICT,
  FOREIGN KEY (item_id) REFERENCES relief_items(id) ON DELETE RESTRICT,
  CHECK (quantity > 0)
) ENGINE=InnoDB;

-- 15. Stock history; negative stock prevented by transaction logic in PHP.
CREATE TABLE relief_stock_movements (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  item_id INT UNSIGNED NOT NULL,
  movement_type ENUM('stock_in','stock_out','distribution') NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  distribution_id INT UNSIGNED NULL,
  recorded_by INT UNSIGNED NOT NULL,
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (item_id) REFERENCES relief_items(id) ON DELETE RESTRICT,
  FOREIGN KEY (distribution_id) REFERENCES relief_distributions(id) ON DELETE RESTRICT,
  FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE RESTRICT,
  CHECK (quantity > 0),
  CHECK ((movement_type = 'distribution' AND distribution_id IS NOT NULL)
    OR (movement_type IN ('stock_in','stock_out') AND distribution_id IS NULL)),
  UNIQUE KEY uq_stock_distribution_item (distribution_id, item_id),
  INDEX idx_stock_item_date (item_id, created_at)
) ENGINE=InnoDB;

-- Demo labels are configured in ignored config/config.php.

-- NO extra reports table: reports are SELECT queries on the tables above.
-- All FK relationships intentionally use RESTRICT for historical integrity.
-- Delete only unlinked records; never turn on foreign_key_checks=0 to force deletion.
-- Validate coordinates, same-barangay location, uniqueness, and roles server-side.
-- Prevent duplicate active evacuation records; enforce center capacity in a transaction.
-- Record inventory distribution using transactions and SELECT ... FOR UPDATE.
-- Use password_hash/password_verify; prepared statements; session/role and ownership checks.
```
