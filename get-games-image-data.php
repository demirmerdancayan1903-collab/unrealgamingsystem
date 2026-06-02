<?php
include 'assets/includes/config.php';

$servername = $dbGM['host'];
$username = $dbGM['user'];
$password = $dbGM['pass'];
$dbname = 'arcade_games2';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT image FROM gm_games";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $data = array();
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $json_data = json_encode($data);
} else {
    $json_data = json_encode(array());
}

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="game_data_image.json"');
echo $json_data;

$conn->close();

?>