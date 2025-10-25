<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

require_once __DIR__ . '/../../lib/db.php';
$conn = get_db_connection();

$type = $_GET['type'] ?? 'online';

if ($type === 'online') {
    // Get online users
    $result = $conn->query("
        SELECT 
            vs.ip_address,
            vs.city,
            vs.country_code,
            ou.current_page,
            ou.last_ping,
            TIMESTAMPDIFF(SECOND, ou.last_ping, NOW()) as seconds_ago
        FROM online_users ou
        JOIN visitor_sessions vs ON ou.session_id = vs.session_id
        WHERE ou.last_ping >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ORDER BY ou.last_ping DESC
    ");
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $row['last_ping_relative'] = $row['seconds_ago'] . 's ago';
        $users[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($users),
        'users' => $users
    ]);
    
} elseif ($type === 'activity') {
    // Get recent events
    $result = $conn->query("
        SELECT 
            ce.event_type,
            ce.element_text,
            ce.event_timestamp,
            vs.ip_address,
            TIMESTAMPDIFF(SECOND, ce.event_timestamp, NOW()) as seconds_ago
        FROM click_events ce
        JOIN visitor_sessions vs ON ce.session_id = vs.session_id
        ORDER BY ce.event_timestamp DESC
        LIMIT 50
    ");
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $row['time_relative'] = $row['seconds_ago'] . 's ago';
        $events[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'events' => $events
    ]);
}
