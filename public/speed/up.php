<?php
/**
 * UPLOAD SPEED TEST endpoint
 * Receives data from client and reports bytes received
 */

ignore_user_abort(true);
set_time_limit(30);

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');

$origin = $_ENV['ALLOWED_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$start_time = microtime(true);

// Read the POST data without storing it
$received_bytes = 0;
$input = fopen('php://input', 'r');

while (!feof($input)) {
    $chunk = fread($input, 8192);
    $received_bytes += strlen($chunk);
}

fclose($input);

$end_time = microtime(true);
$duration_ms = ($end_time - $start_time) * 1000;

echo json_encode([
    'received_bytes' => $received_bytes,
    'duration_ms' => round($duration_ms, 2),
    'timestamp' => time()
], JSON_UNESCAPED_UNICODE);
