<?php
$directory = $_SERVER['DOCUMENT_ROOT'] . '/games-thumb-video';
$today = date('Y-m-d');

// Pastikan direktori ada
if (!is_dir($directory)) {
    die("Directory not found: $directory");
}

$files = scandir($directory);
$todayFiles = [];

foreach ($files as $file) {
    $filePath = $directory . '/' . $file;
    
    // Lewati jika bukan file atau bukan file .mp4
    if (!is_file($filePath) || pathinfo($filePath, PATHINFO_EXTENSION) !== 'mp4') {
        continue;
    }
    
    // Cek tanggal pembuatan atau modifikasi file
    $fileDate = date('Y-m-d', filectime($filePath));
    if ($fileDate === $today) {
        $todayFiles[] = $file;
    }
}

// Tampilkan hasil dan hapus file
if (!empty($todayFiles)) {
    echo "Videos that created today:\n";
    foreach ($todayFiles as $video) {
        $filePath = $directory . '/' . $video;
        echo "- $video (Deleted)\n";
        unlink($filePath); // Hapus file
    }
} else {
    echo "No new video today.";
}
?>