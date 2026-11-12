<?php
require_once __DIR__ . '/../../config/db.php';
class ReliefLocationModel
{
    private PDO $pdo;
    public function __construct()
    {
        $this->pdo = (new DATABASE())->connect();
    }
    public function all(): array
    {
        return $this->pdo->query('SELECT * FROM relief_locations WHERE archived_at IS NULL ORDER BY name')->fetchAll();
    }
    public function find(int $id): array
    {
        $s = $this->pdo->prepare('SELECT * FROM relief_locations WHERE id=?');
        $s->execute([$id]);
        return $s->fetch()?:[];
    }
    public function save(?int $id, array $d): void
    {
        $values = [$d['name'],$d['location_type'],$d['address_details'],$d['latitude'],$d['longitude']];
        if ($id) {
            $s = $this->pdo->prepare("UPDATE relief_locations SET name=?,location_type=?,address_details=?,latitude=?,longitude=?,is_public=CASE WHEN ?='storage' THEN 0 ELSE is_public END WHERE id=? AND archived_at IS NULL");
            $values[] = $d['location_type'];
            $values[] = $id;
        } else {
            $s = $this->pdo->prepare('INSERT INTO relief_locations(name,location_type,address_details,latitude,longitude) VALUES(?,?,?,?,?)');
        }
        $s->execute($values);
    }
}
