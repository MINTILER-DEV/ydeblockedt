<?php
require_once 'config.php';

function getCacheFilename($prefix, $id) {
    return CACHE_DIR . '/' . $prefix . '_' . md5($id) . '.json';
}

function isCacheValid($filename) {
    if (!file_exists($filename)) return false;
    return (time() - filemtime($filename)) < CACHE_EXPIRE;
}

function fetchFromYouTube($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        error_log('YouTube API Error: ' . curl_error($ch));
        return false;
    }
    
    curl_close($ch);
    return $response;
}

function searchVideos($query, $maxResults = 20) {
    $cacheFile = getCacheFilename('search', $query . $maxResults);
    
    if (isCacheValid($cacheFile)) {
        return json_decode(file_get_contents($cacheFile), true);
    }
    
    $url = 'https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&q=' . 
          urlencode($query) . '&maxResults=' . $maxResults . '&key=' . API_KEY;
    
    $response = fetchFromYouTube($url);
    if ($response) {
        file_put_contents($cacheFile, $response);
        return json_decode($response, true);
    }
    return false;
}

function getVideoDetails($videoId) {
    $cacheFile = getCacheFilename('video', $videoId);
    
    if (isCacheValid($cacheFile)) {
        return json_decode(file_get_contents($cacheFile), true);
    }
    
    $url = 'https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails,statistics&id=' . 
          $videoId . '&key=' . API_KEY;
    
    $response = fetchFromYouTube($url);
    if ($response) {
        file_put_contents($cacheFile, $response);
        return json_decode($response, true);
    }
    return false;
}

function getRelatedVideos($videoId, $maxResults = 5) {
    $cacheFile = getCacheFilename('related', $videoId . $maxResults);
    
    if (isCacheValid($cacheFile)) {
        return json_decode(file_get_contents($cacheFile), true);
    }
    
    // Get video details to extract tags
    $videoDetails = getVideoDetails($videoId);
    if (!$videoDetails || !isset($videoDetails['items'][0]['snippet']['tags'])) {
        return false;
    }
    
    $tags = $videoDetails['items'][0]['snippet']['tags'];
    
    // Use the most relevant tags for search (limit to 2-3 to avoid long URLs)
    $searchQuery = implode('|', array_slice($tags, 0, 3));
    
    $url = 'https://www.googleapis.com/youtube/v3/search?part=snippet' . 
          '&type=video' .
          '&maxResults=' . (int)$maxResults . 
          '&q=' . urlencode($searchQuery) .
          '&key=' . urlencode(API_KEY);
    
    $response = fetchFromYouTube($url);
    if ($response) {
        file_put_contents($cacheFile, $response);
        return json_decode($response, true);
    }
    return false;
}

function sanitizeOutput($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function getTrendingVideos($maxResults = 6) {
    $cacheFile = getCacheFilename('trending', $maxResults);
    
    if (isCacheValid($cacheFile)) {
        return json_decode(file_get_contents($cacheFile), true);
    }
    
    $url = 'https://www.googleapis.com/youtube/v3/videos?part=snippet&chart=mostPopular&maxResults=' . 
          $maxResults . '&key=' . API_KEY;
    
    $response = fetchFromYouTube($url);
    if ($response) {
        file_put_contents($cacheFile, $response);
        return json_decode($response, true);
    }
    return false;
}
?>
