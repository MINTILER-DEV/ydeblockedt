<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/*
AIzaSyBdCHy_9607c7yMxFOoKZ5LB7rHVsEaT8s
AIzaSyD4KHNewNVn6bDGIHMX3TC2PlXwlbocW2M
AIzaSyCNmPWJLukDyNYxvjjlt4s70_h7Kp8PJho
*/
// YouTube API Configuration
define('API_KEY', 'AIzaSyD4KHNewNVn6bDGIHMX3TC2PlXwlbocW2M');

define('YT_DLP_API', 'https://658b3067be77.ngrok-free.app');

define('CACHE_DIR', __DIR__ . '/../cache');
define('CACHE_EXPIRE', 7200); // 2 hours in seconds

define('SERVER_DOWN', false);

// Site Configuration
define('SITE_NAME', 'YDeblockedT');
define('SITE_DESCRIPTION', 'A modern YouTube experience styling');
?>
