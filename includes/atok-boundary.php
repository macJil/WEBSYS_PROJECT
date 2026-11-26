<?php
function atokBoundary(): array
{
    return json_decode(file_get_contents(__DIR__ . '/../assets/data/atok.geojson'), true, 512, JSON_THROW_ON_ERROR);
}

// The demo feature contains one closed exterior ring without holes.
// A horizontal ray crosses the outline an odd number of times from an inside point.
function insideAtok(float $lat, float $lng): bool
{
    $ring = atokBoundary()['geometry']['coordinates'][0];
    $inside = false;
    for ($i = 1; $i < count($ring); $i++) {
        [$x1, $y1] = $ring[$i - 1];
        [$x2, $y2] = $ring[$i];
        if (($y1 > $lat) !== ($y2 > $lat)) {
            $crossing = $x1 + ($lat - $y1) * ($x2 - $x1) / ($y2 - $y1);
            if ($lng < $crossing) {
                $inside = !$inside;
            }
        }
    }
    return $inside;
}

function locationErrors(string $description, string $lat, string $lng): array
{
    if (!is_string($_POST['latitude'] ?? '') || !is_string($_POST['longitude'] ?? '')) {
        return ['Enter a numeric coordinate pair or clear both fields.'];
    }
    if (($lat === '') !== ($lng === '')) {
        return ['Enter both coordinates or clear both.'];
    }
    if (
        $lat !== '' &&
        (!is_numeric($lat) ||
            !is_numeric($lng) ||
            !is_finite((float) $lat) ||
            !is_finite((float) $lng) ||
            abs((float) $lat) > 90 ||
            abs((float) $lng) > 180 ||
            trim($description) === '')
    ) {
        return ['Enter valid coordinates and a written location.'];
    }
    if ($lat !== '' && !insideAtok(round((float) $lat, 7), round((float) $lng, 7))) {
        return ['Choose a point inside the approximate Atok boundary, or enter the address without a pin.'];
    }
    return [];
}

// Match the map's coordinate rules without treating zero as a missing value.
function hasMapPoint(array $record): bool
{
    $lat = $record['latitude'] ?? null;
    $lng = $record['longitude'] ?? null;
    return is_numeric($lat) &&
        is_numeric($lng) &&
        is_finite((float) $lat) &&
        is_finite((float) $lng) &&
        abs((float) $lat) <= 90 &&
        abs((float) $lng) <= 180;
}
