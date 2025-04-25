<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$videoId = $_GET['id'] ?? '';
if (empty($videoId)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// Cache directory setup
$cacheDir = __DIR__ . '/video_cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);
$cacheFile = "$cacheDir/$videoId.webm";

// YouTube stream URL (via your yt-dlp API)
$streamURL = YT_DLP_API . "/stream?url=https://www.youtube.com/watch?v=$videoId";

// Handle byte-range requests (for seeking)
header("Accept-Ranges: bytes");
header("Content-Type: video/webm");

if (isset($_SERVER['HTTP_RANGE'])) {
    // --- SEEKING REQUEST (serve from cache) ---
    if (!file_exists($cacheFile)) {
        header("HTTP/1.1 416 Range Not Satisfiable");
        exit;
    }

    $size = filesize($cacheFile);
    $range = $_SERVER['HTTP_RANGE'];
    $range = str_replace('bytes=', '', $range);
    list($start, $end) = explode('-', $range, 2);

    $end = (empty($end)) ? ($size - 1) : min(abs(intval($end)), ($size - 1));
    $start = (empty($start) || $start > $end) ? 0 : max(abs(intval($start)), 0);

    header("HTTP/1.1 206 Partial Content");
    header("Content-Length: " . ($end - $start + 1));
    header("Content-Range: bytes $start-$end/$size");

    $fp = fopen($cacheFile, 'rb');
    fseek($fp, $start);
    $chunkSize = 8192; // 8KB chunks

    while (!feof($fp) && ($p = ftell($fp)) <= $end) {
        if ($p + $chunkSize > $end) {
            $chunkSize = $end - $p + 1;
        }
        echo fread($fp, $chunkSize);
        flush();
    }
    fclose($fp);
} else {
    // --- INITIAL STREAM REQUEST ---
    if (file_exists($cacheFile)) {
        // Serve fully cached file if available
        header("Content-Length: " . filesize($cacheFile));
        readfile($cacheFile);
        exit;
    }

    // Stream while caching
    $read = fopen($streamURL, 'rb');
    $write = fopen($cacheFile, 'wb');

    if ($read && $write) {
        header("Content-Type: video/webm");
        while (!feof($read)) {
            $chunk = fread($read, 8192);
            echo $chunk; // Stream to browser
            fwrite($write, $chunk); // Save to cache
            flush();
        }
        fclose($read);
        fclose($write);
    } else {
        header("HTTP/1.0 500 Internal Server Error");
        exit("Stream initialization failed");
    }
}
?>