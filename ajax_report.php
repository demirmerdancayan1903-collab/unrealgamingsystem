<?php
 
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Content-Type: application/json; charset=utf-8');

require $_SERVER['DOCUMENT_ROOT'] . '/assets/includes/config.php';

$conn = mysqli_connect(
    $dbGM['host'],
    $dbGM['user'],
    $dbGM['pass'],
    $dbGM['name']
);

if (!$conn) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'DB failed']);
    exit;
}

$game_id     = (int)($_POST['game_id'] ?? 0);
$game_name   = trim($_POST['game_name'] ?? '');
$user_email  = trim($_POST['user_email'] ?? '');
$report_type = trim($_POST['report_type'] ?? '');
$subject     = trim($_POST['subject'] ?? '');
$message     = trim($_POST['message'] ?? '');

if (!$game_id || !$game_name || !$user_email || !$report_type || !$subject || !$message) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Missing fields']);
    exit;
}

if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Invalid email']);
    exit;
}

$game_name   = mysqli_real_escape_string($conn, $game_name);
$user_email  = mysqli_real_escape_string($conn, $user_email);
$report_type = mysqli_real_escape_string($conn, $report_type);
$subject     = mysqli_real_escape_string($conn, $subject);
$message     = mysqli_real_escape_string($conn, $message);

$sql = "INSERT INTO gm_reports
(game_id, game_name, user_email, report_type, subject, message)
VALUES
('$game_id', '$game_name', '$user_email', '$report_type', '$subject', '$message')";

if (!mysqli_query($conn, $sql)) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    exit;
}

ob_clean();
echo json_encode([
    'status' => 'ok',
    'message' => 'Your report has been submitted successfully.'
]);
exit;