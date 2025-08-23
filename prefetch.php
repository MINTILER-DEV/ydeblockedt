<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$videoId = $_GET['id'] ?? '';
if (!$videoId) exit;

$downloadDir = __DIR__ . '/downloads';
if (!is_dir($downloadDir)) {
    mkdir($downloadDir, 0777, true);
}

$videoPath = "$downloadDir/$videoId.mp4";
if (file_exists($videoPath)) {
    echo "cached";
    exit;
}

$downloadURL = YT_DLP_API . "/downloadfallback?url=https://www.youtube.com/watch?v=$videoId";

$ch = curl_init($downloadURL);
$fp = fopen($videoPath, "wb");

curl_setopt_array($ch, [
    CURLOPT_FILE            => $fp,
    CURLOPT_FOLLOWLOCATION  => true,
    CURLOPT_FAILONERROR     => true,
    CURLOPT_TIMEOUT         => 0, // let it run until done
    CURLOPT_HTTPHEADER      => [
        "skip_zrok_interstitial: true",
        "ngrok-skip-browser-warning: true"
    ]
]);

$success = curl_exec($ch);

if ($success === false) {
    unlink($videoPath); // cleanup broken file
    echo "fail: " . curl_error($ch);
} else {
    echo "downloaded";
}

curl_close($ch);
fclose($fp);
