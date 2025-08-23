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
$videoPath = "$downloadDir/$videoId.mp4";
$videoURL = "/downloads/$videoId.mp4";

$video = $videoDetails['items'][0];
$pageTitle = sanitizeOutput($video['snippet']['title']) . " - " . SITE_NAME;
$pageDescription = sanitizeOutput(substr($video['snippet']['description'], 0, 160));

require_once 'includes/header.php';

if (!file_exists($videoPath)) {
    $downloadURL = YT_DLP_API . "/downloadfallback?url=https://www.youtube.com/watch?v=$videoId";

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

    $headers = [
        "skip_zrok_interstitial: true",
        "ngrok-skip-browser-warning: true"
    ];

    $opts = [
        "http" => [
            "method" => "GET",
            "header" => implode("\r\n", $headers)
        ]
    ];

    $context = stream_context_create($opts);

    $read = @fopen($downloadURL, 'rb', false, $context);
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
        echo "<script>console.log('fopen failed');</script>";
        echo "<script>updateProgress(0);</script>";
        ob_flush(); flush();

        $ch = curl_init($downloadURL);
        $fp = fopen($videoPath, 'wb');

        if (!is_writable(dirname($videoPath))) {
            echo "<div class='text-danger'>Can't download videos right now, sorry.</div>";
            exit;
        }

        if (!$fp) {
            echo "<div class='text-danger'>An error occured, I'm sorry, but you'll have to wait 'till we update our API.</div>";
            curl_close($ch);
            exit;
        }

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 1024 * 8);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["skip_zrok_interstitial: true"]);
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
        <!-- Main video column -->
        <div class="col-lg-8 mb-5">
            <!-- animated video container -->
            <div id="videoContainer" 
                 class="ratio ratio-16x9 mb-4 neon-border animate__animated animate__fadeInUp">
                <iframe src="/video_player.php?id=<?= $videoId ?>" 
                        frameborder="0" 
                        allowfullscreen
                        class="rounded-3"
                        style="width: 100%; height: 100%;"></iframe>
            </div>

            <!-- video title -->
            <h1 class="neon-text mb-3 animate__animated animate__fadeInLeft">
                <?= sanitizeOutput($video['snippet']['title']) ?>
            </h1>

            <!-- video description -->
            <p class="text-muted animate__animated animate__fadeInUp animate__delay-1s">
                <?= nl2br(sanitizeOutput($video['snippet']['description'])) ?>
            </p>
        </div>

        <!-- Related videos sidebar -->
        <div class="col-lg-4">
            <h4 class="neon-text mb-3 animate__animated animate__fadeInRight">Related Videos</h4>
            <?php if ($relatedVideos && !empty($relatedVideos['items'])): ?>
                <?php 
                $delay = 0;
                foreach ($relatedVideos['items'] as $related): 
                    $delay += 100; // stagger animations
                ?>
                    <div class="video-item p-2 rounded-3 mb-3 shadow-lg hover-glow" 
                         data-aos="fade-left" 
                         data-aos-delay="<?= $delay ?>">
                        <a href="/video.php?id=<?= $related['id']['videoId'] ?>" 
                           class="d-flex text-white text-decoration-none">
                            <img src="<?= $related['snippet']['thumbnails']['medium']['url'] ?>"
                                 class="img-fluid rounded-3 me-3 neon-border"
                                 style="width: 120px; height: 90px; object-fit: cover;">
                            <div>
                                <h6 class="mb-1"><?= sanitizeOutput($related['snippet']['title']) ?></h6>
                                <small class="text-muted">
                                    <?= sanitizeOutput($related['snippet']['channelTitle']) ?>
                                </small>
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
