<?php
/**
 * ISP Detection Test Page
 * Visit: https://your-domain.com/test_isp.php
 * DELETE THIS FILE IN PRODUCTION!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/lib/util.php';
require_once __DIR__ . '/lib/geoip.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISP Detection Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 2rem; background: #f8f9fa; }
        .test-card { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .badge-custom { padding: 0.5rem 1rem; border-radius: 50px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="test-card mb-4">
                    <h2 class="mb-4">🔍 ISP Detection Test</h2>
                    
                    <?php
                    $ip = client_ip();
                    echo "<div class='alert alert-info'>";
                    echo "<strong>Your IP:</strong> {$ip}";
                    echo "</div>";
                    
                    echo "<h4 class='mt-4 mb-3'>Testing ISP Detection...</h4>";
                    
                    $start = microtime(true);
                    $result = geoip_lookup($ip);
                    $duration = round((microtime(true) - $start) * 1000, 2);
                    ?>
                    
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Detection Result</h5>
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td width="30%" class="fw-bold">ISP Name:</td>
                                    <td>
                                        <span class="badge bg-<?php echo ($result['isp_name'] !== 'Unknown' && $result['isp_name'] !== 'ISP Pakistan') ? 'success' : 'warning'; ?> badge-custom">
                                            <?php echo htmlspecialchars($result['isp_name']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">City:</td>
                                    <td>
                                        <span class="badge bg-<?php echo ($result['city'] !== 'Unknown' && $result['city'] !== 'Pakistan') ? 'success' : 'warning'; ?> badge-custom">
                                            <?php echo htmlspecialchars($result['city']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">ASN:</td>
                                    <td>
                                        <span class="badge bg-<?php echo $result['asn'] ? 'success' : 'secondary'; ?> badge-custom">
                                            <?php echo $result['asn'] ?: 'Not detected'; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Detection Time:</td>
                                    <td>
                                        <span class="badge bg-info badge-custom"><?php echo $duration; ?> ms</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <?php if ($result['isp_name'] === 'Unknown' || $result['isp_name'] === 'ISP Pakistan'): ?>
                    <div class="alert alert-warning">
                        <strong>⚠️ ISP Not Detected Properly</strong>
                        <p class="mb-0">This could happen if:</p>
                        <ul class="mb-0 mt-2">
                            <li><code>allow_url_fopen</code> is disabled in PHP</li>
                            <li>Server firewall is blocking external API calls</li>
                            <li>You're testing from localhost (127.0.0.1)</li>
                            <li>API rate limits exceeded</li>
                        </ul>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-success">
                        <strong>✅ ISP Detection Working!</strong>
                        <p class="mb-0">The system successfully detected your ISP information.</p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="test-card mb-4">
                    <h4 class="mb-3">🔧 Diagnostics</h4>
                    
                    <?php
                    // Test allow_url_fopen
                    $url_fopen = ini_get('allow_url_fopen');
                    echo "<div class='mb-2'>";
                    echo "<strong>allow_url_fopen:</strong> ";
                    echo $url_fopen ? "<span class='badge bg-success'>Enabled</span>" : "<span class='badge bg-danger'>Disabled</span>";
                    echo "</div>";
                    
                    // Test file_get_contents on external URL
                    echo "<div class='mb-2'>";
                    echo "<strong>External API Access:</strong> ";
                    $test_url = @file_get_contents('https://ipinfo.io/json', false, stream_context_create(['http' => ['timeout' => 2]]));
                    echo $test_url ? "<span class='badge bg-success'>Working</span>" : "<span class='badge bg-danger'>Blocked</span>";
                    echo "</div>";
                    
                    // Check APCu
                    echo "<div class='mb-2'>";
                    echo "<strong>APCu Cache:</strong> ";
                    echo function_exists('apcu_fetch') ? "<span class='badge bg-success'>Available</span>" : "<span class='badge bg-warning'>Not Installed (Optional)</span>";
                    echo "</div>";
                    
                    // Check PHP version
                    echo "<div class='mb-2'>";
                    echo "<strong>PHP Version:</strong> ";
                    echo "<span class='badge bg-info'>" . phpversion() . "</span>";
                    echo "</div>";
                    ?>
                </div>
                
                <div class="test-card">
                    <h4 class="mb-3">🧪 Manual API Tests</h4>
                    
                    <div class="mb-3">
                        <strong>Test IPInfo.io Free API:</strong><br>
                        <code>https://ipinfo.io/<?php echo $ip; ?>/json</code>
                        <?php
                        $ipinfo_test = @file_get_contents("https://ipinfo.io/{$ip}/json", false, stream_context_create(['http' => ['timeout' => 2]]));
                        echo "<div class='mt-2'>";
                        if ($ipinfo_test) {
                            $data = json_decode($ipinfo_test, true);
                            echo "<pre class='bg-light p-3 rounded'>" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                        } else {
                            echo "<div class='alert alert-danger'>Failed to connect</div>";
                        }
                        echo "</div>";
                        ?>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Test IP-API.com:</strong><br>
                        <code>http://ip-api.com/json/<?php echo $ip; ?></code>
                        <?php
                        $ipapi_test = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,city,isp,as,org", false, stream_context_create(['http' => ['timeout' => 2]]));
                        echo "<div class='mt-2'>";
                        if ($ipapi_test) {
                            $data = json_decode($ipapi_test, true);
                            echo "<pre class='bg-light p-3 rounded'>" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                        } else {
                            echo "<div class='alert alert-danger'>Failed to connect</div>";
                        }
                        echo "</div>";
                        ?>
                    </div>
                </div>
                
                <div class="alert alert-danger mt-4">
                    <strong>⚠️ Security Warning:</strong> DELETE THIS FILE IN PRODUCTION!<br>
                    <code>rm public/test_isp.php</code>
                </div>
                
                <div class="text-center mt-4">
                    <a href="/" class="btn btn-primary">← Back to Home</a>
                    <button onclick="location.reload()" class="btn btn-secondary">🔄 Refresh Test</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
