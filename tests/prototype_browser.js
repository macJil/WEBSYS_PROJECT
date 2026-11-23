// Invoked by the isolated HTTP suite with two fictional sessions on stdin.
const {spawn} = require('node:child_process');
const fs = require('node:fs');
const assert = require('node:assert/strict');
const [base, folder] = process.argv.slice(2);
const sessions = JSON.parse(fs.readFileSync(0, 'utf8'));
const browser = spawn(process.env.TEST_BROWSER, ['--headless', '--disable-gpu',
    '--no-first-run', '--disable-background-networking', '--remote-debugging-pipe',
    '--user-data-dir=' + folder + '/browser'], {stdio: ['ignore','ignore','ignore','pipe','pipe']});
const pending = new Map();
let sequence = 0;
let buffer = '';
browser.stdio[4].setEncoding('utf8');
browser.stdio[4].on('data', chunk => {
    buffer += chunk;
    while (buffer.includes('\0')) {
        const end = buffer.indexOf('\0');
        const reply = JSON.parse(buffer.slice(0, end));
        buffer = buffer.slice(end + 1);
        if (!pending.has(reply.id)) continue;
        const {resolve, reject} = pending.get(reply.id);
        pending.delete(reply.id);
        if (reply.error) reject(Error(reply.error.message));
        else resolve(reply.result);
    }
});
function call(method, params = {}, sessionId) {
    const id = ++sequence;
    return new Promise((resolve, reject) => {
        pending.set(id, {resolve, reject});
        browser.stdio[3].write(JSON.stringify({id, method, params, sessionId}) + '\0');
    });
}
async function run() {
    const {targetId} = await call('Target.createTarget', {url:'about:blank'});
    const {sessionId} = await call('Target.attachToTarget', {targetId, flatten:true});
    const send = (method, params) => call(method, params, sessionId);
    const evaluate = async expression => (await send('Runtime.evaluate', {expression, returnByValue:true})).result.value;
    await send('Page.enable');
    await send('Network.enable');
    await send('Network.setBlockedURLs', {urls:['https://*','http://*.tile.*']});
    for (const [role, cookie] of sessions.entries()) {
        await send('Network.setCookie', {name:'PHPSESSID',value:cookie,url:base});
        const pages = role === 0 ? ['admin/admin.index.php','admin/households.php','incidents/list.php',
            'admin/response/teams.php','admin/evacuation/centers.php','admin/relief/inventory.php',
            'admin/announcement/announcement.php','admin/summaries/disaster.php?barangay=Topdac','admin/records.php',
            'centers/list.php','incidents/view.php?id=1'] : ['user/user.index.php','incidents/create.php',
            'centers/list.php','incidents/view.php?id=1'];
        for (const width of [375,1280]) {
            await send('Emulation.setDeviceMetricsOverride',{width,height:800,deviceScaleFactor:1,mobile:false});
            for (const path of pages) {
                const url = base + '/resources/views/' + path;
                if (path.includes('user.index')) await evaluate('localStorage.clear()');
                await send('Page.navigate',{url});
                for (let i=0;i<100;i++) {
                    if (await evaluate('location.href===' + JSON.stringify(url) + ' && document.readyState==="complete"')) break;
                    await new Promise(r=>setTimeout(r,50));
                }
                assert.equal(await evaluate('location.href'),url);
                assert.ok(await evaluate('document.querySelector("main") && document.documentElement.scrollWidth<=innerWidth+1'),width+' '+path);
                assert.equal(await evaluate('!!document.querySelector("#atok-forecast, #community-map, [data-picker-map], #print-summary")'),false);
                assert.equal(await evaluate(`!!document.querySelector('script[src*="leaflet"], script[src*="weather"], script[src*="print-summary"]')`),false);

            }
        }
    }
    console.log('PASS 375px and 1280px prototype layouts, no map/weather/print controls');
}
const timeout = setTimeout(()=>{console.error('Browser timed out');browser.kill();process.exit(1);},95000);
run().catch(error=>{console.error(error);process.exitCode=1;}).finally(()=>{clearTimeout(timeout);browser.kill();});
