<?php
session_start();

// Protect admin dashboard from unauthorized access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php"); // Redirect non-admins to homepage or login
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Dashboard</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f0f2f5;
        margin: 0; padding: 40px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    h1 {
        margin-bottom: 30px;
        color: #1f2937;
    }
    .dashboard-links {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        max-width: 600px;
        width: 100%;
        justify-content: center;
    }
    .dashboard-links a {
        flex: 1 1 150px;
        text-align: center;
        background-color: #3b82f6;
        color: white;
        text-decoration: none;
        padding: 15px 0;
        border-radius: 8px;
        font-weight: 600;
        box-shadow: 0 4px 10px rgb(59 130 246 / 0.3);
        transition: background-color 0.3s ease;
    }
    .dashboard-links a:hover {
        background-color: #2563eb;
    }
</style>
</head>
<body>
    <h1>Admin Dashboard</h1>
    <div class="dashboard-links">
        <a href="admin_users.php">Manage Users</a>
        <a href="admin_courses.php">Manage Courses</a>

    </div>
</body>
</html>
