const vm=require('vm'),fs=require('fs'),assert=require('assert');
let checks=0;
function check(ok,label){assert(ok,label);checks++;console.log('PASS '+label);}
function element(value=''){return {value,dataset:{},events:{},checked:true,hidden:true,textContent:'',addEventListener(k,fn){(this.events[k]??=[]).push(fn);},dispatchEvent(e){for(const fn of this.events[e.type]||[])fn(e);},fire(type){this.dispatchEvent({type});}};}
function fixture(initial='',withMap=true){
 const address=element(initial),form=element(),root=element();form.querySelector=()=>address;root.closest=()=>form;
 const keys=['[data-address-enabled]','[data-address-status]','[data-use-address]','[data-coordinate="latitude"]','[data-coordinate="longitude"]','[data-picker-status]','[data-picker-map]','[data-clear]','[data-locate]'];
 const items=Object.fromEntries(keys.map(k=>[k,element()]));root.querySelector=k=>items[k];root.dataset.addressUrl='https://photon.komoot.io/reverse';items['[data-picker-map]'].dataset={lat:16.41792,lng:120.56091,zoom:15};
 let time=10000,sequence=0,geoOk,geoFail;const timers=new Map(),calls=[];
 const map={setView(){return this;},on(type,fn){this[type]=fn;},removeLayer(){}};
 const context={document:{querySelectorAll:()=>[root]},Event:class{constructor(type){this.type=type;}},URL,AbortController,Map,Date:{now:()=>time},setTimeout(fn,ms){const id=++sequence;timers.set(id,{at:time+ms,fn});return id;},clearTimeout(id){timers.delete(id);},navigator:{geolocation:{getCurrentPosition(ok,fail){geoOk=ok;geoFail=fail;}}},fetch:(url,options)=>new Promise((resolve,reject)=>{calls.push({url,options,resolve,reject});})};
 if(withMap)context.L={map:()=>map,tileLayer:()=>({on(){return this;},addTo(){}}),marker:()=>({addTo(){return this;},on(){}})};
 for(const file of ['location-picker.js','address-suggestion.js'])vm.runInNewContext(fs.readFileSync(__dirname+'/../resources/assets/js/'+file,'utf8'),context);
 const flush=async()=>{for(let i=0;i<8;i++)await Promise.resolve();};
 async function tick(ms){time+=ms;for(const [id,t] of [...timers])if(t.at<=time){timers.delete(id);t.fn();}await flush();}
 function select(lat='16.41792',lng='120.56091'){items['[data-coordinate="latitude"]'].value=lat;items['[data-coordinate="longitude"]'].value=lng;root.fire('location-selected');}
 async function reply(properties={street:'Demo Road',district:'Demo Purok',city:'Demo City'},index=calls.length-1){calls[index].resolve({ok:true,json:async()=>({features:[{properties}]})});await flush();}
 return {items,address,root,form,map,calls,tick,select,reply,flush,geo:()=>({ok:geoOk,fail:geoFail})};
}
(async()=>{
 const f=fixture(),status=f.items['[data-address-status]'];
 await f.tick(5000);check(f.calls.length===0,'page load makes no lookup');
 f.map.click({latlng:{lat:16.41792,lng:120.56091}});await f.tick(300);f.map.click({latlng:{lat:16.4192,lng:120.5623}});await f.tick(700);
 check(f.calls.length===1 && new URL(f.calls[0].url).searchParams.get('lat')==='16.4192000','rapid map clicks debounce to latest point');
 check(f.calls[0].options.credentials==='omit' && f.calls[0].options.referrerPolicy==='no-referrer','request omits cookies and referrer');
 await f.reply();check(f.address.value==='Demo Road, Demo Purok, Demo City' && status.textContent.includes('Address suggested; please confirm'),'empty written field auto-filled');
 const savedLat=f.items['[data-coordinate="latitude"]'].value;check(savedLat==='16.4192000','address result does not move selected point');
 f.select('16.4192000','120.5623000');await f.tick(2000);check(f.calls.length===1,'repeat point uses in-memory cache');
 f.select('16.4168','120.5597');await f.tick(2000);f.address.value='My manually edited landmark';f.address.fire('input');await f.reply({street:'Other Road'});
 check(f.address.value==='My manually edited landmark' && !f.items['[data-use-address]'].hidden,'late response preserves manual edit and offers suggestion');
 f.items['[data-use-address]'].fire('click');check(f.address.value==='Other Road','explicit suggestion button applies candidate');
 f.select('16.418','120.561');await f.tick(2000);const older=f.calls.length-1;
 f.select('16.419','120.562');check(f.calls[older].options.signal.aborted,'new point cancels prior request');await f.tick(2000);await f.reply({street:'Newest Road'});await f.reply({street:'Stale Road'},older);
 check(f.address.value==='Newest Road','out-of-order response ignored');
 f.select('16.420','120.563');await f.tick(2000);f.items['[data-clear]'].fire('click');await f.reply({street:'Cleared Road'});
 check(f.address.value==='Newest Road' && f.items['[data-coordinate="latitude"]'].value==='' && f.items['[data-use-address]'].hidden,'clear cancels lookup but preserves text');
 f.select('16.421','120.564');await f.tick(2000);f.calls.at(-1).reject(new Error('offline'));await f.flush();
 check(status.textContent.includes('manually') && f.address.value==='Newest Road' && f.items['[data-coordinate="latitude"]'].value==='16.421','network failure retains point and address');
 f.items['[data-address-enabled]'].checked=false;f.items['[data-address-enabled]'].fire('change');const before=f.calls.length;f.select();await f.tick(5000);check(f.calls.length===before,'suggestions off prevents lookup');
 f.items['[data-address-enabled]'].checked=true;f.items['[data-address-enabled]'].fire('change');await f.tick(2000);check(f.calls.length===before,'reenabling does not transmit saved point');
 f.select('91','120');await f.tick(2000);check(f.calls.length===before,'invalid point is not transmitted');
 const edited=fixture('Saved written location');await edited.tick(1000);check(edited.calls.length===0,'edit form makes no initial request');edited.select();await edited.tick(700);await edited.reply();check(edited.address.value==='Saved written location','existing saved address is preserved');
 const geo=fixture('',false);geo.items['[data-locate]'].fire('click');geo.geo().fail({code:1});await geo.tick(1000);check(geo.calls.length===0,'denied location makes no lookup');geo.items['[data-locate]'].fire('click');geo.geo().ok({coords:{latitude:16.41792,longitude:120.56091,accuracy:10}});await geo.tick(700);await geo.reply({name:'Irisan',city:'Baguio'});check(geo.address.value==='Irisan, Baguio','chosen current location works without map tiles');
 const noResult=fixture();noResult.select();await noResult.tick(700);noResult.calls[0].resolve({ok:true,json:async()=>({features:[]})});await noResult.flush();check(noResult.address.value==='' && noResult.items['[data-address-status]'].textContent.includes('manually'),'empty result allows manual entry');
 const limit=fixture();for(let i=0;i<11;i++){limit.select(String(16.41+i/1000),'120.56');await limit.tick(2000);if(i<10)await limit.reply({name:'Demo'});}check(limit.calls.length===10 && limit.items['[data-address-status]'].textContent.includes('limit'),'10 requests per minute per picker cap');
 const timeout=fixture();timeout.select();await timeout.tick(700);await timeout.tick(8000);check(timeout.calls[0].options.signal.aborted,'8-second timeout aborts request');timeout.calls[0].reject(new Error('aborted'));await timeout.flush();check(timeout.items['[data-address-status]'].textContent.includes('manually'),'timeout leaves manual fallback');
 const unsafe=fixture();unsafe.select();await unsafe.tick(700);await unsafe.reply({name:'<img src=x onerror=alert(1)>'});check(unsafe.address.value==='<img src=x onerror=alert(1)>','provider text is assigned as a value, not HTML');
 unsafe.select('16.418','120.56');await unsafe.tick(2000);unsafe.form.fire('submit');await unsafe.reply({name:'Late submit result'});check(unsafe.address.value!=='Late submit result','submission cancels delayed autofill');
 console.log('TOTAL',checks);
})().catch(error=>{console.error(error);process.exit(1);});
