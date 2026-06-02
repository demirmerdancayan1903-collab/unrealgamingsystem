<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require $_SERVER['DOCUMENT_ROOT'] . '/assets/includes/config.php';

$conn = mysqli_connect(
    $dbGM['host'],
    $dbGM['user'],
    $dbGM['pass'],
    $dbGM['name']
);

if (!$conn) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . mysqli_connect_error()
    ]);
    exit;
}

/* ---------- GET COUNTS ONLY ---------- */
if (isset($_GET['action']) && $_GET['action'] === 'get_counts') {
    $game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;

    if ($game_id <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing game_id'
        ]);
        exit;
    }

    $res = mysqli_query($conn, "
        SELECT like_count, dislike_count, favorite_count, plays
        FROM gm_games
        WHERE game_id = '$game_id'
        LIMIT 1
    ");

    if (!$res) {
        echo json_encode([
            'status' => 'error',
            'message' => mysqli_error($conn)
        ]);
        exit;
    }

    $row = mysqli_fetch_assoc($res);

    echo json_encode([
        'status' => 'ok',
        'likes' => (int)($row['like_count'] ?? 0),
        'dislikes' => (int)($row['dislike_count'] ?? 0),
        'favorites' => (int)($row['favorite_count'] ?? 0),
        'plays' => (int)($row['plays'] ?? 0)
    ]);
    exit;
}

/* ---------- SAVE VOTE ---------- */
$game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;
$type    = isset($_POST['type']) ? trim($_POST['type']) : '';

if ($game_id <= 0 || $type === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing game_id or type'
    ]);
    exit;
}

$allowed = ['like', 'dislike', 'favorite'];
if (!in_array($type, $allowed, true)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid type'
    ]);
    exit;
}

if (empty($_COOKIE['visitor_token'])) {
    $token = bin2hex(random_bytes(16));
    setcookie('visitor_token', $token, time() + 86400 * 365, '/');
    $_COOKIE['visitor_token'] = $token;
}

$token = mysqli_real_escape_string($conn, $_COOKIE['visitor_token']);
$ip    = mysqli_real_escape_string($conn, $_SERVER['REMOTE_ADDR'] ?? '');
$type  = mysqli_real_escape_string($conn, $type);

mysqli_begin_transaction($conn);

try {
    if ($type === 'like') {
        $hasLike = mysqli_query($conn, "SELECT id FROM gm_game_actions WHERE game_id='$game_id' AND visitor_token='$token' AND action_type='like' LIMIT 1");
        if (mysqli_num_rows($hasLike) > 0) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'exists', 'message' => 'Already liked']);
            exit;
        }

        $hasDislike = mysqli_query($conn, "SELECT id FROM gm_game_actions WHERE game_id='$game_id' AND visitor_token='$token' AND action_type='dislike' LIMIT 1");
        if (mysqli_num_rows($hasDislike) > 0) {
            mysqli_query($conn, "DELETE FROM gm_game_actions WHERE game_id='$game_id' AND visitor_token='$token' AND action_type='dislike'");
            mysqli_query($conn, "UPDATE gm_games SET dislike_count = GREATEST(dislike_count - 1, 0) WHERE game_id='$game_id'");
        }

        mysqli_query($conn, "INSERT INTO gm_game_actions (game_id, visitor_token, ip_address, action_type) VALUES ('$game_id', '$token', '$ip', 'like')");
        mysqli_query($conn, "UPDATE gm_games SET like_count = like_count + 1 WHERE game_id='$game_id'");
    }

    if ($type === 'dislike') {
        $hasDislike = mysqli_query($conn, "SELECT id FROM gm_game_actions WHERE game_id='$game_id' AND visitor_token='$token' AND action_type='dislike' LIMIT 1");
        if (mysqli_num_rows($hasDislike) > 0) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'exists', 'message' => 'Already disliked']);
            exit;
        }

        $hasLike = mysqli_query($conn, "SELECT id FROM gm_game_actions WHERE game_id='$game_id' AND visitor_token='$token' AND action_type='like' LIMIT 1");
        if (mysqli_num_rows($hasLike) > 0) {
            mysqli_query($conn, "DELETE FROM gm_game_actions WHERE game_id='$game_id' AND visitor_token='$token' AND action_type='like'");
            mysqli_query($conn, "UPDATE gm_games SET like_count = GREATEST(like_count - 1, 0) WHERE game_id='$game_id'");
        }

        mysqli_query($conn, "INSERT INTO gm_game_actions (game_id, visitor_token, ip_address, action_type) VALUES ('$game_id', '$token', '$ip', 'dislike')");
        mysqli_query($conn, "UPDATE gm_games SET dislike_count = dislike_count + 1 WHERE game_id='$game_id'");
    }

    if ($type === 'favorite') {
        $hasFavorite = mysqli_query($conn, "SELECT id FROM gm_game_actions WHERE game_id='$game_id' AND visitor_token='$token' AND action_type='favorite' LIMIT 1");
        if (mysqli_num_rows($hasFavorite) > 0) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'exists', 'message' => 'Already favorited']);
            exit;
        }

        mysqli_query($conn, "INSERT INTO gm_game_actions (game_id, visitor_token, ip_address, action_type) VALUES ('$game_id', '$token', '$ip', 'favorite')");
        mysqli_query($conn, "UPDATE gm_games SET favorite_count = favorite_count + 1 WHERE game_id='$game_id'");
    }

    $res = mysqli_query($conn, "SELECT like_count, dislike_count, favorite_count, plays FROM gm_games WHERE game_id='$game_id' LIMIT 1");
    $row = mysqli_fetch_assoc($res);

    mysqli_commit($conn);

    echo json_encode([
        'status' => 'ok',
        'likes' => (int)$row['like_count'],
        'dislikes' => (int)$row['dislike_count'],
        'favorites' => (int)$row['favorite_count'],
        'plays' => (int)$row['plays']
    ]);
    exit;

} catch (Throwable $e) {
    mysqli_rollback($conn);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit;
}