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
