"""Fictional HTTP save/reload checks in a temporary site and a disposable MySQL database.
Requires local MAMP MySQL and PHP; never reads the application's private config.
"""
from pathlib import Path
import http.cookiejar
import json
import re
import os
import shutil
import socket
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request
import uuid

ROOT = Path(__file__).resolve().parents[1]
PHP = os.environ.get('TEST_PHP', '/Applications/MAMP/bin/php/php8.3.30/bin/php')
MYSQL_SOCKET = os.environ.get('TEST_MYSQL_SOCKET', '/Applications/MAMP/tmp/mysql/mysql.sock')
DB_NAME = 'bayanready_address_test_' + uuid.uuid4().hex[:10]

with tempfile.TemporaryDirectory(prefix='bayanready-address-') as temporary:
    folder = Path(temporary)
    site = folder / 'site'
    shutil.copytree(ROOT, site, ignore=shutil.ignore_patterns('.git', 'config.php', 'node_modules'))
    helper = folder / 'database.php'
    helper.write_text('''<?php
    $pdo = new PDO('mysql:unix_socket=' . getenv('TEST_MYSQL_SOCKET') . ';charset=utf8mb4', 'root', 'root', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $sql = stream_get_contents(STDIN);
    if (str_starts_with($sql, 'USE `')) {
        $end = strpos($sql, ';');
        $pdo->exec(substr($sql, 0, $end));
        $sql = substr($sql, $end + 1);
    }
    if (str_starts_with(trim($sql), 'SELECT')) {
        echo json_encode($pdo->query($sql)->fetchAll());
    } else {
        $pdo->exec($sql);
        echo '[]';
    }
    ''')

    def sql(query):
        output = subprocess.check_output([PHP, str(helper)], input=query.encode(), env=dict(os.environ, TEST_MYSQL_SOCKET=MYSQL_SOCKET))
        return json.loads(output)

    def query(query):
        return sql('USE `' + DB_NAME + '`; ' + query)

    sql('CREATE DATABASE `' + DB_NAME + '` CHARACTER SET utf8mb4')
    server = None
    try:
        schema = (ROOT / 'barangay_drrm.sql').read_text()
        schema = schema.replace('CREATE DATABASE IF NOT EXISTS barangay_drrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;', '')
        schema = schema.replace('USE barangay_drrm;', 'USE `' + DB_NAME + '`;')
        sql(schema)
        password_hash = subprocess.check_output([PHP, '-r', "echo password_hash('DemoPass123!', PASSWORD_DEFAULT);"]).decode()
        query("INSERT INTO users(full_name,username,email,password,role) VALUES " +
              ','.join("('Fictional " + role + "','" + role + "','" + role + "@example.invalid','" + password_hash + "','" + role + "')" for role in ['admin', 'resident']))
        query("UPDATE users SET phone='00000000001' WHERE role='resident'")
        config = (ROOT / 'setup/config.example.php').read_text().replace("'base_url' => '/WEBSYS_PROJECT'", "'base_url' => ''")
        config = config.replace("'host' => '127.0.0.1'", "'host' => 'localhost'").replace("'database' => 'barangay_drrm'", "'database' => '" + DB_NAME + "'").replace("'password' => ''", "'password' => 'root'")
        config = config.replace("'incident_upload_dir' => ''", "'incident_upload_dir' => '" + str(folder / 'images') + "'")
        (site / 'config/config.php').write_text(config)
        with socket.socket() as listener:
            listener.bind(('127.0.0.1', 0))
            port = listener.getsockname()[1]
        log = open(folder / 'server.log', 'w')
        server = subprocess.Popen([PHP, '-d', 'pdo_mysql.default_socket=' + MYSQL_SOCKET, '-S', '127.0.0.1:' + str(port), '-t', str(site)], stdout=log, stderr=log)
        base = 'http://127.0.0.1:' + str(port)

        class Client:
            def __init__(self):
                self.client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
            def request(self, path, data=None):
                try:
                    result = self.client.open(base + path, urllib.parse.urlencode(data).encode() if data is not None else None)
                except urllib.error.HTTPError as error:
                    result = error
                self.last_url = result.geturl()
                return result.code, result.read().decode()

        admin, resident = Client(), Client()
        for attempt in range(30):
            try:
                assert admin.request('/index.php')[0] == 200
                break
            except urllib.error.URLError:
                time.sleep(0.1)
        for client, role in [(admin, 'admin'), (resident, 'resident')]:
            code, body = client.request('/index.php', {'action': 'login', 'email': role + '@example.invalid', 'password': 'DemoPass123!'})
            assert code == 200 and 'Log out' in body, body[:300]
        cases = [
            (resident, 'incidents/create.php', 'incident_reports', 'location_description', 'incidents/view.php', dict(action='create_incident', incident_type='other', description='Fictional exercise', reporter_name='Fictional person', contact_phone='0000000000')),
            (admin, 'households/index.php', 'households', 'address_details', 'households/details.php', dict(action='create_household', barangay='Paoay', household_code='DEMO-ADDR', representative_name='Fictional person', contact_phone='', purok='Demo', registry_reason='monitored', hazard_type='unassessed', hazard_notes='', vulnerability_category='unassessed', vulnerability_notes='', evacuation_status='at_home')),
            (admin, 'evacuation/centers.php', 'evacuation_centers', 'address_details', 'evacuation/center.php', dict(action='save_center', name='Fictional center', capacity='5', status='open')),
            (admin, 'relief-inventory/locations.php', 'relief_locations', 'address_details', 'relief-inventory/location.php', dict(action='save_location', name='Fictional relief point', location_type='distribution')),
        ]
        prefix = '/'
        for client, page, table, field, detail, values in cases:
            page_url = '/households/add.php' if table == 'households' else prefix + page
            code, body = client.request(page_url)
            assert code == 200 and 'data-address-status' in body and 'location-picker.js' in body
            if table == 'incident_reports':
                assert 'value="Fictional resident"' in body and 'value="00000000001"' in body
            values.update({field: 'Fictional suggested road, Demo locality', 'latitude': '16.6272000', 'longitude': '120.7672000'})
            outside = dict(values, latitude='16.4', longitude='120.6')
            before = query('SELECT COUNT(*) AS total FROM ' + table)
            assert 'inside the approximate Atok' in client.request(page_url, outside)[1]
            assert before == query('SELECT COUNT(*) AS total FROM ' + table)
            code, body = client.request(page_url, values)
            assert code == 200 and 'alert-danger' not in body, body[-1500:]
            # The SQL helper executes one query at a time after USE through exec below.
            row = query('SELECT * FROM ' + table + ' ORDER BY id DESC LIMIT 1')[0]
            if table == 'incident_reports':
                assert row['reporter_name'] == values['reporter_name'] and row['contact_phone'] == values['contact_phone']
                profile = query("SELECT id,full_name,phone FROM users WHERE role='resident'")[0]
                assert row['reported_by'] == profile['id'] and profile['phone'] == '00000000001' and profile['full_name'] == 'Fictional resident'
            assert row[field] == values[field] and row['latitude'] == values['latitude'] and row['longitude'] == values['longitude'], row
            code, body = client.request(prefix + detail + '?id=' + str(row['id']))
            assert code == 200 and values[field] in body and values['latitude'] in body
            edit = prefix + (detail + '?id=' + str(row['id']) if table == 'incident_reports' else page + '?edit=' + str(row['id']))
            if table == 'households':
                edit = '/households/edit.php?id=' + str(row['id'])
            code, body = admin.request(edit)
            assert code == 200 and values[field] in body and 'data-address-status' in body
            updated = dict(values)
            updated.update({field: 'Fictional manually confirmed landmark', 'latitude': '16.5498834', 'longitude': '120.6533397'})
            if table == 'incident_reports':
                updated.update(action='update_location', location_notes='Fictional correction')
            elif table == 'households':
                updated['action'] = 'update_household'
            code, body = admin.request(edit, updated)
            assert code == 200 and 'alert-danger' not in body, body[-1500:]
            row2 = query('SELECT * FROM ' + table + ' WHERE id=' + str(row['id']))[0]
            assert row2[field] == updated[field] and row2['latitude'] == updated['latitude'] and row2['longitude'] == updated['longitude'], row2
            code, body = admin.request(edit)
            assert code == 200 and updated[field] in body and updated['latitude'] in body
            print('PASS create, edit, confirmed text/point and reload:', table)
        # Actual household POST redirects must be followed by fresh dashboard/list totals.
        query("INSERT INTO evacuation_centers(name,address_details,capacity,status,archived_at) VALUES ('Fictional no-point center','Demo',7,'open',NULL),('Fictional closed','Demo',50,'closed',NULL),('Fictional archived','Demo',100,'open',NOW())")
        for number in range(5):
            query("INSERT INTO household_members(household_id,full_name) VALUES (1,'Fictional Member " + str(number) + "')")
        from html.parser import HTMLParser

        class Cards(HTMLParser):
            def __init__(self):
                super().__init__()
                self.cards = {}
                self.parts = None
            def handle_starttag(self, tag, attributes):
                attributes = dict(attributes)
                if tag == 'a' and attributes.get('class') == 'stat-card':
                    self.parts = []
                    self.path = attributes['href']
            def handle_data(self, data):
                if self.parts is not None and data.strip():
                    self.parts.append(data.strip())
            def handle_endtag(self, tag):
                if tag == 'a' and self.parts is not None:
                    self.cards[self.parts[1]] = (int(self.parts[0]), self.path)
                    self.parts = None

        def verify_dashboard(available, occupants):
            code, body = admin.request(prefix + 'dashboard/staff.php')
            cards = Cards()
            cards.feed(body)
            assert code == 200 and cards.cards['Available capacity (people)'][0] == available
            assert cards.cards['People currently evacuated'][0] == occupants
            assert cards.cards['Open centers'][0] == 2
            for label in ['Available capacity (people)', 'People currently evacuated', 'Open centers']:
                assert admin.request(cards.cards[label][1])[0] == 200
            code, body = admin.request(prefix + 'evacuation/center.php?id=1')
            assert 'Available capacity (people): <strong>' + str(5 - occupants) + '</strong>' in body
            print('PASS HTTP dashboard/detail totals and card links:', available, occupants)

        verify_dashboard(12, 0)
        checkin = [('action', 'checkin_household'), ('household_id', '1')]
        checkin += [('member_ids[]', str(member)) for member in range(1, 6)]
        code, body = admin.request(prefix + 'evacuation/center.php?id=1', checkin)
        assert code == 200 and 'alert-danger' not in body
        verify_dashboard(7, 5)
        code, body = admin.request(prefix + 'evacuation/center.php?id=1', {'action': 'checkout', 'record_id': '1'})
        assert code == 200 and 'alert-danger' not in body
        verify_dashboard(8, 4)
        assert resident.request(prefix + 'households/index.php')[0] == 403
        print('PASS resident blocked from staff forms')
        from simple_actions import check_actions
        check_actions(admin, resident, query, prefix)
        from integrated_acceptance import check_acceptance
        check_acceptance(admin, resident, Client, query, prefix)
        from incident_upload import check_upload
        check_upload(resident, admin, Client, query, base, prefix)
        from response_history import check_response_history
        check_response_history(admin, resident, Client, query, prefix)
        # Exercise atomic distribution with an insufficient second item.
        ids = []
        for name in ['Fictional Water', 'Fictional Rice']:
            admin.request('/relief-inventory/index.php', dict(action='save_item', item_name=name, unit='packs'))
            item = query('SELECT MAX(id) id FROM relief_items')[0]['id']
            ids.append(item)
            admin.request('/relief-inventory/movements.php', dict(action='stock', item_id=item,
                          movement_type='stock_in', quantity='5', notes='Fictional delivery'))
        def relief_state():
            return [query(sql) for sql in (
                'SELECT id,quantity_on_hand FROM relief_items ORDER BY id',
                'SELECT * FROM relief_distributions ORDER BY id',
                'SELECT * FROM relief_distribution_items ORDER BY id',
                'SELECT * FROM relief_stock_movements ORDER BY id')]
        goods = {f'goods[{i}]':'2' for i in ids}
        data = dict(action='distribute', household_id='1', received_by='Demo Receiver', **goods)
        before = relief_state()
        failed = dict(data)
        failed[f'goods[{ids[1]}]'] = '6'
        assert 'alert-danger' in admin.request('/relief-distribution/index.php', failed)[1]
        assert relief_state() == before
        assert 'alert-danger' not in admin.request('/relief-distribution/index.php', data)[1]
        after = relief_state()
        assert [row['quantity_on_hand'] for row in after[0][-2:]] == [3, 3]
        assert [len(a)-len(b) for a,b in zip(after[1:],before[1:])] == [1,2,2]
        report = admin.request('/disaster-reports/index.php?from=2000-01-01&to=2099-12-31')[1]
        assert '2 distribution events to 1 distinct households' in report
        for name in ['Fictional Water', 'Fictional Rice']:
            assert name in report
        assert '0 distribution events to 0 distinct households' in admin.request('/disaster-reports/index.php?from=2000-01-01&to=2000-01-02')[1]
        assert resident.request('/disaster-reports/index.php')[0] == 403
        query("UPDATE evacuation_centers SET barangay='Paoay' WHERE id=1")
        summary_path = '/disaster-reports/index.php?from=2000-01-01&to=2099-12-31&barangay='
        scoped = admin.request(summary_path + 'Paoay')[1]
        assert '2 distribution events to 1 distinct households' in scoped
        assert 'Center arrivals: <strong>6</strong> · Checkouts: <strong>1</strong>' in scoped
        assert '0 distribution events' in admin.request(summary_path + 'Topdac')[1]
        quiet = admin.request('/disaster-reports/index.php?from=2000-01-01&to=2000-01-01&barangay=Paoay')[1]
        assert 'Center arrivals: <strong>0</strong>' in quiet and '<td>5</td>' in quiet
        assert admin.request(summary_path + 'invalid')[0] == 400
        print_path = '/incidents/print.php?id=1'
        other = Client()
        other.request('/index.php', dict(action='login',email='other@example.invalid',password='DemoPass123!'))
        assert other.request(print_path)[0] == 404
        public = Client()
        public.request(print_path)
        assert public.last_url.endswith('/index.php')
        own = resident.request(print_path)[1]
        staff = admin.request(print_path)[1]
        assert 'Resident submission copy' in own and 'Staff review and response history' not in own
        assert 'Fictional arrival' in staff and 'Fictional arrival' not in own
        print('PASS scoped print totals, current occupancy, period arrivals, and incident copy permissions')
        if os.environ.get('TEST_PRINT_BROWSER'):
            for name, html in [('summary', scoped), ('resident', own), ('staff', staff)]:
                snapshot = folder / (name + '.html')
                snapshot.write_text(html.replace('<head>', '<head><base href="' + base + '/">'))
                pdf = folder / (name + '.pdf')
                subprocess.run([os.environ['TEST_PRINT_BROWSER'], '--headless', '--disable-gpu',
                                '--user-data-dir=' + str(folder / 'browser'), '--no-pdf-header-footer',
                                '--print-to-pdf=' + str(pdf), snapshot.as_uri()], check=True,
                               stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, timeout=40)
                info = subprocess.check_output(['pdfinfo', str(pdf)]).decode()
                assert 'A4' in info, info
                printed = subprocess.check_output(['pdftotext', '-layout', str(pdf), '-']).decode()
                assert 'Save as PDF' not in printed and 'Back to dashboard' not in printed
                if name == 'summary':
                    assert 'Activity during the selected period' in printed.split('\f')[1]
                    assert '2 distribution events' in printed
                else:
                    assert 'Incident report #1' in printed and ('Fictional arrival' in printed) == (name == 'staff')
            print('PASS browser A4 PDFs, summary page break, printed totals and staff-only history')
        print('PASS multi-good distribution, four-table rollback and filtered disaster summary')

        title = 'Fictional visibility exercise'
        notice = dict(title=title, message='Fictional notice', action='save_draft')
        admin.request('/announcements/index.php', notice)
        notice_id = query('SELECT MAX(id) id FROM announcements')[0]['id']
        edit = '/announcements/edit.php?id=' + str(notice_id)
        def check_notice(visible):
            for page in ['/announcements/published.php', '/dashboard/resident.php']:
                assert (title in resident.request(page)[1]) == visible
        check_notice(False)
        admin.request(edit, dict(notice, action='publish'))
        check_notice(True)
        admin.request(edit, dict(notice, confirm_withdraw='1'))
        check_notice(False)
        print('PASS draft, publication and withdrawal on resident list and dashboard')
        center_url = '/evacuation/center.php?id=2'
        assert 'name="walkin_id"' not in admin.request(center_url)[1]
        center_url += '&walkin_search=Fictional+Walk-in'
        assert 'No matching walk-ins' in admin.request(center_url)[1]
        code, body = admin.request(center_url, dict(action='checkin', kind='new_walkin',
            full_name='Fictional Walk-in', age='25', address_details='Demo Road', contact_phone='09000000000'))
        assert code == 200 and 'alert-danger' not in body
        stay, = query('SELECT id, walkin_id FROM evacuation_records WHERE center_id=2 AND checked_out_at IS NULL')
        admin.request(center_url, dict(action='checkout', record_id=stay['id']))
        assert 'Choose returning person' in admin.request(center_url)[1]
        admin.request(center_url, dict(action='checkin', kind='walkin', walkin_id=stay['walkin_id']))
        rows = query('SELECT checked_out_at FROM evacuation_records WHERE center_id=2 ORDER BY id')
        assert len(rows) == 2 and rows[0]['checked_out_at'] and rows[1]['checked_out_at'] is None
        print('PASS direct walk-in arrival, checkout, return and retained stay history')
        detail = '/households/details.php?id=1'
        assert resident.request(detail)[0] == 403
        assert '<h2>Login</h2>' in Client().request('/households/add.php')[1]
        assert 'DEMO-ADDR' in admin.request('/households/index.php?q=DEMO-ADDR')[1]
        assert 'DEMO-ADDR' not in admin.request('/households/index.php?q=absent')[1]
        data = dict(action='create_member', full_name='Fictional Arrival', age='24', notes='')
        assert 'Fictional Arrival' in admin.request(detail, data)[1]
        member = query("SELECT id FROM household_members WHERE full_name='Fictional Arrival'")[0]['id']
        data.update(action='update_member', full_name='Fictional Updated')
        assert 'Fictional Updated' in admin.request(detail + '&member=' + str(member), data)[1]
        endpoint = '/households/account.php?member=' + str(member)
        assert 'id="enrollment-help"' in admin.request(detail)[1]
        assert 'disabled aria-describedby="enrollment-help">Create resident account' in admin.request(detail)[1]
        credentials = dict(confirmed='yes', email='new@example.invalid')
        assert admin.request(endpoint, credentials)[0] == 403
        query("UPDATE users SET assigned_barangay='Topdac' WHERE role='admin'")
        assert admin.request(endpoint, credentials)[0] == 403
        query("UPDATE users SET assigned_barangay='Paoay' WHERE role='admin'")
        assert 'href="' + endpoint + '"' in admin.request(detail)[1]
        assert 'Resident email for login' in admin.request(endpoint)[1]
        assert resident.request(endpoint, credentials)[0] == 403
        assert 'Confirm residency' in admin.request(endpoint, dict(credentials, confirmed=''))[1]
        guest = Client()
        assert 'Invalid form action' in guest.request('/index.php', dict(action='register', **credentials))[1]
        temporary = re.search(r'<strong>([a-f0-9]{20})</strong>', admin.request(endpoint, credentials)[1]).group(1)
        enrolled_page = admin.request(detail)[1]
        assert 'Resident account already created' in enrolled_page and 'href="' + endpoint + '"' not in enrolled_page
        assert 'already has an account' in admin.request(endpoint, credentials)[1]
        login = dict(action='login', email=credentials['email'], password=temporary)
        assert 'Choose your password' in guest.request('/index.php', login)[1]
        assert 'Choose your password' in guest.request('/incidents/create.php', dict(action='create_incident'))[1]
        for path in ['/map/data.php', '/map/address.php']:
            assert guest.request(path)[0] == 403
        new = 'FictionalNewPass!'
        assert guest.request('/change-password.php', dict(temporary=temporary, password=new, confirmation=new))[0] == 200
        assert guest.last_url.endswith('/dashboard/resident.php')
        assert 'Invalid email or password' in Client().request('/index.php', login)[1]
        assert 'Log out' in Client().request('/index.php', dict(login, password=new))[1]
        history = "SELECT e.id FROM evacuation_records e JOIN household_members m ON m.id=e.household_member_id WHERE m.household_id=1 ORDER BY e.id"
        distributions = "SELECT id FROM relief_distributions WHERE household_id=1 ORDER BY id"
        before = query(history), query(distributions)
        assert all(before)
        edit_values = dict(cases[1][-1], action='update_household', representative_name='Fictional New Representative')
        assert 'Fictional New Representative' in admin.request('/households/edit.php?id=1', edit_values)[1]
        assert before == (query(history), query(distributions))
        print('PASS scoped enrollment, first-login guards, password replacement and representative history')
        for action, expected in [('verify_only', 'verified'), ('reject', 'rejected')]:
            resident.request('/incidents/create.php', cases[0][-1])
            incident = query('SELECT MAX(id) id FROM incident_reports')[0]['id']
            url = '/incidents/view.php?id=' + str(incident)
            assert 'alert-danger' not in admin.request(url, dict(action=action, notes='Fictional review'))[1]
            assert query('SELECT status FROM incident_reports WHERE id=' + str(incident))[0]['status'] == expected
            assert not query('SELECT id FROM incident_assignments WHERE incident_id=' + str(incident))
        query("UPDATE incident_reports SET barangay='Topdac', incident_type='flood' WHERE id=1")
        for area, period, expected in [('Topdac', 'today', 1), ('Paoay', 'today', 0), ('Topdac', 'month', 1)]:
            cards = Cards()
            cards.feed(admin.request('/dashboard/staff.php?barangay=' + area + '&period=' + period)[1])
            count, path = cards.cards['Flooding reports']
            assert count == expected
            assert admin.request(path)[1].count('href="/incidents/view.php?id=') == expected
        query("UPDATE incident_reports SET created_at=CURRENT_DATE - INTERVAL 8 DAY WHERE id=1")
        for period, expected in [('week', 0), ('month', 1)]:
            cards = Cards()
            cards.feed(admin.request('/dashboard/staff.php?barangay=Topdac&period=' + period)[1])
            count, path = cards.cards['Flooding reports']
            assert count == expected and admin.request(path)[1].count('href="/incidents/view.php?id=') == expected
        print('PASS incident type, barangay and period counts match linked lists')
        print('PASS editable contact defaults, atomic dispatch, verification only and rejection')
        assert '<h2>Login</h2>' in admin.request('/logout.php', {'action':'logout'})[1]
        assert '<h2>Login</h2>' in admin.request(detail)[1]
        print('PASS direct household search, member create/edit, role guards and logout')
        if os.environ.get('TEST_BROWSER_PAUSE') == '1':
            Path('/tmp/bayanready-actions-preview.txt').write_text(base)
            print('Fictional browser preview:', base, flush=True)
            input('Press Enter after browser review to remove the temporary site and database. ')

    finally:
        if server:
            server.terminate()
            server.wait(timeout=5)
            log.close()
        sql('DROP DATABASE `' + DB_NAME + '`')
