<?php
require_once 'assets/includes/core.php';

if (isset($_GET['action'])) {
    if ($_GET['action'] == 'update_last_30_days') {
        $newGames_query = $GameMonetizeConnect->query("SELECT * FROM ".GAMES." WHERE published='1' AND date_added >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY)) ORDER BY date_added DESC");
    } elseif ($_GET['action'] == 'update_all') {
        $newGames_query = $GameMonetizeConnect->query("SELECT * FROM ".GAMES." WHERE published='1' ORDER BY date_added DESC");
    } else {
        exit('Invalid action');
    }

    if ($newGames_query->num_rows > 0) {
        while ($newGames = $newGames_query->fetch_array()) {
            preg_match("/\/([a-zA-Z0-9]+)\//", $newGames['image'], $matches);
            $imageId = $matches[1];
            $imageUrl = $newGames['image'];
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/games-image/' . $imageId . '/250x150.webp';

            if (!file_exists($imagePath)) {
                try {
                    resizeAndCompressImage($imageUrl, $imagePath, 200, 150);
                    echo "Images processed successfully.<br>";
                } catch (Exception $e) {
                    echo "Failed to process image for game ID {$newGames['game_id']}: " . $e->getMessage() . "<br>";
                }
            } else {
                echo "Image for game ID {$newGames['game_id']} already exists.<br>";
            }
        }
    } else {
        echo "No games found.";
    }
} else {
    exit('No action specified');
}
?>