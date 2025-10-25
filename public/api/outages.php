<?php
/**
 * OUTAGES endpoint
 * Returns detected anomalies (latency spikes, download drops)
 */

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/util.php';

$date = $_GET['date'] ?? 'today';
$city_filter = isset($_GET['city']) ? sanitize_string($_GET['city'], 128) : null;

$conn = get_db_connection();
if (!$conn) {
    json_response(['error' => 'Database connection failed'], 500);
}

// Get recent outages (last 24 hours)
$cutoff = date('Y-m-d H:i:s', time() - 86400);

$query = "
    SELECT 
        id,
        window_start,
        city,
        isp_name,
        anomaly_type,
        severity,
        evidence
    FROM outages
    WHERE window_start >= ?
";

$params = [$cutoff];
$types = 's';

if ($city_filter) {
    $query .= " AND city = ?";
    $params[] = $city_filter;
    $types .= 's';
}

$query .= " ORDER BY window_start DESC LIMIT 50";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$outages = [];
while ($row = $result->fetch_assoc()) {
    $outages[] = [
        'id' => $row['id'],
        'window_start' => $row['window_start'],
        'city' => $row['city'],
        'isp_name' => $row['isp_name'],
        'anomaly_type' => $row['anomaly_type'],
        'severity' => (int)$row['severity'],
        'evidence' => json_decode($row['evidence'], true)
    ];
}

$stmt->close();

json_response([
    'outages' => $outages,
    'count' => count($outages)
]);
