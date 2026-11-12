<?php
require_once __DIR__ . '/../../config/db.php';
class MapModel
{
    private PDO $pdo;
    public function __construct() { $this->pdo=(new DATABASE())->connect(); }
    private function rows(string $sql): array { return $this->pdo->query($sql)->fetchAll(); }
    private function point(string $lat,string $lng): string
    {
        return "$lat IS NOT NULL AND $lng IS NOT NULL AND $lat BETWEEN -90 AND 90 AND $lng BETWEEN -180 AND 180";
    }
    public function resident(): array
    {
        // Explicit allowlists: never select private incident fields for the resident response.
        return [
            'incidents'=>$this->rows("SELECT id,incident_type,public_label,public_latitude,public_longitude,status,DATE(created_at) AS report_date FROM incident_reports WHERE archived_at IS NULL AND is_public=1 AND status IN ('verified','responding','resolved') AND public_label IS NOT NULL AND TRIM(public_label)<>'' AND ".$this->point('public_latitude','public_longitude')),
            'centers'=>$this->centers(true),
            'relief_locations'=>$this->rows("SELECT id,name,address_details,latitude,longitude,location_type FROM relief_locations WHERE archived_at IS NULL AND is_public=1 AND location_type IN ('distribution','both') AND ".$this->point('latitude','longitude')),
            'incident_location_note'=>'Incident points are approximate public locations.'
        ];
    }
    public function admin(): array
    {
        return [
            'incidents'=>$this->rows('SELECT id,incident_type,location_description,latitude,longitude,status,is_public,DATE(created_at) AS report_date FROM incident_reports WHERE archived_at IS NULL AND '.$this->point('latitude','longitude')),
            'households'=>$this->rows("SELECT id,household_code,purok,address_details,hazard_type,latitude,longitude FROM households WHERE archived_at IS NULL AND hazard_type<>'unassessed' AND ".$this->point('latitude','longitude')),
            'centers'=>$this->centers(false),
            'relief_locations'=>$this->rows('SELECT id,name,address_details,latitude,longitude,location_type,is_public FROM relief_locations WHERE archived_at IS NULL AND '.$this->point('latitude','longitude'))
        ];
    }
    public function reliefLocation(int $id): array
    {
        $stmt=$this->pdo->prepare('SELECT id,name,address_details,latitude,longitude,location_type,archived_at,is_public FROM relief_locations WHERE id=?');
        $stmt->execute([$id]); return $stmt->fetch() ?: [];
    }
    private function centers(bool $resident): array
    {
        return $this->rows("SELECT c.id,c.name,c.address_details,c.latitude,c.longitude,c.status,c.capacity,COUNT(e.id) AS occupancy,CASE WHEN c.status='open' AND c.capacity>COUNT(e.id) THEN c.capacity-COUNT(e.id) ELSE 0 END AS available FROM evacuation_centers c LEFT JOIN evacuation_records e ON e.center_id=c.id AND e.checked_out_at IS NULL WHERE c.archived_at IS NULL AND ".$this->point('c.latitude','c.longitude').($resident?" AND c.is_public=1 AND c.status='open'":'')." GROUP BY c.id,c.name,c.address_details,c.latitude,c.longitude,c.status,c.capacity");
    }
}
