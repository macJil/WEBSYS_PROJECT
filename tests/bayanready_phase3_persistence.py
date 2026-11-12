"""Run only on the marked Phase 3 fictional fixture and copied site at localhost:18968."""
import urllib.request, urllib.parse, urllib.error, http.cookiejar, subprocess
BASE='http://127.0.0.1:18968'
def sql(q):
    return subprocess.run(['/usr/local/mysql/bin/mysql','--no-defaults','--socket=/private/tmp/websys-rev6.sock','-uroot','-N','-B','bayanready_phase3_fixture','-e',q],capture_output=True,text=True,check=True).stdout.strip()
assert sql('SELECT label FROM websys_integration_fixture')=='fictional-only'
assert sql('SELECT COUNT(*) FROM incident_reports')=='0','Use a fresh incident fixture'
class Client:
    def __init__(self):self.client=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
    def request(self,path,data=None):
        try:r=self.client.open(BASE+path,urllib.parse.urlencode(data).encode() if data is not None else None)
        except urllib.error.HTTPError as e:r=e
        return r.code,r.read().decode()
admin,resident=Client(),Client()
for c,role in [(admin,'admin'),(resident,'resident')]:
    assert c.request('/index.php',{'action':'login','email':role+'@example.invalid','password':'DemoPass123!'})[0]==200
# Label returned by live Photon check, made clearly fictional for storage.
label='DEMO ONLY — Irisan, District 16, Baguio'
lat,lng='16.4179398','120.5609179'
h=dict(action='create_household',household_code='DEMO-P3',representative_name='Demo',contact_phone='',purok='DEMO',address_details=label,registry_reason='monitored',hazard_type='unassessed',hazard_notes='',vulnerability_category='unassessed',vulnerability_notes='',evacuation_status='at_home')
c=dict(action='save_center',name='DEMO ONLY P3 persistence',address_details=label,capacity='5',status='closed')
l=dict(action='save_location',name='DEMO ONLY P3 relief',address_details=label,location_type='distribution')
r=dict(action='create_incident',incident_type='other',description='Fictional exercise',reporter_name='Demo',contact_phone='0000000000',location_description=label)
for client,path,table,data,field,detail in [
    (resident,'/resources/views/incidents/create.php','incident_reports',r,'location_description','/resources/views/incidents/view.php'),
    (admin,'/resources/views/admin/households.php','households',h,'address_details','/resources/views/admin/household.php'),
    (admin,'/resources/views/admin/evacuation/centers.php','evacuation_centers',c,'address_details','/resources/views/admin/evacuation/center.php'),
    (admin,'/resources/views/admin/relief/locations.php','relief_locations',l,'address_details','/resources/views/admin/relief/location.php')]:
    code,body=client.request(path)
    assert code==200 and 'data-address-enabled' in body and 'address-suggestion.js' in body
    code,body=client.request(path,dict(data,latitude=lat,longitude=lng))
    assert code==200 and 'alert-danger' not in body
    id=int(sql(f'SELECT MAX(id) FROM {table}'))
    assert sql(f'SELECT {field} FROM {table} WHERE id={id}')==label
    assert sql(f'SELECT CONCAT(latitude,\",\",longitude) FROM {table} WHERE id={id}')==lat+','+lng
    code,body=client.request(detail+'?id='+str(id))
    assert code==200 and label in body and lat in body and lng in body
    print('PASS suggestion controls, saved written text, coordinates and detail reload:',table)
print('TOTAL 4 module persistence workflows')
