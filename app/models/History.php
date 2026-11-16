<?php
require_once __DIR__ . '/../../config/db.php';

class HistoryModel
{
    public function hide(string $type, int $id): string
    {
        // Only these fixed tables can be updated; never accept a posted table name.
        $types = [
            'response' => ['response_updates', 'incident_id', 'response/incident.php?id='],
            'evacuation' => ['evacuation_records', 'center_id', 'evacuation/center.php?id='],
            'stock' => ['relief_stock_movements', null, 'relief/movements.php'],
            'distribution' => ['relief_distributions', null, 'relief/distributions.php'],
        ];
        if (!isset($types[$type]) || $id < 1) {
            throw new DomainException('Choose a valid history entry.');
        }
        [$table, $parent, $page] = $types[$type];
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT * FROM $table WHERE id=? FOR UPDATE");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) {
                throw new DomainException('History entry not found.');
            }
            if ($type === 'evacuation' && $row['checked_out_at'] === null) {
                throw new DomainException('Check out this person before hiding their evacuation history.');
            }
            $stmt = $pdo->prepare("UPDATE $table SET history_hidden_at=COALESCE(history_hidden_at, NOW()) WHERE id=?");
            $stmt->execute([$id]);
            $pdo->commit();
            return 'resources/views/admin/' . $page . ($parent ? $row[$parent] : '');
        } catch (Throwable $error) {
            $pdo->rollBack();
            throw $error;
        }
    }
}
