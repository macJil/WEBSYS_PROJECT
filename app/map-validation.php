<?php
function publicationErrors(array $data): array
{
    if (!in_array($data['is_public'] ?? null, [0, 1, '0', '1'], true)) {
        return ['Choose a valid visibility value.'];
    }
    $lat = $data['public_latitude'] ?? '';
    $lng = $data['public_longitude'] ?? '';
    $emptyLat = $lat === '' || $lat === null;
    $emptyLng = $lng === '' || $lng === null;
    if ($emptyLat !== $emptyLng || (!$emptyLat && (!is_numeric($lat) || !is_numeric($lng) || !is_finite((float)$lat) || !is_finite((float)$lng) || abs((float)$lat) > 90 || abs((float)$lng) > 180))) {
        return ['Enter both valid public coordinates, or leave both blank.'];
    }
    if ((int)$data['is_public'] === 1) {
        $label = $data['public_label'] ?? null;
        if (!is_string($label) || trim($label) === '' || strlen($label) > 150 || $emptyLat || !in_array($data['status'] ?? '', ['verified', 'responding', 'resolved'], true) || !empty($data['archived_at'])) {
            return ['Publication requires an eligible incident, safe label, and separate public point.'];
        }
    }
    return [];
}
