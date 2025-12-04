<?php
session_start();
require_once 'database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'] ?? $_POST['course_id'] ?? null;

if (!$course_id) {
    header('Location: my-courses.php?error=invalid_course');
    exit();
}

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
        header('Location: my-courses.php?error=not_enrolled');
        exit();
    }
    
    // Handle form submission for course retake
    if ($_POST && isset($_POST['confirm_retake'])) {
        try {
            // Begin transaction
            $pdo->beginTransaction();
            
            // Reset course progress and exam data
            $stmt = $pdo->prepare("
                UPDATE course_enrollments 
                SET progress_percentage = 0,
                    status = 'in_progress',
                    exam_score = NULL,
                    exam_status = NULL,
                    completion_date = NULL,
                    time_spent = 0,
                    last_accessed = NOW()
                WHERE user_id = ? AND course_id = ?
            ");
            
            $result = $stmt->execute([$user_id, $course_id]);
            
            if ($result && $stmt->rowCount() > 0) {
                $pdo->commit();
                // Redirect to course viewer with success message
                header('Location: course-viewer.php?id=' . $course_id . '&message=retake_started');
                exit();
            } else {
                $pdo->rollback();
                $error_message = "Failed to reset course progress. Please try again.";
            }
            
        } catch (PDOException $e) {
            $pdo->rollback();
            $error_message = "Database error: " . $e->getMessage();
        }
    }
    
} catch (Exception $e) {
    $error_message = "System error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retake Course - EduLearn Academy</title>
    <link href="Web3.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin-top: 80px;
            background: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
        }

        .retake-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .retake-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .retake-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .course-info {
            padding: 2rem;
        }

        .course-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1rem;
        }

        .current-status {
            background: #f3f4f6;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .current-status h3 {
            color: #1f2937;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
        }

        .status-item {
            text-align: center;
        }

        .status-label {
            font-size: 0.8rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            font-weight: 600;
        }

        .status-value {
            font-weight: 700;
            font-size: 1.2rem;
            color: #1f2937;
        }

        .retake-warning {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .retake-warning h3 {
            color: #92400e;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .retake-warning ul {
            color: #92400e;
            margin-left: 1.5rem;
        }

        .retake-warning li {
            margin-bottom: 0.5rem;
        }

        .retake-actions {
            padding: 2rem;
            border-top: 1px solid #f3f4f6;
        }

        .btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .btn-warning:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.4);
        }

        .btn-outline {
            background: transparent;
            color: #6b7280;
            border: 2px solid #e5e7eb;
        }

        .btn-outline:hover {
            border-color: #f59e0b;
            color: #f59e0b;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .error-message {
            background: #fef2f2;
            color: #dc2626;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
        }

        .confirmation-section {
            background: #f0f9ff;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid #3b82f6;
        }

        .confirmation-section h4 {
            color: #1e40af;
            margin-bottom: 1rem;
        }

        .confirmation-section label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #1e40af;
            cursor: pointer;
        }

        .confirmation-section input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #3b82f6;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .status-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="retake-card">
            <div class="retake-header">
                <h1><i class="fas fa-redo"></i> Retake Course</h1>
                <p>Start over and improve your performance</p>
            </div>

            <div class="course-info">
                <!-- Error Message -->
                <?php if (isset($error_message)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <h2 class="course-title"><?php echo htmlspecialchars($enrollment['course_title']); ?></h2>

                <!-- Current Status -->
                <div class="current-status">
                    <h3><i class="fas fa-chart-line"></i> Current Progress</h3>
                    <div class="status-grid">
                        <div class="status-item">
                            <div class="status-label">Progress</div>
                            <div class="status-value"><?php echo $enrollment['progress_percentage']; ?>%</div>
                        </div>
                        <div class="status-item">
                            <div class="status-label">Status</div>
                            <div class="status-value" style="color: <?php 
                                echo match($enrollment['status']) {
                                    'completed' => '#10b981',
                                    'failed' => '#ef4444',
                                    'in_progress' => '#f59e0b',
                                    default => '#6b7280'
                                };
                            ?>">
                                <?php echo ucfirst($enrollment['status']); ?>
                            </div>
                        </div>
                        <?php if ($enrollment['exam_score'] !== null): ?>
                            <div class="status-item">
                                <div class="status-label">Exam Score</div>
                                <div class="status-value" style="color: <?php echo ($enrollment['exam_status'] === 'passed') ? '#10b981' : '#ef4444'; ?>">
                                    <?php echo $enrollment['exam_score']; ?>%
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="status-item">
                            <div class="status-label">Time Spent</div>
                            <div class="status-value"><?php echo $enrollment['time_spent'] ?? 0; ?>h</div>
                        </div>
                    </div>
                </div>

                <!-- Warning -->
                <div class="retake-warning">
                    <h3><i class="fas fa-exclamation-triangle"></i> Important Notice</h3>
                    <p><strong>Retaking this course will:</strong></p>
                    <ul>
                        <li>Reset your progress to 0%</li>
                        <li>Change status to "In Progress"</li>
                        <li>Clear your exam score</li>
                        <li>Clear your exam status</li>
                        <li>Clear completion date</li>
                        <li>Reset time spent to 0</li>
                        <li>Allow you to retake the final exam</li>
                    </ul>
                    <p style="margin-top: 1rem;"><strong>This action cannot be undone!</strong></p>
                </div>

                <!-- Confirmation -->
                <div class="confirmation-section">
                    <h4>Confirmation Required</h4>
                    <label>
                        <input type="checkbox" id="confirmRetake" required>
                        I understand that retaking this course will reset all my progress and exam scores.
                    </label>
                </div>
            </div>

            <div class="retake-actions">
                <form method="POST" id="retakeForm">
                    <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_id); ?>">
                    <button type="submit" name="confirm_retake" class="btn btn-warning" id="retakeButton" disabled>
                        <i class="fas fa-redo"></i> Yes, Reset This Course
                    </button>
                </form>
                
                <a href="my-courses.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Cancel - Back to My Courses
                </a>
            </div>
        </div>
    </div>

    <script>
        // Enable/disable retake button based on confirmation checkbox
        const confirmCheckbox = document.getElementById('confirmRetake');
        const retakeButton = document.getElementById('retakeButton');
        
        confirmCheckbox.addEventListener('change', function() {
            retakeButton.disabled = !this.checked;
            retakeButton.style.opacity = this.checked ? '1' : '0.6';
        });
        
        // FIXED: Don't disable button until AFTER form submits
        document.getElementById('retakeForm').addEventListener('submit', function(e) {
            if (!confirmCheckbox.checked) {
                e.preventDefault();
                alert('Please confirm by checking the checkbox.');
                return false;
            }
            
            if (!confirm('FINAL CONFIRMATION: This will permanently reset all your progress and exam scores. Are you absolutely sure?')) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state AFTER confirmation but allow form to submit
            setTimeout(() => {
                retakeButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resetting Course...';
                retakeButton.disabled = true;
            }, 50);
        });
    </script>
</body>
</html>