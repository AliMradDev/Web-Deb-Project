<?php
// database.php - Fixed to work with your existing tables

// Database configuration
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'edulearn';  // Change this if your database name is different

/**
 * Get PDO database connection
 */
function getDbConnection() {
    global $db_host, $db_username, $db_password, $db_name;
    
    try {
        $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, $db_username, $db_password, $options);
        return $pdo;
        
    } catch (PDOException $e) {
        die("
        <div style='font-family: Arial; max-width: 500px; margin: 50px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);'>
            <h3 style='color: #ef4444;'>Database Connection Error</h3>
            <p style='margin: 15px 0;'>Unable to connect to database.</p>
            <p style='font-size: 0.9em; color: #6b7280;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            <div style='background: #f0f9ff; padding: 10px; border-radius: 5px; margin: 15px 0;'>
                <strong>Quick fixes:</strong><br>
                1. Make sure XAMPP MySQL is running<br>
                2. Check database name is correct<br>
                3. Verify credentials in database.php
            </div>
            <div style='text-align: center;'>
                <a href='" . $_SERVER['PHP_SELF'] . "' style='background: #8b5cf6; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>Try Again</a>
            </div>
        </div>
        ");
    }
}

/**
 * Get table structure info (for debugging)
 */
function getTableInfo($table_name) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->query("DESCRIBE $table_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Check if required columns exist in users_acc table
 */
function checkUsersAccTable() {
    $pdo = getDbConnection();
    
    try {
        $stmt = $pdo->query("DESCRIBE users_acc");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $columns;
    } catch (Exception $e) {
        return false;
    }
}
?>