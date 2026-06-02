<!DOCTYPE html>
<html>
<head>
    <title>Update Total Games</title>
</head>
<body>
    <form method="post">
        <button type="submit" name="action" value="add_columns">Add Columns</button>
        <button type="submit" name="action" value="update_categories">Update Categories</button>
        <button type="submit" name="action" value="update_tags">Update Tags</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
        echo "Connected successfully<br>";

        $action = $_POST['action'];

        if ($action == 'add_columns') {
            // Check and add total_games column to gm_tags if not exists
            $result = $conn->query("SHOW COLUMNS FROM gm_tags LIKE 'total_games'");
            if ($result->num_rows == 0) {
                if ($conn->query("ALTER TABLE gm_tags ADD total_games INT DEFAULT 0") === TRUE) {
                    echo "Column total_games added to gm_tags<br>";
                } else {
                    echo "Error adding column to gm_tags: " . $conn->error . "<br>";
                }
            } else {
                echo "Column total_games already exists in gm_tags<br>";
            }

            // Check and add total_games column to gm_categories if not exists
            $result = $conn->query("SHOW COLUMNS FROM gm_categories LIKE 'total_games'");
            if ($result->num_rows == 0) {
                if ($conn->query("ALTER TABLE gm_categories ADD total_games INT DEFAULT 0") === TRUE) {
                    echo "Column total_games added to gm_categories<br>";
                } else {
                    echo "Error adding column to gm_categories: " . $conn->error . "<br>";
                }
            } else {
                echo "Column total_games already exists in gm_categories<br>";
            }
        } elseif ($action == 'update_categories') {
            // Update total_games for gm_categories where total_games is zero
            if ($conn->query("
                UPDATE gm_categories gc
                LEFT JOIN (
                    SELECT category, COUNT(*) as total
                    FROM gm_games
                    GROUP BY category
                ) gg ON gc.id = gg.category
                SET gc.total_games = IFNULL(gg.total, 0)
                WHERE gc.total_games = 0
            ") === TRUE) {
                echo "Updated total_games for gm_categories<br>";
            } else {
                echo "Error updating total_games for gm_categories: " . $conn->error . "<br>";
            }
        } elseif ($action == 'update_tags') {
            // Update total_games for gm_tags where total_games is zero
            $tags = $conn->query("SELECT id FROM gm_tags WHERE total_games = 0");
            while ($tag = $tags->fetch_assoc()) {
                $tag_id = $tag['id'];
                $result = $conn->query("
                    SELECT COUNT(*) as total
                    FROM gm_games
                    WHERE JSON_CONTAINS(tags_ids, '\"$tag_id\"')
                ");
                if ($result) {
                    $total = $result->fetch_assoc()['total'];
                    if ($conn->query("UPDATE gm_tags SET total_games = $total WHERE id = $tag_id") === TRUE) {
                        echo "Updated total_games for tag ID $tag_id<br>";
                    } else {
                        echo "Error updating total_games for tag ID $tag_id: " . $conn->error . "<br>";
                    }
                } else {
                    echo "Error fetching total games for tag ID $tag_id: " . $conn->error . "<br>";
                }
            }
        }

        $conn->close();
        echo "Connection closed<br>";
    }
    ?>
</body>
</html>