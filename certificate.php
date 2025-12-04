<?php
session_start();
require_once 'database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'] ?? null;

if (!$course_id) {
    header('Location: my-courses.php?error=invalid_course');
    exit();
}

try {
    $pdo = getDbConnection();
    
    // Verify user is enrolled and passed the exam
    $stmt = $pdo->prepare("
        SELECT e.*, cl.title as course_title, cl.description
        FROM course_enrollments e 
        LEFT JOIN course_list cl ON e.course_id = cl.id 
        WHERE e.user_id = ? AND e.course_id = ? AND e.exam_status = 'passed'
    ");
    $stmt->execute([$user_id, $course_id]);
    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$enrollment) {
        header('Location: my-courses.php?error=certificate_not_available');
        exit();
    }
    
    // Get user details
    $stmt = $pdo->prepare("SELECT username, email FROM users_acc WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('Location: my-courses.php?error=user_not_found');
        exit();
    }
    
} catch (Exception $e) {
    header('Location: my-courses.php?error=database_error');
    exit();
}

// Handle download request
if (isset($_GET['download']) && $_GET['download'] === 'pdf') {
    
    // Path to your certificate PDF file
    $pdf_file = 'certificate_edulearn.pdf';
    
    // Check if file exists
    if (!file_exists($pdf_file)) {
        die('Certificate file not found. Please contact administrator.');
    }
    
    // Generate custom filename
    $course_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $enrollment['course_title']);
    $username = preg_replace('/[^a-zA-Z0-9_-]/', '_', $user['username']);
    $custom_filename = "Certificate_{$course_name}_{$username}.pdf";
    
    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $custom_filename . '"');
    header('Content-Length: ' . filesize($pdf_file));
    header('Cache-Control: private');
    header('Pragma: private');
    header('Expires: 0');
    
    // Output the file
    readfile($pdf_file);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Certificate - EduLearn Academy</title>
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

        .certificate-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .certificate-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }

        .certificate-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .certificate-header .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .certificate-content {
            padding: 3rem 2rem;
            text-align: center;
        }

        .course-info {
            background: #f8fafc;
            padding: 2rem;
            border-radius: 12px;
            margin: 2rem 0;
        }

        .course-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1rem;
        }

        .achievement-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .achievement-item {
            text-align: center;
        }

        .achievement-label {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            font-weight: 600;
        }

        .achievement-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: #10b981;
        }

        .congratulations {
            background: linear-gradient(135deg, #10b981, #059669);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 1.3rem;
            font-weight: 600;
            margin: 2rem 0;
        }

        .download-section {
            background: #f0fdf4;
            padding: 2rem;
            border-radius: 12px;
            border: 2px solid #10b981;
            margin: 2rem 0;
        }

        .download-section h3 {
            color: #065f46;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
            font-size: 1.1rem;
            margin: 0.5rem;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .btn-outline {
            background: transparent;
            color: #6b7280;
            border: 2px solid #e5e7eb;
        }

        .btn-outline:hover {
            border-color: #10b981;
            color: #10b981;
        }

        .actions {
            text-align: center;
            margin-top: 2rem;
        }

        .user-info {
            color: #6b7280;
            font-size: 0.9rem;
            margin-top: 1rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .certificate-header {
                padding: 2rem 1rem;
            }

            .certificate-header h1 {
                font-size: 2rem;
            }

            .certificate-content {
                padding: 2rem 1rem;
            }

            .achievement-details {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="certificate-card">
            <div class="certificate-header">
                <div class="icon">
                    <i class="fas fa-certificate"></i>
                </div>
                <h1>🎉 Congratulations!</h1>
                <p>You have successfully completed the course and earned your certificate</p>
            </div>

            <div class="certificate-content">
                <div class="congratulations">
                    Well done, <?php echo htmlspecialchars($user['username']); ?>!
                </div>

                <div class="course-info">
                    <h2 class="course-title"><?php echo htmlspecialchars($enrollment['course_title']); ?></h2>
                    <p><?php echo htmlspecialchars($enrollment['description'] ?? 'Course completed successfully'); ?></p>
                </div>

                <div class="achievement-details">
                    <div class="achievement-item">
                        <div class="achievement-label">Final Score</div>
                        <div class="achievement-value"><?php echo $enrollment['exam_score']; ?>%</div>
                    </div>
                    <div class="achievement-item">
                        <div class="achievement-label">Completion Date</div>
                        <div class="achievement-value">
                            <?php echo date('M j, Y', strtotime($enrollment['completion_date'])); ?>
                        </div>
                    </div>
                    <div class="achievement-item">
                        <div class="achievement-label">Time Invested</div>
                        <div class="achievement-value"><?php echo $enrollment['time_spent'] ?? 0; ?> hours</div>
                    </div>
                    <div class="achievement-item">
                        <div class="achievement-label">Status</div>
                        <div class="achievement-value">PASSED</div>
                    </div>
                </div>

                <div class="download-section">
                    <h3><i class="fas fa-download"></i> Download Your Certificate</h3>
                    <p>Click the button below to download your official certificate in PDF format.</p>
                    
                    <a href="?course_id=<?php echo $course_id; ?>&download=pdf" class="btn btn-success">
                        <i class="fas fa-file-pdf"></i> Download Certificate PDF
                    </a>
                </div>

                <div class="user-info">
                    <p><strong>Student:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Course ID:</strong> <?php echo $course_id; ?></p>
                </div>

                <div class="actions">
                    <a href="my-courses.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to My Courses
                    </a>
                    <a href="courses.php" class="btn btn-outline">
                        <i class="fas fa-book"></i> Browse More Courses
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Optional: Auto-start download after 3 seconds
        // setTimeout(() => {
        //     window.location.href = '?course_id=<?php echo $course_id; ?>&download=pdf';
        // }, 3000);
    </script>
</body>
</html>