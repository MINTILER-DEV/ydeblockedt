<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$pageTitle = "Home - " . SITE_NAME;
$pageDescription = "Search and watch YouTube videos with a modern neon interface";

require_once 'includes/header.php';

$trendingVideos = getTrendingVideos();
?>

<div class="container text-center my-5">
    <h1 class="neon-text display-3 mb-4">Welcome to YDeblockedT</h1>
    <p class="lead mb-5">Search for your favorite YouTube videos with our modern neon interface</p>
    
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <form action="/search.php" method="get" class="mb-5">
                <div class="input-group">
                    <input type="text" name="q" class="form-control form-control-lg glow" 
                           placeholder="What are you looking for?" required>
                    <button type="submit" class="btn neon-btn-lg">
                        <i class="fas fa-search me-2"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="mt-5">
        <h3 class="neon-text mb-4">Trending Videos</h3>
        <div class="row">
            <?php if ($trendingVideos && !empty($trendingVideos['items'])): ?>
                <?php foreach ($trendingVideos['items'] as $video): ?>
                    <div class="col-md-4 mb-4">
                        <a href="/video_v2.php?id=<?= $video['id'] ?>" class="video-card">
                            <img src="<?= $video['snippet']['thumbnails']['medium']['url'] ?>" 
                                 class="img-fluid rounded-3 mb-2">
                            <h5><?= sanitizeOutput($video['snippet']['title']) ?></h5>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <p>Unable to load trending videos at this time</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>