<?php
/**
 * LEADERBOARD endpoint
 * Returns ISP rankings for a given date
 */

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/util.php';

$date = $_GET['date'] ?? 'today';
$city_filter = isset($_GET['city']) ? sanitize_string($_GET['city'], 128) : null;

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

// Build query
$query = "
    SELECT 
        isp_name,
        city,
        AVG(dl_mbps) as avg_dl,
        AVG(ul_mbps) as avg_ul,
        AVG(ping_ms) as avg_ping,
        COUNT(*) as tests_count
    FROM tests
    WHERE DATE(ts) = ?
";

$params = [$date];
$types = 's';

if ($city_filter) {
    $query .= " AND city = ?";
    $params[] = $city_filter;
    $types .= 's';
}

$query .= " GROUP BY isp_name, city ORDER BY avg_dl DESC LIMIT 20";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$leaderboard = [];
while ($row = $result->fetch_assoc()) {
    $leaderboard[] = [
        'isp_name' => $row['isp_name'],
        'city' => $row['city'],
        'avg_dl' => round((float)$row['avg_dl'], 2),
        'avg_ul' => round((float)$row['avg_ul'], 2),
        'avg_ping' => round((float)$row['avg_ping'], 2),
        'tests_count' => (int)$row['tests_count']
    ];
}

$stmt->close();

// Check for outages/spikes for the city
$spike = false;
if ($city_filter) {
    $cutoff = date('Y-m-d H:i:s', time() - 7200); // Last 2 hours
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM outages WHERE city = ? AND window_start >= ?");
    $stmt->bind_param('ss', $city_filter, $cutoff);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $spike = (int)$row['cnt'] > 0;
    $stmt->close();
}

json_response([
    'date' => $date,
    'city' => $city_filter,
    'spike' => $spike,
    'leaderboard' => $leaderboard
]);
