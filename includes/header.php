<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_URI'] != '/server_down.php' && SERVER_DOWN) {
    header("Location: /server_down.php");
    exit;
}

$pageTitle = isset($pageTitle) ? $pageTitle : SITE_NAME;
$pageDescription = isset($pageDescription) ? $pageDescription : SITE_DESCRIPTION;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitizeOutput($pageTitle) ?></title>
    <meta name="description" content="<?= sanitizeOutput($pageDescription) ?>">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Animate.css for big flashy animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <!-- AOS (Animate On Scroll) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/styles.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark neon-border-bottom">
        <div class="container">
            <a class="navbar-brand neon-text" href="/">YDeblockedT</a>
            <form class="d-flex ms-auto" action="/search.php" method="get">
                <input class="form-control me-2 glow" type="search" name="q" placeholder="Search..." required>
                <button class="btn neon-btn" type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </nav>
    <main class="flex-grow-1 py-4">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1200, // how long animations last
            once: true, // animate only once
        });
    </script>

