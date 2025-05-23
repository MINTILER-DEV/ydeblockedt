<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$videoId = $_GET['id'] ?? '';
if (empty($videoId)) {
    header("Location: /");
    exit;
}

$videoDetails = getVideoDetails($videoId);
if (empty($videoDetails['items'])) {
    header("Location: /");
    exit;
}

$downloadDir = __DIR__ . '/downloads';
$videoPath = "$downloadDir/$videoId.webm";
$videoURL = "/downloads/$videoId.webm";

$video = $videoDetails['items'][0];
$pageTitle = sanitizeOutput($video['snippet']['title']) . " - " . SITE_NAME;
$pageDescription = sanitizeOutput(substr($video['snippet']['description'], 0, 160));

require_once 'includes/header.php';

if (!file_exists($videoPath)) {
    $downloadURL = YT_DLP_API . "/download?url=https://www.youtube.com/watch?v=$videoId";

    header("Content-Type: text/html");
    echo '<div class="container text-center py-5">
            <h3 class="neon-text">Downloading your video... 🔥</h3>
            <div class="progress mt-4" style="height: 30px;">
                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-info" 
                     role="progressbar" style="width: 0%">0%</div>
            </div>
          </div>';
    echo '<script>
            function updateProgress(percent) {
                const bar = document.getElementById("progressBar");
                bar.style.width = percent + "%";
                bar.textContent = percent + "%";
            }
          </script>';
    ob_flush(); flush();

    // Try to get total content length
    $headers = @get_headers($downloadURL, 1);
    $totalSize = 0;
    if (isset($headers['Content-Length'])) {
        $totalSize = (int)$headers['Content-Length'];
    }

    $read = @fopen($downloadURL, 'rb');
    $write = @fopen($videoPath, 'wb');

    if ($read && $write) {
        $downloaded = 0;
        while (!feof($read)) {
            $chunk = fread($read, 1024 * 8);
            fwrite($write, $chunk);
            $downloaded += strlen($chunk);

            if ($totalSize > 0) {
                $percent = round(($downloaded / $totalSize) * 100);
                echo "<script>updateProgress($percent);</script>";
                ob_flush(); flush();
            }
        }
        fclose($read);
        fclose($write);
    } else {
        // fallback to cURL if fopen fails
        echo "<script>updateProgress(0);</script>";
        ob_flush(); flush();

        $ch = curl_init($downloadURL);
        $fp = fopen($videoPath, 'wb');

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 1024 * 8);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function (
            $resource, $dl_size, $dl, $ul_size, $ul
        ) {
            if ($dl_size > 0) {
                $percent = round(($dl / $dl_size) * 100);
                echo "<script>updateProgress($percent);</script>";
                ob_flush(); flush();
            }
        });

        curl_exec($ch);

        if (curl_errno($ch)) {
            echo "<div class='text-danger'>cURL failed: " . curl_error($ch) . " 💀</div>";
        }

        curl_close($ch);
        fclose($fp);
    }

    echo "<script>setTimeout(() => window.location.reload(), 1000);</script>";
    exit;
}


$relatedVideos = getRelatedVideos($videoId);
?>

<!-- VIDEO PLAYER -->
<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <div id="videoContainer" style="display: block;" class="ratio ratio-16x9 mb-4 neon-border">
                <iframe src="/video_player.php?id=<?= $videoId ?>" 
        frameborder="0" 
        allowfullscreen
        class="ratio ratio-16x9 mb-4 neon-border"
        style="width: 100%; height: 100%;"></iframe>
            </div>
            <h1 class="neon-text"><?= sanitizeOutput($video['snippet']['title']) ?></h1>
            <!-- rest of your content... -->
        </div>
        <div class="col-lg-4">
            <h4 class="neon-text mb-3">Related Videos</h4>
            <?php if ($relatedVideos && !empty($relatedVideos['items'])): ?>
                <?php foreach ($relatedVideos['items'] as $related): ?>
                    <div class="mb-3">
                        <a href="/video.php?id=<?= $related['id']['videoId'] ?>" class="d-flex text-white text-decoration-none">
                            <img src="<?= $related['snippet']['thumbnails']['medium']['url'] ?>"
                                 class="img-fluid rounded-3 me-3"
                                 style="width: 120px; height: 90px">
                            <div>
                                <h6><?= sanitizeOutput($related['snippet']['title']) ?></h6>
                                <small class="text-muted"><?= sanitizeOutput($related['snippet']['channelTitle']) ?></small>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No related videos found</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
