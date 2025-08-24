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
    <h1 class="neon-text mb-4 animate__animated animate__fadeInDown">
        Search Results for "<?= sanitizeOutput($query) ?>"
    </h1>
    
    <div class="row">
        <div class="col-md-8">
            <div id="results-container" class="d-grid gap-3">
                <?php 
                $i = 0;
                foreach ($results['items'] as $item): 
                    if ($item['id']['kind'] === 'youtube#video'): 
                        $i++;
                ?>
                        <div class="video-item p-3 rounded-3 shadow-lg position-relative" 
                            data-aos="fade-up" 
                            data-aos-delay="<?= $i * 100 ?>"
                            data-video-id="<?= $item['id']['videoId'] ?>">

                            <div class="d-flex video-content opacity-50">
                                <a href="/video_v2.php?id=<?= $item['id']['videoId'] ?>" class="flex-shrink-0 disabled-link">
                                    <img src="<?= $item['snippet']['thumbnails']['medium']['url'] ?>" 
                                        class="img-fluid rounded-3 me-3 neon-border" 
                                        style="width: 240px; height: 180px; object-fit: cover;">
                                </a>
                                <div>
                                    <h4 class="mb-2">
                                        <a href="/video.php?id=<?= $item['id']['videoId'] ?>" 
                                        class="text-white text-decoration-none hover-glow disabled-link">
                                            <?= sanitizeOutput($item['snippet']['title']) ?>
                                        </a>
                                    </h4>
                                    <p class="text-muted small mb-2">
                                        <?= sanitizeOutput($item['snippet']['description']) ?>
                                    </p>
                                    <small class="text-accent">Channel: <?= sanitizeOutput($item['snippet']['channelTitle']) ?></small>
                                </div>
                            </div>
                        </div>
                <?php 
                    endif; 
                endforeach; 
                ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const videoIds = <?= json_encode(array_map(
    fn($item) => $item['id']['videoId'], 
    array_filter($results['items'], fn($i) => $i['id']['kind'] === 'youtube#video')
  )) ?>;

  videoIds.forEach((id, idx) => {
    setTimeout(() => {
      fetch(`/prefetch.php?id=${id}`)
        .then(r => r.text())
        .then(txt => console.log("Prefetch", id, txt));
    }, idx * 2000); // stagger to avoid hammering server
  });
});
</script>


<?php require_once 'includes/footer.php'; ?>
