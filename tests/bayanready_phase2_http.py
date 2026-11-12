"""Fictional Phase 2 fixture only; see docs/BAYANREADY_PHASE_2_LOCATIONS.md."""
import urllib.request, urllib.parse, urllib.error, http.cookiejar, subprocess, json
BASE='http://127.0.0.1:18967'
passes=0
def sql(query):
    p=subprocess.run(['/usr/local/mysql/bin/mysql','--no-defaults','--socket=/private/tmp/websys-rev6.sock','-uroot','-N','-B','bayanready_phase2_fixture','-e',query],capture_output=True,text=True,check=True)
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
check(sql('SELECT COUNT(*) FROM incident_reports')=='0','empty fixture')
admin,resident=Client(),Client()
for client,email in [(admin,'admin@example.invalid'),(resident,'resident@example.invalid')]:
    check(client.request('/index.php',{'action':'login','email':email,'password':'DemoPass123!'})[0]==200,'login')
def saved(client,path,data,label):
    code,body,_=client.request(path,data)
    check(code==200 and 'alert-danger' not in body and 'could not' not in body,label)
    return body
def point(table,id):
    return sql(f"SELECT CONCAT(COALESCE(latitude,'NULL'),',',COALESCE(longitude,'NULL')) FROM {table} WHERE id={id}")
def equalpoint(table,id,lat,lng):
    got=point(table,id).split(',')
    return got==['NULL','NULL'] if lat=='' else all(abs(float(a)-float(b))<0.0000001 for a,b in zip(got,[lat,lng]))
def inputpoint(body,lat,lng):
    from html.parser import HTMLParser
    class Inputs(HTMLParser):
        def __init__(self):super().__init__();self.values={}
        def handle_starttag(self,tag,attrs):
            a=dict(attrs)
            if tag=='input' and a.get('name') in ['latitude','longitude']:self.values[a['name']]=a.get('value','')
    parser=Inputs();parser.feed(body)
    return all((parser.values.get(k)=='') if v=='' else abs(float(parser.values[k])-float(v))<0.0000001 for k,v in [('latitude',lat),('longitude',lng)])
lat,lng='16.4192','120.5623'
report=dict(action='create_incident',incident_type='flood',description='DEMO ONLY fictional incident',reporter_name='Demo resident',contact_phone='0000000000',location_description='DEMO ONLY Sample B',latitude=lat,longitude=lng)
saved(resident,'/resources/views/incidents/create.php',report,'new report saved')
check(equalpoint('incident_reports',1,lat,lng),'new report correct coordinates')
check(any(int(r['id'])==1 for r in admin.map()['incidents']),'new report on staff map')
check('data-lat="16.4192000"' in resident.request('/resources/views/incidents/view.php?id=1')[1],'resident saved point after reload')
saved(resident,'/resources/views/incidents/create.php',dict(report,description='DEMO ONLY second report'),'second report')
incidentPath='/resources/views/incidents/view.php?id=1'
correction=dict(action='update_location',location_description='DEMO ONLY corrected address',latitude='16.4168',longitude='120.5597',location_notes='Fictional pin correction')
check(resident.request(incidentPath,correction)[0]==403,'resident cannot submit staff location correction')
saved(admin,incidentPath,correction,'staff location correction')
check(equalpoint('incident_reports',1,'16.4168','120.5597') and equalpoint('incident_reports',2,lat,lng),'incident edit changes only selected record')
check(sql('SELECT COUNT(*) FROM response_updates WHERE incident_id=1')=='1','correction history retained')
check(sql('SELECT status FROM incident_reports WHERE id=1')=='pending','correction leaves workflow status unchanged')
check(inputpoint(admin.request(incidentPath)[1],'16.4168','120.5597'),'incident edit reload uses saved point')
# A different form submission must not clear the correction picker.
_,body,_=admin.request(incidentPath,{'action':'review_incident','status':'verified','notes':''})
check(inputpoint(body,'16.4168','120.5597'),'invalid review does not erase saved pin')
saved(admin,incidentPath,dict(correction,latitude='',longitude=''),'clear incident point')
check(equalpoint('incident_reports',1,'','') and 'No map point' in admin.request(incidentPath)[1],'incident clear persists and explains omission')
check('No map point' in resident.request('/resources/views/incidents/list.php?search=fictional')[1],'no-point incident searchable')
check(sql('SELECT COUNT(*) FROM incident_reports')=='2','clearing does not remove incident')
h=dict(action='create_household',household_code='DEMO-P2-A',representative_name='Demo representative',contact_phone='0000000000',purok='DEMO ONLY Purok A',address_details='DEMO ONLY household address',registry_reason='affected',hazard_type='flood',hazard_notes='',vulnerability_category='unassessed',vulnerability_notes='',evacuation_status='at_home',latitude=lat,longitude=lng)
c=dict(action='save_center',name='DEMO ONLY Center A',address_details='DEMO ONLY center address',capacity='10',status='open',latitude=lat,longitude=lng)
r=dict(action='save_location',name='DEMO ONLY Relief A',address_details='DEMO ONLY relief address',location_type='distribution',latitude=lat,longitude=lng)
for table,path,detail,data,key in [
 ('households','/resources/views/admin/households.php','/resources/views/admin/household.php',h,'household_code'),
 ('evacuation_centers','/resources/views/admin/evacuation/centers.php','/resources/views/admin/evacuation/center.php',c,'name'),
 ('relief_locations','/resources/views/admin/relief/locations.php','/resources/views/admin/relief/location.php',r,'name')]:
    saved(admin,path,data,table+' create')
    saved(admin,path,dict(data,**{key:data[key]+' SECOND'}),table+' second record')
    edit=dict(data,action='update_household' if table=='households' else data['action'],latitude='16.4168',longitude='120.5597')
    saved(admin,path+'?edit=1',edit,table+' edit')
    check(equalpoint(table,1,'16.4168','120.5597') and equalpoint(table,2,lat,lng),table+' only selected point changed')
    check(inputpoint(admin.request(path+'?edit=1')[1],'16.4168','120.5597'),table+' edit reload uses saved point')
    check('data-lat="16.4168000"' in admin.request(detail+'?id=1')[1],table+' staff detail shows saved point')
    for changes in [dict(longitude=''),dict(latitude='91'),dict(address_details='')]:
        _,body,_=admin.request(path+'?edit=1',dict(edit,**changes))
        check('alert-danger' in body and equalpoint(table,1,'16.4168','120.5597'),table+' invalid edit leaves saved point intact')
    _,body,_=admin.request(path+'?edit=1',dict(edit,latitude='16.4181',longitude='120.5611',**{key:''}))
    check(inputpoint(body,'16.4181','120.5611'),table+' failed form retains submitted pin')
    saved(admin,path+'?edit=1',dict(edit,latitude='0',longitude='0'),table+' zero coordinates accepted')
    check('No map point' not in admin.request(detail+'?id=1')[1],table+' zero is not missing')
    saved(admin,path+'?edit=1',dict(edit,latitude='',longitude=''),table+' clear pin')
    check(equalpoint(table,1,'','') and 'No map point' in admin.request(detail+'?id=1')[1],table+' clear persists on detail')
    check('No map point' in admin.request(path+'?q=DEMO')[1],table+' no-point record remains in list')
    check(sql(f'SELECT COUNT(*) FROM {table}')=='2',table+' records retained')
    check(resident.request(path+'?edit=1',edit)[0]==403,table+' resident edit denied')
# Finish with an edited center pin for staff browser verification.
saved(admin,'/resources/views/admin/evacuation/centers.php?edit=1',dict(c,latitude='16.4168',longitude='120.5597'),'final edited center')
check(any(int(row['id'])==1 and float(row['latitude'])==16.4168 for row in admin.map()['centers']),'edited center map point after reload')
check(sql("SELECT COUNT(*) FROM households WHERE registry_reason='affected'")=='2','no-point household still affected')
check(sql("SELECT SUM(capacity) FROM evacuation_centers WHERE status='open'")=='20','center capacity retained')
# Correction validation, publication separation, and archived-record protection.
_,body,_=admin.request(incidentPath,dict(correction,latitude='16.4',longitude=''))
check('alert-danger' in body and equalpoint('incident_reports',1,'',''),'partial incident correction rejected')
sql("UPDATE incident_reports SET status='verified',is_public=1,public_label='DEMO approximate public location',public_latitude=16.418,public_longitude=120.561 WHERE id=1")
saved(admin,incidentPath,correction,'correct published incident private point')
check(sql('SELECT CONCAT(public_latitude,",",public_longitude) FROM incident_reports WHERE id=1')=='16.4180000,120.5610000','private correction leaves approved public point unchanged')
count=sql('SELECT COUNT(*) FROM response_updates WHERE incident_id=1')
sql('UPDATE incident_reports SET archived_at=NOW() WHERE id=1')
_,body,_=admin.request(incidentPath,dict(correction,latitude='16.4192',longitude='120.5623'))
check('Archived or missing incidents cannot be edited.' in body and equalpoint('incident_reports',1,'16.4168','120.5597'),'archived location correction blocked')
check(sql('SELECT COUNT(*) FROM response_updates WHERE incident_id=1')==count,'blocked correction adds no history')
print('TOTAL',passes)
