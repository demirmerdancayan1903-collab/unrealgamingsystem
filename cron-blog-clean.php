<?php
// 1. Force error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Correct path based on your FTP structure (image_58a774.png)
$configPath = 'assets/includes/config.php';

if (!file_exists($configPath)) {
    die("STOP: 'config.php' not found at: " . $configPath);
}

include($configPath);

// 2. Connect to Database using your CMS variables
$conn = new mysqli($dbGM['host'], $dbGM['user'], $dbGM['pass'], $dbGM['name']);

if ($conn->connect_error) {
    die("STOP: Database Connection failed: " . $conn->connect_error);
}

// 3. Check for expired links in your tracking table
$sql = "SELECT * FROM blog_link_expiry WHERE expiry_date <= NOW()";
$result = $conn->query($sql);

if (!$result) {
    die("STOP: Table 'blog_link_expiry' does not exist.");
}

if ($result->num_rows > 0) {
    while ($item = $result->fetch_assoc()) {
        $blogId = $item['blog_id'];
        $linkContent = $item['full_html_link'];
        $id = $item['id'];

        // Fetch the blog post from gm_blogs (image_59100d.jpg)
        $stmt = $conn->prepare("SELECT post FROM gm_blogs WHERE id = ?");
        $stmt->bind_param("i", $blogId);
        $stmt->execute();
        $blog = $stmt->get_result()->fetch_assoc();
        
        if ($blog) {
            $currentPost = $blog['post'];

            // --- THE CLEANUP LOGIC ---
            // This Regex finds the entire <a ...>...</a> block.
            // Even if the link has extra spaces or attributes, it will be removed.
            $pattern = '/<a\s[^>]*>(.*?)<\/a>/is';
            
            // We search for the specific link content you saved in the tracker
            // and replace the WHOLE tag with just the text inside ($1)
            // If you want the text GONE too, change '$1' to ''
            $cleanedContent = preg_replace($pattern, '$1', $currentPost);

            // Update the database
            $update = $conn->prepare("UPDATE gm_blogs SET post = ? WHERE id = ?");
            $update->bind_param("si", $cleanedContent, $blogId);
            
            if ($update->execute()) {
                // Delete the record from the tracker
                $conn->query("DELETE FROM blog_link_expiry WHERE id = $id");
                echo "Blog #$blogId: Link removed, text preserved.<br>";
            }
        }
    }
} else {
    echo "No expired links found.";
}

$conn->close();
?>