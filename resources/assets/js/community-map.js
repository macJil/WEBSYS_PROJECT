(() => {
    const element = document.getElementById("community-map");
    if (!element) return;
    const status = document.getElementById("community-map-status");
    if (typeof L === "undefined") {
        status.textContent =
            "Map files could not load. Refresh the page; record pages remain available.";
        return;
    }
    const admin = element.dataset.admin === "1";
    const home = [Number(element.dataset.lat), Number(element.dataset.lng)];
    const zoom = Number(element.dataset.zoom);
    const map = L.map(element, { minZoom: Number(element.dataset.minZoom || 14) }).setView(
        home,
        zoom,
    );
    L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    })
        .on("tileerror", () => {
            document.getElementById("community-tile-status").textContent =
                "Some background tiles are unavailable. Saved markers can still be selected; refresh when internet is available.";
        })
        .addTo(map);
    const groups = {};
    const types = {
        flood: "Flooding",
        fire: "Fire",
        landslide: "Landslide",
        road_obstruction: "Road obstruction",
        other: "Other emergency",
    };
    const symbols = {
        incidents: ["incident", "⚠"],
        centers: ["center-open", "⌂"],
        relief_locations: ["relief", "▣"],
        households: ["household", "⌂"],
    };
    const boxes = document.querySelectorAll("[data-layer]");
    boxes.forEach((box) => {
        groups[box.dataset.layer] = L.featureGroup().addTo(map);
        box.addEventListener("change", () => {
            const group = groups[box.dataset.layer];
            if (box.checked) group.addTo(map);
            else map.removeLayer(group);
        });
    });
    function line(parent, label, value) {
        if (value === undefined || value === null || value === "") return;
        const p = document.createElement("p");
        p.textContent = label + String(value);
        parent.appendChild(p);
    }
    function popup(layer, row) {
        const node = document.createElement("div");
        node.className = "map-popup";
        let title = "",
            path = "";
        if (layer === "incidents") {
            title = types[row.incident_type] || "Incident";
            if (admin) line(node, "Location: ", row.location_description);
            line(node, "Status: ", row.status);
            line(node, "Reported: ", row.report_date);
            if (!admin) line(node, "", "Saved report location.");
            path = "resources/views/incidents/view.php?id=";
        } else if (layer === "centers") {
            title = row.name;
            line(node, "Address: ", row.address_details);
            line(node, "Status: ", row.status);
            line(node, "Capacity: ", row.capacity);
            line(node, "Occupants: ", row.occupancy);
            line(node, "Available places: ", row.available);
            path = "resources/views/admin/evacuation/center.php?id=";
        } else if (layer === "households") {
            title = row.household_code;
            line(node, "Purok: ", row.purok);
            line(node, "Location: ", row.address_details);
            line(node, "Recorded concern: ", types[row.hazard_type] || row.hazard_type);
            path = "resources/views/admin/household.php?id=";
        } else {
            title = row.name;
            line(node, "Address: ", row.address_details);
            line(node, "Type: ", row.location_type);
            path = "resources/views/admin/relief/location.php?id=";
        }
        const heading = document.createElement("strong");
        heading.textContent = title;
        node.prepend(heading);
        if (admin && Number.isInteger(Number(row.id)) && Number(row.id) > 0) {
            const link = document.createElement("a");
            link.textContent = "Open staff record";
            link.href = element.dataset.base + "/" + path + encodeURIComponent(row.id);
            node.appendChild(link);
        }
        return node;
    }
    document.getElementById("map-home").addEventListener("click", () => map.setView(home, zoom));
    document.getElementById("map-fit").addEventListener("click", () => {
        const bounds = L.latLngBounds([]);
        boxes.forEach((box) => {
            if (box.checked)
                groups[box.dataset.layer].eachLayer((marker) => bounds.extend(marker.getLatLng()));
        });
        if (bounds.isValid()) map.fitBounds(bounds, { padding: [30, 30], maxZoom: 16 });
        else
            status.textContent =
                "No markers in the selected layers. Enable a layer or return to the demo area.";
    });
    fetch(element.dataset.url, { credentials: "same-origin", cache: "no-store" })
        .then((response) => {
            if (!response.ok)
                throw new Error(
                    response.status === 401
                        ? "Please sign in again to view saved locations."
                        : "Saved locations could not be loaded. Please refresh.",
                );
            return response.json();
        })
        .then((data) => {
            let total = 0;
            boxes.forEach((box) => {
                const layer = box.dataset.layer,
                    group = groups[layer];
                let count = 0;
                (data[layer] || []).forEach((row) => {
                    const latValue = row.latitude;
                    const lngValue = row.longitude;
                    const lat = Number(latValue),
                        lng = Number(lngValue);
                    if (
                        latValue === null ||
                        lngValue === null ||
                        latValue === "" ||
                        lngValue === "" ||
                        !Number.isFinite(lat) ||
                        !Number.isFinite(lng) ||
                        Math.abs(lat) > 90 ||
                        Math.abs(lng) > 180
                    )
                        return;
                    let [color, symbol] = symbols[layer];
                    if (layer === "centers" && row.status === "closed") color = "center-closed";
                    // Only fixed icon constants enter HTML; database strings use textContent in popup().
                    const icon = L.divIcon({
                        className: "map-marker-icon",
                        html:
                            '<span class="map-symbol ' +
                            color +
                            '" aria-hidden="true">' +
                            symbol +
                            "</span>",
                        iconSize: [28, 28],
                        iconAnchor: [14, 14],
                    });
                    L.marker([lat, lng], {
                        icon,
                        title: layer === "centers" ? "Evacuation center: " + row.status : layer,
                        keyboard: true,
                    })
                        .bindPopup(popup(layer, row))
                        .addTo(group);
                    count++;
                });
                document.getElementById("count-" + layer).textContent = "(" + count + ")";
                total += count;
            });
            status.textContent = total
                ? total + " saved locations loaded. Select a marker for details."
                : admin
                  ? "No active saved locations have map points yet. Add a point using a record form."
                  : "No approved locations with map points are published yet. Your submissions remain in My reports.";
            document.getElementById("map-fit").disabled = false;
        })
        .catch((error) => {
            status.textContent =
                error.message || "Saved locations could not be loaded. Please refresh.";
        });
})();
