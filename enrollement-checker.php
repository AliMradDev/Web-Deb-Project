<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    die("Please log in first");
}

$user_id = $_SESSION['user_id'];

try {
    $pdo = getDbConnection();
    
    echo "<style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .null { color: #999; font-style: italic; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        .error { color: red; background: #ffe6e6; padding: 10px; }
        .success { color: green; background: #e6ffe6; padding: 10px; }
        .warning { color: orange; background: #fff3cd; padding: 10px; }
        .btn { padding: 10px 15px; margin: 5px; background: #007cba; color: white; border: none; cursor: pointer; }
        .btn:hover { background: #005a87; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
    </style>";
    
    echo "<h1>📚 Enrollment Checker & Fixer</h1>";
    echo "<p><strong>Current User ID:</strong> $user_id</p>";
    
    // 1. Check all enrollments for this user
    echo "<div class='section'>";
    echo "<h2>📋 All Enrollments for User ID $user_id</h2>";
    
    $stmt = $pdo->prepare("
        SELECT e.*, cl.title as course_title
        FROM course_enrollments e 
        LEFT JOIN course_list cl ON e.course_id = cl.id 
        WHERE e.user_id = ?
        ORDER BY e.course_id
    ");
    $stmt->execute([$user_id]);
    $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($enrollments)) {
        echo "<div class='warning'>⚠️ No enrollments found for this user!</div>";
    } else {
        echo "<table>";
        echo "<tr><th>Course ID</th><th>Course Title</th><th>Status</th><th>Progress</th><th>Exam Score</th><th>Exam Status</th><th>Enrolled</th></tr>";
        foreach ($enrollments as $enrollment) {
            echo "<tr>";
            echo "<td>" . $enrollment['course_id'] . "</td>";
            echo "<td>" . htmlspecialchars($enrollment['course_title'] ?? 'Unknown') . "</td>";
            echo "<td>" . $enrollment['status'] . "</td>";
            echo "<td>" . $enrollment['progress_percentage'] . "%</td>";
            echo "<td>" . ($enrollment['exam_score'] ?? '<span class="null">NULL</span>') . "</td>";
            echo "<td>" . ($enrollment['exam_status'] ?? '<span class="null">NULL</span>') . "</td>";
            echo "<td>" . $enrollment['enrolled_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
    // 2. Check available courses
    echo "<div class='section'>";
    echo "<h2>📖 Available Courses</h2>";
    
    $stmt = $pdo->query("SELECT * FROM course_list ORDER BY id");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($courses)) {
        echo "<div class='error'>❌ No courses found in course_list table!</div>";
    } else {
        echo "<table>";
        echo "<tr><th>Course ID</th><th>Title</th><th>Description</th><th>Actions</th></tr>";
        foreach ($courses as $course) {
            $isEnrolled = false;
            foreach ($enrollments as $enrollment) {
                if ($enrollment['course_id'] == $course['id']) {
                    $isEnrolled = true;
                    break;
                }
            }
            
            echo "<tr>";
            echo "<td>" . $course['id'] . "</td>";
            echo "<td>" . htmlspecialchars($course['title']) . "</td>";
            echo "<td>" . htmlspecialchars($course['description'] ?? '') . "</td>";
            echo "<td>";
            if ($isEnrolled) {
                echo "<span style='color: green;'>✅ Enrolled</span> | ";
                echo "<a href='debug-enrollment.php?course_id=" . $course['id'] . "' class='btn'>🔍 Debug</a>";
            } else {
                echo "<form method='POST' style='display: inline;'>";
                echo "<input type='hidden' name='enroll_course_id' value='" . $course['id'] . "'>";
                echo "<button type='submit' class='btn btn-success'>➕ Enroll</button>";
                echo "</form>";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
    // 3. Handle enrollment
    if ($_POST && isset($_POST['enroll_course_id'])) {
        $course_id_to_enroll = (int)$_POST['enroll_course_id'];
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO course_enrollments 
                (user_id, course_id, status, progress_percentage, enrolled_at, last_accessed) 
                VALUES (?, ?, 'in_progress', 0, NOW(), NOW())
            ");
            
            $result = $stmt->execute([$user_id, $course_id_to_enroll]);
            
            if ($result) {
                echo "<div class='success'>✅ Successfully enrolled in Course ID $course_id_to_enroll!</div>";
                echo "<script>setTimeout(() => window.location.reload(), 2000);</script>";
            } else {
                echo "<div class='error'>❌ Failed to enroll in course</div>";
            }
            
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Enrollment error: " . $e->getMessage() . "</div>";
        }
    }
    
    // 4. Handle creating test enrollment with exam data
    echo "<div class='section'>";
    echo "<h2>🧪 Create Test Enrollment (For Testing Retake)</h2>";
    echo "<p>This will create a completed course with exam score for testing the retake functionality.</p>";
    
    if ($_POST && isset($_POST['create_test_enrollment'])) {
        $test_course_id = (int)$_POST['test_course_id'];
        
        try {
            // Check if enrollment already exists
            $stmt = $pdo->prepare("SELECT id FROM course_enrollments WHERE user_id = ? AND course_id = ?");
            $stmt->execute([$user_id, $test_course_id]);
            
            if ($stmt->fetch()) {
                // Update existing enrollment
                $stmt = $pdo->prepare("
                    UPDATE course_enrollments 
                    SET status = 'completed',
                        progress_percentage = 100,
                        exam_score = 85.50,
                        exam_status = 'passed',
                        completion_date = NOW(),
                        time_spent = 5,
                        last_accessed = NOW()
                    WHERE user_id = ? AND course_id = ?
                ");
                $result = $stmt->execute([$user_id, $test_course_id]);
                $action = "Updated existing";
            } else {
                // Create new enrollment
                $stmt = $pdo->prepare("
                    INSERT INTO course_enrollments 
                    (user_id, course_id, status, progress_percentage, exam_score, exam_status, completion_date, time_spent, enrolled_at, last_accessed) 
                    VALUES (?, ?, 'completed', 100, 85.50, 'passed', NOW(), 5, NOW(), NOW())
                ");
                $result = $stmt->execute([$user_id, $test_course_id]);
                $action = "Created new";
            }
            
            if ($result) {
                echo "<div class='success'>✅ $action test enrollment for Course ID $test_course_id with exam score 85.5%!</div>";
                echo "<script>setTimeout(() => window.location.reload(), 2000);</script>";
            } else {
                echo "<div class='error'>❌ Failed to create test enrollment</div>";
            }
            
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Test enrollment error: " . $e->getMessage() . "</div>";
        }
    }
    
    echo "<form method='POST' style='margin: 10px 0;'>";
    echo "<label>Course ID for test: <input type='number' name='test_course_id' value='1' min='1' required></label><br><br>";
    echo "<button type='submit' name='create_test_enrollment' class='btn btn-success' onclick='return confirm(\"Create/update test enrollment with completed status and 85.5% exam score?\")'>🧪 Create Test Enrollment</button>";
    echo "</form>";
    echo "</div>";
    
    // 5. Quick actions
    echo "<div class='section'>";
    echo "<h2>🔗 Quick Actions</h2>";
    echo "<a href='my-courses.php' class='btn'>📚 My Courses</a>";
    if (!empty($enrollments)) {
        $first_course = $enrollments[0]['course_id'];
        echo "<a href='debug-enrollment.php?course_id=$first_course' class='btn'>🔍 Debug First Course</a>";
        echo "<a href='retake-course.php?course_id=$first_course' class='btn'>🔄 Test Retake</a>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>💥 Error: " . $e->getMessage() . "</div>";
}
?>