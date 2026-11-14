const vm=require('vm'),fs=require('fs'),assert=require('assert');
let checks=0;
function check(ok,label){assert(ok,label);checks++;console.log('PASS '+label);}
function element(value=''){return {value,dataset:{},events:{},checked:true,hidden:true,textContent:'',addEventListener(k,fn){(this.events[k]??=[]).push(fn);},dispatchEvent(e){for(const fn of this.events[e.type]||[])fn(e);},fire(type){this.dispatchEvent({type});}};}
function fixture(initial='',withMap=true){
 const address=element(initial),form=element(),root=element();form.querySelector=()=>address;root.closest=()=>form;
 const keys=['[data-address-enabled]','[data-address-status]','[data-use-address]','[data-coordinate="latitude"]','[data-coordinate="longitude"]','[data-picker-status]','[data-picker-map]','[data-clear]','[data-locate]'];
 const items=Object.fromEntries(keys.map(k=>[k,element()]));root.querySelector=k=>items[k];root.dataset.addressUrl='https://photon.komoot.io/reverse';items['[data-picker-map]'].dataset={lat:16.6272,lng:120.7672,zoom:15};
 let time=10000,sequence=0,geoOk,geoFail;const timers=new Map(),calls=[];
 const map={setView(){return this;},on(type,fn){this[type]=fn;},removeLayer(){}};
 const context={document:{querySelectorAll:()=>[root]},Event:class{constructor(type){this.type=type;}},URL,AbortController,TextEncoder,Map,Date:{now:()=>time},setTimeout(fn,ms){const id=++sequence;timers.set(id,{at:time+ms,fn});return id;},clearTimeout(id){timers.delete(id);},navigator:{geolocation:{getCurrentPosition(ok,fail){geoOk=ok;geoFail=fail;}}},fetch:(url,options)=>new Promise((resolve,reject)=>{calls.push({url,options,resolve,reject});})};
 if(withMap)context.L={map:()=>map,tileLayer:()=>({on(){return this;},addTo(){}}),marker:()=>({addTo(){return this;},on(){}})};
 for(const file of ['location-picker.js'])vm.runInNewContext(fs.readFileSync(__dirname+'/../resources/assets/js/'+file,'utf8'),context);
 const flush=async()=>{for(let i=0;i<8;i++)await Promise.resolve();};
 async function tick(ms){time+=ms;for(const [id,t] of [...timers])if(t.at<=time){timers.delete(id);t.fn();}await flush();}
 function select(lat='16.6272',lng='120.7672'){items['[data-coordinate="latitude"]'].value=lat;items['[data-coordinate="longitude"]'].value=lng;map.click({latlng:{lat:Number(lat),lng:Number(lng)}});}
 async function reply(properties={street:'Demo Road',district:'Demo Purok',city:'Demo City'},index=calls.length-1){calls[index].resolve({ok:true,json:async()=>({features:[{properties}]})});await flush();}
 return {items,address,root,form,map,calls,tick,select,reply,flush,geo:()=>({ok:geoOk,fail:geoFail})};
}
(async()=>{
 const f=fixture('Saved address');
 await f.tick(5000);check(f.calls.length===0 && f.address.value==='Saved address','edit load preserves saved address without request');
 f.select('16.6272','120.7672');await f.tick(300);f.select('16.628','120.768');await f.tick(700);
 check(f.calls.length===1 && new URL(f.calls[0].url).searchParams.get('lat')==='16.6280000','debounce requests latest selected point');
 check(f.calls[0].options.credentials==='omit' && f.calls[0].options.referrerPolicy==='no-referrer','no application cookies or page URL sent');
 await f.reply({street:'Fictional Road',city:'Demo Town'});
 check(f.address.value==='Fictional Road, Demo Town' && f.items['[data-address-status]'].textContent==='Address suggested; please confirm.','explicit selection fills editable address');
 check(f.items['[data-coordinate="latitude"]'].value==='16.6280000','suggestion does not move selected coordinates');
 f.select('16.628','120.768');await f.tick(2000);check(f.calls.length===1,'same point uses memory cache');
 f.select('16.629','120.769');await f.tick(2000);f.address.value='My corrected landmark';f.address.fire('input');await f.reply({name:'Late address'});
 check(f.address.value==='My corrected landmark','manual edit survives late response');
 f.select('16.630','120.770');await f.tick(2000);const old=f.calls.length-1;
 f.select('16.631','120.771');await f.tick(2000);await f.reply({name:'Latest place'});await f.reply({name:'Old place'},old);
 check(f.address.value==='Latest place','out-of-order response ignored');
 f.select('16.632','120.772');await f.tick(2000);f.items['[data-clear]'].fire('click');await f.reply({name:'Cleared place'});
 check(f.address.value==='Latest place' && f.items['[data-coordinate="latitude"]'].value==='','clear preserves address and ignores pending result');
 f.select('16.633','120.773');await f.tick(2000);f.calls.at(-1).reject(new Error('offline'));await f.flush();
 check(f.address.value==='Latest place' && f.items['[data-address-status]'].textContent.includes('manually'),'failure keeps text and manual entry');
 f.select('16.634','120.774');await f.tick(2000);f.form.fire('submit');await f.reply({name:'After submit'});check(f.address.value==='Latest place','submit cancels delayed update');
 const geo=fixture('',false);geo.items['[data-locate]'].fire('click');geo.geo().fail({code:1});await geo.tick(1000);check(geo.calls.length===0,'denied permission sends no address request');
 geo.items['[data-locate]'].fire('click');geo.geo().ok({coords:{latitude:16.6272,longitude:120.7672,accuracy:12}});await geo.tick(700);await geo.reply({name:'Demo locality'});
 check(geo.address.value==='Demo locality','current location suggests address without Leaflet');
 geo.items['[data-locate]'].fire('click');geo.address.value='Typed while waiting';geo.address.fire('input');geo.geo().ok({coords:{latitude:16.628,longitude:120.768,accuracy:12}});await geo.tick(2000);
 check(geo.address.value==='Typed while waiting' && geo.calls.length===1,'manual edit during geolocation survives');
 const empty=fixture();empty.select();await empty.tick(700);empty.calls[0].resolve({ok:true,json:async()=>({features:[]})});await empty.flush();check(empty.address.value==='' && empty.items['[data-address-status]'].textContent.includes('manually'),'no result leaves manual fallback');
 const timeout=fixture();timeout.select();await timeout.tick(700);await timeout.tick(8000);check(timeout.calls[0].options.signal.aborted,'timeout aborts lookup');timeout.calls[0].reject(new Error('timeout'));await timeout.flush();check(timeout.items['[data-address-status]'].textContent.includes('manually'),'timeout explains fallback');
 const limit=fixture();for(let i=0;i<11;i++){limit.select(String(16.62+i/1000),'120.76');await limit.tick(2000);if(i<10)await limit.reply({name:'Demo'});}check(limit.calls.length===10,'ten requests per minute cap');
 const unsafe=fixture();unsafe.select();await unsafe.tick(700);await unsafe.reply({name:'<img src=x onerror=alert(1)>'});check(unsafe.address.value==='<img src=x onerror=alert(1)>','provider value is plain input text');
 const disabled=fixture();disabled.root.dataset.addressUrl='';disabled.select();await disabled.tick(1000);check(disabled.calls.length===0,'empty endpoint disables lookup');
 const httpError=fixture();httpError.select();await httpError.tick(700);httpError.calls[0].resolve({ok:false});await httpError.flush();check(httpError.items['[data-address-status]'].textContent.includes('manually'),'HTTP error permits manual entry');
 const malformed=fixture();malformed.select();await malformed.tick(700);malformed.calls[0].resolve({ok:true,json:async()=>{throw new Error('invalid JSON');}});await malformed.flush();check(malformed.address.value==='' && malformed.items['[data-address-status]'].textContent.includes('manually'),'invalid response permits manual entry');
 const manual=fixture();manual.items['[data-coordinate="latitude"]'].value='16.6272';manual.items['[data-coordinate="latitude"]'].fire('input');await manual.tick(1000);check(manual.calls.length===0,'typing coordinates does not send a lookup');
 console.log('TOTAL '+checks);
})().catch(error=>{console.error(error);process.exit(1);});
