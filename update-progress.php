<?php
session_start();
require_once 'database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['course_id']) || !isset($input['lesson_id']) || !isset($input['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$user_id = $_SESSION['user_id'];
$course_id = (int)$input['course_id'];
$lesson_id = (int)$input['lesson_id'];
$action = $input['action'];

try {
    $pdo = getDbConnection();
    
    // Verify user is enrolled in this course
    $stmt = $pdo->prepare("SELECT * FROM course_enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user_id, $course_id]);
    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$enrollment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Enrollment not found']);
        exit();
    }
    
    if ($action === 'mark_completed') {
        // Calculate new progress based on lesson completion
        // Assuming 5 lessons total, each worth 20%
        $total_lessons = 5;
        $progress_per_lesson = 100 / $total_lessons;
        $new_progress = min(100, $lesson_id * $progress_per_lesson);
        
        // Update progress
        $status = ($new_progress >= 100) ? 'completed' : 'in_progress';
        
        $stmt = $pdo->prepare("
            UPDATE course_enrollments 
            SET progress_percentage = ?, 
                status = ?,
                last_accessed = NOW(),
                completion_date = CASE WHEN ? >= 100 THEN NOW() ELSE completion_date END
            WHERE user_id = ? AND course_id = ?
        ");
        
        $result = $stmt->execute([$new_progress, $status, $new_progress, $user_id, $course_id]);
        
        if ($result) {
            echo json_encode([
                'success' => true, 
                'new_progress' => $new_progress,
                'completed' => $new_progress >= 100,
                'status' => $status,
                'message' => 'Progress updated successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update progress']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
}
?>