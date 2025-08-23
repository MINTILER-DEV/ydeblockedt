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
if (!$apiId) {
    echo "data: {\"percent\":-1,\"status\":\"Missing ID\"}\n\n";
    flush();
    exit;
}

$apiUrl = YT_DLP_API . "/progress?id=" . urlencode($apiId);

// 1. Poll API download progress (0–50)
while (true) {
    $progress = file_get_contents($apiUrl); 
    $percent = intval($progress); // assuming API returns just a number

    if ($percent < 100) {
        // scale API’s 0–100 into 0–50
        $scaled = intval($percent / 2);
        echo "data: {\"percent\":$scaled,\"status\":\"Downloading video, this may take a while\"}\n\n";
        flush();
        sleep(1);
    } else {
        break; // API finished downloading
    }
}

// 2. Transfer to PHP server (51–100)
$videoId = $_GET['vid'] ?? $apiId; 
$savePath = __DIR__ . "/downloads/$videoId.webm";
$downloadUrl = YT_DLP_API . "/serve?id=" . urlencode($apiId);

$ch = curl_init($downloadUrl);
$fp = fopen($savePath, 'w');

curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_NOPROGRESS, false);
curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($res, $dltotal, $dlnow) use ($savePath) {
    if ($dltotal > 0) {
        $percent = intval(($dlnow / $dltotal) * 50) + 50;
        echo "data: {\"percent\":$percent,\"status\":\"Transferring video\"}\n\n";
        flush();
    }
});
curl_exec($ch);
curl_close($ch);
fclose($fp);

// Final 100%
echo "data: {\"percent\":100,\"status\":\"Done\"}\n\n";
flush();