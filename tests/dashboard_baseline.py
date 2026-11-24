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

    for _ in range(4):
        query("""INSERT INTO incident_reports(reported_by, incident_type, description, status)
                 VALUES (2,'other','Demo queue','pending')""")
    queue_ids = lambda body: re.findall(r'aria-label="Review incident #(\d+)"', body)
    body = staff.request(page + '?barangay=Topdac')[1]
    assert queue_ids(body) == ['1', '4', '6', '7', '8']
    assert 'Demo closed shelter' not in body
    for path in ['households/index.php', 'incidents/list.php', 'response/teams.php', 'evacuation/centers.php',
                 'relief-inventory/index.php', 'relief-distribution/index.php', 'announcements/index.php', 'disaster-reports/index.php']:
        assert 'href="/' + path + '"' in body and staff.request('/' + path)[0] == 200
    for record in [1, 4, 6, 7, 8, 9]:
        staff.request('/incidents/view.php?id=' + str(record), dict(action='verify_only', notes='Fictional verification'))
        if record == 1:
            assert queue_ids(staff.request(page)[1]) == ['4', '6', '7', '8', '9']
    assert 'No reports awaiting review' in staff.request(page)[1]

    center = '/evacuation/center.php?id=1'
    staff.request(center, dict(action='checkout', record_id=1))
    for name in ['Demo first arrival', 'Demo second arrival']:
        status, body = staff.request(center, dict(action='checkin', kind='new_walkin', full_name=name,
            age='20', address_details='Fictional landmark', contact_phone='00000000000'))
        assert status == 200 and 'alert-danger' not in body
    def check_capacity(staying, remaining):
        body = staff.request(page)[1]
        row = re.search(r'Demo open shelter</a></td>(.*?)</tr>', body, re.S).group(1)
        assert re.findall(r'<td>(\d+)</td>', row) == [str(staying), '5', str(remaining)]
        detail = staff.request(center)[1]
        assert f'Occupancy: {staying} / 5 people' in detail
        assert f'Available capacity (people): <strong>{remaining}</strong>' in detail
    check_capacity(2, 3)
    staff.request(center, dict(action='checkout', record_id=2))
    check_capacity(1, 4)
    print('PASS oldest five, verification refresh, eight module shortcuts and center occupancy parity')
