<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=edulearn", "root", "");
    echo "✅ PDO connection works!";
    
    $stmt = $pdo->query("SHOW TABLES");
    echo "<h3>Your tables:</h3>";
    while ($row = $stmt->fetch()) {
        echo "- " . $row[0] . "<br>";
    }
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
?>