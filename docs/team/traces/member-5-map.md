# Member 5 — Map request, safe JSON and marker rendering

These are study notes for the pre-formatting source at commit 954885b, not application changes or a claim that a live demonstration has passed. Use fictional examples. The complete primary request is traced below, with exact source excerpts and explanations. Source links are relative to the repository and include line anchors. The main presentation should show only the marked 60–90 second segment; use the rest to prepare for questions.

All PHP save paths run on the server through MAMP. The browser sends form values, not PHP objects. A successful POST redirects to a new GET; validation errors remain on the form. `[]` means an empty array, `$this` means the current object, `->` accesses an object, `?int` accepts integer or null, and `: array` declares a returned array. `prepare()` creates a SQL statement and `execute()` supplies its values.
## Focus and example

Own shared map/location behavior, dashboards, announcements, summaries and shared interface integration. Present the map code on slide 23. This is a read flow, so it ends in rendered markers rather than a save redirect. Example: one pending incident and one verified incident both have valid coordinates. The shared map response includes the eligible verified record, not the pending one.

### Complete execution order

GET map.php; check login; output configuration and endpoint URL; load Leaflet and community-map.js. JavaScript fetches data.php with same-origin credentials. The endpoint enforces session and GET, calls controller/model, and encodes safe rows as JSON. JavaScript validates points, creates markers and writes popup fields as text. Layer controls affect display only.

## 1. Guard the map page

Source: [resources/views/maps/map.php, lines 1–10](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/resources/views/maps/map.php#L1). Exact excerpt, not pseudocode.

```php
<?php
require_once __DIR__ . '/../../../app/helpers.php';
session_checker();
requireRole($_SESSION['ROLE']);
header('Cache-Control: private, no-store');
$admin = $_SESSION['ROLE'] === 'admin';
$pageTitle = 'Community map';
require __DIR__ . '/../components/header.php';
?>
<link rel="stylesheet" href="<?= e(url('resources/assets/vendor/leaflet/leaflet.css')) ?>" />
```

The page checks session/role before rendering. Its admin flag controls interface behavior, not a broader private-data response. The actual data endpoint guards access separately, because someone can request it without opening this page first.

## 2. Load endpoint and map configuration into the page

Source: [resources/views/maps/map.php, lines 65–80](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/resources/views/maps/map.php#L65). Exact excerpt, not pseudocode.

```php
            data-min-zoom="<?= e($settings['map_min_zoom']) ?>"
        >
        </div>
        <p id="community-tile-status" role="status" class="small"
            >Map tiles require internet. The background map does not confirm the accuracy of a
            reported point.</p
        >
        <noscript
            >Enable JavaScript to explore this map. Your regular record pages remain available
            through navigation.</noscript
        >
    </div>
</div>
<script src="<?= e(url('resources/assets/vendor/leaflet/leaflet.js')) ?>"></script>
<script src="<?= e(url('resources/assets/js/community-map.js')) ?>"></script>
<?php require __DIR__ . '/../components/footer.php'; ?>
```

The escaped `data-url` points to a direct PHP JSON page. `data-lat`, `data-lng` and zoom values configure the opening view. Leaflet and the shared script load afterwards. These are browser configuration values, not database credentials. No boundary polygon is enforced.

## 3. JSON endpoint guards and response

Source: [resources/views/maps/data.php, lines 1–22](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/resources/views/maps/data.php#L1). Exact excerpt, not pseudocode.

```php
<?php
require_once __DIR__ . '/../../../app/controllers/MapController.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
if (empty($_SESSION['ID']) || !in_array($_SESSION['ROLE'] ?? '', ['admin','resident'], true)) {
    http_response_code(401);
    echo json_encode(['error' => 'Sign in to view map data.']);
    exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['error' => 'Use GET.']);
    exit;
}
try {
    echo json_encode((new MapController())->mapData(), JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['error' => 'Map data is unavailable.']);
}
```

The endpoint sets JSON and no-store headers. Missing/invalid account role returns 401 JSON rather than redirecting to an HTML login page. A non-GET method returns 405 and `Allow: GET`. The successful branch encodes the model result. A caught failure returns 500 with a generic message. The browser checks the HTTP status before using the response.

## 4. Controller delegates to the map model

Source: [app/controllers/MapController.php, lines 6–11](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/app/controllers/MapController.php#L6). Exact excerpt, not pseudocode.

```php
    public function mapData(): array
    {
        session_checker();
        $model = new MapModel();
        return $model->publicMap();
    }
```

The controller requires a session and obtains a MapModel object. Both staff and residents use `publicMap()`. Staff-only popup links in JavaScript do not cause extra private fields to be sent. Detailed staff records apply their own guards when opened.

## 5. Select only intended map fields and rows

Source: [app/models/Map.php, lines 19–27](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/app/models/Map.php#L19). Exact excerpt, not pseudocode.

```php
    public function publicMap(): array
    {
        // Explicit allowlists: never select private incident fields for the resident response.
        return [
            'incidents' => $this->rows("SELECT id,incident_type,latitude,longitude,status,DATE(created_at) AS report_date FROM incident_reports WHERE archived_at IS NULL AND status IN ('verified','responding','resolved') AND " . $this->point('latitude', 'longitude')),
            'centers' => $this->centers(),
            'relief_locations' => $this->rows("SELECT id,name,address_details,latitude,longitude,location_type FROM relief_locations WHERE archived_at IS NULL AND location_type IN ('distribution','both') AND " . $this->point('latitude', 'longitude'))
        ];
    }
```

The incident SELECT explicitly names ID, type, coordinates, status and report date. Names, phones, descriptions, household links and images are absent from this JSON query. Eligibility requires non-archived state, verified/responding/resolved status and a valid point. Center queries allow open/non-archived centers with points. Relief locations require non-archived distribution/both type and points.

This protects the omitted personal fields, but the selected incident coordinates themselves are shared. Do not call them automatically anonymized or approximate. A future stakeholder must confirm that policy. No map point means no marker, not deletion from ordinary records or all dashboard totals.

## 6. Coordinate condition

Source: [app/models/Map.php, lines 15–18](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/app/models/Map.php#L15). Exact excerpt, not pseudocode.

```php
    private function point(string $lat, string $lng): string
    {
        return "$lat IS NOT NULL AND $lng IS NOT NULL AND $lat BETWEEN -90 AND 90 AND $lng BETWEEN -180 AND 180";
    }
```

The helper builds SQL from fixed internal column names supplied by this class. Latitude/longitude must both exist and be in their allowed ranges. Zero is valid and is not treated as missing. This checks numeric geography ranges, not an Atok boundary or whether a real event happened.

## 7. Center markers include live capacity

Source: [app/models/Map.php, lines 34–46](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/app/models/Map.php#L34). Exact excerpt, not pseudocode.

```php
    private function centers(): array
    {
        $centers = $this->rows("SELECT c.id, c.name, c.address_details, c.latitude, c.longitude,
            c.status, c.capacity, COUNT(e.id) AS occupancy
            FROM evacuation_centers c
            LEFT JOIN evacuation_records e ON e.center_id = c.id AND e.checked_out_at IS NULL
            WHERE c.archived_at IS NULL AND c.status = 'open' AND " . $this->point('c.latitude', 'c.longitude') . "
            GROUP BY c.id, c.name, c.address_details, c.latitude, c.longitude, c.status, c.capacity");
        foreach ($centers as $index => $center) {
            $centers[$index]['available'] = availableCapacity($center);
        }
        return $centers;
    }
```

Only active stays join into occupancy. Grouping produces one result per center rather than one marker per evacuee. Each result gets the shared available-capacity calculation. This marker query excludes no-point centers; ordinary center lists/dashboard totals may still include them, so marker counts need not equal dashboard counts.

## 8. Full browser consumer: fetch, validate and render

Source: [resources/assets/js/community-map.js, lines 1–181](https://github.com/macJil/WEBSYS_PROJECT/blob/954885b/resources/assets/js/community-map.js#L1). Exact excerpt, not pseudocode.

```javascript
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
    const minimumZoom = Number(element.dataset.minZoom || 11);
    const map = L.map(element, { minZoom: minimumZoom }).setView(
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
            line(node, "Status: ", row.status);
            line(node, "Reported: ", row.report_date);
            line(node, "", "Saved report location.");
            path = "resources/views/incidents/view.php?id=";
        } else if (layer === "centers") {
            title = row.name;
            line(node, "Address: ", row.address_details);
            line(node, "Status: ", row.status);
            line(node, "Capacity: ", row.capacity);
            line(node, "Occupants: ", row.occupancy);
            line(node, "Available capacity (people): ", row.available);
            path = "resources/views/admin/evacuation/center.php?id=";
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
    document.getElementById("map-home").addEventListener("click", () => {
        map.setMinZoom(minimumZoom);
        map.setView(home, zoom);
        status.textContent = "Atok demo view. Use Show all locations to see saved points outside this view.";
    });
    document.getElementById("map-fit").addEventListener("click", () => {
        const bounds = L.latLngBounds([]);
        boxes.forEach((box) => {
            box.checked = true;
            groups[box.dataset.layer].addTo(map);
            groups[box.dataset.layer].eachLayer((marker) => bounds.extend(marker.getLatLng()));
        });
        if (bounds.isValid()) {
            // Saved points can be anywhere; fit them without imposing a demo boundary.
            map.setMinZoom(1);
            map.fitBounds(bounds, { padding: [30, 30], maxZoom: 16 });
            status.textContent = "Showing all saved locations. Select a marker for details.";
        } else {
            status.textContent = "No saved locations have map points yet. The demo view is unchanged.";
        }
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
                  : "No verified incidents, open centers, or distribution points have map coordinates yet. Your submissions remain in My reports.";
            document.getElementById("map-fit").disabled = false;
        })
        .catch((error) => {
            status.textContent =
                error.message || "Saved locations could not be loaded. Please refresh.";
        });
})();
```

Read the script in these blocks:

- The immediately invoked function scopes the variables. Missing map element ends quietly; missing Leaflet produces a readable status. Settings establish the demo view and tiles. Tile failures have their own message.
- Layer groups keep incidents, centers and relief points separate. Checkbox changes add/remove these groups locally; no database update happens.
- `line()` creates a paragraph and uses `textContent`. `popup()` chooses the allowed fields and writes a text heading. Only fixed icon constants are inserted into icon HTML. Database text is not concatenated as popup HTML.
- Admin users get links to guarded staff records. Hiding a link is convenience, not the security check.
- Home returns to the configured demo view. Show all locations enables layers and fits saved marker bounds, so saved locations outside the demo opening view remain reachable.
- Fetch uses the endpoint stored in the page. Non-success responses become errors; successful JSON is grouped by layer. Each candidate coordinate pair is checked again before creating a Leaflet marker.
- The script updates mapped counts and status text after loading. Catch displays a failure instead of pretending the map loaded. There is no polling: after adding/changing a report, refresh to fetch current rows.

The PHP query is authoritative for visibility. JavaScript validation supports reliable display but cannot protect data already sent to the browser.
## Other owned workflows

- **Address picker:** the form includes the shared picker, a click/current-location selection changes coordinates, and a delayed request goes to `maps/address.php`. That signed-in endpoint validates coordinates and calls the fixed Photon provider. The browser ignores stale results and protects manual edits. The edited address and point later save through the owning module's ordinary POST. Opening an edit page should preserve the saved address.
- **Dashboard:** the role page creates `DashboardController`, whose `dashboardData()` chooses admin-wide or resident-owned counts. `DashboardModel` queries incident groups, centers, affected households, stays and notices. Shared card markup links the returned values to lists. Counts without points can exceed marker counts.
- **Announcement:** staff editor calls `AnnouncementController::save()` then `AnnouncementModel::save()`. The model preserves first publication history. Resident listing requires published state and excludes archived rows.
- **Summaries:** `DisasterSummaryController::period()` validates calendar dates and supplies an exclusive next-day end. `summarize()` runs related queries in a read transaction. Historical incident/relief totals and current occupancy have different definitions, which should be explained on screen.

## Code speech (about 75 seconds)

“The map page gives JavaScript the address of this PHP data endpoint. The endpoint checks the session and only accepts GET. The controller calls the map model, whose query names the fields that may be returned. Notice that the incident query excludes reporter names, phone numbers, descriptions and images, and filters for eligible statuses and valid coordinates. The result is encoded as JSON. In the browser, this fetch receives the rows and Leaflet creates the markers. Popup values use textContent, so record text is displayed as text. The layer checkboxes only change which marker groups are shown. They do not change the database. New or updated records appear after the map is refreshed.”

## Questions to be ready for

Leaflet is the map library, the tile provider supplies the background, browser geolocation supplies a selected device point, and Photon supplies an address suggestion. These are separate operations. A map point does not predict risk or prove safety. MAMP hosts PHP/MySQL locally, but external tiles and lookup require internet.
