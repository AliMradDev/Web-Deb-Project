<?php
session_start();
require_once 'database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$course_id = $_GET['id'] ?? null;
$message = $_GET['message'] ?? '';

if (!$course_id) {
    header('Location: my-courses.php');
    exit();
}

try {
    $pdo = getDbConnection();
    
    // Get course details
    $stmt = $pdo->prepare("SELECT * FROM course_list WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$course) {
        header('Location: my-courses.php?error=course_not_found');
        exit();
    }
    
    // Check if user is enrolled
    $stmt = $pdo->prepare("
        SELECT * FROM course_enrollments 
        WHERE user_id = ? AND course_id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $course_id]);
    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$enrollment) {
        header('Location: enroll.php?course_id=' . $course_id);
        exit();
    }
    
    // Update last accessed time
    $stmt = $pdo->prepare("
        UPDATE course_enrollments 
        SET last_accessed = NOW() 
        WHERE user_id = ? AND course_id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $course_id]);
    
    // Get course lessons/videos (demo data since we don't have a lessons table)
    $lessons = [
        [
            'id' => 1,
            'title' => 'Introduction to ' . $course['title'],
            'duration' => '10:30',
            'video_url' => 'videos/lesson1.mp4', // Demo video
            'completed' => $enrollment['progress_percentage'] > 10
        ],
        [
            'id' => 2,
            'title' => 'Getting Started',
            'duration' => '15:45',
            'video_url' => 'videos/lesson1.mp4',
            'completed' => $enrollment['progress_percentage'] > 30
        ],
        [
            'id' => 3,
            'title' => 'Core Concepts',
            'duration' => '22:15',
            'video_url' => 'videos/lesson1.mp4',
            'completed' => $enrollment['progress_percentage'] > 60
        ],
        [
            'id' => 4,
            'title' => 'Advanced Topics',
            'duration' => '18:30',
            'video_url' => 'videos/lesson1.mp4',
            'completed' => $enrollment['progress_percentage'] > 80
        ],
        [
            'id' => 5,
            'title' => 'Final Project',
            'duration' => '25:00',
            'video_url' => 'videos/lesson1.mp4',
            'completed' => $enrollment['progress_percentage'] >= 100
        ]
    ];
    
    $current_lesson = $_GET['lesson'] ?? 1;
    $current_lesson_data = $lessons[$current_lesson - 1] ?? $lessons[0];
    
} catch (Exception $e) {
    $error_message = "Error loading course: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - Course Viewer</title>
    <link href="Web3.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin-top: 80px;
            background: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .course-viewer {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 0;
            height: calc(100vh - 80px);
        }

        /* Main Content Area */
        .main-content {
            background: #000;
            display: flex;
            flex-direction: column;
        }

        .video-container {
            flex: 1;
            position: relative;
            background: #000;
        }

        .video-player {
            width: 100%;
            height: 100%;
            border: none;
        }

        .video-controls {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .lesson-info h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.2rem;
        }

        .lesson-meta {
            opacity: 0.8;
            font-size: 0.9rem;
        }

        .progress-controls {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: #10b981;
            color: white;
        }

        .btn-primary:hover {
            background: #059669;
        }

        .btn-outline {
            background: transparent;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Sidebar */
        .sidebar {
            background: white;
            border-left: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .course-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .course-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .course-progress {
            margin-bottom: 1rem;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            transition: width 0.3s ease;
        }

        .progress-text {
            font-size: 0.9rem;
            color: #6b7280;
            display: flex;
            justify-content: space-between;
        }

        .lessons-container {
            flex: 1;
            overflow-y: auto;
        }

        .lessons-header {
            padding: 1rem 1.5rem;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            color: #1f2937;
        }

        .lessons-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .lesson-item {
            border-bottom: 1px solid #f3f4f6;
        }

        .lesson-link {
            display: block;
            padding: 1rem 1.5rem;
            text-decoration: none;
            color: #1f2937;
            transition: all 0.3s;
            position: relative;
        }

        .lesson-link:hover {
            background: #f9fafb;
        }

        .lesson-link.active {
            background: #eff6ff;
            border-right: 3px solid #3b82f6;
        }

        .lesson-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .lesson-duration {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .lesson-status {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
        }

        .status-completed {
            color: #10b981;
        }

        .status-current {
            color: #3b82f6;
        }

        .status-locked {
            color: #9ca3af;
        }

        /* Success Message */
        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 1rem 1.5rem;
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Course Actions */
        .course-actions {
            padding: 1.5rem;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .course-actions .btn {
            width: 100%;
            justify-content: center;
            margin-bottom: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .course-viewer {
                grid-template-columns: 1fr;
                grid-template-rows: 1fr auto;
            }

            .sidebar {
                max-height: 400px;
            }
        }

        @media (max-width: 768px) {
            .course-viewer {
                height: auto;
                min-height: calc(100vh - 80px);
            }

            .video-container {
                height: 250px;
            }

            .video-controls {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .progress-controls {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="course-viewer">
        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Success Message -->
            <?php if ($message === 'enrollment_success'): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <span>Successfully enrolled! Welcome to your course.</span>
                </div>
            <?php elseif ($message === 'already_enrolled'): ?>
                <div class="success-message">
                    <i class="fas fa-info-circle"></i>
                    <span>Welcome back! Continue your learning journey.</span>
                </div>
            <?php elseif ($message === 'retake_started'): ?>
                <div class="success-message">
                    <i class="fas fa-redo"></i>
                    <span>Course retake started! Your progress has been reset. Good luck!</span>
                </div>
            <?php endif; ?>

            <!-- Video Container -->
            <div class="video-container">
                <iframe 
                    src="<?php echo $current_lesson_data['video_url']; ?>" 
                    class="video-player"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen>
                </iframe>
            </div>

            <!-- Video Controls -->
            <div class="video-controls">
                <div class="lesson-info">
                    <h3><?php echo htmlspecialchars($current_lesson_data['title']); ?></h3>
                    <div class="lesson-meta">
                        Duration: <?php echo $current_lesson_data['duration']; ?> • 
                        Lesson <?php echo $current_lesson; ?> of <?php echo count($lessons); ?>
                    </div>
                </div>
                <div class="progress-controls">
                    <?php if ($current_lesson > 1): ?>
                        <a href="?id=<?php echo $course_id; ?>&lesson=<?php echo $current_lesson - 1; ?>" class="btn btn-outline">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <button onclick="markCompleted(<?php echo $current_lesson; ?>)" class="btn btn-primary">
                        <i class="fas fa-check"></i> Mark Complete
                    </button>
                    
                    <?php if ($current_lesson < count($lessons)): ?>
                        <a href="?id=<?php echo $course_id; ?>&lesson=<?php echo $current_lesson + 1; ?>" class="btn btn-outline">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Course Header -->
            <div class="course-header">
                <h2 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h2>
                <div class="course-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $enrollment['progress_percentage']; ?>%"></div>
                    </div>
                    <div class="progress-text">
                        <span>Progress</span>
                        <span><?php echo $enrollment['progress_percentage']; ?>%</span>
                    </div>
                </div>
            </div>

            <!-- Lessons List -->
            <div class="lessons-container">
                <div class="lessons-header">
                    <i class="fas fa-list"></i> Course Content
                </div>
                <ul class="lessons-list">
                    <?php foreach ($lessons as $index => $lesson): ?>
                        <li class="lesson-item">
                            <a href="?id=<?php echo $course_id; ?>&lesson=<?php echo $lesson['id']; ?>" 
                               class="lesson-link <?php echo $current_lesson == $lesson['id'] ? 'active' : ''; ?>">
                                <div class="lesson-title">
                                    <i class="fas fa-play-circle"></i>
                                    <?php echo htmlspecialchars($lesson['title']); ?>
                                </div>
                                <div class="lesson-duration"><?php echo $lesson['duration']; ?></div>
                                <div class="lesson-status">
                                    <?php if ($lesson['completed']): ?>
                                        <i class="fas fa-check-circle status-completed"></i>
                                    <?php elseif ($current_lesson == $lesson['id']): ?>
                                        <i class="fas fa-play-circle status-current"></i>
                                    <?php else: ?>
                                        <i class="fas fa-lock status-locked"></i>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Course Actions -->
            <div class="course-actions">
                <a href="my-courses.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to My Courses
                </a>
                <a href="courses.php" class="btn btn-outline">
                    <i class="fas fa-book"></i> Browse More Courses
                </a>
                <?php if ($enrollment['progress_percentage'] >= 100): ?>
                    <a href="exam.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary">
                        <i class="fas fa-clipboard-check"></i> Take Final Exam
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function markCompleted(lessonId) {
            // Simulate marking lesson as completed
            fetch('update-progress.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    course_id: <?php echo $course_id; ?>,
                    lesson_id: lessonId,
                    action: 'mark_completed'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update progress bar
                    const progressFill = document.querySelector('.progress-fill');
                    const progressText = document.querySelector('.progress-text span:last-child');
                    
                    progressFill.style.width = data.new_progress + '%';
                    progressText.textContent = data.new_progress + '%';
                    
                    // Mark lesson as completed in sidebar
                    const currentLesson = document.querySelector('.lesson-link.active .lesson-status');
                    currentLesson.innerHTML = '<i class="fas fa-check-circle status-completed"></i>';
                    
                    // Check if course is completed
                    if (data.completed) {
                        showNotification('🎉 Course completed! Ready for final exam!', 'success');
                        
                        // Show exam button in sidebar
                        setTimeout(() => {
                            if (confirm('Congratulations! You\'ve completed all lessons. Would you like to take the final exam now?')) {
                                window.location.href = 'exam.php?course_id=<?php echo $course_id; ?>';
                            }
                        }, 2000);
                    } else {
                        showNotification('Lesson marked as completed!', 'success');
                        
                        // Auto-advance to next lesson after 2 seconds
                        setTimeout(() => {
                            const nextButton = document.querySelector('.btn-outline[href*="lesson=' + (lessonId + 1) + '"]');
                            if (nextButton) {
                                nextButton.click();
                            }
                        }, 2000);
                    }
                } else {
                    showNotification('Error updating progress. Please try again.', 'error');
                }
            })
            .catch(error => {
                showNotification('Network error. Please check your connection.', 'error');
            });
        }

        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                background: ${type === 'success' ? '#10b981' : '#ef4444'};
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                font-weight: 600;
                z-index: 1000;
                transform: translateX(400px);
                transition: transform 0.3s ease;
            `;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.transform = 'translateX(400px)';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Auto-hide success messages after 5 seconds
        const successMessage = document.querySelector('.success-message');
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.opacity = '0';
                setTimeout(() => {
                    successMessage.style.display = 'none';
                }, 300);
            }, 5000);
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                const prevButton = document.querySelector('.btn-outline[href*="lesson=' + (<?php echo $current_lesson; ?> - 1) + '"]');
                if (prevButton) prevButton.click();
            } else if (e.key === 'ArrowRight') {
                const nextButton = document.querySelector('.btn-outline[href*="lesson=' + (<?php echo $current_lesson; ?> + 1) + '"]');
                if (nextButton) nextButton.click();
            } else if (e.key === ' ') {
                e.preventDefault();
                markCompleted(<?php echo $current_lesson; ?>);
            }
        });

        // Update page title with current lesson : 
//download certificate, retake exam after redoing courses, failed counter courses, dashboard, profile,admin page, teacher account, drop course
        document.title = '<?php echo htmlspecialchars($current_lesson_data['title']); ?> - <?php echo htmlspecialchars($course['title']); ?>';
    </script>
</body>
</html>