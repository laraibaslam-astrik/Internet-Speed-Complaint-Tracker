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
    
    if (empty($token)) {
        // Fallback: try to guess based on common Pakistan ISPs
        $result = guess_pakistan_isp($ip);
    } else {
        // Call IPInfo API
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
                if ($data) {
                    $result['city'] = $data['city'] ?? 'Unknown';
                    $result['isp_name'] = $data['org'] ?? 'Unknown';
                    
                    // Extract ASN from org field (format: "AS12345 ISP Name")
                    if (preg_match('/AS(\d+)/', $result['isp_name'], $matches)) {
                        $result['asn'] = (int)$matches[1];
                        $result['isp_name'] = trim(preg_replace('/AS\d+\s*/', '', $result['isp_name']));
                    }
                }
            }
        } catch (Exception $e) {
            error_log("GeoIP lookup failed: " . $e->getMessage());
        }
    }
    
    // Cache for 5 minutes
    if (function_exists('apcu_store')) {
        apcu_store($cache_key, $result, 300);
    }
    
    return $result;
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
