# Member 9 · Follow a map filter to one marker

The map page’s GET form carries the selected barangay into the data URL. The browser requests the guarded data page. MapController::mapData validates the selection and calls MapModel::publicMap. Show the incident query’s status and coordinate conditions and the explicit columns it returns. Both roles receive this shared projection. The browser draws the saved coordinates with Leaflet; toggling a layer changes display locally. A pending report remains visible to its owner through the private incident detail page. [Filter](../../../resources/views/maps/map.php) · [Data page](../../../resources/views/maps/data.php) · [Controller](../../../app/controllers/MapController.php) · [Model](../../../app/models/Map.php) · [Rendering](../../../resources/assets/js/community-map.js)

## Carry the selected barangay into the marker request

[resources/views/maps/map.php, lines 7–13](../../../resources/views/maps/map.php#L7)

```php
$selectedBarangay = $_GET['barangay'] ?? '';
if ($selectedBarangay !== '' && !in_array($selectedBarangay, ATOK_BARANGAYS, true)) {
    http_response_code(400);
    exit('Choose a barangay in Atok.');
}
$query = $selectedBarangay === '' ? '' : '?' . http_build_query(['barangay' => $selectedBarangay]);
$pageTitle = "Community map";
```

## Validate the barangay filter

[app/controllers/MapController.php, lines 6–16](../../../app/controllers/MapController.php#L6)

```php
    public function mapData(): array
    {
        session_checker();
        $model = new MapModel();
        $barangay = $_GET['barangay'] ?? '';
        if ($barangay !== '' && !in_array($barangay, ATOK_BARANGAYS, true)) {
            throw new InvalidArgumentException('Choose a barangay in Atok.');
        }
        return $model->publicMap($barangay);
    }
```

## Read only eligible mapped records

[app/models/Map.php, lines 24–41](../../../app/models/Map.php#L24)

```php
    public function publicMap(string $barangay = ''): array
    {
        $filter = $barangay === '' ? '' : ' AND barangay=?';
        // Explicit allowlists: never select private incident fields for the resident response.
        return [
            'incidents' => $this->rows(
                "SELECT id,incident_type,latitude,longitude,status,DATE(created_at) AS report_date FROM incident_reports WHERE archived_at IS NULL AND status IN ('verified','responding','resolved') AND " .
                    $this->point('latitude', 'longitude') . $filter,
                $barangay,
            ),
            'centers' => $this->centers($barangay),
            'relief_locations' => $this->rows(
                "SELECT id,name,address_details,latitude,longitude,location_type FROM relief_locations WHERE archived_at IS NULL AND location_type IN ('distribution','both') AND " .
                    $this->point('latitude', 'longitude') . $filter,
                $barangay,
            ),
        ];
    }
```

## Fit the map to the saved marker bounds

[resources/assets/js/community-map.js, lines 94–107](../../../resources/assets/js/community-map.js#L94)

```javascript
    function fitSavedLocations() {
        // Distant records must still fit. A single point stops at street-level zoom.
        map.setMinZoom(1);
        map.fitBounds(savedBounds, { padding: [30, 30], maxZoom: 17 });
    }
    document.getElementById("map-fit").addEventListener("click", () => {
        if (!savedBounds.isValid()) return;
        boxes.forEach((box) => {
            box.checked = true;
            groups[box.dataset.layer].addTo(map);
        });
        fitSavedLocations();
        status.textContent = "Showing all saved locations for this selection. Select a marker for details.";
    });
```

## Demonstrate it

Filter to a barangay with saved points. The map frames the results automatically. Pan away, hide a layer and click Show all locations to restore every layer and frame the same filtered set. Try an empty barangay and read its message.
