<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$query = $_GET['q'] ?? '';
$maxResults = min(50, max(1, intval($_GET['max'] ?? 20)));

$pageTitle = "Search for '{$query}' - " . SITE_NAME;
$pageDescription = "Search results for '{$query}' on " . SITE_NAME;

require_once 'includes/header.php';

if (empty($query)) {
    header("Location: /");
    exit;
}

$results = searchVideos($query, $maxResults);
?>

<div class="container">
    <h1 class="neon-text mb-4">Search Results for "<?= sanitizeOutput($query) ?>"</h1>
    
    <div class="row">
        <div class="col-md-8">
            <div id="results-container" class="d-grid gap-3">
                <?php foreach ($results['items'] as $item): ?>
                    <?php if ($item['id']['kind'] === 'youtube#video'): ?>
                        <div class="video-item p-3 rounded-3">
                            <div class="d-flex">
                                <a href="/video.php?id=<?= $item['id']['videoId'] ?>" class="flex-shrink-0">
                                    <img src="<?= $item['snippet']['thumbnails']['medium']['url'] ?>" 
                                         class="img-fluid rounded-3 me-3" 
                                         style="width: 240px; height: 180px">
                                </a>
                                <div>
                                    <h4>
                                        <a href="/video.php?id=<?= $item['id']['videoId'] ?>" class="text-white text-decoration-none">
                                            <?= sanitizeOutput($item['snippet']['title']) ?>
                                        </a>
                                    </h4>
                                    <p><?= sanitizeOutput($item['snippet']['description']) ?></p>
                                    <small class="text-muted">Channel: <?= sanitizeOutput($item['snippet']['channelTitle']) ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>