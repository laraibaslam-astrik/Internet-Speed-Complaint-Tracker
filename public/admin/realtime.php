<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/analytics.php';
$conn = get_db_connection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-time - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #667eea 0%, #764ba2 100%); }
        .online-indicator { width: 10px; height: 10px; background: #10b981; border-radius: 50%; animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        
        <div class="flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Real-time Activity</h2>
                <div>
                    <span class="badge bg-success">
                        <span class="online-indicator d-inline-block me-1"></span>
                        <span id="online-count"><?php echo get_online_users_count(); ?></span> Online
                    </span>
                </div>
            </div>
            
            <!-- Online Users -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Currently Online</h5>
                </div>
                <div class="card-body p-0">
                    <div id="online-users" class="table-responsive">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-activity me-2"></i>Recent Activity</h5>
                </div>
                <div class="card-body p-0">
                    <div id="recent-activity" class="table-responsive">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function loadOnlineUsers() {
        fetch('api/realtime_data.php?type=online')
            .then(r => r.json())
            .then(data => {
                document.getElementById('online-count').textContent = data.count || 0;
                
                let html = '<table class="table table-hover mb-0"><thead class="table-light"><tr><th>IP</th><th>Location</th><th>Page</th><th>Last Active</th></tr></thead><tbody>';
                
                if (data.users && data.users.length > 0) {
                    data.users.forEach(user => {
                        html += `<tr>
                            <td><code>${user.ip_address}</code></td>
                            <td>${user.city}, ${user.country_code}</td>
                            <td><small>${user.current_page}</small></td>
                            <td>${user.last_ping_relative}</td>
                        </tr>`;
                    });
                } else {
                    html += '<tr><td colspan="4" class="text-center text-muted">No users online</td></tr>';
                }
                
                html += '</tbody></table>';
                document.getElementById('online-users').innerHTML = html;
            })
            .catch(e => console.error('Error loading online users:', e));
    }
    
    function loadRecentActivity() {
        fetch('api/realtime_data.php?type=activity')
            .then(r => r.json())
            .then(data => {
                let html = '<table class="table table-hover mb-0"><thead class="table-light"><tr><th>Event</th><th>User</th><th>Details</th><th>Time</th></tr></thead><tbody>';
                
                if (data.events && data.events.length > 0) {
                    data.events.forEach(event => {
                        html += `<tr>
                            <td><span class="badge bg-primary">${event.event_type}</span></td>
                            <td><small><code>${event.ip_address}</code></small></td>
                            <td><small>${event.element_text || '-'}</small></td>
                            <td>${event.time_relative}</td>
                        </tr>`;
                    });
                } else {
                    html += '<tr><td colspan="4" class="text-center text-muted">No recent activity</td></tr>';
                }
                
                html += '</tbody></table>';
                document.getElementById('recent-activity').innerHTML = html;
            })
            .catch(e => console.error('Error loading activity:', e));
    }
    
    // Load initially
    loadOnlineUsers();
    loadRecentActivity();
    
    // Refresh every 3 seconds
    setInterval(() => {
        loadOnlineUsers();
        loadRecentActivity();
    }, 3000);
    </script>
</body>
</html>
