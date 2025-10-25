<?php
/**
 * Anomaly Detection Cron Job
 * Run every 15 minutes to detect latency spikes and download drops
 * 
 * Usage: php detect_anomalies.php
 */

require_once __DIR__ . '/../lib/db.php';

// Only allow CLI execution
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from command line\n");
}

echo "[" . date('Y-m-d H:i:s') . "] Starting anomaly detection...\n";

$conn = get_db_connection();
if (!$conn) {
    die("Failed to connect to database\n");
}

// Time windows
$recent_start = date('Y-m-d H:i:s', strtotime('-60 minutes'));
$baseline_start = date('Y-m-d H:i:s', strtotime('-7 days'));
$baseline_end = date('Y-m-d H:i:s', strtotime('-1 day'));

// Get unique city+ISP combinations with recent activity
$result = $conn->query("
    SELECT DISTINCT city, isp_name
    FROM tests
    WHERE ts >= '$recent_start'
    GROUP BY city, isp_name
    HAVING COUNT(*) >= 5
");

$anomalies_detected = 0;

while ($row = $result->fetch_assoc()) {
    $city = $row['city'];
    $isp = $row['isp_name'];
    
    // Get recent stats (last 60 minutes)
    $stmt = $conn->prepare("
        SELECT 
            AVG(ping_ms) as avg_ping,
            AVG(dl_mbps) as avg_dl,
            COUNT(*) as test_count
        FROM tests
        WHERE city = ? AND isp_name = ? AND ts >= ?
    ");
    $stmt->bind_param('sss', $city, $isp, $recent_start);
    $stmt->execute();
    $recentResult = $stmt->get_result();
    $recent = $recentResult->fetch_assoc();
    $stmt->close();
    
    if (!$recent || $recent['test_count'] < 5) {
        continue;
    }
    
    // Get baseline stats (7 days ago to 1 day ago)
    $stmt = $conn->prepare("
        SELECT 
            AVG(ping_ms) as baseline_ping,
            STDDEV(ping_ms) as stddev_ping,
            AVG(dl_mbps) as baseline_dl,
            STDDEV(dl_mbps) as stddev_dl,
            COUNT(*) as test_count
        FROM tests
        WHERE city = ? AND isp_name = ? 
            AND ts >= ? AND ts <= ?
    ");
    $stmt->bind_param('ssss', $city, $isp, $baseline_start, $baseline_end);
    $stmt->execute();
    $baselineResult = $stmt->get_result();
    $baseline = $baselineResult->fetch_assoc();
    $stmt->close();
    
    if (!$baseline || $baseline['test_count'] < 20) {
        continue; // Not enough baseline data
    }
    
    // Detect latency spike
    $ping_threshold = $baseline['baseline_ping'] + (2 * $baseline['stddev_ping']);
    if ($recent['avg_ping'] > $ping_threshold && $recent['avg_ping'] > 100) {
        $severity = calculateSeverity($recent['avg_ping'], $baseline['baseline_ping']);
        
        $evidence = json_encode([
            'recent_avg' => round($recent['avg_ping'], 2),
            'baseline_avg' => round($baseline['baseline_ping'], 2),
            'threshold' => round($ping_threshold, 2),
            'stddev' => round($baseline['stddev_ping'], 2),
            'test_count' => $recent['test_count']
        ]);
        
        insertAnomaly($conn, $city, $isp, 'latency_spike', $severity, $evidence);
        $anomalies_detected++;
        
        echo "⚠ Latency spike detected: $city - $isp (Ping: {$recent['avg_ping']}ms, Baseline: {$baseline['baseline_ping']}ms)\n";
    }
    
    // Detect download drop
    $dl_threshold = $baseline['baseline_dl'] - (1.5 * $baseline['stddev_dl']);
    if ($recent['avg_dl'] < $dl_threshold && $recent['avg_dl'] < 5) {
        $severity = calculateSeverity($baseline['baseline_dl'], $recent['avg_dl']);
        
        $evidence = json_encode([
            'recent_avg' => round($recent['avg_dl'], 2),
            'baseline_avg' => round($baseline['baseline_dl'], 2),
            'threshold' => round($dl_threshold, 2),
            'stddev' => round($baseline['stddev_dl'], 2),
            'test_count' => $recent['test_count']
        ]);
        
        insertAnomaly($conn, $city, $isp, 'dl_drop', $severity, $evidence);
        $anomalies_detected++;
        
        echo "⚠ Download drop detected: $city - $isp (DL: {$recent['avg_dl']}Mbps, Baseline: {$baseline['baseline_dl']}Mbps)\n";
    }
}

// Clean up old anomalies (older than 7 days)
$cutoff = date('Y-m-d H:i:s', strtotime('-7 days'));
$stmt = $conn->prepare("DELETE FROM outages WHERE window_start < ?");
$stmt->bind_param('s', $cutoff);
$stmt->execute();
$cleaned = $stmt->affected_rows;
$stmt->close();

$conn->close();

echo "✓ Detected $anomalies_detected anomalies\n";
if ($cleaned > 0) {
    echo "✓ Cleaned up $cleaned old anomaly records\n";
}
echo "[" . date('Y-m-d H:i:s') . "] Anomaly detection completed\n";

function insertAnomaly($conn, $city, $isp, $type, $severity, $evidence) {
    $window_start = date('Y-m-d H:i:s', strtotime('-60 minutes'));
    
    // Check if this anomaly already exists (deduplication)
    $stmt = $conn->prepare("
        SELECT id FROM outages 
        WHERE city = ? AND isp_name = ? 
            AND anomaly_type = ? 
            AND window_start >= ?
    ");
    $recent_check = date('Y-m-d H:i:s', strtotime('-120 minutes'));
    $stmt->bind_param('ssss', $city, $isp, $type, $recent_check);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        return; // Already recorded
    }
    $stmt->close();
    
    // Insert new anomaly
    $stmt = $conn->prepare("
        INSERT INTO outages (window_start, city, isp_name, anomaly_type, severity, evidence)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('ssssis', $window_start, $city, $isp, $type, $severity, $evidence);
    $stmt->execute();
    $stmt->close();
}

function calculateSeverity($current, $baseline) {
    $ratio = abs($current - $baseline) / max($baseline, 1);
    
    if ($ratio > 3) return 3; // Critical
    if ($ratio > 2) return 2; // High
    if ($ratio > 1) return 1; // Medium
    return 0; // Low
}
