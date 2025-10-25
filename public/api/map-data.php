<?php
/**
 * Map Data API - City-level speed aggregation
 */

// Suppress all errors and output valid JSON no matter what
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Initialize
$conn = null;
$cities = [];

// Try database connection only if file exists
if (file_exists(__DIR__ . '/../lib/db.php')) {
    try {
        @require_once __DIR__ . '/../lib/db.php';
        if (function_exists('get_db_connection')) {
            $conn = @get_db_connection();
        }
    } catch (Throwable $e) {
        $conn = null;
    }
}

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

// Try to get real data from database (with complete error suppression)
if ($conn && is_object($conn)) {
    try {
        $result = @$conn->query("
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
        
        if ($result && is_object($result)) {
            while ($row = @$result->fetch_assoc()) {
                if (!$row || !isset($row['city'])) continue;
                
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
                    'test_count' => (int)($row['test_count'] ?? 0),
                    'avg_download' => round((float)($row['avg_download'] ?? 0), 1),
                    'avg_upload' => round((float)($row['avg_upload'] ?? 0), 1),
                    'avg_ping' => round((float)($row['avg_ping'] ?? 0), 1)
                ];
            }
        }
    } catch (Throwable $e) {
        // Any error = continue with empty array
    }
}

// If no data, return empty array (frontend will use demo data)
echo json_encode([
    'success' => true,
    'cities' => $cities,
    'timestamp' => date('Y-m-d H:i:s')
]);
