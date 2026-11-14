document.querySelectorAll(".location-picker").forEach((root) => {
    if (root.dataset.ready) return;
    root.dataset.ready = "1";
    const lat = root.querySelector('[data-coordinate="latitude"]'),
        lng = root.querySelector('[data-coordinate="longitude"]'),
        status = root.querySelector("[data-picker-status]"),
        el = root.querySelector("[data-picker-map]");
    const form = root.closest("form");
    const address = form.querySelector('[name="location_description"], [name="address_details"]');
    const addressStatus = root.querySelector("[data-address-status]");
    let addressTimer = null;
    let addressRequest = null;
    let addressVersion = 0;
    let lastLookup = 0;
    let lookupTimes = [];
    const addressCache = new Map();

    function cancelAddress() {
        addressVersion++;
        clearTimeout(addressTimer);
        if (addressRequest) addressRequest.abort();
    }

    // Only an explicit point choice calls this; displaying a saved pin does not.
    function suggestAddress() {
        cancelAddress();
        if (!address || !addressStatus || !root.dataset.addressUrl) return;
        if (typeof fetch === "undefined" || typeof AbortController === "undefined") {
            addressStatus.textContent = "Address lookup unavailable. Enter the address manually.";
            return;
        }
        const version = addressVersion;
        const previousText = address.value;
        const latitude = lat.value;
        const longitude = lng.value;
        const key = latitude + "," + longitude;
        addressStatus.textContent = "Looking up an address…";
        addressTimer = setTimeout(async function () {
            let timeout = null;
            try {
                let label = addressCache.get(key);
                if (!label) {
                    const now = Date.now();
                    lookupTimes = lookupTimes.filter(function (time) { return now - time < 60000; });
                    if (lookupTimes.length >= 10) {
                        addressStatus.textContent = "Address lookup limit reached. Enter the address manually or try again in a minute.";
                        return;
                    }
                    lookupTimes.push(now);
                    lastLookup = now;
                    const controller = new AbortController();
                    addressRequest = controller;
                    timeout = setTimeout(function () { controller.abort(); }, 8000);
                    const url = new URL(root.dataset.addressUrl, window.location.href);
                    url.searchParams.set("lat", latitude);
                    url.searchParams.set("lon", longitude);
                    url.searchParams.set("limit", "1");
                    url.searchParams.set("radius", "1");
                    const response = await fetch(url, {
                        signal: controller.signal,
                        credentials: url.origin === window.location.origin ? "same-origin" : "omit",
                        referrerPolicy: "no-referrer"
                    });
                    if (!response.ok) throw new Error("Address lookup failed");
                    const data = await response.json();
                    const properties = data.features && data.features[0] && data.features[0].properties;
                    if (!properties) throw new Error("No address found");
                    const parts = [];
                    for (const field of ["street", "name", "district", "city", "county", "state"]) {
                        const text = properties[field];
                        if (typeof text === "string" && text.trim() && !parts.includes(text.trim())) {
                            parts.push(text.trim());
                        }
                    }
                    label = parts.join(", ");
                    if (!label) throw new Error("No readable address");
                    // All current written-location columns accept up to 255 bytes.
                    if (new TextEncoder().encode(label).length > 255) throw new Error("Address too long");
                    if (controller.signal.aborted) throw new Error("Lookup cancelled");
                    addressCache.set(key, label);
                }
                if (version !== addressVersion || address.value !== previousText) return;
                address.value = label;
                addressStatus.textContent = "Address suggested; please confirm.";
            } catch (error) {
                if (version === addressVersion) {
                    addressStatus.textContent = "Address unavailable. Keep or enter the address manually.";
                }
            } finally {
                clearTimeout(timeout);
            }
        }, Math.max(700, 2000 - (Date.now() - lastLookup)));
    }

    if (address) {
        address.addEventListener("input", function () {
            cancelAddress();
            if (addressStatus) addressStatus.textContent = "Your written address is kept. Confirm it before saving.";
        });
    }
    form.addEventListener("submit", cancelAddress);
    let map = null,
        marker = null,
        request = 0;
    const availability = root.querySelector("[data-picker-availability]");
    if (typeof L === "undefined" && availability)
        availability.textContent =
            "Map files could not load. Refresh the page or type the location and coordinates manually.";
    if (typeof L !== "undefined") {
        map = L.map(el, { minZoom: Number(el.dataset.minZoom || 14) }).setView(
            [Number(el.dataset.lat), Number(el.dataset.lng)],
            Number(el.dataset.zoom),
        );
        L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 19,
            attribution:
                '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        })
            .on(
                "tileerror",
                () => (status.textContent = "Tiles unavailable. Manual coordinates still work."),
            )
            .addTo(map);
        map.on("click", (e) => {
            request++;
            setPoint(e.latlng.lat, e.latlng.lng);
        });
    }
    function show() {
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }
        const a = Number(lat.value),
            b = Number(lng.value);
        if (lat.value === "" && lng.value === "") {
            status.textContent = "No point selected.";
            return;
        }
        if (
            lat.value === "" ||
            lng.value === "" ||
            !Number.isFinite(a) ||
            !Number.isFinite(b) ||
            Math.abs(a) > 90 ||
            Math.abs(b) > 180
        ) {
            status.textContent = "Enter both valid coordinates.";
            return;
        }
        if (map) {
            marker = L.marker([a, b], { draggable: true }).addTo(map);
            map.setView([a, b], 16);
            marker.on("dragend", () => {
                request++;
                const p = marker.getLatLng();
                setPoint(p.lat, p.lng);
            });
        }
        status.textContent =
            "Selected: " +
            lat.value +
            ", " +
            lng.value +
            ". Confirm the point and written location before saving.";
    }
    function setPoint(a, b) {
        lat.value = a.toFixed(7);
        lng.value = b.toFixed(7);
        show();
        suggestAddress();
    }
    [lat, lng].forEach((input) =>
        input.addEventListener("input", () => {
            request++;
            cancelAddress();
            if (addressStatus) addressStatus.textContent = "Confirm the written address for these coordinates.";
            show();
        }),
    );
    root.querySelector("[data-clear]").addEventListener("click", () => {
        request++;
        cancelAddress();
        if (addressStatus) addressStatus.textContent = "Pin cleared. Written address kept.";
        lat.value = "";
        lng.value = "";
        show();
    });
    root.querySelector("[data-locate]").addEventListener("click", () => {
        cancelAddress();
        if (addressStatus) addressStatus.textContent = "";
        if (!navigator.geolocation) {
            status.textContent =
                "Current location unavailable. Click the map or enter coordinates.";
            return;
        }
        const current = ++request;
        const addressBeforeLocation = addressVersion;
        status.textContent = "Requesting your location…";
        navigator.geolocation.getCurrentPosition(
            (p) => {
                if (current !== request) return;
                const addressWasEdited = addressBeforeLocation !== addressVersion;
                setPoint(p.coords.latitude, p.coords.longitude);
                if (addressWasEdited) {
                    cancelAddress();
                    if (addressStatus) addressStatus.textContent = "Your written address is kept. Confirm it before saving.";
                }
                status.textContent +=
                    " Estimated accuracy: " +
                    Math.round(p.coords.accuracy) +
                    " meters." +
                    (p.coords.accuracy > 100 ? " Accuracy is low; adjust the pin." : "");
            },
            (e) => {
                if (current !== request) return;
                status.textContent =
                    ({
                        1: "Location permission denied.",
                        2: "Location unavailable.",
                        3: "Location request timed out.",
                    }[e.code] || "Location failed.") + " Click the map or enter coordinates.";
            },
            { timeout: 10000, maximumAge: 0, enableHighAccuracy: true },
        );
    });
    show();
});
