<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/../app/models/Dashboard.php';
require __DIR__.'/../app/models/Report.php';
$p=db();
if($p->query('SELECT label FROM websys_integration_fixture')->fetchColumn()!=='fictional-only'||(int)$p->query('SELECT @@port')->fetchColumn()!==18960)throw new RuntimeException('Isolated fixture required.');
$d=new DashboardModel();$r=new ReportModel();$n=0;
function check($ok,$label){global $n;if(!$ok)throw new RuntimeException($label);$n++;echo "PASS $label\n";}
foreach([null,2,3] as $owner){foreach($d->incidents($owner) as $status=>$total){
 $filters=array_fill_keys(['incident_type','status','date','location','search'],'');$filters['status']=$status;
 check(count($r->listing($filters,$owner))===$total,'incident dashboard/list parity '.($owner??'admin').' '.$status);
}}
foreach([false,true] as $public){
 $rows=$d->centers($public);
 check(count($rows)==(int)$p->query("SELECT COUNT(*) FROM evacuation_centers WHERE archived_at IS NULL AND status='open'".($public?' AND is_public=1':''))->fetchColumn(),'center count parity');
 foreach($rows as $row)check((int)$row['occupancy']==(int)$p->query('SELECT COUNT(*) FROM evacuation_records WHERE checked_out_at IS NULL AND center_id='.(int)$row['id'])->fetchColumn(),'occupancy parity');
}
check(count($d->affected())==(int)$p->query("SELECT COUNT(*) FROM households WHERE archived_at IS NULL AND registry_reason IN ('affected','both')")->fetchColumn(),'affected parity');
check(count($d->stays())==(int)$p->query('SELECT COUNT(*) FROM evacuation_records WHERE checked_out_at IS NULL')->fetchColumn(),'active stays parity');
check($d->lowStock()==(int)$p->query('SELECT COUNT(*) FROM relief_items WHERE archived_at IS NULL AND minimum_stock>0 AND quantity_on_hand<minimum_stock')->fetchColumn(),'low stock parity');
check(count($d->notices())==(int)$p->query('SELECT COUNT(*) FROM announcements WHERE archived_at IS NULL AND is_published=1')->fetchColumn(),'notice parity');
echo "TOTAL $n\n";
