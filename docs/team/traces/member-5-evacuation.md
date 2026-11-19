# Member 5 · Check in a partial household

The center form posts checkin_household, household_id and the checked member_ids. EvacuationController::handleStay requires a list of IDs and passes the center and staff IDs. EvacuationModel::checkinHousehold locks the relevant records, checks membership, current stays and capacity, then inserts one stay for each arriving person. The transaction rolls back if any selected person is ineligible. Checkout ends one stay. An active stay has no checkout timestamp. Show the before and after center totals. [Form and result](../../../resources/views/admin/evacuation/center.php) · [Controller](../../../app/controllers/EvacuationController.php) · [Model](../../../app/models/Evacuation.php)

## Submit the members who arrived

[resources/views/admin/evacuation/center.php, lines 67–79](../../../resources/views/admin/evacuation/center.php#L67)

```php
                <legend>Arriving members (<?= count($members) ?> registered)</legend>
                <p>Uncheck anyone who is not arriving. Each selected person uses one place.</p>
                <?php foreach ($members as $member): ?>
                    <label class="d-block mb-2">
                        <input class="form-check-input" type="checkbox" name="member_ids[]" value="<?= (int)$member['id'] ?>" <?= in_array($member['id'], $selectedMembers) ? 'checked' : '' ?>> <?= e($member['full_name']) ?> </label>
                <?php endforeach; ?>
            </fieldset>
            <?php if (!$members): ?>
                <p>No active members found. Update the household registry first.</p>
            <?php endif; ?>
            <p>Current occupancy: <?= (int)$center['occupancy'] ?> / <?= (int)$center['capacity'] ?> people.</p>
            <p data-arrival-summary aria-live="polite">Arriving: <?= $arrivingCount ?> people. Remaining capacity after check-in: <?= (int)$center['capacity'] - (int)$center['occupancy'] - $arrivingCount ?> people.</p>
            <button class="btn btn-primary" name="action" value="checkin_household" <?= !$members ? 'disabled' : '' ?>>Check in household</button>
```

## Dispatch the household check-in

[app/controllers/EvacuationController.php, lines 113–125](../../../app/controllers/EvacuationController.php#L113)

```php
        try {
            if (input('action') === 'checkin_household') {
                $memberIds = $_POST['member_ids'] ?? [];
                if (!is_array($memberIds)) {
                    return ['Choose arriving members.'];
                }
                $this->model->checkinHousehold(
                    $id,
                    $this->recordId(input('household_id')),
                    $memberIds,
                    (int) $_SESSION['ID'],
                );
            } elseif (input('action') === 'checkout') {
```

## Reject a group larger than remaining capacity

[app/models/Evacuation.php, lines 165–173](../../../app/models/Evacuation.php#L165)

```php
                throw new DomainException('Active household not found.');
            }
            $occupancy = $this->occupancy($centerId);
            if ($occupancy + count($memberIds) > (int) $center['capacity']) {
                throw new DomainException(
                    'Not enough capacity for all selected members. Nobody was checked in.',
                );
            }
            foreach ($memberIds as $memberId) {
```

## Reject duplicate stays and save one row per arrival

[app/models/Evacuation.php, lines 188–205](../../../app/models/Evacuation.php#L188)

```php
                );
                if ($active) {
                    throw new DomainException(
                        $member[0]['full_name'] . ' already has an active stay. Nobody was checked in.',
                    );
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

## Demonstrate it

Select two members at a five-person empty center. Check one out. Explain why the capacity changes from three to four.
