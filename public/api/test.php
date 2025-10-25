<?php
// Direct API test - shows exact errors
header('Content-Type: text/plain');

echo "=== API Debug Test ===\n\n";

// Check if db.php exists
$dbPath = __DIR__ . '/../lib/db.php';
echo "1. db.php exists: " . (file_exists($dbPath) ? "YES" : "NO") . "\n";

// Check if we can include it
if (file_exists($dbPath)) {
    try {
        require_once $dbPath;
        echo "2. db.php loaded: YES\n";
        
        // Check function
        if (function_exists('get_db_connection')) {
            echo "3. get_db_connection() exists: YES\n";
            
            // Try connection
            $conn = get_db_connection();
            if ($conn) {
                echo "4. Database connected: YES\n";
                
                // Try query
                $result = $conn->query("SHOW TABLES LIKE 'tests'");
                if ($result && $result->num_rows > 0) {
                    echo "5. 'tests' table exists: YES\n";
                } else {
                    echo "5. 'tests' table exists: NO\n";
                }
            } else {
                echo "4. Database connected: NO\n";
            }
        } else {
            echo "3. get_db_connection() exists: NO\n";
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "2. Cannot find db.php at: $dbPath\n";
}

echo "\n=== Test map-data.php directly ===\n";
$mapDataPath = __DIR__ . '/map-data.php';
echo "map-data.php exists: " . (file_exists($mapDataPath) ? "YES" : "NO") . "\n";

echo "\n=== Try fetching map-data.php ===\n";
$output = @file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/api/map-data.php');
if ($output) {
    echo "Response received: YES\n";
    echo "Content: " . substr($output, 0, 200) . "...\n";
} else {
    echo "Response received: NO (500 error)\n";
}

echo "\n=== PHP Info ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Error Reporting: " . error_reporting() . "\n";
echo "Display Errors: " . ini_get('display_errors') . "\n";
