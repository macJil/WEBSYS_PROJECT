<?php
require_once __DIR__ . '/../../config/db.php';
class DisasterSummaryModel
{
    private PDO $pdo;
    public function __construct() { $this->pdo=(new DATABASE())->connect(); }
    private function rows(string $sql,array $values=[]): array
    {
        $stmt=$this->pdo->prepare($sql); $stmt->execute($values); return $stmt->fetchAll();
    }
    public function summarize(string $start,string $endExclusive): array
    {
        // A single read transaction keeps the summaries on the same database snapshot.
        $this->pdo->beginTransaction();
        try {
            $affected=$this->rows("SELECT COUNT(*) AS total FROM households WHERE archived_at IS NULL AND registry_reason IN ('affected','both')")[0]['total'];
            $centers=$this->rows("SELECT c.id,c.name,c.status,c.capacity,COUNT(e.id) AS occupancy,
                CASE WHEN c.status='open' AND c.capacity>COUNT(e.id) THEN c.capacity-COUNT(e.id) ELSE 0 END AS available
                FROM evacuation_centers c LEFT JOIN evacuation_records e ON e.center_id=c.id AND e.checked_out_at IS NULL
                GROUP BY c.id,c.name,c.status,c.capacity ORDER BY c.name,c.id");
            $incidents=$this->rows('SELECT incident_type,status,COUNT(*) AS total FROM incident_reports WHERE created_at>=? AND created_at<? GROUP BY incident_type,status',[$start,$endExclusive]);
            $goods=$this->rows('SELECT i.id,i.item_name,i.unit,SUM(d.quantity) AS quantity FROM relief_distributions r JOIN relief_distribution_items d ON d.distribution_id=r.id JOIN relief_items i ON i.id=d.item_id WHERE r.distributed_at>=? AND r.distributed_at<? GROUP BY i.id,i.item_name,i.unit ORDER BY i.item_name,i.unit,i.id',[$start,$endExclusive]);
            $distributions=$this->rows('SELECT COUNT(*) AS events,COUNT(DISTINCT household_id) AS households FROM relief_distributions WHERE distributed_at>=? AND distributed_at<?',[$start,$endExclusive])[0];
            $this->pdo->commit();
            return ['affected'=>(int)$affected,'centers'=>$centers,'incidents'=>$incidents,'goods'=>$goods,'distributions'=>$distributions];
        } catch (Throwable $error) { $this->pdo->rollBack(); throw $error; }
    }
}
