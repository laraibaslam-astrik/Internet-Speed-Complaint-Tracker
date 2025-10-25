<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/analytics.php';
$conn = get_db_connection();

// Pagination parameters
$online_page = isset($_GET['online_page']) ? max(1, (int)$_GET['online_page']) : 1;
$activity_page = isset($_GET['activity_page']) ? max(1, (int)$_GET['activity_page']) : 1;
$per_page = 20;

// Get online users count
$online_count = get_online_users_count();
$online_offset = ($online_page - 1) * $per_page;
$online_total_pages = ceil($online_count / $per_page);

// Get activity count
$activity_count = $conn->query("SELECT COUNT(*) as count FROM click_events WHERE event_timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)")->fetch_assoc()['count'];
$activity_offset = ($activity_page - 1) * $per_page;
$activity_total_pages = ceil($activity_count / $per_page);
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
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Currently Online</h5>
                    <span class="text-muted">Showing <?php echo min($per_page, $online_count); ?> of <?php echo $online_count; ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>IP Address</th>
                                    <th>Location</th>
                                    <th>Current Page</th>
                                    <th>Last Active</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $online_users = $conn->query("
                                    SELECT 
                                        vs.ip_address,
                                        vs.city,
                                        vs.country_code,
                                        ou.current_page,
                                        ou.last_ping,
                                        TIMESTAMPDIFF(SECOND, ou.last_ping, NOW()) as seconds_ago
                                    FROM online_users ou
                                    JOIN visitor_sessions vs ON ou.session_id = vs.session_id
                                    WHERE ou.last_ping >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                                    ORDER BY ou.last_ping DESC
                                    LIMIT $per_page OFFSET $online_offset
                                ");
                                
                                if ($online_users->num_rows > 0):
                                    while($user = $online_users->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($user['ip_address']); ?></code></td>
                                    <td><?php echo htmlspecialchars($user['city'] . ', ' . $user['country_code']); ?></td>
                                    <td><small><?php echo htmlspecialchars($user['current_page']); ?></small></td>
                                    <td>
                                        <span class="badge bg-success">
                                            <span class="online-indicator d-inline-block me-1"></span>
                                            <?php echo $user['seconds_ago']; ?>s ago
                                        </span>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No users currently online</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if ($online_total_pages > 1): ?>
                <div class="card-footer bg-white">
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php for($i = 1; $i <= min($online_total_pages, 10); $i++): ?>
                            <li class="page-item <?php echo $i === $online_page ? 'active' : ''; ?>">
                                <a class="page-link" href="?online_page=<?php echo $i; ?>&activity_page=<?php echo $activity_page; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            <?php if ($online_total_pages > 10): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                            <li class="page-item">
                                <a class="page-link" href="?online_page=<?php echo $online_total_pages; ?>&activity_page=<?php echo $activity_page; ?>"><?php echo $online_total_pages; ?></a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-activity me-2"></i>Recent Activity (Last Hour)</h5>
                    <span class="text-muted">Showing <?php echo min($per_page, $activity_count); ?> of <?php echo $activity_count; ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Event Type</th>
                                    <th>User (IP)</th>
                                    <th>Details</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $activities = $conn->query("
                                    SELECT 
                                        ce.event_type,
                                        ce.element_text,
                                        ce.event_timestamp,
                                        vs.ip_address,
                                        TIMESTAMPDIFF(SECOND, ce.event_timestamp, NOW()) as seconds_ago
                                    FROM click_events ce
                                    JOIN visitor_sessions vs ON ce.session_id = vs.session_id
                                    WHERE ce.event_timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                                    ORDER BY ce.event_timestamp DESC
                                    LIMIT $per_page OFFSET $activity_offset
                                ");
                                
                                if ($activities->num_rows > 0):
                                    while($activity = $activities->fetch_assoc()):
                                        $badge_color = 'secondary';
                                        switch($activity['event_type']) {
                                            case 'click': $badge_color = 'primary'; break;
                                            case 'scroll': $badge_color = 'info'; break;
                                            case 'form_focus': $badge_color = 'warning'; break;
                                            case 'page_exit': $badge_color = 'danger'; break;
                                        }
                                ?>
                                <tr>
                                    <td><span class="badge bg-<?php echo $badge_color; ?>"><?php echo htmlspecialchars($activity['event_type']); ?></span></td>
                                    <td><code><?php echo htmlspecialchars($activity['ip_address']); ?></code></td>
                                    <td><small><?php echo htmlspecialchars(substr($activity['element_text'], 0, 100)); ?></small></td>
                                    <td><?php echo $activity['seconds_ago']; ?>s ago</td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No recent activity</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if ($activity_total_pages > 1): ?>
                <div class="card-footer bg-white">
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php for($i = 1; $i <= min($activity_total_pages, 10); $i++): ?>
                            <li class="page-item <?php echo $i === $activity_page ? 'active' : ''; ?>">
                                <a class="page-link" href="?online_page=<?php echo $online_page; ?>&activity_page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            <?php if ($activity_total_pages > 10): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                            <li class="page-item">
                                <a class="page-link" href="?online_page=<?php echo $online_page; ?>&activity_page=<?php echo $activity_total_pages; ?>"><?php echo $activity_total_pages; ?></a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
    // Update online count badge only
    function updateOnlineCount() {
        fetch('api/realtime_data.php?type=online_count')
            .then(r => r.json())
            .then(data => {
                document.getElementById('online-count').textContent = data.count || 0;
            })
            .catch(e => console.error('Error updating count:', e));
    }
    
    // Auto-refresh page every 10 seconds to show new data
    let autoRefreshInterval;
    function startAutoRefresh() {
        // Update count every 3 seconds
        setInterval(updateOnlineCount, 3000);
        
        // Full page refresh every 10 seconds
        autoRefreshInterval = setInterval(() => {
            window.location.reload();
        }, 10000);
    }
    
    // Start auto-refresh
    startAutoRefresh();
    
    // Update count immediately
    updateOnlineCount();
    </script>
</body>
</html>
