<?php
/**
 * Analytics Tracking Endpoint
 * Called via AJAX from frontend
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/lib/analytics.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'pageview';

switch ($action) {
    case 'init':
        // Initialize tracking
        $session_id = track_visitor();
        $page_url = $_POST['page_url'] ?? '/';
        $page_title = $_POST['page_title'] ?? '';
        track_pageview($session_id, $page_url, $page_title);
        
        echo json_encode([
            'success' => true,
            'session_id' => $session_id
        ]);
        break;
        
    case 'pageview':
        $session_id = $_POST['session_id'] ?? get_session_id();
        $page_url = $_POST['page_url'] ?? '/';
        $page_title = $_POST['page_title'] ?? '';
        track_pageview($session_id, $page_url, $page_title);
        
        echo json_encode(['success' => true]);
        break;
        
    case 'event':
        $session_id = $_POST['session_id'] ?? get_session_id();
        $event_type = $_POST['event_type'] ?? 'click';
        $element_id = $_POST['element_id'] ?? null;
        $element_class = $_POST['element_class'] ?? null;
        $element_text = $_POST['element_text'] ?? null;
        
        track_event($session_id, $event_type, $element_id, $element_class, $element_text);
        
        echo json_encode(['success' => true]);
        break;
        
    case 'heartbeat':
        // Keep session alive
        $session_id = $_POST['session_id'] ?? get_session_id();
        $conn = get_db_connection();
        if ($conn) {
            $stmt = $conn->prepare("UPDATE visitor_sessions SET last_activity = NOW() WHERE session_id = ?");
            $stmt->bind_param('s', $session_id);
            $stmt->execute();
            $stmt->close();
        }
        
        echo json_encode(['success' => true]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}
