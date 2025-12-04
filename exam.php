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

$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 1;
$user_id = $_SESSION['user_id'];

try {
    $pdo = getDbConnection();
    
    // Check if user is enrolled in this course
    $stmt = $pdo->prepare("
        SELECT e.*, cl.title as course_title 
        FROM course_enrollments e 
        LEFT JOIN course_list cl ON e.course_id = cl.id 
        WHERE e.user_id = ? AND e.course_id = ?
    ");
    $stmt->execute([$user_id, $course_id]);
    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$enrollment) {
        header('Location: courses.php');
        exit();
    }
    
    // Check if course is completed (100% progress)
    if ($enrollment['progress_percentage'] < 100) {
        header('Location: course-viewer.php?id=' . $course_id);
        exit();
    }
    
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Course data (same as before)
$courses = [
    1 => [
        'title' => 'Complete Web Development Bootcamp',
        'questions' => [
            [
                'id' => 1,
                'question' => 'Which HTML tag is used to create the largest heading?',
                'options' => ['<h1>', '<h6>', '<header>', '<title>'],
                'correct' => 0
            ],
            [
                'id' => 2,
                'question' => 'What does CSS stand for?',
                'options' => ['Computer Style Sheets', 'Cascading Style Sheets', 'Creative Style Sheets', 'Colorful Style Sheets'],
                'correct' => 1
            ],
            [
                'id' => 3,
                'question' => 'Which JavaScript method is used to select an element by its ID?',
                'options' => ['getElementByClass()', 'getElementById()', 'selectElement()', 'findElementById()'],
                'correct' => 1
            ],
            [
                'id' => 4,
                'question' => 'What is the correct way to link an external CSS file?',
                'options' => ['<link href="style.css">', '<css src="style.css">', '<link rel="stylesheet" href="style.css">', '<style src="style.css">'],
                'correct' => 2
            ],
            [
                'id' => 5,
                'question' => 'Which HTTP status code indicates a successful request?',
                'options' => ['404', '500', '200', '301'],
                'correct' => 2
            ]
        ]
    ],
    2 => [
        'title' => 'Data Science with Python',
        'questions' => [
            [
                'id' => 1,
                'question' => 'Which library is primarily used for data manipulation in Python?',
                'options' => ['NumPy', 'Pandas', 'Matplotlib', 'Scikit-learn'],
                'correct' => 1
            ],
            [
                'id' => 2,
                'question' => 'What does the head() method do in Pandas?',
                'options' => ['Returns the last 5 rows', 'Returns the first 5 rows', 'Returns column headers', 'Returns data types'],
                'correct' => 1
            ],
            [
                'id' => 3,
                'question' => 'Which visualization library is built on top of Matplotlib?',
                'options' => ['Plotly', 'Seaborn', 'Bokeh', 'Altair'],
                'correct' => 1
            ],
            [
                'id' => 4,
                'question' => 'What is the purpose of train_test_split in machine learning?',
                'options' => ['To clean data', 'To visualize data', 'To divide data into training and testing sets', 'To normalize data'],
                'correct' => 2
            ],
            [
                'id' => 5,
                'question' => 'Which metric is commonly used for regression problems?',
                'options' => ['Accuracy', 'Precision', 'Mean Squared Error', 'F1-Score'],
                'correct' => 2
            ]
        ]
    ]
];

// Use course title from database if available, otherwise fall back to hardcoded
$course = $courses[$course_id] ?? $courses[1];
if ($enrollment['course_title']) {
    $course['title'] = $enrollment['course_title'];
}

// Handle form submission
$exam_submitted = false;
$exam_results = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_exam'])) {
    $exam_submitted = true;
    $user_answers = $_POST['answers'] ?? [];
    $correct_count = 0;
    $total_questions = count($course['questions']);
    
    foreach ($course['questions'] as $q) {
        $user_answer = isset($user_answers[$q['id']]) ? (int)$user_answers[$q['id']] : -1;
        if ($user_answer === $q['correct']) {
            $correct_count++;
        }
    }
    
    $score_percentage = round(($correct_count / $total_questions) * 100, 2);
    $passed = $score_percentage >= 70; // 70% passing grade
    
    // Update enrollment record with exam results
    try {
        $stmt = $pdo->prepare("
            UPDATE course_enrollments 
            SET exam_score = ?, 
                exam_status = ?, 
                status = ?,
                last_accessed = NOW()
            WHERE user_id = ? AND course_id = ?
        ");
        
        $exam_status = $passed ? 'passed' : 'failed';
        $course_status = $passed ? 'completed' : 'failed';
        
        $stmt->execute([$score_percentage, $exam_status, $course_status, $user_id, $course_id]);
        
    } catch (PDOException $e) {
        // Handle database error
        error_log("Exam result save error: " . $e->getMessage());
    }
    
    $exam_results = [
        'score' => $score_percentage,
        'correct' => $correct_count,
        'total' => $total_questions,
        'passed' => $passed,
        'user_answers' => $user_answers
    ];
}

// FIXED: Check if exam was already taken
$exam_already_taken = ($enrollment['exam_score'] !== null && $enrollment['exam_score'] !== '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Exam - <?php echo htmlspecialchars($course['title']); ?></title>
    <link href="Web3.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Add cache busting -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
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
            margin-top: 80px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Header */
        .exam-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin: -2rem -2rem 3rem -2rem;
        }

        .exam-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .exam-info {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }

        /* Navigation */
        .nav-breadcrumb {
            background: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #6b7280;
        }

        .breadcrumb a {
            color: #8b5cf6;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* Exam Content */
        .exam-container {
            background: white;
            border-radius: 16px;
            padding: 3rem;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.08);
            margin-bottom: 2rem;
        }

        .exam-instructions {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 3rem;
        }

        .exam-instructions h3 {
            color: #92400e;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .exam-instructions ul {
            color: #92400e;
            margin-left: 1.5rem;
        }

        .exam-instructions li {
            margin-bottom: 0.5rem;
        }

        /* Questions */
        .question {
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .question:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .question-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .question-number {
            background: #8b5cf6;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .question-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
        }

        .options {
            margin-left: 3rem;
        }

        .option {
            margin-bottom: 1rem;
        }

        .option label {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .option label:hover {
            border-color: #8b5cf6;
            background: #f3f4f6;
        }

        .option input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: #8b5cf6;
        }

        .option input[type="radio"]:checked + .option-content {
            border-color: #8b5cf6;
            background: #ede9fe;
        }

        .option-content {
            flex: 1;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.3s;
        }

        .option-text {
            font-size: 1rem;
        }

        /* Results */
        .exam-results {
            background: white;
            border-radius: 16px;
            padding: 3rem;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.08);
            text-align: center;
        }

        .result-header {
            margin-bottom: 3rem;
        }

        .result-score {
            font-size: 4rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .result-score.passed {
            color: #10b981;
        }

        .result-score.failed {
            color: #ef4444;
        }

        .result-status.failed {
            color: #ef4444;
        }

        .result-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }

        .result-item {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
        }

        .result-item h4 {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .result-item .value {
            font-size: 2rem;
            font-weight: bold;
            color: #1f2937;
        }

        .certificate-section {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-top: 3rem;
        }

        .certificate-section h3 {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        .exam-actions {
            text-align: center;
            margin-top: 3rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Progress Timer */
        .exam-timer {
            position: fixed;
            top: 100px;
            right: 20px;
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            z-index: 1000;
        }

        .timer-icon {
            color: #8b5cf6;
        }

        /* Already Taken Message */
        .already-taken {
            background: white;
            border-radius: 16px;
            padding: 3rem;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.08);
            text-align: center;
        }

        .already-taken-icon {
            font-size: 4rem;
            color: #10b981;
            margin-bottom: 2rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .exam-header {
                margin: -1rem -1rem 3rem -1rem;
                padding: 2rem 1rem;
            }

            .exam-header h1 {
                font-size: 2rem;
            }

            .exam-info {
                gap: 1rem;
            }

            .exam-container {
                padding: 2rem;
            }

            .options {
                margin-left: 1rem;
            }

            .result-score {
                font-size: 3rem;
            }

            .exam-timer {
                position: static;
                margin-bottom: 2rem;
                justify-content: center;
            }

            .exam-actions {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <?php if (!$exam_submitted && !$exam_already_taken): ?>
        <!-- Exam Timer -->
        <div class="exam-timer">
            <i class="fas fa-clock timer-icon"></i>
            <span id="timer">30:00</span>
        </div>
    <?php endif; ?>

    <!-- Exam Header -->
    <div class="exam-header">
        <div class="container">
            <h1>Final Exam</h1>
            <p><?php echo htmlspecialchars($course['title']); ?></p>
            
            <?php if (!$exam_submitted && !$exam_already_taken): ?>
                <div class="exam-info">
                    <div class="info-item">
                        <i class="fas fa-question-circle"></i>
                        <span><?php echo count($course['questions']); ?> Questions</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <span>30 Minutes</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-percentage"></i>
                        <span>70% to Pass</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <!-- Breadcrumb -->
        <nav class="nav-breadcrumb">
            <div class="breadcrumb">
                <a href="my-courses.php">My Courses</a>
                <i class="fas fa-chevron-right"></i>
                <a href="course-viewer.php?id=<?php echo $course_id; ?>">Course Viewer</a>
                <i class="fas fa-chevron-right"></i>
                <span>Final Exam</span>
            </div>
        </nav>

        <?php if ($exam_already_taken && !$exam_submitted): ?>
            <!-- Already Taken Message -->
            <div class="already-taken">
                <div class="already-taken-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Exam Already Completed</h2>
                <p>You have already taken this exam.</p>
                <div class="result-details">
                    <div class="result-item">
                        <h4>Your Score</h4>
                        <div class="value"><?php echo $enrollment['exam_score']; ?>%</div>
                    </div>
                    <div class="result-item">
                        <h4>Status</h4>
                        <div class="value" style="color: <?php echo ($enrollment['exam_status'] === 'passed') ? '#10b981' : '#ef4444'; ?>">
                            <?php echo strtoupper($enrollment['exam_status'] ?? 'unknown'); ?>
                        </div>
                    </div>
                </div>
                
                <?php if ($enrollment['exam_status'] === 'passed'): ?>
                    <div class="certificate-section">
                        <h3><i class="fas fa-certificate"></i> Certificate Earned!</h3>
                        <p>Congratulations! You can download your certificate.</p>
                        <a href="certificate.php?course_id=<?php echo $course_id; ?>" class="btn btn-success" style="margin-top: 1rem;">
                            <i class="fas fa-download"></i> Download Certificate
                        </a>
                    </div>
                <?php endif; ?>

                <div class="exam-actions">
                    <a href="course-viewer.php?id=<?php echo $course_id; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Course
                    </a>
                    <a href="my-courses.php" class="btn btn-secondary">
                        <i class="fas fa-book"></i> My Courses
                    </a>
                </div>
            </div>

        <?php elseif ($exam_submitted): ?>
            <!-- Exam Results -->
            <div class="exam-results">
                <div class="result-header">
                    <div class="result-score <?php echo $exam_results['passed'] ? 'passed' : 'failed'; ?>">
                        <?php echo $exam_results['score']; ?>%
                    </div>
                    <div class="result-status <?php echo $exam_results['passed'] ? 'passed' : 'failed'; ?>">
                        <?php echo $exam_results['passed'] ? 'CONGRATULATIONS! YOU PASSED!' : 'SORRY, YOU DID NOT PASS'; ?>
                    </div>
                    <p><?php echo $exam_results['passed'] ? 'Well done! You can now download your certificate.' : 'You need 70% or higher to pass. You can retake the course.'; ?></p>
                </div>

                <div class="result-details">
                    <div class="result-item">
                        <h4>Correct Answers</h4>
                        <div class="value"><?php echo $exam_results['correct']; ?>/<?php echo $exam_results['total']; ?></div>
                    </div>
                    <div class="result-item">
                        <h4>Score</h4>
                        <div class="value"><?php echo $exam_results['score']; ?>%</div>
                    </div>
                    <div class="result-item">
                        <h4>Passing Grade</h4>
                        <div class="value">70%</div>
                    </div>
                </div>

                <?php if ($exam_results['passed']): ?>
                    <div class="certificate-section">
                        <h3><i class="fas fa-certificate"></i> Certificate Earned!</h3>
                        <p>You have successfully completed the course and earned your certificate.</p>
                        <a href="certificate.php?course_id=<?php echo $course_id; ?>" class="btn btn-success" style="margin-top: 1rem;">
                            <i class="fas fa-download"></i> Download Certificate
                        </a>
                    </div>
                <?php endif; ?>

                <div class="exam-actions">
                    <a href="course-viewer.php?id=<?php echo $course_id; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Course
                    </a>
                    <a href="my-courses.php" class="btn btn-secondary">
                        <i class="fas fa-book"></i> My Courses
                    </a>
                    <?php if (!$exam_results['passed']): ?>
                        <a href="courses.php" class="btn btn-primary">
                            <i class="fas fa-redo"></i> Browse Courses
                        </a>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- Exam Form -->
            <div class="exam-container">
                <div class="exam-instructions">
                    <h3><i class="fas fa-info-circle"></i> Exam Instructions</h3>
                    <ul>
                        <li>You have 30 minutes to complete this exam</li>
                        <li>You need to score 70% or higher to pass</li>
                        <li>Each question has only one correct answer</li>
                        <li>You can only take this exam once</li>
                        <li>Make sure you have a stable internet connection</li>
                    </ul>
                </div>

                <form method="POST" id="examForm">
                    <?php foreach ($course['questions'] as $index => $question): ?>
                        <div class="question">
                            <div class="question-header">
                                <div class="question-number"><?php echo $index + 1; ?></div>
                                <div class="question-text"><?php echo htmlspecialchars($question['question']); ?></div>
                            </div>
                            
                            <div class="options">
                                <?php foreach ($question['options'] as $opt_index => $option): ?>
                                    <div class="option">
                                        <label for="q<?php echo $question['id']; ?>_<?php echo $opt_index; ?>">
                                            <input type="radio" 
                                                   id="q<?php echo $question['id']; ?>_<?php echo $opt_index; ?>" 
                                                   name="answers[<?php echo $question['id']; ?>]" 
                                                   value="<?php echo $opt_index; ?>" 
                                                   required>
                                            <div class="option-content">
                                                <span class="option-text"><?php echo htmlspecialchars($option); ?></span>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="exam-actions">
                        <button type="submit" name="submit_exam" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-paper-plane"></i> Submit Exam
                        </button>
                        <a href="course-viewer.php?id=<?php echo $course_id; ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script>
        <?php if (!$exam_submitted && !$exam_already_taken): ?>
        // Timer functionality
        let timeLeft = 30 * 60; // 30 minutes in seconds
        const timerElement = document.getElementById('timer');
        
        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                // Auto-submit when time is up
                document.getElementById('examForm').submit();
            } else if (timeLeft <= 300) { // Last 5 minutes
                timerElement.style.color = '#ef4444';
            } else if (timeLeft <= 600) { // Last 10 minutes
                timerElement.style.color = '#f59e0b';
            }
            
            timeLeft--;
        }
        
        // Update timer every second
        const timerInterval = setInterval(updateTimer, 1000);
        updateTimer(); // Initial call
        
        // Confirm before leaving page
        window.addEventListener('beforeunload', function(e) {
            e.preventDefault();
            e.returnValue = '';
        });
        
        // Remove confirmation when submitting
        document.getElementById('examForm').addEventListener('submit', function() {
            window.removeEventListener('beforeunload', function() {});
            clearInterval(timerInterval);
        });
        
        // Progress tracking
        const questions = document.querySelectorAll('.question');
        const submitBtn = document.getElementById('submitBtn');
        
        function updateProgress() {
            let answered = 0;
            questions.forEach(question => {
                const inputs = question.querySelectorAll('input[type="radio"]:checked');
                if (inputs.length > 0) {
                    answered++;
                }
            });
            
            if (answered === questions.length) {
                submitBtn.style.background = '#10b981';
                submitBtn.innerHTML = '<i class="fas fa-check"></i> All Questions Answered - Submit Exam';
            } else {
                submitBtn.style.background = '';
                submitBtn.innerHTML = `<i class="fas fa-paper-plane"></i> Submit Exam (${answered}/${questions.length} answered)`;
            }
        }
        
        // Add event listeners to all radio buttons
        document.querySelectorAll('input[type="radio"]').forEach(input => {
            input.addEventListener('change', updateProgress);
        });
        
        updateProgress(); // Initial call
        <?php endif; ?>
    </script>
</body>
</html> 



<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pdo = getDbConnection();
$user_id = $_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 1;

// Fetch enrollment info for this user & course
$stmt = $pdo->prepare("
    SELECT e.*, cl.title AS course_title 
    FROM course_enrollments e 
    LEFT JOIN course_list cl ON e.course_id = cl.id 
    WHERE e.user_id = ? AND e.course_id = ?
");
$stmt->execute([$user_id, $course_id]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    header('Location: courses.php');
    exit();
}

if ($enrollment['progress_percentage'] < 100) {
    header('Location: course-viewer.php?id=' . $course_id);
    exit();
}

// Fetch all exam questions for this course
$stmt = $pdo->prepare("SELECT * FROM exam WHERE course_id = ? ORDER BY id ASC");
$stmt->execute([$course_id]);
$questions = $stmt->fetchAll();

if (!$questions) {
    die("No questions found for this course exam.");
}

// Format questions array for form and grading
$formatted_questions = [];
foreach ($questions as $q) {
    $formatted_questions[] = [
        'id' => $q['id'],
        'question' => $q['question_text'],
        'options' => [
            $q['option_a'],
            $q['option_b'],
            $q['option_c'],
            $q['option_d']
        ],
        'correct' => array_search($q['correct_option'], ['option_a', 'option_b', 'option_c', 'option_d'])
    ];
}

// Check if exam was already taken
$exam_already_taken = ($enrollment['exam_score'] !== null && $enrollment['exam_score'] !== '');

// Handle form submission and autograding
$exam_submitted = false;
$exam_results = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_exam'])) {
    $exam_submitted = true;
    $user_answers = $_POST['answers'] ?? [];
    $correct_count = 0;
    $total_questions = count($formatted_questions);
    
    foreach ($formatted_questions as $q) {
        $user_answer = isset($user_answers[$q['id']]) ? (int)$user_answers[$q['id']] : -1;
        if ($user_answer === $q['correct']) {
            $correct_count++;
        }
    }
    
    $score_percentage = round(($correct_count / $total_questions) * 100, 2);
    $passed = $score_percentage >= 70; // passing grade 70%
    
    // Update enrollment with exam results
    $stmt = $pdo->prepare("
        UPDATE course_enrollments 
        SET exam_score = ?, 
            exam_status = ?, 
            status = ?,
            last_accessed = NOW()
        WHERE user_id = ? AND course_id = ?
    ");
    $exam_status = $passed ? 'passed' : 'failed';
    $course_status = $passed ? 'completed' : 'failed';
    
    $stmt->execute([$score_percentage, $exam_status, $course_status, $user_id, $course_id]);
    
    $exam_results = [
        'score' => $score_percentage,
        'correct' => $correct_count,
        'total' => $total_questions,
        'passed' => $passed,
        'user_answers' => $user_answers
    ];
}