<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Correct path based on your FTP structure (image_58a774.png)
$configPath = 'assets/includes/config.php';

if (!file_exists($configPath)) {
    die("Debug Error: 'config.php' not found at: " . realpath($configPath));
}

include($configPath); 

// Rest of your connection code...
$servername = $dbGM['host'];
$username   = $dbGM['user'];
$password   = $dbGM['pass'];
$dbname     = $dbGM['name'];

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}

$message = "";

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_link'])) {
    $blog_id     = (int)$_POST['blog_id'];
    $html_code   = $_POST['html_code']; 
    $days        = (int)$_POST['days'];
    $expiry_date = date('Y-m-d H:i:s', strtotime("+$days days"));

    $stmt = $conn->prepare("INSERT INTO blog_link_expiry (blog_id, full_html_link, expiry_date) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $blog_id, $html_code, $expiry_date);
    
    if ($stmt->execute()) {
        $message = "<div style='color: #4CAF50; padding: 10px; background: rgba(76, 175, 80, 0.1); border-radius: 4px; margin-bottom: 20px;'>Link scheduled successfully!</div>";
    } else {
        $message = "<div style='color: #f44336; padding: 10px; background: rgba(244, 67, 54, 0.1); border-radius: 4px; margin-bottom: 20px;'>Error: " . $conn->error . "</div>";
    }
    $stmt->close();
}

// --- Fetch Active Removals ---
$active_links = $conn->query("SELECT e.*, b.title FROM blog_link_expiry e LEFT JOIN gm_blogs b ON e.blog_id = b.id ORDER BY e.expiry_date ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Link Sync Manager</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #1a1a2e; color: #fff; padding: 20px; }
        .container { display: flex; gap: 20px; flex-wrap: wrap; }
        .card { background: #16213e; padding: 20px; border-radius: 8px; border: 1px solid #0f3460; flex: 1; min-width: 400px; }
        h2 { color: #e94560; border-bottom: 1px solid #0f3460; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #95a5a6; font-size: 0.9em; }
        input, textarea { width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #0f3460; background: #0f3460; color: #fff; box-sizing: border-box; }
        button { background: #e94560; color: white; border: none; padding: 12px 25px; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.85em; }
        th { text-align: left; color: #95a5a6; border-bottom: 2px solid #0f3460; padding: 10px; }
        td { padding: 10px; border-bottom: 1px solid #0f3460; }
        .expiry-tag { color: #ff9f43; font-weight: bold; }
        code { background: #000; padding: 2px 5px; border-radius: 3px; color: #4db8ff; }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h2>Schedule Link Removal</h2>
        <?php echo $message; ?>
        <form method="POST">
            <div class="form-group">
                <label>Blog ID (Check gm_blogs table)</label>
                <input type="number" name="blog_id" required>
            </div>
            <div class="form-group">
                <label>Exact HTML Code to Remove</label>
                <textarea name="html_code" rows="5" placeholder='<a href="https://...">Link Text</a>' required></textarea>
            </div>
            <div class="form-group">
                <label>Remove After (Days)</label>
                <input type="number" name="days" value="30" required>
            </div>
            <button type="submit" name="add_link">Add to Link Sync</button>
        </form>
    </div>

    <div class="card">
        <h2>Active Scheduled Removals</h2>
        <table>
            <thead>
                <tr>
                    <th>Blog ID</th>
                    <th>HTML Code</th>
                    <th>Expiry Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $active_links->fetch_assoc()): ?>
                <tr>
                    <td><strong>#<?php echo $row['blog_id']; ?></strong></td>
                    <td><code><?php echo htmlspecialchars($row['full_html_link']); ?></code></td>
                    <td class="expiry-tag"><?php echo date('M d, Y', strtotime($row['expiry_date'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>