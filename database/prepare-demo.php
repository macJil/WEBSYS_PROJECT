<?php
// Run locally from a terminal. Never expose account creation through a web page.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../config/db.php';

function insertRecord(PDO $pdo, string $sql, array $values = []): int
{
    $statement = $pdo->prepare($sql);
    $statement->execute($values);
    return (int)$pdo->lastInsertId();
}

function createLogin(PDO $pdo, string $name, string $username, string $email, string $role, ?int $member = null): array
{
    $password = bin2hex(random_bytes(12));
    $id = insertRecord($pdo, 'INSERT INTO users
        (full_name,username,email,password,role,household_member_id,must_change_password)
        VALUES (?,?,?,?,?,?,1)', [$name, $username, $email, password_hash($password, PASSWORD_DEFAULT), $role, $member]);
    return [$id, "$role login: $email\nTemporary password: $password\n"];
}

$mode = $argv[1] ?? '';
if ($mode !== 'demo' && ($mode !== 'admin' || !filter_var($argv[2] ?? '', FILTER_VALIDATE_EMAIL))) {
    fwrite(STDERR, "Usage: php database/prepare-demo.php demo | admin <login-email>\n");
    exit(1);
}
try {
    $pdo = db();
    $pdo->beginTransaction();
    if ($mode === 'admin') {
        [, $receipt] = createLogin($pdo, 'Barangay Administrator', 'barangay_handoff_admin', $argv[2], 'admin');
    } else {
        [$staff, $receipt] = createLogin($pdo, 'Demo Duty Officer', 'atok_demo_staff', 'staff@demo.example.invalid', 'admin');
        $house = insertRecord($pdo, "INSERT INTO households (household_code,representative_name,barangay,purok,address_details,registry_reason,hazard_type)
            VALUES ('DEMO-ATOK-01','Demo Resident One','Paoay','Demo Block A','Fictional training lane','both','flood')");
        $member = insertRecord($pdo, "INSERT INTO household_members (household_id,full_name,age) VALUES (?,'Demo Resident One',32)", [$house]);
        insertRecord($pdo, "INSERT INTO household_members (household_id,full_name,age) VALUES (?,'Demo Resident Two',24)", [$house]);
        [$resident, $residentReceipt] = createLogin($pdo, 'Demo Resident One', 'atok_demo_resident', 'resident@demo.example.invalid', 'resident', $member);
        $receipt .= $residentReceipt;
        $pdo->prepare("UPDATE users SET phone='09000000000' WHERE id=?")->execute([$resident]);
        insertRecord($pdo, "INSERT INTO incident_reports (reported_by,barangay,reporter_name,contact_phone,incident_type,description,location_description,latitude,longitude)
            VALUES (?,'Paoay','Demo Resident One','09000000000','flood','Fictional drill: water across the training lane','Demo crossing',16.62366,120.76422)", [$resident]);
        insertRecord($pdo, "INSERT INTO responders (team_name) VALUES ('Demo Response Alpha'),('Demo Response Bravo')");
        $center = insertRecord($pdo, "INSERT INTO evacuation_centers (name,barangay,address_details,capacity,status,latitude,longitude)
            VALUES ('Demo Training Shelter','Paoay','Fictional assembly hall',5,'open',16.6238,120.7643)");
        insertRecord($pdo, "INSERT INTO evacuation_centers (name,barangay,address_details,capacity,status) VALUES ('Demo Reserve Shelter','Topdac','Fictional reserve hall',4,'closed')");
        insertRecord($pdo, 'INSERT INTO evacuation_records (center_id,household_member_id,recorded_by) VALUES (?,?,?)', [$center,$member,$staff]);
        $identity = hash('sha256', json_encode(['demo returning visitor','42','demo lane b','09000000001']));
        $visitor = insertRecord($pdo, "INSERT INTO evacuation_walkins (full_name,age,address_details,contact_phone,identity_key)
            VALUES ('Demo Returning Visitor',42,'Demo Lane B','09000000001',?)", [$identity]);
        insertRecord($pdo, "INSERT INTO evacuation_records (center_id,walkin_id,walkin_name,walkin_contact,recorded_by,checked_in_at,checked_out_at,checked_out_by)
            VALUES (?,?,'Demo Returning Visitor','09000000001',?,NOW() - INTERVAL 1 DAY,NOW(),?)", [$center,$visitor,$staff,$staff]);
        $point = insertRecord($pdo, "INSERT INTO relief_locations (name,address_details,latitude,longitude) VALUES ('Demo Relief Desk','Fictional assembly hall',16.6239,120.7644)");
        $event = insertRecord($pdo, "INSERT INTO relief_distributions (household_id,relief_location_id,received_by,distributed_by) VALUES (?,?,'Demo Resident One',?)", [$house,$point,$staff]);
        foreach (['Demo food pack' => 'packs','Demo drinking water' => 'bottles'] as $good => $unit) {
            $item = insertRecord($pdo, 'INSERT INTO relief_items (item_name,unit,quantity_on_hand) VALUES (?,?,18)', [$good,$unit]);
            insertRecord($pdo, "INSERT INTO relief_stock_movements (item_id,movement_type,quantity,recorded_by,notes) VALUES (?,'stock_in',20,?,'Demo delivery')", [$item,$staff]);
            insertRecord($pdo, 'INSERT INTO relief_distribution_items (distribution_id,item_id,quantity) VALUES (?,?,2)', [$event,$item]);
            insertRecord($pdo, "INSERT INTO relief_stock_movements (item_id,movement_type,quantity,recorded_by,distribution_id,notes) VALUES (?,'distribution',2,?,?,'Demo household distribution')", [$item,$staff,$event]);
        }
        insertRecord($pdo, "INSERT INTO announcements (title,message,is_published,first_published_at,created_by)
            VALUES ('Demo exercise notice','Classroom exercise only. View the Demo Training Shelter on the map.',1,NOW(),?)", [$staff]);
    }
    $pdo->commit();
    echo $receipt;
} catch (Throwable $error) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Nothing added. Check the configured database, schema and existing login or demo names. Error code: " . $error->getCode() . "\n");
    exit(1);
}
