<?php
/**
 * Advanced Analytics Tracking Library
 * Tracks visitors with detailed information
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/util.php';

function get_session_id() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['analytics_session_id'])) {
        $_SESSION['analytics_session_id'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['analytics_session_id'];
}

function parse_user_agent($user_agent) {
    $result = [
        'device_type' => 'desktop',
        'browser' => 'Unknown',
        'browser_version' => '',
        'os' => 'Unknown',
        'os_version' => ''
    ];
    
    // Device type
    if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $user_agent)) {
        $result['device_type'] = 'tablet';
    } elseif (preg_match('/Mobile|iP(hone|od)|Android|BlackBerry|IEMobile/', $user_agent)) {
        $result['device_type'] = 'mobile';
    }
    
    // Browser
    if (preg_match('/Edge\/(\d+)/', $user_agent, $matches)) {
        $result['browser'] = 'Edge';
        $result['browser_version'] = $matches[1];
    } elseif (preg_match('/Chrome\/(\d+)/', $user_agent, $matches)) {
        $result['browser'] = 'Chrome';
        $result['browser_version'] = $matches[1];
    } elseif (preg_match('/Firefox\/(\d+)/', $user_agent, $matches)) {
        $result['browser'] = 'Firefox';
        $result['browser_version'] = $matches[1];
    } elseif (preg_match('/Safari\/(\d+)/', $user_agent, $matches)) {
        $result['browser'] = 'Safari';
        $result['browser_version'] = $matches[1];
    }
    
    // OS
    if (preg_match('/Windows NT (\d+\.\d+)/', $user_agent, $matches)) {
        $result['os'] = 'Windows';
        $result['os_version'] = $matches[1];
    } elseif (preg_match('/Mac OS X (\d+[._]\d+)/', $user_agent, $matches)) {
        $result['os'] = 'macOS';
        $result['os_version'] = str_replace('_', '.', $matches[1]);
    } elseif (preg_match('/Android (\d+)/', $user_agent, $matches)) {
        $result['os'] = 'Android';
        $result['os_version'] = $matches[1];
    } elseif (preg_match('/iPhone OS (\d+_\d+)/', $user_agent, $matches)) {
        $result['os'] = 'iOS';
        $result['os_version'] = str_replace('_', '.', $matches[1]);
    }
    
    return $result;
}

function get_detailed_geo_data($ip) {
    $cache_key = "geo_detail_" . md5($ip);
    
    // Try cache first
    if (function_exists('apcu_fetch')) {
        $cached = apcu_fetch($cache_key);
        if ($cached !== false) {
            return $cached;
        }
    }
    
    $result = [
        'country' => 'Unknown',
        'country_code' => 'XX',
        'region' => 'Unknown',
        'city' => 'Unknown',
        'latitude' => null,
        'longitude' => null,
        'timezone' => 'UTC',
        'postal_code' => null,
        'isp_name' => 'Unknown',
        'asn' => null,
        'connection_type' => 'Unknown'
    ];
    
    // Try ip-api.com with more details
    try {
        $url = "http://ip-api.com/json/{$ip}?fields=66846719";
        $response = @file_get_contents($url, false, stream_context_create([
            'http' => ['timeout' => 3, 'ignore_errors' => true]
        ]));
        
        if ($response) {
            $data = json_decode($response, true);
            if ($data && $data['status'] === 'success') {
                $result = [
                    'country' => $data['country'] ?? 'Unknown',
                    'country_code' => $data['countryCode'] ?? 'XX',
                    'region' => $data['regionName'] ?? 'Unknown',
                    'city' => $data['city'] ?? 'Unknown',
                    'latitude' => $data['lat'] ?? null,
                    'longitude' => $data['lon'] ?? null,
                    'timezone' => $data['timezone'] ?? 'UTC',
                    'postal_code' => $data['zip'] ?? null,
                    'isp_name' => $data['isp'] ?? 'Unknown',
                    'asn' => isset($data['as']) ? (int)preg_replace('/[^0-9]/', '', $data['as']) : null,
                    'connection_type' => $data['mobile'] ? 'mobile' : 'broadband'
                ];
            }
        }
    } catch (Exception $e) {
        error_log("Detailed geo lookup failed: " . $e->getMessage());
    }
    
    // Cache for 1 hour
    if (function_exists('apcu_store')) {
        apcu_store($cache_key, $result, 3600);
    }
    
    return $result;
}

function track_visitor() {
    $conn = get_db_connection();
    if (!$conn) return false;
    
    $session_id = get_session_id();
    $ip = client_ip();
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ua_data = parse_user_agent($user_agent);
    $geo_data = get_detailed_geo_data($ip);
    
    $referrer = $_SERVER['HTTP_REFERER'] ?? '';
    $referrer_domain = $referrer ? parse_url($referrer, PHP_URL_HOST) : null;
    $landing_page = $_SERVER['REQUEST_URI'] ?? '/';
    
    // Check if session exists
    $stmt = $conn->prepare("SELECT id FROM visitor_sessions WHERE session_id = ?");
    $stmt->bind_param('s', $session_id);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    
    if (!$exists) {
        // Insert new session
        $stmt = $conn->prepare("
            INSERT INTO visitor_sessions (
                session_id, ip_address, user_agent, device_type, browser, browser_version,
                os, os_version, country, country_code, region, city, latitude, longitude,
                timezone, postal_code, isp_name, asn, connection_type, referrer_url,
                referrer_domain, landing_page
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param('ssssssssssssddssiissss',
            $session_id, $ip, $user_agent, $ua_data['device_type'],
            $ua_data['browser'], $ua_data['browser_version'],
            $ua_data['os'], $ua_data['os_version'],
            $geo_data['country'], $geo_data['country_code'],
            $geo_data['region'], $geo_data['city'],
            $geo_data['latitude'], $geo_data['longitude'],
            $geo_data['timezone'], $geo_data['postal_code'],
            $geo_data['isp_name'], $geo_data['asn'],
            $geo_data['connection_type'], $referrer,
            $referrer_domain, $landing_page
        );
        
        $stmt->execute();
        $stmt->close();
    } else {
        // Update existing session
        $stmt = $conn->prepare("
            UPDATE visitor_sessions 
            SET last_activity = NOW(), total_pageviews = total_pageviews + 1
            WHERE session_id = ?
        ");
        $stmt->bind_param('s', $session_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Update online users
    $current_page = $_SERVER['REQUEST_URI'] ?? '/';
    $stmt = $conn->prepare("
        INSERT INTO online_users (session_id, current_page) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE last_ping = NOW(), current_page = ?
    ");
    $stmt->bind_param('sss', $session_id, $current_page, $current_page);
    $stmt->execute();
    $stmt->close();
    
    return $session_id;
}

function track_pageview($session_id, $page_url, $page_title = '') {
    $conn = get_db_connection();
    if (!$conn) return false;
    
    $referrer = $_SERVER['HTTP_REFERER'] ?? '';
    
    $stmt = $conn->prepare("
        INSERT INTO pageviews (session_id, page_url, page_title, referrer_url)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param('ssss', $session_id, $page_url, $page_title, $referrer);
    $stmt->execute();
    $stmt->close();
    
    return true;
}

function track_event($session_id, $event_type, $element_id = null, $element_class = null, $element_text = null) {
    $conn = get_db_connection();
    if (!$conn) return false;
    
    $page_url = $_SERVER['REQUEST_URI'] ?? '/';
    
    $stmt = $conn->prepare("
        INSERT INTO click_events (session_id, event_type, element_id, element_class, element_text, page_url)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('ssssss', $session_id, $event_type, $element_id, $element_class, $element_text, $page_url);
    $stmt->execute();
    $stmt->close();
    
    return true;
}

function get_online_users_count() {
    $conn = get_db_connection();
    if (!$conn) return 0;
    
    // Users active in last 5 minutes
    $result = $conn->query("
        SELECT COUNT(*) as count 
        FROM online_users 
        WHERE last_ping >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ");
    
    $row = $result->fetch_assoc();
    return (int)$row['count'];
}

function cleanup_old_sessions() {
    $conn = get_db_connection();
    if (!$conn) return;
    
    // Remove sessions older than 30 days
    $conn->query("DELETE FROM visitor_sessions WHERE first_visit < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    
    // Remove offline users (inactive > 5 minutes)
    $conn->query("DELETE FROM online_users WHERE last_ping < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
}
