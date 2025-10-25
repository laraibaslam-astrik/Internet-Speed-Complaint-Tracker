<?php
/**
 * GeoIP lookup with caching
 */

require_once __DIR__ . '/util.php';

function geoip_lookup($ip) {
    // Try APCu cache first
    $cache_key = "geoip_" . md5($ip);
    
    if (function_exists('apcu_fetch')) {
        $cached = apcu_fetch($cache_key);
        if ($cached !== false) {
            return $cached;
        }
    }
    
    $token = env('IPINFO_TOKEN', '');
    $result = [
        'asn' => null,
        'isp_name' => 'Unknown',
        'city' => 'Unknown'
    ];
    
    // Try free IPInfo API first (no token needed, 50k requests/month)
    try {
        $url = "https://ipinfo.io/{$ip}/json";
        $context = stream_context_create([
            'http' => [
                'timeout' => 2,
                'ignore_errors' => true,
                'method' => 'GET',
                'header' => "Accept: application/json\r\n"
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response) {
            $data = json_decode($response, true);
            if ($data && !isset($data['error'])) {
                $result['city'] = $data['city'] ?? 'Pakistan';
                $result['isp_name'] = $data['org'] ?? 'ISP Pakistan';
                
                // Extract ASN from org field (format: "AS12345 ISP Name")
                if (isset($data['org']) && preg_match('/AS(\d+)/', $data['org'], $matches)) {
                    $result['asn'] = (int)$matches[1];
                    $result['isp_name'] = trim(preg_replace('/AS\d+\s*/', '', $data['org']));
                }
                
                // If still Unknown, try to extract from hostname
                if ($result['isp_name'] === 'Unknown' && isset($data['hostname'])) {
                    $result['isp_name'] = extract_isp_from_hostname($data['hostname']);
                }
            }
        }
    } catch (Exception $e) {
        error_log("IPInfo free API failed: " . $e->getMessage());
    }
    
    // If free API failed and token is available, try with token
    if ($result['isp_name'] === 'Unknown' && !empty($token)) {
        try {
            $url = "https://ipinfo.io/{$ip}?token={$token}";
            $response = @file_get_contents($url, false, stream_context_create([
                'http' => [
                    'timeout' => 3,
                    'ignore_errors' => true
                ]
            ]));
            
            if ($response) {
                $data = json_decode($response, true);
                if ($data && !isset($data['error'])) {
                    $result['city'] = $data['city'] ?? 'Pakistan';
                    $result['isp_name'] = $data['org'] ?? 'ISP Pakistan';
                    
                    if (preg_match('/AS(\d+)/', $result['isp_name'], $matches)) {
                        $result['asn'] = (int)$matches[1];
                        $result['isp_name'] = trim(preg_replace('/AS\d+\s*/', '', $result['isp_name']));
                    }
                }
            }
        } catch (Exception $e) {
            error_log("IPInfo token API failed: " . $e->getMessage());
        }
    }
    
    // Final fallback: Try ip-api.com (free, no token needed)
    if ($result['isp_name'] === 'Unknown') {
        try {
            $url = "http://ip-api.com/json/{$ip}?fields=status,country,city,isp,as,org";
            $response = @file_get_contents($url, false, stream_context_create([
                'http' => [
                    'timeout' => 2,
                    'ignore_errors' => true
                ]
            ]));
            
            if ($response) {
                $data = json_decode($response, true);
                if ($data && $data['status'] === 'success') {
                    $result['city'] = $data['city'] ?? 'Pakistan';
                    $result['isp_name'] = $data['isp'] ?? ($data['org'] ?? 'ISP Pakistan');
                    
                    // Extract ASN from 'as' field
                    if (isset($data['as']) && preg_match('/AS(\d+)/', $data['as'], $matches)) {
                        $result['asn'] = (int)$matches[1];
                    }
                }
            }
        } catch (Exception $e) {
            error_log("ip-api.com failed: " . $e->getMessage());
        }
    }
    
    // Clean up ISP name
    $result['isp_name'] = clean_isp_name($result['isp_name']);
    
    // Cache for 5 minutes
    if (function_exists('apcu_store')) {
        apcu_store($cache_key, $result, 300);
    }
    
    return $result;
}

function extract_isp_from_hostname($hostname) {
    // Common Pakistan ISP patterns in hostnames
    $patterns = [
        '/ptcl/i' => 'PTCL',
        '/nayatel/i' => 'Nayatel',
        '/stormfiber|storm/i' => 'StormFiber',
        '/connect/i' => 'Connect',
        '/fiberlink/i' => 'Fiberlink',
        '/wateen/i' => 'Wateen',
        '/cybernet/i' => 'Cybernet',
        '/worldcall/i' => 'WorldCall',
        '/wi-tribe|witribe/i' => 'Wi-tribe',
        '/zong/i' => 'Zong',
        '/jazz/i' => 'Jazz',
        '/telenor/i' => 'Telenor',
        '/ufone/i' => 'Ufone'
    ];
    
    foreach ($patterns as $pattern => $isp) {
        if (preg_match($pattern, $hostname)) {
            return $isp;
        }
    }
    
    return 'Unknown';
}

function clean_isp_name($name) {
    // Remove common prefixes/suffixes
    $name = preg_replace('/^AS\d+\s+/', '', $name);
    $name = preg_replace('/\s+(Pakistan|Pvt\.?|Ltd\.?|Limited|Inc\.?|Corporation)$/i', '', $name);
    $name = trim($name);
    
    return $name ?: 'ISP Pakistan';
}

function guess_pakistan_isp($ip) {
    // Basic fallback for common Pakistan ISP patterns
    // This is a very basic implementation
    $octets = explode('.', $ip);
    $first = (int)($octets[0] ?? 0);
    
    $result = [
        'asn' => null,
        'isp_name' => 'Unknown ISP',
        'city' => 'Pakistan'
    ];
    
    // Common Pakistan ISP ASN ranges (examples)
    $pak_isps = [
        'PTCL' => [23674],
        'Nayatel' => [133492],
        'StormFiber' => [136525],
        'Fiberlink' => [45595],
    ];
    
    return $result;
}
