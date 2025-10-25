<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../lib/db.php';
$conn = get_db_connection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get visitor details
$stmt = $conn->prepare("SELECT * FROM visitor_sessions WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$visitor = $stmt->get_result()->fetch_assoc();

if (!$visitor) {
    die('Visitor not found');
}

// Pagination for pageviews
$pageviews_page = isset($_GET['pv_page']) ? max(1, (int)$_GET['pv_page']) : 1;
$pageviews_per_page = 20;
$pageviews_offset = ($pageviews_page - 1) * $pageviews_per_page;

// Get total pageviews count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM pageviews WHERE session_id = ?");
$stmt->bind_param('s', $visitor['session_id']);
$stmt->execute();
$pageviews_total = $stmt->get_result()->fetch_assoc()['count'];
$pageviews_total_pages = ceil($pageviews_total / $pageviews_per_page);

// Get pageviews
$stmt = $conn->prepare("SELECT * FROM pageviews WHERE session_id = ? ORDER BY view_timestamp DESC LIMIT ? OFFSET ?");
$stmt->bind_param('sii', $visitor['session_id'], $pageviews_per_page, $pageviews_offset);
$stmt->execute();
$pageviews = $stmt->get_result();

// Pagination for events
$events_page = isset($_GET['ev_page']) ? max(1, (int)$_GET['ev_page']) : 1;
$events_per_page = 50;
$events_offset = ($events_page - 1) * $events_per_page;

// Get total events count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM click_events WHERE session_id = ?");
$stmt->bind_param('s', $visitor['session_id']);
$stmt->execute();
$events_total = $stmt->get_result()->fetch_assoc()['count'];
$events_total_pages = ceil($events_total / $events_per_page);

// Get click events
$stmt = $conn->prepare("SELECT * FROM click_events WHERE session_id = ? ORDER BY event_timestamp DESC LIMIT ? OFFSET ?");
$stmt->bind_param('sii', $visitor['session_id'], $events_per_page, $events_offset);
$stmt->execute();
$events = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Detail - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #667eea 0%, #764ba2 100%); }
        .info-card { background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        
        <div class="flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Visitor Details</h2>
                <a href="visitors.php" class="btn btn-secondary">← Back to Visitors</a>
            </div>
            
            <!-- Visitor Info -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="info-card">
                        <h5 class="mb-3"><i class="bi bi-globe me-2"></i>Network Information</h5>
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td width="40%"><strong>IP Address:</strong></td>
                                <td><code><?php echo htmlspecialchars($visitor['ip_address']); ?></code></td>
                            </tr>
                            <tr>
                                <td><strong>ISP:</strong></td>
                                <td><?php echo htmlspecialchars($visitor['isp_name']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>ASN:</strong></td>
                                <td><?php echo $visitor['asn'] ?: 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Connection:</strong></td>
                                <td><?php echo htmlspecialchars($visitor['connection_type']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="info-card">
                        <h5 class="mb-3"><i class="bi bi-geo-alt me-2"></i>Location</h5>
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td width="40%"><strong>Country:</strong></td>
                                <td><?php echo htmlspecialchars($visitor['country']); ?> (<?php echo $visitor['country_code']; ?>)</td>
                            </tr>
                            <tr>
                                <td><strong>Region:</strong></td>
                                <td><?php echo htmlspecialchars($visitor['region']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>City:</strong></td>
                                <td><?php echo htmlspecialchars($visitor['city']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Coordinates:</strong></td>
                                <td><?php echo $visitor['latitude'] ? number_format($visitor['latitude'], 4) . ', ' . number_format($visitor['longitude'], 4) : 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Timezone:</strong></td>
                                <td><?php echo htmlspecialchars($visitor['timezone']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Postal Code:</strong></td>
                                <td><?php echo $visitor['postal_code'] ?: 'N/A'; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="info-card">
                        <h5 class="mb-3"><i class="bi bi-laptop me-2"></i>Device Information</h5>
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td width="40%"><strong>Device Type:</strong></td>
                                <td><?php echo htmlspecialchars($visitor['device_type']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>OS:</strong></td>
                                <td><?php echo htmlspecialchars($visitor['os'] . ' ' . $visitor['os_version']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Browser:</strong></td>
                                <td><?php echo htmlspecialchars($visitor['browser'] . ' ' . $visitor['browser_version']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Screen:</strong></td>
                                <td><?php echo $visitor['screen_resolution'] ?: 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Language:</strong></td>
                                <td><?php echo htmlspecialchars($visitor['language']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="info-card">
                        <h5 class="mb-3"><i class="bi bi-clock me-2"></i>Session Information</h5>
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td width="40%"><strong>First Visit:</strong></td>
                                <td><?php echo date('M d, Y H:i:s', strtotime($visitor['first_visit'])); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Last Activity:</strong></td>
                                <td><?php echo date('M d, Y H:i:s', strtotime($visitor['last_activity'])); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Total Pageviews:</strong></td>
                                <td><span class="badge bg-primary"><?php echo $visitor['total_pageviews']; ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>Landing Page:</strong></td>
                                <td><small><?php echo htmlspecialchars($visitor['landing_page']); ?></small></td>
                            </tr>
                            <tr>
                                <td><strong>Referrer:</strong></td>
                                <td><small><?php echo $visitor['referrer_domain'] ? htmlspecialchars($visitor['referrer_domain']) : 'Direct'; ?></small></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Pageviews -->
            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-eye me-2"></i>Pageviews</h5>
                    <span class="text-muted">Showing <?php echo min($pageviews_per_page, $pageviews_total); ?> of <?php echo $pageviews_total; ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Page URL</th>
                                    <th>Page Title</th>
                                    <th>Time</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($pv = $pageviews->fetch_assoc()): ?>
                                <tr>
                                    <td><small><?php echo htmlspecialchars($pv['page_url']); ?></small></td>
                                    <td><?php echo htmlspecialchars($pv['page_title'] ?: 'Untitled'); ?></td>
                                    <td><?php echo date('H:i:s', strtotime($pv['view_timestamp'])); ?></td>
                                    <td><?php echo $pv['time_on_page'] ? $pv['time_on_page'] . 's' : '-'; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if ($pageviews_total_pages > 1): ?>
                <div class="card-footer bg-white">
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php for($i = 1; $i <= min($pageviews_total_pages, 10); $i++): ?>
                            <li class="page-item <?php echo $i === $pageviews_page ? 'active' : ''; ?>">
                                <a class="page-link" href="?id=<?php echo $id; ?>&pv_page=<?php echo $i; ?>&ev_page=<?php echo $events_page; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Click Events -->
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-cursor me-2"></i>Click Events</h5>
                    <span class="text-muted">Showing <?php echo min($events_per_page, $events_total); ?> of <?php echo $events_total; ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Event Type</th>
                                    <th>Element</th>
                                    <th>Details</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($ev = $events->fetch_assoc()): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($ev['event_type']); ?></span></td>
                                    <td><code><?php echo htmlspecialchars($ev['element_id'] ?: $ev['element_class']); ?></code></td>
                                    <td><small><?php echo htmlspecialchars(substr($ev['element_text'], 0, 100)); ?></small></td>
                                    <td><?php echo date('H:i:s', strtotime($ev['event_timestamp'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if ($events_total_pages > 1): ?>
                <div class="card-footer bg-white">
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php for($i = 1; $i <= min($events_total_pages, 10); $i++): ?>
                            <li class="page-item <?php echo $i === $events_page ? 'active' : ''; ?>">
                                <a class="page-link" href="?id=<?php echo $id; ?>&pv_page=<?php echo $pageviews_page; ?>&ev_page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            <?php if ($events_total_pages > 10): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                            <li class="page-item">
                                <a class="page-link" href="?id=<?php echo $id; ?>&pv_page=<?php echo $pageviews_page; ?>&ev_page=<?php echo $events_total_pages; ?>"><?php echo $events_total_pages; ?></a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
