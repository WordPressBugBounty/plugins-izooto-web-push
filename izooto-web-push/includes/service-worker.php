<?php

// Get parameter safely
$hash = filter_input(INPUT_GET, 'sw', FILTER_SANITIZE_STRING);

// Validate strictly
if (
    empty($hash) ||
    !is_string($hash) ||
    !preg_match('/^[a-f0-9]{5,50}$/i', $hash)
) {
    http_response_code(400);
    exit;
}

// Build URL
$worker_url = 'https://cdn.izooto.com/scripts/workers/' . $hash . '.js';

// Headers
header('Content-Type: application/javascript');
header('Service-Worker-Allowed: /');
header('Cache-Control: public, max-age=2592000, immutable');

// Output
echo "var izCacheVer = 1; importScripts(" . json_encode($worker_url) . ");";

exit;