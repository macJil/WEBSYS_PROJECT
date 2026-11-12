(() => {
    const element = document.getElementById('saved-incident-map');
    if (!element) return;
    if (typeof L === 'undefined') { document.getElementById('saved-map-status').textContent='Map files could not load. Saved coordinates and the written location remain available above.'; return; }
    const lat = Number(element.dataset.lat), lng = Number(element.dataset.lng);
    if (!Number.isFinite(lat) || !Number.isFinite(lng) || Math.abs(lat) > 90 || Math.abs(lng) > 180) return;
    const map = L.map(element).setView([lat,lng],16);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom:19, attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).on('tileerror', () => {
        document.getElementById('saved-map-status').textContent = 'Map tiles unavailable. Use the saved coordinates and location description above.';
    }).addTo(map);
    L.circleMarker([lat,lng], {radius:8}).addTo(map);
})();
