"""Fictional HTTP save/reload checks in a temporary site and a disposable MySQL database.
Requires local MAMP MySQL and PHP; never reads the application's private config.
"""
from pathlib import Path
import http.cookiejar
import json
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
DB_NAME = 'bayanready_address_test_' + uuid.uuid4().hex[:10]

with tempfile.TemporaryDirectory(prefix='bayanready-address-') as temporary:
    folder = Path(temporary)
    site = folder / 'site'
    shutil.copytree(ROOT, site, ignore=shutil.ignore_patterns('.git', 'config.php', 'node_modules'))
    helper = folder / 'database.php'
    helper.write_text('''<?php
    $pdo = new PDO('mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;charset=utf8mb4', 'root', 'root', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
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
        output = subprocess.check_output([PHP, str(helper)], input=query.encode())
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
        config = (ROOT / 'config/config.example.php').read_text().replace("const BASE_URL = '/WEBSYS_PROJECT'", "const BASE_URL = ''")
        config = config.replace("const DB_HOST = '127.0.0.1'", "const DB_HOST = 'localhost'").replace("const DB_NAME = 'barangay_drrm'", "const DB_NAME = '" + DB_NAME + "'").replace("const DB_PASSWORD = ''", "const DB_PASSWORD = 'root'")
        config = config.replace("const INCIDENT_UPLOAD_DIR = ''", "const INCIDENT_UPLOAD_DIR = '" + str(folder / 'images') + "'")
        (site / 'config/config.php').write_text(config)
        with socket.socket() as listener:
            listener.bind(('127.0.0.1', 0))
            port = listener.getsockname()[1]
        log = open(folder / 'server.log', 'w')
        server = subprocess.Popen([PHP, '-d', 'pdo_mysql.default_socket=/Applications/MAMP/tmp/mysql/mysql.sock', '-S', '127.0.0.1:' + str(port), '-t', str(site)], stdout=log, stderr=log)
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
            (admin, 'admin/households.php', 'households', 'address_details', 'admin/household.php', dict(action='create_household', barangay='Abiang', contact_phone='', purok='Demo', registry_reason='monitored', hazard_type='unassessed', hazard_notes='', vulnerability_category='unassessed', vulnerability_notes='', evacuation_status='at_home')),
            (admin, 'admin/evacuation/centers.php', 'evacuation_centers', 'address_details', 'admin/evacuation/center.php', dict(action='save_center', barangay='Abiang', name='Fictional center', capacity='5', status='open')),
            (admin, 'admin/relief/locations.php', 'relief_locations', 'address_details', 'admin/relief/location.php', dict(action='save_location', barangay='Abiang', name='Fictional relief point', location_type='distribution')),
        ]
        prefix = '/resources/views/'
        for client, page, table, field, detail, values in cases:
            code, body = client.request(prefix + page)
            assert code == 200 and 'data-address-status' in body and 'location-picker.js' in body
            values.update({field: 'Fictional suggested road, Demo locality', 'latitude': '16.6272000', 'longitude': '120.7672000'})
            code, body = client.request(prefix + page, values)
            assert code == 200 and 'alert-danger' not in body, body[-1500:]
            # The SQL helper executes one query at a time after USE through exec below.
            row = query('SELECT * FROM ' + table + ' ORDER BY id DESC LIMIT 1')[0]
            assert row['barangay'] == values.get('barangay')
            assert row[field] == values[field] and row['latitude'] == values['latitude'] and row['longitude'] == values['longitude'], row
            code, body = client.request(prefix + detail + '?id=' + str(row['id']))
            assert code == 200 and values[field] in body and values['latitude'] in body
            edit = prefix + (detail + '?id=' + str(row['id']) if table == 'incident_reports' else page + '?edit=' + str(row['id']))
            code, body = admin.request(edit)
            assert code == 200 and values[field] in body and 'data-address-status' in body
            updated = dict(values, barangay='Topdac')
            updated.update({field: 'Fictional manually confirmed landmark', 'latitude': '16.6280000', 'longitude': '120.7680000'})
            if table == 'incident_reports':
                updated.update(action='update_location', location_notes='Fictional correction')
            elif table == 'households':
                updated['action'] = 'update_household'
            code, body = admin.request(edit, updated)
            assert code == 200 and 'alert-danger' not in body, body[-1500:]
            row2 = query('SELECT * FROM ' + table + ' WHERE id=' + str(row['id']))[0]
            assert row2['barangay'] == 'Topdac'
            listing = 'incidents/list.php' if table == 'incident_reports' else page
            marker = {'households': 'Representative not selected — Topdac', 'incident_reports': 'Fictional exercise', 'evacuation_centers': 'Fictional center', 'relief_locations': 'Fictional relief point'}[table]
            if table == 'incident_reports':
                marker = updated[field]
            assert marker in admin.request(prefix + listing + '?barangay=Topdac')[1]
            assert marker not in admin.request(prefix + listing + '?barangay=Abiang')[1]
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
            code, body = admin.request(prefix + 'admin/admin.index.php')
            cards = Cards()
            cards.feed(body)
            assert code == 200 and cards.cards['Available capacity (people)'][0] == available
            assert cards.cards['Active evacuees'][0] == occupants
            assert cards.cards['Open centers'][0] == 2
            for label in ['Available capacity (people)', 'Active evacuees', 'Open centers']:
                assert admin.request(cards.cards[label][1])[0] == 200
            code, body = admin.request(prefix + 'admin/evacuation/center.php?id=1')
            assert 'Available capacity (people): <strong>' + str(5 - occupants) + '</strong>' in body
            for barangay, places, people, centers in [('Topdac', 5 - occupants, occupants, 1), ('Abiang', 0, 0, 0)]:
                filtered = Cards()
                filtered.feed(admin.request(prefix + 'admin/admin.index.php?barangay=' + barangay)[1])
                for label, total in [('Available capacity (people)', places), ('Active evacuees', people), ('Open centers', centers)]:
                    value, link = filtered.cards[label]
                    assert value == total and 'barangay=' + barangay in link
                    body = admin.request(link)[1]
                    assert (str(people) + ' records.' if label == 'Active evacuees' else str(centers) + ' centers.') in body
                assert filtered.cards['Municipal inventory items'] == cards.cards['Municipal inventory items']
            print('PASS HTTP dashboard/detail totals and card links:', available, occupants)

        verify_dashboard(12, 0)
        checkin = [('action', 'checkin_household'), ('household_id', '1')]
        checkin += [('member_ids[]', str(member)) for member in range(1, 6)]
        code, body = admin.request(prefix + 'admin/evacuation/center.php?id=1', checkin)
        assert code == 200 and 'alert-danger' not in body
        verify_dashboard(7, 5)
        code, body = admin.request(prefix + 'admin/evacuation/center.php?id=1', {'action': 'checkout', 'record_id': '1'})
        assert code == 200 and 'alert-danger' not in body
        verify_dashboard(8, 4)
        assert resident.request(prefix + 'admin/households.php')[0] == 403
        print('PASS resident blocked from staff forms')
        from simple_actions import check_actions
        check_actions(admin, resident, query, prefix)
        from integrated_acceptance import check_acceptance
        check_acceptance(admin, resident, Client, query, prefix)
        # Replace the contact on a household that already has stays and relief history.
        detail = prefix + 'admin/household.php?id=1'
        history = 'SELECT id,household_id,received_by FROM relief_distributions ORDER BY id'
        stays = 'SELECT id,household_member_id FROM evacuation_records ORDER BY id'
        original = (query(history), query(stays))
        members = query('SELECT id,full_name FROM household_members WHERE household_id=1 AND archived_at IS NULL ORDER BY id')
        for member in members[:2]:
            code, body = admin.request(detail, dict(action='select_representative', member_id=member['id']))
            assert code == 200 and query('SELECT display_name FROM household_directory WHERE id=1')[0]['display_name'] == member['full_name']
            code, body = admin.request(prefix+'admin/records.php?type=members', [('action','delete'),('ids[]','members:'+str(member['id']))])
            assert 'replacement representative' in body
        assert len(members) >= 2 and original == (query(history), query(stays))
        from incident_upload import check_upload
        check_upload(resident, admin, Client, query, base, prefix)
        from response_history import check_response_history
        check_response_history(admin, resident, Client, query, prefix)
        from simple_actions import Controls
        def contact_fields(client, page, values=None):
            code, body = client.request(prefix + page, values)
            assert code == 200
            controls = Controls()
            controls.feed(body)
            return [controls.inputs[k] for k in ('reporter_name', 'reporter_email', 'contact_phone')]
        form = 'incidents/create.php'
        assisted = form + '?intake=assisted'
        assert contact_fields(resident, form) == ['Fictional resident', 'resident@example.invalid', '']
        query("UPDATE users SET phone='0001112222' WHERE username='admin'")
        assert contact_fields(admin, form) == ['Fictional admin', 'admin@example.invalid', '0001112222']
        assert contact_fields(admin, assisted) == ['', '', '']
        values = dict(action='create_incident', incident_type='other', description='Fictional intake',
                      location_description='Fictional hall', reporter_name='Fictional Caller',
                      contact_phone='0003334444', reporter_email='invalid', report_source='phone',
                      reported_by='999999')
        assert contact_fields(admin, assisted, values) == ['Fictional Caller', 'invalid', '0003334444']
        values['reporter_email'] = 'caller@example.invalid'
        for client, page, owner, source in [(admin, assisted, 'admin', 'phone'), (resident, assisted, 'resident', 'resident')]:
            code, body = client.request(prefix + page, values)
            assert code == 200 and 'caller@example.invalid' in body
            row = query("SELECT r.*, u.username FROM incident_reports r JOIN users u ON u.id=r.reported_by ORDER BY r.id DESC LIMIT 1")[0]
            assert (row['username'], row['report_source']) == (owner, source)
            assert [row[k] for k in ('reporter_name','reporter_email','contact_phone')] == ['Fictional Caller','caller@example.invalid','0003334444']
            if owner == 'admin':
                assert resident.request(prefix + 'incidents/view.php?id=' + str(row['id']))[0] == 404
        print('PASS reporter prefilling, blank assisted intake, validation retries, contact snapshots and session ownership')
        summary_page = prefix + 'admin/summaries/disaster.php?from=2000-01-01&to=2000-01-02&barangay='
        for area in ['Topdac', 'Abiang']:
            code, page = admin.request(summary_page + area)
            assert code == 200 and 'Coverage: <strong>' + area in page
            assert ('Fictional center' in page) == (area == 'Topdac')
            assert '0 distribution events to 0 distinct households' in page
        assert resident.request(summary_page + 'Topdac')[0] == 403
        for client in [resident, admin]:
            code, page = client.request(prefix + 'incidents/view.php?id=1')
            assert code == 200 and 'Print incident report' in page and 'Asia/Manila' in page
            assert ('Staff review history' in page) == (client is admin)
        print('PASS print controls, current versus historical totals, barangay selection and role-specific content')
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
