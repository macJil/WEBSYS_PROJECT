// Exercise actual shared-map code with DOM/Leaflet stand-ins; no network dependency.
const fs=require('fs'),vm=require('vm'),assert=require('assert');
class Element {
 constructor(){this.children=[];this.events={};this.dataset={};this.textContent='';this.checked=true;}
 addEventListener(name,fn){this.events[name]=fn;}
 appendChild(child){this.children.push(child);}
 prepend(child){this.children.unshift(child);}
}
async function run(admin){
 const ids={};for(const id of ['community-map','community-map-status','community-tile-status','map-home','map-fit'])ids[id]=new Element();
 ids['community-map'].dataset={admin:admin?'1':'0',lat:'16.41792',lng:'120.56091',zoom:'15',base:'/demo',url:'/maps/data.php'};
 const layers=['incidents','centers','relief_locations',...(admin?['households']:[])];
 const boxes=layers.map(layer=>{ids['count-'+layer]=new Element();const e=new Element();e.dataset.layer=layer;return e;});
 const groups=[],markers=[],views=[];let fits=0,fetches=0;
 const map={setView(p,z){views.push([p,z]);return this;},removeLayer(g){g.visible=false;},fitBounds(){fits++;}};
 const data={incidents:[{id:1,incident_type:'fire',public_label:'<img src=x onerror=alert(1)>',location_description:'PRIVATE',public_latitude:16,public_longitude:120,latitude:17,longitude:121,status:'verified'}],centers:[{id:2,name:'Center',latitude:16,longitude:120,status:admin?'closed':'open',capacity:5,occupancy:1,available:4}],relief_locations:[{id:3,name:'Relief',latitude:16,longitude:120,location_type:'distribution'}],households:[{id:4,household_code:'PRIVATE',latitude:16,longitude:120,hazard_type:'flood'}]};
 const context={document:{getElementById:id=>ids[id],querySelectorAll:()=>boxes,createElement:()=>new Element()},fetch:async()=>{fetches++;return {ok:true,json:async()=>data};},L:{
 map:(element,options)=>{assert.equal(options.minZoom,14);assert(!options.maxBounds);return map;},tileLayer:()=>({on(){return this;},addTo(){return this;}}),
 featureGroup:()=>{const g={markers:[],visible:true,addTo(){this.visible=true;return this;},eachLayer(fn){this.markers.forEach(fn);}};groups.push(g);return g;},
 divIcon:config=>{assert(!config.html.includes('onerror'));return config;},
 marker:(point,options)=>{const m={point,options,bindPopup(p){this.popup=p;return this;},addTo(g){g.markers.push(this);markers.push(this);return this;},getLatLng(){return point;}};return m;},
 latLngBounds:()=>({points:[],extend(p){this.points.push(p);},isValid(){return this.points.length>0;}})
 }};
 vm.createContext(context);vm.runInContext(fs.readFileSync(__dirname+'/../resources/assets/js/community-map.js','utf8'),context);
 await new Promise(resolve=>setImmediate(resolve));
 assert.equal(markers.length,admin?4:3);assert.equal(fits,0);assert.equal(views[0][0][0],16.41792);
 assert.equal(markers[0].point[0],admin?17:16);
 const children=markers.flatMap(m=>m.popup.children);
 if(admin){assert.equal(children.filter(c=>c.href).length,4);assert(markers[1].options.icon.html.includes('center-closed'));}
 else{assert(!children.some(c=>c.href));assert(!children.some(c=>c.textContent.includes('PRIVATE')));assert(children.some(c=>c.textContent.includes('<img src=x onerror=alert(1)>')));}
 boxes[0].checked=false;boxes[0].events.change();assert.equal(groups[0].visible,false);assert.equal(fetches,1);
 ids['map-fit'].events.click();assert.equal(fits,1);
 boxes.forEach(b=>{b.checked=false;b.events.change();});ids['map-fit'].events.click();assert.equal(fits,1);
 assert(ids['community-map-status'].textContent.includes('No markers'));
 console.log('PASS '+(admin?'admin':'resident')+' layers, coordinate source, safe popups, links, center status, toggles and initial view');
}
(async()=>{await run(false);await run(true);})().catch(e=>{console.error(e);process.exit(1);});
