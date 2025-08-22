<?php
// update.php - YDeblockedT updater
// Downloads the repo as a ZIP and extracts it

$zipUrl = "https://github.com/MINTILER-DEV/ydeblockedt/archive/refs/heads/main.zip";
$zipFile = __DIR__ . "/repo.zip";
$extractDir = __DIR__ . "/__extract__";

// 1. Clear directory except this file
$files = scandir(__DIR__);
foreach ($files as $f) {
    if ($f === '.' || $f === '..' || $f === basename(__FILE__)) {
        continue;
    }

    $path = __DIR__ . DIRECTORY_SEPARATOR . $f;
    if (is_dir($path)) {
        $it = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $filesIt = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($filesIt as $file) {
            $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }
        rmdir($path);
    } else {
        unlink($path);
    }
}

// 2. Download repo zip
echo "Downloading repository...\n";
file_put_contents($zipFile, file_get_contents($zipUrl));

// 3. Extract zip
$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo($extractDir);
    $zip->close();
    echo "Extracted repository.\n";
} else {
    die("Failed to extract zip\n");
}

// 4. Move extracted files to current dir
$subdir = $extractDir . "/ydeblockedt-main";
$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($subdir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($it as $file) {
    $dest = __DIR__ . DIRECTORY_SEPARATOR . $it->getSubPathName();
    if ($file->isDir()) {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
    } else {
        copy($file, $dest);
    }
}

// 5. Cleanup
unlink($zipFile);

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($extractDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::CHILD_FIRST
);
foreach ($it as $file) {
    $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
}
rmdir($extractDir);

echo "\nUpdate complete!\n";

header("Location: /");
exit;