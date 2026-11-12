<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/../app/models/RecordActions.php';
// Explicit isolated fixture only, never the normal application connection.
$p=new PDO('mysql:unix_socket=/private/tmp/websys-rev6.sock;dbname=websys_revision6_fixture','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
if((int)$p->query('SELECT COUNT(*) FROM users')->fetchColumn()!==0)throw new RuntimeException('Use an empty disposable fixture.');

$p->exec("INSERT INTO users(id,full_name,username,email,password,role) VALUES(1,'Demo','demo','demo@example.invalid','unused','admin');
INSERT INTO households(id,household_code,representative_name,purok) VALUES(1,'Demo1','Demo','Demo'),(2,'Demo2','Demo','Demo');
INSERT INTO household_members(id,household_id,full_name) VALUES(1,2,'Demo member');
INSERT INTO relief_items(id,item_name,unit,quantity_on_hand) VALUES(1,'Empty','packs',0),(2,'Stocked','packs',3);
INSERT INTO evacuation_centers(id,name,capacity,status,is_public) VALUES(1,'Empty',3,'open',1),(2,'Occupied',3,'open',1);
INSERT INTO evacuation_records(center_id,household_member_id,recorded_by) VALUES(2,1,1);
INSERT INTO incident_reports(id,reported_by,incident_type,description,status,is_public) VALUES(1,1,'fire','Demo','resolved',1),(2,1,'flood','Demo','responding',0);
INSERT INTO response_updates(incident_id,updated_by,action_taken) VALUES(1,1,'Demo history');
INSERT INTO announcements(id,title,message,created_by,is_published,first_published_at) VALUES(1,'Draft','Demo',1,0,NULL),(2,'Published','Demo',1,1,NOW());
INSERT INTO responders(id,team_name) VALUES(1,'Demo team');
INSERT INTO incident_assignments(incident_id,responder_id,assigned_by,status) VALUES(1,1,1,'completed');");
$m=new RecordActionsModel($p);
function check($ok,$label){if(!$ok)throw new RuntimeException($label);echo "PASS $label\n";}
function batch($type,$ids,$action){global $m;$expected=[];foreach($m->selected($type,$ids) as $row)$expected[$row['id']]=hash('sha256',json_encode($row));$m->apply($type,$ids,$action,$expected);}
function blocked($type,$ids,$action){try{batch($type,$ids,$action);}catch(DomainException $e){echo 'PASS blocked '.$type.' '.$action."\n";return;}throw new RuntimeException('Unexpected success');}
blocked('households',[1,2],'delete');check($p->query('SELECT COUNT(*) FROM households')->fetchColumn()==2,'delete batch rollback');
blocked('goods',[1,2],'archive');check($p->query('SELECT COUNT(*) FROM relief_items WHERE archived_at IS NOT NULL')->fetchColumn()==0,'archive batch rollback');
blocked('centers',[1,2],'archive');blocked('members',[1],'archive');blocked('households',[2],'archive');
blocked('incidents',[1],'delete');blocked('incidents',[2],'archive');blocked('teams',[1],'delete');blocked('announcements',[2],'delete');
batch('centers',[1],'archive');check($p->query('SELECT is_public FROM evacuation_centers WHERE id=1')->fetchColumn()==0,'center archive withdraws visibility');
batch('incidents',[1],'archive');check($p->query('SELECT COUNT(*) FROM response_updates')->fetchColumn()==1,'incident history retained');
batch('teams',[1],'archive');check($p->query('SELECT is_active FROM responders WHERE id=1')->fetchColumn()==0,'team deactivated');
batch('announcements',[2],'archive');check($p->query('SELECT is_published FROM announcements WHERE id=2')->fetchColumn()==0,'announcement withdrawn');
batch('announcements',[1],'delete');check($p->query('SELECT COUNT(*) FROM announcements')->fetchColumn()==1,'unused draft deleted');
batch('goods',[1],'archive');check(count($m->listing('goods','',false,1))==1 && count($m->listing('goods','',true,1))==1,'active/archive filters');
$rows=$m->selected('goods',[2]);$expected=[2=>hash('sha256',json_encode($rows[0]))];$p->exec("UPDATE relief_items SET item_name='Changed' WHERE id=2");
try{$m->apply('goods',[2],'archive',$expected);throw new RuntimeException('Stale review accepted');}catch(DomainException $e){echo "PASS stale review rejected\n";}
try{$m->listing('users','',false,1);throw new RuntimeException('Invalid type accepted');}catch(DomainException $e){echo "PASS table allowlist\n";}
batch('households',[1],'delete');check($p->query('SELECT COUNT(*) FROM households')->fetchColumn()==1,'single unused record deletion');
