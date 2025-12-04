<?php
session_start();
require_once 'database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in. <a href='login.php'>Login here</a>");
}

// Check if user has subscription
if (!isset($_SESSION['subscription']) || $_SESSION['subscription'] === 'free') {
    die("Error: Subscription required. <a href='pricing.php'>Upgrade here</a>");
}

// Get course ID
$course_id = $_GET['course_id'] ?? $_POST['course_id'] ?? null;
if (!$course_id) {
    die("Error: No course ID provided. <a href='courses.php'>Go to courses</a>");
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

try {
    $pdo = getDbConnection();
    
    // Create table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS course_enrollments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            course_id INT NOT NULL,
            status VARCHAR(50) DEFAULT 'in_progress',
            progress_percentage INT DEFAULT 0,
            enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            time_spent INT DEFAULT 0,
            UNIQUE KEY unique_enrollment (user_id, course_id)
        )
    ");
    
    // Get course info
    $stmt = $pdo->prepare("SELECT * FROM course_list WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$course) {
        die("Error: Course not found. <a href='courses.php'>Go to courses</a>");
    }
    
    // Check if already enrolled
    $stmt = $pdo->prepare("SELECT id FROM course_enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user_id, $course_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $message = "You are already enrolled in this course!";
        echo "<script>setTimeout(() => window.location.href = 'my-courses.php', 2000);</script>";
    }
    
    // Handle enrollment
    if ($_POST && isset($_POST['enroll']) && !$existing) {
        $stmt = $pdo->prepare("
            INSERT INTO course_enrollments (user_id, course_id, status, progress_percentage, enrolled_at, last_accessed, time_spent) 
            VALUES (?, ?, 'in_progress', 0, NOW(), NOW(), 0)
        ");
        
        if ($stmt->execute([$user_id, $course_id])) {
            $message = "✅ Successfully enrolled! Redirecting to your courses...";
            echo "<script>setTimeout(() => window.location.href = 'my-courses.php?message=enrolled', 3000);</script>";
        } else {
            $error = "❌ Failed to enroll. Database error.";
        }
    }
    
} catch (Exception $e) {
    $error = "❌ Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll in Course - EduLearn Academy</title>
    <link href="Web3.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin-top: 80px;
            background: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
        }

        .enrollment-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .enrollment-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .enrollment-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .course-info {
            padding: 2rem;
        }

        .course-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1rem;
        }

        .course-meta {
            display: flex;
            gap: 2rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
        }

        .course-description {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .user-info {
            background: #f0f9ff;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }

        .subscription-badge {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .message {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
        }

        .success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .enrollment-actions {
            padding: 2rem;
            border-top: 1px solid #f3f4f6;
        }

        .btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
        }

        .btn-outline {
            background: transparent;
            color: #6b7280;
            border: 2px solid #e5e7eb;
        }

        .btn-outline:hover {
            border-color: #10b981;
            color: #10b981;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .debug-info {
            background: #f9fafb;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            font-size: 0.9rem;
            border: 1px solid #e5e7eb;
        }

        .debug-info h4 {
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .course-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="enrollment-card">
            <div class="enrollment-header">
                <h1><i class="fas fa-graduation-cap"></i> Course Enrollment</h1>
                <p>Join this course and start learning today!</p>
            </div>

            <div class="course-info">
               

                <div class="subscription-badge">
                    <i class="fas fa-crown"></i>
                    <?php echo ucfirst($_SESSION['subscription']); ?> Member Access
                </div>
                
                <?php if ($message): ?>
                    <div class="message success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="message error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($course)): ?>
                    <h2 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h2>
                    
                    <div class="course-meta">
                        <div class="meta-item">
                            <i class="fas fa-tag"></i>
                            <span><?php echo htmlspecialchars($course['category'] ?? 'General'); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-signal"></i>
                            <span><?php echo htmlspecialchars($course['level'] ?? 'Beginner'); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-user"></i>
                            <span><?php echo htmlspecialchars($course['instructor'] ?? 'Expert'); ?></span>
                        </div>
                    </div>
                    
                    <?php if ($course['description']): ?>
                        <p class="course-description">
                            <?php echo htmlspecialchars($course['description']); ?>
                        </p>
                    <?php endif; ?>
                    
                <?php endif; ?>
            </div>

            <div class="enrollment-actions">
                <?php if (!$existing && !$message): ?>
                    <form method="POST">
                        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                        <button type="submit" name="enroll" class="btn btn-primary">
                            <i class="fas fa-graduation-cap"></i> Enroll in This Course
                        </button>
                    </form>
                <?php endif; ?>
                
                <a href="courses.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Courses
                </a>
                <a href="my-courses.php" class="btn btn-outline">
                    <i class="fas fa-book"></i> My Courses
                </a>
            </div>
        </div>
    </div>
</body>
</html>