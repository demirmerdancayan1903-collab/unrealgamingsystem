<?php
/*
|--------------------------------------------------------------------------
| Apply games-wt-video.json to gm_games.wt_video
|--------------------------------------------------------------------------
| Put this file in website root of the upgraded/new website.
| Put games-wt-video.json in the same root folder.
|
| Run:
| /add-wt-to-db.php?limit=500
|
| Restart:
| /add-wt-to-db.php?reset=1&limit=500
|
| Force replace all matched videos:
| /add-wt-to-db.php?reset=1&limit=500&force=1
|
| What it does:
| - Adds gm_games.wt_video if missing
| - Loads games-wt-video.json once
| - Checks every game by batches until all games are checked
| - Matches by GameMonetize ID, image, file, and game name
| - Replaces empty/bad/simple guessed video links with correct JSON video
| - Keeps local /games-thumb videos unless force=1
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

$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 500;
if ($limit < 1 || $limit > 5000) {
    $limit = 500;
}

$force = isset($_GET['force']) && (int)$_GET['force'] === 1;

$stateDir = __DIR__ . '/json';
if (!is_dir($stateDir)) {
    mkdir($stateDir, 0755, true);
}

$stateFile = $stateDir . '/add-wt-to-db-state.json';
$jsonFile = __DIR__ . '/games-wt-video.json';

if (isset($_GET['reset'])) {
    @unlink($stateFile);
}

if (!file_exists($jsonFile)) {
    die('games-wt-video.json not found in website root.');
}

$lastId = 0;
if (file_exists($stateFile)) {
    $state = json_decode(file_get_contents($stateFile), true);
    $lastId = isset($state['last_game_id']) ? (int)$state['last_game_id'] : 0;
}

$checkColumn = $conn->query("SHOW COLUMNS FROM `gm_games` LIKE 'wt_video'");
if ($checkColumn && $checkColumn->num_rows == 0) {
    if (!$conn->query("ALTER TABLE `gm_games` ADD `wt_video` VARCHAR(255) NULL")) {
        die('Error adding wt_video column: ' . $conn->error);
    }
}

function normalizeUrl($url)
{
    $url = trim((string)$url);
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

function isLocalVideo($video)
{
    return stripos((string)$video, '/games-thumb') !== false;
}

function isSimpleGuessedGmVideo($video)
{
    $video = normalizeUrl($video);

    return stripos($video, 'gamemonetize.video/video/') !== false
        && !preg_match('/-[0-9]{8,12}\.mp4$/i', $video);
}

function shouldReplaceVideo($current, $new, $force)
{
    $current = normalizeUrl($current);
    $new = normalizeUrl($new);

    if (!isUsableVideo($new)) {
        return false;
    }

    if ($force) {
        return $current !== $new;
    }

    if ($current === '' || stripos($current, '.mp4') === false) {
        return true;
    }

    if (stripos($current, 'https://html5.gamemonetize') === 0) {
        return true;
    }

    /*
     * Keep local uploaded/converted videos unless forcing.
     */
    if (isLocalVideo($current)) {
        return false;
    }

    /*
     * Replace guessed GameMonetize links without timestamp when JSON has better URL.
     * If JSON also has a simple URL but different, still replace because JSON is source of truth.
     */
    if (isSimpleGuessedGmVideo($current) && $current !== $new) {
        return true;
    }

    return false;
}

function loadVideoMaps($jsonFile)
{
    $raw = json_decode(file_get_contents($jsonFile), true);
    if (!is_array($raw)) {
        die('Invalid games-wt-video.json');
    }

    if (isset($raw['items']) && is_array($raw['items'])) {
        $raw = $raw['items'];
    }

    $maps = [
        'by_gm_id' => [],
        'by_image' => [],
        'by_file' => [],
        'by_name' => []
    ];

    foreach ($raw as $item) {
        if (!is_array($item)) {
            continue;
        }

        $video = normalizeUrl($item['wt_video'] ?? '');
        if (!isUsableVideo($video)) {
            continue;
        }

        $image = normalizeUrl($item['image'] ?? '');
        $file = normalizeUrl($item['file'] ?? '');
        $name = trim((string)($item['game_name'] ?? ''));
        $gmId = trim((string)($item['gm_id'] ?? ''));

        if ($gmId === '' && $image !== '') {
            $gmId = extractGmIdFromImage($image);
        }
        if ($gmId === '' && $file !== '') {
            $gmId = extractGmIdFromFile($file);
        }
        if ($gmId === '') {
            $gmId = extractGmIdFromVideo($video);
        }

        if ($gmId !== '') {
            $maps['by_gm_id'][$gmId] = $video;
        }

        if ($image !== '') {
            $maps['by_image'][$image] = $video;
        }

        if ($file !== '') {
            $maps['by_file'][$file] = $video;
        }

        if ($name !== '') {
            $maps['by_name'][mb_strtolower($name, 'UTF-8')] = $video;
        }
    }

    return $maps;
}

$maps = loadVideoMaps($jsonFile);

$sql = "
    SELECT `game_id`, `game_name`, `image`, `file`, `wt_video`
    FROM `gm_games`
    WHERE `game_id` > {$lastId}
    ORDER BY `game_id` ASC
    LIMIT {$limit}
";

$result = $conn->query($sql);
if (!$result) {
    die('SQL error: ' . $conn->error);
}

$processed = 0;
$matched = 0;
$updated = 0;
$skippedGood = 0;
$noMatch = 0;
$newLastId = $lastId;

echo '<pre>';
echo "APPLY games-wt-video.json TO DATABASE\n";
echo "Starting after game_id: {$lastId}\n";
echo "Limit: {$limit}\n";
echo "Force mode: " . ($force ? 'YES' : 'NO') . "\n";
echo "JSON gm_id matches loaded: " . count($maps['by_gm_id']) . "\n";
echo "JSON image matches loaded: " . count($maps['by_image']) . "\n";
echo "JSON file matches loaded: " . count($maps['by_file']) . "\n";
echo "JSON name matches loaded: " . count($maps['by_name']) . "\n\n";

while ($row = $result->fetch_assoc()) {
    $processed++;
    $gameDbId = (int)$row['game_id'];
    $newLastId = $gameDbId;

    $image = normalizeUrl($row['image']);
    $file = normalizeUrl($row['file']);
    $name = trim((string)$row['game_name']);
    $nameKey = mb_strtolower($name, 'UTF-8');
    $current = normalizeUrl($row['wt_video']);

    $gmId = extractGmIdFromImage($image);
    if ($gmId === '') {
        $gmId = extractGmIdFromFile($file);
    }
    if ($gmId === '') {
        $gmId = extractGmIdFromVideo($current);
    }

    $newVideo = '';

    if ($gmId !== '' && isset($maps['by_gm_id'][$gmId])) {
        $newVideo = $maps['by_gm_id'][$gmId];
    } elseif ($image !== '' && isset($maps['by_image'][$image])) {
        $newVideo = $maps['by_image'][$image];
    } elseif ($file !== '' && isset($maps['by_file'][$file])) {
        $newVideo = $maps['by_file'][$file];
    } elseif ($nameKey !== '' && isset($maps['by_name'][$nameKey])) {
        $newVideo = $maps['by_name'][$nameKey];
    }

    if ($newVideo === '') {
        $noMatch++;
        continue;
    }

    $matched++;

    if (!shouldReplaceVideo($current, $newVideo, $force)) {
        $skippedGood++;
        continue;
    }

    $safeVideo = $conn->real_escape_string($newVideo);

    $update = $conn->query("
        UPDATE `gm_games`
        SET `wt_video` = '{$safeVideo}'
        WHERE `game_id` = {$gameDbId}
        LIMIT 1
    ");

    if ($update) {
        $updated++;
        echo "UPDATED {$gameDbId} | {$name} | {$newVideo}\n";
    } else {
        echo "DB ERROR {$gameDbId}: {$conn->error}\n";
    }
}

file_put_contents($stateFile, json_encode([
    'last_game_id' => $newLastId,
    'last_run' => date('Y-m-d H:i:s'),
    'processed_last_run' => $processed,
    'matched_last_run' => $matched,
    'updated_last_run' => $updated
], JSON_PRETTY_PRINT));

echo "\nDONE\n";
echo "Processed this run: {$processed}\n";
echo "Matched JSON: {$matched}\n";
echo "Updated rows: {$updated}\n";
echo "Skipped already good/local: {$skippedGood}\n";
echo "No JSON match: {$noMatch}\n";
echo "New last_game_id: {$newLastId}\n";

if ($processed === 0) {
    echo "\nAll games checked.\n";
} else {
    echo "\nRun again:\n";
    echo "/add-wt-to-db.php?limit={$limit}" . ($force ? "&force=1" : "") . "\n";
}

echo "\nRestart:\n";
echo "/add-wt-to-db.php?reset=1&limit={$limit}" . ($force ? "&force=1" : "") . "\n";
echo '</pre>';

$conn->close();
