# Member 1 · Sign in

The form posts its action, email and password to the root page. UserController::handlePost reads them and calls UserModel::findByEmail. The model binds the email to a prepared query. The controller verifies the password hash and account state, regenerates the session ID, stores the account ID and role, and redirects to the corresponding home page. The guard on a staff page checks the session again. Show where this role originates. Public registration calls createResident. [Entry form](../../../index.php) · [Controller](../../../app/controllers/UserController.php) · [Model](../../../app/models/User.php) · [Guards](../../../app/helpers.php)

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
