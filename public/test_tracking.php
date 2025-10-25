<?php
/**
 * Tracking System Test & Debug
 * DELETE IN PRODUCTION
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking System Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 2rem; }
        .test-card { background: white; border-radius: 12px; padding: 2rem; margin-bottom: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        pre { background: #f3f4f6; padding: 1rem; border-radius: 6px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">🔍 Tracking System Test</h2>
        
        <!-- Analytics Library Check -->
        <div class="test-card">
            <h5>1. Analytics Library</h5>
            <?php
            $analyticsPath = __DIR__ . '/lib/analytics.php';
            if (file_exists($analyticsPath)) {
                echo "<div class='success'>✓ analytics.php exists</div>";
                require_once $analyticsPath;
                
                // Check functions
                $functions = ['track_visitor', 'track_pageview', 'track_event', 'get_session_id'];
                foreach ($functions as $fn) {
                    if (function_exists($fn)) {
                        echo "<div class='success'>✓ Function: $fn()</div>";
                    } else {
                        echo "<div class='error'>✗ Missing: $fn()</div>";
                    }
                }
            } else {
                echo "<div class='error'>✗ analytics.php NOT FOUND</div>";
                echo "<p class='small text-muted'>Run: Setup analytics schema first</p>";
            }
            ?>
        </div>
        
        <!-- Database Connection -->
        <div class="test-card">
            <h5>2. Database Connection</h5>
            <?php
            require_once __DIR__ . '/lib/db.php';
            $conn = get_db_connection();
            
            if ($conn) {
                echo "<div class='success'>✓ Database connected</div>";
                
                // Check tables
                $tables = ['visitor_sessions', 'pageviews', 'click_events', 'online_users'];
                foreach ($tables as $table) {
                    $result = $conn->query("SHOW TABLES LIKE '$table'");
                    if ($result && $result->num_rows > 0) {
                        echo "<div class='success'>✓ Table: $table</div>";
                    } else {
                        echo "<div class='error'>✗ Missing table: $table</div>";
                    }
                }
            } else {
                echo "<div class='error'>✗ Database connection FAILED</div>";
            }
            ?>
        </div>
        
        <!-- Track.php Endpoint -->
        <div class="test-card">
            <h5>3. Tracking Endpoint</h5>
            <div id="endpoint-test">Testing...</div>
        </div>
        
        <!-- Map Data API -->
        <div class="test-card">
            <h5>4. Map Data API</h5>
            <div id="map-api-test">Testing...</div>
        </div>
        
        <!-- Live Tracking Test -->
        <div class="test-card">
            <h5>5. Live Tracking Test</h5>
            <div class="mb-3">
                <button class="btn btn-primary" onclick="testTracking()">Test Tracking</button>
            </div>
            <div id="tracking-results"></div>
        </div>
        
        <div class="alert alert-warning">
            <strong>⚠️ DELETE THIS FILE IN PRODUCTION!</strong><br>
            <code>rm public/test_tracking.php</code>
        </div>
    </div>
    
    <script>
    // Test tracking endpoint
    fetch('/track.php?action=init', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'page_url=/test&page_title=Test'
    })
    .then(r => r.json())
    .then(data => {
        const el = document.getElementById('endpoint-test');
        if (data.success) {
            el.innerHTML = '<div class="success">✓ Tracking endpoint working</div>';
            el.innerHTML += `<div class="small text-muted">Session ID: ${data.session_id || 'N/A'}</div>`;
        } else {
            el.innerHTML = '<div class="error">✗ Tracking endpoint error</div>';
            el.innerHTML += `<pre>${JSON.stringify(data, null, 2)}</pre>`;
        }
    })
    .catch(e => {
        document.getElementById('endpoint-test').innerHTML = 
            '<div class="error">✗ Tracking endpoint FAILED</div>' +
            '<div class="small text-muted">' + e.message + '</div>';
    });
    
    // Test map API
    fetch('/api/map-data.php')
    .then(r => r.json())
    .then(data => {
        const el = document.getElementById('map-api-test');
        if (data.success) {
            el.innerHTML = '<div class="success">✓ Map API working</div>';
            el.innerHTML += `<div class="small text-muted">Cities: ${data.cities.length}</div>`;
        } else {
            el.innerHTML = '<div class="error">✗ Map API error</div>';
        }
    })
    .catch(e => {
        document.getElementById('map-api-test').innerHTML = 
            '<div class="error">✗ Map API FAILED</div>' +
            '<div class="small text-muted">' + e.message + '</div>';
    });
    
    // Test tracking function
    function testTracking() {
        const results = document.getElementById('tracking-results');
        results.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Testing...';
        
        let sessionId = null;
        
        // Init
        fetch('/track.php?action=init', {
            method: 'POST',
            body: new URLSearchParams({
                page_url: '/test',
                page_title: 'Test Page'
            })
        })
        .then(r => r.json())
        .then(data => {
            sessionId = data.session_id;
            let html = '<div class="success">✓ Session initialized: ' + sessionId + '</div>';
            
            // Test event tracking
            return fetch('/track.php?action=event', {
                method: 'POST',
                body: new URLSearchParams({
                    session_id: sessionId,
                    event_type: 'test_click',
                    element_id: 'test-button',
                    element_text: 'Test Event'
                })
            }).then(r => r.json()).then(eventData => {
                html += '<div class="success">✓ Event tracked</div>';
                
                // Test heartbeat
                return fetch('/track.php?action=heartbeat', {
                    method: 'POST',
                    body: new URLSearchParams({ session_id: sessionId })
                }).then(r => r.json()).then(hbData => {
                    html += '<div class="success">✓ Heartbeat sent</div>';
                    html += '<div class="alert alert-success mt-3">All tracking tests PASSED!</div>';
                    results.innerHTML = html;
                });
            });
        })
        .catch(e => {
            results.innerHTML = '<div class="error">✗ Tracking test FAILED: ' + e.message + '</div>';
        });
    }
    </script>
</body>
</html>
