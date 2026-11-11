(() => {
    const element = document.getElementById('incident-map');
    const status = document.getElementById('map-status');
    const lat = document.getElementById('latitude');
    const lng = document.getElementById('longitude');
    if (!element || typeof L === 'undefined') return;
    const map = L.map(element).setView([Number(element.dataset.lat), Number(element.dataset.lng)], Number(element.dataset.zoom));
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).on('tileerror', () => { status.textContent = 'Map tiles unavailable. Enter coordinates or describe the location above.'; }).addTo(map);
    let marker;
    function showPoint() {
        if (marker) { map.removeLayer(marker); marker = null; }
        if (lat.value === '' || lng.value === '') return;
        const a = Number(lat.value), b = Number(lng.value);
        if (!Number.isFinite(a) || !Number.isFinite(b) || Math.abs(a) > 90 || Math.abs(b) > 180) return;
        marker = L.circleMarker([a,b], {radius:8}).addTo(map);
        map.setView([a,b], 16);
    }
    function select(a,b) {
        lat.value = a.toFixed(7); lng.value = b.toFixed(7); showPoint();
        status.textContent = 'Pin selected. Check that it matches the incident location.';
    }
    map.on('click', event => select(event.latlng.lat, event.latlng.lng));
    lat.addEventListener('change', showPoint); lng.addEventListener('change', showPoint);
    document.getElementById('clear-location').addEventListener('click', () => { lat.value = ''; lng.value = ''; showPoint(); });
    document.getElementById('locate').addEventListener('click', () => {
        if (!navigator.geolocation) { status.textContent = 'Location unavailable. Click the map or enter coordinates.'; return; }
        navigator.geolocation.getCurrentPosition(p => select(p.coords.latitude, p.coords.longitude),
            () => { status.textContent = 'Could not get your location. Click the map or enter coordinates.'; }, {timeout:10000});
    });
    showPoint();
})();
