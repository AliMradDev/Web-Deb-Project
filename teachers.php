<?php
// teachers.php - Simple teachers listing page for 2nd year project
session_start();

// Sample teachers data (since database might not work)
$teachers = [
    [
        'id' => 1,
        'first_name' => 'Zein',
        'last_name' => 'Awada',
        'email' => 'john@edulearn.com',
        'specialization' => 'Web Development',
        'experience' => '8 years',
        'rating' => 4.8,
        'students' => 1250,
        'courses' => 12,
        'bio' => 'Experienced web developer with expertise in modern JavaScript frameworks and full-stack development.',
        'profile_image' => null,
        'skills' => ['JavaScript', 'React', 'Node.js', 'PHP', 'MySQL']
    ],
    [
        'id' => 2,
        'first_name' => 'Joe',
        'last_name' => 'Chamoun',
        'email' => 'sarah@edulearn.com',
        'specialization' => 'UI/UX Design',
        'experience' => '6 years',
        'rating' => 4.9,
        'students' => 980,
        'courses' => 8,
        'bio' => 'Creative designer passionate about user experience and modern design principles.',
        'profile_image' => null,
        'skills' => ['Figma', 'Adobe XD', 'Photoshop', 'Illustrator', 'Sketch']
    ],
    [
        'id' => 3,
        'first_name' => 'Michel',
        'last_name' => 'Bader',
        'email' => 'michael@edulearn.com',
        'specialization' => 'Data Science',
        'experience' => '10 years',
        'rating' => 4.7,
        'students' => 2100,
        'courses' => 15,
        'bio' => 'Data scientist with extensive experience in machine learning and statistical analysis.',
        'profile_image' => null,
        'skills' => ['Python', 'R', 'Machine Learning', 'Statistics', 'SQL']
    ],
    [
        'id' => 4,
        'first_name' => 'Husen',
        'last_name' => 'Rahal',
        'email' => 'emily@edulearn.com',
        'specialization' => 'Digital Marketing',
        'experience' => '5 years',
        'rating' => 4.6,
        'students' => 850,
        'courses' => 10,
        'bio' => 'Digital marketing expert specializing in social media marketing and SEO strategies.',
        'profile_image' => null,
        'skills' => ['SEO', 'Social Media', 'Google Ads', 'Analytics', 'Content Marketing']
    ],
    [
        'id' => 5,
        'first_name' => 'Ali',
        'last_name' => 'Kataya',
        'email' => 'david@edulearn.com',
        'specialization' => 'Mobile Development',
        'experience' => '7 years',
        'rating' => 4.8,
        'students' => 1450,
        'courses' => 14,
        'bio' => 'Mobile app developer with expertise in both iOS and Android development.',
        'profile_image' => null,
        'skills' => ['Swift', 'Kotlin', 'React Native', 'Flutter', 'Firebase']
    ],
    [
        'id' => 6,
        'first_name' => 'Hasan',
        'last_name' => 'bazal',
        'email' => 'lisa@edulearn.com',
        'specialization' => 'Business Strategy',
        'experience' => '12 years',
        'rating' => 4.9,
        'students' => 1680,
        'courses' => 9,
        'bio' => 'Business consultant and entrepreneur with extensive experience in startup strategy.',
        'profile_image' => null,
        'skills' => ['Strategy', 'Leadership', 'Finance', 'Marketing', 'Operations']
    ]
];

// Get filter parameters
$specialization_filter = isset($_GET['specialization']) ? trim($_GET['specialization']) : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? trim($_GET['sort']) : 'rating';

// Filter teachers
$filtered_teachers = $teachers;

if (!empty($specialization_filter)) {
    $filtered_teachers = array_filter($filtered_teachers, function($teacher) use ($specialization_filter) {
        return stripos($teacher['specialization'], $specialization_filter) !== false;
    });
}

if (!empty($search_query)) {
    $filtered_teachers = array_filter($filtered_teachers, function($teacher) use ($search_query) {
        return stripos($teacher['first_name'] . ' ' . $teacher['last_name'], $search_query) !== false ||
               stripos($teacher['specialization'], $search_query) !== false;
    });
}

// Sort teachers
switch ($sort_by) {
    case 'name':
        usort($filtered_teachers, function($a, $b) {
            return strcmp($a['first_name'], $b['first_name']);
        });
        break;
    case 'experience':
        usort($filtered_teachers, function($a, $b) {
            return (int)$b['experience'] - (int)$a['experience'];
        });
        break;
    case 'students':
        usort($filtered_teachers, function($a, $b) {
            return $b['students'] - $a['students'];
        });
        break;
    default: // rating
        usort($filtered_teachers, function($a, $b) {
            return $b['rating'] <=> $a['rating'];
        });
}

$specializations = array_unique(array_column($teachers, 'specialization'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Teachers - EduLearn Academy</title>
    <link href="Web3.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">
    <style>
        .teachers-page {
            margin-top: 80px;
            padding: 2rem 0;
        }
        
        .page-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }
        
        .page-header h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .page-header p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .stats-section {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-top: 2rem;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            display: block;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .filters-section {
            background: white;
            padding: 2rem 0;
            box-shadow: 0 2px 10px rgba(139, 92, 246, 0.1);
            border-bottom: 1px solid rgba(139, 92, 246, 0.1);
        }
        
        .filters-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .filter-select {
            padding: 0.5rem 1rem;
            border: 2px solid rgba(139, 92, 246, 0.1);
            border-radius: 8px;
            background: white;
            color: #1f2937;
            font-size: 0.9rem;
            min-width: 150px;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: #8b5cf6;
        }
        
        .search-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .search-input {
            padding: 0.5rem 1rem;
            border: 2px solid rgba(139, 92, 246, 0.1);
            border-radius: 25px;
            width: 250px;
            font-size: 0.9rem;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #8b5cf6;
        }
        
        .search-btn {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .results-info {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .teachers-container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .teachers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }
        
        .teacher-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.08);
            transition: all 0.3s;
            border: 1px solid rgba(139, 92, 246, 0.1);
        }
        
        .teacher-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(139, 92, 246, 0.15);
        }
        
        .teacher-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            padding: 2rem;
            text-align: center;
            color: white;
            position: relative;
        }
        
        .teacher-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto 1rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }
        
        .teacher-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .teacher-specialization {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .teacher-rating {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .teacher-content {
            padding: 2rem;
        }
        
        .teacher-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-box {
            text-align: center;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 8px;
        }
        
        .stat-value {
            font-size: 1.2rem;
            font-weight: 700;
            color: #8b5cf6;
        }
        
        .stat-label-small {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        
        .teacher-bio {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        .teacher-skills {
            margin-bottom: 1.5rem;
        }
        
        .skills-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.75rem;
        }
        
        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .skill-tag {
            background: #e0f2fe;
            color: #0369a1;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .teacher-actions {
            display: flex;
            gap: 1rem;
        }
        
        .btn-primary {
            flex: 1;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 0.75rem 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.4);
        }
        
        .btn-outline {
            flex: 1;
            background: transparent;
            color: #8b5cf6;
            border: 2px solid #8b5cf6;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
        }
        
        .btn-outline:hover {
            background: #8b5cf6;
            color: white;
        }
        
        .no-teachers {
            text-align: center;
            padding: 4rem 2rem;
            color: #6b7280;
        }
        
        .no-teachers i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #8b5cf6;
            opacity: 0.5;
        }
        
        .no-teachers h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }
        
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }
            
            .stats-section {
                flex-direction: column;
                gap: 1rem;
            }
            
            .filters-container {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
            }
            
            .filter-group {
                justify-content: center;
            }
            
            .search-input {
                width: 200px;
            }
            
            .teachers-grid {
                grid-template-columns: 1fr;
            }
            
            .teacher-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Meet Our Expert Teachers</h1>
            <p>Learn from industry professionals with years of experience and thousands of satisfied students.</p>
            
            <div class="stats-section">
                <div class="stat-item">
                    <span class="stat-number"><?php echo count($teachers); ?></span>
                    <span class="stat-label">Expert Teachers</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo array_sum(array_column($teachers, 'students')); ?></span>
                    <span class="stat-label">Students Taught</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo array_sum(array_column($teachers, 'courses')); ?></span>
                    <span class="stat-label">Courses Created</span>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Filters Section -->
    <section class="filters-section">
        <div class="filters-container">
            <form method="GET" action="" style="display: contents;">
                <div class="filter-group">
                    <select name="specialization" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Specializations</option>
                        <?php foreach ($specializations as $spec): ?>
                            <option value="<?php echo htmlspecialchars($spec); ?>" 
                                    <?php echo ($specialization_filter == $spec) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($spec); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="sort" class="filter-select" onchange="this.form.submit()">
                        <option value="rating" <?php echo ($sort_by == 'rating') ? 'selected' : ''; ?>>Highest Rated</option>
                        <option value="name" <?php echo ($sort_by == 'name') ? 'selected' : ''; ?>>Name A-Z</option>
                        <option value="experience" <?php echo ($sort_by == 'experience') ? 'selected' : ''; ?>>Most Experience</option>
                        <option value="students" <?php echo ($sort_by == 'students') ? 'selected' : ''; ?>>Most Students</option>
                    </select>
                </div>
                
                <div class="search-container">
                    <input type="text" name="search" class="search-input" 
                           placeholder="Search teachers..." 
                           value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </section>
    
    <!-- Results Info -->
    <div class="results-info">
        Showing <?php echo count($filtered_teachers); ?> teacher<?php echo count($filtered_teachers) != 1 ? 's' : ''; ?>
        <?php if (!empty($specialization_filter) || !empty($search_query)): ?>
            with your filters
        <?php endif; ?>
    </div>
    
    <!-- Teachers Container -->
    <div class="teachers-container">
        <?php if (!empty($filtered_teachers)): ?>
            <div class="teachers-grid">
                <?php foreach ($filtered_teachers as $teacher): ?>
                    <div class="teacher-card">
                        <div class="teacher-header">
                            <div class="teacher-rating">
                                <i class="fas fa-star"></i> <?php echo $teacher['rating']; ?>
                            </div>
                            
                            <div class="teacher-avatar">
                                <?php echo strtoupper(substr($teacher['first_name'], 0, 1) . substr($teacher['last_name'], 0, 1)); ?>
                            </div>
                            
                            <div class="teacher-name">
                                <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                            </div>
                            
                            <div class="teacher-specialization">
                                <?php echo htmlspecialchars($teacher['specialization']); ?>
                            </div>
                        </div>
                        
                        <div class="teacher-content">
                            <div class="teacher-stats">
                                <div class="stat-box">
                                    <div class="stat-value"><?php echo $teacher['experience']; ?></div>
                                    <div class="stat-label-small">Experience</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-value"><?php echo number_format($teacher['students']); ?></div>
                                    <div class="stat-label-small">Students</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-value"><?php echo $teacher['courses']; ?></div>
                                    <div class="stat-label-small">Courses</div>
                                </div>
                            </div>
                            
                            <p class="teacher-bio">
                                <?php echo htmlspecialchars($teacher['bio']); ?>
                            </p>
                            
                            <div class="teacher-skills">
                                <div class="skills-title">Skills & Expertise</div>
                                <div class="skills-list">
                                    <?php foreach (array_slice($teacher['skills'], 0, 4) as $skill): ?>
                                        <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($teacher['skills']) > 4): ?>
                                        <span class="skill-tag">+<?php echo count($teacher['skills']) - 4; ?> more</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="teacher-actions">
                                <a href="teacher-profile.php?id=<?php echo $teacher['id']; ?>" class="btn-primary">
                                    <i class="fas fa-user"></i> View Profile
                                </a>
                                <a href="courses.php?teacher=<?php echo $teacher['id']; ?>" class="btn-outline">
                                    <i class="fas fa-book"></i> View Courses
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-teachers">
                <i class="fas fa-search"></i>
                <h3>No Teachers Found</h3>
                <p>Try adjusting your search or filter criteria.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>