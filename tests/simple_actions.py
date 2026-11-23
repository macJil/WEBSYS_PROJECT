"""Exercise styled actions and return destinations inside the fictional HTTP fixture."""
from html.parser import HTMLParser
from urllib.parse import urljoin, urlparse


class Controls(HTMLParser):
    def __init__(self):
        super().__init__()
        self.links = []
        self.buttons = []
        self.inputs = {}
        self.current = None

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag in ['a', 'button']:
            self.current = (tag, attrs, [])
        if tag == 'input' and attrs.get('name'):
            self.inputs[attrs['name']] = attrs.get('value', '')

    def handle_data(self, data):
        if self.current:
            self.current[2].append(data)

    def handle_endtag(self, tag):
        if self.current and self.current[0] == tag:
            kind, attrs, text = self.current
            (self.links if kind == 'a' else self.buttons).append((attrs, ' '.join(''.join(text).split())))
            self.current = None


def check_actions(admin, resident, query, prefix):
    def save(path, values, destination):
        code, body = admin.request(prefix + path, values)
        assert code == 200 and 'alert-danger' not in body, body[-2000:]
        assert urlparse(admin.last_url).path + ('?' + urlparse(admin.last_url).query if urlparse(admin.last_url).query else '') == prefix + destination, admin.last_url
        return body

    save('response/teams.php', dict(action='save_team', team_name='Fictional Test Team', contact_phone='', is_active='1'), 'response/teams.php')
    save('response/teams.php?edit=1', dict(action='save_team', team_name='Fictional Edited Team', contact_phone='', is_active='1'), 'response/teams.php')
    save('relief-inventory/index.php', dict(action='save_item', item_name='Fictional Packs', unit='packs'), 'relief-inventory/index.php')
    save('relief-inventory/index.php?edit=1', dict(action='save_item', item_name='Fictional Food Packs', unit='packs'), 'relief-inventory/index.php')
    save('relief-inventory/movements.php', dict(action='stock', item_id='1', movement_type='stock_in', quantity='10', notes='Fictional stock'), 'relief-inventory/movements.php')
    save('relief-distribution/index.php', {'action':'distribute','household_id':'1','received_by':'Fictional Receiver','notes':'Demo','goods[1]':'2'}, 'relief-distribution/index.php?id=1')
    save('announcements/index.php', dict(action='save_draft', title='Fictional Notice', message='Fictional reminder'), 'announcements/index.php')
    save('announcements/edit.php?id=1', dict(action='publish', title='Fictional Notice', message='Fictional reminder'), 'announcements/index.php')
    values = dict(action='save_draft', title='Fictional Notice', message='Fictional reminder')
    code, body = admin.request(prefix + 'announcements/edit.php?id=1', values)
    assert 'Confirm withdrawal' in body and query('SELECT is_published FROM announcements WHERE id=1')[0]['is_published'] == 1
    values['confirm_withdraw'] = '1'
    save('announcements/edit.php?id=1', values, 'announcements/index.php')
    print('PASS team, goods, stock, distribution and announcement save redirects; withdrawal confirmation')

    page = 'records/index.php?type=teams'
    code, body = admin.request(prefix + page)
    assert 'Delete selected' in body and 'teams:1' in body
    save(page, [('action','delete'),('ids[]','teams:1')], page + '&q=&page=1')
    assert query('SELECT COUNT(*) AS total FROM responders')[0]['total'] == 0
    print('PASS direct checkbox deletion and filtered redirect')
    # Recreate the team for the navigation crawl and browser check.
    save('response/teams.php', dict(action='save_team', team_name='Fictional UI Team', contact_phone='', is_active='1'), 'response/teams.php')

    save('incidents/view.php?id=1', dict(action='review_incident', status='verified', notes='Fictional verification'), 'incidents/view.php?id=1')
    save('response/incident.php?id=1', dict(action='assign_team', team_id='2', notes='Fictional dispatch'), 'response/incident.php?id=1')
    save('response/incident.php?id=1', dict(action='update_assignment', assignment_id='1', assignment_status='on_scene', notes='Fictional arrival'), 'response/incident.php?id=1')
    print('PASS incident review, assignment and response update redirects')

    pages = [
        'dashboard/staff.php','incidents/list.php','incidents/create.php','incidents/view.php?id=1',
        'households/index.php','households/edit.php?id=1','households/details.php?id=1&member=1',
        'response/teams.php','response/teams.php?edit=2','response/incident.php?id=1',
        'evacuation/centers.php','evacuation/centers.php?edit=1','evacuation/center.php?id=1',
        'relief-inventory/index.php','relief-inventory/index.php?edit=1','relief-inventory/movements.php',
        'relief-distribution/index.php','relief-distribution/index.php?id=1','relief-inventory/locations.php',
        'relief-inventory/locations.php?edit=1','relief-inventory/location.php?id=1',
        'announcements/index.php','announcements/edit.php?id=1',
        'disaster-reports/index.php','disaster-reports/current.php?type=evacuees','map/map.php','evacuation/open-centers.php','records/index.php',
    ]
    visited = set()
    for client, paths in [(admin,pages),(resident,['dashboard/resident.php','announcements/published.php','incidents/list.php','incidents/create.php','incidents/view.php?id=1','map/map.php','evacuation/open-centers.php'])]:
        for page in paths:
            code, body = client.request(prefix + page)
            assert code == 200, (page,code)
            parser = Controls()
            parser.feed(body)
            for attrs, text in parser.buttons:
                assert text and ('btn' in attrs.get('class','').split() or 'navigation-toggle' in attrs.get('class','')), (page,text,attrs)
            for attrs, text in parser.links:
                href = attrs.get('href','')
                if not href or href.startswith(('https:', 'http:', '#')):
                    continue
                target = urljoin(prefix + page, href)
                if (id(client), target) in visited:
                    continue
                visited.add((id(client), target))
                assert client.request(target)[0] == 200, (page,text,target)
    print('PASS 35 rendered page states: labelled/styled buttons and', len(visited), 'local link destinations')
