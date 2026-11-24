<section class="panel mb-4" aria-labelledby="weather-heading">
    <h2 id="weather-heading" class="h5">Sayangan daily forecast</h2>
    <p class="small mb-1">Paoay, Atok, Benguet · <a href="https://open-meteo.com/">Open-Meteo</a></p>
    <p id="atok-forecast" role="status">Forecast unavailable.</p>
    <details id="weather-links">
        <summary>Weather advisories and sources</summary>
        <ul class="mt-2">
            <li><a href="https://bagong.pagasa.dost.gov.ph/regional-forecast/nlprsd">PAGASA Northern Luzon advisories</a></li>
            <li><a href="https://www.pagasa.dost.gov.ph/flood">PAGASA flood advisories</a></li>
            <li>Forecast by <a href="https://open-meteo.com/">Open-Meteo</a> · <a href="https://creativecommons.org/licenses/by/4.0/">CC BY 4.0</a></li>
        </ul>
    </details>
</section>
<script src="<?= e(url('assets/js/atok-weather.js')) ?>" defer></script>
