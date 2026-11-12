<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
// Fictional in-memory fixture only; does not connect to or change MySQL.
require __DIR__ . '/../app/models/DisasterSummary.php';
$pdo=new PDO('sqlite::memory:',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$property=new ReflectionProperty(DATABASE::class,'connection'); $property->setValue(null,$pdo);
$pdo->exec("CREATE TABLE households(id INTEGER,registry_reason TEXT,archived_at TEXT);
CREATE TABLE evacuation_centers(id INTEGER,name TEXT,status TEXT,capacity INTEGER);
CREATE TABLE evacuation_records(id INTEGER,center_id INTEGER,checked_out_at TEXT);
CREATE TABLE incident_reports(id INTEGER,incident_type TEXT,status TEXT,created_at TEXT);
CREATE TABLE relief_items(id INTEGER,item_name TEXT,unit TEXT);
CREATE TABLE relief_distributions(id INTEGER,household_id INTEGER,distributed_at TEXT);
CREATE TABLE relief_distribution_items(id INTEGER,distribution_id INTEGER,item_id INTEGER,quantity INTEGER);
INSERT INTO households(id,registry_reason) VALUES(1,'affected'),(2,'both'),(3,'monitored');
INSERT INTO evacuation_centers VALUES(1,'Demo A','open',3),(2,'Demo B','closed',5),(3,'Demo C','open',2);
INSERT INTO evacuation_records VALUES(1,1,NULL),(2,1,NULL),(3,1,'2026-11-01 12:00:00');
INSERT INTO incident_reports VALUES(1,'flood','pending','2026-11-01 00:00:00'),(2,'fire','resolved','2026-11-30 23:59:59'),(3,'flood','pending','2026-11-15 12:00:00'),(4,'other','verified','2026-12-01 00:00:00'),(5,'fire','rejected','2026-10-31 23:59:59');
INSERT INTO relief_items VALUES(1,'Demo food','packs'),(2,'Demo water','bottles');
INSERT INTO relief_distributions VALUES(1,1,'2026-11-01 00:00:00'),(2,1,'2026-11-30 23:59:59'),(3,2,'2026-11-20 12:00:00'),(4,3,'2026-12-01 00:00:00');
INSERT INTO relief_distribution_items VALUES(1,1,1,2),(2,1,2,3),(3,2,1,4),(4,3,2,5),(5,4,1,100);");
function check($condition,$label) { if (!$condition) { throw new RuntimeException($label); } echo "PASS $label\n"; }
$model=new DisasterSummaryModel(); $s=$model->summarize('2026-11-01 00:00:00','2026-12-01 00:00:00');
check($s['affected']===2,'affected registry total');
check(array_sum(array_column($s['centers'],'capacity'))===10,'capacity includes all centers');
check(array_sum(array_column($s['centers'],'occupancy'))===2,'active-only occupancy');
check(array_sum(array_column($s['centers'],'available'))===3,'open-center availability excludes closed center');
check(array_sum(array_column($s['incidents'],'total'))===3,'incident period boundaries');
$counts=[]; foreach($s['incidents'] as $row) {$counts[$row['incident_type'].'/'.$row['status']]=$row['total'];}
check($counts===['fire/resolved'=>1,'flood/pending'=>2],'type and status grouping');
check((int)$s['distributions']['events']===3 && (int)$s['distributions']['households']===2,'events and distinct households');
check((int)$s['goods'][0]['quantity']===6 && (int)$s['goods'][1]['quantity']===8,'multi-item quantities without join multiplication');
$empty=$model->summarize('2027-01-01 00:00:00','2027-02-01 00:00:00');
check(!$empty['goods'] && !$empty['incidents'] && (int)$empty['distributions']['events']===0,'empty-period totals');
check($empty['affected']===2 && array_sum(array_column($empty['centers'],'occupancy'))===2,'current snapshots independent of date filter');
