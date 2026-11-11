<?php
// Actual ReportModel persistence against fictional in-memory records, no live MySQL writes.
require __DIR__ . '/../app/models/Report.php';
$pdo=new PDO('sqlite::memory:',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
(new ReflectionProperty(DATABASE::class,'connection'))->setValue(null,$pdo);
$pdo->exec('CREATE TABLE incident_reports(id INTEGER PRIMARY KEY AUTOINCREMENT,reported_by INTEGER,report_source TEXT,reporter_name TEXT,incident_type TEXT,description TEXT,contact_phone TEXT,location_description TEXT,latitude NUMERIC,longitude NUMERIC)');
$model=new ReportModel();
$data=['reported_by'=>1,'report_source'=>'resident','reporter_name'=>'Demo resident','incident_type'=>'other','description'=>'Fictional exercise','contact_phone'=>'0000000000','location_description'=>'DEMO ONLY — Purok Sample B, fictional roadside landmark','latitude'=>'12.8812000','longitude'=>'121.7763000'];
$first=$model->create($data,null);
$data['reported_by']=2;$data['latitude']='12.8797000';$data['longitude']='121.7740000';$data['location_description']='DEMO ONLY — Purok Sample A';
$second=$model->create($data,null);
function check($ok,$label) { if(!$ok) throw new RuntimeException($label); echo "PASS $label\n"; }
$saved=$model->find($first,null);
check(abs((float)$saved['latitude']-12.8812)<0.0000001 && abs((float)$saved['longitude']-121.7763)<0.0000001,'staff retrieves intended coordinates');
check(str_contains($saved['location_description'],'Sample B'),'description remains linked to intended incident');
check($model->find($second,null)['location_description']==='DEMO ONLY — Purok Sample A','second report remains separate');
check(!$model->find($first,2),'other resident cannot read selected incident');
check((int)$model->find($first,1)['id']===$first,'reporter can review own location');
