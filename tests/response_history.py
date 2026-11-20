"""Assignment synchronization and history access on a fictional database."""


def check_response_history(admin, resident, Client, query, prefix):
    page = prefix + 'admin/response/incident.php?id=1'
    def update(assignment, status):
        code, body = admin.request(page, dict(action='update_assignment', assignment_id=str(assignment), assignment_status=status, notes='Fictional synchronization check'))
        assert code == 200 and 'alert-danger' not in body, body[-2000:]
    def state():
        return query('SELECT status FROM incident_reports WHERE id=1')[0]['status']
    update(1, 'completed')
    assert state() == 'resolved'
    update(1, 'assigned')
    assert state() == 'responding'
    admin.request(prefix + 'admin/response/teams.php', dict(action='save_team', team_name='Fictional second response', contact_phone='', is_active='1'))
    team = query('SELECT MAX(id) id FROM responders')[0]['id']
    assert admin.request(page, dict(action='assign_team', team_id=str(team), notes='Fictional second dispatch'))[0] == 200
    second = query('SELECT MAX(id) id FROM incident_assignments WHERE incident_id=1')[0]['id']
    update(1, 'completed')
    assert state() == 'responding'
    update(second, 'cancelled')
    assert state() == 'verified'
    update(second, 'assigned')
    update(second, 'completed')
    assert state() == 'resolved'
    admin.request(prefix + 'incidents/view.php?id=1', dict(action='review_incident', status='verified', notes='Conflicting manual change'))
    assert state() == 'resolved'
    endpoint = prefix + 'admin/history.php'
    assert admin.request(endpoint)[0] == 404
    for path in ['admin/response/incident.php?id=1', 'incidents/view.php?id=1', 'admin/evacuation/center.php?id=1', 'admin/relief/movements.php', 'admin/relief/distributions.php']:
        code, body = admin.request(prefix + path)
        assert code == 200
        assert 'name="history_id" value="1"' not in body, path
    print('PASS assignment synchronization and removal of hide-history controls')
