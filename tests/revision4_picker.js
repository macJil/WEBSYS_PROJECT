const vm=require('vm'),fs=require('fs'),assert=require('assert');
function element(){return {value:'',dataset:{},events:{},dispatchEvent(){},addEventListener(k,f){this.events[k]=f;}};}
for(const withMap of [true,false]){
 const items={};for(const key of ['[data-coordinate="latitude"]','[data-coordinate="longitude"]','[data-picker-status]','[data-picker-map]','[data-clear]','[data-locate]'])items[key]=element();
 const root=element();root.querySelector=k=>items[k];items['[data-picker-map]'].dataset={lat:16.41792,lng:120.56091,zoom:15};
 const lat=items['[data-coordinate="latitude"]'],lng=items['[data-coordinate="longitude"]'];
 lat.value='16.4192000';lng.value='120.5623000';
 let ok,fail,calls=0,currentMarker=null;
 const views=[];
 const map={setView(point,zoom){views.push([point,zoom]);return this;},on(k,f){this[k]=f;},removeLayer(){currentMarker=null;}};
 const context={Event:class {constructor(type){this.type=type;}},document:{querySelectorAll:()=>[root]},navigator:{geolocation:{getCurrentPosition(a,b){calls++;ok=a;fail=b;}}}};
 if(withMap)context.L={map:(element,options)=>{assert.equal(options.minZoom,14);assert(!options.maxBounds);return map;},tileLayer:()=>({on(){return this;},addTo(){}}),marker:(point)=>{currentMarker={point,events:{},addTo(){return this;},on(k,f){this.events[k]=f;},getLatLng(){return {lat:16.4168,lng:120.5597};}};return currentMarker;}};
 vm.runInNewContext(fs.readFileSync(__dirname+'/../resources/assets/js/location-picker.js','utf8'),context);
 assert.equal(calls,0);
 if(withMap){assert.equal(currentMarker.point[0],16.4192);assert.equal(views.at(-1)[0][1],120.5623);}
 const locate=items['[data-locate]'],status=items['[data-picker-status]'];
 locate.events.click();fail({code:1});assert(status.textContent.includes('denied'));assert.equal(lat.value,'16.4192000');
 locate.events.click();ok({coords:{latitude:16.41792,longitude:120.56091,accuracy:300}});assert.equal(lat.value,'16.4179200');assert(status.textContent.includes('low'));
 locate.events.click();items['[data-clear]'].events.click();ok({coords:{latitude:16.418,longitude:120.561,accuracy:1}});assert.equal(lat.value,'');assert.equal(lng.value,'');assert.equal(currentMarker,null);
 lat.value='16.4192';lng.value='120.5623';lat.events.input();assert(status.textContent.includes('Selected'));
 if(withMap){map.click({latlng:{lat:16.41792,lng:120.56091}});assert.equal(lat.value,'16.4179200');currentMarker.events.dragend();assert.equal(lat.value,'16.4168000');assert.equal(lng.value,'120.5597000');}
 lat.value='0';lng.value='0';lat.events.input();assert(status.textContent.includes('Selected'));
 lng.value='';lng.events.input();assert(status.textContent.includes('both valid'));assert.equal(currentMarker,null);
 for(const code of [2,3]){locate.events.click();fail({code});assert(status.textContent.includes(code===2?'unavailable':'timed out'));}
 delete context.navigator.geolocation;locate.events.click();assert(status.textContent.includes('unavailable'));
 console.log('PASS saved edit pin, click/drag, manual pair, geolocation, clear, zero, invalid pair, fallback; map='+withMap);
}
