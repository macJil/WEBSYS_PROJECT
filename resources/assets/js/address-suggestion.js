// One suggestion helper for all shared pickers. No lookup on page load or saved-pin display.
document.querySelectorAll(".location-picker").forEach((root) => {
    if (root.dataset.addressReady) return;
    root.dataset.addressReady = "1";
    const enabled = root.querySelector("[data-address-enabled]");
    const address = root
        .closest("form")
        .querySelector('[name="location_description"], [name="address_details"]');
    if (!enabled || !address) return;
    const lat = root.querySelector('[data-coordinate="latitude"]'),
        lng = root.querySelector('[data-coordinate="longitude"]');
    const status = root.querySelector("[data-address-status]"),
        use = root.querySelector("[data-use-address]");
    let timer = null,
        controller = null,
        generation = 0,
        manual = address.value.trim() !== "",
        suggestion = "",
        lastRequest = 0;
    const cache = new Map();
    let requests = [];
    function cancel() {
        generation++;
        clearTimeout(timer);
        if (controller) controller.abort();
        controller = null;
        suggestion = "";
        use.hidden = true;
    }
    function valid() {
        return (
            lat.value !== "" &&
            lng.value !== "" &&
            Number.isFinite(Number(lat.value)) &&
            Number.isFinite(Number(lng.value)) &&
            Math.abs(Number(lat.value)) <= 90 &&
            Math.abs(Number(lng.value)) <= 180
        );
    }
    function label(data) {
        const p = data.features?.[0]?.properties;
        if (!p) return "";
        // Only provider-returned names; no invented house number or forced barangay.
        const parts = [p.street || p.name, p.locality || p.district, p.city || p.town || p.village];
        const clean = [
            ...new Set(parts.filter((v) => typeof v === "string" && v.trim()).map((v) => v.trim())),
        ];
        const text = clean.join(", ");
        return text.length <= 255 ? text : "";
    }
    function display(text) {
        suggestion = text;
        if (!manual) {
            address.value = text;
            status.textContent = "Address suggested; please confirm: " + text;
        } else {
            status.textContent =
                "Address suggested; please confirm: " + text + ". Your written location was kept.";
            use.hidden = false;
        }
    }
    async function lookup(version, a, b) {
        if (version !== generation || !enabled.checked) return;
        const key = a + "," + b;
        if (cache.has(key)) {
            display(cache.get(key));
            return;
        }
        const now = Date.now();
        requests = requests.filter((time) => now - time < 60000);
        if (requests.length >= 10) {
            status.textContent =
                "Address lookup limit reached. Wait a minute and choose a point again, or type the address.";
            return;
        }
        const url = new URL(root.dataset.addressUrl);
        if (url.protocol !== "https:") {
            status.textContent = "Address lookup unavailable. Type the address manually.";
            return;
        }
        url.searchParams.set("lat", a);
        url.searchParams.set("lon", b);
        url.searchParams.set("limit", "1");
        url.searchParams.set("lang", "en");
        lastRequest = now;
        requests.push(now);
        controller = new AbortController();
        const active = controller;
        const timeout = setTimeout(() => active.abort(), 8000);
        status.textContent = "Finding a nearby address…";
        try {
            const response = await fetch(url.toString(), {
                signal: active.signal,
                credentials: "omit",
                referrerPolicy: "no-referrer",
            });
            if (!response.ok) throw new Error("Lookup unavailable");
            const text = label(await response.json());
            if (version !== generation || !enabled.checked) return;
            if (!text) throw new Error("No readable address");
            if (cache.size >= 30) cache.delete(cache.keys().next().value);
            cache.set(key, text);
            display(text);
        } catch (error) {
            if (version === generation)
                status.textContent =
                    "Address lookup unavailable or no nearby address found. Keep the pin and type the address manually.";
        } finally {
            clearTimeout(timeout);
            if (controller === active) controller = null;
        }
    }
    function choose() {
        cancel();
        if (!enabled.checked) {
            status.textContent = "Address suggestions are off. Type the address manually.";
            return;
        }
        if (!valid()) {
            status.textContent =
                "Choose a valid point to suggest an address. Written location is kept.";
            return;
        }
        if (typeof fetch !== "function" || typeof AbortController === "undefined") {
            status.textContent = "Address lookup unavailable. Type the address manually.";
            return;
        }
        const version = generation,
            a = lat.value,
            b = lng.value;
        status.textContent = "Waiting for your point selection…";
        timer = setTimeout(
            () =>
                lookup(version, a, b).catch(() => {
                    if (version === generation)
                        status.textContent =
                            "Address lookup unavailable. Type the address manually.";
                }),
            Math.max(700, 2000 - (Date.now() - lastRequest)),
        );
    }
    address.addEventListener("input", () => {
        manual = true;
    });
    root.addEventListener("location-selected", choose);
    [lat, lng].forEach((input) => input.addEventListener("input", choose));
    root.querySelector("[data-clear]").addEventListener("click", () => {
        cancel();
        status.textContent = "Pin cleared. Written location is kept.";
    });
    root.querySelector("[data-locate]").addEventListener("click", () => {
        cancel();
        status.textContent = "Waiting for your location choice. You can still type the address.";
    });
    enabled.addEventListener("change", () => {
        cancel();
        status.textContent = enabled.checked
            ? "Choose a point to request an address."
            : "Address suggestions are off. Type the address manually.";
    });
    use.addEventListener("click", () => {
        if (!suggestion) return;
        address.value = suggestion;
        manual = false;
        use.hidden = true;
        status.textContent = "Address suggested; please confirm: " + suggestion;
    });
    root.closest("form").addEventListener("submit", cancel);
});
