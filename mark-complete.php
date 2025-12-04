<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in first']);
    exit;
}

// Get the POST data
$input = json_decode(file_get_contents('php://input'), true);
$course_id = isset($input['course_id']) ? (int)$input['course_id'] : 0;
$video_id = isset($input['video_id']) ? (int)$input['video_id'] : 0;

if ($course_id <= 0 || $video_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid course or video ID']);
    exit;
}

// Check if user is enrolled in the course
if (!isset($_SESSION['enrolled_courses']) || !in_array($course_id, $_SESSION['enrolled_courses'])) {
    echo json_encode(['success' => false, 'message' => 'Not enrolled in this course']);
    exit;
}

// Get current progress
$progress_key = 'course_' . $course_id . '_progress';
if (!isset($_SESSION[$progress_key])) {
    $_SESSION[$progress_key] = [
        'completed_videos' => [],
        'exam_taken' => false,
        'exam_score' => null,
        'certificate_earned' => false
    ];
}

$progress = $_SESSION[$progress_key];

// Check if video is already completed
if (in_array($video_id, $progress['completed_videos'])) {
    echo json_encode(['success' => false, 'message' => 'Video already marked as complete']);
    exit;
}

// Mark video as complete
$progress['completed_videos'][] = $video_id;
$progress['last_updated'] = date('Y-m-d H:i:s');

// Update session
$_SESSION[$progress_key] = $progress;

// Define course structures to validate video IDs
$course_videos = [
    1 => [1, 2, 3], // Web Development course video IDs
    2 => [1, 2, 3]  // Data Science course video IDs
];

// Validate video ID exists for this course
$valid_videos = $course_videos[$course_id] ?? [];
if (!in_array($video_id, $valid_videos)) {
    echo json_encode(['success' => false, 'message' => 'Invalid video ID for this course']);
    exit;
}

// Check if all videos are now completed
$all_completed = count($progress['completed_videos']) === count($valid_videos);

// In a real application, you would save this to a database
// Example:
/*
$pdo = new PDO('mysql:host=localhost;dbname=edulearn', $username, $password);

// Update the completed videos array
$completed_videos_json = json_encode($progress['completed_videos']);
$stmt = $pdo->prepare("UPDATE course_progress SET completed_videos = ?, last_updated = NOW() WHERE user_id = ? AND course_id = ?");
$stmt->execute([$completed_videos_json, $_SESSION['user_id'], $course_id]);

// Log the video completion
$stmt = $pdo->prepare("INSERT INTO video_completions (user_id, course_id, video_id, completed_at) VALUES (?, ?, ?, NOW())");
$stmt->execute([$_SESSION['user_id'], $course_id, $video_id]);
*/

$response = [
    'success' => true,
    'message' => 'Video marked as complete',
    'video_id' => $video_id,
    'total_completed' => count($progress['completed_videos']),
    'total_videos' => count($valid_videos),
    'all_completed' => $all_completed,
    'progress_percentage' => round((count($progress['completed_videos']) / count($valid_videos)) * 100, 2)
];

echo json_encode($response);
?>