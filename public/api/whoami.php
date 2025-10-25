<?php
/**
 * WHO AM I endpoint
 * Returns client's ISP, ASN, and city information
 */

require_once __DIR__ . '/../lib/util.php';
require_once __DIR__ . '/../lib/geoip.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

$ip = client_ip();
$geoinfo = geoip_lookup($ip);

json_response([
    'asn' => $geoinfo['asn'],
    'isp_name' => $geoinfo['isp_name'],
    'city' => $geoinfo['city'],
    'ip' => $ip // Optional, for debugging
]);
