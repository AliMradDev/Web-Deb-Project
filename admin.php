<?php
session_start();
require_once 'database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// For now, check if user ID is 1 (you can modify this to check a role column)
$user_id = $_SESSION['user_id'];
if ($user_id != "admin") { // Change this to check role = 'admin' if you have roles
    die("Access denied. Admin privileges required.");
}

try {
    $pdo = getDbConnection();
    
    // Get dashboard statistics
    $stats = [];
    
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users_acc");
    $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
    
    // Total courses
    $stmt = $pdo->query("SELECT COUNT(*) as total_courses FROM course_list");
    $stats['total_courses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_courses'];
    
    // Total enrollments
    $stmt = $pdo->query("SELECT COUNT(*) as total_enrollments FROM course_enrollments");
    $stats['total_enrollments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_enrollments'];
    
    // Active enrollments
    $stmt = $pdo->query("SELECT COUNT(*) as active_enrollments FROM course_enrollments WHERE status = 'in_progress'");
    $stats['active_enrollments'] = $stmt->fetch(PDO::FETCH_ASSOC)['active_enrollments'];
    
    // Completed courses
    $stmt = $pdo->query("SELECT COUNT(*) as completed_courses FROM course_enrollments WHERE status = 'completed'");
    $stats['completed_courses'] = $stmt->fetch(PDO::FETCH_ASSOC)['completed_courses'];
    
    // Average exam score
    $stmt = $pdo->query("SELECT AVG(exam_score) as avg_score FROM course_enrollments WHERE exam_score IS NOT NULL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['avg_exam_score'] = round($result['avg_score'] ?? 0, 1);
    
    // Recent activities
    $stmt = $pdo->prepare("
        SELECT 
            u.username,
            c.title as course_title,
            e.status,
            e.enrolled_at,
            e.last_accessed
        FROM course_enrollments e
        JOIN users_acc u ON e.user_id = u.id
        JOIN course_list c ON e.course_id = c.id
        ORDER BY e.last_accessed DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error_message = "Error loading dashboard: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EduLearn Academy</title>
    <link href="Web3.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            background: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Admin Layout */
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .admin-sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #3b82f6;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar-subtitle {
            font-size: 0.8rem;
            opacity: 0.7;
            margin-top: 0.25rem;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-section {
            margin-bottom: 2rem;
        }

        .nav-section-title {
            padding: 0 1.5rem 0.5rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            opacity: 0.5;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .nav-item {
            display: block;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border-left-color: #3b82f6;
        }

        .nav-item i {
            width: 20px;
            margin-right: 0.75rem;
        }

        /* Main Content */
        .admin-main {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }

        .admin-header {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.users { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .stat-icon.courses { background: linear-gradient(135deg, #10b981, #059669); }
        .stat-icon.enrollments { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .stat-icon.completed { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: #6b7280;
            font-weight: 600;
        }

        .stat-change {
            font-size: 0.8rem;
            color: #10b981;
            margin-top: 0.5rem;
        }

        /* Quick Actions */
        .quick-actions {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .section-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1f2937;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            padding: 2rem;
        }

        .action-card {
            padding: 1.5rem;
            border: 2px solid #f3f4f6;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s;
        }

        .action-card:hover {
            border-color: #3b82f6;
            background: #f8fafc;
        }

        .action-icon {
            font-size: 2rem;
            color: #3b82f6;
            margin-bottom: 1rem;
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

        /* Recent Activity */
        .activity-table {
            width: 100%;
            border-collapse: collapse;
        }

        .activity-table th,
        .activity-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #f3f4f6;
        }

        .activity-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-in_progress {
            background: #fef3c7;
            color: #92400e;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-failed {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }

            .admin-sidebar.open {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .admin-main {
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Content sections for different pages */
        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-graduation-cap"></i>
                    EduLearn Admin
                </div>
                <div class="sidebar-subtitle">Administration Panel</div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Overview</div>
                    <a href="#dashboard" class="nav-item active" onclick="showSection('dashboard')">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Course Management</div>
                    <a href="#courses" class="nav-item" onclick="showSection('courses')">
                        <i class="fas fa-book"></i>
                        Courses
                    </a>
                    <a href="#course-content" class="nav-item" onclick="showSection('course-content')">
                        <i class="fas fa-video"></i>
                        Course Content
                    </a>
                    <a href="#course-create" class="nav-item" onclick="showSection('course-create')">
                        <i class="fas fa-plus"></i>
                        Create Course
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">User Management</div>
                    <a href="#users" class="nav-item" onclick="showSection('users')">
                        <i class="fas fa-users"></i>
                        All Users
                    </a>
                    <a href="#enrollments" class="nav-item" onclick="showSection('enrollments')">
                        <i class="fas fa-user-graduate"></i>
                        Enrollments
                    </a>
                    <a href="#teachers" class="nav-item" onclick="showSection('teachers')">
                        <i class="fas fa-chalkboard-teacher"></i>
                        Teachers
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Analytics</div>
                    <a href="#analytics" class="nav-item" onclick="showSection('analytics')">
                        <i class="fas fa-chart-bar"></i>
                        Analytics
                    </a>
                    <a href="#reports" class="nav-item" onclick="showSection('reports')">
                        <i class="fas fa-file-alt"></i>
                        Reports
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">System</div>
                    <a href="#settings" class="nav-item" onclick="showSection('settings')">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                    <a href="dashboard.php" class="nav-item">
                        <i class="fas fa-home"></i>
                        Back to Site
                    </a>
                    <a href="logout.php" class="nav-item">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            <!-- Header -->
            <div class="admin-header">
                <h1 class="page-title">
                    <i class="fas fa-tachometer-alt"></i>
                    <span id="page-title-text">Dashboard</span>
                </h1>
                <div class="admin-user">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600;">Admin User</div>
                        <div style="font-size: 0.8rem; color: #6b7280;">Administrator</div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Section -->
            <div id="dashboard" class="content-section active">
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value"><?php echo $stats['total_users']; ?></div>
                                <div class="stat-label">Total Users</div>
                            </div>
                            <div class="stat-icon users">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="stat-change">+12% from last month</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value"><?php echo $stats['total_courses']; ?></div>
                                <div class="stat-label">Total Courses</div>
                            </div>
                            <div class="stat-icon courses">
                                <i class="fas fa-book"></i>
                            </div>
                        </div>
                        <div class="stat-change">+3 new courses</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value"><?php echo $stats['total_enrollments']; ?></div>
                                <div class="stat-label">Total Enrollments</div>
                            </div>
                            <div class="stat-icon enrollments">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                        </div>
                        <div class="stat-change">+<?php echo $stats['active_enrollments']; ?> active</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value"><?php echo $stats['avg_exam_score']; ?>%</div>
                                <div class="stat-label">Avg Exam Score</div>
                            </div>
                            <div class="stat-icon completed">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="stat-change"><?php echo $stats['completed_courses']; ?> completed</div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <div class="section-header">
                        <h2 class="section-title">Quick Actions</h2>
                    </div>
                    <div class="actions-grid">
                        <a href="#course-create" class="action-card" onclick="showSection('course-create')">
                            <div class="action-icon">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="action-title">Create New Course</div>
                            <div class="action-description">Add a new course to the platform</div>
                        </a>

                        <a href="#users" class="action-card" onclick="showSection('users')">
                            <div class="action-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="action-title">Manage Users</div>
                            <div class="action-description">View and manage user accounts</div>
                        </a>

                        <a href="#courses" class="action-card" onclick="showSection('courses')">
                            <div class="action-icon">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div class="action-title">Edit Courses</div>
                            <div class="action-description">Update existing course content</div>
                        </a>

                        <a href="#analytics" class="action-card" onclick="showSection('analytics')">
                            <div class="action-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="action-title">View Analytics</div>
                            <div class="action-description">Check platform performance</div>
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="quick-actions">
                    <div class="section-header">
                        <h2 class="section-title">Recent Activity</h2>
                        <a href="#enrollments" onclick="showSection('enrollments')" style="color: #3b82f6; text-decoration: none; font-weight: 600;">View All</a>
                    </div>
                    <div style="padding: 0 2rem 2rem;">
                        <table class="activity-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Course</th>
                                    <th>Status</th>
                                    <th>Last Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recent_activities, 0, 5) as $activity): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($activity['username']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['course_title']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $activity['status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $activity['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $activity['last_accessed'] ? date('M j, Y', strtotime($activity['last_accessed'])) : 'Never'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Other sections will be loaded via JavaScript/AJAX -->
            <div id="courses" class="content-section">
                <div style="text-align: center; padding: 4rem;">
                    <i class="fas fa-book" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <h3>Course Management</h3>
                    <p>Course management interface will be loaded here.</p>
                    <button onclick="loadCourseManagement()" class="btn btn-primary">Load Course Management</button>
                </div>
            </div>

            <!-- Placeholder sections -->
            <div id="course-content" class="content-section">
                <div style="text-align: center; padding: 4rem;">
                    <i class="fas fa-video" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <h3>Course Content Management</h3>
                    <p>Video and content management interface will be loaded here.</p>
                </div>
            </div>

            <div id="course-create" class="content-section">
                <div style="text-align: center; padding: 4rem;">
                    <i class="fas fa-plus" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <h3>Create New Course</h3>
                    <p>Course creation form will be loaded here.</p>
                </div>
            </div>

            <div id="users" class="content-section">
                <div style="text-align: center; padding: 4rem;">
                    <i class="fas fa-users" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <h3>User Management</h3>
                    <p>User management interface will be loaded here.</p>
                </div>
            </div>

            <div id="enrollments" class="content-section">
                <div style="text-align: center; padding: 4rem;">
                    <i class="fas fa-user-graduate" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <h3>Enrollment Management</h3>
                    <p>Enrollment management interface will be loaded here.</p>
                </div>
            </div>

            <div id="teachers" class="content-section">
                <div style="text-align: center; padding: 4rem;">
                    <i class="fas fa-chalkboard-teacher" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <h3>Teacher Management</h3>
                    <p>Teacher management interface will be loaded here.</p>
                </div>
            </div>

            <div id="analytics" class="content-section">
                <div style="text-align: center; padding: 4rem;">
                    <i class="fas fa-chart-bar" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <h3>Analytics</h3>
                    <p>Analytics dashboard will be loaded here.</p>
                </div>
            </div>

            <div id="reports" class="content-section">
                <div style="text-align: center; padding: 4rem;">
                    <i class="fas fa-file-alt" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <h3>Reports</h3>
                    <p>Report generation interface will be loaded here.</p>
                </div>
            </div>

            <div id="settings" class="content-section">
                <div style="text-align: center; padding: 4rem;">
                    <i class="fas fa-cog" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <h3>System Settings</h3>
                    <p>System configuration interface will be loaded here.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Show selected section
            document.getElementById(sectionId).classList.add('active');
            
            // Update nav items
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Update page title
            const titles = {
                'dashboard': 'Dashboard',
                'courses': 'Course Management',
                'course-content': 'Course Content',
                'course-create': 'Create Course',
                'users': 'User Management',
                'enrollments': 'Enrollments',
                'teachers': 'Teachers',
                'analytics': 'Analytics',
                'reports': 'Reports',
                'settings': 'Settings'
            };
            
            document.getElementById('page-title-text').textContent = titles[sectionId] || 'Dashboard';
            
            // Mark current nav item as active
            event.target.classList.add('active');
        }

        function loadCourseManagement() {
            // This would load the course management interface
            alert('Course management interface would be loaded here');
        }

        // Auto-refresh stats every 30 seconds
        setInterval(() => {
            // You can add AJAX calls here to update stats
        }, 30000);
    </script>
</body>
</html>