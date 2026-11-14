<?php
// Disposable fictional database only. Never uses the application's configured database.
if (PHP_SAPI !== 'cli') {
    exit;
}
require __DIR__ . '/../app/models/Evacuation.php';
require __DIR__ . '/../app/models/Dashboard.php';
require __DIR__ . '/../app/models/DisasterSummary.php';
require __DIR__ . '/../app/models/Map.php';
require __DIR__ . '/../app/models/Report.php';
$pdo = new PDO('mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;charset=utf8mb4', 'root', 'root', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$name = 'bayanready_household_test_' . bin2hex(random_bytes(4));
$pdo->exec("CREATE DATABASE `$name`");
try {
    $pdo->exec("USE `$name`");
    $schema = file_get_contents(__DIR__ . '/../barangay_drrm.sql');
    $schema = str_replace(['CREATE DATABASE IF NOT EXISTS barangay_drrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;', 'USE barangay_drrm;'], '', $schema);
    $pdo->exec($schema);
    $pdo->exec("INSERT INTO users(full_name, username, email, password, role) VALUES ('Fictional Staff','fixture','fixture@example.invalid','unused','admin')");
    $pdo->exec("INSERT INTO households(household_code,representative_name,purok) VALUES ('TEST-A','Fictional Household','Demo'),('TEST-B','Other Fictional Household','Demo')");
    for ($i = 1; $i <= 7; $i++) {
        $pdo->exec("INSERT INTO household_members(household_id,full_name) VALUES (1,'Fictional Member $i')");
    }
    $pdo->exec("INSERT INTO household_members(household_id,full_name) VALUES (2,'Other Member')");
    $pdo->exec("INSERT INTO evacuation_centers(name,address_details,capacity,status) VALUES ('Demo A','Fictional',10,'open'),('Demo B','Fictional',2,'open')");
    $pdo->exec("INSERT INTO evacuation_centers(name,address_details,capacity,status,archived_at)
        VALUES ('Fictional closed','Demo',50,'closed',NULL),('Fictional archived','Demo',100,'open',NOW())");
    $pdo->exec('UPDATE evacuation_centers SET latitude=16.6272,longitude=120.7672 WHERE id=2');
    $model = new EvacuationModel($pdo);
    $dashboard = new DashboardModel($pdo);
    $summary = new DisasterSummaryModel($pdo);
    $map = new MapModel($pdo);
    function check($condition, $label) {
        if (!$condition) {
            throw new RuntimeException($label);
        }
        echo "PASS $label\n";
    }
    function blocked($model, $pdo, $center, $members, $label) {
        $before = $pdo->query('SELECT COUNT(*) FROM evacuation_records')->fetchColumn();
        try {
            $model->checkinHousehold($center, 1, $members, 1);
            throw new RuntimeException('Unexpected success: ' . $label);
        } catch (DomainException $e) {
            check($before == $pdo->query('SELECT COUNT(*) FROM evacuation_records')->fetchColumn(), $label);
        }
    }
    function checkCounts($dashboard, $summary, $model, $map, $available, $occupants, $label) {
        $centers = $dashboard->centers();
        $report = $summary->summarize('2000-01-01', '2100-01-01');
        check(count($centers) === 2, $label . ': open center count includes no-point center');
        check(array_sum(array_column($centers, 'available')) === $available, $label . ': dashboard available capacity');
        check(array_sum(array_column($report['centers'], 'available')) === $available, $label . ': summary excludes closed/archived capacity');
        check(count($dashboard->stays()) === $occupants && array_sum(array_column($report['centers'], 'occupancy')) == $occupants, $label . ': active stays match summary');
        foreach ($centers as $center) {
            $detail = $model->center((int)$center['id']);
            check($detail['occupancy'] == $center['occupancy'] && $detail['available'] === $center['available'], $label . ': detail matches dashboard');
        }
        foreach ($model->centers('') as $center) {
            check($center['available'] === $model->center((int)$center['id'])['available'], $label . ': admin list matches detail');
        }
        $mapped = $map->publicMap()['centers'];
        check(count($mapped) === 1 && (int)$mapped[0]['id'] === 2 && $mapped[0]['available'] === $model->center(2)['available'], $label . ': mapped center matches; no-point center remains in totals');
        check($model->center(4)['available'] === 0 && count($report['centers']) === 4, $label . ': archived detail/history retained without available capacity');
    }
    checkCounts($dashboard, $summary, $model, $map, 12, 0, 'before check-in');
    $model->checkinHousehold(1, 1, [1,2,3,4,5], 1);
    check((int)$model->center(1)['occupancy'] === 5, 'five members add five occupants');
    checkCounts($dashboard, $summary, $model, $map, 7, 5, 'after household check-in');
    blocked($model, $pdo, 1, [6,1], 'duplicate batch adds nobody');
    blocked($model, $pdo, 2, [5], 'duplicate at another center adds nobody');
    blocked($model, $pdo, 2, [6,7,8], 'insufficient capacity adds nobody');
    blocked($model, $pdo, 1, [6,8], 'foreign household member adds nobody');
    blocked($model, $pdo, 1, [], 'empty selection adds nobody');
    blocked($model, $pdo, 1, [6,6], 'duplicate selection adds nobody');
    $pdo->exec('UPDATE household_members SET archived_at=NOW() WHERE id=7');
    blocked($model, $pdo, 1, [6,7], 'archived member adds nobody');
    checkCounts($dashboard, $summary, $model, $map, 7, 5, 'after rejected batches');
    $pdo->exec("INSERT INTO household_members(household_id,full_name) VALUES(1,'Later Fictional Member')");
    check((int)$model->center(1)['occupancy'] === 5, 'registry changes do not change stays');
    $model->checkout(1, 1, 1);
    check((int)$model->center(1)['occupancy'] === 4 && count($model->records(1)) === 5, 'individual checkout retains history');
    checkCounts($dashboard, $summary, $model, $map, 8, 4, 'after individual checkout');
    $model->checkinHousehold(2, 1, [1,6], 1);
    check((int)$model->center(2)['occupancy'] === 2, 'partial household exactly fills center');
    $model->checkin(1, 'new_walkin', 0, ['full_name'=>'Fictional Walkin','age'=>30,'address_details'=>'Demo','contact_phone'=>'0000000000'], 1);
    check((int)$model->center(1)['occupancy'] === 5, 'walk-in flow remains usable');
    checkCounts($dashboard, $summary, $model, $map, 5, 7, 'after transfer and walk-in');
    $pdo->exec("INSERT INTO users(full_name,username,email,password,role) VALUES
        ('Fictional Resident A','resident_a','a@example.invalid','unused','resident'),
        ('Fictional Resident B','resident_b','b@example.invalid','unused','resident')");
    $insert = $pdo->prepare("INSERT INTO incident_reports(reported_by,incident_type,description,status,archived_at) VALUES (?,'other','Fictional incident',?,?)");
    foreach (['pending','verified','responding','resolved','rejected'] as $status) {
        $insert->execute([2,$status,null]);
        $insert->execute([3,$status,null]);
        $insert->execute([2,$status,'2026-01-01 00:00:00']);
    }
    $reports = new ReportModel($pdo);
    foreach ([null,2,3] as $owner) {
        foreach ($dashboard->incidents($owner) as $status => $total) {
            $filters = array_fill_keys(['incident_type','status','date','location','search'], '');
            $filters['status'] = $status;
            check($total === ($owner === null ? 2 : 1) && count($reports->listing($filters,$owner)) === $total, 'incident card/list matches owner and status: ' . ($owner ?? 'admin') . ' ' . $status);
        }
    }
    $historical = $summary->summarize('2000-01-01','2100-01-01');
    check(array_sum(array_column($historical['incidents'],'total')) == 15, 'historical incident summary retains archived reports');
} finally {
    $pdo->exec("DROP DATABASE `$name`");
}
