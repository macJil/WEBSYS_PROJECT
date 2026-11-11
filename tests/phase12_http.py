import urllib.request, urllib.parse, urllib.error, http.cookiejar, subprocess, json, re, base64
from pathlib import Path
BASE='http://127.0.0.1:18961'
ROOT=Path(__file__).resolve().parents[1]
passes=[]
def check(ok,label):
 if not ok: raise AssertionError(label)
 passes.append(label); print('PASS',label,flush=True)
def sql(q):
 p=subprocess.run(['/usr/local/mysql/bin/mysql','--no-defaults','--socket=/private/tmp/websys-phase12.sock','-uroot','-N','-B','barangay_drrm','-e',q],capture_output=True,text=True)
 if p.returncode: raise RuntimeError(p.stderr)
 return p.stdout.strip()
class Client:
 def __init__(self): self.opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
 def req(self,path,data=None,file=None):
  headers={}
  if file:
   boundary='WebsysPhase12Boundary'; parts=[]
   for k,v in data.items():parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{k}"\r\n\r\n{v}\r\n'.encode())
   name,mime,content=file
   parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="image"; filename="{name}"\r\nContent-Type: {mime}\r\n\r\n'.encode()+content+b'\r\n')
   parts.append(f'--{boundary}--\r\n'.encode()); body=b''.join(parts);headers['Content-Type']='multipart/form-data; boundary='+boundary
  else: body=urllib.parse.urlencode(data).encode() if data is not None else None
  try:r=self.opener.open(urllib.request.Request(BASE+path,body,headers),timeout=20)
  except urllib.error.HTTPError as e:r=e
  return r.code,r.read(),r.url,dict(r.headers)
 def get(self,path): return self.req(path)
 def post(self,path,data,file=None):return self.req(path,data,file)
def success(result,label):
 check(result[0]==200 and b'could not' not in result[1] and b'alert-danger' not in result[1],label)
 return result
# Explicit fixture marker plus application connection checks prevent running on a normal database.
check(sql("SELECT label FROM websys_integration_fixture")=='fictional-only','isolated fixture marker')
preflight=subprocess.run(['php','-r',"require 'config/db.php'; $p=(new DATABASE())->connect(); echo $p->query('SELECT label FROM websys_integration_fixture')->fetchColumn().':'.$p->query('SELECT @@port')->fetchColumn();"],cwd=ROOT,capture_output=True,text=True)
check(preflight.returncode==0 and preflight.stdout=='fictional-only:18960','application uses isolated fixture database')
check(sql('SELECT COUNT(*) FROM users')=='1' and sql('SELECT COUNT(*) FROM households')=='0','fixture starts with only a demo admin')
admin=Client(); resident=Client(); other=Client(); anon=Client()
for client,n in [(resident,1),(other,2)]:
 success(client.post('/index.php',{'action':'register','full_name':f'Demo Resident {n}','username':f'demo_resident{n}','email':f'resident{n}@example.invalid','phone':'0000000000','password':'DemoPass123!','password_confirmation':'DemoPass123!','role':'admin'}),'resident registration '+str(n))
check(sql("SELECT GROUP_CONCAT(role) FROM users WHERE username LIKE 'demo_resident%'")=='resident,resident','public registration ignores forged admin role')
for client,email in [(admin,'admin@example.invalid'),(resident,'resident1@example.invalid'),(other,'resident2@example.invalid')]:success(client.post('/index.php',{'action':'login','email':email,'password':'DemoPass123!'}),'login '+email)
admin_pages=['/'+str(p.relative_to(ROOT)) for p in (ROOT/'resources/views/admin').rglob('*.php')]
resident_pages=['/'+str(p.relative_to(ROOT)) for p in (ROOT/'resources/views/user').rglob('*.php')]
incident_pages=['/'+str(p.relative_to(ROOT)) for p in (ROOT/'resources/views/incidents').rglob('*.php')]
for path in admin_pages+resident_pages+incident_pages:
 for method in ['get','post']:
  result=anon.get(path) if method=='get' else anon.post(path,{'action':'forged'})
  check(result[2].endswith('/index.php'),'anonymous '+method+' blocked '+path)
for path in admin_pages:
 for method in ['get','post']:
  result=resident.get(path) if method=='get' else resident.post(path,{'action':'forged'})
  check(result[0]==403,'resident '+method+' denied '+path)
for path in resident_pages:check(admin.get(path)[0]==403,'admin denied resident-only page '+path)
h={'household_code':'DEMO-001','representative_name':'Demo Household <script>x</script>','contact_phone':'0000000000','purok':'Demo Purok','address_details':'Fictional street','registry_reason':'affected','hazard_type':'unassessed','hazard_notes':'','vulnerability_category':'unassessed','vulnerability_notes':'','evacuation_status':'at_home'}
success(admin.post('/resources/views/admin/households.php',dict(h,action='create_household')),'create household')
hid=sql('SELECT MAX(id) FROM households')
success(admin.post('/resources/views/admin/households.php?edit='+hid,dict(h,action='update_household',address_details='Edited fictional street')),'edit household')
result=admin.get('/resources/views/admin/households.php?q=DEMO-001');check(b'DEMO-001' in result[1] and b'<script>x</script>' not in result[1],'household search and escaping')
success(admin.post('/resources/views/admin/household.php?id='+hid,{'action':'create_member','full_name':'Demo Member','age':'0','notes':'Fictional'}),'create member age zero')
mid=sql('SELECT MAX(id) FROM household_members')
success(admin.post('/resources/views/admin/household.php?id='+hid+'&member='+mid,{'action':'update_member','full_name':'Demo Member Edited','age':'1','notes':'Fictional'}),'edit member')
check(sql('SELECT COUNT(*) FROM household_members WHERE household_id='+hid)=='1','member count from rows')
png=base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=')
report={'action':'create_incident','incident_type':'flood','description':'Demo incident only','reporter_name':'Demo reporter','contact_phone':'0000000000','location_description':'DEMO ONLY Sample B','latitude':'12.8812','longitude':'121.7763','report_source':'admin'}
success(resident.post('/resources/views/incidents/create.php',report,('demo.png','image/png',png)),'resident incident and image')
iid=sql('SELECT MAX(id) FROM incident_reports');imageid=sql('SELECT MAX(id) FROM incident_images')
check(sql('SELECT report_source FROM incident_reports WHERE id='+iid)=='resident','resident cannot forge report source')
check(other.get('/resources/views/incidents/view.php?id='+iid)[0]==404,'other resident incident privacy')
check(other.get('/resources/views/incidents/image.php?id='+imageid)[0]==404,'other resident image privacy')
for client in [admin,resident]:
 result=client.get('/resources/views/incidents/image.php?id='+imageid);check(result[0]==200 and result[1]==png and result[3]['Content-Type']=='image/png','authorized private image stream')
check(resident.post('/resources/views/incidents/view.php?id='+iid,{'action':'review_incident','status':'resolved','notes':'forged'})[0]==403,'resident review submission blocked')
count=sql('SELECT COUNT(*) FROM incident_reports')
for name,mime,data in [('fake.png','image/png',b'<?php echo 1; ?>'),('huge.png','image/png',b'x'*(5242880+1))]:
 result=resident.post('/resources/views/incidents/create.php',report,(name,mime,data));check(b'alert-danger' in result[1] and sql('SELECT COUNT(*) FROM incident_reports')==count,'invalid upload rejected '+name)
result=resident.post('/resources/views/incidents/create.php',dict(report,latitude='91'));check(b'alert-danger' in result[1],'coordinate range validation')
success(admin.post('/resources/views/incidents/create.php',dict(report,report_source='phone',latitude='',longitude='')),'admin phone incident')
check(b'Demo incident only' in admin.get('/resources/views/incidents/view.php?id='+iid)[1],'staff incident detail')
check(b'View' in admin.get('/resources/views/incidents/list.php?incident_type=flood&status=pending&location=Sample')[1],'incident search/filter')
for n in [1,2]:success(admin.post('/resources/views/admin/response/teams.php',{'action':'save_team','team_name':f'Demo Team {n}','contact_phone':'0000000000','is_active':'1'}),'create team '+str(n))
tids=sql('SELECT id FROM responders ORDER BY id').splitlines()
resp='/resources/views/admin/response/incident.php?id='+iid
check(b'alert-danger' in admin.post(resp,{'action':'assign_team','team_id':tids[0],'notes':'Demo dispatch'})[1],'pending incident cannot dispatch')
success(admin.post('/resources/views/incidents/view.php?id='+iid,{'action':'review_incident','status':'verified','notes':'Demo verification'}),'verify incident')
for tid in tids:success(admin.post(resp,{'action':'assign_team','team_id':tid,'notes':'Demo dispatch'}),'assign team '+tid)
check(sql('SELECT status FROM incident_reports WHERE id='+iid)=='responding','dispatch changes incident status')
check(b'alert-danger' in admin.post('/resources/views/incidents/view.php?id='+iid,{'action':'review_incident','status':'resolved','notes':'Premature'})[1],'premature closure blocked')
for aid in sql('SELECT id FROM incident_assignments').splitlines():success(admin.post(resp,{'action':'update_assignment','assignment_id':aid,'assignment_status':'completed','notes':'Demo work completed'}),'complete assignment '+aid)
success(admin.post('/resources/views/incidents/view.php?id='+iid,{'action':'review_incident','status':'resolved','notes':'Demo resolved'}),'resolve after teams complete')
check(int(sql('SELECT COUNT(*) FROM response_updates WHERE incident_id='+iid))==6,'response append-only history')
success(admin.post('/resources/views/admin/response/teams.php?edit='+tids[0],{'action':'save_team','team_name':'Demo Team 1 Edited','contact_phone':'0000000000','is_active':'0'}),'edit deactivate team')
for n in [1,2]:success(admin.post('/resources/views/admin/evacuation/centers.php',{'action':'save_center','name':f'Demo Center {n}','address_details':'Fictional address','capacity':'2','status':'open'}),'create center '+str(n))
cids=sql('SELECT id FROM evacuation_centers ORDER BY id').splitlines();center='/resources/views/admin/evacuation/center.php?id='+cids[0]
success(admin.post(center,{'action':'checkin','kind':'member','member_id':mid}),'member checkin')
check(b'alert-danger' in admin.post('/resources/views/admin/evacuation/center.php?id='+cids[1],{'action':'checkin','kind':'member','member_id':mid})[1],'duplicate member across centers blocked')
walk={'action':'checkin','kind':'new_walkin','full_name':'Demo Walkin','age':'20','address_details':'Fictional address','contact_phone':'0000000000'}
success(admin.post(center,walk),'walkin checkin')
check(sql('SELECT COUNT(*) FROM evacuation_records WHERE checked_out_at IS NULL')=='2','active occupancy')
check(b'alert-danger' in admin.post(center,dict(walk,full_name='Demo Overflow'))[1],'capacity prevents overflow')
check(b'alert-danger' in admin.post('/resources/views/admin/evacuation/center.php?id='+cids[1],walk)[1],'duplicate walkin across centers blocked')
check(b'alert-danger' in admin.post('/resources/views/admin/evacuation/centers.php?edit='+cids[0],{'action':'save_center','name':'Demo Center 1','address_details':'Fictional address','capacity':'1','status':'open'})[1],'capacity cannot drop below occupancy')
for rid in sql('SELECT id FROM evacuation_records WHERE checked_out_at IS NULL').splitlines():success(admin.post(center,{'action':'checkout','record_id':rid}),'checkout '+rid)
success(admin.post('/resources/views/admin/evacuation/centers.php?edit='+cids[0],{'action':'save_center','name':'Demo Center 1 Edited','address_details':'Fictional address','capacity':'2','status':'closed'}),'close empty center')
check(sql('SELECT COUNT(*) FROM evacuation_records')=='2','evacuation history retained')
check(b'Demo Center 1 Edited' in admin.get('/resources/views/admin/evacuation/centers.php?q=Edited')[1],'center search')
for name,unit in [('Demo Food','packs'),('Demo Water','bottles')]:success(admin.post('/resources/views/admin/relief/inventory.php',{'action':'save_item','item_name':name,'unit':unit}),'add relief '+name)
gids=sql('SELECT id FROM relief_items ORDER BY id').splitlines()
for gid in gids:success(admin.post('/resources/views/admin/relief/movements.php',{'action':'stock','item_id':gid,'movement_type':'stock_in','quantity':'10','notes':'Demo donation'}),'stock in '+gid)
success(admin.post('/resources/views/admin/relief/movements.php',{'action':'stock','item_id':gids[0],'movement_type':'stock_out','quantity':'1','notes':'Demo damage'}),'manual stock out')
dist={'action':'distribute','household_id':hid,'received_by':'Demo Receiver','notes':'Demo relief','goods['+gids[0]+']':'2','goods['+gids[1]+']':'3'}
check(b'alert-danger' in admin.post('/resources/views/admin/relief/distributions.php',dict(dist,household_id='999999'))[1],'unregistered household distribution blocked')
excess=dict(dist);excess['goods['+gids[1]+']']='99'
check(b'alert-danger' in admin.post('/resources/views/admin/relief/distributions.php',excess)[1],'insufficient distribution rejected')
check(sql('SELECT GROUP_CONCAT(quantity_on_hand ORDER BY id) FROM relief_items')=='9,10' and sql('SELECT COUNT(*) FROM relief_distributions')=='0','failed distribution rolls back all writes')
success(admin.post('/resources/views/admin/relief/distributions.php',dist),'multi-good distribution')
check(sql('SELECT GROUP_CONCAT(quantity_on_hand ORDER BY id) FROM relief_items')=='7,7','distribution deducts exact amounts')
check(sql("SELECT COUNT(*) FROM relief_stock_movements WHERE movement_type='distribution'")=='2','linked distribution stock history')
check(b'Demo Food' in admin.get('/resources/views/admin/relief/inventory.php?q=Food')[1],'inventory search')
check(b'Demo donation' in admin.get('/resources/views/admin/relief/movements.php?q=donation&type=stock_in')[1],'movement filters')
success(admin.post('/resources/views/admin/relief/inventory.php?edit='+gids[0],{'action':'save_item','item_name':'Demo Food Edited','unit':'packs'}),'edit good')
check(b'alert-danger' in admin.post('/resources/views/admin/relief/inventory.php?edit='+gids[0],{'action':'save_item','item_name':'Demo Food Edited','unit':'boxes'})[1],'historical unit protected')
notice={'action':'save_draft','title':'Demo Notice <tag>','message':'DEMO ONLY demo@example.invalid'}
success(admin.post('/resources/views/admin/announcement/announcement.php',notice),'save announcement draft');nid=sql('SELECT MAX(id) FROM announcements')
check(b'Demo Notice' not in resident.get('/resources/views/user/announcement.php?status=draft')[1],'draft hidden despite forged filter')
notice['action']='publish';success(admin.post('/resources/views/admin/announcement/announcement.update.php?id='+nid,notice),'publish notice')
check(b'Demo Notice &lt;tag&gt;' in resident.get('/resources/views/user/announcement.php?q=Demo')[1],'resident sees escaped published notice')
notice['action']='save_draft';success(admin.post('/resources/views/admin/announcement/announcement.update.php?id='+nid,notice),'withdraw notice')
check(b'Demo Notice' not in resident.get('/resources/views/user/announcement.php')[1],'withdrawal hides notice')
summary=admin.get('/resources/views/admin/summaries/disaster.php?from=2020-01-01&to=2030-12-31');success(summary,'summary shared database')
check(b'Demo Household' not in summary[1] and b'Demo Receiver' not in summary[1] and b'0000000000' not in summary[1],'summary excludes private records')
check(admin.get('/resources/views/admin/summaries/disaster.php?from=2026-02-30')[0]==400,'invalid summary date rejected')
# Linked-history foreign keys reject deletions even outside the UI.
for table,id in [('households',hid),('household_members',mid),('incident_reports',iid),('responders',tids[0]),('evacuation_centers',cids[0]),('relief_items',gids[0]),('relief_distributions','1')]:
 try:sql(f'DELETE FROM {table} WHERE id={id}');raise AssertionError('Deletion unexpectedly succeeded '+table)
 except RuntimeError as e:check('1451' in str(e),'FK protects '+table)
# Shared navigation links resolve for admin. No external requests.
home=admin.get('/resources/views/admin/admin.index.php')[1].decode()
for href in re.findall(r'href="([^"]+)"',home):
 if href.startswith('/'):check(admin.get(href.replace('&amp;','&'))[0]==200,'navigation link '+href)
# No personal data is exported; the output contains check labels only.
print('TOTAL',len(passes))
