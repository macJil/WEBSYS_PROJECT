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
