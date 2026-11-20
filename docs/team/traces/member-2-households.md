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

## Login and session code assigned to Member 2

## Login submits an email and password

[index.php, lines 29–39](../../../index.php#L29)

```php
        <div class="panel h-100">
            <h2>Login</h2>
            <form method="post" action="<?= e(url('index.php')) ?>">
                <input type="hidden" name="action" value="login" />
                <label for="login-email" class="form-label">Email</label>
                <input
                    id="login-email"
                    class="form-control mb-3"
                    type="email"
                    name="email"
                    required
```

## Check the account and create the signed-in session

[app/controllers/UserController.php, lines 68–86](../../../app/controllers/UserController.php#L68)

```php
            $user = $users->findByEmail($email);
            if (
                !$user ||
                !password_verify($password, $user['password']) ||
                !$user['is_active'] ||
                !in_array($user['role'], ['admin', 'resident'], true)
            ) {
                return ['Invalid email or password, or the account is inactive.'];
            }
            session_regenerate_id(true);
            $_SESSION = [
                'ID' => (int) $user['id'],
                'FULL_NAME' => $user['full_name'],
                'USERNAME' => $user['username'],
                'PHONE' => $user['phone'] ?? '',
                'EMAIL' => $user['email'],
                'ROLE' => $user['role'],
            ];
            $this->success('You are now signed in.', dashboardPath());
```

## Retrieve the stored account by email

[app/models/User.php, lines 11–16](../../../app/models/User.php#L11)

```php
    public function findByEmail(string $email)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
```

## Demonstrate it

Sign in as a fictional staff member. Point out where the role is read from the account and where a failed password stops the request.
