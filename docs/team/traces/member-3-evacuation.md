# Member 3 — Household evacuation check-in and checkout

These are study notes for the pre-formatting source at commit 954885b, not application changes or a claim that a live demonstration has passed. Use fictional examples. The complete primary request is traced below, with exact source excerpts and explanations. Source links are relative to the repository and include line anchors. The main presentation should show only the marked 60–90 second segment; use the rest to prepare for questions.

All PHP save paths run on the server through MAMP. The browser sends form values, not PHP objects. A successful POST redirects to a new GET; validation errors remain on the form. `[]` means an empty array, `$this` means the current object, `->` accesses an object, `?int` accepts integer or null, and `: array` declares a returned array. `prepare()` creates a SQL statement and `execute()` supplies its values.
## Focus and example

Own centers, partial household arrivals, walk-ins, capacity and stay history. Present the code on slide 15. Example: center 4 has capacity 5, occupancy 1, household 8 has five active members, and staff select IDs 21, 22 and 23. These are illustrative IDs. The result is three new stay rows, occupancy 4 and available capacity 1.

### Complete execution order

GET center; choose household using GET; load active members selected by default. POST `checkin_household` with only arriving member IDs. Controller guards/validates and calls model. Model locks center/household, checks whole-batch capacity and each person's membership/active stay, inserts all stays, commits and redirects. New GET recalculates occupancy from active rows.

## 1. Page preparation and selection

Source: [resources/views/admin/evacuation/center.php, lines 1–30](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/resources/views/admin/evacuation/center.php#L1). Exact excerpt, not pseudocode.

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
```

The center ID is validated and the staff controller handles the POST before output. GET `household_id` chooses which members to display. On first selection, `array_column()` selects all member IDs. On a returned POST error, the posted selection is kept instead. `$arrivingCount` is a display calculation. It is not trusted as the authoritative database occupancy. The center is loaded again before rendering.

## 2. Browser form sends individual member IDs

Source: [resources/views/admin/evacuation/center.php, lines 62–80](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/resources/views/admin/evacuation/center.php#L62). Exact excerpt, not pseudocode.

```php
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
```

The checkbox name `member_ids[]` becomes a PHP array. Unchecked members are omitted by the browser. `household_id` identifies the intended household, while the submitted button supplies `action=checkin_household`. Capacity/occupancy data attributes support a JavaScript preview only. Hiding the form when full does not replace model validation.

## 3. Controller dispatches the operation

Source: [app/controllers/EvacuationController.php, lines 90–128](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/app/controllers/EvacuationController.php#L90). Exact excerpt, not pseudocode.

```php
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
```

The constructor already requires admin access. `handleStay()` first ignores GET. A household arrival needs an array of member IDs and a positive household ID. The current staff ID comes from the session. Other branches handle checkout or existing/new walk-ins. An unknown action is rejected. Expected errors return to the current form; successful operations call the shared message/303 redirect to this center.

## 4. Validate the whole batch and write stays

Source: [app/models/Evacuation.php, lines 96–148](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/app/models/Evacuation.php#L96). Exact excerpt, not pseudocode.

```php
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
```

Follow each block:

1. Empty selection fails. IDs must be positive scalar integers; casting normalizes strings such as `"21"`. Duplicate IDs fail rather than counting the same person twice. Sorting makes lock order more consistent.
2. Begin a transaction, lock the center and require it to be open/non-archived. Lock an active household row.
3. Read current active occupancy under the lock. `occupancy + count(memberIds)` must fit the center capacity. No stay has been inserted at this point.
4. Every selected ID must belong to that active household. Every selected person must lack an active stay in any center, not only this center.
5. After all checks pass, prepare one insert and execute it once per arriving member, saving the staff ID.
6. Commit all stays. Any exception rolls back the transaction and is rethrown to the controller.

For the example: 1 + 3 is within capacity 5. Three rows are inserted. If member 22 already has an active stay, no selected person is inserted. If occupancy were 3, 3 + 3 exceeds 5 and the entire batch fails. Later household membership changes do not automatically create or end these stay rows.

## 5. Lock the center

Source: [app/models/Evacuation.php, lines 36–43](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/app/models/Evacuation.php#L36). Exact excerpt, not pseudocode.

```php
    private function lockCenter(int $id): array
    {
        $rows = $this->rows('SELECT * FROM evacuation_centers WHERE id=? FOR UPDATE', [$id]);
        if (!$rows) {
            throw new DomainException('Center not found.');
        }
        return $rows[0];
    }
```

The model obtains a row lock inside the transaction. Check-in and checkout share this center lock, coordinating changes for the same center. This supports concurrent correctness; it is not a general performance guarantee for a particular number of simultaneous users.

## 6. Count current active stays

Source: [app/models/Evacuation.php, lines 44–48](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/app/models/Evacuation.php#L44). Exact excerpt, not pseudocode.

```php
    private function occupancy(int $id): int
    {
        // Current locking read, including changes committed while waiting for the center lock.
        return count($this->rows('SELECT id FROM evacuation_records WHERE center_id=? AND checked_out_at IS NULL FOR UPDATE', [$id]));
    }
```

A stay is active when `checked_out_at IS NULL`. This locking read sees the current relevant rows when the transaction proceeds. It avoids relying on a browser preview or an earlier page load while another staff member changes occupancy.

## 7. Reload center totals

Source: [app/models/Evacuation.php, lines 26–35](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/app/models/Evacuation.php#L26). Exact excerpt, not pseudocode.

```php
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
```

The ordinary detail query counts active stays after the redirect. The shared helper calculates available places from this count and the center state. The view renders the returned occupancy and capacity; it does not need a duplicated occupancy column.

## 8. Shared capacity definition

Source: [app/evacuation-capacity.php, lines 1–9](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/app/evacuation-capacity.php#L1). Exact excerpt, not pseudocode.

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

Closed or archived centers have no currently available capacity. Otherwise capacity minus occupancy is clamped at zero. The same meaning is used by related list/dashboard/map queries. A missing coordinate does not by itself make a center unavailable.

## 9. Checkout preserves the stay

Source: [app/models/Evacuation.php, lines 207–222](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/app/models/Evacuation.php#L207). Exact excerpt, not pseudocode.

```php
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
```

Checkout updates the selected row's end time and responsible staff; it does not delete the stay. Both record ID and center ID must match, and only an active row can change. A second checkout has no eligible row and reports an error. The subsequent GET no longer counts this row as active, but history can still join it.
## Walk-in branch and preview

The controller supports choosing an existing walk-in or creating one with basic details. The model can normalize a new person's identity fields to reuse a matching walk-in identity, then writes a separate stay. The legacy single-member model branch exists but is not the current household form's entry path. `household-checkin.js` updates the visible arriving/remaining preview when checkboxes change; it does not save records or authorize arrivals.

## Code speech (about 75 seconds)

“The household form sends an array of selected member IDs. It does not simply submit the whole household size. The controller chooses the household check-in branch and passes the center, household, selected people and signed-in staff ID to the model. Inside the transaction, the model locks the center and reads current occupancy. It checks capacity for the entire group, then checks each member's household and whether they already have an active stay. Only after all checks pass does it insert one stay per selected person. Commit saves the group together, while an error rolls the group back. After redirecting, the center query counts active stays again. Checkout later ends a stay rather than deleting it.”
