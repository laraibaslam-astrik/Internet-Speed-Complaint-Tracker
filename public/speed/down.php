<?php
/**
 * DOWNLOAD SPEED TEST endpoint
 * Sends random data to client for download speed measurement
 */

ignore_user_abort(true);
set_time_limit(30);

header('Content-Type: application/octet-stream');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$origin = $_ENV['ALLOWED_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");

// Get requested size in MB (default 4MB)
$size_mb = isset($_GET['b']) ? (int)$_GET['b'] : 4;
$size_mb = max(1, min(10, $size_mb)); // Clamp between 1-10 MB

$chunk_size = 1024 * 256; // 256KB chunks
$total_bytes = $size_mb * 1024 * 1024;
$chunks = (int)ceil($total_bytes / $chunk_size);

// Generate and send random data in chunks
for ($i = 0; $i < $chunks; $i++) {
    if (connection_aborted()) {
        break;
    }
    
    // Generate random bytes
    echo random_bytes($chunk_size);
    
    // Flush output buffer
    if (ob_get_level() > 0) {
        ob_flush();
    }
    flush();
}

exit;
