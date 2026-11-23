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
    for folder in ['app', 'resources']:
        shutil.copytree(ROOT / folder, site / folder)
    (site / 'config').mkdir()
    for name in ['bootstrap.php', 'db.php']:
        shutil.copyfile(ROOT / 'config' / name, site / 'config' / name)
    shutil.copyfile(ROOT / 'index.php', site / 'index.php')
    example = (ROOT / 'config/config.example.php').read_text()
    constants = '''<?php
const APP_NAME = 'BayanReady';
const BASE_URL = '/fictional-demo/';
const DB_HOST = '127.0.0.1';
const DB_PORT = '1';
const DB_NAME = 'fictional_only';
const DB_USER = 'fictional';
const DB_PASSWORD = '';
const INCIDENT_UPLOAD_DIR = '/tmp/fictional-upload-directory';
const DEMO_BARANGAY_NAME = 'Atok, Benguet (DEMO)';
const MAP_LATITUDE = 16.6272;
const MAP_LONGITUDE = 120.7672;
const MAP_ZOOM = 14;
const MAP_MIN_ZOOM = 11;
const ADDRESS_LOOKUP_URL = '';
'''
    for label, config, base in [('array', example, '/WEBSYS_PROJECT'),
                                 ('constants', constants, '/fictional-demo')]:
        # A counter also verifies that db() does not execute config.php again.
        config = config.replace('<?php', "<?php\n$GLOBALS['config_loads'] = ($GLOBALS['config_loads'] ?? 0) + 1;", 1)
        (site / 'config/config.php').write_text(config)
        for first, second in [('app/helpers.php', 'config/db.php'),
                              ('config/db.php', 'app/helpers.php')]:
            output = run(site, f'''require_once '{first}'; require_once '{second}';
require_once 'config/bootstrap.php';
if ($GLOBALS['config_loads'] !== 1) throw new Exception('Config loaded more than once');
if (url('index.php') !== '{base}/index.php') throw new Exception('Incorrect URL');
if ($settings['app_name'] !== 'BayanReady') throw new Exception('Missing app name');
// A local SQLite connection checks connection reuse without opening MySQL.
$databaseConnection = new PDO('sqlite::memory:');
if (db() !== $databaseConnection || db()->query('SELECT 1')->fetchColumn() != 1) {{
    throw new Exception('Connection reuse failed');
}}
$_SERVER['REQUEST_METHOD'] = 'GET'; require 'index.php';
$_SESSION['ID'] = 1; $_SESSION['ROLE'] = 'resident';
require 'resources/views/components/location-picker.php';
if ($GLOBALS['config_loads'] !== 1) throw new Exception('Page reloaded config');
''')
            assert 'Register as a resident' in output and 'data-zoom="14"' in output
            assert f'action="{base}/index.php"' in output
        print('PASS', label, 'configuration: both include orders, login, URLs, picker, connection reuse')

    if os.environ.get('TEST_MYSQL_SOCKET'):
        # Read-only connection check; no application records or private config are used.
        array_database = example.replace("'host' => '127.0.0.1'", "'host' => 'localhost'")
        array_database = array_database.replace("'database' => 'barangay_drrm'", "'database' => 'information_schema'")
        array_database = array_database.replace("'password' => ''", "'password' => 'root'")
        constant_database = constants.replace("DB_HOST = '127.0.0.1'", "DB_HOST = 'localhost'")
        constant_database = constant_database.replace("DB_NAME = 'fictional_only'", "DB_NAME = 'information_schema'")
        constant_database = constant_database.replace("DB_USER = 'fictional'", "DB_USER = 'root'")
        constant_database = constant_database.replace("DB_PASSWORD = ''", "DB_PASSWORD = 'root'")
        for label, config in [('array', array_database), ('constants', constant_database)]:
            (site / 'config/config.php').write_text(config)
            for first, second in [('app/helpers.php', 'config/db.php'), ('config/db.php', 'app/helpers.php')]:
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
        output = run(site, "require 'config/bootstrap.php';")
        assert 'Setup required:' in output
    print('PASS malformed configuration returns controlled setup message')
