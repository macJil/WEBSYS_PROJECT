"""Predictable before-change fixture for the disposable HTTP runner."""
import re


def check_baseline(query, staff, first, Client):
    query("""INSERT INTO users(full_name, username, email, password, role)
             SELECT 'Fictional Resident B', 'baseline_b', 'baseline-b@example.invalid', password, 'resident'
             FROM users WHERE username='resident'""")
    second = Client()
    second.request('/index.php', dict(action='login', email='baseline-b@example.invalid', password='DemoPass123!'))

    def cards(client, path):
        status, body = client.request(path)
        assert status == 200
        return {label: int(count) for count, label in re.findall(
            r'<strong class="stat-value">(\d+)</strong>\s*<span class="stat-label">([^<]+)</span>', body)}

    statuses = ['Pending', 'Verified', 'Responding', 'Resolved', 'Rejected']
    def resident_counts(client):
        totals = cards(client, '/dashboard/resident.php')
        return [totals[name + ' incidents'] for name in statuses]

    assert resident_counts(second) == [0, 0, 0, 0, 0]
    query("""INSERT INTO incident_reports(reported_by, barangay, incident_type, description, status, archived_at)
             VALUES (2,'Paoay','flood','Demo A1','pending',NULL),
                    (2,'Topdac','fire','Demo A2','resolved',NULL),
                    (3,'Topdac','landslide','Demo B1','verified',NULL),
                    (3,'Paoay','flood','Demo B2','pending',NULL),
                    (3,'Paoay','fire','Archived demo','resolved',NOW())""")
    query("""INSERT INTO evacuation_centers(name, barangay, status, capacity) VALUES
             ('Demo open shelter','Paoay','open',5), ('Demo closed shelter','Topdac','closed',9)""")
    query("""INSERT INTO evacuation_records(center_id, walkin_name, walkin_contact, recorded_by)
             VALUES (1,'Fictional arrival','00000000000',1)""")
    page = '/dashboard/staff.php'
    expected = {'Pending incidents': 2, 'Verified incidents': 1, 'Responding incidents': 0,
                'Open centers': 1, 'Available capacity (people)': 4, 'People currently evacuated': 1,
                'Affected households': 0, 'Flooding reports': 2, 'Fire reports': 1, 'Landslide reports': 1}
    totals = cards(staff, page)
    assert all(totals[name] == count for name, count in expected.items()), totals
    assert resident_counts(first) == [1, 0, 0, 1, 0]
    assert resident_counts(second) == [1, 1, 0, 0, 0]
    assert cards(staff, page + '?barangay=Topdac')['Flooding reports'] == 0
    assert cards(staff, page + '?barangay=Paoay')['Flooding reports'] == 2
    assert first.request('/incidents/view.php?id=4')[0] == 404
    assert second.request('/incidents/view.php?id=2')[0] == 404
    print('PASS dashboard baseline: two residents, ownership, archive exclusion, barangays, occupancy and capacity')
