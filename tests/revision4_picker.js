const vm=require('vm'),fs=require('fs'),assert=require('assert');
function element(){return {value:'',dataset:{},events:{},addEventListener(k,f){this.events[k]=f;}};}
for(const withMap of [true,false]){
 const items={};for(const key of ['[data-coordinate="latitude"]','[data-coordinate="longitude"]','[data-picker-status]','[data-picker-map]','[data-clear]','[data-locate]'])items[key]=element();
 const root=element();root.querySelector=k=>items[k];items['[data-picker-map]'].dataset={lat:16,lng:120,zoom:15};
 let ok,fail,calls=0;const map={setView(){return this;},on(k,f){this[k]=f;},removeLayer(){}};
 const context={document:{querySelectorAll:()=>[root]},navigator:{geolocation:{getCurrentPosition(a,b){calls++;ok=a;fail=b;}}}};
 if(withMap)context.L={map:()=>map,tileLayer:()=>({on(){return this;},addTo(){}}),marker:()=>({addTo(){return this;},on(){}})};
 vm.runInNewContext(fs.readFileSync(__dirname+'/../resources/assets/js/location-picker.js','utf8'),context);
 assert.equal(calls,0);const locate=items['[data-locate]'],status=items['[data-picker-status]'],lat=items['[data-coordinate="latitude"]'],lng=items['[data-coordinate="longitude"]'];
 locate.events.click();fail({code:1});assert(status.textContent.includes('denied'));
 locate.events.click();ok({coords:{latitude:16.4,longitude:120.5,accuracy:300}});assert.equal(lat.value,'16.4000000');assert(status.textContent.includes('low'));
 locate.events.click();items['[data-clear]'].events.click();ok({coords:{latitude:17,longitude:121,accuracy:1}});assert.equal(lat.value,'');
 lat.value='16';lng.value='120';lat.events.input();assert(status.textContent.includes('Selected'));
 if(withMap){map.click({latlng:{lat:16.1,lng:120.1}});assert.equal(lat.value,'16.1000000');}
 console.log('PASS opt-in, denial, accuracy, stale callback, manual fallback; map='+withMap);
}
