document.querySelectorAll('.location-picker').forEach(root => {
 if(root.dataset.ready) return; root.dataset.ready='1';
 const lat=root.querySelector('[data-coordinate="latitude"]'), lng=root.querySelector('[data-coordinate="longitude"]'), status=root.querySelector('[data-picker-status]'), el=root.querySelector('[data-picker-map]');
 let map=null,marker=null,request=0;
 if(typeof L!=='undefined') {
  map=L.map(el, {minZoom:Number(el.dataset.minZoom || 14)}).setView([Number(el.dataset.lat),Number(el.dataset.lng)],Number(el.dataset.zoom));
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'}).on('tileerror',()=>status.textContent='Tiles unavailable. Manual coordinates still work.').addTo(map);
  map.on('click',e=>{request++;setPoint(e.latlng.lat,e.latlng.lng);});
 }
 function show(){
  if(marker){map.removeLayer(marker);marker=null;}
  const a=Number(lat.value),b=Number(lng.value);
  if(lat.value===''&&lng.value===''){status.textContent='No point selected.';return;}
  if(lat.value===''||lng.value===''||!Number.isFinite(a)||!Number.isFinite(b)||Math.abs(a)>90||Math.abs(b)>180){status.textContent='Enter both valid coordinates.';return;}
  if(map){marker=L.marker([a,b],{draggable:true}).addTo(map);map.setView([a,b],16);marker.on('dragend',()=>{request++;const p=marker.getLatLng();setPoint(p.lat,p.lng);});}
  status.textContent='Selected: '+lat.value+', '+lng.value+'. Confirm the point and written location before saving.';
 }
 function setPoint(a,b){lat.value=a.toFixed(7);lng.value=b.toFixed(7);show();root.dispatchEvent(new Event('location-selected'));}
 [lat,lng].forEach(input=>input.addEventListener('input',()=>{request++;show();}));
 root.querySelector('[data-clear]').addEventListener('click',()=>{request++;lat.value='';lng.value='';show();});
 root.querySelector('[data-locate]').addEventListener('click',()=>{
  if(!navigator.geolocation){status.textContent='Current location unavailable. Click the map or enter coordinates.';return;}
  const current=++request;status.textContent='Requesting your location…';
  navigator.geolocation.getCurrentPosition(p=>{if(current!==request)return;setPoint(p.coords.latitude,p.coords.longitude);status.textContent+=' Estimated accuracy: '+Math.round(p.coords.accuracy)+' meters.'+(p.coords.accuracy>100?' Accuracy is low; adjust the pin.':'');},e=>{if(current!==request)return;status.textContent=({1:'Location permission denied.',2:'Location unavailable.',3:'Location request timed out.'}[e.code]||'Location failed.')+' Click the map or enter coordinates.';},{timeout:10000,maximumAge:0,enableHighAccuracy:true});
 });
 show();
});
