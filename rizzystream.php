<?php
require_once 'includes/config.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', false);
while (ob_get_level() > 0) ob_end_flush();
ob_implicit_flush(true);

$apiId = $_GET['id'] ?? null;
$videoId = $_GET['vid'] ?? $apiId;
if (!$apiId) {
    echo "data: {\"percent\":-1,\"status\":\"Missing ID\"}\n\n";
    flush();
    exit;
}

$apiProgressUrl = YT_DLP_API . "/progress?id=" . urlencode($apiId);
$savePath = __DIR__ . "/downloads/$videoId.webm";

// -------- PHASE 1: API Download 0–50 --------
while (true) {
    $apiPercent = @file_get_contents($apiProgressUrl);
    $apiPercent = intval(substr($apiPercent, 6));

    if ($apiPercent < 50) {
        echo "data: {\"percent\":$apiPercent,\"status\":\"Downloading video, this may take a while\"}\n\n";
        flush();
        sleep(1);
    } else {
        break;
    }
}

// -------- PHASE 2: Transfer to server 51–100 --------
$downloadUrl = YT_DLP_API . "/serve?id=" . urlencode($apiId);
$ch = curl_init($downloadUrl);
$fp = fopen($savePath, 'w');

curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_NOPROGRESS, false);
curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($res, $dltotal, $dlnow) {
    if ($dltotal > 0) {
        $percent = intval(($dlnow / $dltotal) * 50) + 50; // 51–100
        echo "data: {\"percent\":$percent,\"status\":\"Transferring video\"}\n\n";
        flush();
    }
});

curl_exec($ch);
curl_close($ch);
fclose($fp);

// final 100%
echo "data: {\"percent\":100,\"status\":\"Done\"}\n\n";
flush();