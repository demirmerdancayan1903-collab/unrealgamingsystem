<?php
// --- 1. FORCE DEBUGGING ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }

echo "<div style='background:#000; color:#0f0; padding:15px; font-family:monospace; font-size:12px; border-bottom:2px solid #0f0;'>";
echo "<strong>DEBUG CONSOLE:</strong><br>";

// --- 2. CONFIG & DB CONNECTION ---
$configPath = 'assets/includes/config.php'; 
if (!file_exists($configPath)) { die("CRITICAL ERROR: 'config.php' missing at $configPath</div>"); }
include($configPath);
echo "- Config: FOUND<br>";

$conn = new mysqli($dbGM['host'], $dbGM['user'], $dbGM['pass'], $dbGM['name']);
if ($conn->connect_error) { die("- Connection: FAILED (" . $conn->connect_error . ")</div>"); }
echo "- Database: CONNECTED to " . $dbGM['name'] . "<br>";

// --- 3. AUTO-REPAIR: Add Missing Columns ---
// We check if the column 'setting_key' exists to avoid the Fatal Error
$columnCheck = $conn->query("SHOW COLUMNS FROM `gm_redirects` LIKE 'setting_key'");
if ($columnCheck && $columnCheck->num_rows == 0) {
    echo "- Repair: Adding missing security columns...<br>";
    $conn->query("ALTER TABLE `gm_redirects` ADD `setting_key` VARCHAR(50) DEFAULT NULL, ADD `setting_value` TEXT DEFAULT NULL");
} else {
    echo "- Table Structure: OK<br>";
}

// --- 4. PASSWORD SECURITY LOGIC ---
$passwordRow = $conn->query("SELECT setting_value FROM gm_redirects WHERE setting_key = 'admin_password' LIMIT 1");
$passwordExists = ($passwordRow && $passwordRow->num_rows > 0);

if (!$passwordExists) { echo "- Status: NO PASSWORD SET<br>"; }

// Handle Setting/Logging In
if (!$passwordExists && isset($_POST['set_new_pass'])) {
    $hashed = password_hash($_POST['new_pass'], PASSWORD_DEFAULT);
    $conn->query("INSERT INTO gm_redirects (setting_key, setting_value) VALUES ('admin_password', '$hashed')");
    echo "- Action: PASSWORD CREATED<br>";
    $passwordExists = true;
}

if (isset($_POST['login_pass']) && $passwordExists) {
    $data = $passwordRow->fetch_assoc();
    if (password_verify($_POST['login_pass'], $data['setting_value'])) {
        $_SESSION['manager_access'] = true;
        echo "- Action: LOGIN SUCCESS<br>";
    } else {
        echo "- Action: LOGIN FAILED (Wrong Password)<br>";
    }
}

if (isset($_GET['logout'])) { session_destroy(); header("Location: redirect-manager.php"); exit(); }

echo "</div>";

// --- 5. SECURE LOCK ---
if (!isset($_SESSION['manager_access']) || $_SESSION['manager_access'] !== true) {
    echo '<body style="background:#1a1a2e;color:#fff;font-family:sans-serif;display:flex;justify-content:center;align-items:center;height:80vh;margin:0;">';
    echo '<form method="POST" style="background:#16213e;padding:40px;border-radius:10px;border:1px solid #0f3460;width:320px;">';
    if (!$passwordExists) {
        echo '<h3>Setup Security</h3><p style="font-size:12px;color:#95a5a6;">Create a password to lock this manager.</p>';
        echo '<input type="password" name="new_pass" placeholder="New Password" required style="padding:12px;width:100%;margin-bottom:20px;background:#0f3460;color:#fff;border:1px solid #4db8ff;box-sizing:border-box;">';
        echo '<button type="submit" name="set_new_pass" style="background:#4db8ff;color:#fff;padding:12px;width:100%;border:none;cursor:pointer;font-weight:bold;">Save & Lock</button>';
    } else {
        echo '<h2 style="color:#e94560;">Secure Login</h2>';
        echo '<input type="password" name="login_pass" placeholder="Enter Password" required style="padding:12px;width:100%;margin-bottom:20px;background:#0f3460;color:#fff;border:1px solid #e94560;box-sizing:border-box;">';
        echo '<button type="submit" style="background:#e94560;color:#fff;padding:12px;width:100%;border:none;cursor:pointer;font-weight:bold;">Login</button>';
    }
    echo '</form></body>';
    exit;
}

// --- 6. MANAGEMENT LOGIC ---
$message = "";
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM gm_redirects WHERE id = $id");
    $message = "<div class='alert' style='background:rgba(233,69,96,0.2);color:#e94560;padding:10px;border-radius:5px;'>Deleted.</div>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_redirect'])) {
    $old = mysqli_real_escape_string($conn, $_POST['old_url']);
    $new = mysqli_real_escape_string($conn, $_POST['new_url']);
    $conn->query("INSERT INTO gm_redirects (old_url, new_url) VALUES ('$old', '$new')");
    $message = "<div class='alert' style='background:rgba(76,175,80,0.2);color:#4CAF50;padding:10px;border-radius:5px;'>Saved!</div>";
}

$list = $conn->query("SELECT * FROM gm_redirects WHERE setting_key IS NULL ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>301 Redirect Manager</title>
    <style>
        body { font-family: sans-serif; background: #1a1a2e; color: #fff; padding: 20px; }
        .card { background: #16213e; padding: 25px; border-radius: 10px; border: 1px solid #0f3460; max-width: 900px; margin: 0 auto 25px auto; }
        input { width: 100%; padding: 12px; margin-bottom: 20px; background: #0f3460; border: 1px solid #1a1a2e; color: #fff; border-radius: 5px; box-sizing: border-box; }
        .btn { background: #e94560; color: #fff; padding: 12px; border: none; border-radius: 5px; cursor: pointer; width: 100%; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; background: #0f3460; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #16213e; font-size: 0.9em; }
        code { color: #4db8ff; word-break: break-all; }
    </style>
</head>
<body>
    <div class="card">
        <a href="?logout=1" style="float:right;color:#95a5a6;text-decoration:none;">Logout</a>
        <h2 style="color:#e94560;">301 Redirect Manager</h2>
        <?php echo $message; ?>
        <form method="POST">
            <label>Full Old Website Link</label>
            <input type="url" name="old_url" placeholder="https://www.ovigames.com/old-page" required>
            <label>Full New Destination Link</label>
            <input type="url" name="new_url" placeholder="https://www.ovigames.com/new-page" required>
            <button type="submit" name="add_redirect" class="btn">Add 301 Redirect</button>
        </form>
    </div>

    <div class="card">
        <h3>Active Redirects</h3>
        <table>
            <thead><tr><th>Old URL</th><th>New URL</th><th>Action</th></tr></thead>
            <tbody>
                <?php while($row = $list->fetch_assoc()): ?>
                <tr>
                    <td><code><?php echo htmlspecialchars($row['old_url']); ?></code></td>
                    <td><code><?php echo htmlspecialchars($row['new_url']); ?></code></td>
                    <td><a href="?delete_id=<?php echo $row['id']; ?>" style="color:#e94560;text-decoration:none;" onclick="return confirm('Delete?')">Delete</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>