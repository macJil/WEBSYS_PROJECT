"""Fresh-teammate configuration smoke check; uses no live database or credentials."""
from pathlib import Path
import shutil, subprocess, tempfile
root=Path(__file__).resolve().parents[1]
def run(folder,code):
    return subprocess.run(['php','-d','display_errors=1','-r',code],cwd=folder,text=True,capture_output=True)
with tempfile.TemporaryDirectory(prefix='bayanready-config-') as tmp:
    copy=Path(tmp)/'site'
    subprocess.run(['git','checkout-index','--all','--prefix='+str(copy)+'/'],cwd=root,check=True)
    # Overlay the working files relevant to this phase before it is committed.
    for name in ['app/helpers.php','config/db.php','config/config.example.php']:
        shutil.copyfile(root/name,copy/name)
    for name in ['config/config.php','config/local.php','config/settings.php','config/local.example.php']:
        (copy/name).unlink(missing_ok=True)
    for code in ["require 'app/helpers.php';", "require 'config/db.php'; db();"]:
        r=run(copy,code)
        assert 'Setup required:' in r.stdout and 'Warning:' not in r.stdout and 'Fatal error:' not in r.stdout,r
    print('PASS missing config produces controlled setup message for pages and database')
    shutil.copyfile(copy/'config/config.example.php',copy/'config/config.php')
    r=run(copy,"$s=require 'config/config.php'; if(!is_array($s)||!isset($s['host'],$s['database'],$s['base_url']))exit(1);")
    assert r.returncode==0,r.stderr
    r=run(copy,"$_SERVER['REQUEST_METHOD']='GET'; require 'index.php';")
    assert r.returncode==0 and 'BayanReady' in r.stdout and 'Register as a resident' in r.stdout and 'Warning:' not in r.stdout,r.stderr
    print('PASS example copy loads configuration and renders fresh login/registration')
r=subprocess.run(['git','check-ignore','config/config.php'],cwd=root,text=True,capture_output=True)
assert r.returncode==0
print('PASS local config is ignored by Git')
