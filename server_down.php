<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!SERVER_DOWN) {
    header("Location: /");
    exit;
}

// Set maintenance window (3:30 PM CST)
$maintenanceEndUTC = '15:30'; // 3:30 PM UTC-6 (CST)
$cstTime = new DateTimeZone('America/Chicago');
$maintenanceTime = new DateTime("today $maintenanceEndUTC", $cstTime);

$pageTitle = "Server Down - " . SITE_NAME;
$pageDescription = "We're upgrading our systems. Service will resume at 3:30 PM CST.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --neon-color: #0fa;
            --neon-glow: 0 0 10px var(--neon-color), 
                         0 0 20px var(--neon-color), 
                         0 0 40px var(--neon-color);
            --bg-color: #111;
            --text-color: #fff;
        }
        
        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            overflow: hidden;
        }
        
        .neon-text {
            color: var(--neon-color);
            text-shadow: var(--neon-glow);
            animation: flicker 1.5s infinite alternate;
        }
        
        .neon-border {
            border: 2px solid var(--neon-color);
            box-shadow: inset 0 0 10px var(--neon-color), 
                        0 0 20px var(--neon-color);
            border-radius: 15px;
            animation: border-pulse 2s infinite;
        }
        
        .maintenance-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            text-align: center;
        }
        
        .countdown {
            font-size: 3rem;
            margin: 2rem 0;
        }
        
        @keyframes flicker {
            0%, 19%, 21%, 23%, 25%, 54%, 56%, 100% {
                text-shadow: var(--neon-glow);
            }
            20%, 24%, 55% {
                text-shadow: none;
            }
        }
        
        @keyframes border-pulse {
            0% { box-shadow: 0 0 10px var(--neon-color); }
            100% { box-shadow: 0 0 20px var(--neon-color); }
        }
        
        .local-time {
            font-size: 1.2rem;
            margin-top: 1rem;
            color: var(--neon-color);
        }
    </style>
</head>
<body>
    <div class="maintenance-container neon-border">
        <h1 class="neon-text">SERVER DOWN</h1>
        <p class="lead">Sorry for the inconvenience, but our servers are currently down.</p>
        
        <div class="countdown neon-text" id="countdown">
            --:--:--
        </div>
        
        <div class="local-time" id="localTime">
            Server will be up at <span id="cstTime">3:30 PM CST</span><br>
            (<span id="userTime"></span> your time)
        </div>
        
        <div class="progress mt-4" style="height: 10px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                 role="progressbar" style="width: 100%"></div>
        </div>
    </div>

    <script>
        // Convert PHP time to JS
        const maintenanceEnd = new Date(<?= $maintenanceTime->getTimestamp() * 1000 ?>);
        
        // Display user's local time equivalent
        const userTimeEl = document.getElementById('userTime');
        userTimeEl.textContent = maintenanceEnd.toLocaleTimeString([], { 
            hour: '2-digit', 
            minute: '2-digit',
            timeZoneName: 'short' 
        });
        
        // Countdown function
        function updateCountdown() {
            const now = new Date();
            const diff = maintenanceEnd - now;
            
            if (diff <= 0) {
                document.getElementById('countdown').innerHTML = "MAINTENANCE COMPLETE!";
                document.querySelector('.progress-bar').style.width = "0%";
                setTimeout(() => { location.reload(); }, 3000);
                return;
            }
            
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const secs = Math.floor((diff % (1000 * 60)) / 1000);
            
            document.getElementById('countdown').innerHTML = 
                `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                
            // Progress bar (linear countdown)
            const totalHours = 2;
            const progress = 100 - Math.min(100, (now - (maintenanceEnd - totalHours * 60 * 60 * 1000)) / (totalHours * 60 * 60 * 1000) * 100);
            document.querySelector('.progress-bar').style.width = `${progress}%`;
        }
        
        setInterval(updateCountdown, 1000);
        updateCountdown();
    </script>
</body>
</html>