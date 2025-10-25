<?php
/**
 * SUBMIT endpoint
 * Receives speed test results and stores them
 */

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/util.php';
require_once __DIR__ . '/../lib/rate_limit.php';

// Handle OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

// Parse JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    json_response(['error' => 'Invalid JSON'], 400);
}

// Get client IP and hash it
$ip = client_ip();
$salt = env('HASH_SALT', 'default_salt');
$hash_ip = hash_ip($ip, $salt);

// Check rate limit
$rate_check = check_rate_limit($hash_ip);
if (!$rate_check['allowed']) {
    json_response([
        'error' => 'rate_limited',
        'message' => 'Too many tests. Please wait before testing again.',
        'retry_after' => $rate_check['retry_after']
    ], 429);
}

// Validate and sanitize inputs
$dl_mbps = validate_numeric($input['dl_mbps'] ?? 0, 0, 2000, 0);
$ul_mbps = validate_numeric($input['ul_mbps'] ?? 0, 0, 2000, 0);
$ping_ms = validate_numeric($input['ping_ms'] ?? 0, 0, 5000, 0);
$jitter_ms = validate_numeric($input['jitter_ms'] ?? 0, 0, 1000, 0);
$sample_ms = validate_numeric($input['sample_ms'] ?? 0, 0, 100000, 0);

$isp_name = sanitize_string($input['isp_name'] ?? 'Unknown', 128);
$asn = isset($input['asn']) && is_numeric($input['asn']) ? (int)$input['asn'] : null;
$city = sanitize_string($input['city'] ?? 'Unknown', 128);
$tech = sanitize_string($input['tech'] ?? 'Unknown', 32);
$device_type = sanitize_string($input['device_type'] ?? 'unknown', 16);

// Generate ULID
$id = ulid();

// Insert into database
$conn = get_db_connection();
if (!$conn) {
    json_response(['error' => 'Database connection failed'], 500);
}

$stmt = $conn->prepare("
    INSERT INTO tests (id, asn, isp_name, city, dl_mbps, ul_mbps, ping_ms, jitter_ms, tech, device_type, hash_ip, sample_ms)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    'sissddddsssi',
    $id,
    $asn,
    $isp_name,
    $city,
    $dl_mbps,
    $ul_mbps,
    $ping_ms,
    $jitter_ms,
    $tech,
    $device_type,
    $hash_ip,
    $sample_ms
);

if (!$stmt->execute()) {
    error_log("Failed to insert test: " . $stmt->error);
    json_response(['error' => 'Failed to save test results'], 500);
}

$stmt->close();

// Return success with share URL
json_response([
    'success' => true,
    'id' => $id,
    'share_url' => "/r/{$id}",
    'message' => 'Test results saved successfully'
], 201);
