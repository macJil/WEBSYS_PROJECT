"""Run after phase12_http.py on its marked disposable fictional fixture."""
from pathlib import Path
exec(Path(__file__).with_name('phase12_http.py').read_text().split('# Explicit fixture marker')[0])

check(sql("SELECT label FROM websys_integration_fixture") == 'fictional-only', 'fictional fixture guard')
check(sql('SELECT @@port') == '18960', 'isolated server port')
admin, resident = Client(), Client()
for client, email in [(admin, 'admin@example.invalid'), (resident, 'resident1@example.invalid')]:
    success(client.post('/index.php', {'action': 'login', 'email': email, 'password': 'DemoPass123!'}), 'login ' + email)

def map_data(client):
    response = client.get('/resources/views/maps/data.php')
    check(response[0] == 200, 'map response')
    return json.loads(response[1])

sql("UPDATE incident_reports SET status='verified',latitude=16.6272,longitude=120.7672 WHERE id=1")
sql("UPDATE incident_reports SET status='pending',latitude=16.6273,longitude=120.7673 WHERE id=2")
sql("UPDATE evacuation_centers SET status='open',latitude=16.6272,longitude=120.7672 WHERE id=1")
sql("INSERT INTO relief_locations(name,location_type,address_details,latitude,longitude) VALUES ('DEMO ONLY distribution','distribution','Fictional point',16.6272,120.7672),('DEMO ONLY storage','storage','Fictional point',16.6272,120.7672)")

for client, role in [(admin, 'admin'), (resident, 'resident')]:
    data = map_data(client)
    check('households' not in data, role + ' map has no household layer')
    check(any(int(row['id']) == 1 for row in data['incidents']), role + ' sees verified incident automatically')
    check(not any(int(row['id']) == 2 for row in data['incidents']), role + ' does not see pending incident')
    check(all(set(row) == {'id', 'incident_type', 'latitude', 'longitude', 'status', 'report_date'} for row in data['incidents']), role + ' incident fields are public-safe')
    check(any(int(row['id']) == 1 for row in data['centers']), role + ' sees open center automatically')
    check(len(data['relief_locations']) == 1 and data['relief_locations'][0]['location_type'] == 'distribution', role + ' sees distribution point but not storage')

sql("UPDATE incident_reports SET status='rejected' WHERE id=1")
sql("UPDATE evacuation_centers SET status='closed' WHERE id=1")
sql("UPDATE relief_locations SET location_type='storage' WHERE location_type='distribution'")
for client, role in [(admin, 'admin'), (resident, 'resident')]:
    data = map_data(client)
    check(not any(int(row['id']) == 1 for row in data['incidents']), role + ' rejected incident disappears')
    check(not any(int(row['id']) == 1 for row in data['centers']), role + ' closed center disappears')
    check(not data['relief_locations'], role + ' storage point disappears')

print('TOTAL', len(passes))
