(async function () {
    const panel = document.getElementById("atok-forecast");
    if (!panel) return;

    const endpoint = "https://api.open-meteo.com/v1/forecast?latitude=16.60&longitude=120.71" +
        "&daily=temperature_2m_min,temperature_2m_max,precipitation_probability_max&timezone=Asia%2FManila&forecast_days=1";
    const key = "bayanready-atok-weather-v1";
    const today = new Intl.DateTimeFormat("en-CA", {
        timeZone: "Asia/Manila", year: "numeric", month: "2-digit", day: "2-digit"
    }).format(new Date());

    function display(entry) {
        const day = entry.daily;
        if (day?.time?.[0] !== today) return false;
        const low = day.temperature_2m_min?.[0];
        const high = day.temperature_2m_max?.[0];
        const rain = day.precipitation_probability_max?.[0];
        if (![low, high, rain].every(Number.isFinite) || low > high || rain < 0 || rain > 100) return false;
        const time = new Date(entry.fetched).toLocaleTimeString("en-PH", {
            timeZone: "Asia/Manila", hour: "numeric", minute: "2-digit"
        });
        panel.textContent = today + " · " + low + "–" + high + " °C · Peak rain probability " + rain +
            "%. Retrieved " + time + " Philippine time.";
        return true;
    }

    try {
        const cached = JSON.parse(localStorage.getItem(key));
        const age = Date.now() - cached?.fetched;
        if (age >= 0 && age < 30 * 60 * 1000 && display(cached)) return;
    } catch {
        // Forecasts also work when browser storage is unavailable.
    }

    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), 5000);
    panel.textContent = "Loading forecast…";
    try {
        const response = await fetch(endpoint, {
            signal: controller.signal, credentials: "omit", referrerPolicy: "no-referrer"
        });
        if (!response.ok) throw new Error();
        const data = await response.json();
        const entry = { daily: data.daily, fetched: Date.now() };
        if (!display(entry)) throw new Error();
        try {
            localStorage.setItem(key, JSON.stringify(entry));
        } catch {
            // An uncached forecast is still displayed.
        }
    } catch {
        panel.textContent = "Forecast unavailable. Check the PAGASA links below for advisories.";
    } finally {
        clearTimeout(timer);
    }
})();
