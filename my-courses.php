<?php
session_start();
require_once 'database.php';

// Clear course cache if retake parameter is present
if (isset($_GET['retake_success']) || isset($_GET['reset_success'])) {
    if (isset($_SESSION['course_cache'])) {
        unset($_SESSION['course_cache']);
    }
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get database connection
try {
    $pdo = getDbConnection();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$category_filter = $_GET['category'] ?? '';

// Get user's enrolled courses
try {
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
    ";
    
    $params = [$user_id];
    
    // Apply filters
    if (!empty($status_filter)) {
        $query .= " AND e.status = ?";
        $params[] = $status_filter;
    }
    
    $query .= " ORDER BY e.enrolled_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $enrolled_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $enrolled_courses = [];
    $error_message = "Error loading enrolled courses: " . $e->getMessage();
}

// Get categories for filter (simplified since category column might not exist)
$categories = [];

// Calculate stats
$total_courses = count($enrolled_courses);
$completed_courses = count(array_filter($enrolled_courses, fn($course) => $course['display_status'] === 'Completed'));
$in_progress_courses = count(array_filter($enrolled_courses, fn($course) => $course['display_status'] === 'In Progress'));
$failed_courses = count(array_filter($enrolled_courses, fn($course) => $course['display_status'] === 'Failed'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - EduLearn Academy</title>
    <link href="Web3.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">
    
    <!-- Add cache busting -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <style>
        /* Keep all your existing CSS styles */
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

        .page-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin: -2rem -2rem 2rem -2rem;
        }

        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .stat-icon.total { color: #3b82f6; }
        .stat-icon.completed { color: #10b981; }
        .stat-icon.progress { color: #f59e0b; }
        .stat-icon.failed { color: #ef4444; }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
        }

        /* Filters */
        .filters-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-group label {
            font-weight: 600;
            color: #1f2937;
            font-size: 0.9rem;
        }

        .filter-group select {
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .filter-group select:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .clear-filters {
            background: #6b7280;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .clear-filters:hover {
            background: #4b5563;
        }

        /* Course Cards */
        .courses-grid {
            display: grid;
            gap: 1.5rem;
        }

        .course-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .course-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .course-header {
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }

        .course-info {
            flex: 1;
        }

        .course-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .course-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.75rem;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .course-description {
            color: #6b7280;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-in-progress {
            background: #fef3c7;
            color: #92400e;
        }

        .status-failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-not-started {
            background: #f3f4f6;
            color: #374151;
        }

        .status-dropped {
            background: #e5e7eb;
            color: #4b5563;
        }

        /* Progress Section */
        .progress-section {
            padding: 1.5rem;
            border-top: 1px solid #f3f4f6;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .progress-label {
            font-weight: 600;
            color: #1f2937;
        }

        .progress-percentage {
            font-weight: 700;
            color: #3b82f6;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            transition: width 0.3s ease;
        }

        .progress-fill.completed {
            background: linear-gradient(90deg, #10b981, #059669);
        }

        .progress-fill.failed {
            background: linear-gradient(90deg, #ef4444, #dc2626);
        }

        .course-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .detail-item {
            text-align: center;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            font-weight: 600;
        }

        .detail-value {
            font-weight: 600;
            color: #1f2937;
        }

        /* Action Buttons */
        .course-actions {
            padding: 1.5rem;
            border-top: 1px solid #f3f4f6;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            font-size: 0.9rem;
            flex: 1;
            min-width: 120px;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .btn-outline {
            background: transparent;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }

        .btn-outline:hover {
            background: #f9fafb;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #1f2937;
        }

        /* Error Message */
        .error-message {
            background: #fef2f2;
            color: #dc2626;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .page-header {
                margin: -1rem -1rem 2rem -1rem;
                padding: 2rem 1rem;
            }

            .page-header h1 {
                font-size: 1.8rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .course-header {
                flex-direction: column;
                text-align: center;
            }

            .course-meta {
                justify-content: center;
            }

            .course-actions {
                flex-direction: column;
            }

            .btn {
                flex: none;
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-graduation-cap"></i> My Courses</h1>
            <p>Track your learning progress and continue your education journey</p>
        </div>

        <!-- Error Message -->
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-number"><?php echo $total_courses; ?></div>
                <div class="stat-label">Total Courses</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon completed">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number"><?php echo $completed_courses; ?></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon progress">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number"><?php echo $in_progress_courses; ?></div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon failed">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-number"><?php echo $failed_courses; ?></div>
                <div class="stat-label">Failed</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-section">
            <form method="GET" action="my-courses.php" class="filters-grid">
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="failed" <?php echo $status_filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                        <option value="dropped" <?php echo $status_filter === 'dropped' ? 'selected' : ''; ?>>Dropped</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>&nbsp;</label>
                    <button type="button" class="clear-filters" onclick="clearFilters()">
                        <i class="fas fa-times"></i> Clear Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Courses List -->
        <?php if (empty($enrolled_courses)): ?>
            <div class="empty-state">
                <i class="fas fa-graduation-cap"></i>
                <h3>No Courses Enrolled</h3>
                <p>You haven't enrolled in any courses yet. Start learning today!</p>
                <a href="courses.php" class="btn btn-primary" style="margin-top: 1rem;">
                    Browse Courses
                </a>
            </div>
        <?php else: ?>
            <div class="courses-grid">
                <?php foreach ($enrolled_courses as $course): ?>
                    <div class="course-card">
                        <div class="course-header">
                            <div class="course-info">
                                <h3 class="course-title">
                                    <?php echo htmlspecialchars($course['course_title']); ?>
                                </h3>
                                <div class="course-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-tag"></i>
                                        <span>General</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-signal"></i>
                                        <span>Beginner</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-user"></i>
                                        <span>Expert Instructor</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>Enrolled <?php echo date('M j, Y', strtotime($course['enrolled_at'])); ?></span>
                                    </div>
                                </div>
                                <p class="course-description">
                                    <?php echo htmlspecialchars($course['course_description'] ?? ''); ?>
                                </p>
                            </div>
                            <div class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $course['display_status'])); ?>">
                                <?php echo $course['display_status']; ?>
                            </div>
                        </div>

                        <div class="progress-section">
                            <div class="progress-header">
                                <span class="progress-label">Progress</span>
                                <span class="progress-percentage"><?php echo $course['progress_percentage'] ?? 0; ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill <?php echo strtolower(str_replace(' ', '-', $course['display_status'])); ?>" 
                                     style="width: <?php echo $course['progress_percentage'] ?? 0; ?>%"></div>
                            </div>

                            <div class="course-details">
                                <div class="detail-item">
                                    <div class="detail-label">Last Activity</div>
                                    <div class="detail-value">
                                        <?php echo $course['last_accessed'] ? date('M j, Y', strtotime($course['last_accessed'])) : 'Never'; ?>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Time Spent</div>
                                    <div class="detail-value">
                                        <?php echo $course['time_spent'] ? $course['time_spent'] . ' hrs' : '0 hrs'; ?>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Exam Score</div>
                                    <div class="detail-value">
                                        <?php echo $course['exam_display']; ?>
                                    </div>
                                </div>
                                <?php if ($course['completion_date']): ?>
                                    <div class="detail-item">
                                        <div class="detail-label">Completed</div>
                                        <div class="detail-value">
                                            <?php echo date('M j, Y', strtotime($course['completion_date'])); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- FIXED ACTION BUTTONS SECTION -->
                        <div class="course-actions">
                            <?php if ($course['display_status'] === 'In Progress' || $course['display_status'] === 'Not Started'): ?>
                                <a href="course-viewer.php?id=<?php echo $course['course_id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-play"></i> Continue Learning
                                </a>
                                <a href="course-details.php?id=<?php echo $course['course_id']; ?>" class="btn btn-outline">
                                    <i class="fas fa-info-circle"></i> Details
                                </a>
                                
                            <?php elseif ($course['display_status'] === 'Completed'): ?>
                                <?php 
                                // FIXED: Check if exam was taken (more strict checking)
                                $exam_taken = ($course['exam_score'] !== null && $course['exam_score'] !== '');
                                if (!$exam_taken): 
                                ?>
                                    <a href="exam.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-primary" onclick="return confirmExam()">
                                        <i class="fas fa-clipboard-check"></i> Take Final Exam
                                    </a>
                                <?php elseif ($course['exam_status'] === 'passed'): ?>
                                    <a href="certificate.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-success">
                                        <i class="fas fa-certificate"></i> Download Certificate
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Always show retake option for completed courses -->
                                <a href="retake-course.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-warning">
                                    <i class="fas fa-redo"></i> Retake Course
                                </a>
                                
                                <a href="course-viewer.php?id=<?php echo $course['course_id']; ?>" class="btn btn-outline">
                                    <i class="fas fa-eye"></i> Review Course
                                </a>
                                
                            <?php elseif ($course['display_status'] === 'Failed'): ?>
                                <a href="retake-course.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-warning">
                                    <i class="fas fa-redo"></i> Retake Course
                                </a>
                                <a href="course-viewer.php?id=<?php echo $course['course_id']; ?>" class="btn btn-outline">
                                    <i class="fas fa-eye"></i> Review Course
                                </a>
                                
                            <?php else: ?>
                                <a href="course-viewer.php?id=<?php echo $course['course_id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-play"></i> Start Learning
                                </a>
                                <a href="course-details.php?id=<?php echo $course['course_id']; ?>" class="btn btn-outline">
                                    <i class="fas fa-info-circle"></i> Details
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($course['display_status'] !== 'Completed' && $course['display_status'] !== 'Failed'): ?>
                                <button onclick="dropCourse(<?php echo $course['id']; ?>)" class="btn btn-outline">
                                    <i class="fas fa-trash"></i> Drop
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function clearFilters() {
            window.location.href = 'my-courses.php';
        }

        function confirmExam() {
            return confirm('Are you ready to take the final exam for this course? Make sure you have reviewed all the material.');
        }

        function dropCourse(enrollmentId) {
            if (confirm('Are you sure you want to drop this course? This action cannot be undone.')) {
                fetch('drop-course.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        enrollment_id: enrollmentId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error dropping course: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error dropping course. Please try again.');
                });
            }
        }

        // Auto-submit form when filters change
        document.querySelectorAll('#status, #category').forEach(element => {
            element.addEventListener('change', function() {
                this.form.submit();
            });
        });

        // Update progress bars animation
        document.addEventListener('DOMContentLoaded', function() {
            const progressBars = document.querySelectorAll('.progress-fill');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });
            
            // FIXED: Add cache busting to prevent browser caching issues
            const courseLinks = document.querySelectorAll('a[href*="course-viewer"], a[href*="exam.php"], a[href*="retake-course"]');
            courseLinks.forEach(link => {
                const url = new URL(link.href);
                url.searchParams.set('t', Date.now());
                link.href = url.toString();
            });
            
            // Debug: Log current exam status
            console.log('Debug: Current course states loaded');
            document.querySelectorAll('.course-card').forEach((card, index) => {
                const status = card.querySelector('.status-badge').textContent;
                const examScore = card.querySelector('.detail-value');
                console.log(`Course ${index + 1}: Status=${status}, Exam=${examScore?.textContent || 'N/A'}`);
            });
        });
    </script>
</body>
</html>