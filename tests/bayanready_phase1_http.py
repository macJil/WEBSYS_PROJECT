"""Fictional Phase 1 fixture only; see docs/BAYANREADY_PHASE_1_MAP_REVIEW.md."""
import urllib.request, urllib.parse, urllib.error, http.cookiejar, subprocess, json
BASE='http://127.0.0.1:18966'
passes=0
def sql(query):
    p=subprocess.run(['/usr/local/mysql/bin/mysql','--no-defaults','--socket=/private/tmp/websys-rev6.sock','-uroot','-N','-B','bayanready_phase1_fixture','-e',query],capture_output=True,text=True,check=True)
    return p.stdout.strip()
def check(ok,label):
    global passes
    assert ok,label
    passes+=1
    print('PASS',label)
class Client:
    def __init__(self): self.opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
    def request(self,path,data=None):
        try: r=self.opener.open(BASE+path,urllib.parse.urlencode(data).encode() if data is not None else None)
        except urllib.error.HTTPError as e: r=e
        return r.code,r.read().decode(),r.headers
    def map(self):
        code,body,headers=self.request('/resources/views/maps/data.php')
        check(code==200 and 'no-store' in headers['Cache-Control'],'fresh guarded map response')
        return json.loads(body)
check(sql('SELECT label FROM websys_integration_fixture')=='fictional-only','fixture marker')
check(sql('SELECT COUNT(*) FROM incident_reports')=='0','empty incident fixture')
admin,resident,anon=Client(),Client(),Client()
for client,email in [(admin,'admin@example.invalid'),(resident,'resident@example.invalid')]:
    check(client.request('/index.php',{'action':'login','email':email,'password':'DemoPass123!'})[0]==200,'fictional login')
check(anon.request('/resources/views/maps/data.php')[0]==401,'anonymous map rejected')
report=dict(action='create_incident',incident_type='flood',description='PRIVATE DEMO incident',reporter_name='PRIVATE DEMO reporter',contact_phone='0000000000',location_description='DEMO ONLY Irisan example',latitude='16.41792',longitude='120.56091',report_source='resident')
code,body,_=resident.request('/resources/views/incidents/create.php',report)
check(code==200 and 'Incident submitted for staff review.' in body,'ordinary resident form saves report')
id=int(sql('SELECT MAX(id) FROM incident_reports'))
snapshot=admin.map()
check(any(int(r['id'])==id and r['status']=='pending' and float(r['latitude'])==16.41792 for r in snapshot['incidents']),'new pending point visible to staff')
check(not resident.map()['incidents'],'new pending report stays off community map')
for lat,lng in [('16',''),('91','120'),('nan','120')]:
    before=sql('SELECT COUNT(*) FROM incident_reports')
    _,body,_=resident.request('/resources/views/incidents/create.php',dict(report,latitude=lat,longitude=lng))
    check('alert-danger' in body and sql('SELECT COUNT(*) FROM incident_reports')==before,'invalid coordinates rejected '+lat+','+lng)
resident.request('/resources/views/incidents/create.php',dict(report,latitude='',longitude='',description='DEMO ONLY no point'))
check(sql('SELECT COUNT(*) FROM incident_reports')=='2' and len(admin.map()['incidents'])==1,'no-point report saved but omitted from markers')
# Existing older test fixture point, deliberately outside the Irisan opening view.
resident.request('/resources/views/incidents/create.php',dict(report,latitude='12.8812',longitude='121.7763',description='DEMO ONLY distant legacy test point'))
check(len(snapshot['incidents'])==1 and len(admin.map()['incidents'])==2,'old page snapshot stale; new request includes distant saved point')
check(any(float(r['latitude'])==12.8812 for r in admin.map()['incidents']),'outside-view point retained without boundary filter')
# Simulate fixture lifecycle/publication states; there is no staff publication editor yet.
sql(f"UPDATE incident_reports SET status='verified' WHERE id={id}")
check(not resident.map()['incidents'],'verification alone does not publish')
sql(f"UPDATE incident_reports SET is_public=1,public_label='DEMO approximate public point',public_latitude=16.418,public_longitude=120.561 WHERE id={id}")
public=resident.map()
check(len(public['incidents'])==1 and 'PRIVATE' not in json.dumps(public) and 'households' not in public,'published safe projection only')
check(set(public['incidents'][0])=={'id','incident_type','public_label','public_latitude','public_longitude','status','report_date'},'public incident column allowlist')
for change,label in [("status='resolved'",'resolved remains published'),("status='rejected'",'rejected hidden'),("status='verified',is_public=0",'withdrawn hidden'),("is_public=1,public_latitude=NULL",'incomplete public point hidden'),("public_latitude=16.418,archived_at=NOW()",'archived hidden')]:
    sql(f'UPDATE incident_reports SET {change} WHERE id={id}')
    check(bool(resident.map()['incidents'])==(label=='resolved remains published'),label)
check(not any(int(r['id'])==id for r in admin.map()['incidents']),'archived incident excluded from active staff map')
for client in [admin,resident]:
    code,body,_=client.request('/resources/views/maps/map.php')
    check(code==200 and 'data-min-zoom="14"' in body and 'data-zoom="15"' in body and 'data-lat="16.41792"' in body,'Irisan opening settings reach map page')
print('TOTAL',passes)
