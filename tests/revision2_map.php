<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/../app/controllers/MapController.php';
$p=new PDO('sqlite::memory:',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
(new ReflectionProperty(DATABASE::class,'connection'))->setValue(null,$p);
$p->exec("CREATE TABLE incident_reports(id INTEGER,incident_type TEXT,public_label TEXT,public_latitude REAL,public_longitude REAL,status TEXT,created_at TEXT,archived_at TEXT,is_public INTEGER,location_description TEXT,latitude REAL,longitude REAL,reporter_name TEXT);
CREATE TABLE households(id INTEGER,household_code TEXT,purok TEXT,address_details TEXT,hazard_type TEXT,latitude REAL,longitude REAL,archived_at TEXT);
CREATE TABLE evacuation_centers(id INTEGER,name TEXT,address_details TEXT,latitude REAL,longitude REAL,status TEXT,capacity INTEGER,archived_at TEXT,is_public INTEGER);
CREATE TABLE evacuation_records(id INTEGER,center_id INTEGER,checked_out_at TEXT);
CREATE TABLE relief_locations(id INTEGER,name TEXT,address_details TEXT,latitude REAL,longitude REAL,location_type TEXT,archived_at TEXT,is_public INTEGER);
INSERT INTO incident_reports VALUES(1,'fire','Public demo',16,120,'verified','2026-11-12',NULL,1,'PRIVATE',16.5,120.5,'SECRET');
INSERT INTO households VALUES(1,'PRIVATE','PRIVATE','PRIVATE','flood',16,120,NULL);
INSERT INTO evacuation_centers VALUES(1,'Public center','Demo',16,120,'open',5,NULL,1),(2,'Closed','Demo',16,120,'closed',5,NULL,1),(3,'Private','Demo',16,120,'open',5,NULL,0);
INSERT INTO evacuation_records VALUES(1,1,NULL),(2,1,'2026-11-12');
INSERT INTO relief_locations VALUES(1,'Public relief','Demo',16,120,'distribution',NULL,1),(2,'Private storage','Demo',16,120,'storage',NULL,1);");
function check($ok,$label){if(!$ok)throw new RuntimeException($label);echo "PASS $label\n";}
$m=new MapModel();$r=$m->resident();$json=json_encode($r);
check(!str_contains($json,'PRIVATE')&&!str_contains($json,'SECRET')&&!isset($r['households']),'resident private fields absent');
check(count($r['centers'])===1 && $r['centers'][0]['occupancy']===1 && $r['centers'][0]['available']===4,'approved open centers and active occupancy');
check(count($r['relief_locations'])===1,'storage locations excluded');
check(count($m->admin()['households'])===1 && count($m->admin()['centers'])===3,'staff layers available');
foreach(["is_public=0","status='pending'","status='rejected'","archived_at='2026-11-12'","public_latitude=NULL","public_label=''","public_latitude=91"] as $change){
 $p->exec('UPDATE incident_reports SET '.$change);check(!$m->resident()['incidents'],'exclude '.$change);
 $p->exec("UPDATE incident_reports SET is_public=1,status='verified',archived_at=NULL,public_latitude=16,public_label='Public demo'");
}
check(MapController::publicationErrors(['is_public'=>1,'status'=>'verified','public_label'=>'Demo','public_latitude'=>16,'public_longitude'=>120])===[],'valid public data');
check((bool)MapController::publicationErrors(['is_public'=>1,'status'=>'pending','public_label'=>'Demo','public_latitude'=>16,'public_longitude'=>120]),'pending publication rejected');
