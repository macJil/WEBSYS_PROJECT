<?php
require_once __DIR__ . '/../../config/db.php';
class ReliefLocationModel
{
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }
    public function all(): array
    {
        return $this->pdo->query('SELECT * FROM relief_locations WHERE archived_at IS NULL ORDER BY name')->fetchAll();
    }
    public function find(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM relief_locations WHERE id=?');
        $stmt->execute([$id]);
        return $stmt->fetch()?:[];
    }
    public function save(?int $id, array $data): void
    {
        $values = [$data['name'],$data['location_type'],$data['address_details'],$data['latitude'],$data['longitude']];
        if ($id) {
            $stmt = $this->pdo->prepare("UPDATE relief_locations SET name=?,location_type=?,address_details=?,latitude=?,longitude=? WHERE id=? AND archived_at IS NULL");
            $values[] = $id;
        } else {
            $stmt = $this->pdo->prepare('INSERT INTO relief_locations(name,location_type,address_details,latitude,longitude) VALUES(?,?,?,?,?)');
        }
        $stmt->execute($values);
    }
}
