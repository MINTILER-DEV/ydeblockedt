<?php
// rizzystream.php
require_once 'includes/config.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

$apiId = $_GET['id'] ?? null;
if (!$apiId) {
    echo "data: -1\n\n";
    flush();
    exit;
}

$apiUrl = YT_DLP_API . "/progress?id=" . urlencode($apiId);

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) {
    echo $data;
    ob_flush();
    flush();
    return strlen($data);
});
curl_exec($ch);
curl_close($ch);
