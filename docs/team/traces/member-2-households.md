# Member 2 · Choose the representative

The household detail form sends select_representative and member_id. The controller obtains the household ID from the open record and validates the submitted member ID. HouseholdModel::selectRepresentative checks the household/member relationship and active status before updating the representative. The household_directory view joins the member name to the same household ID for labels used downstream. Show that replacing the representative leaves prior stays and distributions linked. [Form](../../../resources/views/admin/household.php) · [Controller](../../../app/controllers/HouseholdController.php) · [Model](../../../app/models/Household.php) · [Directory definition](../../../database/migrations/010_household_representative.sql)

## Select a member of the displayed household

[resources/views/admin/household.php, lines 54–65](../../../resources/views/admin/household.php#L54)

```php
<?php if ($members && !$household['archived_at']): ?>
    <form method="post" class="mb-3">
        <label>Household representative
            <select class="form-select" name="member_id" required>
                <option value="">Choose a member</option>
                <?php foreach ($members as $member): ?>
                    <option value="<?= (int)$member['id'] ?>" <?= $member['id'] == $household['representative_member_id'] ? 'selected' : '' ?>><?= e($member['full_name']) ?> (#<?= (int)$member['id'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="btn btn-primary" name="action" value="select_representative">Save representative</button>
    </form>
```

## Pass the household ID and submitted member ID

[app/controllers/HouseholdController.php, lines 185–193](../../../app/controllers/HouseholdController.php#L185)

```php
    public function selectRepresentative(int $id): array
    {
        try {
            $this->households->selectRepresentative($id, $this->recordId(input('member_id')));
        } catch (DomainException $error) {
            return [$error->getMessage()];
        }
        $this->success('Representative updated.', 'resources/views/admin/household.php?id=' . $id);
        return [];
```

## Check membership before updating the representative

[app/models/Household.php, lines 78–95](../../../app/models/Household.php#L78)

```php
    public function selectRepresentative(int $householdId, int $memberId): void
    {
        $this->pdo->beginTransaction();
        try {
            $member = $this->pdo->prepare(
                'SELECT id FROM household_members WHERE id=? AND household_id=? AND archived_at IS NULL FOR UPDATE'
            );
            $member->execute([$memberId, $householdId]);
            if (!$member->fetchColumn()) {
                throw new DomainException('Choose an active member of this household.');
            }
            $stmt = $this->pdo->prepare(
                'UPDATE households SET representative_member_id=? WHERE id=? AND archived_at IS NULL'
            );
            $stmt->execute([$memberId, $householdId]);
            if (!$stmt->rowCount() && !empty($this->findById($householdId)['archived_at'])) {
                throw new DomainException('Archived households cannot be edited.');
            }
```

## Demonstrate it

Replace the representative, then reopen the household. Show the new name and the same household ID.
