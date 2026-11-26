<?php
require_once __DIR__ . '/../includes/session.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (empty($_SESSION['ID'])) {
    http_response_code(401);
    exit(json_encode(['error' => 'Sign in first.']));
}
if (!empty($_SESSION['CHANGE_PASSWORD'])) {
    http_response_code(403);
    exit(json_encode(['error' => 'Choose your password before using the map.']));
}

$lat = filter_var($_GET['lat'] ?? '', FILTER_VALIDATE_FLOAT);
$lng = filter_var($_GET['lon'] ?? '', FILTER_VALIDATE_FLOAT);
if ($lat === false || $lng === false || abs($lat) > 90 || abs($lng) > 180) {
    http_response_code(400);
    exit(json_encode(['error' => 'Invalid coordinates.']));
}
if (!insideAtok($lat, $lng)) {
    http_response_code(400);
    exit(json_encode(['error' => 'Point outside the approximate Atok boundary.']));
}
$now = time();
$requests = $_SESSION['address_requests'] ?? [];
$recent = [];
foreach ($requests as $time) {
    if ($now - $time < 60) {
        $recent[] = $time;
    }
}
if (count($recent) >= 10) {
    http_response_code(429);
    exit(json_encode(['error' => 'Try again in a minute or type the address.']));
}
$recent[] = $now;
$_SESSION['address_requests'] = $recent;
session_write_close();
$url = 'https://photon.komoot.io/reverse?' . http_build_query(['lat' => $lat, 'lon' => $lng, 'limit' => 1]);
$curl = curl_init($url);
curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 7]);
$body = curl_exec($curl);
$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);
if ($body === false || $status !== 200 || !is_array(json_decode($body, true))) {
    http_response_code(502);
    exit(json_encode(['error' => 'Address unavailable. Please type it manually.']));
}
echo $body;
