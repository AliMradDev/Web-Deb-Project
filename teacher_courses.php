<?php
require_once 'database.php';
session_start();

// Admin check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    die("Access denied.");
}

$pdo = getDbConnection();

// Handle Add/Edit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST["title"];
    $description = $_POST["description"];
    $category_id = $_POST["category_id"] ?? null;
    $curriculum = $_POST["curriculum"] ?? '';
    $overview = $_POST["overview"] ?? '';

    if (isset($_POST['course_id']) && !empty($_POST['course_id'])) {
        // Update
        $stmt = $pdo->prepare("UPDATE course_list SET title=?, description=?, category_id=?, curriculum=?, overview=? WHERE id=?");
        $stmt->execute([$title, $description, $category_id, $curriculum, $overview, $_POST['course_id']]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO course_list (title, description, category_id, curriculum, overview) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $category_id, $curriculum, $overview]);
    }
    header("Location: teacher_courses.php");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM course_list WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: admin_courses.php");
    exit();
}

// Fetch categories for dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch courses with category name using JOIN
$courses = $pdo->query("
    SELECT c.*, cat.name AS category_name 
    FROM course_list c 
    LEFT JOIN categories cat ON c.category_id = cat.id 
    ORDER BY c.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Edit mode
$editCourse = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM course_list WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editCourse = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage Courses - Admin</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet" />
<style>
    body {
        background: #f8fafc;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 2rem;
    }
    .admin-container {
        max-width: 900px;
        margin: auto;
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }
    h2 {
        color: #1f2937;
    }
    form {
        display: grid;
        gap: 1rem;
        margin-bottom: 2rem;
    }
    input, textarea, select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 1rem;
        resize: vertical;
    }
    button {
        padding: 0.75rem 1.5rem;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1rem;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        text-align: left;
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: top;
    }
    th {
        background: #f9fafb;
    }
    .actions a {
        margin-right: 0.5rem;
        text-decoration: none;
        color: #3b82f6;
    }
    .actions a:hover {
        text-decoration: underline;
    }
    /* Optional: make textarea taller */
    textarea {
        min-height: 100px;
    }
</style>
</head>
<body>
<div class="admin-container">
    <h2><?php echo $editCourse ? "Edit Course" : "Add New Course"; ?></h2>
    <form method="POST" autocomplete="off">
        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($editCourse['id'] ?? ''); ?>">

        <input type="text" name="title" required placeholder="Course Title" value="<?php echo htmlspecialchars($editCourse['title'] ?? ''); ?>">

        <select name="category_id" required>
            <option value="">Select Category</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>"
                    <?php if (isset($editCourse['category_id']) && $editCourse['category_id'] == $cat['id']) echo "selected"; ?>>
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <textarea name="overview" placeholder="Course Overview"><?php echo htmlspecialchars($editCourse['overview'] ?? ''); ?></textarea>

        <textarea name="curriculum" placeholder="Course Curriculum"><?php echo htmlspecialchars($editCourse['curriculum'] ?? ''); ?></textarea>

        <textarea name="description" placeholder="Description"><?php echo htmlspecialchars($editCourse['description'] ?? ''); ?></textarea>

        <button type="submit"><?php echo $editCourse ? "Update" : "Add"; ?> Course</button>
    </form>

    <h3>All Courses</h3>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Overview</th>
                <th>Curriculum</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courses as $course): ?>
            <tr>
                <td><?php echo htmlspecialchars($course['title']); ?></td>
                <td><?php echo htmlspecialchars($course['category_name'] ?? 'Uncategorized'); ?></td>
                <td><?php echo nl2br(htmlspecialchars($course['overview'] ?? '')); ?></td>
                <td><?php echo nl2br(htmlspecialchars($course['curriculum'] ?? '')); ?></td>
                <td><?php echo nl2br(htmlspecialchars($course['description'] ?? '')); ?></td>
                <td class="actions">
                    <a href="?edit=<?php echo $course['id']; ?>"><i class="fas fa-edit"></i> Edit</a>
                    <a href="?delete=<?php echo $course['id']; ?>" onclick="return confirm('Are you sure you want to delete this course?')"><i class="fas fa-trash"></i> Delete</a>
                    <a href="admin_videos.php?course_id=<?php echo $course['id']; ?>"><i class="fas fa-video"></i> Videos</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
