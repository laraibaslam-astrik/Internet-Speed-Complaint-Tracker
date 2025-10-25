<?php
/**
 * Daily Rollups Cron Job
 * Run at midnight UTC+5 to aggregate previous day's data
 * 
 * Usage: php rollups_daily.php
 */

require_once __DIR__ . '/../lib/db.php';

// Only allow CLI execution
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from command line\n");
}

echo "[" . date('Y-m-d H:i:s') . "] Starting daily rollups...\n";

$conn = get_db_connection();
if (!$conn) {
    die("Failed to connect to database\n");
}

// Get yesterday's date
$yesterday = date('Y-m-d', strtotime('-1 day'));

echo "Processing data for: $yesterday\n";

// Aggregate data by city and ISP
$sql = "
    INSERT INTO rollups_daily (dt, city, isp_name, avg_dl, avg_ul, avg_ping, p95_ping, tests_count)
    SELECT 
        DATE(ts) as dt,
        city,
        isp_name,
        AVG(dl_mbps) as avg_dl,
        AVG(ul_mbps) as avg_ul,
        AVG(ping_ms) as avg_ping,
        0 as p95_ping,
        COUNT(*) as tests_count
    FROM tests
    WHERE DATE(ts) = ?
    GROUP BY DATE(ts), city, isp_name
    ON DUPLICATE KEY UPDATE
        avg_dl = VALUES(avg_dl),
        avg_ul = VALUES(avg_ul),
        avg_ping = VALUES(avg_ping),
        tests_count = VALUES(tests_count)
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $yesterday);

if ($stmt->execute()) {
    $affected = $stmt->affected_rows;
    echo "✓ Aggregated $affected city+ISP combinations\n";
} else {
    echo "✗ Error: " . $stmt->error . "\n";
}

$stmt->close();

// Calculate P95 ping for each city+ISP
$result = $conn->query("
    SELECT DISTINCT city, isp_name 
    FROM rollups_daily 
    WHERE dt = '$yesterday'
");

while ($row = $result->fetch_assoc()) {
    $city = $row['city'];
    $isp = $row['isp_name'];
    
    // Get all ping values for this city+ISP
    $stmt = $conn->prepare("
        SELECT ping_ms 
        FROM tests 
        WHERE DATE(ts) = ? AND city = ? AND isp_name = ?
        ORDER BY ping_ms
    ");
    $stmt->bind_param('sss', $yesterday, $city, $isp);
    $stmt->execute();
    $pingResult = $stmt->get_result();
    
    $pings = [];
    while ($pingRow = $pingResult->fetch_assoc()) {
        $pings[] = (float)$pingRow['ping_ms'];
    }
    $stmt->close();
    
    if (count($pings) > 0) {
        $p95_index = (int)ceil(count($pings) * 0.95) - 1;
        $p95_ping = $pings[$p95_index];
        
        // Update rollup with P95
        $stmt = $conn->prepare("
            UPDATE rollups_daily 
            SET p95_ping = ? 
            WHERE dt = ? AND city = ? AND isp_name = ?
        ");
        $stmt->bind_param('dsss', $p95_ping, $yesterday, $city, $isp);
        $stmt->execute();
        $stmt->close();
    }
}

echo "✓ P95 calculations complete\n";

// Clean up old data (optional - keep last 90 days)
$cutoff_date = date('Y-m-d', strtotime('-90 days'));
$stmt = $conn->prepare("DELETE FROM tests WHERE DATE(ts) < ?");
$stmt->bind_param('s', $cutoff_date);
$stmt->execute();
$deleted = $stmt->affected_rows;
$stmt->close();

if ($deleted > 0) {
    echo "✓ Cleaned up $deleted old test records\n";
}

$conn->close();

echo "[" . date('Y-m-d H:i:s') . "] Daily rollups completed successfully!\n";
