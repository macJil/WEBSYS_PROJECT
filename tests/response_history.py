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
    values = dict(history_type='response', history_id='1')
    assert resident.request(endpoint, values)[0] == 403
    anonymous = Client()
    anonymous.request(endpoint, values)
    assert anonymous.last_url.endswith('/index.php')
    assert admin.request(endpoint)[0] == 405
    assert admin.request(endpoint, dict(history_type='users', history_id='1'))[0] == 400
    active = query('SELECT id FROM evacuation_records WHERE checked_out_at IS NULL LIMIT 1')[0]['id']
    assert admin.request(endpoint, dict(history_type='evacuation', history_id=str(active)))[0] == 400
    details = query('SELECT * FROM relief_distribution_items ORDER BY id')
    snapshot = query('SELECT id,quantity_on_hand FROM relief_items ORDER BY id')
    stays = query('SELECT COUNT(*) n FROM evacuation_records WHERE checked_out_at IS NULL')
    rows = [('response', 'response_updates', '1'), ('evacuation', 'evacuation_records', '1'), ('stock', 'relief_stock_movements', '1'), ('distribution', 'relief_distributions', '1')]
    for kind, table, record in rows:
        assert admin.request(endpoint, dict(history_type=kind, history_id=record))[0] == 200
        assert query('SELECT history_hidden_at FROM ' + table + ' WHERE id=' + record)[0]['history_hidden_at']
        assert query('SELECT COUNT(*) n FROM ' + table)[0]['n'] >= before[table]
    assert snapshot == query('SELECT id,quantity_on_hand FROM relief_items ORDER BY id')
    assert details == query('SELECT * FROM relief_distribution_items ORDER BY id')
    assert stays == query('SELECT COUNT(*) n FROM evacuation_records WHERE checked_out_at IS NULL')
    assert state() == 'resolved'
    for path in ['admin/response/incident.php?id=1', 'incidents/view.php?id=1', 'admin/evacuation/center.php?id=1', 'admin/relief/movements.php', 'admin/relief/distributions.php']:
        code, body = admin.request(prefix + path)
        assert code == 200
        assert 'name="history_id" value="1"' not in body, path
    print('PASS migration preserves records; single/multiple team synchronization; cancelled/reactivated work; manual conflict blocked; history roles, active-stay protection and unchanged stock/occupancy')
