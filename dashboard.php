<?php
session_start();
require_once 'database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $pdo = getDbConnection();
    
    // Get user information
    $stmt = $pdo->prepare("SELECT * FROM users_acc WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit();
    }
    
    // Get enrollment statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_courses,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_courses,
            COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress_courses,
            COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_courses,
            COUNT(CASE WHEN exam_status = 'passed' THEN 1 END) as certificates_earned,
            AVG(CASE WHEN exam_score IS NOT NULL THEN exam_score END) as avg_exam_score,
            SUM(time_spent) as total_time_spent
        FROM course_enrollments 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent enrollments
    $stmt = $pdo->prepare("
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
            END as display_status
        FROM course_enrollments e
        LEFT JOIN course_list cl ON e.course_id = cl.id
        WHERE e.user_id = ?
        ORDER BY e.last_accessed DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get courses in progress
    $stmt = $pdo->prepare("
        SELECT 
            e.*,
            cl.title as course_title,
            cl.description as course_description
        FROM course_enrollments e
        LEFT JOIN course_list cl ON e.course_id = cl.id
        WHERE e.user_id = ? AND e.status = 'in_progress'
        ORDER BY e.last_accessed DESC
        LIMIT 3
    ");
    $stmt->execute([$user_id]);
    $in_progress_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get achievements/certificates
    $stmt = $pdo->prepare("
        SELECT 
            e.*,
            cl.title as course_title
        FROM course_enrollments e
        LEFT JOIN course_list cl ON e.course_id = cl.id
        WHERE e.user_id = ? AND e.exam_status = 'passed'
        ORDER BY e.completion_date DESC
        LIMIT 3
    ");
    $stmt->execute([$user_id]);
    $achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error_message = "Error loading dashboard: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EduLearn Academy</title>
    <link href="Web3.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin-top: 80px;
            background: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin: -2rem -2rem 3rem -2rem;
            border-radius: 0 0 24px 24px;
        }

        .dashboard-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .dashboard-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 16px;
            margin-top: 2rem;
            backdrop-filter: blur(10px);
        }

        .welcome-message {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .last-activity {
            opacity: 0.8;
            font-size: 0.9rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
        }

        .stat-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .stat-icon.courses { color: #3b82f6; }
        .stat-icon.completed { color: #10b981; }
        .stat-icon.certificates { color: #f59e0b; }
        .stat-icon.time { color: #8b5cf6; }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
        }

        .stat-sublabel {
            color: #9ca3af;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .dashboard-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .section-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f3f4f6;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .view-all {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .view-all:hover {
            text-decoration: underline;
        }

        /* Course Cards */
        .course-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border: 1px solid #f3f4f6;
            border-radius: 12px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .course-item:hover {
            border-color: #3b82f6;
            background: #f8fafc;
        }

        .course-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            margin-right: 1rem;
        }

        .course-info {
            flex: 1;
        }

        .course-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .course-meta {
            color: #6b7280;
            font-size: 0.85rem;
        }

        .course-progress {
            width: 100%;
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            margin-top: 0.5rem;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            transition: width 0.3s ease;
        }

        .progress-fill.completed {
            background: linear-gradient(90deg, #10b981, #059669);
        }

        /* Achievement Cards */
        .achievement-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: linear-gradient(135deg, #fef3c7, #fbbf24);
            border-radius: 12px;
            margin-bottom: 1rem;
            color: #92400e;
        }

        .achievement-icon {
            font-size: 2rem;
            margin-right: 1rem;
        }

        .achievement-info h4 {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .achievement-date {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 3rem;
        }

        .action-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .action-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .action-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #3b82f6;
        }

        .action-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }

        .action-description {
            color: #6b7280;
            font-size: 0.9rem;
        }

        /* Empty States */
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .dashboard-header {
                margin: -1rem -1rem 2rem -1rem;
                padding: 2rem 1rem;
            }

            .dashboard-header h1 {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
            <p>Welcome back to your learning journey</p>
            
            <div class="welcome-card">
                <div class="welcome-message">
                    Hello, <?php echo htmlspecialchars($user['username']); ?>! 👋
                </div>
                <div class="last-activity">
                    Member since <?php echo date('F Y', strtotime($user['created_at'] ?? 'now')); ?>
                </div>
            </div>
        </div>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon courses">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-number"><?php echo $stats['total_courses'] ?? 0; ?></div>
                <div class="stat-label">Total Courses</div>
                <div class="stat-sublabel">Enrolled</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon completed">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number"><?php echo $stats['completed_courses'] ?? 0; ?></div>
                <div class="stat-label">Completed</div>
                <div class="stat-sublabel">Courses finished</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon certificates">
                    <i class="fas fa-certificate"></i>
                </div>
                <div class="stat-number"><?php echo $stats['certificates_earned'] ?? 0; ?></div>
                <div class="stat-label">Certificates</div>
                <div class="stat-sublabel">Earned</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon time">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number"><?php echo $stats['total_time_spent'] ?? 0; ?></div>
                <div class="stat-label">Hours Spent</div>
                <div class="stat-sublabel">Learning time</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="courses.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-search"></i>
                </div>
                <div class="action-title">Browse Courses</div>
                <div class="action-description">Discover new learning opportunities</div>
            </a>

            <a href="my-courses.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="action-title">My Courses</div>
                <div class="action-description">Continue your learning journey</div>
            </a>

            <a href="profile.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-user-edit"></i>
                </div>
                <div class="action-title">Edit Profile</div>
                <div class="action-description">Update your information</div>
            </a>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Recent Activity -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-history"></i>
                        Recent Activity
                    </h2>
                    <a href="my-courses.php" class="view-all">View All</a>
                </div>

                <?php if (empty($recent_courses)): ?>
                    <div class="empty-state">
                        <i class="fas fa-book-open"></i>
                        <h3>No courses yet</h3>
                        <p>Start your learning journey by enrolling in a course!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recent_courses as $course): ?>
                        <div class="course-item">
                            <div class="course-icon">
                                <i class="fas fa-play"></i>
                            </div>
                            <div class="course-info">
                                <div class="course-title"><?php echo htmlspecialchars($course['course_title']); ?></div>
                                <div class="course-meta">
                                    <?php echo $course['display_status']; ?> • 
                                    Last accessed <?php echo $course['last_accessed'] ? date('M j', strtotime($course['last_accessed'])) : 'Never'; ?>
                                </div>
                                <div class="course-progress">
                                    <div class="progress-fill <?php echo $course['display_status'] === 'Completed' ? 'completed' : ''; ?>" 
                                         style="width: <?php echo $course['progress_percentage']; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div>
                <!-- Continue Learning -->
                <div class="dashboard-section" style="margin-bottom: 2rem;">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-play-circle"></i>
                            Continue Learning
                        </h3>
                    </div>

                    <?php if (empty($in_progress_courses)): ?>
                        <div class="empty-state">
                            <i class="fas fa-play"></i>
                            <p>No courses in progress</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($in_progress_courses as $course): ?>
                            <div class="course-item" style="margin-bottom: 1rem;">
                                <div class="course-icon" style="width: 40px; height: 40px; font-size: 1rem;">
                                    <i class="fas fa-play"></i>
                                </div>
                                <div class="course-info">
                                    <div class="course-title" style="font-size: 0.9rem;">
                                        <?php echo htmlspecialchars($course['course_title']); ?>
                                    </div>
                                    <div class="course-meta">
                                        <?php echo $course['progress_percentage']; ?>% complete
                                    </div>
                                    <div class="course-progress">
                                        <div class="progress-fill" style="width: <?php echo $course['progress_percentage']; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Recent Achievements -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-trophy"></i>
                            Recent Achievements
                        </h3>
                    </div>

                    <?php if (empty($achievements)): ?>
                        <div class="empty-state">
                            <i class="fas fa-trophy"></i>
                            <p>No certificates yet</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($achievements as $achievement): ?>
                            <div class="achievement-item">
                                <div class="achievement-icon">
                                    <i class="fas fa-certificate"></i>
                                </div>
                                <div class="achievement-info">
                                    <h4><?php echo htmlspecialchars($achievement['course_title']); ?></h4>
                                    <div class="achievement-date">
                                        Completed <?php echo date('M j, Y', strtotime($achievement['completion_date'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Animate progress bars
        document.addEventListener('DOMContentLoaded', function() {
            const progressBars = document.querySelectorAll('.progress-fill');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 500);
            });
        });

        // Auto-refresh every 5 minutes
        setTimeout(() => {
            window.location.reload();
        }, 300000);
    </script>
</body>
</html>