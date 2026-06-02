<?php
/*
|--------------------------------------------------------------------------
| CMS Upgrade Tool - Simple + Video Walkthrough JSON Fix
|--------------------------------------------------------------------------
| Put this file in website root as:
| upgrade-cms.php
|
| Put these files in website root:
| latest-cms.zip       = latest CMS files
| database.sql         = latest database structure
| games-wt-video.json  = correct walkthrough video links
|
| Important:
| - Files replace protects ONLY config.php
| - Database step creates missing tables and adds missing columns
| - Video step reads games-wt-video.json and fixes gm_games.wt_video
| - Video step runs in small AJAX batches, so one click can continue until done
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);
ignore_user_abort(true);

$ROOT = realpath(__DIR__);
$TOOL_NAME = basename(__FILE__);
$CMS_ZIP = $ROOT . DIRECTORY_SEPARATOR . 'latest-cms.zip';
$DATABASE_SQL = $ROOT . DIRECTORY_SEPARATOR . 'database.sql';
$VIDEO_JSON = $ROOT . DIRECTORY_SEPARATOR . 'games-wt-video.json';
$STATE_DIR = $ROOT . DIRECTORY_SEPARATOR . 'json';
$VIDEO_STATE = $STATE_DIR . DIRECTORY_SEPARATOR . 'upgrade-video-walkthrough-state.json';
$TEMP_DIR = $ROOT . DIRECTORY_SEPARATOR . '_cms_upgrade_tmp_' . date('Ymd_His');

if (!is_dir($STATE_DIR)) {
    @mkdir($STATE_DIR, 0755, true);
}

$summary = [
    'ran' => false,
    'title' => '',
    'status' => 'info',
    'message' => '',
    'db_tables_created' => 0,
    'db_tables_would_create' => 0,
    'db_columns_added' => 0,
    'db_columns_would_add' => 0,
    'db_errors' => 0,
    'files_replaced' => 0,
    'files_would_replace' => 0,
    'files_created' => 0,
    'files_would_create' => 0,
    'files_same' => 0,
    'files_config_skipped' => 0,
    'file_errors' => 0,
    'post_theme_fixed' => 0,
    'post_theme_would_fix' => 0,
    'post_errors' => 0,
    'cleanup_deleted' => 0,
    'cleanup_errors' => 0,
    'first_error' => ''
];

function h($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

function setSummary($title, $message, $status = 'info') {
    global $summary;
    $summary['ran'] = true;
    $summary['title'] = $title;
    $summary['message'] = $message;
    $summary['status'] = $status;
}

function addError($message, $kind = 'post_errors') {
    global $summary;
    if ($summary['first_error'] === '') {
        $summary['first_error'] = $message;
    }
    if (isset($summary[$kind])) {
        $summary[$kind]++;
    }
}

function jsonResponse($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function normalizePath($path) {
    $path = str_replace('\\', '/', $path);
    $path = preg_replace('#/+#', '/', $path);
    return trim($path, '/');
}

function deleteDirSafe($dir) {
    if (!is_dir($dir)) {
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        if ($item->isDir()) {
            @rmdir($item->getPathname());
        } else {
            @unlink($item->getPathname());
        }
    }

    @rmdir($dir);
}

/*
|--------------------------------------------------------------------------
| Database connection
|--------------------------------------------------------------------------
*/

function getMysqliOrNull() {
    static $mysqli = null;
    static $checked = false;

    if ($checked) {
        return $mysqli;
    }

    $checked = true;

    $configFile = __DIR__ . '/assets/includes/config.php';

    if (!file_exists($configFile)) {
        addError('Config file missing: assets/includes/config.php', 'db_errors');
        return null;
    }

    $dbGM = [];
    $config = [];
    require $configFile;

    if (isset($dbGM) && is_array($dbGM)) {
        $host = $dbGM['host'] ?? null;
        $name = $dbGM['name'] ?? null;
        $user = $dbGM['user'] ?? null;
        $pass = $dbGM['pass'] ?? '';

        if ($host && $name && $user) {
            $mysqli = @new mysqli($host, $user, $pass, $name);
            if ($mysqli instanceof mysqli && !$mysqli->connect_error) {
                $mysqli->set_charset('utf8mb4');
                return $mysqli;
            }
            if ($mysqli instanceof mysqli && $mysqli->connect_error) {
                addError('Database connection failed: ' . $mysqli->connect_error, 'db_errors');
                return null;
            }
        }
    }

    if (isset($config) && is_array($config)) {
        $host = $config['host'] ?? $config['db_host'] ?? null;
        $name = $config['name'] ?? $config['database'] ?? $config['db_name'] ?? null;
        $user = $config['user'] ?? $config['username'] ?? $config['db_user'] ?? null;
        $pass = $config['pass'] ?? $config['password'] ?? $config['db_pass'] ?? '';

        if ($host && $name && $user) {
            $mysqli = @new mysqli($host, $user, $pass, $name);
            if ($mysqli instanceof mysqli && !$mysqli->connect_error) {
                $mysqli->set_charset('utf8mb4');
                return $mysqli;
            }
        }
    }

    addError('Database connection not detected from config.php', 'db_errors');
    return null;
}

function tableExists($table) {
    $mysqli = getMysqliOrNull();
    if (!$mysqli) {
        return false;
    }

    $safe = $mysqli->real_escape_string($table);
    $result = $mysqli->query("SHOW TABLES LIKE '{$safe}'");
    return $result && $result->num_rows > 0;
}

function getColumns($table) {
    $mysqli = getMysqliOrNull();
    $columns = [];

    if (!$mysqli || !tableExists($table)) {
        return $columns;
    }

    $safeTable = str_replace('`', '', $table);
    $result = $mysqli->query("SHOW COLUMNS FROM `{$safeTable}`");

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
    }

    return $columns;
}

/*
|--------------------------------------------------------------------------
| Database structure upgrade
|--------------------------------------------------------------------------
*/

function parseCreateTables($filePath) {
    $tables = [];

    if (!file_exists($filePath)) {
        return $tables;
    }

    $handle = fopen($filePath, 'r');
    if (!$handle) {
        return $tables;
    }

    $collecting = false;
    $sql = '';
    $table = '';

    while (($line = fgets($handle)) !== false) {
        if (!$collecting) {
            if (preg_match('/^\s*CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z0-9_]+)`?/i', $line, $m)) {
                $collecting = true;
                $table = $m[1];
                $sql = $line;

                if (strpos($line, ';') !== false) {
                    $tables[$table] = trim($sql);
                    $collecting = false;
                    $sql = '';
                    $table = '';
                }
            }
            continue;
        }

        $sql .= $line;

        if (strpos($line, ';') !== false) {
            if ($table !== '') {
                $tables[$table] = trim($sql);
            }
            $collecting = false;
            $sql = '';
            $table = '';
        }
    }

    fclose($handle);
    return $tables;
}

function extractCreateColumns($createSql) {
    $columns = [];
    $start = strpos($createSql, '(');
    $end = strrpos($createSql, ')');

    if ($start === false || $end === false || $end <= $start) {
        return $columns;
    }

    $inside = substr($createSql, $start + 1, $end - $start - 1);
    $lines = preg_split('/\r\n|\r|\n/', $inside);

    foreach ($lines as $line) {
        $line = trim(rtrim(trim($line), ','));
        if ($line === '') {
            continue;
        }

        if (preg_match('/^`([^`]+)`\s+(.+)$/s', $line, $m)) {
            $columns[$m[1]] = $line;
        }
    }

    return $columns;
}

function cleanCreateSql($sql) {
    $sql = trim($sql);
    $sql = preg_replace('/^\s*DROP\s+TABLE\s+.*?;\s*/is', '', $sql);

    if (!preg_match('/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS/i', $sql)) {
        $sql = preg_replace('/CREATE\s+TABLE\s+/i', 'CREATE TABLE IF NOT EXISTS ', $sql, 1);
    }

    $lines = preg_split('/\r\n|\r|\n/', $sql);
    $new = [];

    foreach ($lines as $line) {
        $trim = trim($line);
        if (stripos($trim, 'CONSTRAINT ') === 0 && stripos($trim, 'FOREIGN KEY') !== false) {
            continue;
        }
        if (stripos($trim, 'FOREIGN KEY') === 0) {
            continue;
        }
        $new[] = $line;
    }

    $sql = implode("\n", $new);
    $sql = preg_replace('/,\s*\)\s*ENGINE=/is', "\n) ENGINE=", $sql);
    $sql = preg_replace('/,\s*\)\s*;/is', "\n);", $sql);

    return $sql;
}

function runDatabaseUpgrade($dryRun = true) {
    global $DATABASE_SQL, $summary;

    $mysqli = getMysqliOrNull();
    if (!$mysqli) {
        return;
    }

    if (!file_exists($DATABASE_SQL)) {
        addError('database.sql not found in website root', 'db_errors');
        return;
    }

    $tables = parseCreateTables($DATABASE_SQL);
    if (empty($tables)) {
        addError('No CREATE TABLE blocks found in database.sql', 'db_errors');
        return;
    }

    if (!$dryRun) {
        $mysqli->query('SET FOREIGN_KEY_CHECKS=0');
    }

    foreach ($tables as $table => $createSql) {
        $safeTable = str_replace('`', '', $table);

        if (!tableExists($safeTable)) {
            if ($dryRun) {
                $summary['db_tables_would_create']++;
            } else {
                $sql = cleanCreateSql($createSql);
                if ($mysqli->query($sql)) {
                    $summary['db_tables_created']++;
                } else {
                    addError($mysqli->error . ' | SQL: ' . $sql, 'db_errors');
                }
            }
            continue;
        }

        $templateColumns = extractCreateColumns($createSql);
        $existingColumns = getColumns($safeTable);

        foreach ($templateColumns as $column => $definition) {
            if (in_array($column, $existingColumns, true)) {
                continue;
            }

            if ($dryRun) {
                $summary['db_columns_would_add']++;
            } else {
                $sql = "ALTER TABLE `{$safeTable}` ADD COLUMN {$definition}";
                if ($mysqli->query($sql)) {
                    $summary['db_columns_added']++;
                    $existingColumns[] = $column;
                } else {
                    addError($mysqli->error . ' | SQL: ' . $sql, 'db_errors');
                }
            }
        }
    }

    if (!$dryRun) {
        $mysqli->query('SET FOREIGN_KEY_CHECKS=1');
    }
}

/*
|--------------------------------------------------------------------------
| File upgrade
|--------------------------------------------------------------------------
*/

function findRealCmsRoot($extractDir) {
    $rootFiles = ['index.php', 'gm-load.php', 'assets', 'templates'];
    $score = 0;

    foreach ($rootFiles as $item) {
        if (file_exists($extractDir . DIRECTORY_SEPARATOR . $item)) {
            $score++;
        }
    }

    if ($score >= 2) {
        return $extractDir;
    }

    $items = array_values(array_filter(scandir($extractDir), function ($item) use ($extractDir) {
        return $item !== '.' && $item !== '..' && is_dir($extractDir . DIRECTORY_SEPARATOR . $item);
    }));

    if (count($items) === 1) {
        $inside = $extractDir . DIRECTORY_SEPARATOR . $items[0];
        $insideScore = 0;

        foreach ($rootFiles as $item) {
            if (file_exists($inside . DIRECTORY_SEPARATOR . $item)) {
                $insideScore++;
            }
        }

        if ($insideScore >= 2) {
            return $inside;
        }
    }

    return $extractDir;
}

function unzipCms($zipPath, $tempDir) {
    if (!class_exists('ZipArchive')) {
        addError('PHP ZipArchive extension is missing. Enable php-zip first.', 'file_errors');
        return false;
    }

    if (!file_exists($zipPath)) {
        addError('latest-cms.zip not found in website root', 'file_errors');
        return false;
    }

    deleteDirSafe($tempDir);

    if (!mkdir($tempDir, 0755, true)) {
        addError('Cannot create temp folder: ' . $tempDir, 'file_errors');
        return false;
    }

    $zip = new ZipArchive();
    $open = $zip->open($zipPath);

    if ($open !== true) {
        addError('Cannot open latest-cms.zip. ZipArchive code: ' . $open, 'file_errors');
        return false;
    }

    if (!$zip->extractTo($tempDir)) {
        $zip->close();
        addError('Cannot extract latest-cms.zip', 'file_errors');
        return false;
    }

    $zip->close();
    return true;
}

function collectFiles($sourceRoot) {
    $files = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceRoot, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        if ($item->isDir()) {
            continue;
        }

        $full = $item->getPathname();
        $relative = normalizePath(substr($full, strlen($sourceRoot) + 1));

        if ($relative !== '') {
            $files[] = [
                'source' => $full,
                'relative' => $relative
            ];
        }
    }

    usort($files, function ($a, $b) {
        return strcmp($a['relative'], $b['relative']);
    });

    return $files;
}

function runFileUpgrade($dryRun = true) {
    global $ROOT, $CMS_ZIP, $TEMP_DIR, $TOOL_NAME, $summary;

    if (!file_exists($CMS_ZIP)) {
        addError('latest-cms.zip not found in website root', 'file_errors');
        return;
    }

    if (!unzipCms($CMS_ZIP, $TEMP_DIR)) {
        return;
    }

    $sourceRoot = findRealCmsRoot($TEMP_DIR);
    $files = collectFiles($sourceRoot);

    if (empty($files)) {
        addError('No files found inside latest-cms.zip', 'file_errors');
        deleteDirSafe($TEMP_DIR);
        return;
    }

    foreach ($files as $file) {
        $relative = $file['relative'];
        $source = $file['source'];
        $destination = $ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);

        if (strtolower(basename($relative)) === 'config.php') {
            $summary['files_config_skipped']++;
            continue;
        }

        if ($relative === $TOOL_NAME) {
            continue;
        }

        $destinationExists = file_exists($destination);
        $same = false;

        if ($destinationExists && filesize($source) === filesize($destination)) {
            $same = (@md5_file($source) === @md5_file($destination));
        }

        if ($same) {
            $summary['files_same']++;
            continue;
        }

        if ($dryRun) {
            if ($destinationExists) {
                $summary['files_would_replace']++;
            } else {
                $summary['files_would_create']++;
            }
            continue;
        }

        $destinationDir = dirname($destination);

        if (!is_dir($destinationDir) && !mkdir($destinationDir, 0755, true)) {
            addError('Cannot create folder: ' . $destinationDir, 'file_errors');
            continue;
        }

        if (!copy($source, $destination)) {
            addError('Copy failed: ' . $relative, 'file_errors');
            continue;
        }

        @chmod($destination, 0644);

        if ($destinationExists) {
            $summary['files_replaced']++;
        } else {
            $summary['files_created']++;
        }
    }

    deleteDirSafe($TEMP_DIR);
}

/*
|--------------------------------------------------------------------------
| Post install simple fixes
|--------------------------------------------------------------------------
*/

function addColumnIfMissing($table, $column, $definition) {
    $mysqli = getMysqliOrNull();
    if (!$mysqli || !tableExists($table)) {
        return false;
    }

    $columns = getColumns($table);
    if (in_array($column, $columns, true)) {
        return true;
    }

    $safeTable = str_replace('`', '', $table);
    $safeColumn = str_replace('`', '', $column);

    return $mysqli->query("ALTER TABLE `{$safeTable}` ADD COLUMN `{$safeColumn}` {$definition}");
}

function runPostInstallFixes($dryRun = true) {
    global $summary;

    $mysqli = getMysqliOrNull();
    if (!$mysqli) {
        return;
    }

    /*
     * Set theme to crazygames-like when possible.
     */
    foreach (['gm_settings', 'gm_setting', 'settings'] as $table) {
        if (!tableExists($table)) {
            continue;
        }

        $columns = getColumns($table);

        if (in_array('site_theme', $columns, true)) {
            if ($dryRun) {
                $summary['post_theme_would_fix']++;
            } else {
                if ($mysqli->query("UPDATE `{$table}` SET `site_theme`='crazygames-like'")) {
                    $summary['post_theme_fixed']++;
                } else {
                    addError($mysqli->error, 'post_errors');
                }
            }
            break;
        }

        $keyColumn = '';
        $valueColumn = '';

        foreach (['name', 'setting_name', 'option_name', 'config_name', 'key', 'setting_key'] as $candidate) {
            if (in_array($candidate, $columns, true)) {
                $keyColumn = $candidate;
                break;
            }
        }

        foreach (['value', 'setting_value', 'option_value', 'config_value'] as $candidate) {
            if (in_array($candidate, $columns, true)) {
                $valueColumn = $candidate;
                break;
            }
        }

        if ($keyColumn && $valueColumn) {
            if ($dryRun) {
                $summary['post_theme_would_fix']++;
            } else {
                $safeTable = str_replace('`', '', $table);
                $safeKey = str_replace('`', '', $keyColumn);
                $safeValue = str_replace('`', '', $valueColumn);

                $exists = 0;
                $check = $mysqli->query("SELECT COUNT(*) AS total FROM `{$safeTable}` WHERE `{$safeKey}`='site_theme'");
                if ($check) {
                    $row = $check->fetch_assoc();
                    $exists = (int)($row['total'] ?? 0);
                }

                if ($exists > 0) {
                    $sql = "UPDATE `{$safeTable}` SET `{$safeValue}`='crazygames-like' WHERE `{$safeKey}`='site_theme'";
                } else {
                    $sql = "INSERT INTO `{$safeTable}` (`{$safeKey}`, `{$safeValue}`) VALUES ('site_theme', 'crazygames-like')";
                }

                if ($mysqli->query($sql)) {
                    $summary['post_theme_fixed']++;
                } else {
                    addError($mysqli->error, 'post_errors');
                }
            }
            break;
        }
    }

    /*
     * Make old ChatGPT settings save correctly.
     */
    if (tableExists('gm_chatgpt')) {
        addColumnIfMissing('gm_chatgpt', 'llm_provider', "varchar(50) NOT NULL DEFAULT 'openai'");
        addColumnIfMissing('gm_chatgpt', 'template_blog', "text NULL");
        addColumnIfMissing('gm_chatgpt', 'template_blog_tag', "text NULL");
        addColumnIfMissing('gm_chatgpt', 'template_blog_title', "text NULL");
        addColumnIfMissing('gm_chatgpt', 'template_blog_related_box', "text NULL");
        addColumnIfMissing('gm_chatgpt', 'openrouter_api_key', "text NULL");
        addColumnIfMissing('gm_chatgpt', 'rewrite_old_games_limit', "int(11) NOT NULL DEFAULT 1");

        if (!$dryRun) {
            $mysqli->query("INSERT INTO `gm_chatgpt` (`id`) SELECT 1 WHERE NOT EXISTS (SELECT 1 FROM `gm_chatgpt` WHERE `id`=1)");
        }
    }
}

/*
|--------------------------------------------------------------------------
| Video JSON fix helpers
|--------------------------------------------------------------------------
*/

function normalizeUrl($url) {
    $url = trim((string)$url);
    $url = str_replace('\\/', '/', $url);
    $url = preg_replace('/\?.*$/', '', $url);
    return $url;
}

function extractGmIdFromImage($image) {
    $image = normalizeUrl($image);

    if (preg_match('#img\.gamemonetize\.com/([^/]+)/#i', $image, $m)) {
        return trim($m[1]);
    }

    return '';
}

function extractGmIdFromFile($file) {
    $file = normalizeUrl($file);

    foreach (['html5.gamemonetize.com/', 'html5.gamemonetize.co/', 'html5.gamemonetize.games/'] as $domain) {
        if (strpos($file, $domain) !== false) {
            return trim(explode('/', explode($domain, $file, 2)[1])[0]);
        }
    }

    return '';
}

function extractGmIdFromVideo($video) {
    $video = normalizeUrl($video);

    if (preg_match('#gamemonetize\.video/video/([a-zA-Z0-9]+)(?:-[0-9]{8,12})?\.mp4#i', $video, $m)) {
        return trim($m[1]);
    }

    return '';
}

function isUsableVideo($video) {
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

function isLocalVideo($video) {
    return stripos((string)$video, '/games-thumb') !== false;
}

function isSimpleGuessedGmVideo($video) {
    $video = normalizeUrl($video);

    return stripos($video, 'gamemonetize.video/video/') !== false
        && !preg_match('/-[0-9]{8,12}\.mp4$/i', $video);
}

function shouldReplaceVideo($current, $new, $force) {
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

    if (isLocalVideo($current)) {
        return false;
    }

    if (isSimpleGuessedGmVideo($current) && $current !== $new) {
        return true;
    }

    return false;
}

function loadVideoMaps($jsonFile) {
    $raw = json_decode(file_get_contents($jsonFile), true);

    if (!is_array($raw)) {
        return false;
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

function getVideoProgress() {
    global $VIDEO_STATE;

    $mysqli = getMysqliOrNull();
    if (!$mysqli || !tableExists('gm_games')) {
        return [
            'last_game_id' => 0,
            'total_games' => 0,
            'remaining_games' => 0,
            'checked_games' => 0
        ];
    }

    $lastId = 0;

    if (file_exists($VIDEO_STATE)) {
        $state = json_decode(file_get_contents($VIDEO_STATE), true);
        $lastId = isset($state['last_game_id']) ? (int)$state['last_game_id'] : 0;
    }

    $total = 0;
    $remaining = 0;

    $totalResult = $mysqli->query("SELECT COUNT(*) AS total FROM `gm_games`");
    if ($totalResult) {
        $row = $totalResult->fetch_assoc();
        $total = (int)($row['total'] ?? 0);
    }

    $remainingResult = $mysqli->query("SELECT COUNT(*) AS total FROM `gm_games` WHERE `game_id` > {$lastId}");
    if ($remainingResult) {
        $row = $remainingResult->fetch_assoc();
        $remaining = (int)($row['total'] ?? 0);
    }

    return [
        'last_game_id' => $lastId,
        'total_games' => $total,
        'remaining_games' => $remaining,
        'checked_games' => max(0, $total - $remaining)
    ];
}

function runVideoJsonBatch($limit = 500, $force = false, $reset = false) {
    global $VIDEO_JSON, $VIDEO_STATE;

    $mysqli = getMysqliOrNull();

    if (!$mysqli) {
        jsonResponse([
            'ok' => false,
            'message' => 'Database connection problem.'
        ]);
    }

    if (!file_exists($VIDEO_JSON)) {
        jsonResponse([
            'ok' => false,
            'message' => 'games-wt-video.json was not found in website root. Add it inside the ZIP root or upload it beside upgrade-cms.php.'
        ]);
    }

    if (!tableExists('gm_games')) {
        jsonResponse([
            'ok' => false,
            'message' => 'gm_games table not found.'
        ]);
    }

    if ($reset && file_exists($VIDEO_STATE)) {
        @unlink($VIDEO_STATE);
    }

    $checkColumn = $mysqli->query("SHOW COLUMNS FROM `gm_games` LIKE 'wt_video'");
    if ($checkColumn && $checkColumn->num_rows == 0) {
        if (!$mysqli->query("ALTER TABLE `gm_games` ADD `wt_video` VARCHAR(255) NULL")) {
            jsonResponse([
                'ok' => false,
                'message' => 'Could not add wt_video column: ' . $mysqli->error
            ]);
        }
    }

    $maps = loadVideoMaps($VIDEO_JSON);
    if ($maps === false) {
        jsonResponse([
            'ok' => false,
            'message' => 'games-wt-video.json is not valid JSON.'
        ]);
    }

    $lastId = 0;
    if (file_exists($VIDEO_STATE)) {
        $state = json_decode(file_get_contents($VIDEO_STATE), true);
        $lastId = isset($state['last_game_id']) ? (int)$state['last_game_id'] : 0;
    }

    $limit = (int)$limit;
    if ($limit < 1 || $limit > 5000) {
        $limit = 500;
    }

    $result = $mysqli->query("
        SELECT `game_id`, `game_name`, `image`, `file`, `wt_video`
        FROM `gm_games`
        WHERE `game_id` > {$lastId}
        ORDER BY `game_id` ASC
        LIMIT {$limit}
    ");

    if (!$result) {
        jsonResponse([
            'ok' => false,
            'message' => 'SQL error: ' . $mysqli->error
        ]);
    }

    $processed = 0;
    $matched = 0;
    $updated = 0;
    $skipped = 0;
    $noMatch = 0;
    $newLastId = $lastId;

    while ($row = $result->fetch_assoc()) {
        $processed++;
        $gameId = (int)$row['game_id'];
        $newLastId = $gameId;

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
            $skipped++;
            continue;
        }

        $safeVideo = $mysqli->real_escape_string($newVideo);

        if ($mysqli->query("UPDATE `gm_games` SET `wt_video`='{$safeVideo}' WHERE `game_id`={$gameId} LIMIT 1")) {
            $updated++;
        }
    }

    file_put_contents($VIDEO_STATE, json_encode([
        'last_game_id' => $newLastId,
        'last_run' => date('Y-m-d H:i:s'),
        'processed_last_run' => $processed,
        'matched_last_run' => $matched,
        'updated_last_run' => $updated
    ], JSON_PRETTY_PRINT));

    $progress = getVideoProgress();

    jsonResponse([
        'ok' => true,
        'done' => ($processed === 0 || $progress['remaining_games'] === 0),
        'processed' => $processed,
        'matched' => $matched,
        'updated' => $updated,
        'skipped' => $skipped,
        'no_match' => $noMatch,
        'last_game_id' => $newLastId,
        'total_games' => $progress['total_games'],
        'checked_games' => $progress['checked_games'],
        'remaining_games' => $progress['remaining_games'],
        'json_gm_id_count' => count($maps['by_gm_id'])
    ]);
}

/*
|--------------------------------------------------------------------------
| Cleanup
|--------------------------------------------------------------------------
*/

function cleanupUpgradeFiles() {
    global $CMS_ZIP, $DATABASE_SQL, $VIDEO_JSON, $summary;

    foreach ([$CMS_ZIP, $DATABASE_SQL, $VIDEO_JSON] as $file) {
        if (!file_exists($file) || !is_file($file)) {
            continue;
        }

        if (@unlink($file)) {
            $summary['cleanup_deleted']++;
        } else {
            addError('Could not delete: ' . basename($file), 'cleanup_errors');
        }
    }
}

/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

if (isset($_POST['ajax_action']) && $_POST['ajax_action'] === 'fix_videos') {
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 500;
    $force = isset($_POST['force']) && (int)$_POST['force'] === 1;
    $reset = isset($_POST['reset']) && (int)$_POST['reset'] === 1;

    runVideoJsonBatch($limit, $force, $reset);
}

$action = $_POST['action'] ?? '';

if ($action === 'analyze_db') {
    runDatabaseUpgrade(true);
    setSummary('Database analyze finished', 'No changes were made. Check the numbers below.', $summary['db_errors'] ? 'error' : 'success');
}

if ($action === 'upgrade_db') {
    runDatabaseUpgrade(false);
    setSummary('Database upgrade finished', 'Missing tables/columns were added.', $summary['db_errors'] ? 'error' : 'success');
}

if ($action === 'analyze_files') {
    runFileUpgrade(true);
    setSummary('CMS files analyze finished', 'No files were changed. Check the numbers below.', $summary['file_errors'] ? 'error' : 'success');
}

if ($action === 'upgrade_files') {
    runFileUpgrade(false);
    setSummary('CMS files replace finished', 'Files were copied from latest-cms.zip. Test the site before cleanup.', $summary['file_errors'] ? 'error' : 'success');
}

if ($action === 'analyze_post') {
    runPostInstallFixes(true);
    setSummary('Post install analyze finished', 'No changes were made. Video walkthrough is fixed with the separate purple button.', $summary['post_errors'] ? 'error' : 'success');
}

if ($action === 'upgrade_post') {
    runPostInstallFixes(false);
    setSummary('Post install fixes finished', 'Theme/default admin fixes were applied. Use the purple button for video walkthrough.', $summary['post_errors'] ? 'error' : 'success');
}

if ($action === 'analyze_full') {
    runDatabaseUpgrade(true);
    runFileUpgrade(true);
    runPostInstallFixes(true);
    setSummary('Full analyze finished', 'No changes were made. Check the numbers below.', ($summary['db_errors'] || $summary['file_errors'] || $summary['post_errors']) ? 'error' : 'success');
}

if ($action === 'upgrade_full') {
    runDatabaseUpgrade(false);
    runFileUpgrade(false);
    runPostInstallFixes(false);
    setSummary('Full upgrade finished', 'Database, files, and simple post install fixes are done. Now run video walkthrough fix if needed.', ($summary['db_errors'] || $summary['file_errors'] || $summary['post_errors']) ? 'error' : 'success');
}

if ($action === 'cleanup') {
    cleanupUpgradeFiles();
    setSummary('Cleanup finished', 'Deleted latest-cms.zip, database.sql, and games-wt-video.json if they existed. Now delete upgrade-cms.php manually.', $summary['cleanup_errors'] ? 'error' : 'success');
}

$zipFound = file_exists($CMS_ZIP);
$sqlFound = file_exists($DATABASE_SQL);
$jsonFound = file_exists($VIDEO_JSON);

$zipSize = $zipFound ? round(filesize($CMS_ZIP) / 1024 / 1024, 2) . ' MB' : 'Not found';
$sqlSize = $sqlFound ? round(filesize($DATABASE_SQL) / 1024 / 1024, 2) . ' MB' : 'Not found';
$jsonSize = $jsonFound ? round(filesize($VIDEO_JSON) / 1024 / 1024, 2) . ' MB' : 'Not found';

$dbStatus = 'Not checked';
$mysqli = getMysqliOrNull();
if ($mysqli) {
    $dbStatus = 'Connected';
} else {
    $dbStatus = 'Connection problem';
}

$videoProgress = getVideoProgress();

$dbDisabled = $sqlFound ? '' : 'disabled';
$fileDisabled = $zipFound ? '' : 'disabled';
$fullDisabled = ($zipFound && $sqlFound) ? '' : 'disabled';
$videoDisabled = ($jsonFound && $mysqli && tableExists('gm_games')) ? '' : 'disabled';

$alertText = '';
if ($summary['ran']) {
    $alertText =
        $summary['title'] . "\n\n" .
        $summary['message'] . "\n\n" .
        "DB tables to create: " . $summary['db_tables_would_create'] . "\n" .
        "DB tables created: " . $summary['db_tables_created'] . "\n" .
        "DB columns to add: " . $summary['db_columns_would_add'] . "\n" .
        "DB columns added: " . $summary['db_columns_added'] . "\n" .
        "Files to replace: " . $summary['files_would_replace'] . "\n" .
        "Files replaced: " . $summary['files_replaced'] . "\n" .
        "Files to create: " . $summary['files_would_create'] . "\n" .
        "Files created: " . $summary['files_created'] . "\n" .
        "Errors: " . ($summary['db_errors'] + $summary['file_errors'] + $summary['post_errors'] + $summary['cleanup_errors']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Simple CMS Upgrade Tool</title>
<style>
    body {
        margin: 0;
        background: #171923;
        color: #f3f4ff;
        font-family: Arial, sans-serif;
        padding: 28px;
    }
    .wrap {
        max-width: 1050px;
        margin: 0 auto;
    }
    .box {
        background: #222436;
        border: 1px solid #373a55;
        border-radius: 14px;
        padding: 22px;
        margin-bottom: 18px;
    }
    h1 {
        margin-top: 0;
        color: #4ee07a;
    }
    h2 {
        margin-top: 0;
    }
    .good {
        background: #173626;
        border: 1px solid #32b76a;
        color: #bfffd6;
        padding: 14px;
        border-radius: 10px;
        line-height: 1.55;
        margin-bottom: 14px;
    }
    .danger {
        background: #3b1f22;
        border: 1px solid #d94b5d;
        color: #ffd4da;
        padding: 14px;
        border-radius: 10px;
        line-height: 1.55;
        margin-bottom: 14px;
    }
    .status-row {
        background: #2d3045;
        border: 1px solid #424761;
        padding: 11px 13px;
        border-radius: 8px;
        margin: 8px 0;
    }
    code {
        background: #11131f;
        color: #ffd166;
        border-radius: 5px;
        padding: 2px 6px;
    }
    .section-title {
        margin-top: 18px;
        margin-bottom: 6px;
        color: #ffd166;
        font-weight: bold;
    }
    .buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 10px;
    }
    button {
        border: 0;
        color: white;
        padding: 12px 18px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 700;
        font-size: 15px;
    }
    button:disabled {
        opacity: .45;
        cursor: not-allowed;
        filter: grayscale(1);
    }
    .btn-analyze { background: #536dfe; }
    .btn-db { background: #0ea5e9; }
    .btn-files { background: #16a34a; }
    .btn-post { background: #a855f7; }
    .btn-clean { background: #e11d48; }
    .result-success {
        background: #173626;
        border: 1px solid #32b76a;
        color: #bfffd6;
    }
    .result-error {
        background: #3b1f22;
        border: 1px solid #d94b5d;
        color: #ffd4da;
    }
    .result-info {
        background: #1f2b44;
        border: 1px solid #536dfe;
        color: #dce6ff;
    }
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        margin-top: 12px;
    }
    .small-stat {
        background: rgba(0,0,0,.22);
        padding: 9px 11px;
        border-radius: 8px;
    }
    ul {
        line-height: 1.7;
    }
    .modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 99999;
        background: rgba(0,0,0,.72);
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-card {
        width: min(620px, 100%);
        background: #222436;
        border: 1px solid #536dfe;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 20px 80px rgba(0,0,0,.45);
    }
    .bar {
        width: 100%;
        height: 18px;
        border-radius: 999px;
        background: #11131f;
        overflow: hidden;
        border: 1px solid #424761;
    }
    .bar-fill {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, #16a34a, #a855f7);
        transition: width .25s ease;
    }
    .modal-text {
        margin: 14px 0;
        line-height: 1.6;
    }
    @media (max-width: 700px) {
        .summary-grid { grid-template-columns: 1fr; }
        .buttons { flex-direction: column; }
        button { width: 100%; }
    }
</style>
</head>
<body>
<div class="wrap">
    <div class="box">
        <h1>Simple CMS Upgrade Tool</h1>

        <div class="good">
            Upload these files beside this tool: <code>latest-cms.zip</code>, <code>database.sql</code>, and optional <code>games-wt-video.json</code>.
            Video walkthrough fix uses the JSON file and runs in small batches with one click.
        </div>

        <div class="danger">
            File replace protects only <code>config.php</code>. Everything else from the ZIP can overwrite old CMS files.
        </div>

        <div class="status-row">Website root: <code><?php echo h($ROOT); ?></code></div>
        <div class="status-row">Database: <code><?php echo h($dbStatus); ?></code></div>
        <div class="status-row">CMS ZIP: <?php echo $zipFound ? '✅' : '❌'; ?> <code>latest-cms.zip</code> - <?php echo h($zipSize); ?></div>
        <div class="status-row">Database SQL: <?php echo $sqlFound ? '✅' : '❌'; ?> <code>database.sql</code> - <?php echo h($sqlSize); ?></div>
        <div class="status-row">Video JSON: <?php echo $jsonFound ? '✅' : '❌'; ?> <code>games-wt-video.json</code> - <?php echo h($jsonSize); ?></div>
        <div class="status-row">
            Video progress:
            checked <code id="checkedText"><?php echo (int)$videoProgress['checked_games']; ?></code> /
            total <code id="totalText"><?php echo (int)$videoProgress['total_games']; ?></code>,
            remaining <code id="remainingText"><?php echo (int)$videoProgress['remaining_games']; ?></code>
        </div>

        <form method="post">
            <div class="section-title">1. Database</div>
            <div class="buttons">
                <button <?php echo $dbDisabled; ?> class="btn-analyze" type="submit" name="action" value="analyze_db">Analyze Database</button>
                <button <?php echo $dbDisabled; ?> class="btn-db" type="submit" name="action" value="upgrade_db" onclick="return confirm('Run database upgrade?');">Run Database Upgrade</button>
            </div>

            <div class="section-title">2. CMS files</div>
            <div class="buttons">
                <button <?php echo $fileDisabled; ?> class="btn-analyze" type="submit" name="action" value="analyze_files">Analyze CMS Files</button>
                <button <?php echo $fileDisabled; ?> class="btn-files" type="submit" name="action" value="upgrade_files" onclick="return confirm('Replace CMS files from ZIP except config.php?');">Run CMS File Replace</button>
            </div>

            <div class="section-title">3. Post install</div>
            <div class="buttons">
                <button class="btn-analyze" type="submit" name="action" value="analyze_post">Analyze Post Install</button>
                <button class="btn-post" type="submit" name="action" value="upgrade_post" onclick="return confirm('Run simple post install fixes?');">Run Post Install Fixes</button>
            </div>

            <div class="section-title">4. Video walkthrough from JSON</div>
            <div class="buttons">
                <button <?php echo $videoDisabled; ?> class="btn-post" type="button" onclick="startVideoFix(false)">
                    Fix Video Walkthroughs
                </button>
                <button <?php echo $videoDisabled; ?> class="btn-analyze" type="button" onclick="startVideoFix(true)">
                    Restart + Force Fix All Videos
                </button>
            </div>

            <div class="section-title">Full upgrade</div>
            <div class="buttons">
                <button <?php echo $fullDisabled; ?> class="btn-analyze" type="submit" name="action" value="analyze_full">Analyze Full Upgrade</button>
                <button <?php echo $fullDisabled; ?> class="btn-files" type="submit" name="action" value="upgrade_full" onclick="return confirm('Run database upgrade, CMS file replace, and simple post install fixes?');">Run Full Upgrade</button>
            </div>

            <div class="section-title">Cleanup</div>
            <div class="buttons">
                <button class="btn-clean" type="submit" name="action" value="cleanup" onclick="return confirm('Delete latest-cms.zip, database.sql, and games-wt-video.json now? Do this only after testing.');">Manual Cleanup</button>
            </div>
        </form>
    </div>

    <?php if ($summary['ran']): ?>
        <div class="box result-<?php echo h($summary['status']); ?>">
            <h2><?php echo h($summary['title']); ?></h2>
            <p><?php echo h($summary['message']); ?></p>

            <?php if ($summary['first_error'] !== ''): ?>
                <p><strong>First error:</strong> <code><?php echo h($summary['first_error']); ?></code></p>
            <?php endif; ?>

            <div class="summary-grid">
                <div class="small-stat">DB tables to create: <strong><?php echo (int)$summary['db_tables_would_create']; ?></strong></div>
                <div class="small-stat">DB tables created: <strong><?php echo (int)$summary['db_tables_created']; ?></strong></div>
                <div class="small-stat">DB columns to add: <strong><?php echo (int)$summary['db_columns_would_add']; ?></strong></div>
                <div class="small-stat">DB columns added: <strong><?php echo (int)$summary['db_columns_added']; ?></strong></div>
                <div class="small-stat">DB errors: <strong><?php echo (int)$summary['db_errors']; ?></strong></div>
                <div class="small-stat">Files to replace: <strong><?php echo (int)$summary['files_would_replace']; ?></strong></div>
                <div class="small-stat">Files replaced: <strong><?php echo (int)$summary['files_replaced']; ?></strong></div>
                <div class="small-stat">Files to create: <strong><?php echo (int)$summary['files_would_create']; ?></strong></div>
                <div class="small-stat">Files created: <strong><?php echo (int)$summary['files_created']; ?></strong></div>
                <div class="small-stat">Same files: <strong><?php echo (int)$summary['files_same']; ?></strong></div>
                <div class="small-stat">Config skipped: <strong><?php echo (int)$summary['files_config_skipped']; ?></strong></div>
                <div class="small-stat">File errors: <strong><?php echo (int)$summary['file_errors']; ?></strong></div>
                <div class="small-stat">Theme fixed: <strong><?php echo (int)$summary['post_theme_fixed']; ?></strong></div>
                <div class="small-stat">Post errors: <strong><?php echo (int)$summary['post_errors']; ?></strong></div>
                <div class="small-stat">Cleanup deleted: <strong><?php echo (int)$summary['cleanup_deleted']; ?></strong></div>
            </div>
        </div>
    <?php endif; ?>

    <div class="box">
        <h2>How to use</h2>
        <ul>
            <li>Upload <code>upgrade-cms.php</code> to website root.</li>
            <li>Upload <code>latest-cms.zip</code> and <code>database.sql</code> to the same root folder.</li>
            <li>Add <code>games-wt-video.json</code> inside the ZIP root or upload it to website root.</li>
            <li>Run database upgrade, then CMS file replace.</li>
            <li>Click <strong>Fix Video Walkthroughs</strong>. Wait until the popup says finished.</li>
            <li>If hosting times out, reopen this page and click the same video button again. It continues from the last game.</li>
            <li>After testing website and admin, click cleanup and manually delete <code>upgrade-cms.php</code>.</li>
        </ul>
    </div>
</div>

<div class="modal" id="videoModal">
    <div class="modal-card">
        <h2 id="videoTitle">Fixing video walkthroughs...</h2>
        <div class="bar"><div class="bar-fill" id="videoBar"></div></div>
        <div class="modal-text" id="videoText">Starting...</div>
        <div class="buttons">
            <button class="btn-clean" type="button" onclick="stopVideoFix()">Stop</button>
            <button class="btn-files" type="button" id="closeVideoBtn" style="display:none" onclick="location.reload()">Close and Refresh Page</button>
        </div>
    </div>
</div>

<?php if ($summary['ran'] && $alertText !== ''): ?>
<script>
alert(<?php echo json_encode($alertText); ?>);
</script>
<?php endif; ?>

<script>
let videoFixRunning = false;
let videoFixFirstCall = true;
let videoForceMode = false;

function setVideoText(text) {
    document.getElementById('videoText').innerHTML = text;
}

function setVideoProgress(checked, total, remaining) {
    const percent = total > 0 ? Math.min(100, Math.round((checked / total) * 100)) : 0;
    document.getElementById('videoBar').style.width = percent + '%';
    document.getElementById('checkedText').textContent = checked;
    document.getElementById('totalText').textContent = total;
    document.getElementById('remainingText').textContent = remaining;
}

function stopVideoFix() {
    videoFixRunning = false;
    document.getElementById('videoTitle').textContent = 'Stopped';
    setVideoText('Stopped by user. You can click the button again later and it will continue.');
    document.getElementById('closeVideoBtn').style.display = 'inline-block';
}

function startVideoFix(force) {
    videoForceMode = !!force;
    videoFixRunning = true;
    videoFixFirstCall = !!force;

    document.getElementById('videoModal').style.display = 'flex';
    document.getElementById('videoTitle').textContent = force ? 'Restarting and force fixing videos...' : 'Fixing video walkthroughs...';
    document.getElementById('closeVideoBtn').style.display = 'none';
    setVideoText('Starting. Please wait...');
    runVideoBatch();
}

function runVideoBatch() {
    if (!videoFixRunning) {
        return;
    }

    const formData = new FormData();
    formData.append('ajax_action', 'fix_videos');
    formData.append('limit', '500');
    formData.append('force', videoForceMode ? '1' : '0');
    formData.append('reset', videoFixFirstCall ? '1' : '0');

    videoFixFirstCall = false;

    fetch(window.location.href, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (!data.ok) {
            videoFixRunning = false;
            document.getElementById('videoTitle').textContent = 'Video fix stopped with error';
            setVideoText(data.message || 'Unknown error.');
            document.getElementById('closeVideoBtn').style.display = 'inline-block';
            return;
        }

        setVideoProgress(data.checked_games, data.total_games, data.remaining_games);

        setVideoText(
            '<strong>Processed this batch:</strong> ' + data.processed + '<br>' +
            '<strong>Matched JSON:</strong> ' + data.matched + '<br>' +
            '<strong>Updated:</strong> ' + data.updated + '<br>' +
            '<strong>Skipped already good/local:</strong> ' + data.skipped + '<br>' +
            '<strong>No JSON match:</strong> ' + data.no_match + '<br>' +
            '<strong>Remaining games:</strong> ' + data.remaining_games + '<br>' +
            '<strong>JSON video records loaded:</strong> ' + data.json_gm_id_count
        );

        if (data.done) {
            videoFixRunning = false;
            document.getElementById('videoTitle').textContent = 'Video walkthrough fix finished';
            document.getElementById('closeVideoBtn').style.display = 'inline-block';
            return;
        }

        setTimeout(runVideoBatch, 350);
    })
    .catch(function(error) {
        videoFixRunning = false;
        document.getElementById('videoTitle').textContent = 'Connection stopped';
        setVideoText(
            'The request stopped or hosting timed out.<br>' +
            'Refresh this page and click <strong>Fix Video Walkthroughs</strong> again.<br>' +
            'It will continue from the last saved game.'
        );
        document.getElementById('closeVideoBtn').style.display = 'inline-block';
    });
}
</script>
</body>
</html>
