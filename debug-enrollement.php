<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    die("Please log in first");
}

$user_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'] ?? 4;

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
        .btn { padding: 10px 15px; margin: 5px; background: #007cba; color: white; border: none; cursor: pointer; }
        .btn:hover { background: #005a87; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
    </style>";
    
    echo "<h1>🔍 Database Debug Tool</h1>";
    echo "<p><strong>User ID:</strong> $user_id | <strong>Course ID:</strong> $course_id</p>";
    
    // 1. Check course_enrollments table structure
    echo "<div class='section'>";
    echo "<h2>📋 Table Structure: course_enrollments</h2>";
    $stmt = $pdo->query("DESCRIBE course_enrollments");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . ($col['Default'] === null ? '<span class="null">NULL</span>' : $col['Default']) . "</td>";
        echo "<td>" . $col['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // 2. Check current enrollment data
    echo "<div class='section'>";
    echo "<h2>📊 Current Enrollment Data</h2>";
    
    $stmt = $pdo->prepare("
        SELECT e.*, cl.title as course_title
        FROM course_enrollments e 
        LEFT JOIN course_list cl ON e.course_id = cl.id 
        WHERE e.user_id = ? AND e.course_id = ?
    ");
    $stmt->execute([$user_id, $course_id]);
    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($enrollment) {
        echo "<table>";
        echo "<tr><th>Column</th><th>Value</th><th>Type</th><th>Is NULL?</th><th>Is Empty?</th></tr>";
        foreach ($enrollment as $key => $value) {
            $isNull = $value === null ? 'YES' : 'NO';
            $isEmpty = empty($value) ? 'YES' : 'NO';
            $type = gettype($value);
            $displayValue = $value === null ? '<span class="null">NULL</span>' : htmlspecialchars($value);
            
            echo "<tr>";
            echo "<td><strong>$key</strong></td>";
            echo "<td>$displayValue</td>";
            echo "<td>$type</td>";
            echo "<td>$isNull</td>";
            echo "<td>$isEmpty</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 3. Test the conditions
        echo "<h3>🧪 Condition Tests</h3>";
        echo "<ul>";
        echo "<li><strong>exam_score === null:</strong> " . ($enrollment['exam_score'] === null ? '✅ TRUE' : '❌ FALSE') . "</li>";
        echo "<li><strong>exam_score !== null:</strong> " . ($enrollment['exam_score'] !== null ? '✅ TRUE' : '❌ FALSE') . "</li>";
        echo "<li><strong>empty(exam_score):</strong> " . (empty($enrollment['exam_score']) ? '✅ TRUE' : '❌ FALSE') . "</li>";
        echo "<li><strong>!empty(exam_score):</strong> " . (!empty($enrollment['exam_score']) ? '✅ TRUE' : '❌ FALSE') . "</li>";
        echo "<li><strong>exam_status === null:</strong> " . ($enrollment['exam_status'] === null ? '✅ TRUE' : '❌ FALSE') . "</li>";
        echo "<li><strong>progress_percentage == 0:</strong> " . ($enrollment['progress_percentage'] == 0 ? '✅ TRUE' : '❌ FALSE') . "</li>";
        echo "</ul>";
        
        // 4. Show how my-courses.php would interpret this data
        echo "<h3>🎯 How my-courses.php interprets this:</h3>";
        $exam_already_taken = ($enrollment['exam_score'] !== null && $enrollment['exam_score'] !== '');
        $display_status = match($enrollment['status']) {
            'completed' => 'Completed',
            'failed' => 'Failed', 
            'in_progress' => 'In Progress',
            default => 'Not Started'
        };
        
        echo "<ul>";
        echo "<li><strong>exam_already_taken (FIXED logic):</strong> " . ($exam_already_taken ? '❌ TRUE (will show "already taken")' : '✅ FALSE (can take exam)') . "</li>";
        echo "<li><strong>display_status:</strong> $display_status</li>";
        echo "<li><strong>Can show retake button:</strong> " . (in_array($display_status, ['Completed', 'Failed']) ? '✅ YES' : '❌ NO') . "</li>";
        echo "</ul>";
        
    } else {
        echo "<div class='error'>❌ No enrollment found for this user/course combination!</div>";
    }
    echo "</div>";
    
    // 5. Check for multiple enrollments
    echo "<div class='section'>";
    echo "<h2>🔍 Check for Multiple Enrollments</h2>";
    $stmt = $pdo->prepare("SELECT * FROM course_enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user_id, $course_id]);
    $all_enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Found " . count($all_enrollments) . " enrollment record(s)</strong></p>";
    if (count($all_enrollments) > 1) {
        echo "<div class='error'>⚠️ WARNING: Multiple enrollment records found! This could cause issues.</div>";
    }
    echo "</div>";
    
    // 6. Manual reset form
    echo "<div class='section'>";
    echo "<h2>🔧 Manual Reset Tools</h2>";
    
    if ($_POST && isset($_POST['reset_action'])) {
        try {
            $pdo->beginTransaction();
            
            if ($_POST['reset_action'] === 'soft_reset') {
                $stmt = $pdo->prepare("
                    UPDATE course_enrollments 
                    SET progress_percentage = 0,
                        status = 'in_progress',
                        exam_score = NULL,
                        exam_status = NULL,
                        completion_date = NULL,
                        time_spent = 0,
                        last_accessed = NOW()
                    WHERE user_id = ? AND course_id = ?
                ");
                $result = $stmt->execute([$user_id, $course_id]);
                
            } elseif ($_POST['reset_action'] === 'hard_reset') {
                // Delete and recreate
                $stmt = $pdo->prepare("DELETE FROM course_enrollments WHERE user_id = ? AND course_id = ?");
                $stmt->execute([$user_id, $course_id]);
                
                $stmt = $pdo->prepare("
                    INSERT INTO course_enrollments 
                    (user_id, course_id, progress_percentage, status, enrolled_at, last_accessed) 
                    VALUES (?, ?, 0, 'in_progress', NOW(), NOW())
                ");
                $result = $stmt->execute([$user_id, $course_id]);
            }
            
            if ($result) {
                $pdo->commit();
                echo "<div class='success'>✅ Reset successful! Rows affected: " . $stmt->rowCount() . "</div>";
                echo "<script>setTimeout(() => window.location.reload(), 2000);</script>";
            } else {
                $pdo->rollback();
                echo "<div class='error'>❌ Reset failed - no rows affected</div>";
            }
            
        } catch (PDOException $e) {
            $pdo->rollback();
            echo "<div class='error'>❌ Reset failed: " . $e->getMessage() . "</div>";
        }
    }
    
    echo "<form method='POST' style='margin: 10px 0;'>";
    echo "<input type='hidden' name='course_id' value='$course_id'>";
    echo "<button type='submit' name='reset_action' value='soft_reset' class='btn' onclick='return confirm(\"Soft reset: Update existing record?\")'>🔄 Soft Reset (Update)</button>";
    echo "<button type='submit' name='reset_action' value='hard_reset' class='btn btn-danger' onclick='return confirm(\"Hard reset: Delete and recreate record?\")'>💥 Hard Reset (Delete & Recreate)</button>";
    echo "</form>";
    echo "</div>";
    
    // 7. Quick navigation
    echo "<div class='section'>";
    echo "<h2>🔗 Quick Navigation</h2>";
    echo "<a href='my-courses.php' class='btn'>📚 My Courses</a>";
    echo "<a href='course-viewer.php?id=$course_id' class='btn'>👁️ Course Viewer</a>";
    echo "<a href='retake-course.php?course_id=$course_id' class='btn'>🔄 Retake Form</a>";
    echo "<a href='?course_id=$course_id&refresh=" . time() . "' class='btn'>🔄 Refresh Debug</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>💥 Error: " . $e->getMessage() . "</div>";
}
?>