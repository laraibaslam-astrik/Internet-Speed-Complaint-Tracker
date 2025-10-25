<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../lib/db.php';
$conn = get_db_connection();

// Get filter parameters
$search = $_GET['search'] ?? '';
$filter_date = $_GET['date'] ?? '';
$filter_city = $_GET['city'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Build query
$where = ['1=1'];
$params = [];
$types = '';

if ($search) {
    $where[] = "(ip_address LIKE ? OR city LIKE ? OR isp_name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if ($filter_date) {
    $where[] = "DATE(first_visit) = ?";
    $params[] = $filter_date;
    $types .= 's';
}

if ($filter_city) {
    $where[] = "city = ?";
    $params[] = $filter_city;
    $types .= 's';
}

$where_clause = implode(' AND ', $where);

// Get total count
$count_query = "SELECT COUNT(*) as total FROM visitor_sessions WHERE $where_clause";
$stmt = $conn->prepare($count_query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

// Get visitors
$query = "SELECT * FROM visitor_sessions WHERE $where_clause ORDER BY last_activity DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$visitors = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitors - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #667eea 0%, #764ba2 100%); }
        .visitor-row:hover { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        
        <div class="flex-grow-1 p-4">
            <h2 class="mb-4">All Visitors</h2>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Search IP, City, ISP..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($filter_date); ?>">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="city" class="form-control" placeholder="Filter by city" value="<?php echo htmlspecialchars($filter_city); ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Results -->
            <div class="card">
                <div class="card-header bg-white">
                    <strong>Total: <?php echo number_format($total); ?> visitors</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>IP</th>
                                    <th>Location</th>
                                    <th>ISP</th>
                                    <th>Device</th>
                                    <th>First Visit</th>
                                    <th>Pages</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($v = $visitors->fetch_assoc()): ?>
                                <tr class="visitor-row">
                                    <td><code><?php echo htmlspecialchars($v['ip_address']); ?></code></td>
                                    <td><?php echo htmlspecialchars($v['city'] . ', ' . $v['country_code']); ?></td>
                                    <td><small><?php echo htmlspecialchars($v['isp_name'] ?: 'Unknown'); ?></small></td>
                                    <td><small><?php echo htmlspecialchars($v['device_type'] . ' / ' . $v['browser']); ?></small></td>
                                    <td><small><?php echo date('M d, H:i', strtotime($v['first_visit'])); ?></small></td>
                                    <td><span class="badge bg-secondary"><?php echo $v['total_pageviews']; ?></span></td>
                                    <td>
                                        <a href="visitor_detail.php?id=<?php echo $v['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                    </td>
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
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&date=<?php echo urlencode($filter_date); ?>&city=<?php echo urlencode($filter_city); ?>"><?php echo $i; ?></a>
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
