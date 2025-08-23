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
$videoURL  = "/downloads/$videoId.webm";

$video = $videoDetails['items'][0];
$pageTitle = sanitizeOutput($video['snippet']['title']) . " - " . SITE_NAME;
$pageDescription = sanitizeOutput(substr($video['snippet']['description'], 0, 160));

require_once 'includes/header.php';

// If not downloaded yet, trigger new download via API
if (!file_exists($videoPath)) {
    $downloadURL = YT_DLP_API . "/download?url=https://www.youtube.com/watch?v=$videoId";

    // request background download from API
    $ch = curl_init($downloadURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resp = curl_exec($ch);
    curl_close($ch);

    $respData = json_decode($resp, true);
    $apiVideoId = $respData['video_id'] ?? null;

    if (!$apiVideoId) {
        echo "<div class='container text-center py-5 text-danger'>Failed to start download 💀</div>";
        require_once 'includes/footer.php';
        exit;
    }

    // show progress UI
    echo '<div class="container text-center py-5">
            <h3 class="neon-text">Downloading your video... 🔥</h3>
            <div id="progressStatus" class="mt-2 text-muted">Starting...</div>
            <div class="progress mt-4" style="height: 30px;">
                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-info" 
                    role="progressbar" style="width: 0%">0%</div>
            </div>
        </div>';

    echo '<script>
            function updateProgress(percent, status) {
                const bar = document.getElementById("progressBar");
                const statusEl = document.getElementById("progressStatus");
                bar.style.width = percent + "%";
                bar.textContent = percent + "%";
                statusEl.textContent = status;
            }

            const es = new EventSource("/rizzystream.php?id=' . $apiVideoId . '&vid=' . $videoId . '");
            es.onmessage = function(e) {
                const data = JSON.parse(e.data);
                if (data.percent >= 0) {
                    updateProgress(data.percent, data.status);
                }
                if (data.percent >= 100) {
                    es.close();
                    setTimeout(() => window.location.reload(), 1000);
                }
            };
          </script>';
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
                <iframe src="/video_player.php?id=<?= $videoId ?>&ext=webm" 
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
                        <a href="/video_v2.php?id=<?= $related['id']['videoId'] ?>" 
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
