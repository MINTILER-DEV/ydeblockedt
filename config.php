<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// YouTube API Configuration
define('NONE', 'AIzaSyD4KHNewNVn6bDGIHMX3TC2PlXwlbocW2M');
define('API_KEY', 'AIzaSyBdCHy_9607c7yMxFOoKZ5LB7rHVsEaT8s');
define('NONE2', 'AIzaSyCNmPWJLukDyNYxvjjlt4s70_h7Kp8PJho');

define('YT_DLP_API', 'https://2c21-2605-a601-ae33-8100-b4bd-ad7e-f0bc-2524.ngrok-free.app');

define('CACHE_DIR', __DIR__ . '/../cache');
define('CACHE_EXPIRE', 7200); // 2 hours in seconds

define('SERVER_DOWN', false);

// Site Configuration
define('SITE_NAME', 'YDeblockedT');
define('SITE_DESCRIPTION', 'A modern YouTube experience with neon styling');
?>