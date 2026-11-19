// Invoked by the isolated HTTP suite with two fictional sessions on stdin.
const {spawn, execFileSync} = require('node:child_process');
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
    await send('Page.addScriptToEvaluateOnNewDocument', {source:`
        const nativeFetch = window.fetch.bind(window);
        window.fetch = async (url, options) => {
            if (!String(url).includes('api.open-meteo.com')) return nativeFetch(url,options);
            if (location.search.includes('outage')) throw Error('Fictional outage');
            const today = new Intl.DateTimeFormat('en-CA',{timeZone:'Asia/Manila',
                year:'numeric',month:'2-digit',day:'2-digit'}).format(new Date());
            return {ok:true,json:async()=>({daily:{time:[today],temperature_2m_min:[12],
                temperature_2m_max:[21],precipitation_probability_max:[42]}})};
        };`});
    for (const [role, cookie] of sessions.entries()) {
        await send('Network.setCookie', {name:'PHPSESSID',value:cookie,url:base});
        const pages = role === 0 ? ['admin/admin.index.php','admin/households.php','incidents/list.php',
            'admin/response/teams.php','admin/evacuation/centers.php','admin/relief/inventory.php',
            'admin/announcement/announcement.php','admin/summaries/disaster.php?barangay=Topdac','admin/records.php',
            'maps/map.php','maps/map.php?barangay=Topdac','incidents/view.php?id=1'] : ['user/user.index.php?outage','incidents/create.php',
            'maps/map.php','maps/map.php?barangay=Topdac','incidents/view.php?id=1'];
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
                if (path.endsWith('barangay=Topdac') && path.startsWith('maps/')) {
                    // These fictional records share a point, so the map reaches its close-up zoom.
                    async function waitForTilesAt(zoom) {
                        const selector = '.leaflet-tile[src*="/' + zoom + '/"]';
                        for (let attempt = 0; attempt < 60; attempt++) {
                            if (await evaluate('!!document.querySelector(' + JSON.stringify(selector) + ')')) return;
                            await new Promise(resolve => setTimeout(resolve, 50));
                        }
                        assert.fail('Expected filtered map tiles at zoom ' + zoom);
                    }
                    await waitForTilesAt(17);
                    await evaluate('document.getElementById("map-home").click()');
                    await waitForTilesAt(11);
                    await evaluate('document.getElementById("map-fit").click()');
                    await waitForTilesAt(17);
                }
                if (path.includes('.index')) {
                    const forecast = await evaluate('document.querySelector("#atok-forecast").textContent');
                    assert.ok(forecast.includes(role ? 'unavailable' : '42%'),forecast);
                }
                if (width === 1280 && (path.includes('summaries/') || path.includes('incidents/view'))) {
                    const {data} = await send('Page.printToPDF',{printBackground:true});
                    const pdf = folder + '/acceptance.pdf';
                    fs.writeFileSync(pdf,Buffer.from(data,'base64'));
                    const text = execFileSync('pdftotext',['-layout',pdf,'-'],{encoding:'utf8'});
                    assert.match(text,/Asia\/Manila/);
                    assert.doesNotMatch(text,/Log out|Print incident report|Apply filters/);
                    if (path.includes('summaries/')) assert.match(text,/Topdac/);
                    else {
                        assert.match(text,/incident report #1/i);
                        assert.equal(text.includes('Staff review history'),role===0);
                    }
                }
            }
        }
    }
    console.log('PASS 375px and 1280px layouts, weather success/outage and three authenticated PDFs');
}
const timeout = setTimeout(()=>{console.error('Browser timed out');browser.kill();process.exit(1);},95000);
run().catch(error=>{console.error(error);process.exitCode=1;}).finally(()=>{clearTimeout(timeout);browser.kill();});
