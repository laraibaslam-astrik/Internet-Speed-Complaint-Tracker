<?php
/**
 * Map Data API - City-level speed aggregation
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../lib/db.php';

$conn = get_db_connection();
if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get city-level aggregated data from today
$result = $conn->query("
    SELECT 
        city,
        COUNT(*) as test_count,
        AVG(download_mbps) as avg_download,
        AVG(upload_mbps) as avg_upload,
        AVG(ping_ms) as avg_ping
    FROM tests
    WHERE DATE(ts) = CURDATE() AND city != 'Unknown'
    GROUP BY city
    ORDER BY test_count DESC
    LIMIT 20
");

$cities = [];

// Major Pakistan cities with approximate relative coordinates
$cityCoordinates = [
    'Karachi' => ['x' => 0.67, 'y' => 0.85],
    'Lahore' => ['x' => 0.74, 'y' => 0.31],
    'Islamabad' => ['x' => 0.73, 'y' => 0.22],
    'Rawalpindi' => ['x' => 0.73, 'y' => 0.23],
    'Faisalabad' => ['x' => 0.73, 'y' => 0.35],
    'Multan' => ['x' => 0.71, 'y' => 0.52],
    'Hyderabad' => ['x' => 0.68, 'y' => 0.82],
    'Gujranwala' => ['x' => 0.74, 'y' => 0.29],
    'Peshawar' => ['x' => 0.72, 'y' => 0.18],
    'Quetta' => ['x' => 0.67, 'y' => 0.50],
    'Sialkot' => ['x' => 0.74, 'y' => 0.27],
    'Sargodha' => ['x' => 0.72, 'y' => 0.30],
    'Sukkur' => ['x' => 0.69, 'y' => 0.72],
    'Bahawalpur' => ['x' => 0.72, 'y' => 0.55],
    'Jhang' => ['x' => 0.72, 'y' => 0.38],
    'Sheikhupura' => ['x' => 0.74, 'y' => 0.30],
    'Larkana' => ['x' => 0.68, 'y' => 0.75],
    'Gujrat' => ['x' => 0.74, 'y' => 0.28],
    'Mardan' => ['x' => 0.72, 'y' => 0.19],
    'Kasur' => ['x' => 0.74, 'y' => 0.33]
];

while ($row = $result->fetch_assoc()) {
    $cityName = $row['city'];
    
    // Get coordinates or use default
    $coords = $cityCoordinates[$cityName] ?? [
        'x' => 0.70 + (rand(-5, 5) / 100),
        'y' => 0.50 + (rand(-30, 30) / 100)
    ];
    
    $cities[] = [
        'name' => $cityName,
        'x' => $coords['x'],
        'y' => $coords['y'],
        'test_count' => (int)$row['test_count'],
        'avg_download' => round((float)$row['avg_download'], 1),
        'avg_upload' => round((float)$row['avg_upload'], 1),
        'avg_ping' => round((float)$row['avg_ping'], 1)
    ];
}

// If no data, return empty array (frontend will use demo data)
echo json_encode([
    'success' => true,
    'cities' => $cities,
    'timestamp' => date('Y-m-d H:i:s')
]);
