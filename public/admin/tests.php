<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../lib/db.php';
$conn = get_db_connection();

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Get total
$total = $conn->query("SELECT COUNT(*) as total FROM tests")->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

// Get tests
$tests = $conn->query("SELECT * FROM tests ORDER BY ts DESC LIMIT $per_page OFFSET $offset");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Speed Tests - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        
        <div class="flex-grow-1 p-4">
            <h2 class="mb-4">All Speed Tests</h2>
            
            <div class="card">
                <div class="card-header bg-white">
                    <strong>Total: <?php echo number_format($total); ?> tests</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Time</th>
                                    <th>IP</th>
                                    <th>Location</th>
                                    <th>ISP</th>
                                    <th>Download</th>
                                    <th>Upload</th>
                                    <th>Ping</th>
                                    <th>Jitter</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($test = $tests->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('M d, H:i', strtotime($test['ts'])); ?></td>
                                    <td><code><?php echo htmlspecialchars(substr($test['ip'], 0, 15)); ?></code></td>
                                    <td><?php echo htmlspecialchars($test['city']); ?></td>
                                    <td><small><?php echo htmlspecialchars($test['isp']); ?></small></td>
                                    <td><strong><?php echo number_format($test['download_mbps'], 1); ?></strong> Mbps</td>
                                    <td><strong><?php echo number_format($test['upload_mbps'], 1); ?></strong> Mbps</td>
                                    <td><?php echo number_format($test['ping_ms'], 0); ?> ms</td>
                                    <td><?php echo number_format($test['jitter_ms'], 1); ?> ms</td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if ($total_pages > 1): ?>
                <div class="card-footer">
                    <nav>
                        <ul class="pagination mb-0">
                            <?php for($i = 1; $i <= min($total_pages, 10); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
