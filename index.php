<?php
/**
* @package GameMonetize.com CMS - Modern Arcade Script
*
*
* @author GameMonetize.com
*
*/

// --- 1. CONNECT TO DATABASE ---
include('assets/includes/config.php'); 
$conn = new mysqli($dbGM['host'], $dbGM['user'], $dbGM['pass'], $dbGM['name']);

if (!$conn->connect_error) {
    // --- 2. CAPTURE THE FULL URL ---
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $current_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    // --- 3. CHECK DATABASE FOR MATCH ---
    // This looks for the exact link you saved in the Manager
    $stmt = $conn->prepare("SELECT new_url FROM gm_redirects WHERE old_url = ?");
    $stmt->bind_param("s", $current_url);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        // --- 4. EXECUTE REDIRECT ---
        // This moves the user to the New URL
        header("Location: " . $row['new_url'], true, 301);
        exit();
    }
    $stmt->close();
}


if ( !isset($_GET['p']) ) $_GET['p'] = 'home';

require_once dirname( __FILE__ ) . '/gm-load.php';

require_once ABSPATH . 'assets/index/header_tags.php';
require_once ABSPATH . 'assets/index/header.php';
require_once ABSPATH . 'assets/index/footer.php';
require_once ABSPATH . 'assets/index/page.php';
echo \GameMonetize\UI::view('index');

$GameMonetizeConnect->close();