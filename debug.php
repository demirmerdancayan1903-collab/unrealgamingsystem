<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>ArcadeGames Index Debug</h1>";

register_shutdown_function(function () {
    $error = error_get_last();

    if ($error !== null) {
        echo "<h2 style='color:red;'>Shutdown Fatal Error</h2>";
        echo "<pre>";
        print_r($error);
        echo "</pre>";
    }
});

try {
    require __DIR__ . "/index.php";
} catch (Throwable $e) {
    echo "<h2 style='color:red;'>Throwable Error</h2>";
    echo "<pre>";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo $e->getTraceAsString();
    echo "</pre>";
}