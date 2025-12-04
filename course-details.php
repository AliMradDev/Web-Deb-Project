<?php
session_start();

// Mock data - replace with your database queries
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Mock user subscription status - replace with your database query
$user_subscribed = isset($_SESSION['subscription']) && $_SESSION['subscription'] !== 'free';
$user_enrolled = isset($_SESSION['enrolled_courses']) && in_array($course_id, $_SESSION['enrolled_courses']);

// Mock course data - replace with your database query
$courses = [
    1 => [
        'id' => 1,
        'title' => 'Complete Web Development Bootcamp',
        'instructor' => 'Dr. Sarah Johnson',
        'rating' => 4.8,
        'students' => 15420,
        'duration' => '40 hours',
        'level' => 'Beginner to Advanced',
        'price' => 89.99,
        'description' => 'Master modern web development with HTML, CSS, JavaScript, React, Node.js, and more. Build real-world projects and launch your web development career.',
        'image' => 'course1.jpg',
        'what_you_learn' => [
            'Build responsive websites with HTML5 and CSS3',
            'Master JavaScript ES6+ and modern frameworks',
            'Create dynamic web applications with React',
            'Develop backend APIs with Node.js and Express',
            'Work with databases (MongoDB, MySQL)',
            'Deploy applications to production'
        ],
        'requirements' => [
            'Basic computer knowledge',
            'No prior programming experience needed',
            'Access to a computer with internet connection'
        ],
        'curriculum' => [
            'Introduction to Web Development',
            'HTML5 & CSS3 Fundamentals', 
            'JavaScript Basics & Advanced Concepts',
            'React.js Framework',
            'Backend Development with Node.js',
            'Database Integration',
            'Deployment & Production'
        ]
    ],
    2 => [
        'id' => 2,
        'title' => 'Data Science with Python',
        'instructor' => 'Prof. Michael Chen',
        'rating' => 4.9,
        'students' => 8340,
        'duration' => '35 hours',
        'level' => 'Intermediate',
        'price' => 79.99,
        'description' => 'Learn data science from scratch using Python. Master pandas, numpy, matplotlib, and machine learning algorithms.',
        'image' => 'course2.jpg',
        'what_you_learn' => [
            'Python programming for data science',
            'Data manipulation with pandas',
            'Data visualization with matplotlib and seaborn',
            'Statistical analysis and hypothesis testing',
            'Machine learning algorithms',
            'Real-world data science projects'
        ],
        'requirements' => [
            'Basic Python knowledge helpful but not required',
            'High school level mathematics',
            'Computer with Python installed'
        ],
        'curriculum' => [
            'Python Fundamentals for Data Science',
            'Data Manipulation with Pandas',
            'Data Visualization',
            'Statistical Analysis',
            'Machine Learning Basics',
            'Advanced ML Algorithms',
            'Capstone Project'
        ]
    ]
];

$course = $courses[$course_id] ?? $courses[1];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - EduLearn</title>
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
            background: #f8fafc;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Header Styles */
        .header, header, .navbar {
            background: white !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 1000 !important;
            width: 100% !important;
        }

        .nav, .navbar-content, .header-content {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 1rem 0 !important;
            max-width: 1200px !important;
            margin: 0 auto !important;
            padding-left: 2rem !important;
            padding-right: 2rem !important;
        }

        .logo, .brand, .site-logo {
            font-size: 1.8rem !important;
            font-weight: bold !important;
            color: #8b5cf6 !important;
            text-decoration: none !important;
        }

        .nav-links, .navbar-nav, .menu, .navigation {
            display: flex !important;
            gap: 2rem !important;
            list-style: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .nav-links a, .navbar-nav a, .menu a, .navigation a {
            color: #1f2937 !important;
            text-decoration: none !important;
            font-weight: 500 !important;
            transition: color 0.3s ease !important;
        }

        .nav-links a:hover, .navbar-nav a:hover, .menu a:hover, .navigation a:hover {
            color: #8b5cf6 !important;
        }

        /* Course Header */
        .course-header {
            margin-top: 80px;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 4rem 0;
        }

        .course-header-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 3rem;
            align-items: start;
        }

        .course-info h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .course-meta {
            display: flex;
            gap: 2rem;
            margin: 1rem 0;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stars {
            color: #ffd700;
        }

        .course-description {
            font-size: 1.2rem;
            opacity: 0.9;
            margin: 1.5rem 0;
            line-height: 1.7;
        }

        .course-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            position: sticky;
            top: 100px;
        }

        .course-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            color: white;
            font-size: 4rem;
        }

        .price {
            font-size: 2rem;
            font-weight: bold;
            color: #8b5cf6;
            margin-bottom: 1rem;
        }

        .enroll-btn, .access-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
            text-align: center;
            margin-bottom: 1rem;
        }

        .enroll-btn:hover, .access-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
        }

        .enroll-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }

        .subscription-required {
            background: #fef3c7;
            color: #92400e;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 0.9rem;
        }

        .course-includes {
            margin-top: 2rem;
        }

        .course-includes h4 {
            margin-bottom: 1rem;
            color: #1f2937;
        }

        .includes-list {
            list-style: none;
        }

        .includes-list li {
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
        }

        .includes-list i {
            color: #10b981;
        }

        /* Course Content */
        .course-content {
            padding: 4rem 0;
        }

        .content-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 3rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .tab-btn {
            padding: 1rem 2rem;
            background: none;
            border: none;
            font-size: 1.1rem;
            font-weight: 600;
            color: #6b7280;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .tab-btn.active {
            color: #8b5cf6;
            border-bottom-color: #8b5cf6;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .content-section {
            background: white;
            padding: 3rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.08);
            margin-bottom: 2rem;
        }

        .content-section h3 {
            font-size: 1.8rem;
            color: #1f2937;
            margin-bottom: 2rem;
            font-weight: 600;
        }

        .learn-list, .requirements-list, .curriculum-list {
            list-style: none;
        }

        .learn-list li, .requirements-list li, .curriculum-list li {
            padding: 0.8rem 0;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            border-bottom: 1px solid #f3f4f6;
        }

        .learn-list li:last-child, .requirements-list li:last-child, .curriculum-list li:last-child {
            border-bottom: none;
        }

        .learn-list i {
            color: #10b981;
            margin-top: 0.2rem;
        }

        .requirements-list i {
            color: #8b5cf6;
            margin-top: 0.2rem;
        }

        .curriculum-list i {
            color: #6b7280;
            margin-top: 0.2rem;
        }

        /* Instructor Section */
        .instructor-section {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-top: 2rem;
            padding: 2rem;
            background: #f8fafc;
            border-radius: 12px;
        }

        .instructor-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }

        .instructor-info h4 {
            font-size: 1.3rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .instructor-info p {
            color: #6b7280;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .course-header-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .course-info h1 {
                font-size: 2rem;
            }

            .course-meta {
                gap: 1rem;
            }

            .content-tabs {
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .tab-btn {
                padding: 0.8rem 1.5rem;
                font-size: 1rem;
            }

            .content-section {
                padding: 2rem;
            }

            .instructor-section {
                flex-direction: column;
                text-align: center;
            }

            .nav-links, .navbar-nav, .menu, .navigation {
                display: none !important;
            }
        }

        /* Success Messages */
        .success-message {
            background: #f0f9ff;
            color: #10b981;
            border: 1px solid #10b981;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            display: none;
        }

        .success-message.show {
            display: block;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Course Header -->
    <section class="course-header">
        <div class="container">
            <div class="course-header-content">
                <div class="course-info">
                    <h1><?php echo htmlspecialchars($course['title']); ?></h1>
                    <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
                    
                    <div class="course-meta">
                        <div class="meta-item rating">
                            <div class="stars">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star<?php echo $i <= floor($course['rating']) ? '' : ($i <= $course['rating'] ? '-half-alt' : ' far'); ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span><?php echo $course['rating']; ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-users"></i>
                            <span><?php echo number_format($course['students']); ?> students</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <span><?php echo $course['duration']; ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-signal"></i>
                            <span><?php echo $course['level']; ?></span>
                        </div>
                    </div>

                    <div class="instructor-section">
                        <div class="instructor-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="instructor-info">
                            <h4><?php echo htmlspecialchars($course['instructor']); ?></h4>
                            <p>Expert Instructor with 10+ years of experience</p>
                        </div>
                    </div>
                </div>

                <div class="course-card">
                    <div class="course-image">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    
                    <?php if (!$user_subscribed): ?>
                        <div class="subscription-required">
                            <i class="fas fa-lock"></i>
                            Premium subscription required to enroll
                        </div>
                        <a href="subscription.php" class="enroll-btn">
                            Subscribe to Enroll
                        </a>
                    <?php elseif ($user_enrolled): ?>
                        <div class="success-message show">
                            <i class="fas fa-check-circle"></i>
                            You are enrolled in this course
                        </div>
                        <a href="course-player.php?id=<?php echo $course['id']; ?>" class="access-btn">
                            <i class="fas fa-play"></i> Continue Learning
                        </a>
                    <?php else: ?>
                        <div class="price">Included in Subscription</div>
                        <button class="enroll-btn" onclick="enrollInCourse(<?php echo $course['id']; ?>)">
                            <i class="fas fa-graduation-cap"></i> Enroll Now
                        </button>
                    <?php endif; ?>

                    <div class="course-includes">
                        <h4>This course includes:</h4>
                        <ul class="includes-list">
                            <li><i class="fas fa-video"></i> 3 on-demand videos</li>
                            <li><i class="fas fa-certificate"></i> Certificate of completion</li>
                            <li><i class="fas fa-mobile-alt"></i> Access on mobile and TV</li>
                            <li><i class="fas fa-infinity"></i> Full lifetime access</li>
                            <li><i class="fas fa-question-circle"></i> Direct instructor support</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Course Content -->
    <section class="course-content">
        <div class="container">
            <div class="content-tabs">
                <button class="tab-btn active" onclick="switchTab('overview')">Overview</button>
                <button class="tab-btn" onclick="switchTab('curriculum')">Curriculum</button>
                <button class="tab-btn" onclick="switchTab('requirements')">Requirements</button>
            </div>

            <div id="overview" class="tab-content active">
                <div class="content-section">
                    <h3>What you'll learn</h3>
                    <ul class="learn-list">
                        <?php foreach ($course['what_you_learn'] as $item): ?>
                            <li>
                                <i class="fas fa-check"></i>
                                <span><?php echo htmlspecialchars($item); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div id="curriculum" class="tab-content">
                <div class="content-section">
                    <h3>Course Curriculum</h3>
                    <ul class="curriculum-list">
                        <?php foreach ($course['curriculum'] as $index => $module): ?>
                            <li>
                                <i class="fas fa-play-circle"></i>
                                <span>Module <?php echo $index + 1; ?>: <?php echo htmlspecialchars($module); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div id="requirements" class="tab-content">
                <div class="content-section">
                    <h3>Requirements</h3>
                    <ul class="requirements-list">
                        <?php foreach ($course['requirements'] as $requirement): ?>
                            <li>
                                <i class="fas fa-chevron-right"></i>
                                <span><?php echo htmlspecialchars($requirement); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <script>
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }

        function enrollInCourse(courseId) {
            // Simulate enrollment process
            fetch('enroll.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ course_id: courseId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI to show enrolled state
                    location.reload();
                } else {
                    alert('Enrollment failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Enrollment failed. Please try again.');
            });
        }

        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.header, header, .navbar');
            if (header && window.scrollY > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.backdropFilter = 'blur(10px)';
            } else if (header) {
                header.style.background = 'white';
                header.style.backdropFilter = 'none';
            }
        });
    </script>
</body>
</html>