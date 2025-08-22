<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$videoId = $_GET['id'] ?? '';
if (empty($videoId)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

$videoPath = __DIR__ . '/downloads/' . $videoId . '.mp4';
if (!file_exists($videoPath)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// Get video details for title
$videoDetails = getVideoDetails($videoId);
$pageTitle = !empty($videoDetails['items']) ? sanitizeOutput($videoDetails['items'][0]['snippet']['title']) : 'Video Player';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
    <style>
        :root {
            --neon-color: #0fa;
            --bg-color: #111;
            --text-color: #fff;
        }
        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
        }
        .video-container {
            position: relative;
            width: 100%;
            height: 100vh;
        }
        video {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .neon-text {
            color: var(--neon-color);
            text-shadow: 0 0 5px var(--neon-color), 0 0 10px var(--neon-color);
        }
        .controls {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            padding: 10px;
            display: flex;
            flex-direction: column;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .video-container:hover .controls {
            opacity: 1;
        }
        .progress-container {
            width: 100%;
            height: 5px;
            background: rgba(255,255,255,0.2);
            margin-bottom: 10px;
            cursor: pointer;
        }
        .progress-bar {
            height: 100%;
            background: var(--neon-color);
            width: 0%;
        }
        .button-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .left-controls, .right-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        button {
            background: transparent;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
        }
        button:hover {
            color: var(--neon-color);
            text-shadow: 0 0 5px var(--neon-color);
        }
        .volume-control {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        input[type="range"] {
            -webkit-appearance: none;
            height: 5px;
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
        }
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: var(--neon-color);
            cursor: pointer;
        }
        .title-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            padding: 10px;
            background: linear-gradient(rgba(0,0,0,0.7), transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .video-container:hover .title-bar {
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="video-container">
        <div class="title-bar neon-text">
            <h3><?= $pageTitle ?></h3>
        </div>
        
        <video id="videoPlayer" autoplay>
            <source src="/downloads/<?= $videoId ?>.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        
        <div class="controls">
            <div class="progress-container" id="progressContainer">
                <div class="progress-bar" id="progressBar"></div>
            </div>
            <div class="button-row">
                <div class="left-controls">
                    <button id="playPauseBtn">⏯</button>
                    <button id="muteBtn">🔊</button>
                    <div class="volume-control">
                        <input type="range" id="volumeSlider" min="0" max="1" step="0.1" value="1">
                    </div>
                    <span id="timeDisplay">0:00 / 0:00</span>
                </div>
                <div class="right-controls">
                    <button id="fullscreenBtn">⛶</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const video = document.getElementById('videoPlayer');
        const playPauseBtn = document.getElementById('playPauseBtn');
        const muteBtn = document.getElementById('muteBtn');
        const volumeSlider = document.getElementById('volumeSlider');
        const progressBar = document.getElementById('progressBar');
        const progressContainer = document.getElementById('progressContainer');
        const timeDisplay = document.getElementById('timeDisplay');
        const fullscreenBtn = document.getElementById('fullscreenBtn');
        
        // Play/Pause toggle
        playPauseBtn.addEventListener('click', () => {
            if (video.paused) {
                video.play();
            } else {
                video.pause();
            }
        });
        
        // Mute toggle
        muteBtn.addEventListener('click', () => {
            video.muted = !video.muted;
            muteBtn.textContent = video.muted ? '🔇' : '🔊';
        });
        
        // Volume control
        volumeSlider.addEventListener('input', () => {
            video.volume = volumeSlider.value;
            video.muted = false;
            muteBtn.textContent = '🔊';
        });
        
        // Update progress bar
        video.addEventListener('timeupdate', () => {
            const progress = (video.currentTime / video.duration) * 100;
            progressBar.style.width = `${progress}%`;
            
            // Update time display
            const currentMinutes = Math.floor(video.currentTime / 60);
            const currentSeconds = Math.floor(video.currentTime % 60).toString().padStart(2, '0');
            const durationMinutes = Math.floor(video.duration / 60);
            const durationSeconds = Math.floor(video.duration % 60).toString().padStart(2, '0');
            
            timeDisplay.textContent = `${currentMinutes}:${currentSeconds} / ${durationMinutes}:${durationSeconds}`;
        });
        
        // Click on progress bar to seek
        progressContainer.addEventListener('click', (e) => {
            const rect = progressContainer.getBoundingClientRect();
            const pos = (e.clientX - rect.left) / rect.width;
            video.currentTime = pos * video.duration;
        });
        
        // Fullscreen toggle
        fullscreenBtn.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                document.querySelector('.video-container').requestFullscreen().catch(err => {
                    console.error(`Error attempting to enable fullscreen: ${err.message}`);
                });
            } else {
                document.exitFullscreen();
            }
        });
        
        // Update button states based on video events
        video.addEventListener('play', () => {
            playPauseBtn.textContent = '⏸';
        });
        
        video.addEventListener('pause', () => {
            playPauseBtn.textContent = '⏯';
        });
        
        // Auto-hide controls when video is playing
        let controlsTimeout;
        const controls = document.querySelector('.controls');
        const titleBar = document.querySelector('.title-bar');
        
        video.addEventListener('playing', () => {
            controlsTimeout = setTimeout(() => {
                controls.style.opacity = '0';
                titleBar.style.opacity = '0';
            }, 3000);
        });
        
        video.addEventListener('pause', () => {
            clearTimeout(controlsTimeout);
            controls.style.opacity = '1';
            titleBar.style.opacity = '1';
        });
        
        // Show controls on mouse move
        document.addEventListener('mousemove', () => {
            controls.style.opacity = '1';
            titleBar.style.opacity = '1';
            clearTimeout(controlsTimeout);
            if (!video.paused) {
                controlsTimeout = setTimeout(() => {
                    controls.style.opacity = '0';
                    titleBar.style.opacity = '0';
                }, 3000);
            }
        });
    </script>
</body>
</html>