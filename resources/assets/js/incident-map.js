(() => {
    const element = document.getElementById('incident-map');
    if (!element) return;
    const status = document.getElementById('map-status');
    const lat = document.getElementById('latitude');
    const lng = document.getElementById('longitude');
    const description = document.querySelector('[name="location_description"]');
    const search = document.getElementById('location-search');
    const results = document.getElementById('location-results');
    let map = null, marker = null;
    if (typeof L !== 'undefined') {
        map = L.map(element).setView([Number(element.dataset.lat), Number(element.dataset.lng)], Number(element.dataset.zoom));
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom:19, attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).on('tileerror', () => { status.textContent = 'Map tiles unavailable. Demo search and manual coordinates still work.'; }).addTo(map);
        map.on('click', event => {
            select(event.latlng.lat, event.latlng.lng);
            status.textContent = 'Point selected. Update the written location to match this point. A pin is not a hazard assessment.';
        });
    }
    function showPoint() {
        if (map && marker) { map.removeLayer(marker); marker = null; }
        if (lat.value.trim() === '' && lng.value.trim() === '') { status.textContent = 'No point selected. Written location can still be submitted.'; return; }
        const a = Number(lat.value), b = Number(lng.value);
        if (lat.value.trim() === '' || lng.value.trim() === '' || !Number.isFinite(a) || !Number.isFinite(b) || Math.abs(a)>90 || Math.abs(b)>180) {
            status.textContent = 'Enter both valid coordinates, or clear both.'; return;
        }
        if (map) {
            marker = L.circleMarker([a,b], {radius:8,color:'#245b8a',fillOpacity:0.8}).addTo(map);
            map.setView([a,b],16);
        }
        status.textContent = 'Selected coordinates: ' + lat.value + ', ' + lng.value + '. Check the written location before submitting.';
    }
    function select(a,b) { lat.value=a.toFixed(7); lng.value=b.toFixed(7); showPoint(); }
    function runSearch() {
        results.replaceChildren();
        const term=search.value.trim().toLowerCase();
        if (!term) { results.textContent='Enter a fictional purok or landmark, such as “Sample A”.'; return; }
        const matches=(window.demoLocations || []).filter(place => (place.name+' '+place.description).toLowerCase().includes(term));
        if (!matches.length) { results.textContent='No demo locations found. Use Sample A, Sample B, Community Hall, or enter coordinates manually.'; return; }
        matches.forEach(place => {
            const button=document.createElement('button');
            button.type='button'; button.className='btn btn-outline-secondary d-block my-2';
            button.textContent='Use '+place.name;
            button.addEventListener('click', () => {
                select(place.lat,place.lng); description.value=place.description;
                status.textContent='Demo location selected and written description filled. This is a fictional example, not an approved real location.';
            });
            results.appendChild(button);
        });
    }
    document.getElementById('search-location').addEventListener('click',runSearch);
    search.addEventListener('keydown',event => { if (event.key==='Enter') { event.preventDefault(); runSearch(); } });
    lat.addEventListener('input',showPoint); lng.addEventListener('input',showPoint);
    document.getElementById('clear-location').addEventListener('click', () => { lat.value=''; lng.value=''; showPoint(); });
    showPoint();
})();
