"""Check array/constant configuration in copied files, never the private local config."""
from pathlib import Path
import os
import shutil
import subprocess
import tempfile

ROOT = Path(__file__).resolve().parents[1]
PHP = os.environ.get('TEST_PHP', 'php')


def run(site, code):
    # Treat warnings and deprecations as failures, including duplicate constants.
    strict = '''error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
'''
    options = [PHP, '-d', 'display_errors=1', '-d', 'session.save_path=' + str(site)]
    if os.environ.get('TEST_MYSQL_SOCKET'):
        options += ['-d', 'pdo_mysql.default_socket=' + os.environ['TEST_MYSQL_SOCKET']]
    result = subprocess.run(options + ['-r', strict + code],
                            cwd=site, capture_output=True, text=True)
    assert result.returncode == 0, result.stdout + result.stderr
    assert not result.stderr, result.stderr
    return result.stdout


with tempfile.TemporaryDirectory(prefix='bayanready-config-regression-') as temp:
    site = Path(temp) / 'site'
    site.mkdir()
    for folder in ['assets', 'includes']:
        shutil.copytree(ROOT / folder, site / folder)
    (site / 'config').mkdir()
    for name in ['db.php']:
        shutil.copyfile(ROOT / 'config' / name, site / 'config' / name)
    shutil.copyfile(ROOT / 'index.php', site / 'index.php')
    example = (ROOT / 'setup/config.example.php').read_text()
    for label, config, base in [('constants', example.replace('/WEBSYS_PROJECT', '/fictional-demo/'), '/fictional-demo')]:
        # A counter also verifies that db() does not execute config.php again.
        config = config.replace('<?php', "<?php\n$GLOBALS['config_loads'] = ($GLOBALS['config_loads'] ?? 0) + 1;", 1)
        (site / 'config/config.php').write_text(config)
        for first, second in [('includes/helpers.php', 'config/db.php'),
                              ('config/db.php', 'includes/helpers.php')]:
            output = run(site, f'''require_once '{first}'; require_once '{second}';
require_once 'config/db.php';
if ($GLOBALS['config_loads'] !== 1) throw new Exception('Config loaded more than once');
if (url('index.php') !== '{base}/index.php') throw new Exception('Incorrect URL');
if (APP_NAME !== 'BayanReady') throw new Exception('Missing app name');
// A local SQLite connection checks connection reuse without opening MySQL.
$databaseConnection = new PDO('sqlite::memory:');
if (db() !== $databaseConnection || db()->query('SELECT 1')->fetchColumn() != 1) {{
    throw new Exception('Connection reuse failed');
}}
$_SERVER['REQUEST_METHOD'] = 'GET'; require 'index.php';
$_SESSION['ID'] = 1; $_SESSION['ROLE'] = 'resident';
require 'includes/location-picker.php';
if ($GLOBALS['config_loads'] !== 1) throw new Exception('Page reloaded config');
''')
            assert 'Register as a resident' in output and 'data-zoom="14"' in output
            assert f'action="{base}/index.php"' in output
        print('PASS', label, 'configuration: both include orders, login, URLs, picker, connection reuse')

    if os.environ.get('TEST_MYSQL_SOCKET'):
        # Read-only connection check; no application records or private config are used.
        config = example.replace("DB_HOST = '127.0.0.1'", "DB_HOST = 'localhost'")
        config = config.replace("DB_NAME = 'barangay_drrm'", "DB_NAME = 'information_schema'")
        config = config.replace("DB_PASSWORD = ''", "DB_PASSWORD = 'root'")
        for label, config in [('constants', config)]:
            (site / 'config/config.php').write_text(config)
            for first, second in [('includes/helpers.php', 'config/db.php'), ('config/db.php', 'includes/helpers.php')]:
                output = run(site, f"""require_once '{first}'; require_once '{second}';
$connection = db();
if ($connection->query('SELECT 1')->fetchColumn() != 1 || $connection !== db()) {{
    throw new Exception('Database connection failed');
}}
echo 'connected';
""")
                assert output == 'connected'
            print('PASS', label, 'configuration: actual MySQL connection and reuse in both include orders')

    for invalid in ['<?php return 1;', "<?php return ['base_url' => []];"]:
        (site / 'config/config.php').write_text(invalid)
        output = run(site, "require 'config/db.php';")
        assert 'Setup required:' in output
    print('PASS malformed configuration returns controlled setup message')
