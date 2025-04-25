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

$video = $videoDetails['items'][0];
$pageTitle = sanitizeOutput($video['snippet']['title']) . " - " . SITE_NAME;
$pageDescription = sanitizeOutput(substr($video['snippet']['description'], 0, 160));

require_once 'includes/header.php';

$relatedVideos = getRelatedVideos($videoId);
?>

<!-- VIDEO PLAYER -->
<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <div id="videoContainer" style="display: block;" class="ratio ratio-16x9 mb-4 neon-border">
                <iframe src="/video_player_stream.php?id=<?= $videoId ?>" 
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
                        <a href="/video_v2.php?id=<?= $related['id']['videoId'] ?>" class="d-flex text-white text-decoration-none">
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