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
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        .course-box { border: 2px solid #007cba; padding: 15px; margin: 10px 0; background: #f9f9f9; }
        .error { color: red; background: #ffe6e6; padding: 10px; }
        .success { color: green; background: #e6ffe6; padding: 10px; }
        .warning { color: orange; background: #fff3cd; padding: 10px; }
        .btn { padding: 8px 12px; margin: 3px; background: #007cba; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-warning { background: #f59e0b; }
        .btn-success { background: #10b981; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>";
    
    echo "<h1>🔍 My Courses Debug</h1>";
    echo "<p><strong>User ID:</strong> $user_id</p>";
    
    // Get the exact same query as my-courses.php
    $query = "
        SELECT 
            e.*,
            cl.title as course_title,
            cl.description as course_description,
            CASE 
                WHEN e.status = 'in_progress' AND e.progress_percentage < 100 THEN 'In Progress'
                WHEN e.status = 'completed' OR e.progress_percentage = 100 THEN 'Completed'
                WHEN e.status = 'failed' THEN 'Failed'
                WHEN e.status = 'dropped' THEN 'Dropped'
                ELSE 'Not Started'
            END as display_status,
            CASE 
                WHEN e.exam_score IS NOT NULL AND e.exam_score != '' THEN CONCAT(e.exam_score, '%')
                ELSE 'Not Taken'
            END as exam_display
        FROM course_enrollments e
        LEFT JOIN course_list cl ON e.course_id = cl.id
        WHERE e.user_id = ?
        ORDER BY e.enrolled_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $enrolled_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='section'>";
    echo "<h2>📋 Query Results (Same as My Courses)</h2>";
    
    if (empty($enrolled_courses)) {
        echo "<div class='error'>❌ No courses found!</div>";
    } else {
        foreach ($enrolled_courses as $course) {
            echo "<div class='course-box'>";
            echo "<h3>🎓 " . htmlspecialchars($course['course_title']) . "</h3>";
            
            // Show all data
            echo "<pre>";
            foreach ($course as $key => $value) {
                echo sprintf("%-20s: %s\n", $key, $value ?? 'NULL');
            }
            echo "</pre>";
            
            // Test the button logic
            echo "<h4>🔧 Button Logic Test:</h4>";
            
            $display_status = $course['display_status'];
            echo "<p><strong>display_status:</strong> '$display_status'</p>";
            
            if ($display_status === 'In Progress' || $display_status === 'Not Started') {
                echo "<div class='success'>✅ Would show: Continue Learning + Details</div>";
                
            } elseif ($display_status === 'Completed') {
                echo "<div class='warning'>🔄 COMPLETED STATUS - Testing exam logic...</div>";
                
                // Test exam logic
                $exam_taken = ($course['exam_score'] !== null && $course['exam_score'] !== '');
                echo "<p><strong>exam_taken logic:</strong> (\$course['exam_score'] !== null && \$course['exam_score'] !== '')</p>";
                echo "<p><strong>exam_score value:</strong> '" . ($course['exam_score'] ?? 'NULL') . "'</p>";
                echo "<p><strong>exam_taken result:</strong> " . ($exam_taken ? 'TRUE' : 'FALSE') . "</p>";
                
                if (!$exam_taken) {
                    echo "<div class='success'>✅ Would show: Take Final Exam button</div>";
                } elseif ($course['exam_status'] === 'passed') {
                    echo "<div class='success'>✅ Would show: Download Certificate button</div>";
                } else {
                    echo "<div class='warning'>⚠️ Exam taken but not passed</div>";
                }
                
                echo "<div class='success'><strong>✅ WOULD SHOW RETAKE BUTTON</strong> (Always shown for completed courses)</div>";
                echo "<div class='success'>✅ Would show: Review Course button</div>";
                
            } elseif ($display_status === 'Failed') {
                echo "<div class='error'>❌ FAILED STATUS</div>";
                echo "<div class='success'>✅ Would show: Retake Course + Review Course</div>";
                
            } else {
                echo "<div class='warning'>⚠️ OTHER STATUS</div>";
                echo "<div class='success'>✅ Would show: Start Learning + Details</div>";
            }
            
            // Generate actual buttons
            echo "<h4>🎯 Actual Buttons That Should Appear:</h4>";
            echo "<div style='margin: 10px 0;'>";
            
            if ($display_status === 'Completed') {
                $exam_taken = ($course['exam_score'] !== null && $course['exam_score'] !== '');
                
                if (!$exam_taken) {
                    echo "<a href='exam.php?course_id=" . $course['course_id'] . "' class='btn'>📝 Take Final Exam</a>";
                } elseif ($course['exam_status'] === 'passed') {
                    echo "<a href='certificate.php?course_id=" . $course['course_id'] . "' class='btn btn-success'>🏆 Download Certificate</a>";
                }
                
                echo "<a href='retake-course.php?course_id=" . $course['course_id'] . "' class='btn btn-warning'>🔄 Retake Course</a>";
                echo "<a href='course-viewer.php?id=" . $course['course_id'] . "' class='btn'>👁️ Review Course</a>";
            }
            echo "</div>";
            
            echo "</div>";
        }
    }
    echo "</div>";
    
    // Test the retake URL directly
    if (!empty($enrolled_courses)) {
        $first_course = $enrolled_courses[0];
        echo "<div class='section'>";
        echo "<h2>🔗 Direct Test Links</h2>";
        echo "<p>Course ID: " . $first_course['course_id'] . " (" . $first_course['course_title'] . ")</p>";
        echo "<a href='retake-course.php?course_id=" . $first_course['course_id'] . "' class='btn btn-warning'>🔄 Test Retake Course Direct Link</a>";
        echo "<a href='my-courses.php' class='btn'>📚 Go to Actual My Courses</a>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>💥 Error: " . $e->getMessage() . "</div>";
}
?>