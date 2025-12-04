<?php
// Test what database we're actually connecting to
$conn = new mysqli("localhost", "root", "", "edulearn");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Connection Test</h2>";
echo "<p>Connected to database: " . $conn->get_server_info() . "</p>";

// Show what database we're using
$result = $conn->query("SELECT DATABASE()");
$row = $result->fetch_array();
echo "<p>Current database: " . $row[0] . "</p>";

// Show tables
echo "<h3>Tables in current database:</h3>";
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    echo "- " . $row[0] . "<br>";
}

// Test the specific table
$result = $conn->query("SELECT COUNT(*) as count FROM courses_list");
if ($result) {
    $count = $result->fetch_assoc();
    echo "<p style='color: green;'>✅ courses_list has " . $count['count'] . " records</p>";
} else {
    echo "<p style='color: red;'>❌ Error accessing courses_list: " . $conn->error . "</p>";
}
?>