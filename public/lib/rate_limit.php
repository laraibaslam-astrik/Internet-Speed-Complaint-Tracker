<?php
/**
 * Rate limiting using MySQL
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/util.php';

function check_rate_limit($hash_ip) {
    $conn = get_db_connection();
    if (!$conn) {
        return ['allowed' => true]; // Fail open
    }
    
    $window_sec = (int)env('RATE_LIMIT_WINDOW_SEC', 600);
    $max_tests = (int)env('RATE_LIMIT_MAX', 2);
    
    $cutoff = date('Y-m-d H:i:s', time() - $window_sec);
    
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM tests WHERE hash_ip = ? AND ts >= ?");
    $stmt->bind_param('ss', $hash_ip, $cutoff);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $count = (int)$row['cnt'];
    $stmt->close();
    
    if ($count >= $max_tests) {
        // Find the oldest test in the window
        $stmt = $conn->prepare("SELECT ts FROM tests WHERE hash_ip = ? AND ts >= ? ORDER BY ts ASC LIMIT 1");
        $stmt->bind_param('ss', $hash_ip, $cutoff);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        $oldest_ts = strtotime($row['ts']);
        $retry_after = max(0, ($oldest_ts + $window_sec) - time());
        
        return [
            'allowed' => false,
            'retry_after' => $retry_after,
            'count' => $count,
            'max' => $max_tests
        ];
    }
    
    return [
        'allowed' => true,
        'count' => $count,
        'max' => $max_tests,
        'remaining' => $max_tests - $count
    ];
}
