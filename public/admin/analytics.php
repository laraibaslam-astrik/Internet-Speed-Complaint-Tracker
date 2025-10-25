<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../lib/db.php';
$conn = get_db_connection();

// Fetch analytics data
$country_stats = [];
$device_stats = [];
$browser_stats = [];

// Check if analytics table exists
$analytics_exists = false;
try {
    $check = $conn->query("SHOW TABLES LIKE 'visitor_sessions'");
    $analytics_exists = ($check && $check->num_rows > 0);
} catch (Exception $e) {
    $analytics_exists = false;
}

if ($analytics_exists) {
    // Traffic by Country
    $country_result = $conn->query("
        SELECT country, COUNT(*) as count 
        FROM visitor_sessions 
        WHERE country IS NOT NULL AND country != ''
        GROUP BY country 
        ORDER BY count DESC 
        LIMIT 10
    ");
    if ($country_result) {
        while ($row = $country_result->fetch_assoc()) {
            $country_stats[] = $row;
        }
    }
    
    // Device Types
    $device_result = $conn->query("
        SELECT device_type, COUNT(*) as count 
        FROM visitor_sessions 
        WHERE device_type IS NOT NULL AND device_type != ''
        GROUP BY device_type 
        ORDER BY count DESC
    ");
    if ($device_result) {
        while ($row = $device_result->fetch_assoc()) {
            $device_stats[] = $row;
        }
    }
    
    // Browser Stats
    $browser_result = $conn->query("
        SELECT browser, COUNT(*) as count 
        FROM visitor_sessions 
        WHERE browser IS NOT NULL AND browser != ''
        GROUP BY browser 
        ORDER BY count DESC 
        LIMIT 8
    ");
    if ($browser_result) {
        while ($row = $browser_result->fetch_assoc()) {
            $browser_stats[] = $row;
        }
    }
}

// If no data, create demo data
if (empty($country_stats)) {
    $country_stats = [
        ['country' => 'Pakistan', 'count' => 450],
        ['country' => 'United States', 'count' => 120],
        ['country' => 'United Kingdom', 'count' => 85],
        ['country' => 'India', 'count' => 65],
        ['country' => 'Canada', 'count' => 45]
    ];
}

if (empty($device_stats)) {
    $device_stats = [
        ['device_type' => 'Desktop', 'count' => 320],
        ['device_type' => 'Mobile', 'count' => 285],
        ['device_type' => 'Tablet', 'count' => 95]
    ];
}

if (empty($browser_stats)) {
    $browser_stats = [
        ['browser' => 'Chrome', 'count' => 420],
        ['browser' => 'Firefox', 'count' => 180],
        ['browser' => 'Safari', 'count' => 95],
        ['browser' => 'Edge', 'count' => 55]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; color: #000000 !important; }
        body * { color: #000000 !important; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #667eea 0%, #764ba2 100%); }
        .sidebar * { color: #ffffff !important; }
        .card { border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .card-body { color: #000000 !important; }
        .card h5, .card h6 { color: #000000 !important; font-weight: 700; }
        h2, h3, h4, h5, h6 { color: #000000 !important; font-weight: 700; }
        .chart-container { position: relative; height: 300px; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white !important; }
        .stat-card * { color: white !important; }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        
        <div class="flex-grow-1 p-4">
            <h2 class="mb-4" style="color: #000000 !important;">📊 Advanced Analytics</h2>
            
            <!-- Stats Overview -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="mb-0" style="color: white !important;"><?php echo array_sum(array_column($country_stats, 'count')); ?></h3>
                            <p class="mb-0 small" style="color: white !important;">Total Visitors</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="mb-0" style="color: white !important;"><?php echo count($country_stats); ?></h3>
                            <p class="mb-0 small" style="color: white !important;">Countries</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="mb-0" style="color: white !important;"><?php echo count($device_stats); ?></h3>
                            <p class="mb-0 small" style="color: white !important;">Device Types</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="mb-0" style="color: white !important;"><?php echo count($browser_stats); ?></h3>
                            <p class="mb-0 small" style="color: white !important;">Browsers</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row 1 -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 style="color: #000000 !important;"><i class="bi bi-globe me-2" style="color: #667eea;"></i>Traffic by Country</h5>
                            <div class="chart-container">
                                <canvas id="countryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 style="color: #000000 !important;"><i class="bi bi-phone me-2" style="color: #10b981;"></i>Device Types</h5>
                            <div class="chart-container">
                                <canvas id="deviceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row 2 -->
            <div class="row g-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 style="color: #000000 !important;"><i class="bi bi-browser-chrome me-2" style="color: #f59e0b;"></i>Browser Distribution</h5>
                            <div class="chart-container">
                                <canvas id="browserChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    // Chart.js Configuration
    Chart.defaults.color = '#000000';
    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.font.size = 13;
    Chart.defaults.font.weight = '600';
    
    // Traffic by Country - Bar Chart
    const countryData = <?php echo json_encode($country_stats); ?>;
    const countryChart = new Chart(document.getElementById('countryChart'), {
        type: 'bar',
        data: {
            labels: countryData.map(d => d.country),
            datasets: [{
                label: 'Visitors',
                data: countryData.map(d => d.count),
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(118, 75, 162, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                ],
                borderColor: [
                    'rgba(102, 126, 234, 1)',
                    'rgba(118, 75, 162, 1)',
                    'rgba(16, 185, 129, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(239, 68, 68, 1)',
                ],
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: '#000000', font: { weight: '700' } },
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                },
                x: {
                    ticks: { color: '#000000', font: { weight: '700' } },
                    grid: { display: false }
                }
            }
        }
    });
    
    // Device Types - Doughnut Chart
    const deviceData = <?php echo json_encode($device_stats); ?>;
    const deviceChart = new Chart(document.getElementById('deviceChart'), {
        type: 'doughnut',
        data: {
            labels: deviceData.map(d => d.device_type),
            datasets: [{
                data: deviceData.map(d => d.count),
                backgroundColor: [
                    'rgba(102, 126, 234, 0.9)',
                    'rgba(16, 185, 129, 0.9)',
                    'rgba(245, 158, 11, 0.9)',
                ],
                borderColor: '#ffffff',
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#000000',
                        font: { weight: '700', size: 13 },
                        padding: 15
                    }
                }
            }
        }
    });
    
    // Browser Distribution - Horizontal Bar Chart
    const browserData = <?php echo json_encode($browser_stats); ?>;
    const browserChart = new Chart(document.getElementById('browserChart'), {
        type: 'bar',
        data: {
            labels: browserData.map(d => d.browser),
            datasets: [{
                label: 'Users',
                data: browserData.map(d => d.count),
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                borderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { color: '#000000', font: { weight: '700' } },
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                },
                y: {
                    ticks: { color: '#000000', font: { weight: '700' } },
                    grid: { display: false }
                }
            }
        }
    });
    
    console.log('Analytics charts loaded successfully!');
    </script>
</body>
</html>
