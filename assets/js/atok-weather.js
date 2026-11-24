(async function () {
    const panel = document.getElementById("atok-forecast");
    if (!panel) return;

    const endpoint = "https://api.open-meteo.com/v1/forecast?latitude=16.62366&longitude=120.76422" +
        "&daily=weather_code,temperature_2m_min,temperature_2m_max,precipitation_probability_max&timezone=Asia%2FManila&forecast_days=1";
    const key = "atok-sayangan-daily-v1";
    const today = new Intl.DateTimeFormat("en-CA", {
        timeZone: "Asia/Manila", year: "numeric", month: "2-digit", day: "2-digit"
    }).format(new Date());

    function weatherStatus(code) {
        if (code === 0) return "Clear sky";
        if ([1, 2, 3].includes(code)) return ["Mainly clear", "Partly cloudy", "Overcast"][code - 1];
        if ([45, 48].includes(code)) return "Fog";
        if ([51, 53, 55, 56, 57].includes(code)) return "Drizzle";
        if ([61, 63, 65, 66, 67].includes(code)) return "Rain";
        if ([80, 81, 82].includes(code)) return "Rain showers";
        if ([95, 96, 99].includes(code)) return "Thunderstorms";
        if ([71, 73, 75, 77, 85, 86].includes(code)) return "Snow";
        return "Daily forecast";
    }

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
        panel.textContent = weatherStatus(day.weather_code?.[0]) + " — forecast for " + today + ". Temperatures range from " + low + " to " + high + " °C. The highest hourly chance of rain is " + rain +
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
        panel.textContent = "Forecast unavailable. Check the official advisories below.";
        document.getElementById("weather-links").open = true;
    } finally {
        clearTimeout(timer);
    }
})();
