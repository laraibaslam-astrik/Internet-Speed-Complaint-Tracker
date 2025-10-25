<?php
/**
 * Database connection using mysqli
 * Loads configuration from .env file
 */

function load_env($file = __DIR__ . '/../.env') {
    if (!file_exists($file)) {
        return;
    }
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

load_env();

function get_db_connection() {
    static $conn = null;
    
    if ($conn !== null) {
        return $conn;
    }
    
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? 'speedtracker';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';
    
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        return null;
    }
    
    $conn->set_charset('utf8mb4');
    
    return $conn;
}

function close_db_connection() {
    $conn = get_db_connection();
    if ($conn) {
        $conn->close();
    }
}
