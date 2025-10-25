<?php
/**
 * Result sharing page
 * Displays a specific test result with social sharing
 */

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/util.php';

$test_id = $_GET['id'] ?? null;

if (!$test_id) {
    header('Location: /');
    exit;
}

// Fetch test result
$conn = get_db_connection();
if (!$conn) {
    header('Location: /');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM tests WHERE id = ? LIMIT 1");
$stmt->bind_param('s', $test_id);
$stmt->execute();
$result = $stmt->get_result();
$test = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$test) {
    header('Location: /');
    exit;
}

// Format data
$dl = number_format($test['dl_mbps'], 1);
$ul = number_format($test['ul_mbps'], 1);
$ping = number_format($test['ping_ms'], 0);
$jitter = number_format($test['jitter_ms'], 1);
$city = htmlspecialchars($test['city']);
$isp = htmlspecialchars($test['isp_name']);
$date = date('F j, Y', strtotime($test['ts']));

$pageTitle = "Speed Test Result - {$city}, {$isp}";
$pageDescription = "Download: {$dl} Mbps, Upload: {$ul} Mbps, Ping: {$ping} ms - Tested on {$date}";
$shareUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/r/{$test_id}";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $pageDescription; ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="<?php echo $pageDescription; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $shareUrl; ?>">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $pageTitle; ?>">
    <meta name="twitter:description" content="<?php echo $pageDescription; ?>">
    
    <title><?php echo $pageTitle; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .result-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .result-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .metric-value {
            font-size: 3rem;
            font-weight: 700;
            line-height: 1;
        }
        
        .metric-label {
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 1px;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-6">
                <div class="result-card">
                    <div class="result-header">
                        <h1 class="h3 mb-2">
                            <i class="bi bi-speedometer2 me-2"></i>
                            Pakistan Internet Speed Test
                        </h1>
                        <p class="mb-0 opacity-75"><?php echo $city; ?> • <?php echo $isp; ?></p>
                        <small class="opacity-75"><?php echo $date; ?></small>
                    </div>
                    
                    <div class="p-4">
                        <!-- Metrics Grid -->
                        <div class="row g-4 mb-4">
                            <div class="col-6">
                                <div class="text-center p-3 border rounded">
                                    <div class="metric-label text-primary mb-2">
                                        <i class="bi bi-download me-1"></i>
                                        Download
                                    </div>
                                    <div class="metric-value text-primary"><?php echo $dl; ?></div>
                                    <small class="text-muted">Mbps</small>
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="text-center p-3 border rounded">
                                    <div class="metric-label text-success mb-2">
                                        <i class="bi bi-upload me-1"></i>
                                        Upload
                                    </div>
                                    <div class="metric-value text-success"><?php echo $ul; ?></div>
                                    <small class="text-muted">Mbps</small>
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="text-center p-3 border rounded">
                                    <div class="metric-label text-info mb-2">
                                        <i class="bi bi-clock me-1"></i>
                                        Ping
                                    </div>
                                    <div class="metric-value text-info"><?php echo $ping; ?></div>
                                    <small class="text-muted">ms</small>
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="text-center p-3 border rounded">
                                    <div class="metric-label text-warning mb-2">
                                        <i class="bi bi-activity me-1"></i>
                                        Jitter
                                    </div>
                                    <div class="metric-value text-warning"><?php echo $jitter; ?></div>
                                    <small class="text-muted">ms</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Share Buttons -->
                        <div class="d-grid gap-2 mb-3">
                            <a href="/" class="btn btn-primary btn-lg">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Run Your Own Test
                            </a>
                        </div>
                        
                        <div class="d-flex gap-2 justify-content-center">
                            <button class="btn btn-sm btn-outline-secondary" onclick="copyLink()">
                                <i class="bi bi-link-45deg"></i>
                            </button>
                            <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($pageDescription); ?>&url=<?php echo urlencode($shareUrl); ?>" 
                               target="_blank" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-twitter-x"></i>
                            </a>
                            <a href="https://wa.me/?text=<?php echo urlencode($pageDescription . ' ' . $shareUrl); ?>" 
                               target="_blank" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($shareUrl); ?>" 
                               target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-facebook"></i>
                            </a>
                        </div>
                        
                        <div class="text-center mt-4">
                            <small class="text-muted">
                                Test ID: <?php echo htmlspecialchars($test_id); ?>
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-white opacity-75">
                        Made for Pakistan 🇵🇰
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyLink() {
            navigator.clipboard.writeText(window.location.href);
            alert('Link copied to clipboard!');
        }
    </script>
</body>
</html>
