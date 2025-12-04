<?php
session_start();

echo '<pre>';
if (isset($_SESSION['user'])) {
    print_r($_SESSION['user']);
} else {
    echo "User session not set.";
}
echo '</pre>';
require_once 'database.php';

// Get database connection using your existing function
try {
    $pdo = getDbConnection();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get filter parameters
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$level = $_GET['level'] ?? '';
$sort = $_GET['sort'] ?? 'popularity';

// Build query based on filters
$query = "SELECT * FROM course_list WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR description LIKE ? OR instructor LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
}

if (!empty($level)) {
    $query .= " AND level = ?";
    $params[] = $level;
}

// Add sorting (with fallbacks for missing columns)
switch ($sort) {
    case 'rating':
        $query .= " ORDER BY rating DESC";
        break;
    case 'newest':
        $query .= " ORDER BY created_at DESC";
        break;
    case 'title_asc':
        $query .= " ORDER BY title ASC";
        break;
    case 'title_desc':
        $query .= " ORDER BY title DESC";
        break;
    default:
        // Fallback to id DESC if total_students doesn't exist
        $query .= " ORDER BY id DESC";
}

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $courses = [];
    $error_message = "Error loading courses: " . $e->getMessage();
}

// Get categories for filter
try {
    $stmt = $pdo->query("SELECT DISTINCT category FROM course_list WHERE category IS NOT NULL ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $categories = ['web', 'data', 'design', 'business', 'programming'];
}

// Check user subscription status
$user_logged_in = isset($_SESSION['user_id']);
$user_subscribed = isset($_SESSION['subscription']) && $_SESSION['subscription'] !== 'free';
$subscription_type = $_SESSION['subscription'] ?? 'free';

// Check if user is enrolled in each course (if logged in)
$enrolled_courses = [];
if ($user_logged_in) {
    try {
        $stmt = $pdo->prepare("SELECT course_id FROM course_enrollments WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $enrolled_courses = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        $enrolled_courses = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Courses - EduLearn Academy</title>
    <link href="Web3.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Page Styles */
        body {
            margin-top: 80px;
            background: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .courses-hero {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }

        .courses-hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .courses-hero p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Subscription Notice */
        .subscription-notice {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 1rem;
            margin: 2rem 0;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.2);
        }

        .subscription-notice.free {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .subscription-notice.subscribed {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .subscription-notice h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.2rem;
        }

        .subscription-notice p {
            margin: 0;
            opacity: 0.9;
        }

        .upgrade-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 0.75rem;
            display: inline-block;
            transition: all 0.3s;
        }

        .upgrade-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }

        /* Filters Section */
        .filters-section {
            background: white;
            padding: 2rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .filters-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

        .filter-group select,
        .filter-group input {
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #8b5cf6;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding-right: 3rem;
        }

        .search-box button {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            background: #8b5cf6;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 0.5rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        .search-box button:hover {
            background: #7c3aed;
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

        /* Results Info */
        .results-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 2rem;
        }

        .results-count {
            color: #6b7280;
        }

        .results-sort {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .results-sort select {
            padding: 0.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }

        /* Courses Grid */
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            padding: 0 2rem 4rem;
        }

        .course-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.08);
            transition: all 0.3s ease;
            position: relative;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(139, 92, 246, 0.15);
        }

        .course-card.locked {
            opacity: 0.7;
        }

        .course-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            position: relative;
        }

        .course-level {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: rgba(255, 255, 255, 0.9);
            color: #1f2937;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .lock-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 0.5rem;
            border-radius: 50%;
            font-size: 1.5rem;
        }

        .course-content {
            padding: 1.5rem;
        }

        .course-category {
            color: #8b5cf6;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .course-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.75rem;
            line-height: 1.3;
        }

        .course-description {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .course-instructor {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f3f4f6;
        }

        .course-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stars {
            color: #fbbf24;
            font-size: 0.9rem;
        }

        .rating-score {
            font-weight: 600;
            color: #1f2937;
        }

        .students-count {
            color: #6b7280;
            font-size: 0.85rem;
        }

        .access-status {
            font-size: 1.2rem;
            font-weight: bold;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 1rem;
        }

        .access-included {
            background: #d1fae5;
            color: #065f46;
        }

        .access-locked {
            background: #fee2e2;
            color: #991b1b;
        }

        .access-enrolled {
            background: #dbeafe;
            color: #1e40af;
        }

        .course-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            flex: 1;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }

        .btn-primary {
            background: #8b5cf6;
            color: white;
        }

        .btn-primary:hover {
            background: #7c3aed;
            transform: translateY(-1px);
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-outline {
            background: transparent;
            color: #8b5cf6;
            border: 2px solid #8b5cf6;
        }

        .btn-outline:hover {
            background: #8b5cf6;
            color: white;
        }

        .btn-disabled {
            background: #9ca3af;
            color: white;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .btn-disabled:hover {
            background: #9ca3af;
            transform: none;
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            color: #6b7280;
        }

        .no-results i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }

        .no-results h3 {
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
            margin: 2rem;
            text-align: center;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .courses-hero h1 {
                font-size: 2rem;
            }

            .filters-container {
                grid-template-columns: 1fr;
            }

            .results-info {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .courses-grid {
                grid-template-columns: 1fr;
                padding: 0 1rem 4rem;
            }

            .course-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Hero Section -->
    <section class="courses-hero">
        <div class="container">
            <h1>Explore Our Courses</h1>
            <p>Discover thousands of courses taught by industry experts. Start learning today and advance your career.</p>
        </div>
    </section>

    <!-- Subscription Notice -->
    <?php if (!$user_logged_in): ?>
        <div class="container">
            <div class="subscription-notice">
                <h3><i class="fas fa-info-circle"></i> Login Required</h3>
                <p>Please log in to view course access and enrollment options.</p>
                <a href="login.php" class="upgrade-btn">Login Now</a>
            </div>
        </div>
    <?php elseif (!$user_subscribed): ?>
        <div class="container">
            <div class="subscription-notice free">
                <h3><i class="fas fa-lock"></i> Premium Subscription Required</h3>
                <p>Upgrade to a Pro or University subscription to access our premium course library.</p>
                <a href="pricing.php" class="upgrade-btn">Upgrade Now</a>
            </div>
        </div>
    <?php else: ?>
        <div class="container">
            <div class="subscription-notice subscribed">
                <h3><i class="fas fa-crown"></i> <?php echo ucfirst($subscription_type); ?> Member</h3>
                <p>You have full access to all courses! Start learning today.</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Filters Section -->
    <section class="filters-section">
        <div class="container">
            <form method="GET" action="courses.php" class="filters-container">
                <div class="filter-group">
                    <label for="search">Search Courses</label>
                    <div class="search-box">
                        <input type="text" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search by title, instructor, or keyword">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </div>

                <div class="filter-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" 
                                    <?php echo $category === $cat ? 'selected' : ''; ?>>
                                <?php echo ucfirst(htmlspecialchars($cat)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="level">Difficulty Level</label>
                    <select id="level" name="level">
                        <option value="">All Levels</option>
                        <option value="beginner" <?php echo $level === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                        <option value="intermediate" <?php echo $level === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                        <option value="advanced" <?php echo $level === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
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
    </section>

    <!-- Results Info -->
    <div class="results-info">
        <div class="results-count">
            <?php 
            $total_courses = count($courses);
            echo $total_courses . ' course' . ($total_courses !== 1 ? 's' : '') . ' found';
            ?>
        </div>
        <div class="results-sort">
            <label for="sort">Sort by:</label>
            <select id="sort" name="sort" onchange="updateSort()">
                <option value="popularity" <?php echo $sort === 'popularity' ? 'selected' : ''; ?>>Most Popular</option>
                <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                <option value="title_asc" <?php echo $sort === 'title_asc' ? 'selected' : ''; ?>>Title: A to Z</option>
                <option value="title_desc" <?php echo $sort === 'title_desc' ? 'selected' : ''; ?>>Title: Z to A</option>
            </select>
        </div>
    </div>

    <!-- Error Message -->
    <?php if (isset($error_message)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Courses Grid -->
    <?php if (empty($courses)): ?>
        <div class="no-results">
            <i class="fas fa-search"></i>
            <h3>No courses found</h3>
            <p>Try adjusting your search criteria or browse all courses.</p>
            <button onclick="clearFilters()" class="btn btn-primary" style="margin-top: 1rem;">
                View All Courses
            </button>
        </div>
    <?php else: ?>
        <div class="courses-grid">
            <?php foreach ($courses as $course): ?>
                <?php 
                $is_enrolled = in_array($course['id'], $enrolled_courses);
                $can_access = $user_logged_in && $user_subscribed;
                ?>
                <div class="course-card <?php echo !$can_access ? 'locked' : ''; ?>">
                    <div class="course-image">
                        <span class="course-level"><?php echo ucfirst($course['level'] ?? 'Beginner'); ?></span>
                        <i class="fas fa-play-circle"></i>
                        <?php if (!$can_access): ?>
                            <div class="lock-overlay">
                                <i class="fas fa-lock"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="course-content">
                        <div class="course-category">
                            <?php echo htmlspecialchars($course['category'] ?? 'General'); ?>
                        </div>
                        
                        <h3 class="course-title">
                            <?php echo htmlspecialchars($course['title']); ?>
                        </h3>
                        
                        <p class="course-description">
                            <?php echo htmlspecialchars($course['description'] ?? ''); ?>
                        </p>
                        
                        <div class="course-instructor">
                            <i class="fas fa-user"></i>
                            <span><?php echo htmlspecialchars($course['instructor'] ?? 'Expert Instructor'); ?></span>
                        </div>
                        
                        <div class="course-meta">
                            <div class="course-rating">
                                <div class="stars">
                                    <?php 
                                    $rating = $course['rating'] ?? 0;
                                    for ($i = 1; $i <= 5; $i++): 
                                    ?>
                                        <i class="fas fa-star<?php echo $i <= $rating ? '' : ' far'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-score"><?php echo number_format($rating, 1); ?></span>
                            </div>
                            <div class="students-count">
                                <?php echo number_format($course['total_students'] ?? 0); ?> students
                            </div>
                        </div>
                        
                        <!-- Access Status -->
                        <div class="access-status <?php 
                            if (!$user_logged_in) {
                                echo 'access-locked';
                            } elseif ($is_enrolled) {
                                echo 'access-enrolled';
                            } elseif ($can_access) {
                                echo 'access-included';
                            } else {
                                echo 'access-locked';
                            }
                        ?>">
                            <?php if (!$user_logged_in): ?>
                                <i class="fas fa-sign-in-alt"></i> Login Required
                            <?php elseif ($is_enrolled): ?>
                                <i class="fas fa-check-circle"></i> Enrolled
                            <?php elseif ($can_access): ?>
                                <i class="fas fa-crown"></i> Available
                            <?php else: ?>
                                <i class="fas fa-lock"></i> Subscription Required
                            <?php endif; ?>
                        </div>
                        
                        <div class="course-actions">
                            <?php if (!$user_logged_in): ?>
                                <a href="login.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt"></i> Login to Access
                                </a>
                            <?php elseif ($is_enrolled): ?>
                                <a href="course-viewer.php?id=<?php echo $course['id']; ?>" class="btn btn-success">
                                    <i class="fas fa-play"></i> Continue Learning
                                </a>
                                <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn btn-outline">
                                    <i class="fas fa-info-circle"></i> Details
                                </a>
                            <?php elseif ($can_access): ?>
                                <a href="enroll.php?course_id=<?php echo $course['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Enroll Now
                                </a>
                                <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn btn-outline">
                                    <i class="fas fa-info-circle"></i> View Details
                                </a>
                            <?php else: ?>
                                <a href="pricing.php" class="btn btn-disabled">
                                    <i class="fas fa-lock"></i> Upgrade Required
                                </a>
                                <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn btn-outline">
                                    <i class="fas fa-eye"></i> Preview
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <script>
        function clearFilters() {
            window.location.href = 'courses.php';
        }

        function updateSort() {
            const sort = document.getElementById('sort').value;
            const url = new URL(window.location);
            url.searchParams.set('sort', sort);
            window.location.href = url.toString();
        }

        // Auto-submit form when filters change
        document.querySelectorAll('#category, #level').forEach(element => {
            element.addEventListener('change', function() {
                this.form.submit();
            });
        });

        // Search on Enter key
        document.getElementById('search').addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                this.form.submit();
            }
        });

        // Prevent disabled buttons from being clicked
        document.querySelectorAll('.btn-disabled').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
            });
        });
    </script>
</body>
</html>