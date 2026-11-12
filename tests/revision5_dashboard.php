<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/../app/models/Dashboard.php';require __DIR__.'/../app/models/Relief.php';require __DIR__.'/../app/models/Report.php';
$p=new PDO('sqlite::memory:',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$p->exec("CREATE TABLE incident_reports(id INTEGER,reported_by INTEGER,status TEXT,archived_at TEXT,created_at TEXT); INSERT INTO incident_reports VALUES(1,1,'pending',NULL,'2026-11-12'),(2,2,'pending',NULL,'2026-11-12'),(3,1,'resolved',NULL,'2026-11-12'),(4,1,'pending','2026-11-12','2026-11-12');
CREATE TABLE evacuation_centers(id INTEGER,name TEXT,address_details TEXT,capacity INTEGER,status TEXT,is_public INTEGER,archived_at TEXT); INSERT INTO evacuation_centers VALUES(1,'Demo','Demo',3,'open',1,NULL),(2,'Internal','Demo',5,'open',0,NULL),(3,'Closed','Demo',5,'closed',1,NULL);
CREATE TABLE evacuation_records(id INTEGER,center_id INTEGER,household_member_id INTEGER,walkin_name TEXT,checked_in_at TEXT,checked_out_at TEXT); INSERT INTO evacuation_records VALUES(1,1,NULL,'Demo visitor','2026-11-12',NULL),(2,1,NULL,'Demo past','2026-11-12','2026-11-12');
CREATE TABLE household_members(id INTEGER,full_name TEXT);
CREATE TABLE households(id INTEGER,household_code TEXT,representative_name TEXT,registry_reason TEXT,archived_at TEXT); INSERT INTO households VALUES(1,'Demo1','Demo','affected',NULL),(2,'Demo2','Demo','both','2026-11-12');
CREATE TABLE announcements(id INTEGER,title TEXT,is_published INTEGER,archived_at TEXT,created_at TEXT); INSERT INTO announcements VALUES(1,'Public',1,NULL,'2026-11-12'),(2,'Draft',0,NULL,'2026-11-12');
CREATE TABLE relief_items(id INTEGER PRIMARY KEY AUTOINCREMENT,item_name TEXT,unit TEXT,quantity_on_hand INTEGER DEFAULT 0,minimum_stock INTEGER DEFAULT 0,archived_at TEXT); INSERT INTO relief_items VALUES(1,'Low','packs',2,3,NULL),(2,'Equal','bottles',3,3,NULL),(3,'Disabled','packs',0,0,NULL),(4,'Archived','packs',0,3,'2026-11-12');");
function check($ok,$label){if(!$ok)throw new RuntimeException($label);echo "PASS $label\n";}
$d=new DashboardModel($p);$r=new ReliefModel($p);$reports=new ReportModel($p);
$f=['incident_type'=>'','status'=>'pending','date'=>'','location'=>'','search'=>''];
check($d->incidents(1)['pending']===1 && $d->incidents(null)['pending']===2,'account-scoped and current incident counts');
check(count($reports->listing($f,1))===$d->incidents(1)['pending'],'incident link count parity');
check(count($d->centers(true))===1 && count($d->centers(false))===2,'public centers exclude closed/private');
check((int)$d->centers(true)[0]['occupancy']===1 && count($d->stays())===1,'active stays count');
check(count($d->affected())===1,'current affected households');
check(count($d->notices())===1,'published notices only');
check($d->lowStock()===1 && count($r->items('',true))===1,'low stock parity with threshold zero/equality/archive');
$r->saveItem(null,'New','pieces',4);check($d->lowStock()===2,'saved minimum threshold affects counts');
