<?php
require_once 'assets/includes/core.php';

set_time_limit(0);
ini_set('max_execution_time', 0);

function processVideos($videoUrls, $apiUrl) {

    $data = json_encode(["video_urls" => $videoUrls]);

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

if (isset($_GET['action']) && isset($_GET['ngrokurl'])) {
    if ($_GET['action'] == 'update_last_30_days') {
        $newGames_query = $GameMonetizeConnect->query("SELECT * FROM ".GAMES." WHERE published='1' AND date_added >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY)) ORDER BY date_added DESC");
    } elseif ($_GET['action'] == 'update_all') {
        $newGames_query = $GameMonetizeConnect->query("SELECT * FROM ".GAMES." WHERE published='1' ORDER BY date_added DESC");
    } else {
        exit('Invalid action');
    }

    if ($newGames_query->num_rows > 0) {
        while ($newGames = $newGames_query->fetch_array()) {
            $gameId = explode("/", $newGames['image'])[3];
            if ($newGames['wt_video']) {
                $wtVideo = $newGames['wt_video'];
            } else {
                $wtVideo = getGameVideo($gameId);
            }

            if ($wtVideo && strpos($wtVideo, '.mp4') !== false) {
                $updateQuery = "UPDATE gm_games SET wt_video = '" . $GameMonetizeConnect->real_escape_string($wtVideo) . "' WHERE game_id = " . $newGames['game_id'];
                if ($GameMonetizeConnect->query($updateQuery) === TRUE) {
                    preg_match('/([^\/]+\.mp4)$/', $wtVideo, $matches);
                    $baseVideoThumbPath = $_SERVER['DOCUMENT_ROOT'] . '/games-thumb-video/' . $matches[1];

                    if (!file_exists($baseVideoThumbPath)) {
                        try {
                            $results = processVideos([$wtVideo], $_GET['ngrokurl'] . '/process_videos');
                            if ($results[0]["output_file"]) {
                                $outputFile = str_replace("\\", "/", $results[0]["output_file"]);
                                $videoContent = file_get_contents($_GET['ngrokurl'] . '/' . $outputFile);
                                file_put_contents($baseVideoThumbPath, $videoContent);
                                echo "Video processed successfully.<br>";
                            }
                        } catch (Exception $e) {
                            echo "Failed to process image for game ID {$newGames['game_id']}: " . $e->getMessage() . "<br>";
                        }
                    } else {
                        echo "Video for game ID {$newGames['game_id']} already exists.<br>";
                    }
                } else {
                    echo "Error updating game ID " . $newGames['game_id'] . ": " . $GameMonetizeConnect->error . "<br>";
                }
            } else {
                echo "No video Walkthrough found for game ID " . $newGames['game_id'] . "<br>";
            }
        }
    } else {
        echo "No games found.";
    }
} else {
    exit('No action specified');
}
?>