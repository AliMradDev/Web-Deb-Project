<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    die("Please log in first");
}

$user_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'] ?? 4; // Default to course 4

echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
    .error { color: red; background: #ffe6e6; padding: 10px; }
    .success { color: green; background: #e6ffe6; padding: 10px; }
    .warning { color: orange; background: #fff3cd; padding: 10px; }
    .btn { padding: 10px 15px; margin: 5px; background: #007cba; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
    .btn-warning { background: #f59e0b; }
    .btn-danger { background: #dc3545; }
    pre { background: #f5f5f5; padding: 10px; border: 1px solid #ddd; }
    .test-form { background: #f9f9f9; padding: 20px; border: 2px solid #007cba; margin: 10px 0; }
</style>";

echo "<h1>🔧 Retake Button Debug Test</h1>";
echo "<p><strong>User ID:</strong> $user_id | <strong>Course ID:</strong> $course_id</p>";

try {
    $pdo = getDbConnection();
    
    // Check current enrollment status
    echo "<div class='section'>";
    echo "<h2>📊 Current Enrollment Status</h2>";
    
    $stmt = $pdo->prepare("
        SELECT e.*, cl.title as course_title
        FROM course_enrollments e 
        LEFT JOIN course_list cl ON e.course_id = cl.id 
        WHERE e.user_id = ? AND e.course_id = ?
    ");
    $stmt->execute([$user_id, $course_id]);
    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($enrollment) {
        echo "<div class='success'>✅ Enrollment found!</div>";
        echo "<pre>";
        foreach ($enrollment as $key => $value) {
            echo sprintf("%-20s: %s\n", $key, $value ?? 'NULL');
        }
        echo "</pre>";
    } else {
        echo "<div class='error'>❌ No enrollment found!</div>";
    }
    echo "</div>";
    
    // Test the actual retake process
    if ($_POST && isset($_POST['test_retake'])) {
        echo "<div class='section'>";
        echo "<h2>🔄 Processing Retake...</h2>";
        
        try {
            $pdo->beginTransaction();
            
            echo "<div class='warning'>🔄 Starting database update...</div>";
            
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
            $rowsAffected = $stmt->rowCount();
            
            echo "<div class='warning'>📊 Rows affected: $rowsAffected</div>";
            
            if ($result && $rowsAffected > 0) {
                // Verify the update
                $verify_stmt = $pdo->prepare("
                    SELECT progress_percentage, status, exam_score, exam_status, completion_date
                    FROM course_enrollments 
                    WHERE user_id = ? AND course_id = ?
                ");
                $verify_stmt->execute([$user_id, $course_id]);
                $updated_data = $verify_stmt->fetch(PDO::FETCH_ASSOC);
                
                echo "<div class='warning'>🔍 Verification data:</div>";
                echo "<pre>";
                foreach ($updated_data as $key => $value) {
                    echo sprintf("%-20s: %s\n", $key, $value ?? 'NULL');
                }
                echo "</pre>";
                
                if ($updated_data['progress_percentage'] == 0 && 
                    $updated_data['status'] == 'in_progress' && 
                    $updated_data['exam_score'] === null) {
                    
                    $pdo->commit();
                    echo "<div class='success'>✅ SUCCESS! Reset completed successfully!</div>";
                    echo "<script>setTimeout(() => window.location.reload(), 3000);</script>";
                    
                } else {
                    $pdo->rollback();
                    echo "<div class='error'>❌ VERIFICATION FAILED! Data was not reset properly.</div>";
                    echo "<div class='error'>Expected: progress=0, status=in_progress, exam_score=NULL</div>";
                }
                
            } else {
                $pdo->rollback();
                echo "<div class='error'>❌ UPDATE FAILED! No rows were affected.</div>";
                echo "<div class='error'>This usually means the WHERE clause didn't match any records.</div>";
            }
            
        } catch (PDOException $e) {
            $pdo->rollback();
            echo "<div class='error'>❌ DATABASE ERROR: " . $e->getMessage() . "</div>";
        }
        echo "</div>";
    }
    
    // Restore original data button
    if ($_POST && isset($_POST['restore_data'])) {
        echo "<div class='section'>";
        echo "<h2>🔄 Restoring Original Data...</h2>";
        
        try {
            $stmt = $pdo->prepare("
                UPDATE course_enrollments 
                SET progress_percentage = 100,
                    status = 'completed',
                    exam_score = 80.00,
                    exam_status = 'passed',
                    completion_date = '2025-07-26 14:51:06',
                    time_spent = 5,
                    last_accessed = NOW()
                WHERE user_id = ? AND course_id = ?
            ");
            
            $result = $stmt->execute([$user_id, $course_id]);
            
            if ($result) {
                echo "<div class='success'>✅ Original data restored!</div>";
                echo "<script>setTimeout(() => window.location.reload(), 2000);</script>";
            } else {
                echo "<div class='error'>❌ Failed to restore data</div>";
            }
            
        } catch (PDOException $e) {
            echo "<div class='error'>❌ RESTORE ERROR: " . $e->getMessage() . "</div>";
        }
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>💥 SYSTEM ERROR: " . $e->getMessage() . "</div>";
}
?>

<!-- Test Forms -->
<div class="section">
    <h2>🧪 Manual Test Forms</h2>
    
    <div class="test-form">
        <h3>🔄 Test Retake Process</h3>
        <p>This will manually execute the exact same reset logic as the retake button:</p>
        <form method="POST">
            <button type="submit" name="test_retake" class="btn btn-warning" onclick="return confirm('Test the retake reset process?')">
                🔄 Test Retake Reset
            </button>
        </form>
    </div>
    
    <div class="test-form">
        <h3>🔙 Restore Original Data</h3>
        <p>This will restore the course to completed status with exam score:</p>
        <form method="POST">
            <button type="submit" name="restore_data" class="btn btn-danger" onclick="return confirm('Restore original completed status?')">
                🔙 Restore Original Data
            </button>
        </form>
    </div>
</div>

<div class="section">
    <h2>🔗 Navigation</h2>
    <a href="my-courses.php" class="btn">📚 My Courses</a>
    <a href="retake-course.php?course_id=<?php echo $course_id; ?>" class="btn">🔄 Actual Retake Page</a>
    <a href="debug-enrollment.php?course_id=<?php echo $course_id; ?>" class="btn">🔍 Debug Enrollment</a>
</div>