<?php
// teacher-profile.php - Individual teacher profile page
session_start();

// Get teacher ID from URL
$teacher_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Sample teachers data (same as teachers.php)
$teachers = [
    1 => [
        'id' => 1,
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => 'john@edulearn.com',
        'specialization' => 'Web Development',
        'experience' => '8 years',
        'rating' => 4.8,
        'students' => 1250,
        'courses' => 12,
        'bio' => 'Experienced web developer with expertise in modern JavaScript frameworks and full-stack development. I have been teaching programming for over 8 years and have helped thousands of students launch their careers in tech.',
        'profile_image' => null,
        'skills' => ['JavaScript', 'React', 'Node.js', 'PHP', 'MySQL', 'HTML/CSS', 'Vue.js', 'Express.js'],
        'education' => [
            'Bachelor of Computer Science - MIT (2010)',
            'Master of Software Engineering - Stanford (2012)'
        ],
        'certifications' => [
            'AWS Certified Developer',
            'Google Cloud Professional',
            'MongoDB Certified Developer'
        ],
        'languages' => ['English (Native)', 'Spanish (Fluent)', 'French (Basic)'],
        'achievements' => [
            'Top Rated Instructor 2023',
            'Best Course Creator Award',
            'Student Choice Award Winner'
        ],
        'social' => [
            'linkedin' => 'https://linkedin.com/in/johnsmith',
            'github' => 'https://github.com/johnsmith',
            'twitter' => 'https://twitter.com/johnsmith'
        ]
    ],
    2 => [
        'id' => 2,
        'first_name' => 'Sarah',
        'last_name' => 'Davis',
        'email' => 'sarah@edulearn.com',
        'specialization' => 'UI/UX Design',
        'experience' => '6 years',
        'rating' => 4.9,
        'students' => 980,
        'courses' => 8,
        'bio' => 'Creative designer passionate about user experience and modern design principles. I specialize in creating intuitive and beautiful digital experiences that users love.',
        'profile_image' => null,
        'skills' => ['Figma', 'Adobe XD', 'Photoshop', 'Illustrator', 'Sketch', 'InVision', 'Principle', 'After Effects'],
        'education' => [
            'Bachelor of Fine Arts - RISD (2015)',
            'UX Design Certificate - Nielsen Norman Group'
        ],
        'certifications' => [
            'Google UX Design Certificate',
            'Adobe Certified Expert',
            'Figma Professional Certificate'
        ],
        'languages' => ['English (Native)', 'German (Intermediate)'],
        'achievements' => [
            'Design Excellence Award 2023',
            'Top UX Mentor',
            'Innovation in Design Award'
        ],
        'social' => [
            'linkedin' => 'https://linkedin.com/in/sarahdavis',
            'dribbble' => 'https://dribbble.com/sarahdavis',
            'behance' => 'https://behance.net/sarahdavis'
        ]
    ],
    3 => [
        'id' => 3,
        'first_name' => 'Michael',
        'last_name' => 'Johnson',
        'email' => 'michael@edulearn.com',
        'specialization' => 'Data Science',
        'experience' => '10 years',
        'rating' => 4.7,
        'students' => 2100,
        'courses' => 15,
        'bio' => 'Data scientist with extensive experience in machine learning and statistical analysis. I help students understand complex data concepts and apply them to real-world problems.',
        'profile_image' => null,
        'skills' => ['Python', 'R', 'Machine Learning', 'Statistics', 'SQL', 'TensorFlow', 'Pandas', 'Scikit-learn'],
        'education' => [
            'PhD in Statistics - Harvard University (2013)',
            'Master of Data Science - UC Berkeley (2011)'
        ],
        'certifications' => [
            'Google Cloud ML Engineer',
            'Microsoft Azure AI Engineer',
            'IBM Data Science Professional'
        ],
        'languages' => ['English (Native)', 'Mandarin (Fluent)'],
        'achievements' => [
            'Data Science Excellence Award',
            'Research Publication Award',
            'Industry Innovation Recognition'
        ],
        'social' => [
            'linkedin' => 'https://linkedin.com/in/michaeljohnson',
            'github' => 'https://github.com/michaeljohnson',
            'kaggle' => 'https://kaggle.com/michaeljohnson'
        ]
    ]
];

// Get teacher data or default to first teacher
$teacher = isset($teachers[$teacher_id]) ? $teachers[$teacher_id] : $teachers[1];

// Sample courses for this teacher
$teacher_courses = [
    [
        'id' => 1,
        'title' => 'Complete Web Development Bootcamp',
        'description' => 'Learn HTML, CSS, JavaScript, and PHP from scratch',
        'price' => 49.99,
        'students' => 450,
        'rating' => 4.8,
        'level' => 'Beginner',
        'duration' => '25 hours'
    ],
    [
        'id' => 2,
        'title' => 'Advanced JavaScript Masterclass',
        'description' => 'Master advanced JavaScript concepts and frameworks',
        'price' => 79.99,
        'students' => 320,
        'rating' => 4.9,
        'level' => 'Advanced',
        'duration' => '30 hours'
    ],
    [
        'id' => 3,
        'title' => 'React Development Course',
        'description' => 'Build modern web applications with React',
        'price' => 69.99,
        'students' => 280,
        'rating' => 4.7,
        'level' => 'Intermediate',
        'duration' => '28 hours'
    ]
];

// Sample reviews
$reviews = [
    [
        'name' => 'Alice Johnson',
        'rating' => 5,
        'comment' => 'Excellent instructor! Clear explanations and great examples.',
        'date' => '2024-01-15',
        'course' => 'Web Development Bootcamp'
    ],
    [
        'name' => 'Bob Smith',
        'rating' => 5,
        'comment' => 'Best programming course I have ever taken. Highly recommended!',
        'date' => '2024-01-10',
        'course' => 'JavaScript Masterclass'
    ],
    [
        'name' => 'Carol Williams',
        'rating' => 4,
        'comment' => 'Great content and very helpful instructor. Learned a lot!',
        'date' => '2024-01-05',
        'course' => 'React Development Course'
    ],
    [
        'name' => 'David Chen',
        'rating' => 5,
        'comment' => 'Amazing teacher! Makes complex topics easy to understand.',
        'date' => '2023-12-28',
        'course' => 'Web Development Bootcamp'
    ]
    ];
    ?>
<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?> - Teacher Profile</title>
    <link href="Web3.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">
    <style>
        .profile-page {
            margin-top: 80px;
            padding: 2rem 0;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 3rem 0;
        }
        
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .profile-top {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 2rem;
            align-items: center;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            border: 4px solid rgba(255, 255, 255, 0.3);
        }
        
        .profile-info h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .profile-specialization {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }
        
        .profile-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.1rem;
        }
        
        .rating-stars {
            color: #fbbf24;
        }
        
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            text-align: center;
        }
        
        .stat-box-header {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem;
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }
        
        .stat-value-header {
            font-size: 1.8rem;
            font-weight: bold;
            display: block;
        }
        
        .stat-label-header {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-top: 0.25rem;
        }
        
        .profile-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 2rem;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 3rem;
        }
        
        .main-content {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.08);
        }
        
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #8b5cf6;
        }
        
        .bio-section {
            margin-bottom: 2rem;
        }
        
        .bio-text {
            color: #6b7280;
            line-height: 1.7;
            font-size: 1.1rem;
        }
        
        .courses-grid {
            display: grid;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .course-card-small {
            background: #f8fafc;
            border: 1px solid rgba(139, 92, 246, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s;
        }
        
        .course-card-small:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.1);
        }
        
        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .course-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .course-price {
            font-size: 1.1rem;
            font-weight: 700;
            color: #8b5cf6;
        }
        
        .course-description {
            color: #6b7280;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        
        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            color: #6b7280;
        }
        
        .course-stats-small {
            display: flex;
            gap: 1rem;
        }
        
        .reviews-section {
            margin-top: 2rem;
        }
        
        .review-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid #8b5cf6;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .reviewer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .reviewer-name {
            font-weight: 600;
            color: #1f2937;
        }
        
        .review-rating {
            color: #fbbf24;
        }
        
        .review-comment {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 0.5rem;
        }
        
        .review-meta {
            font-size: 0.85rem;
            color: #9ca3af;
        }
        
        .sidebar-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.08);
        }
        
        .sidebar-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
        }
        
        .skills-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .skill-tag {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .info-list {
            list-style: none;
            padding: 0;
        }
        
        .info-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(139, 92, 246, 0.1);
            color: #6b7280;
        }
        
        .info-list li:last-child {
            border-bottom: none;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .social-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.4);
        }
        
        .contact-section {
            text-align: center;
        }
        
        .btn-contact {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            margin-bottom: 1rem;
        }
        
        .btn-contact:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.4);
        }
        
        @media (max-width: 768px) {
            .profile-top {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 1rem;
            }
            
            .profile-avatar {
                margin: 0 auto;
            }
            
            .profile-stats {
                grid-template-columns: repeat(3, 1fr);
                gap: 0.5rem;
            }
            
            .profile-content {
                grid-template-columns: 1fr;
                gap: 2rem;
                padding: 2rem 1rem;
            }
            
            .course-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .course-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .review-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <!-- Profile Header -->
    <section class="profile-header">
        <div class="profile-container">
            <div class="profile-top">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($teacher['first_name'], 0, 1) . substr($teacher['last_name'], 0, 1)); ?>
                </div>
                
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></h1>
                    <p class="profile-specialization"><?php echo htmlspecialchars($teacher['specialization']); ?> Expert</p>
                    <div class="profile-rating">
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?php echo ($i <= floor($teacher['rating'])) ? '' : (($i - $teacher['rating'] < 1) ? '-half-alt' : ' far'); ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span><?php echo $teacher['rating']; ?> (<?php echo rand(50, 200); ?> reviews)</span>
                    </div>
                </div>
                
                <div class="profile-stats">
                    <div class="stat-box-header">
                        <span class="stat-value-header"><?php echo $teacher['experience']; ?></span>
                        <div class="stat-label-header">Experience</div>
                    </div>
                    <div class="stat-box-header">
                        <span class="stat-value-header"><?php echo number_format($teacher['students']); ?></span>
                        <div class="stat-label-header">Students</div>
                    </div>
                    <div class="stat-box-header">
                        <span class="stat-value-header"><?php echo $teacher['courses']; ?></span>
                        <div class="stat-label-header">Courses</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Profile Content -->
    <div class="profile-content">
        <!-- Main Content -->
        <div class="main-content">
            <!-- About Section -->
            <div class="bio-section">
                <h2 class="section-title">About <?php echo htmlspecialchars($teacher['first_name']); ?></h2>
                <p class="bio-text"><?php echo htmlspecialchars($teacher['bio']); ?></p>
            </div>
            
            <!-- Courses Section -->
            <div class="courses-section">
                <h2 class="section-title">Courses by <?php echo htmlspecialchars($teacher['first_name']); ?></h2>
                <div class="courses-grid">
                    <?php foreach ($teacher_courses as $course): ?>
                        <div class="course-card-small">
                            <div class="course-header">
                                <div>
                                    <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                                    <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
                                </div>
                                <div class="course-price">$<?php echo number_format($course['price'], 2); ?></div>
                            </div>
                            <div class="course-meta">
                                <div class="course-stats-small">
                                    <span><i class="fas fa-users"></i> <?php echo $course['students']; ?> students</span>
                                    <span><i class="fas fa-star"></i> <?php echo $course['rating']; ?></span>
                                    <span><i class="fas fa-clock"></i> <?php echo $course['duration']; ?></span>
                                </div>
                                <span class="course-level"><?php echo $course['level']; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Reviews Section -->
            <div class="reviews-section">
                <h2 class="section-title">Student Reviews</h2>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <div class="reviewer-avatar">
                                    <?php echo strtoupper(substr($review['name'], 0, 2)); ?>
                                </div>
                                <div>
                                    <div class="reviewer-name"><?php echo htmlspecialchars($review['name']); ?></div>
                                    <div class="review-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo ($i <= $review['rating']) ? '' : ' far'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="review-comment"><?php echo htmlspecialchars($review['comment']); ?></p>
                        <div class="review-meta">
                            <?php echo htmlspecialchars($review['course']); ?> • <?php echo date('M j, Y', strtotime($review['date'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Skills Card -->
            <div class="sidebar-card">
                <h3 class="sidebar-title">Skills & Expertise</h3>
                <div class="skills-grid">
                    <?php foreach ($teacher['skills'] as $skill): ?>
                        <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Education Card -->
            <div class="sidebar-card">
                <h3 class="sidebar-title">Education</h3>
                <ul class="info-list">
                    <?php foreach ($teacher['education'] as $edu): ?>
                        <li><?php echo htmlspecialchars($edu); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Certifications Card -->
            <div class="sidebar-card">
                <h3 class="sidebar-title">Certifications</h3>
                <ul class="info-list">
                    <?php foreach ($teacher['certifications'] as $cert): ?>
                        <li><?php echo htmlspecialchars($cert); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Languages Card -->
            <div class="sidebar-card">
                <h3 class="sidebar-title">Languages</h3>
                <ul class="info-list">
                    <?php foreach ($teacher['languages'] as $lang): ?>
                        <li><?php echo htmlspecialchars($lang); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Contact Card -->
            <div class="sidebar-card contact-section">
                <h3 class="sidebar-title">Get in Touch</h3>
                <a href="mailto:<?php echo htmlspecialchars($teacher['email']); ?>" class="btn-contact">
                    <i class="fas fa-envelope"></i> Contact Teacher
                </a>
                
                <div class="social-links">
                    <?php foreach ($teacher['social'] as $platform => $url): ?>
                        <a href="<?php echo htmlspecialchars($url); ?>" class="social-link" target="_blank">
                            <i class="fab fa-<?php echo $platform; ?>"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>
