<?php
/**
 * HEATMAP endpoint
 * Returns city-level aggregated data for map visualization
 */

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/util.php';

$date = $_GET['date'] ?? 'today';

// Convert date
if ($date === 'today') {
    $date = date('Y-m-d');
} else {
    $date = date('Y-m-d', strtotime($date));
}

$conn = get_db_connection();
if (!$conn) {
    json_response(['error' => 'Database connection failed'], 500);
}

$stmt = $conn->prepare("
    SELECT 
        city,
        AVG(dl_mbps) as avg_dl,
        AVG(ul_mbps) as avg_ul,
        AVG(ping_ms) as avg_ping,
        COUNT(*) as tests_count
    FROM tests
    WHERE DATE(ts) = ?
    GROUP BY city
    HAVING tests_count >= 3
    ORDER BY tests_count DESC
    LIMIT 50
");

$stmt->bind_param('s', $date);
$stmt->execute();
$result = $stmt->get_result();

$heatmap = [];
while ($row = $result->fetch_assoc()) {
    $heatmap[] = [
        'city' => $row['city'],
        'avg_dl' => round((float)$row['avg_dl'], 2),
        'avg_ul' => round((float)$row['avg_ul'], 2),
        'avg_ping' => round((float)$row['avg_ping'], 2),
        'tests' => (int)$row['tests_count']
    ];
}

$stmt->close();

json_response([
    'date' => $date,
    'cities' => $heatmap
]);
