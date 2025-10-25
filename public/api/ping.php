<?php
/**
 * PING endpoint
 * Returns server timestamp for client-side ping calculation
 */

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

$origin = $_ENV['ALLOWED_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");

echo json_encode([
    'server_now' => microtime(true),
    'timestamp' => time()
], JSON_UNESCAPED_UNICODE);
