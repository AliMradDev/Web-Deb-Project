<?php
session_start();
require_once 'database.php';

// Enable error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Simple Retake Debug</h1>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<div style='color: red;'>❌ NOT LOGGED IN</div>";
    exit();
}

$user_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'] ?? $_POST['course_id'] ?? 4;

echo "<p><strong>User ID:</strong> $user_id</p>";
echo "<p><strong>Course ID:</strong> $course_id</p>";
echo "<p><strong>Request Method:</strong> " . $_SERVER['REQUEST_METHOD'] . "</p>";

// Debug $_POST data
echo "<h3>📊 POST Data:</h3>";
echo "<pre>";
var_dump($_POST);
echo "</pre>";

// Debug $_GET data
echo "<h3>📊 GET Data:</h3>";
echo "<pre>";
var_dump($_GET);
echo "</pre>";

// Check database connection
try {
    $pdo = getDbConnection();
    echo "<div style='color: green;'>✅ Database connection OK</div>";
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Database error: " . $e->getMessage() . "</div>";
    exit();
}

// Check enrollment exists
try {
    $stmt = $pdo->prepare("SELECT * FROM course_enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user_id, $course_id]);
    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($enrollment) {
        echo "<div style='color: green;'>✅ Enrollment found</div>";
        echo "<p>Status: " . $enrollment['status'] . ", Progress: " . $enrollment['progress_percentage'] . "%, Exam: " . ($enrollment['exam_score'] ?? 'NULL') . "</p>";
    } else {
        echo "<div style='color: red;'>❌ No enrollment found</div>";
        exit();
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Enrollment check error: " . $e->getMessage() . "</div>";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>🔄 Processing Form Submission...</h3>";
    
    // Check if confirm_retake is set
    if (isset($_POST['confirm_retake'])) {
        echo "<div style='color: blue;'>📝 confirm_retake detected</div>";
        
        try {
            echo "<div style='color: blue;'>🔄 Starting transaction...</div>";
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                UPDATE course_enrollments 
                SET progress_percentage = 0,
                    status = 'in_progress',
                    exam_score = NULL,
                    exam_status = NULL,
                    completion_date = NULL,
                    time_spent = 0,
                    last_accessed = NOW()
                WHERE user_id = ? AND course_id = ? 5
            ");
            
            echo "<div style='color: blue;'>📤 Executing update...</div>";
            $result = $stmt->execute([$user_id, $course_id]);
            $rowsAffected = $stmt->rowCount();
            
            echo "<div style='color: blue;'>📊 Rows affected: $rowsAffected</div>";
            
            if ($result && $rowsAffected > 0) {
                $pdo->commit();
                echo "<div style='color: green;'>✅ SUCCESS! Reset completed</div>";
                echo "<div style='color: green;'>🔄 Redirecting in 3 seconds...</div>";
                echo "<script>setTimeout(() => window.location.href = 'my-courses.php', 3000);</script>";
            } else {
                $pdo->rollback();
                echo "<div style='color: red;'>❌ No rows updated</div>";
            }
            
        } catch (PDOException $e) {
            $pdo->rollback();
            echo "<div style='color: red;'>❌ Database error: " . $e->getMessage() . "</div>";
        }
        
    } else {
        echo "<div style='color: red;'>❌ confirm_retake not found in POST data</div>";
    }
    
} else {
    echo "<h3>📝 Showing Form</h3>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple Retake Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-container { background: #f9f9f9; padding: 20px; border: 2px solid #007cba; margin: 20px 0; }
        .btn { padding: 15px 20px; background: #f59e0b; color: white; border: none; font-size: 16px; cursor: pointer; }
        .btn:hover { background: #d97706; }
        .checkbox-container { margin: 20px 0; }
        input[type="checkbox"] { width: 20px; height: 20px; margin-right: 10px; }
    </style>
</head>
<body>

<div class="form-container">
    <h3>🔄 Simple Retake Form</h3>
    <p><strong>Course:</strong> <?php echo htmlspecialchars($enrollment['course_title'] ?? 'Unknown'); ?></p>
    
    <form method="POST" id="simpleRetakeForm">
        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_id); ?>">
        
        <div class="checkbox-container">
            <label>
                <input type="checkbox" id="confirmCheckbox" required>
                I confirm I want to reset this course completely
            </label>
        </div>
        
        <button type="submit" name="confirm_retake" class="btn" id="submitBtn" disabled>
            🔄 Reset Course Now
        </button>
    </form>
</div>

<div style="margin-top: 30px;">
    <a href="my-courses.php" style="padding: 10px 15px; background: #6b7280; color: white; text-decoration: none;">
        ← Back to My Courses
    </a>
</div>

<script>
// Enable/disable submit button based on checkbox
const checkbox = document.getElementById('confirmCheckbox');
const submitBtn = document.getElementById('submitBtn');

checkbox.addEventListener('change', function() {
    submitBtn.disabled = !this.checked;
    submitBtn.style.opacity = this.checked ? '1' : '0.5';
});

// Debug form submission
document.getElementById('simpleRetakeForm').addEventListener('submit', function(e) {
    console.log('Form submitting...');
    console.log('Checkbox checked:', checkbox.checked);
    console.log('Form data:', new FormData(this));
    
    if (!checkbox.checked) {
        e.preventDefault();
        alert('Please check the confirmation checkbox');
        return;
    }
    
    // Show loading state
    submitBtn.innerHTML = '🔄 Processing...';
    submitBtn.disabled = true;
});
</script>

</body>
</html>