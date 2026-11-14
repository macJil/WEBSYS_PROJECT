<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../evacuation-capacity.php';
class MapModel
{
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }
    private function rows(string $sql): array
    {
        return $this->pdo->query($sql)->fetchAll();
    }
    private function point(string $lat, string $lng): string
    {
        return "$lat IS NOT NULL AND $lng IS NOT NULL AND $lat BETWEEN -90 AND 90 AND $lng BETWEEN -180 AND 180";
    }
    public function publicMap(): array
    {
        // Explicit allowlists: never select private incident fields for the resident response.
        return [
            'incidents' => $this->rows("SELECT id,incident_type,latitude,longitude,status,DATE(created_at) AS report_date FROM incident_reports WHERE archived_at IS NULL AND status IN ('verified','responding','resolved') AND " . $this->point('latitude', 'longitude')),
            'centers' => $this->centers(),
            'relief_locations' => $this->rows("SELECT id,name,address_details,latitude,longitude,location_type FROM relief_locations WHERE archived_at IS NULL AND location_type IN ('distribution','both') AND " . $this->point('latitude', 'longitude'))
        ];
    }
    public function reliefLocation(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT id,name,address_details,latitude,longitude,location_type,archived_at FROM relief_locations WHERE id=?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: [];
    }
    private function centers(): array
    {
        $centers = $this->rows("SELECT c.id, c.name, c.address_details, c.latitude, c.longitude,
            c.status, c.capacity, COUNT(e.id) AS occupancy
            FROM evacuation_centers c
            LEFT JOIN evacuation_records e ON e.center_id = c.id AND e.checked_out_at IS NULL
            WHERE c.archived_at IS NULL AND c.status = 'open' AND " . $this->point('c.latitude', 'c.longitude') . "
            GROUP BY c.id, c.name, c.address_details, c.latitude, c.longitude, c.status, c.capacity");
        foreach ($centers as $index => $center) {
            $centers[$index]['available'] = availableCapacity($center);
        }
        return $centers;
    }
}
