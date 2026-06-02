<?php
include 'assets/includes/config.php';

$servername = $dbGM['host'];
$username = $dbGM['user'];
$password = $dbGM['pass'];
$dbname = $dbGM['name'];

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to check if an index exists
function indexExists($conn, $table, $index) {
    $result = $conn->query("SHOW INDEX FROM $table WHERE Key_name = '$index'");
    return $result->num_rows > 0;
}

// SQL to add indexes if they do not exist
$indexes = [
    ['table' => 'gm_games', 'index' => 'idx_published', 'column' => 'published'],
    ['table' => 'gm_games', 'index' => 'idx_category', 'column' => 'category'],
    ['table' => 'gm_games', 'index' => 'idx_date_added', 'column' => 'date_added'],
    ['table' => 'gm_sliders', 'index' => 'idx_ordering', 'column' => 'ordering'],
    ['table' => 'gm_categories', 'index' => 'idx_show_home', 'column' => 'show_home'],
    ['table' => 'gm_tags', 'index' => 'idx_show_home', 'column' => 'show_home'],
    ['table' => 'gm_tags', 'index' => 'idx_name', 'column' => 'name'],
];

foreach ($indexes as $index) {
    if (!indexExists($conn, $index['table'], $index['index'])) {
        $sql = "ALTER TABLE {$index['table']} ADD INDEX {$index['index']} ({$index['column']})";
        if ($conn->query($sql) === TRUE) {
            echo "Index {$index['index']} added successfully on table {$index['table']}<br>";
        } else {
            echo "Error adding index {$index['index']} on table {$index['table']}: " . $conn->error . "<br>";
        }
    } else {
        echo "Index {$index['index']} already exists on table {$index['table']}<br>";
    }
}

// Close connection
$conn->close();
?>