"""Fresh-teammate configuration smoke check; uses no live database or credentials."""
from pathlib import Path
import os, shutil, subprocess, tempfile
root=Path(__file__).resolve().parents[1]
def run(folder,code):
    return subprocess.run([os.environ.get('TEST_PHP', 'php'),'-d','display_errors=1','-d','session.save_path='+str(folder),'-r',code],cwd=folder,text=True,capture_output=True)
with tempfile.TemporaryDirectory(prefix='bayanready-config-') as tmp:
    copy=Path(tmp)/'site'
    subprocess.run(['git','checkout-index','--all','--prefix='+str(copy)+'/'],cwd=root,check=True)
    config = copy/'config/config.php'
    settings = config.read_text()
    config.unlink()
    for code in ["require 'includes/session.php';"]:
        r=run(copy,code)
        assert 'Setup required:' in r.stdout and 'Warning:' not in r.stdout and 'Fatal error:' not in r.stdout,r
    print('PASS missing config produces controlled setup message for pages and database')
    config.write_text(settings)
    r=run(copy,"require 'config/config.php'; $s=$settings; if(!is_array($s)||!isset($s['host'],$s['database'],$s['base_url']))exit(1);")
    assert r.returncode==0,r.stderr
    r=run(copy,"$_SERVER['REQUEST_METHOD']='GET'; require 'index.php';")
    assert r.returncode==0 and 'Atok MDRRM System' in r.stdout and '<h2>Login</h2>' in r.stdout and 'Warning:' not in r.stdout,r.stderr
    print('PASS tracked configuration loads the login page')
