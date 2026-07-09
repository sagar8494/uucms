<?php
// test.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/db.php';

if (isset($pdo)) {
    echo "<h3>Database connected successfully!</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "- " . $row[0] . "<br>";
    }
} else {
    echo "PDO instance not found.";
}
?>