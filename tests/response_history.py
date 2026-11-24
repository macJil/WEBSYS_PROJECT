"""Status synchronization and display-only hiding in the disposable HTTP fixture."""
from pathlib import Path


def check_response_history(admin, resident, Client, query, prefix):
    tables = ['response_updates', 'evacuation_records', 'relief_stock_movements', 'relief_distributions']
    # Demonstrate upgrading existing populated tables without losing their rows.
    before = {t: query('SELECT COUNT(*) n FROM ' + t)[0]['n'] for t in tables}
    for table in tables:
        query('ALTER TABLE ' + table + ' DROP COLUMN history_hidden_at')
    query((Path(__file__).resolve().parents[1] / 'database/migrations/008_history_visibility.sql').read_text())
    assert before == {t: query('SELECT COUNT(*) n FROM ' + t)[0]['n'] for t in tables}
    page = prefix + 'response/incident.php?id=1'
    def update(assignment, status):
        code, body = admin.request(page, dict(action='update_assignment', assignment_id=str(assignment), assignment_status=status, notes='Fictional synchronization check'))
        assert code == 200 and 'alert-danger' not in body, body[-2000:]
    def state():
        return query('SELECT status FROM incident_reports WHERE id=1')[0]['status']
    update(1, 'completed')
    assert state() == 'resolved'
    update(1, 'assigned')
    assert state() == 'responding'
    admin.request(prefix + 'response/teams.php', dict(action='save_team', team_name='Fictional second response', contact_phone='', is_active='1'))
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
    admin.request(prefix + 'incidents/view.php?id=1', dict(action='reopen', notes='Conflicting manual change'))
    assert state() == 'resolved'
    endpoint = prefix + 'records/history.php'
    values = dict(history_type='response', history_id='1')
    assert admin.request(endpoint, values)[0] == 404
    assert resident.request(endpoint, values)[0] == 404
    code, body = admin.request(page, dict(action='update_assignment', assignment_id='1', assignment_status='on_scene', notes='Invalid selection'))
    assert 'Choose a valid assignment status' in body and state() == 'resolved'
    for path in ['response/incident.php?id=1', 'incidents/view.php?id=1', 'evacuation/center.php?id=1', 'relief-inventory/movements.php', 'relief-distribution/index.php']:
        code, body = admin.request(prefix + path)
        assert code == 200
        assert 'name="history_id" value="1"' not in body, path
        assert 'Hide history entry' not in body
    print('PASS read-only logs, removed hide endpoint and assignment status synchronization')
