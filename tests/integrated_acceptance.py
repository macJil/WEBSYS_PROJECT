"""Additional acceptance checks on the persistence runner's single fictional database."""
import json
from simple_actions import Controls


def check_acceptance(admin, resident, Client, query, prefix):
    anonymous = Client()
    staff_pages = ['admin/households.php', 'admin/response/teams.php', 'admin/evacuation/centers.php', 'admin/relief/inventory.php', 'admin/relief/distributions.php', 'admin/announcement/announcement.php', 'admin/summaries/disaster.php', 'admin/records.php']
    for page in staff_pages:
        for values in [None, {'action': 'delete', 'id': '1'}]:
            assert resident.request(prefix + page, values)[0] == 403, page
            anonymous.request(prefix + page, values)
            assert anonymous.last_url.endswith('/index.php'), (page, values, anonymous.last_url)
    assert anonymous.request(prefix + 'maps/data.php')[0] == 401
    print('PASS anonymous and resident direct GET/POST staff guards')

    query("INSERT INTO users(full_name,username,email,password,role) SELECT 'Other Fictional Resident','other','other@example.invalid',password,'resident' FROM users WHERE role='resident' LIMIT 1")
    other = Client()
    other.request('/index.php', dict(action='login', email='other@example.invalid', password='DemoPass123!'))
    assert other.request(prefix + 'incidents/view.php?id=1')[0] == 404
    assert resident.request(prefix + 'incidents/view.php?id=1')[0] == 200
    print('PASS private incident ownership')

    for status in ['pending', 'verified', 'responding', 'resolved', 'rejected']:
        query("UPDATE incident_reports SET status='" + status + "' WHERE id=1")
        for client in [admin, resident]:
            code, body = client.request(prefix + 'maps/data.php')
            data = json.loads(body)
            rows = [row for row in data['incidents'] if int(row['id']) == 1]
            assert bool(rows) == (status in ['verified', 'responding', 'resolved'])
            assert 'households' not in data
            for row in data['incidents']:
                assert set(row) == {'id','incident_type','latitude','longitude','status','report_date'}
    query("UPDATE incident_reports SET status='responding' WHERE id=1")
    query("UPDATE relief_locations SET location_type='storage' WHERE id=1")
    assert not json.loads(resident.request(prefix + 'maps/data.php')[1])['relief_locations']
    query("UPDATE relief_locations SET location_type='distribution' WHERE id=1")
    print('PASS automatic incident status visibility, safe field allowlist, no households, storage excluded')

    center = prefix + 'admin/evacuation/center.php?id=1'
    def occupants():
        return query('SELECT COUNT(*) AS n FROM evacuation_records WHERE center_id=1 AND checked_out_at IS NULL')[0]['n']
    before = occupants()
    duplicate = [('action','checkin_household'),('household_id','1'),('member_ids[]','1'),('member_ids[]','2')]
    assert 'alert-danger' in admin.request(center, duplicate)[1]
    assert occupants() == before
    partial = [('action','checkin_household'),('household_id','1'),('member_ids[]','1')]
    assert 'alert-danger' not in admin.request(center, partial)[1]
    assert occupants() == before + 1
    query("INSERT INTO household_members(household_id,full_name) VALUES(1,'Fictional Capacity Arrival')")
    member = query('SELECT MAX(id) AS id FROM household_members')[0]['id']
    full = [('action','checkin_household'),('household_id','1'),('member_ids[]',str(member))]
    assert 'alert-danger' in admin.request(center, full)[1]
    assert occupants() == before + 1
    print('PASS partial arrival, duplicate batch rollback and full-center rejection')

    stock = query('SELECT quantity_on_hand FROM relief_items WHERE id=1')[0]['quantity_on_hand']
    movements = query('SELECT COUNT(*) AS n FROM relief_stock_movements')[0]['n']
    code, body = admin.request(prefix + 'admin/relief/distributions.php', {'action':'distribute','household_id':'1','received_by':'Fictional Receiver','notes':'Blocked test','goods[1]':'999'})
    assert 'alert-danger' in body
    assert query('SELECT quantity_on_hand FROM relief_items WHERE id=1')[0]['quantity_on_hand'] == stock
    assert query('SELECT COUNT(*) AS n FROM relief_stock_movements')[0]['n'] == movements
    for kind, record in [('households',1), ('incidents',1), ('centers',1), ('goods',1)]:
        path = prefix + 'admin/records.php?type=' + kind
        code, body = admin.request(path, [('action','delete'),('ids[]',kind + ':' + str(record))])
        assert 'alert-danger' in body and 'No selected records were changed' in body, kind
    assert query('SELECT COUNT(*) AS n FROM response_updates')[0]['n'] >= 3
    assert query('SELECT COUNT(*) AS n FROM relief_distribution_items')[0]['n'] == 1
    assert query('SELECT COUNT(*) AS n FROM evacuation_records WHERE checked_out_at IS NOT NULL')[0]['n'] == 1
    print('PASS insufficient-stock rollback, active-record rejection and preserved operational history')
    query("UPDATE relief_items SET quantity_on_hand=0 WHERE id=1")
    history = query('SELECT COUNT(*) AS n FROM relief_stock_movements')[0]['n']
    code, body = admin.request(prefix + 'admin/records.php?type=goods', [('action','delete'),('ids[]','goods:1')])
    assert code == 200 and 'alert-danger' not in body
    assert query('SELECT archived_at FROM relief_items WHERE id=1')[0]['archived_at'] is not None
    assert query('SELECT COUNT(*) AS n FROM relief_stock_movements')[0]['n'] == history
    assert 'value="goods:1"' not in body
    print('PASS zero-stock good removed from active selection with stock/distribution history preserved')
