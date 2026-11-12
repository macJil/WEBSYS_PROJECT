"""Run after phase12_http.py on its marked disposable fixture only."""
from pathlib import Path
exec((Path(__file__).with_name('phase12_http.py')).read_text().split('# Explicit fixture marker')[0])
check(sql('SELECT label FROM websys_integration_fixture')=='fictional-only','fixture guard')
admin=Client(); resident=Client(); anon=Client()
for c,email in [(admin,'admin@example.invalid'),(resident,'resident1@example.invalid')]:
 success(c.post('/index.php',{'action':'login','email':email,'password':'DemoPass123!'}),'login')
for path in ['/resources/views/maps/map.php','/resources/views/maps/data.php','/resources/views/admin/admin.index.php','/resources/views/user/user.index.php','/resources/views/admin/records.php']:
 for method in ['get','post']:
  r=anon.get(path) if method=='get' else anon.post(path,{'action':'confirm'})
  check(r[0] in [401,403] or r[2].endswith('/index.php'),'anonymous '+method+' '+path)
for method in ['get','post']:
 r=resident.get('/resources/views/admin/records.php') if method=='get' else resident.post('/resources/views/admin/records.php',{'action':'confirm','type':'households'})
 check(r[0]==403,'resident selection denied '+method)
def data():
 r=resident.get('/resources/views/maps/data.php?role=admin');check(r[0]==200,'resident map response');return json.loads(r[1])
sql("UPDATE incident_reports SET archived_at=NULL,status='verified',is_public=1,public_label='Demo approximate point',public_latitude=16.41792,public_longitude=120.56091 WHERE id=1")
d=data();check(bool(d['incidents']),'published incident visible')
check('households' not in d and all(set(i)<=set(['id','incident_type','public_label','public_latitude','public_longitude','status','report_date']) for i in d['incidents']),'resident incident field allowlist')
sql('UPDATE incident_reports SET is_public=0 WHERE id=1');check(not data()['incidents'],'withdrawal removes marker')
sql("UPDATE evacuation_centers SET archived_at=NULL,status='open',is_public=1,latitude=16.41792,longitude=120.56091 WHERE id=1")
check(any(int(c['id'])==1 for c in data()['centers']),'open center visible')
sql("UPDATE evacuation_centers SET status='closed' WHERE id=1");check(not any(int(c['id'])==1 for c in data()['centers']),'closure removes marker')
sql("INSERT INTO relief_locations(name,location_type,is_public,latitude,longitude) VALUES ('Demo public point','distribution',1,16.41792,120.56091)")
check(bool(data()['relief_locations']),'approved relief point visible')
sql('UPDATE relief_locations SET is_public=0');check(not data()['relief_locations'],'unapproval removes marker')
sql('UPDATE incident_reports SET latitude=NULL,longitude=NULL WHERE id=1');check(sql('SELECT COUNT(*) FROM incident_reports WHERE id=1')=='1','no-point record retained')
print('TOTAL',len(passes))
