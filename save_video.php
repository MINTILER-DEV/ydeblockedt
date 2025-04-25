<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

try {
    // Validate request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $videoId = $_POST['videoId'] ?? '';
    if (empty($videoId)) {
        throw new Exception('Missing video ID');
    }

    // Validate file
    if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload failed');
    }

    // Verify file type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES['video']['tmp_name']);
    if ($mime !== 'video/webm') {
        throw new Exception('Invalid file type');
    }

    // Set up download directory
    $downloadDir = __DIR__ . '/downloads';
    if (!is_dir($downloadDir)) {
        mkdir($downloadDir, 0755, true);
    }

    // Save file
    $destination = "$downloadDir/$videoId.webm";
    if (!move_uploaded_file($_FILES['video']['tmp_name'], $destination)) {
        throw new Exception('Failed to save file');
    }

    // Verify file was saved
    if (!file_exists($destination)) {
        throw new Exception('File verification failed');
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}