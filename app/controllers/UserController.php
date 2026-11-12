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
            if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $data['username'])) {
                $errors[] = 'Use 3–50 letters, numbers or underscores for your username.';
            }
            if (!preg_match('/^[0-9+() .-]{7,20}$/', $data['phone'])) {
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
            error_log((string) $error);
            if ($action === 'register' && $error->getCode() === '23000') {
                return ['That email address or username is already registered.'];
            }
            return ['The database is unavailable. Please try again later.'];
        }
    }
}
