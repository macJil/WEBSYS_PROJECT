// Small DOM/map stand-ins exercise the actual interaction script without network access.
const fs=require('fs'), vm=require('vm'), assert=require('assert');
class Element {
    constructor() { this.value=''; this.textContent=''; this.events={}; this.children=[]; this.dataset={lat:'16.41792',lng:'120.56091',zoom:'15'}; }
    addEventListener(name,fn) { this.events[name]=fn; }
    replaceChildren() { this.children=[]; }
    appendChild(child) { this.children.push(child); }
}
function scenario(withMap) {
    const elements={};
    ['incident-map','map-status','latitude','longitude','location-search','location-results','search-location','clear-location','description'].forEach(id=>elements[id]=new Element());
    const map={events:{},setView(){return this;},on(name,fn){this.events[name]=fn;return this;},removeLayer(){}};
    const context={window:{},document:{getElementById:id=>elements[id],querySelector:()=>elements.description,createElement:()=>new Element()}};
    if(withMap) context.L={map:()=>map,tileLayer:()=>({on(){return this;},addTo(){return this;}}),circleMarker:()=>({addTo(){return this;}})};
    vm.createContext(context);
    for(const file of ['demo-locations.js','incident-map.js']) vm.runInContext(fs.readFileSync(__dirname+'/../resources/assets/js/'+file,'utf8'),context);
    assert.equal(elements.latitude.value,'');
    elements['location-search'].value='Sample B'; elements['search-location'].events.click();
    assert.equal(elements['location-results'].children.length,1);
    elements['location-results'].children[0].events.click();
    assert.equal(elements.latitude.value,'16.4192000'); assert.equal(elements.longitude.value,'120.5623000');
    assert(elements.description.value.includes('DEMO ONLY'));
    if(withMap) {map.events.click({latlng:{lat:12.5,lng:121.5}}); assert.equal(elements.latitude.value,'12.5000000');}
    elements.latitude.value='95'; elements.latitude.events.input(); assert(elements['map-status'].textContent.includes('valid coordinates'));
    elements['clear-location'].events.click(); assert.equal(elements.latitude.value,''); assert.equal(elements.longitude.value,'');
    elements['location-search'].value='not in demo list'; elements['search-location'].events.click(); assert(elements['location-results'].textContent.includes('No demo'));
    console.log('PASS demo search, selection, validation message and clearing '+(withMap?'with map':'without Leaflet'));
}
scenario(true);scenario(false);
