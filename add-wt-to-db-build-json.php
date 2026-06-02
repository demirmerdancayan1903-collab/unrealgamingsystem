<?php
/*
|--------------------------------------------------------------------------
| Build games-wt-video.json from current database
|--------------------------------------------------------------------------
| Put this file in website root of the WORKING website.
|
| Run:
| /build-wt-video-json.php?limit=1000
|
| Restart:
| /build-wt-video-json.php?reset=1&limit=1000
|
| Output:
| /games-wt-video.json
|
| This file stores the real working walkthrough video URLs so the upgrade
| tool or add-wt-to-db.php can apply them later on other websites.
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);
ignore_user_abort(true);

include __DIR__ . '/assets/includes/config.php';

$conn = new mysqli($dbGM['host'], $dbGM['user'], $dbGM['pass'], $dbGM['name']);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 1000;
if ($limit < 1 || $limit > 5000) {
    $limit = 1000;
}

$stateDir = __DIR__ . '/json';
if (!is_dir($stateDir)) {
    mkdir($stateDir, 0755, true);
}

$stateFile = $stateDir . '/build-wt-video-json-state.json';
$outFile = __DIR__ . '/games-wt-video.json';

if (isset($_GET['reset'])) {
    @unlink($stateFile);
    @unlink($outFile);
}

$lastId = 0;
if (file_exists($stateFile)) {
    $state = json_decode(file_get_contents($stateFile), true);
    $lastId = isset($state['last_game_id']) ? (int) $state['last_game_id'] : 0;
}

function normalizeUrl($url)
{
    $url = trim((string) $url);
    $url = str_replace('\\/', '/', $url);
    $url = preg_replace('/\?.*$/', '', $url);
    return $url;
}

function extractGmIdFromImage($image)
{
    $image = normalizeUrl($image);

    if (preg_match('#img\.gamemonetize\.com/([^/]+)/#i', $image, $m)) {
        return trim($m[1]);
    }

    return '';
}

function extractGmIdFromFile($file)
{
    $file = normalizeUrl($file);

    $domains = [
        'html5.gamemonetize.com/',
        'html5.gamemonetize.co/',
        'html5.gamemonetize.games/'
    ];

    foreach ($domains as $domain) {
        if (strpos($file, $domain) !== false) {
            return trim(explode('/', explode($domain, $file, 2)[1])[0]);
        }
    }

    return '';
}

function extractGmIdFromVideo($video)
{
    $video = normalizeUrl($video);

    if (preg_match('#gamemonetize\.video/video/([a-zA-Z0-9]+)(?:-[0-9]{8,12})?\.mp4#i', $video, $m)) {
        return trim($m[1]);
    }

    return '';
}

function isUsableVideo($video)
{
    $video = normalizeUrl($video);

    if ($video === '' || stripos($video, '.mp4') === false) {
        return false;
    }

    if (stripos($video, 'https://html5.gamemonetize') === 0) {
        return false;
    }

    if (stripos($video, 'gamemonetize.video/video/') !== false) {
        return true;
    }

    if (stripos($video, '/games-thumb') !== false) {
        return true;
    }

    return false;
}

function loadExistingJsonMap($file)
{
    $map = [];

    if (!file_exists($file)) {
        return $map;
    }

    $items = json_decode(file_get_contents($file), true);
    if (!is_array($items)) {
        return $map;
    }

    if (isset($items['items']) && is_array($items['items'])) {
        $items = $items['items'];
    }

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $gmId = trim((string)($item['gm_id'] ?? ''));
        $image = normalizeUrl($item['image'] ?? '');
        $video = normalizeUrl($item['wt_video'] ?? '');

        if ($video === '') {
            continue;
        }

        $key = $gmId !== '' ? 'gm:' . $gmId : 'img:' . $image;
        if ($key !== 'img:') {
            $map[$key] = $item;
        }
    }

    return $map;
}

$itemsMap = loadExistingJsonMap($outFile);

$sql = "
    SELECT `game_id`, `game_name`, `image`, `file`, `wt_video`
    FROM `gm_games`
    WHERE `game_id` > {$lastId}
      AND `wt_video` IS NOT NULL
      AND `wt_video` <> ''
      AND `wt_video` LIKE '%.mp4%'
    ORDER BY `game_id` ASC
    LIMIT {$limit}
";

$result = $conn->query($sql);
if (!$result) {
    die('SQL error: ' . $conn->error);
}

$processed = 0;
$stored = 0;
$skipped = 0;
$newLastId = $lastId;

echo '<pre>';
echo "BUILD games-wt-video.json\n";
echo "Starting after game_id: {$lastId}\n";
echo "Limit: {$limit}\n";
echo "Existing JSON rows loaded: " . count($itemsMap) . "\n\n";

while ($row = $result->fetch_assoc()) {
    $processed++;
    $gameId = (int)$row['game_id'];
    $newLastId = $gameId;

    $image = normalizeUrl($row['image']);
    $file = normalizeUrl($row['file']);
    $video = normalizeUrl($row['wt_video']);

    if (!isUsableVideo($video)) {
        $skipped++;
        continue;
    }

    $gmId = extractGmIdFromImage($image);
    if ($gmId === '') {
        $gmId = extractGmIdFromFile($file);
    }
    if ($gmId === '') {
        $gmId = extractGmIdFromVideo($video);
    }

    $key = $gmId !== '' ? 'gm:' . $gmId : 'img:' . $image;
    if ($key === 'img:') {
        $skipped++;
        continue;
    }

    $itemsMap[$key] = [
        'game_id' => $gameId,
        'game_name' => (string)$row['game_name'],
        'gm_id' => $gmId,
        'image' => $image,
        'file' => $file,
        'wt_video' => $video
    ];

    $stored++;
}

$items = array_values($itemsMap);

file_put_contents(
    $outFile,
    json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
);

file_put_contents($stateFile, json_encode([
    'last_game_id' => $newLastId,
    'last_run' => date('Y-m-d H:i:s'),
    'processed_last_run' => $processed,
    'stored_last_run' => $stored,
    'total_json_items' => count($items)
], JSON_PRETTY_PRINT));

echo "DONE\n";
echo "Processed this run: {$processed}\n";
echo "Stored/updated this run: {$stored}\n";
echo "Skipped this run: {$skipped}\n";
echo "Total JSON items now: " . count($items) . "\n";
echo "New last_game_id: {$newLastId}\n\n";

if ($processed === 0) {
    echo "All done or no more rows found.\n";
} else {
    echo "Run again:\n";
    echo "/build-wt-video-json.php?limit={$limit}\n";
}

echo "\nRestart from zero:\n";
echo "/build-wt-video-json.php?reset=1&limit={$limit}\n";
echo '</pre>';

$conn->close();
