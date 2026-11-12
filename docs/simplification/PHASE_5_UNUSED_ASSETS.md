# Phase 5 — Confirmed unused code and assets

Removed the uncalled `Controller::results_checker()` and `content_checker()` helpers. Scoped PHP/HTML/JavaScript reference checks found their definitions only.

Removed 46 asset files: unused Bootstrap builds (grid/reboot/utilities/RTL/ESM and duplicate unminified or non-bundle versions and their maps), old jQuery, `app.js`, `user-weather.js`, `weather.js`, `bulk-select.js`, and `style.css`. Current PHP pages referenced none of those removed files. The exact removal list appears below.

Kept `bootstrap.min.css` and `bootstrap.bundle.min.js`, with their source maps, and all bundled Leaflet files/images/license. Kept summary print CSS/JS, record-selection, saved-point, shared map, picker, and address-suggestion scripts. The older `demo-locations.js` and `incident-map.js` remain because `tests/phase11_location.js` directly uses them. Compatibility page aliases also remain because current links still use them.

Checks passed: PHP lint for changed helpers, current map rendering and picker tests (with/without Leaflet), older location-map tests, and 25 address-suggestion cases. Browser checks against the existing isolated fictional fixture confirmed staff login/navigation, Select all with selected-count update, 12 loaded tiles and four saved facility markers, and picker click populating latitude/longitude. No report or deletion was submitted. The full MySQL workflow suite was not rerun for this asset-only cleanup.

## Removed files
- ` resources/assets/css/bootstrap-grid.rtl.min.css`
- ` resources/assets/css/bootstrap-utilities.rtl.css`
- ` resources/assets/css/bootstrap-grid.rtl.min.css.map`
- ` resources/assets/css/bootstrap.rtl.min.css.map`
- ` resources/assets/css/bootstrap.rtl.css.map`
- ` resources/assets/css/bootstrap-reboot.rtl.css`
- ` resources/assets/css/bootstrap-reboot.min.css.map`
- ` resources/assets/css/bootstrap-utilities.css`
- ` resources/assets/css/bootstrap-reboot.rtl.min.css.map`
- ` resources/assets/css/bootstrap.css`
- ` resources/assets/css/bootstrap-utilities.min.css.map`
- ` resources/assets/css/bootstrap-grid.css.map`
- ` resources/assets/css/bootstrap-grid.min.css`
- ` resources/assets/css/bootstrap.rtl.min.css`
- ` resources/assets/css/bootstrap.css.map`
- ` resources/assets/css/bootstrap-grid.rtl.css.map`
- ` resources/assets/css/bootstrap-reboot.min.css`
- ` resources/assets/css/bootstrap-utilities.rtl.min.css.map`
- ` resources/assets/css/bootstrap.rtl.css`
- ` resources/assets/css/bootstrap-utilities.rtl.css.map`
- ` resources/assets/css/bootstrap-reboot.rtl.css.map`
- ` resources/assets/css/bootstrap-reboot.css`
- ` resources/assets/css/bootstrap-utilities.min.css`
- ` resources/assets/css/bootstrap-grid.css`
- ` resources/assets/css/bootstrap-utilities.css.map`
- ` resources/assets/css/bootstrap-utilities.rtl.min.css`
- ` resources/assets/css/bootstrap-reboot.rtl.min.css`
- ` resources/assets/css/bootstrap-grid.min.css.map`
- ` resources/assets/css/bootstrap-grid.rtl.css`
- ` resources/assets/css/bootstrap-reboot.css.map`
- ` resources/assets/js/bootstrap.esm.min.js`
- ` resources/assets/js/bootstrap.esm.js`
- ` resources/assets/js/bootstrap.bundle.js`
- ` resources/assets/js/bootstrap.bundle.js.map`
- ` resources/assets/js/bootstrap.esm.js.map`
- ` resources/assets/js/bootstrap.js`
- ` resources/assets/js/bootstrap.min.js`
- ` resources/assets/js/bootstrap.esm.min.js.map`
- ` resources/assets/js/bootstrap.js.map`
- ` resources/assets/js/bootstrap.min.js.map`
- ` resources/assets/js/vendor/jquery.min.js`
- ` resources/assets/js/app.js`
- ` resources/assets/js/user-weather.js`
- ` resources/assets/js/weather.js`
- ` resources/assets/js/bulk-select.js`
- ` resources/assets/css/style.css`