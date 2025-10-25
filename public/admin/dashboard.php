<?php
/**
 * Admin Dashboard - Complete Analytics
 * Real-time traffic monitoring with detailed tracking
 */

session_start();

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/util.php';
require_once __DIR__ . '/../lib/analytics.php';

$conn = get_db_connection();

// Get stats
$stats = [];

// Total visitors
$result = $conn->query("SELECT COUNT(DISTINCT session_id) as count FROM visitor_sessions");
$stats['total_visitors'] = $result->fetch_assoc()['count'];

// Today's visitors
$result = $conn->query("SELECT COUNT(DISTINCT session_id) as count FROM visitor_sessions WHERE DATE(first_visit) = CURDATE()");
$stats['today_visitors'] = $result->fetch_assoc()['count'];

// Online users
$stats['online_users'] = get_online_users_count();

// Total pageviews
$result = $conn->query("SELECT COUNT(*) as count FROM pageviews");
$stats['total_pageviews'] = $result->fetch_assoc()['count'];

// Total tests
$result = $conn->query("SELECT COUNT(*) as count FROM tests");
$stats['total_tests'] = $result->fetch_assoc()['count'];

// Today's tests
$result = $conn->query("SELECT COUNT(*) as count FROM tests WHERE DATE(ts) = CURDATE()");
$stats['today_tests'] = $result->fetch_assoc()['count'];

// Pagination for recent visitors
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total recent visitors count
$total_visitors = $conn->query("SELECT COUNT(*) as count FROM visitor_sessions")->fetch_assoc()['count'];
$total_pages = ceil($total_visitors / $per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pakistan Speed Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #667eea 0%, #764ba2 100%); }
        .stat-card { border-left: 4px solid; transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .online-indicator { width: 10px; height: 10px; background: #10b981; border-radius: 50%; animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .visitor-row:hover { background: #f8f9fa; }
        table { font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">Dashboard Overview</h2>
                <div>
                    <span class="badge bg-success">
                        <span class="online-indicator d-inline-block me-1"></span>
                        <?php echo $stats['online_users']; ?> Online Now
                    </span>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card border-0 shadow-sm" style="border-left-color: #667eea !important;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-muted small">Total Visitors</div>
                                    <h3 class="fw-bold mb-0"><?php echo number_format($stats['total_visitors']); ?></h3>
                                </div>
                                <div class="fs-1 text-primary">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card border-0 shadow-sm" style="border-left-color: #10b981 !important;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-muted small">Today's Visitors</div>
                                    <h3 class="fw-bold mb-0"><?php echo number_format($stats['today_visitors']); ?></h3>
                                </div>
                                <div class="fs-1 text-success">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card border-0 shadow-sm" style="border-left-color: #f59e0b !important;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-muted small">Total Tests</div>
                                    <h3 class="fw-bold mb-0"><?php echo number_format($stats['total_tests']); ?></h3>
                                </div>
                                <div class="fs-1 text-warning">
                                    <i class="bi bi-speedometer2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card border-0 shadow-sm" style="border-left-color: #ef4444 !important;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-muted small">Pageviews</div>
                                    <h3 class="fw-bold mb-0"><?php echo number_format($stats['total_pageviews']); ?></h3>
                                </div>
                                <div class="fs-1 text-danger">
                                    <i class="bi bi-eye"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Visitors -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Recent Visitors</h5>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="text-muted small">Showing <?php echo min($per_page, $total_visitors); ?> of <?php echo number_format($total_visitors); ?></span>
                        <a href="visitors.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>IP Address</th>
                                    <th>Location</th>
                                    <th>ISP</th>
                                    <th>Device</th>
                                    <th>First Visit</th>
                                    <th>Pages</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = $conn->query("
                                    SELECT 
                                        vs.*,
                                        ou.session_id IS NOT NULL as is_online
                                    FROM visitor_sessions vs
                                    LEFT JOIN online_users ou ON vs.session_id = ou.session_id 
                                        AND ou.last_ping >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                                    ORDER BY vs.last_activity DESC
                                    LIMIT $per_page OFFSET $offset
                                ");
                                
                                while ($visitor = $result->fetch_assoc()):
                                ?>
                                <tr class="visitor-row">
                                    <td>
                                        <span class="font-monospace"><?php echo htmlspecialchars(substr($visitor['ip_address'], 0, 15)); ?></span>
                                    </td>
                                    <td>
                                        <i class="bi bi-geo-alt text-muted me-1"></i>
                                        <?php echo htmlspecialchars($visitor['city'] . ', ' . $visitor['country_code']); ?>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($visitor['isp_name']); ?></small>
                                    </td>
                                    <td>
                                        <i class="bi bi-<?php echo $visitor['device_type'] === 'mobile' ? 'phone' : ($visitor['device_type'] === 'tablet' ? 'tablet' : 'laptop'); ?> me-1"></i>
                                        <?php echo htmlspecialchars($visitor['browser']); ?>
                                    </td>
                                    <td>
                                        <small><?php echo date('M d, H:i', strtotime($visitor['first_visit'])); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo $visitor['total_pageviews']; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($visitor['is_online']): ?>
                                            <span class="badge bg-success">
                                                <span class="online-indicator d-inline-block me-1"></span>Online
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Offline</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if ($total_pages > 1): ?>
                <div class="card-footer bg-white">
                    <nav>
                        <ul class="pagination pagination-sm mb-0 justify-content-center">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                            </li>
                            <?php endif; ?>
                            
                            <?php 
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);
                            
                            if ($start > 1): ?>
                                <li class="page-item"><a class="page-link" href="?page=1">1</a></li>
                                <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for($i = $start; $i <= $end; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($end < $total_pages): ?>
                                <?php if ($end < $total_pages - 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                                <li class="page-item"><a class="page-link" href="?page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a></li>
                            <?php endif; ?>
                            
                            <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Charts Row -->
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Traffic Today</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="trafficChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Top Countries</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $countries = $conn->query("
                                SELECT country, country_code, COUNT(*) as count
                                FROM visitor_sessions
                                WHERE DATE(first_visit) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                                GROUP BY country, country_code
                                ORDER BY count DESC
                                LIMIT 5
                            ");
                            
                            $total = $conn->query("SELECT COUNT(*) as count FROM visitor_sessions WHERE DATE(first_visit) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetch_assoc()['count'];
                            
                            while ($country = $countries->fetch_assoc()):
                                $percentage = $total > 0 ? ($country['count'] / $total * 100) : 0;
                            ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span><?php echo htmlspecialchars($country['country']); ?></span>
                                    <span class="fw-bold"><?php echo $country['count']; ?></span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Traffic chart (hourly)
        const trafficData = <?php
        $hourly = [];
        for ($i = 0; $i < 24; $i++) {
            $result = $conn->query("
                SELECT COUNT(*) as count 
                FROM visitor_sessions 
                WHERE HOUR(first_visit) = $i AND DATE(first_visit) = CURDATE()
            ");
            $hourly[] = $result->fetch_assoc()['count'];
        }
        echo json_encode($hourly);
        ?>;
        
        const ctx = document.getElementById('trafficChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: Array.from({length: 24}, (_, i) => i + ':00'),
                datasets: [{
                    label: 'Visitors',
                    data: trafficData,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
        
        // Auto-refresh stats every 30 seconds
        setInterval(() => location.reload(), 30000);
    </script>
</body>
</html>
