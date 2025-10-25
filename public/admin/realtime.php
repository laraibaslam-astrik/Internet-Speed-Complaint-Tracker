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
        .stat-mini { padding: 1rem; border-radius: 12px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.2s; }
        .stat-mini:hover { transform: translateY(-2px); }
        .time-badge { font-size: 0.85rem; font-weight: 600; }
        .event-badge { font-size: 0.75rem; font-weight: 600; padding: 0.35rem 0.6rem; }
        .ip-badge { background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 6px; font-family: 'Courier New', monospace; font-size: 0.85rem; }
        .location-text { font-size: 0.9rem; color: #6b7280; }
        .page-link-text { font-size: 0.85rem; color: #4b5563; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .refresh-indicator { display: inline-block; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .filter-card { background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%); }
        .activity-icon { font-size: 1.2rem; margin-right: 0.5rem; }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        
        <div class="flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">🔴 Live Activity Monitor</h2>
                    <p class="text-muted mb-0">Real-time visitor tracking • Auto-refreshes every 10 seconds</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-success" style="font-size: 1rem; padding: 0.5rem 1rem;">
                        <span class="online-indicator d-inline-block me-2"></span>
                        <span id="online-count"><?php echo get_online_users_count(); ?></span> Online Now
                    </span>
                    <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()" title="Refresh now">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-mini">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small">Active Now</div>
                                <h4 class="mb-0 fw-bold" id="stat-online"><?php echo get_online_users_count(); ?></h4>
                            </div>
                            <i class="bi bi-people-fill text-success" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-mini">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small">Last Hour</div>
                                <h4 class="mb-0 fw-bold"><?php echo $activity_count; ?></h4>
                            </div>
                            <i class="bi bi-activity text-primary" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-mini">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small">Total Today</div>
                                <h4 class="mb-0 fw-bold"><?php echo $conn->query("SELECT COUNT(DISTINCT session_id) as count FROM visitor_sessions WHERE DATE(first_visit) = CURDATE()")->fetch_assoc()['count']; ?></h4>
                            </div>
                            <i class="bi bi-calendar-check text-info" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-mini">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small">Avg. Duration</div>
                                <h4 class="mb-0 fw-bold"><?php 
                                $avg = $conn->query("SELECT AVG(total_duration_seconds) as avg FROM visitor_sessions WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 1 HOUR)")->fetch_assoc()['avg'];
                                echo $avg ? gmdate('i:s', $avg) : '0:00';
                                ?></h4>
                            </div>
                            <i class="bi bi-clock-fill text-warning" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Online Users -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center" style="border-bottom: 2px solid #10b981;">
                    <div>
                        <h5 class="mb-1"><i class="bi bi-broadcast text-success me-2"></i>Live Visitors</h5>
                        <small class="text-muted">People currently browsing your site</small>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small">Showing <?php echo min($per_page, $online_count); ?> of <?php echo $online_count; ?></div>
                        <small class="text-success"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Active in last 5 minutes</small>
                    </div>
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
                                    <td>
                                        <span class="ip-badge"><?php echo htmlspecialchars($user['ip_address']); ?></span>
                                    </td>
                                    <td>
                                        <i class="bi bi-geo-alt-fill text-muted" style="font-size: 0.9rem;"></i>
                                        <span class="location-text"><?php echo htmlspecialchars($user['city'] . ', ' . $user['country_code']); ?></span>
                                    </td>
                                    <td>
                                        <div class="page-link-text" title="<?php echo htmlspecialchars($user['current_page']); ?>">
                                            <i class="bi bi-file-text text-muted me-1"></i>
                                            <?php echo htmlspecialchars($user['current_page']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $seconds = $user['seconds_ago'];
                                        $time_text = $seconds < 60 ? $seconds . 's' : floor($seconds/60) . 'm ' . ($seconds%60) . 's';
                                        $badge_class = $seconds < 30 ? 'bg-success' : ($seconds < 120 ? 'bg-warning' : 'bg-secondary');
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?> time-badge">
                                            <span class="online-indicator d-inline-block me-1"></span>
                                            <?php echo $time_text; ?> ago
                                        </span>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <i class="bi bi-person-x" style="font-size: 3rem; color: #d1d5db;"></i>
                                        <p class="text-muted mt-2 mb-0">No visitors online right now</p>
                                        <small class="text-muted">Wait for someone to visit your site...</small>
                                    </td>
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
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center" style="border-bottom: 2px solid #667eea;">
                    <div>
                        <h5 class="mb-1"><i class="bi bi-lightning-fill text-warning me-2"></i>Activity Feed</h5>
                        <small class="text-muted">User interactions from the last hour</small>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small">Showing <?php echo min($per_page, $activity_count); ?> of <?php echo $activity_count; ?></div>
                        <small class="text-primary"><i class="bi bi-clock-history"></i> Last 60 minutes</small>
                    </div>
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
                                    <td>
                                        <span class="badge bg-<?php echo $badge_color; ?> event-badge">
                                            <?php 
                                            $icon = 'cursor';
                                            switch($activity['event_type']) {
                                                case 'click': $icon = 'cursor-fill'; break;
                                                case 'scroll': $icon = 'arrows-expand'; break;
                                                case 'form_focus': $icon = 'input-cursor-text'; break;
                                                case 'page_exit': $icon = 'box-arrow-right'; break;
                                            }
                                            ?>
                                            <i class="bi bi-<?php echo $icon; ?> me-1"></i>
                                            <?php echo htmlspecialchars($activity['event_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="ip-badge"><?php echo htmlspecialchars($activity['ip_address']); ?></span>
                                    </td>
                                    <td>
                                        <div style="max-width: 400px; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($activity['element_text']); ?>">
                                            <small><?php echo htmlspecialchars(substr($activity['element_text'], 0, 100)); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $seconds = $activity['seconds_ago'];
                                        if ($seconds < 60) {
                                            $time_display = $seconds . 's ago';
                                        } elseif ($seconds < 3600) {
                                            $time_display = floor($seconds/60) . 'm ago';
                                        } else {
                                            $time_display = floor($seconds/3600) . 'h ago';
                                        }
                                        $text_color = $seconds < 300 ? 'text-success' : 'text-muted';
                                        ?>
                                        <span class="<?php echo $text_color; ?> fw-semibold" style="font-size: 0.9rem;">
                                            <i class="bi bi-clock"></i> <?php echo $time_display; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <i class="bi bi-inbox" style="font-size: 3rem; color: #d1d5db;"></i>
                                        <p class="text-muted mt-2 mb-0">No activity in the last hour</p>
                                        <small class="text-muted">Activity will appear here as users interact with your site</small>
                                    </td>
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
