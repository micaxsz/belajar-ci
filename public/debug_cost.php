<?php
// Debug: Test RajaOngkir API getCost directly
$apiKey  = 'pqCoi5S9535eed50e5dc3bfel5WidcdY';
$baseUrl = 'https://rajaongkir.komerce.id/api/v1/';

$origin      = '64999';
$destination = $_GET['destination'] ?? '65005'; // default: Bojongsalaman Semarang
$weight      = 1000;
$courier     = 'jne';

$ch = curl_init($baseUrl . 'calculate/domestic-cost');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_POSTFIELDS     => http_build_query([
        'origin'      => $origin,
        'destination' => $destination,
        'weight'      => $weight,
        'courier'     => $courier,
    ]),
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded',
        'key: ' . $apiKey,
    ],
    CURLOPT_SSL_VERIFYPEER => false,
]);

$res      = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err      = curl_error($ch);
curl_close($ch);

header('Content-Type: application/json');
echo json_encode([
    'http_code'    => $httpCode,
    'curl_error'   => $err,
    'params_sent'  => compact('origin', 'destination', 'weight', 'courier'),
    'raw_response' => json_decode($res, true),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
