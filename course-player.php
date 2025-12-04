<?php
session_start();

// Check if user is enrolled
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$user_enrolled = isset($_SESSION['enrolled_courses']) && in_array($course_id, $_SESSION['enrolled_courses']);

if (!$user_enrolled) {
    header('Location: course-details.php?id=' . $course_id);
    exit;
}

// Mock course data with LOCAL videos (using same video file for all lessons)
$courses = [
    1 => [
        'id' => 1,
        'title' => 'Complete Web Development Bootcamp',
        'instructor' => 'Dr. Sarah Johnson',
        'videos' => [
            [
                'id' => 1,
                'title' => 'Introduction to Web Development',
                'duration' => '15:30',
                'video_path' => 'videos/lesson1.mp4', // Same video for all lessons
                'description' => 'Welcome to the course! In this video, we\'ll cover what you\'ll learn and set up your development environment.'
            ],
            [
                'id' => 2,
                'title' => 'HTML5 Fundamentals',
                'duration' => '28:45',
                'video_path' => 'videos/lesson1.mp4', // Same video for all lessons
                'description' => 'Learn the building blocks of the web with HTML5. We\'ll cover elements, attributes, and semantic markup.'
            ],
            [
                'id' => 3,
                'title' => 'CSS3 Styling and Layouts',
                'duration' => '32:15',
                'video_path' => 'videos/lesson1.mp4', // Same video for all lessons
                'description' => 'Master CSS3 to style your web pages. Learn about flexbox, grid, animations, and responsive design.'
            ]
        ]
    ],
    2 => [
        'id' => 2,
        'title' => 'Data Science with Python',
        'instructor' => 'Prof. Michael Chen',
        'videos' => [
            [
                'id' => 1,
                'title' => 'Python for Data Science Overview',
                'duration' => '18:20',
                'video_path' => 'videos/lesson1.mp4', // Same video for all lessons
                'description' => 'Introduction to Python and its role in data science. Setting up your Python environment.'
            ],
            [
                'id' => 2,
                'title' => 'Data Manipulation with Pandas',
                'duration' => '35:10',
                'video_path' => 'videos/lesson1.mp4', // Same video for all lessons
                'description' => 'Learn how to work with datasets using the powerful pandas library.'
            ],
            [
                'id' => 3,
                'title' => 'Data Visualization Techniques',
                'duration' => '29:45',
                'video_path' => 'videos/lesson1.mp4', // Same video for all lessons
                'description' => 'Create stunning visualizations with matplotlib and seaborn libraries.'
            ]
        ]
    ]
];

$course = $courses[$course_id] ?? $courses[1];

// Get user progress from session (in real app, use database)
$progress_key = 'course_' . $course_id . '_progress';
$user_progress = $_SESSION[$progress_key] ?? ['completed_videos' => [], 'exam_available' => false];

$current_video_id = isset($_GET['video']) ? (int)$_GET['video'] : 1;
$current_video = null;
foreach ($course['videos'] as $video) {
    if ($video['id'] == $current_video_id) {
        $current_video = $video;
        break;
    }
}
$current_video = $current_video ?? $course['videos'][0];

// Check if all videos are completed
$all_videos_completed = count($user_progress['completed_videos']) === count($course['videos']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - Course Player</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #000;
        }

        /* Header */
        .course-header {
            background: #1a1a1a;
            padding: 1rem 0;
            border-bottom: 1px solid #333;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .course-title {
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn-back {
            background: #8b5cf6;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: #7c3aed;
            transform: translateY(-1px);
        }

        .progress-indicator {
            color: #9ca3af;
            font-size: 0.9rem;
        }

        /* Main Layout */
        .player-layout {
            display: grid;
            grid-template-columns: 1fr 350px;
            height: calc(100vh - 80px);
        }

        .video-section {
            background: #000;
            display: flex;
            flex-direction: column;
        }

        .video-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
            position: relative;
        }

        /* Enhanced Video Player Styles */
        .video-player {
            width: 100%;
            max-width: 100%;
            height: 100%;
            background: #000;
            outline: none;
        }

        .video-player::-webkit-media-controls-panel {
            background-color: rgba(0, 0, 0, 0.8);
        }

        .video-player::-webkit-media-controls-play-button,
        .video-player::-webkit-media-controls-pause-button {
            background-color: #8b5cf6;
            border-radius: 50%;
        }

        .video-player::-webkit-media-controls-timeline {
            background-color: #8b5cf6;
        }

        .video-player::-webkit-media-controls-volume-slider {
            background-color: #8b5cf6;
        }

        .video-controls {
            background: #1a1a1a;
            padding: 2rem;
            border-top: 1px solid #333;
        }

        .video-info h2 {
            color: white;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .video-meta {
            color: #9ca3af;
            margin-bottom: 1rem;
            display: flex;
            gap: 2rem;
        }

        .video-description {
            color: #d1d5db;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .video-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-complete, .btn-exam {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-complete {
            background: #10b981;
            color: white;
        }

        .btn-complete:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        .btn-complete.completed {
            background: #6b7280;
            cursor: not-allowed;
        }

        .btn-exam {
            background: #f59e0b;
            color: white;
        }

        .btn-exam:hover {
            background: #d97706;
            transform: translateY(-2px);
        }

        .btn-exam:disabled {
            background: #6b7280;
            cursor: not-allowed;
            transform: none;
        }

        /* Video Error Message */
        .video-error {
            color: #ef4444;
            text-align: center;
            padding: 2rem;
            background: #1a1a1a;
            border-radius: 8px;
            margin: 2rem;
        }

        .video-error i {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        /* Sidebar */
        .course-sidebar {
            background: #f8fafc;
            border-left: 1px solid #e5e7eb;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background: white;
        }

        .sidebar-header h3 {
            color: #1f2937;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .course-progress {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .progress-bar {
            flex: 1;
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: #8b5cf6;
            transition: width 0.3s ease;
        }

        .video-list {
            padding: 1rem 0;
        }

        .video-item {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            cursor: pointer;
            transition: background 0.3s;
            border-bottom: 1px solid #f3f4f6;
            text-decoration: none;
            color: inherit;
        }

        .video-item:hover {
            background: #f3f4f6;
        }

        .video-item.active {
            background: #ede9fe;
            border-left: 4px solid #8b5cf6;
        }

        .video-item.completed {
            background: #f0fdf4;
        }

        .video-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: #6b7280;
        }

        .video-item.active .video-number {
            background: #8b5cf6;
            color: white;
        }

        .video-item.completed .video-number {
            background: #10b981;
            color: white;
        }

        .video-details {
            flex: 1;
        }

        .video-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
            font-size: 0.95rem;
        }

        .video-duration {
            color: #6b7280;
            font-size: 0.85rem;
        }

        .completion-icon {
            color: #10b981;
            font-size: 1.2rem;
        }

        /* Exam Section */
        .exam-section {
            background: white;
            margin: 1rem 1.5rem;
            padding: 1.5rem;
            border-radius: 12px;
            border: 2px solid #f59e0b;
        }

        .exam-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .exam-icon {
            color: #f59e0b;
            font-size: 1.5rem;
        }

        .exam-title {
            font-weight: 600;
            color: #1f2937;
        }

        .exam-description {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .exam-btn {
            width: 100%;
            padding: 0.75rem;
            background: #f59e0b;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .exam-btn:hover:not(:disabled) {
            background: #d97706;
        }

        .exam-btn:disabled {
            background: #d1d5db;
            cursor: not-allowed;
        }

        /* Success Messages */
        .success-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .success-message.show {
            transform: translateX(0);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .player-layout {
                grid-template-columns: 1fr;
                grid-template-rows: 1fr auto;
            }

            .course-sidebar {
                border-left: none;
                border-top: 1px solid #e5e7eb;
                max-height: 50vh;
            }

            .video-actions {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .header-content {
                padding: 0 1rem;
            }

            .course-title {
                font-size: 1rem;
            }

            .video-controls {
                padding: 1rem;
            }

            .video-info h2 {
                font-size: 1.2rem;
            }

            .btn-complete, .btn-exam {
                padding: 0.6rem 1.5rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Course Header -->
    <header class="course-header">
        <div class="header-content">
            <h1 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h1>
            <div class="header-actions">
                <div class="progress-indicator">
                    <?php echo count($user_progress['completed_videos']); ?>/<?php echo count($course['videos']); ?> videos completed
                </div>
                <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Course
                </a>
            </div>
        </div>
    </header>

    <!-- Main Player Layout -->
    <div class="player-layout">
        <!-- Video Section -->
        <div class="video-section">
            <div class="video-container">
                <?php 
                // Check if video file exists
                $video_path = $current_video['video_path'];
                if (file_exists($video_path)): 
                ?>
                    <video 
                        class="video-player" 
                        controls 
                        preload="metadata"
                        poster=""
                        onloadstart="console.log('Video loading started')"
                        onerror="console.log('Video loading error')">
                        <source src="<?php echo htmlspecialchars($video_path); ?>" type="video/mp4">
                        <source src="<?php echo htmlspecialchars(str_replace('.mp4', '.webm', $video_path)); ?>" type="video/webm">
                        <source src="<?php echo htmlspecialchars(str_replace('.mp4', '.ogg', $video_path)); ?>" type="video/ogg">
                        Your browser does not support the video tag.
                    </video>
                <?php else: ?>
                    <div class="video-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>Video Not Found</h3>
                        <p>The video file "<?php echo htmlspecialchars($video_path); ?>" could not be found.</p>
                        <p><small>Please make sure the video file exists in the correct location.</small></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="video-controls">
                <div class="video-info">
                    <h2><?php echo htmlspecialchars($current_video['title']); ?></h2>
                    <div class="video-meta">
                        <span><i class="fas fa-clock"></i> <?php echo $current_video['duration']; ?></span>
                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($course['instructor']); ?></span>
                    </div>
                    <p class="video-description">
                        <?php echo htmlspecialchars($current_video['description']); ?>
                    </p>
                </div>
                
                <div class="video-actions">
                    <?php 
                    $is_completed = in_array($current_video['id'], $user_progress['completed_videos']);
                    ?>
                    <button 
                        class="btn-complete <?php echo $is_completed ? 'completed' : ''; ?>"
                        onclick="markVideoComplete(<?php echo $current_video['id']; ?>)"
                        <?php echo $is_completed ? 'disabled' : ''; ?>>
                        <i class="fas fa-check"></i>
                        <?php echo $is_completed ? 'Video Completed' : 'Mark as Complete'; ?>
                    </button>
                    
                    <?php if ($all_videos_completed): ?>
                        <a href="exam.php?course_id=<?php echo $course['id']; ?>" class="btn-exam">
                            <i class="fas fa-clipboard-check"></i>
                            Take Final Exam
                        </a>
                    <?php else: ?>
                        <button class="btn-exam" disabled>
                            <i class="fas fa-lock"></i>
                            Complete All Videos to Unlock Exam
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Course Sidebar -->
        <div class="course-sidebar">
            <div class="sidebar-header">
                <h3>Course Progress</h3>
                <div class="course-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo (count($user_progress['completed_videos']) / count($course['videos'])) * 100; ?>%"></div>
                    </div>
                    <span><?php echo round((count($user_progress['completed_videos']) / count($course['videos'])) * 100); ?>%</span>
                </div>
            </div>

            <div class="video-list">
                <?php foreach ($course['videos'] as $index => $video): ?>
                    <?php 
                    $is_current = $video['id'] == $current_video['id'];
                    $is_completed = in_array($video['id'], $user_progress['completed_videos']);
                    $classes = ['video-item'];
                    if ($is_current) $classes[] = 'active';
                    if ($is_completed) $classes[] = 'completed';
                    ?>
                    <a href="?id=<?php echo $course['id']; ?>&video=<?php echo $video['id']; ?>" 
                       class="<?php echo implode(' ', $classes); ?>">
                        <div class="video-number">
                            <?php if ($is_completed): ?>
                                <i class="fas fa-check"></i>
                            <?php else: ?>
                                <?php echo $index + 1; ?>
                            <?php endif; ?>
                        </div>
                        <div class="video-details">
                            <div class="video-title"><?php echo htmlspecialchars($video['title']); ?></div>
                            <div class="video-duration"><?php echo $video['duration']; ?></div>
                        </div>
                        <?php if ($is_completed): ?>
                            <i class="fas fa-check-circle completion-icon"></i>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Exam Section -->
            <?php if ($all_videos_completed): ?>
                <div class="exam-section">
                    <div class="exam-header">
                        <i class="fas fa-trophy exam-icon"></i>
                        <span class="exam-title">Final Exam Available!</span>
                    </div>
                    <p class="exam-description">
                        Congratulations! You've completed all videos. Take the final exam to earn your certificate.
                    </p>
                    <a href="exam.php?course_id=<?php echo $course['id']; ?>" class="exam-btn">
                        Take Exam Now
                    </a>
                </div>
            <?php else: ?>
                <div class="exam-section">
                    <div class="exam-header">
                        <i class="fas fa-lock exam-icon"></i>
                        <span class="exam-title">Final Exam</span>
                    </div>
                    <p class="exam-description">
                        Complete all videos to unlock the final exam and earn your certificate.
                    </p>
                    <button class="exam-btn" disabled>
                        Exam Locked
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Success Message -->
    <div id="successMessage" class="success-message">
        <i class="fas fa-check-circle"></i>
        <span id="messageText">Video marked as complete!</span>
    </div>

    <script>
        function markVideoComplete(videoId) {
            fetch('mark-complete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    course_id: <?php echo $course['id']; ?>,
                    video_id: videoId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showSuccessMessage('Video marked as complete!');
                    
                    // Update UI
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    alert('Failed to mark video as complete. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to mark video as complete. Please try again.');
            });
        }

        function showSuccessMessage(message) {
            const messageEl = document.getElementById('successMessage');
            const textEl = document.getElementById('messageText');
            
            textEl.textContent = message;
            messageEl.classList.add('show');
            
            setTimeout(() => {
                messageEl.classList.remove('show');
            }, 3000);
        }

        // Auto-hide success message if shown on page load
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('completed') === '1') {
                showSuccessMessage('Video marked as complete!');
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Space bar to mark complete (when not in input field)
            if (e.code === 'Space' && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                const completeBtn = document.querySelector('.btn-complete:not(:disabled)');
                if (completeBtn) {
                    completeBtn.click();
                }
            }
            
            // Arrow keys for navigation
            if (e.code === 'ArrowLeft' || e.code === 'ArrowRight') {
                const currentVideo = <?php echo $current_video['id']; ?>;
                const videos = <?php echo json_encode(array_column($course['videos'], 'id')); ?>;
                const currentIndex = videos.indexOf(currentVideo);
                
                let newIndex;
                if (e.code === 'ArrowLeft' && currentIndex > 0) {
                    newIndex = currentIndex - 1;
                } else if (e.code === 'ArrowRight' && currentIndex < videos.length - 1) {
                    newIndex = currentIndex + 1;
                }
                
                if (newIndex !== undefined) {
                    window.location.href = `?id=<?php echo $course['id']; ?>&video=${videos[newIndex]}`;
                }
            }
        });

        // Video event handlers for enhanced functionality
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.querySelector('.video-player');
            if (video) {
                // Auto-mark as complete when video ends (optional)
                video.addEventListener('ended', function() {
                    const completeBtn = document.querySelector('.btn-complete:not(:disabled)');
                    if (completeBtn) {
                        // Uncomment the line below if you want auto-completion when video ends
                        // completeBtn.click();
                    }
                });

                // Save video progress (optional - you can implement this to remember where user left off)
                video.addEventListener('timeupdate', function() {
                    const progress = (video.currentTime / video.duration) * 100;
                    // You can save this progress to session or database
                    // sessionStorage.setItem('video_' + <?php echo $current_video['id']; ?> + '_progress', video.currentTime);
                });

                // Resume from saved progress (optional)
                // const savedTime = sessionStorage.getItem('video_' + <?php echo $current_video['id']; ?> + '_progress');
                // if (savedTime && savedTime > 10) { // Only resume if more than 10 seconds watched
                //     video.currentTime = savedTime;
                // }
            }
        });
    </script>
</body>
</html>