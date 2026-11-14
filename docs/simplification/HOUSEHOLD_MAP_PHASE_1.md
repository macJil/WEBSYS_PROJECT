# Household and map revision: Phase 1 baseline

Branch: `bayanready-household-map-simplify`. This checklist governs the upcoming revisions. Earlier documents describe historical behavior; automatic map visibility is the current baseline.

## Team coding checklist

- [ ] Use four-space indentation, one statement per line, descriptive variables, and braces around conditions and loops.
- [ ] Use ordinary functions and classes with explicit constructors. Keep direct guarded PHP pages and ordinary POST forms.
- [ ] Write SQL, prepare, execute, and fetch as readable steps. Keep user values in prepared parameters.
- [ ] Keep HTML in views, request handling in controllers, and queries in models.
- [ ] Remove helpers or optional parameters only after searching production pages AND tests, recording callers, updating them, and checking behavior. No removal is approved by this inventory alone.
- [ ] Preserve password hashing/verification, sessions, role and ownership checks, escaped output, upload validation, and controlled database errors.
- [ ] Preserve transactions and duplicate/capacity/stock checks for related writes.
- [ ] Keep all eight required modules and their history. Do not remove members, images, response actions, stays, distribution items, or stock movements.
- [ ] Verify links, redirects, form actions, and direct access after each changed module.

## Course baseline

Reviewed reference folder: `/Users/mac/Documents/database2/websys/ALL/` (read-only).
Units 1–2 and FG labs support fundamentals, forms, uploads and CRUD. PHP OOP supports short functions, constructors and ordinary methods. Unit 4 and the QuickFix assessment support OOP/PDO, prepared statements, validation and escaping. MG Lab 1 explicitly introduces transactions for related records. MG Lab 2's procedural-only restriction applies to its exercise, not the entire project. Older raw-SQL/plaintext-login examples are not a reason to weaken the working application.

## Locked upcoming behavior

1. Household check-in selects active registered members, all selected initially, with absent members removable. Save one existing stay per selected person in one transaction. Reject zero selections, duplicates and insufficient capacity atomically. Keep walk-ins and individual checkout. Later registry edits do not automatically change stays.
2. Shared location picker suggests editable addresses after an explicit point/current-location choice. Never replace a saved address on load or later manual edits with stale results. Manual entry remains usable.
3. Start opening zoom at 14, verify visually, and keep Atok demo label, panning and saved coordinates. No invented boundary.
4. Available capacity (people) means the sum of remaining capacity in open, unarchived centers. Active stays determine occupancy. Centers without map points still count.
5. Use simple Bootstrap action buttons and underlined View/Edit/History/Return links. Return links have explicit destinations. Writes remain POST.

## Affected behavior and query inventory

| Area | Files and methods | Queries / checks / links to preserve |
| --- | --- | --- |
| Household check-in | EvacuationController: members, handleStay; EvacuationModel: members, checkin, occupancy, lockCenter, records, checkout | Active household members; locked center and active stays; insert one stay per person; duplicate prevention; center list/detail/edit links |
| Registry | HouseholdController: listHouseholds, getHousehold, members, saveHousehold, saveMember; HouseholdModel: getAll, findById, members, save, saveMember | Member rows determine size; archived records excluded from new selections; household detail/edit/member links |
| Locations | ReliefLocationController/Model: all, find, save; EvacuationController.saveCenter; HouseholdController.saveHousehold; Report controller/model | Written address and optional valid coordinate pair; edit correct ID; protected saved-point details |
| Dashboards | DashboardController.dashboardData; DashboardModel: incidents, centers, affected, stays, notices; dashboard-cards.php | Incident status/owner/archive filters; open centers and active-stay subquery; affected registry reason; published notices |
| Shared map | MapModel.publicMap, centers, reliefLocation, point; MapController and maps views | Explicit safe incident columns, verified/responding/resolved only; open centers; distribution/both relief points; valid coordinates |
| Shared foundation | helpers.php, config/db.php, config/config.example.php; header/navigation/footer/messages | Session and role checks, base URL, redirects, escaping, private image access, actual ignored local configuration |
| Remaining modules | Response, Relief, Announcement, DisasterSummary, RecordActions controllers/models/views | Required workflows, immutable operational history, guarded record actions, summary parity |

Dashboard links currently target `incidents/list.php?status=…`, `maps/centers.php`, `admin/summaries/current.php?type=households`, `admin/summaries/current.php?type=evacuees`, and `user/announcement.php`, relative to resources/views. Preserve ownership filtering even when residents change URL parameters.

## Small JavaScript additions

Leaflet and browser geolocation are required extensions beyond the supplied PHP lessons. Keep one shared location-picker script and the existing shared community map. Add a small reverse-geocoding request only after a user selects a point; select a provider after checking its current terms, attribution and limits. Handle errors and stale requests, and retain manual input. A service receives selected coordinates; document that behavior. No new framework or build step is planned. Bootstrap continues to handle navigation collapse. Keep legend and text alternatives usable without relying on marker colors.

## Removal review gate

Review helpers `input`, `validPhone`, `url`, `redirect`, location validation, and optional PDO constructor arguments with all callers before changing signatures. Test injection may use constructor arguments that pages omit. Existing scripts named incident-map/incident-location are review candidates only, not confirmed unused. Do not delete them based on filenames. No database table or column removal is approved for this phase; no new table is expected.

## Source inventory

The following generated inventory records current maintained PHP methods, page links/form actions, and SQL locations. It is a review reference, not a promise to edit every file.


### app/controllers/AnnouncementController.php

```text
7: public function __construct(string $role)
12: public function queryValue(string $key): string
16: public function listAnnouncements(): array
26: public function getAnnouncement(): array
40: public function save(?int $id): array
```

### app/controllers/Controller.php

```text
5: protected function success(string $message, string $path): void
8: redirect($path);
```

### app/controllers/DashboardController.php

```text
7: public function __construct(string $role)
12: public function dashboardData(): array
```

### app/controllers/DisasterSummaryController.php

```text
9: public function __construct()
14: public function period(): array
36: public function summary(array $period): array
```

### app/controllers/EvacuationController.php

```text
7: public function __construct()
12: public function recordId($value): int
20: public function center(int $id): array
28: public function centers(string $q): array
32: public function members(): array
36: public function walkins(): array
40: public function records(int $id): array
44: private function failure(Throwable $e): array
54: public function saveCenter(?int $id): array
82: public function handleStay(int $id): array
```

### app/controllers/HouseholdController.php

```text
11: public function __construct()
16: public function recordId($value): int
25: public function getHousehold(int $id): array
34: public function member(int $id, int $householdId): array
43: public function listHouseholds(string $search): array
47: public function members(int $id): array
51: public function householdInput(): array
59: public function saveHousehold(?int $id, array $data): array
110: public function saveMember(int $householdId, ?int $memberId, array $data): array
```

### app/controllers/MapController.php

```text
6: public function mapData(): array
12: public function reliefLocation($value): array
```

### app/controllers/RecordActionsController.php

```text
7: public function __construct()
12: public function listing(string $type, string $q, bool $archived, int $page): array
16: public function review(string $type, array $visibleIds): array
37: public function confirm(string $type): void
```

### app/controllers/ReliefController.php

```text
7: public function __construct()
12: public function recordId($value): int
20: public function queryValue(string $key): string
24: public function items(string $q = ''): array
28: public function item(int $id): array
36: public function households(): array
40: public function distributions(string $q): array
44: public function distribution(int $id): array
52: public function distributedGoods(int $id): array
56: public function movements(): array
66: private function failure(Throwable $e): array
76: private function quantity($value)
80: public function saveItem(?int $id): array
101: public function stock(): array
121: public function distribute(): array
```

### app/controllers/ReliefLocationController.php

```text
7: public function __construct()
12: public function all(): array
16: public function find(int $id): array
24: public function save(?int $id): array
```

### app/controllers/ReportController.php

```text
9: public function __construct(string $role)
14: private function owner(): ?int
18: public function incidentId(): int
26: public function getIncident(int $id): array
34: public function filters(): array
48: private function validDate(string $date): bool
52: public function listIncidents(array $filters): array
56: public function images(int $id): array
61: public function history(int $id): array
66: public function values(): array
74: private function storage(): string
95: private function upload(): ?array
131: public function create(array $data): array
184: public function updateLocation(int $id): array
211: public function review(int $id): array
230: public function serveImage(int $id): void
246: header('Content-Type: ' . $image['mime_type']);
247: header('X-Content-Type-Options: nosniff');
248: header('Cache-Control: private, no-store');
249: header('Content-Length: ' . filesize($path));
```

### app/controllers/ResponseController.php

```text
8: public function __construct()
13: public function recordId($value): int
21: public function team(int $id): array
29: public function incident(int $id): array
37: public function teams(): array
41: public function assignments(int $id): array
45: public function history(int $id): array
49: public function saveTeam(?int $id): array
77: public function record(int $id): array
```

### app/controllers/UserController.php

```text
6: public function handlePost(): array
```

### app/helpers.php

```text
14: function e($value): string
18: function url(string $path): string
22: function redirect(string $path): void
24: header('Location: ' . url($path), true, 303);
27: function isPost(): bool
31: function input(string $key, bool $trim = true): string
36: function validDateInput(string $value): bool
45: function validPhone(string $value, int $minimum): bool
51: function flash(string $message): void
55: function dashboardPath(): string
60: function session_checker(): void
63: redirect('index.php');
66: function requireRole(string $role): void
76: redirect('index.php');
80: set_exception_handler(function (Throwable $error): void {
85: function locationErrors(string $description, string $lat, string $lng): array
97: function hasMapPoint(array $record): bool
```

### app/models/Announcement.php

```text
6: public function __construct(?PDO $pdo = null)
10: public function listing(array $filters, bool $resident): array
28: $stmt = $this->pdo->prepare('SELECT id,title,message,is_published,created_at FROM announcements' . ($where?' WHERE ' . implode(' AND ', $where):'') . ' ORDER BY created_at DESC,id DESC');
32: public function find(int $id): array
34: $stmt = $this->pdo->prepare('SELECT * FROM announcements WHERE id=?');
38: public function save(?int $id, string $title, string $message, int $published, int $staff): void
41: $stmt = $this->pdo->prepare('INSERT INTO announcements (title,message,is_published,created_by,first_published_at) VALUES (?,?,?,?,CASE WHEN ?=1 THEN CURRENT_TIMESTAMP ELSE NULL END)');
44: $stmt = $this->pdo->prepare('UPDATE announcements SET title=?,message=?,is_published=?,first_published_at=CASE WHEN ?=1 THEN COALESCE(first_published_at,CURRENT_TIMESTAMP) ELSE first_published_at END WHERE id=? AND archived_at IS NULL');
```

### app/models/Dashboard.php

```text
6: public function __construct(?PDO $pdo = null)
10: private function rows(string $sql, array $args = []): array
16: public function incidents(?int $owner): array
18: $s = 'SELECT status,COUNT(*) AS total FROM incident_reports WHERE archived_at IS NULL';
28: public function centers(): array
30: return $this->rows("SELECT c.id,c.name,c.address_details,c.capacity,(SELECT COUNT(*) FROM evacuation_records e WHERE e.center_id=c.id AND e.checked_out_at IS NULL) AS occupancy FROM evacuation_centers c WHERE c.archived_at IS NULL AND c.status='open' ORDER BY c.name");
32: public function affected(): array
34: return $this->rows("SELECT id,household_code,representative_name FROM households WHERE archived_at IS NULL AND registry_reason IN ('affected','both') ORDER BY household_code");
36: public function stays(): array
38: return $this->rows('SELECT e.id,e.center_id,c.name AS center_name,COALESCE(m.full_name,e.walkin_name) AS person_name,e.checked_in_at FROM evacuation_records e JOIN evacuation_centers c ON c.id=e.center_id LEFT JOIN household_members m ON m.id=e.household_member_id WHERE e.checked_out_at IS NULL ORDER BY e.id');
40: public function notices(): array
42: return $this->rows('SELECT id,title,created_at FROM announcements WHERE is_published=1 AND archived_at IS NULL ORDER BY created_at DESC,id DESC');
```

### app/models/DisasterSummary.php

```text
6: public function __construct(?PDO $pdo = null)
10: private function rows(string $sql, array $values = []): array
16: public function summarize(string $start, string $endExclusive): array
21: $affected = $this->rows("SELECT COUNT(*) AS total FROM households WHERE archived_at IS NULL AND registry_reason IN ('affected','both')")[0]['total'];
22: $centers = $this->rows("SELECT c.id,c.name,c.status,c.capacity,COUNT(e.id) AS occupancy,
26: $incidents = $this->rows('SELECT incident_type,status,COUNT(*) AS total FROM incident_reports WHERE created_at>=? AND created_at<? GROUP BY incident_type,status', [$start,$endExclusive]);
27: $goods = $this->rows('SELECT i.id,i.item_name,i.unit,SUM(d.quantity) AS quantity FROM relief_distributions r JOIN relief_distribution_items d ON d.distribution_id=r.id JOIN relief_items i ON i.id=d.item_id WHERE r.distributed_at>=? AND r.distributed_at<? GROUP BY i.id,i.item_name,i.unit ORDER BY i.item_name,i.unit,i.id', [$start,$endExclusive]);
28: $distributions = $this->rows('SELECT COUNT(*) AS events,COUNT(DISTINCT household_id) AS households FROM relief_distributions WHERE distributed_at>=? AND distributed_at<?', [$start,$endExclusive])[0];
```

### app/models/Evacuation.php

```text
6: public function __construct(?PDO $pdo = null)
10: private function rows(string $sql, array $values = []): array
16: public function centers(string $search): array
19: return $this->rows("SELECT c.*, (SELECT COUNT(*) FROM evacuation_records e WHERE e.center_id=c.id AND e.checked_out_at IS NULL) AS occupancy FROM evacuation_centers c WHERE c.archived_at IS NULL AND (c.name LIKE ? ESCAPE '!' OR c.address_details LIKE ? ESCAPE '!') ORDER BY c.name", [$term,$term]);
21: public function center(int $id): array
23: $rows = $this->rows('SELECT c.*, (SELECT COUNT(*) FROM evacuation_records e WHERE e.center_id=c.id AND e.checked_out_at IS NULL) AS occupancy FROM evacuation_centers c WHERE c.id=?', [$id]);
26: private function lockCenter(int $id): array
28: $rows = $this->rows('SELECT * FROM evacuation_centers WHERE id=? FOR UPDATE', [$id]);
33: private function occupancy(int $id): int
36: return count($this->rows('SELECT id FROM evacuation_records WHERE center_id=? AND checked_out_at IS NULL FOR UPDATE', [$id]));
38: public function saveCenter(?int $id, array $data): void
52: $stmt = $this->pdo->prepare("UPDATE evacuation_centers SET latitude=?,longitude=?,name=?, address_details=?, capacity=?, status=? WHERE id=?");
55: $stmt = $this->pdo->prepare('INSERT INTO evacuation_centers (latitude,longitude,name,address_details,capacity,status) VALUES (?,?,?,?,?,?)');
64: public function members(): array
66: return $this->rows('SELECT m.id,m.full_name,h.household_code FROM household_members m JOIN households h ON h.id=m.household_id WHERE m.archived_at IS NULL AND h.archived_at IS NULL ORDER BY h.household_code,m.full_name');
68: public function walkins(): array
70: return $this->rows('SELECT * FROM evacuation_walkins WHERE archived_at IS NULL ORDER BY full_name,id');
72: public function records(int $id): array
74: return $this->rows('SELECT e.*, COALESCE(m.full_name,e.walkin_name) AS person_name, h.household_code, w.age AS walkin_age,w.address_details AS walkin_address,u.full_name AS encoder, o.full_name AS checkout_staff FROM evacuation_records e LEFT JOIN household_members m ON m.id=e.household_member_id LEFT JOIN households h ON h.id=m.household_id LEFT JOIN evacuation_walkins w ON w.id=e.walkin_id JOIN users u ON u.id=e.recorded_by LEFT JOIN users o ON o.id=e.checked_out_by WHERE e.center_id=? ORDER BY (e.checked_out_at IS NULL) DESC,e.id DESC', [$id]);
76: public function checkin(int $centerId, string $kind, int $personId, array $data, int $staff): void
92: $member = $this->rows('SELECT m.id FROM household_members m JOIN households h ON h.id=m.household_id WHERE m.id=? AND m.archived_at IS NULL AND h.archived_at IS NULL FOR UPDATE', [$personId]);
100: $stmt = $this->pdo->prepare('INSERT INTO evacuation_walkins (full_name,age,address_details,contact_phone,identity_key) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)');
104: $people = $this->rows('SELECT * FROM evacuation_walkins WHERE id=? AND archived_at IS NULL FOR UPDATE', [$personId]);
112: $legacy = $this->rows('SELECT id FROM evacuation_records WHERE household_member_id IS NULL AND walkin_id IS NULL AND checked_out_at IS NULL AND walkin_name=? AND walkin_contact=? FOR UPDATE', [$name,$phone]);
117: $stmt = $this->pdo->prepare('INSERT INTO evacuation_records (center_id,household_member_id,walkin_id,walkin_name,walkin_contact,recorded_by) VALUES (?,?,?,?,?,?)');
125: public function checkout(int $centerId, int $recordId, int $staff): void
130: $stmt = $this->pdo->prepare('UPDATE evacuation_records SET checked_out_at=CURRENT_TIMESTAMP,checked_out_by=? WHERE id=? AND center_id=? AND checked_out_at IS NULL');
```

### app/models/Household.php

```text
6: public function __construct(?PDO $pdo = null)
10: public function getAll(string $search): array
13: $sql = "SELECT h.*, (SELECT COUNT(*) FROM household_members m WHERE m.household_id = h.id AND m.archived_at IS NULL) AS member_count
22: public function findById(int $id)
24: $stmt = $this->pdo->prepare('SELECT h.*, (SELECT COUNT(*) FROM household_members m WHERE m.household_id=h.id AND m.archived_at IS NULL) AS member_count FROM households h WHERE h.id=?');
28: public function save(array $data, ?int $id): int
36: $stmt = $this->pdo->prepare('INSERT INTO households (' . implode(',', $fields) . ') VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')');
38: $stmt = $this->pdo->prepare('UPDATE households SET ' . implode(',', array_map(fn ($field) => $field . '=?', $fields)) . ' WHERE id=?');
44: public function members(int $householdId): array
46: $stmt = $this->pdo->prepare('SELECT * FROM household_members WHERE household_id=? AND archived_at IS NULL ORDER BY full_name,id');
50: public function findMember(int $id, int $householdId)
52: $stmt = $this->pdo->prepare('SELECT * FROM household_members WHERE id=? AND household_id=?');
56: public function saveMember(int $householdId, ?int $id, array $data): void
62: $stmt = $this->pdo->prepare('INSERT INTO household_members (household_id,full_name,age,notes) VALUES (?,?,?,?)');
65: $stmt = $this->pdo->prepare('UPDATE household_members SET full_name=?,age=?,notes=? WHERE id=? AND household_id=?');
```

### app/models/Map.php

```text
6: public function __construct(?PDO $pdo = null)
10: private function rows(string $sql): array
14: private function point(string $lat, string $lng): string
18: public function publicMap(): array
22: 'incidents' => $this->rows("SELECT id,incident_type,latitude,longitude,status,DATE(created_at) AS report_date FROM incident_reports WHERE archived_at IS NULL AND status IN ('verified','responding','resolved') AND " . $this->point('latitude', 'longitude')),
24: 'relief_locations' => $this->rows("SELECT id,name,address_details,latitude,longitude,location_type FROM relief_locations WHERE archived_at IS NULL AND location_type IN ('distribution','both') AND " . $this->point('latitude', 'longitude'))
27: public function reliefLocation(int $id): array
29: $stmt = $this->pdo->prepare('SELECT id,name,address_details,latitude,longitude,location_type,archived_at FROM relief_locations WHERE id=?');
33: private function centers(): array
35: return $this->rows("SELECT c.id,c.name,c.address_details,c.latitude,c.longitude,c.status,c.capacity,COUNT(e.id) AS occupancy,CASE WHEN c.capacity>COUNT(e.id) THEN c.capacity-COUNT(e.id) ELSE 0 END AS available FROM evacuation_centers c LEFT JOIN evacuation_records e ON e.center_id=c.id AND e.checked_out_at IS NULL WHERE c.archived_at IS NULL AND c.status='open' AND " . $this->point('c.latitude', 'c.longitude') . " GROUP BY c.id,c.name,c.address_details,c.latitude,c.longitude,c.status,c.capacity");
```

### app/models/RecordActions.php

```text
18: public function __construct(?PDO $pdo = null)
22: private function query(string $sql, array $args = []): array
28: private function spec(string $type): array
33: public function listing(string $type, string $search, bool $archived, int $page): array
39: return $this->query("SELECT * FROM $table WHERE $state AND ($label LIKE ? ESCAPE '!' OR CAST(id AS CHAR) LIKE ? ESCAPE '!') ORDER BY id LIMIT 50 OFFSET $offset", [$term,$term]);
41: public function selected(string $type, array $ids, bool $lock = false): array
45: $rows = $this->query("SELECT * FROM $table WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ') ORDER BY id' . ($lock?' FOR UPDATE':''), $ids);
49: private function linked(string $table, string $column, int $id, string $extra = ''): bool
52: return (bool)$this->query("SELECT id FROM $table WHERE $column=? $extra LIMIT 1 FOR UPDATE", [$id]);
54: private function check(string $type, array $r, string $action): void
73: $members = $this->query('SELECT id FROM household_members WHERE household_id=? ORDER BY id FOR UPDATE', [$id]);
84: public function apply(string $type, array $ids, string $action, array $expected): void
98: $s = $this->pdo->prepare("DELETE FROM $table WHERE id=?");
103: $s = $this->pdo->prepare("UPDATE $table SET $set WHERE id=?");
```

### app/models/Relief.php

```text
6: public function __construct(?PDO $pdo = null)
10: private function rows(string $sql, array $args = []): array
16: private function term(string $q): string
20: public function items(string $q = ''): array
22: return $this->rows("SELECT * FROM relief_items WHERE archived_at IS NULL AND (item_name LIKE ? ESCAPE '!' OR unit LIKE ? ESCAPE '!') ORDER BY item_name,unit", [$this->term($q),$this->term($q)]);
24: public function item(int $id): array
26: return $this->rows('SELECT * FROM relief_items WHERE id=?', [$id])[0] ?? [];
28: public function saveItem(?int $id, string $name, string $unit): void
33: $old = $this->rows('SELECT * FROM relief_items WHERE id=? FOR UPDATE', [$id])[0] ?? null;
37: if ($old['unit'] !== $unit && $this->rows('SELECT id FROM relief_stock_movements WHERE item_id=? LIMIT 1', [$id])) {
40: $stmt = $this->pdo->prepare('UPDATE relief_items SET item_name=?,unit=? WHERE id=?');
43: $stmt = $this->pdo->prepare('INSERT INTO relief_items (item_name,unit) VALUES (?,?)');
52: private function movement(int $item, string $type, int $quantity, ?int $distribution, int $staff, string $notes): void
54: $stmt = $this->pdo->prepare('INSERT INTO relief_stock_movements (item_id,movement_type,quantity,distribution_id,recorded_by,notes) VALUES (?,?,?,?,?,?)');
57: public function stock(int $id, string $type, int $quantity, int $staff, string $notes): void
64: $item = $this->rows('SELECT * FROM relief_items WHERE id=? FOR UPDATE', [$id])[0] ?? null;
75: $stmt = $this->pdo->prepare('UPDATE relief_items SET quantity_on_hand=? WHERE id=?');
84: public function movements(string $q, string $type, string $date): array
86: $sql = "SELECT m.*,i.item_name,i.unit,u.full_name FROM relief_stock_movements m JOIN relief_items i ON i.id=m.item_id JOIN users u ON u.id=m.recorded_by WHERE (i.item_name LIKE ? ESCAPE '!' OR m.notes LIKE ? ESCAPE '!' OR u.full_name LIKE ? ESCAPE '!')";
98: public function households(): array
100: return $this->rows('SELECT id,household_code,representative_name FROM households WHERE archived_at IS NULL ORDER BY household_code');
102: public function distribute(int $household, string $receiver, string $notes, array $goods, int $staff): int
114: if (!$this->rows('SELECT id FROM households WHERE id=? AND archived_at IS NULL FOR UPDATE', [$household])) {
120: $item = $this->rows('SELECT * FROM relief_items WHERE id=? FOR UPDATE', [$id])[0] ?? null;
128: $stmt = $this->pdo->prepare('INSERT INTO relief_distributions (household_id,received_by,distributed_by,notes) VALUES (?,?,?,?)');
132: $stmt = $this->pdo->prepare('UPDATE relief_items SET quantity_on_hand=quantity_on_hand-? WHERE id=?');
134: $stmt = $this->pdo->prepare('INSERT INTO relief_distribution_items (distribution_id,item_id,quantity) VALUES (?,?,?)');
145: public function distributions(string $q): array
147: return $this->rows("SELECT d.*,h.household_code,u.full_name FROM relief_distributions d JOIN households h ON h.id=d.household_id JOIN users u ON u.id=d.distributed_by WHERE h.household_code LIKE ? ESCAPE '!' OR d.received_by LIKE ? ESCAPE '!' ORDER BY d.id DESC", [$this->term($q),$this->term($q)]);
149: public function distribution(int $id): array
151: return $this->rows('SELECT d.*,h.household_code,u.full_name FROM relief_distributions d JOIN households h ON h.id=d.household_id JOIN users u ON u.id=d.distributed_by WHERE d.id=?', [$id])[0] ?? [];
153: public function distributedGoods(int $id): array
155: return $this->rows('SELECT d.quantity,i.item_name,i.unit FROM relief_distribution_items d JOIN relief_items i ON i.id=d.item_id WHERE d.distribution_id=? ORDER BY d.id', [$id]);
```

### app/models/ReliefLocation.php

```text
6: public function __construct(?PDO $pdo = null)
10: public function all(): array
12: return $this->pdo->query('SELECT * FROM relief_locations WHERE archived_at IS NULL ORDER BY name')->fetchAll();
14: public function find(int $id): array
16: $s = $this->pdo->prepare('SELECT * FROM relief_locations WHERE id=?');
20: public function save(?int $id, array $d): void
24: $s = $this->pdo->prepare("UPDATE relief_locations SET name=?,location_type=?,address_details=?,latitude=?,longitude=? WHERE id=? AND archived_at IS NULL");
27: $s = $this->pdo->prepare('INSERT INTO relief_locations(name,location_type,address_details,latitude,longitude) VALUES(?,?,?,?,?)');
```

### app/models/Report.php

```text
6: public function __construct(?PDO $pdo = null)
10: public function listing(array $filters, ?int $owner): array
34: $stmt = $this->pdo->prepare('SELECT * FROM incident_reports' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY created_at DESC, id DESC');
38: public function find(int $id, ?int $owner)
40: $stmt = $this->pdo->prepare('SELECT * FROM incident_reports WHERE id = ?' . ($owner !== null ? ' AND reported_by = ?' : ''));
44: public function images(int $id): array
46: $stmt = $this->pdo->prepare('SELECT id FROM incident_images WHERE incident_id = ? ORDER BY id');
50: public function image(int $id, ?int $owner)
52: $stmt = $this->pdo->prepare('SELECT i.* FROM incident_images i JOIN incident_reports r ON r.id = i.incident_id WHERE i.id = ?' . ($owner !== null ? ' AND r.reported_by = ?' : ''));
56: public function history(int $id): array
58: $stmt = $this->pdo->prepare('SELECT action_taken, from_status, to_status, created_at FROM response_updates WHERE incident_id = ? ORDER BY id');
62: public function create(array $data, ?array $image): int
67: $stmt = $this->pdo->prepare('INSERT INTO incident_reports (' . implode(',', $fields) . ') VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')');
71: $stmt = $this->pdo->prepare('INSERT INTO incident_images (incident_id,stored_filename,original_filename,mime_type,file_size,uploaded_by) VALUES (?,?,?,?,?,?)');
81: public function updateLocation(int $id, string $description, ?string $lat, ?string $lng, string $notes, int $staff): void
85: $stmt = $this->pdo->prepare('SELECT location_description,latitude,longitude,status,archived_at FROM incident_reports WHERE id=? FOR UPDATE');
91: $stmt = $this->pdo->prepare('UPDATE incident_reports SET location_description=?,latitude=?,longitude=? WHERE id=?');
94: $stmt = $this->pdo->prepare('INSERT INTO response_updates(incident_id,updated_by,action_taken,from_status,to_status) VALUES(?,?,?,?,?)');
102: public function review(int $id, string $status, string $notes, int $staff): void
106: $stmt = $this->pdo->prepare('SELECT status FROM incident_reports WHERE id = ? AND archived_at IS NULL FOR UPDATE');
112: $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM incident_assignments WHERE incident_id=? AND status IN ('assigned','on_scene')");
121: $stmt = $this->pdo->prepare("UPDATE incident_reports SET status=?, reviewed_by=?, reviewed_at=CURRENT_TIMESTAMP, review_notes=? WHERE id=?");
123: $stmt = $this->pdo->prepare('INSERT INTO response_updates (incident_id,updated_by,action_taken,from_status,to_status) VALUES (?,?,?,?,?)');
```

### app/models/Response.php

```text
6: public function __construct(?PDO $pdo = null)
10: public function teams(): array
12: return $this->pdo->query('SELECT * FROM responders WHERE is_active=1 ORDER BY is_active DESC, team_name')->fetchAll();
14: public function team(int $id)
16: $stmt = $this->pdo->prepare('SELECT * FROM responders WHERE id=?');
20: public function saveTeam(?int $id, string $name, string $phone, int $active): void
23: $stmt = $this->pdo->prepare('UPDATE responders SET team_name=?, contact_phone=?, is_active=? WHERE id=?');
26: $stmt = $this->pdo->prepare('INSERT INTO responders (team_name,contact_phone,is_active) VALUES (?,?,?)');
30: public function incident(int $id)
32: $stmt = $this->pdo->prepare('SELECT * FROM incident_reports WHERE id=?');
36: public function assignments(int $id): array
38: $stmt = $this->pdo->prepare('SELECT a.*, r.team_name, r.contact_phone, r.is_active, u.full_name AS assigned_by_name FROM incident_assignments a JOIN responders r ON r.id=a.responder_id JOIN users u ON u.id=a.assigned_by WHERE a.incident_id=? ORDER BY a.id');
42: public function history(int $id): array
44: $stmt = $this->pdo->prepare('SELECT h.*, u.full_name AS staff_name FROM response_updates h JOIN users u ON u.id=h.updated_by WHERE h.incident_id=? ORDER BY h.id');
49: public function record(int $incidentId, ?int $teamId, ?int $assignmentId, string $status, string $notes, int $staff): void
53: $stmt = $this->pdo->prepare('SELECT status FROM incident_reports WHERE id=? AND archived_at IS NULL FOR UPDATE');
60: $stmt = $this->pdo->prepare('SELECT * FROM responders WHERE id=? FOR UPDATE');
66: $stmt = $this->pdo->prepare('SELECT id FROM incident_assignments WHERE incident_id=? AND responder_id=?');
71: $stmt = $this->pdo->prepare('INSERT INTO incident_assignments (incident_id,responder_id,assigned_by) VALUES (?,?,?)');
77: $stmt = $this->pdo->prepare('SELECT a.*, r.team_name, r.is_active FROM incident_assignments a JOIN responders r ON r.id=a.responder_id WHERE a.id=? AND a.incident_id=? FOR UPDATE');
95: $stmt = $this->pdo->prepare('UPDATE incident_assignments SET status=? WHERE id=? AND incident_id=?');
101: $stmt = $this->pdo->prepare('UPDATE incident_reports SET status=? WHERE id=?');
103: $stmt = $this->pdo->prepare('INSERT INTO response_updates (incident_id,updated_by,action_taken,from_status,to_status,assignment_id,assignment_status) VALUES (?,?,?,?,?,?,?)');
```

### app/models/User.php

```text
6: public function __construct(?PDO $pdo = null)
10: public function findByEmail(string $email)
12: $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
16: public function createResident(array $data): void
18: $stmt = $this->pdo->prepare("INSERT INTO users (full_name, username, email, phone, password, role) VALUES (?, ?, ?, ?, ?, 'resident')");
```

### resources/views/admin/admin.index.php

```text
14: <p><a class="btn btn-primary" href="<?= e(url('resources/views/maps/map.php')) ?>">Explore saved locations on the map</a></p>
28: ><h2 class="h4"><a href="<?= e(url('resources/views/admin/' . $path)) ?>"><?= e($title) ?></a></h2
```

### resources/views/admin/announcement/announcement.php

```text
11: <p><a href="<?= e(url('resources/views/admin/records.php?type=announcements')) ?>">Select records / view archived</a></p>
31: <button class="btn btn-secondary">Search</button> <a href="<?= e(url('resources/views/admin/announcement/announcement.php')) ?>">Clear</a>
52: <td><a href="<?= e(url('resources/views/admin/announcement/announcement.update.php?id=' . $row['id'])) ?>">View / edit / publish / withdraw</a></td> </tr
```

### resources/views/admin/announcement/announcement.update.php

```text
17: <a class="d-block mt-3" href="<?= e(url('resources/views/admin/announcement/announcement.php')) ?>">Back to announcements</a>
```

### resources/views/admin/announcement/editor.php

```text

```

### resources/views/admin/evacuation/center.php

```text
16: <p><a href="<?= e(url('resources/views/admin/records.php?type=walkins')) ?>">Select records / view archived</a></p>
21: <a href="<?= e(url('resources/views/admin/evacuation/centers.php')) ?>">All centers</a> ·
22: <a href="<?= e(url('resources/views/admin/evacuation/centers.php?edit=' . $id)) ?>">Edit / close center</a>
```

### resources/views/admin/evacuation/centers.php

```text
19: <p><a href="<?= e(url('resources/views/admin/records.php?type=centers')) ?>">Select records / view archived</a></p>
22: <button class="btn btn-secondary">Search</button> <a href="<?= e(url('resources/views/admin/evacuation/centers.php')) ?>">Clear</a>
51: ><a href="<?= e(url('resources/views/admin/evacuation/center.php?id=' . $center['id'])) ?>">People / history</a> ·
52: <a href="<?= e(url('resources/views/admin/evacuation/centers.php?edit=' . $center['id'])) ?>">Edit / close</a></td
94: <a href="<?= e(url('resources/views/admin/evacuation/centers.php')) ?>">New center / cancel edit</a>
```

### resources/views/admin/household.php

```text
17: <a href="<?= e(url('resources/views/admin/households.php')) ?>">Back to registry</a>
19: <p><a href="<?= e(url('resources/views/admin/records.php?type=members')) ?>">Select records / view archived</a></p>
20: <a class="btn btn-outline-primary mb-3" href="<?= e(url('resources/views/admin/households.php?edit=' . $id)) ?>">Edit household</a>
49: <td><a href="<?= e(url('resources/views/admin/household.php?id=' . $id . '&member=' . $member['id'])) ?>">View / edit</a></td> </tr
58: <form method="post" action="<?= e(url('resources/views/admin/household.php?id=' . $id . ($memberId === null ? '' : '&member=' . $memberId))) ?>">
83: <a href="<?= e(url('resources/views/admin/household.php?id=' . $id)) ?>">Cancel / new member</a>
```

### resources/views/admin/households.php

```text
16: <p><a href="<?= e(url('resources/views/admin/records.php?type=households')) ?>">Select records / view archived</a></p>
28: ><a class="btn btn-outline-secondary" href="<?= e(url('resources/views/admin/households.php')) ?>">Clear</a>
51: ><a href="<?= e(url('resources/views/admin/household.php?id=' . $row['id'])) ?>">View / members</a> ·
52: <a href="<?= e(url('resources/views/admin/households.php?edit=' . $row['id'])) ?>">Edit</a></td
62: <form method="post" action="<?= e(url('resources/views/admin/households.php' . ($id === null ? '' : '?edit=' . $id))) ?>">
110: <a href="<?= e(url('resources/views/admin/households.php')) ?>">Cancel / new household</a>
```

### resources/views/admin/records.php

```text
64: <a href="<?= e(url('resources/views/admin/records.php?type=' . $type)) ?>">Cancel</a>
94: <a href="<?= e($link) ?>">View record</a></td
113: href="?<?= e(http_build_query($params + ['page' => $page - 1])) ?>"
115: ><?php endif; ?> <?php if (count($rows) === 50): ?><a href="?<?= e(http_build_query($params + ['page' => $page + 1])) ?>">Next</a
```

### resources/views/admin/relief/distributions.php

```text
21: ><a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Inventory</a> ·
22: <a href="<?= e(url('resources/views/admin/relief/movements.php')) ?>">Stock movements</a> ·
23: <a href="<?= e(url('resources/views/admin/relief/distributions.php')) ?>">Household distributions</a></p
115: <button class="btn btn-secondary">Search</button> <a href="<?= e(url('resources/views/admin/relief/distributions.php')) ?>">Clear</a>
134: <td><a href="<?= e(url('resources/views/admin/relief/distributions.php?id=' . $row['id'])) ?>">View #<?= (int)$row['id'] ?></a></td> </tr
```

### resources/views/admin/relief/inventory.php

```text
17: <p><a href="<?= e(url('resources/views/admin/records.php?type=goods')) ?>">Select records / view archived</a></p
19: ><p><a href="<?= e(url('resources/views/admin/relief/locations.php')) ?>">Manage relief locations</a></p>
21: ><a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Inventory</a> ·
22: <a href="<?= e(url('resources/views/admin/relief/movements.php')) ?>">Stock movements</a> ·
23: <a href="<?= e(url('resources/views/admin/relief/distributions.php')) ?>">Household distributions</a></p
28: <button class="btn btn-secondary">Search</button> <a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Clear</a>
45: <td><a href="<?= e(url('resources/views/admin/relief/inventory.php?edit=' . $row['id'])) ?>">Edit</a></td> </tr
75: <a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">New good / cancel edit</a>
```

### resources/views/admin/relief/location.php

```text
16: <a href="<?= e(url('resources/views/maps/map.php')) ?>">Back to map</a>
```

### resources/views/admin/relief/locations.php

```text
20: <p><a href="<?= e(url('resources/views/admin/records.php?type=locations')) ?>">Select records / view archived</a></p>
24: — <a href="<?= e(url('resources/views/admin/relief/location.php?id=' . $row['id'])) ?>">View</a> ·
25: <a href="?edit=<?= (int)$row['id'] ?>">Edit</a></li
46: <a href="locations.php">New location / cancel</a>
```

### resources/views/admin/relief/movements.php

```text
13: ><a href="<?= e(url('resources/views/admin/relief/inventory.php')) ?>">Inventory</a> ·
14: <a href="<?= e(url('resources/views/admin/relief/movements.php')) ?>">Stock movements</a> ·
15: <a href="<?= e(url('resources/views/admin/relief/distributions.php')) ?>">Household distributions</a></p
77: <button class="btn btn-secondary">Search</button> <a href="<?= e(url('resources/views/admin/relief/movements.php')) ?>">Clear</a>
100: href="<?= e(url('resources/views/admin/relief/distributions.php?id=' . $row['distribution_id'])) ?>"
```

### resources/views/admin/response/incident.php

```text
18: <a href="<?= e(url('resources/views/incidents/view.php?id=' . $id)) ?>">Incident details / staff review</a>
48: >No unassigned active teams. <a href="<?= e(url('resources/views/admin/response/teams.php')) ?>">Manage teams</a> or reactivate an
```

### resources/views/admin/response/teams.php

```text
16: <p><a href="<?= e(url('resources/views/admin/records.php?type=teams')) ?>">Select records / view archived</a></p>
36: <td><a href="<?= e(url('resources/views/admin/response/teams.php?edit=' . $row['id'])) ?>">Edit / change availability</a></td> </tr
67: <a href="<?= e(url('resources/views/admin/response/teams.php')) ?>">New team / cancel edit</a>
```

### resources/views/admin/summaries/current.php

```text
22: <td><a href="<?= e(url($type === 'households'?'resources/views/admin/household.php?id=' . $row['id']:'resources/views/admin/evacuation/center.php?id=' . $row['center_id'])) ?>"><?= e($type === 'households'?$row['representative_name']:$row['center_name']) ?></a></td> </tr
```

### resources/views/admin/summaries/disaster.php

```text
26: <link rel="stylesheet" href="<?= e(url('resources/assets/css/disaster-summary.css')) ?>" />
32: <a href="<?= e(url('resources/views/admin/summaries/disaster.php')) ?>">Current month</a>
```

### resources/views/components/dashboard-cards.php

```text
27: ><a class="stat-card" href="<?= e(url($path)) ?>"
34: ><a href="<?= e(url('resources/views/user/announcement.php?q=' . urlencode($notice['title']))) ?>"><?= e($notice['title']) ?></a> · <?= e($notice['created_at']) ?></p
```

### resources/views/components/errors.php

```text

```

### resources/views/components/footer.php

```text

```

### resources/views/components/header.php

```text
9: <link rel="stylesheet" href="<?= e(url('resources/assets/css/bootstrap.min.css')) ?>" />
10: <link rel="stylesheet" href="<?= e(url('resources/assets/css/bayanready.css')) ?>" /><noscript
18: <a class="skip-link" href="#main-content">Skip to content</a>
```

### resources/views/components/location-picker.php

```text
46: <link rel="stylesheet" href="<?= e(url('resources/assets/vendor/leaflet/leaflet.css')) ?>" />
```

### resources/views/components/messages.php

```text

```

### resources/views/components/navigation.php

```text
25: <a class="brand" href="<?= e(url($signedIn?dashboardPath():'index.php')) ?>"
30: <form method="post" action="<?= e(url(dashboardPath())) ?>">
50: <?php foreach ($links as $navLabel => $navPath): ?><a href="<?= e(url($navPath)) ?>" <?= parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) === url($navPath)?'aria-current="page"':'' ?>
```

### resources/views/components/saved-point.php

```text
6: <link rel="stylesheet" href="<?= e(url('resources/assets/vendor/leaflet/leaflet.css')) ?>" /><script src="<?= e(url('resources/assets/vendor/leaflet/leaflet.js')) ?>"></script
```

### resources/views/incidents/create.php

```text

```

### resources/views/incidents/image.php

```text

```

### resources/views/incidents/list.php

```text
11: <?php if ($_SESSION['ROLE'] === 'admin'): ?><p><a href="<?= e(url('resources/views/admin/records.php?type=incidents')) ?>">Select records / view archived</a></p
13: <a class="btn btn-primary mb-3" href="<?= e(url('resources/views/incidents/create.php')) ?>">Enter incident report</a>
42: <a href="<?= e(url('resources/views/incidents/list.php')) ?>">Clear filters</a>
72: <td><a href="<?= e(url('resources/views/incidents/view.php?id=' . $report['id'])) ?>">View</a></td>
```

### resources/views/incidents/view.php

```text
34: <link rel="stylesheet" href="<?= e(url('resources/assets/vendor/leaflet/leaflet.css')) ?>" />
44: <a href="<?= e(url('resources/views/incidents/image.php?id=' . $image['id'])) ?>"
49: ><a class="btn btn-secondary" href="<?= e(url('resources/views/admin/response/incident.php?id=' . $id)) ?>"
118: <a class="d-block mt-3" href="<?= e(url('resources/views/incidents/list.php')) ?>">Back to reports</a>
```

### resources/views/maps/centers.php

```text
22: ><?php if ($admin): ?><a href="<?= e(url('resources/views/admin/evacuation/center.php?id=' . $row['id'])) ?>"><?= e($row['name']) ?></a
```

### resources/views/maps/data.php

```text
3: header('Content-Type: application/json; charset=utf-8');
4: header('Cache-Control: private, no-store');
5: header('X-Content-Type-Options: nosniff');
13: header('Allow: GET');
```

### resources/views/maps/map.php

```text
5: header('Cache-Control: private, no-store');
10: <link rel="stylesheet" href="<?= e(url('resources/assets/vendor/leaflet/leaflet.css')) ?>" />
45: <a class="d-block" href="<?= e(url('resources/views/maps/map.php')) ?>">Refresh saved locations</a>
```

### resources/views/user/announcement.php

```text
8: header('Cache-Control: private, no-store');
17: <button class="btn btn-secondary">Search</button> <a href="<?= e(url('resources/views/user/announcement.php')) ?>">Clear</a>
```

### resources/views/user/user.index.php

```text
14: <p><a class="btn btn-primary" href="<?= e(url('resources/views/maps/map.php')) ?>">Explore saved locations on the map</a></p>
20: <li><a href="<?= e(url('resources/views/incidents/create.php')) ?>">Report an incident and select its location</a></li>
21: <li><a href="<?= e(url('resources/views/incidents/list.php')) ?>">View my incident reports</a></li>
22: <li><a href="<?= e(url('resources/views/user/announcement.php')) ?>">Read emergency announcements</a></li>
```

### config/config.example.php

```text

```

### config/db.php

```text
4: function db(): PDO
```

### config/local.php

```text

```

## Verification

Phase 1 is documentation only. All eight required modules are retained. No helper, parameter, page, asset or schema field has been removed. Runtime acceptance belongs to the implementation phases; historical test results are not a new test run.
