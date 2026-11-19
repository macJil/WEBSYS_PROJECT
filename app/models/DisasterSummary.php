<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../evacuation-capacity.php';
class DisasterSummaryModel
{
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }

    private function rows(string $sql, array $values = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
        return $stmt->fetchAll();
    }

    public function summarize(string $start, string $endExclusive, string $barangay = ''): array
    {
        $area = $barangay === '' ? [] : [$barangay];
        $period = array_merge([$start, $endExclusive], $area);
        $filter = $area ? ' AND barangay=?' : '';
        $centerFilter = $area ? ' WHERE c.barangay=?' : '';
        $recipientFilter = $area ? ' AND h.barangay=?' : '';
        // A single read transaction keeps the summaries on the same database snapshot.
        $this->pdo->beginTransaction();
        try {
            $affected = $this->rows(
                "SELECT COUNT(*) AS total FROM households WHERE archived_at IS NULL AND registry_reason IN ('affected','both')" . $filter, $area,
            )[0]['total'];
            $centers = $this->rows("SELECT c.id, c.name, c.status, c.archived_at, c.capacity,
                COUNT(e.id) AS occupancy
                FROM evacuation_centers c
                LEFT JOIN evacuation_records e ON e.center_id = c.id AND e.checked_out_at IS NULL
                " . $centerFilter . " GROUP BY c.id, c.name, c.status, c.archived_at, c.capacity ORDER BY c.name, c.id", $area);
            foreach ($centers as $index => $center) {
                $centers[$index]['available'] = availableCapacity($center);
            }
            $incidents = $this->rows(
                'SELECT incident_type,status,COUNT(*) AS total FROM incident_reports WHERE created_at>=? AND created_at<?' . $filter . ' GROUP BY incident_type,status',
                $period,
            );
            $goods = $this->rows(
                'SELECT i.id,i.item_name,i.unit,SUM(d.quantity) AS quantity FROM relief_distributions r JOIN households h ON h.id=r.household_id JOIN relief_distribution_items d ON d.distribution_id=r.id JOIN relief_items i ON i.id=d.item_id WHERE r.distributed_at>=? AND r.distributed_at<?' . $recipientFilter . ' GROUP BY i.id,i.item_name,i.unit ORDER BY i.item_name,i.unit,i.id',
                $period,
            );
            $distributions = $this->rows(
                'SELECT COUNT(*) AS events,COUNT(DISTINCT r.household_id) AS households FROM relief_distributions r JOIN households h ON h.id=r.household_id WHERE r.distributed_at>=? AND r.distributed_at<?' . $recipientFilter,
                $period,
            )[0];
            $this->pdo->commit();
            return [
                'affected' => (int) $affected,
                'centers' => $centers,
                'incidents' => $incidents,
                'goods' => $goods,
                'distributions' => $distributions,
            ];
        } catch (Throwable $error) {
            $this->pdo->rollBack();
            throw $error;
        }
    }
}
