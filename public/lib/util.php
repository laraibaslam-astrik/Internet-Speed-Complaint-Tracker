<?php
/**
 * Utility functions for the speed tracker
 */

function env($key, $default = null) {
    return $_ENV[$key] ?? $default;
}

function json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    
    $origin = env('ALLOWED_ORIGIN', '*');
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function ulid() {
    // Simple ULID generator (timestamp + random)
    $time = str_pad(base_convert((int)(microtime(true) * 1000), 10, 32), 10, '0', STR_PAD_LEFT);
    $random = '';
    for ($i = 0; $i < 16; $i++) {
        $random .= base_convert(random_int(0, 31), 10, 32);
    }
    return strtoupper($time . $random);
}

function client_ip() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    // Check for proxy headers
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
    } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
    
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

function hash_ip($ip, $salt) {
    return hash('sha256', $ip . $salt);
}

function clamp($value, $min, $max) {
    return max($min, min($max, $value));
}

function percentile($arr, $p) {
    if (empty($arr)) return 0;
    sort($arr);
    $index = ceil(count($arr) * $p / 100) - 1;
    return $arr[max(0, $index)];
}

function validate_numeric($value, $min, $max, $default = 0) {
    if (!is_numeric($value)) return $default;
    return clamp((float)$value, $min, $max);
}

function sanitize_string($str, $maxlen = 128) {
    $str = strip_tags(trim($str));
    return substr($str, 0, $maxlen);
}
