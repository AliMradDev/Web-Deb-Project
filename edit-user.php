<?php
require_once 'database.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

$pdo = getDbConnection();

$error = '';
$success = '';
$user = null;

// Fetch all users for dropdown
$allUsers = $pdo->query("SELECT id, username FROM users_acc ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);

// If a user is selected from dropdown or via GET param 'user_id'
$selectedUserId = $_POST['selected_user_id'] ?? $_GET['user_id'] ?? null;

if ($selectedUserId) {
    // Fetch selected user details
    $stmt = $pdo->prepare("SELECT id, username, email, role FROM users_acc WHERE id = ?");
    $stmt->execute([$selectedUserId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        $error = "User not found.";
    }
}

// Handle form submission for updating user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email)) {
        $error = "Username and email cannot be empty.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!in_array($role, ['student', 'teacher', 'admin'])) {
        $error = "Invalid role selected.";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Update user info
        $stmt = $pdo->prepare("UPDATE users_acc SET username = ?, email = ?, role = ? WHERE id = ?");
        $stmt->execute([$username, $email, $role, $user_id]);

        // Update password if provided
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users_acc SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $user_id]);
        }

        $success = "User updated successfully.";

        // Refresh user data
        $stmt = $pdo->prepare("SELECT id, username, email, role FROM users_acc WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Edit User</title>
<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; }
    form { background: white; padding: 20px; border-radius: 8px; max-width: 400px; margin-top: 20px; }
    label { display: block; margin-top: 10px; }
    input, select { width: 100%; padding: 8px; margin-top: 5px; }
    button { margin-top: 15px; padding: 10px 15px; background: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer; }
    .error { color: red; margin-top: 10px; }
    .success { color: green; margin-top: 10px; }
</style>
</head>
<body>
<h1>Edit User</h1>

<!-- User select form -->
<form method="POST">
    <label for="selected_user_id">Select User to Edit:</label>
    <select name="selected_user_id" id="selected_user_id" onchange="this.form.submit()" required>
        <option value="">-- Select User --</option>
        <?php foreach ($allUsers as $u): ?>
            <option value="<?= $u['id'] ?>" <?= ($selectedUserId == $u['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($u['username']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <noscript><button type="submit">Load User</button></noscript>
</form>

<?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($user): ?>
    <!-- Edit form -->
    <form method="POST">
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
        <label for="username">Username</label>
        <input id="username" name="username" type="text" required value="<?= htmlspecialchars($user['username']) ?>">

        <label for="email">Email</label>
        <input id="email" name="email" type="email" required value="<?= htmlspecialchars($user['email']) ?>">

        <label for="role">Role</label>
        <select id="role" name="role" required>
            <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
            <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>

        <label for="new_password">New Password (leave blank to keep current)</label>
        <input id="new_password" name="new_password" type="password">

        <label for="confirm_password">Confirm New Password</label>
        <input id="confirm_password" name="confirm_password" type="password">

        <button type="submit" name="update_user">Save Changes</button>
    </form>
<?php endif; ?>

</body>
</html>
