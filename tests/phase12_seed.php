<?php
// Only for the explicitly marked, disposable MySQL integration fixture.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../config/db.php';
$pdo=(new DATABASE())->connect();
if ($pdo->query('SELECT label FROM websys_integration_fixture')->fetchColumn()!=='fictional-only'
    || (int)$pdo->query('SELECT @@port')->fetchColumn()!==18960
    || (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn()!==0) {
    throw new RuntimeException('Use only an empty, marked integration fixture on port 18960.');
}
$stmt=$pdo->prepare('INSERT INTO users(full_name,username,email,password,role) VALUES(?,?,?,?,?)');
$stmt->execute(['Demo Admin','demo_admin','admin@example.invalid',password_hash('DemoPass123!',PASSWORD_DEFAULT),'admin']);
echo "Fictional admin created for the isolated integration suite.\n";
