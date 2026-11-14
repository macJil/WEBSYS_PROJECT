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

    save('admin/response/teams.php', dict(action='save_team', team_name='Fictional Test Team', contact_phone='', is_active='1'), 'admin/response/teams.php')
    save('admin/response/teams.php?edit=1', dict(action='save_team', team_name='Fictional Edited Team', contact_phone='', is_active='1'), 'admin/response/teams.php')
    save('admin/relief/inventory.php', dict(action='save_item', item_name='Fictional Packs', unit='packs'), 'admin/relief/inventory.php')
    save('admin/relief/inventory.php?edit=1', dict(action='save_item', item_name='Fictional Food Packs', unit='packs'), 'admin/relief/inventory.php')
    save('admin/relief/movements.php', dict(action='stock', item_id='1', movement_type='stock_in', quantity='10', notes='Fictional stock'), 'admin/relief/movements.php')
    save('admin/relief/distributions.php', {'action':'distribute','household_id':'1','received_by':'Fictional Receiver','notes':'Demo','goods[1]':'2'}, 'admin/relief/distributions.php?id=1')
    save('admin/announcement/announcement.php', dict(action='save_draft', title='Fictional Notice', message='Fictional reminder'), 'admin/announcement/announcement.php')
    save('admin/announcement/announcement.update.php?id=1', dict(action='publish', title='Fictional Notice', message='Fictional reminder'), 'admin/announcement/announcement.php')
    values = dict(action='save_draft', title='Fictional Notice', message='Fictional reminder')
    code, body = admin.request(prefix + 'admin/announcement/announcement.update.php?id=1', values)
    assert 'Confirm withdrawal' in body and query('SELECT is_published FROM announcements WHERE id=1')[0]['is_published'] == 1
    values['confirm_withdraw'] = '1'
    save('admin/announcement/announcement.update.php?id=1', values, 'admin/announcement/announcement.php')
    print('PASS team, goods, stock, distribution and announcement save redirects; withdrawal confirmation')

    page = 'admin/records.php?type=teams'
    code, body = admin.request(prefix + page)
    assert 'Delete selected' in body and 'teams:1' in body
    save(page, [('action','delete'),('ids[]','teams:1')], page + '&q=&page=1')
    assert query('SELECT COUNT(*) AS total FROM responders')[0]['total'] == 0
    print('PASS direct checkbox deletion and filtered redirect')
    # Recreate the team for the navigation crawl and browser check.
    save('admin/response/teams.php', dict(action='save_team', team_name='Fictional UI Team', contact_phone='', is_active='1'), 'admin/response/teams.php')

    save('incidents/view.php?id=1', dict(action='review_incident', status='verified', notes='Fictional verification'), 'incidents/view.php?id=1')
    save('admin/response/incident.php?id=1', dict(action='assign_team', team_id='2', notes='Fictional dispatch'), 'admin/response/incident.php?id=1')
    save('admin/response/incident.php?id=1', dict(action='update_assignment', assignment_id='1', assignment_status='on_scene', notes='Fictional arrival'), 'admin/response/incident.php?id=1')
    print('PASS incident review, assignment and response update redirects')

    pages = [
        'admin/admin.index.php','incidents/list.php','incidents/create.php','incidents/view.php?id=1',
        'admin/households.php','admin/households.php?edit=1','admin/household.php?id=1&member=1',
        'admin/response/teams.php','admin/response/teams.php?edit=2','admin/response/incident.php?id=1',
        'admin/evacuation/centers.php','admin/evacuation/centers.php?edit=1','admin/evacuation/center.php?id=1',
        'admin/relief/inventory.php','admin/relief/inventory.php?edit=1','admin/relief/movements.php',
        'admin/relief/distributions.php','admin/relief/distributions.php?id=1','admin/relief/locations.php',
        'admin/relief/locations.php?edit=1','admin/relief/location.php?id=1',
        'admin/announcement/announcement.php','admin/announcement/announcement.update.php?id=1',
        'admin/summaries/disaster.php','admin/summaries/current.php?type=evacuees','maps/map.php','maps/centers.php','admin/records.php',
    ]
    visited = set()
    for client, paths in [(admin,pages),(resident,['user/user.index.php','user/announcement.php','incidents/list.php','incidents/create.php','incidents/view.php?id=1','maps/map.php','maps/centers.php'])]:
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
