<?php
// Path to the JSON file
$jsonFilePath = 'games-wt-video.json';

// Check if the file exists
if (!file_exists($jsonFilePath)) {
    die('File not found');
}

// Get the contents of the JSON file
$jsonData = file_get_contents($jsonFilePath);

// Decode the JSON data into a PHP array
$gamesData = json_decode($jsonData, true);

// Check if the JSON data was decoded properly
if (json_last_error() !== JSON_ERROR_NONE) {
    die('Error decoding JSON data: ' . json_last_error_msg());
}

// Display the data
header('Content-Type: application/json');
echo json_encode($gamesData, JSON_PRETTY_PRINT);
?>