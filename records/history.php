<?php
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../config/db.php';
requireRole('admin');
function historyHide(string $type, int $id): string
{
    // Only these fixed tables can be updated; never accept a posted table name.
    $types = [
        'response' => ['response_updates', 'incident_id', 'response/incident.php?id='],
        'evacuation' => ['evacuation_records', 'center_id', 'evacuation/center.php?id='],
        'stock' => ['relief_stock_movements', null, 'relief-inventory/movements.php'],
        'distribution' => ['relief_distributions', null, 'relief-distribution/index.php'],
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
        return $page . ($parent ? $row[$parent] : '');
    } catch (Throwable $error) {
        $pdo->rollBack();
        throw $error;
    }
}

function processHide(): array
{
    requireRole('admin');
    if (!isPost()) {
        http_response_code(405);
        header('Allow: POST');
        return ['Use the Hide history entry button on a history page.'];
    }
    $id = filter_var(input('history_id'), FILTER_VALIDATE_INT);
    try {
        $path = historyHide(input('history_type'), $id ?: 0);
    } catch (DomainException $error) {
        http_response_code(400);
        return [$error->getMessage()];
    } catch (PDOException $error) {
        http_response_code(500);
        return ['History could not be hidden. Please try again.'];
    }
    flash('History entry hidden. Stored records and totals are unchanged.');
    redirect($path);
    return [];
}

$errors = processHide();
$pageTitle = 'History action';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/errors.php';
?>
<a class="btn btn-outline-secondary" href="<?= e(url('dashboard/staff.php')) ?>">Return to dashboard</a>
<?php require __DIR__ . '/../includes/footer.php'; ?>
