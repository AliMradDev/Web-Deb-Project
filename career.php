<?php
// become-teacher.php - Teacher application page with CV upload
session_start();

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $education = trim($_POST['education'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    $portfolio = trim($_POST['portfolio'] ?? '');
    $why_teach = trim($_POST['why_teach'] ?? '');
    
    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($specialization) || empty($bio)) {
        $error_message = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Handle CV upload
        $cv_uploaded = false;
        $cv_filename = '';
        
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/cvs/';
            
            // Create directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['pdf', 'doc', 'docx'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $cv_filename = 'cv_' . time() . '_' . uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $cv_filename;
                
                if (move_uploaded_file($_FILES['cv']['tmp_name'], $upload_path)) {
                    $cv_uploaded = true;
                } else {
                    $error_message = 'Failed to upload CV. Please try again.';
                }
            } else {
                $error_message = 'Please upload a valid CV file (PDF, DOC, or DOCX).';
            }
        }
        
        if (empty($error_message)) {
            // In a real application, you would save this to a database
            // For now, we'll just show a success message
            
            // Here you could save to database:
            /*
            $sql = "INSERT INTO teacher_applications (first_name, last_name, email, phone, specialization, 
                    experience, education, bio, linkedin, portfolio, why_teach, cv_filename, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
            */
            
            $success_message = 'Your application has been submitted successfully! We will review your application and get back to you within 3-5 business days.';
            
            // Clear form data on success
            $first_name = $last_name = $email = $phone = $specialization = $experience = '';
            $education = $bio = $linkedin = $portfolio = $why_teach = '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become a Teacher - EduLearn Academy</title>
    <link href="Web3.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">
    <style>
        .application-page {
            margin-top: 80px;
            padding: 2rem 0;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }
        
        .hero-content h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .hero-content p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto 2rem;
        }
        
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-top: 2rem;
        }
        
        .hero-stat {
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
        
        .benefits-section {
            padding: 4rem 0;
            background: #f8fafc;
        }
        
        .benefits-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .benefits-title {
            text-align: center;
            font-size: 2.5rem;
            color: #1f2937;
            margin-bottom: 3rem;
        }
        
        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .benefit-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.08);
            transition: all 0.3s;
        }
        
        .benefit-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(139, 92, 246, 0.15);
        }
        
        .benefit-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: white;
        }
        
        .benefit-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
        }
        
        .benefit-description {
            color: #6b7280;
            line-height: 1.6;
        }
        
        .application-section {
            padding: 4rem 0;
        }
        
        .application-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .application-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .application-header h2 {
            font-size: 2.5rem;
            color: #1f2937;
            margin-bottom: 1rem;
        }
        
        .application-header p {
            font-size: 1.1rem;
            color: #6b7280;
        }
        
        .application-form {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.08);
        }
        
        .form-section {
            margin-bottom: 2rem;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #8b5cf6;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #1f2937;
            font-weight: 500;
        }
        
        .required {
            color: #ef4444;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid rgba(139, 92, 246, 0.1);
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #8b5cf6;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .file-upload {
            position: relative;
        }
        
        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            border: 2px dashed rgba(139, 92, 246, 0.3);
            border-radius: 8px;
            background: rgba(139, 92, 246, 0.05);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-label:hover {
            border-color: #8b5cf6;
            background: rgba(139, 92, 246, 0.1);
        }
        
        .file-icon {
            font-size: 2rem;
            color: #8b5cf6;
            margin-right: 1rem;
        }
        
        .file-text {
            text-align: center;
        }
        
        .file-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .file-subtitle {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background: #f0f9ff;
            color: #10b981;
            border: 1px solid #10b981;
        }
        
        .alert-error {
            background: #fef2f2;
            color: #ef4444;
            border: 1px solid #ef4444;
        }
        
        .submit-section {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(139, 92, 246, 0.1);
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 1rem 3rem;
            border: none;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.4);
        }
        
        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .form-note {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .hero-stats {
                flex-direction: column;
                gap: 1rem;
            }
            
            .benefits-title {
                font-size: 2rem;
            }
            
            .application-form {
                padding: 2rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .application-header h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1>Teach What You Love</h1>
            <p>Join thousands of instructors teaching millions of students on EduLearn. Share your knowledge and earn money doing what you love.</p>
            
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="stat-number">50K+</span>
                    <span class="stat-label">Active Teachers</span>
                </div>
                <div class="hero-stat">
                    <span class="stat-number">2M+</span>
                    <span class="stat-label">Students Worldwide</span>
                </div>
                <div class="hero-stat">
                    <span class="stat-number">$5K+</span>
                    <span class="stat-label">Average Monthly Earnings</span>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Benefits Section -->
    <section class="benefits-section">
        <div class="benefits-container">
            <h2 class="benefits-title">Why Teach With Us?</h2>
            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3 class="benefit-title">Earn Money</h3>
                    <p class="benefit-description">Keep 70% of your course revenue. Top instructors earn over $10,000 per month teaching their expertise.</p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="benefit-title">Global Reach</h3>
                    <p class="benefit-description">Reach students from around the world. Our platform supports multiple languages and currencies.</p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h3 class="benefit-title">Complete Support</h3>
                    <p class="benefit-description">Get access to course creation tools, marketing support, and dedicated instructor assistance.</p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="benefit-title">Analytics & Insights</h3>
                    <p class="benefit-description">Track your performance with detailed analytics and insights to improve your courses.</p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="benefit-title">Flexible Schedule</h3>
                    <p class="benefit-description">Create content on your own schedule. Work from anywhere, anytime that suits you best.</p>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h3 class="benefit-title">Build Your Brand</h3>
                    <p class="benefit-description">Establish yourself as an expert in your field and build a personal brand that opens new opportunities.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Application Section -->
    <section class="application-section">
        <div class="application-container">
            <div class="application-header">
                <h2>Apply to Become a Teacher</h2>
                <p>Fill out the form below and upload your CV. We'll review your application and get back to you soon!</p>
            </div>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="application-form">
                <div class="form-note">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> All applications are manually reviewed. Please provide accurate information to speed up the approval process.
                </div>
                
                <!-- Personal Information -->
                <div class="form-section">
                    <h3 class="section-title">Personal Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name <span class="required">*</span></label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name <span class="required">*</span></label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Professional Information -->
                <div class="form-section">
                    <h3 class="section-title">Professional Background</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="specialization">Area of Expertise <span class="required">*</span></label>
                            <select id="specialization" name="specialization" required>
                                <option value="">Select your specialization</option>
                                <option value="Web Development" <?php echo ($specialization ?? '') === 'Web Development' ? 'selected' : ''; ?>>Web Development</option>
                                <option value="Mobile Development" <?php echo ($specialization ?? '') === 'Mobile Development' ? 'selected' : ''; ?>>Mobile Development</option>
                                <option value="Data Science" <?php echo ($specialization ?? '') === 'Data Science' ? 'selected' : ''; ?>>Data Science</option>
                                <option value="UI/UX Design" <?php echo ($specialization ?? '') === 'UI/UX Design' ? 'selected' : ''; ?>>UI/UX Design</option>
                                <option value="Digital Marketing" <?php echo ($specialization ?? '') === 'Digital Marketing' ? 'selected' : ''; ?>>Digital Marketing</option>
                                <option value="Business & Finance" <?php echo ($specialization ?? '') === 'Business & Finance' ? 'selected' : ''; ?>>Business & Finance</option>
                                <option value="Graphic Design" <?php echo ($specialization ?? '') === 'Graphic Design' ? 'selected' : ''; ?>>Graphic Design</option>
                                <option value="Photography" <?php echo ($specialization ?? '') === 'Photography' ? 'selected' : ''; ?>>Photography</option>
                                <option value="Music" <?php echo ($specialization ?? '') === 'Music' ? 'selected' : ''; ?>>Music</option>
                                <option value="Language Learning" <?php echo ($specialization ?? '') === 'Language Learning' ? 'selected' : ''; ?>>Language Learning</option>
                                <option value="Other" <?php echo ($specialization ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="experience">Years of Experience</label>
                            <select id="experience" name="experience">
                                <option value="">Select experience level</option>
                                <option value="1-2 years" <?php echo ($experience ?? '') === '1-2 years' ? 'selected' : ''; ?>>1-2 years</option>
                                <option value="3-5 years" <?php echo ($experience ?? '') === '3-5 years' ? 'selected' : ''; ?>>3-5 years</option>
                                <option value="6-10 years" <?php echo ($experience ?? '') === '6-10 years' ? 'selected' : ''; ?>>6-10 years</option>
                                <option value="10+ years" <?php echo ($experience ?? '') === '10+ years' ? 'selected' : ''; ?>>10+ years</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="education">Education Background</label>
                        <textarea id="education" name="education" placeholder="List your degrees, certifications, and relevant education..."><?php echo htmlspecialchars($education ?? ''); ?></textarea>
                    </div>
                </div>
                
                <!-- CV Upload -->
                <div class="form-section">
                    <h3 class="section-title">Upload Your CV</h3>
                    
                    <div class="form-group">
                        <div class="file-upload">
                            <input type="file" id="cv" name="cv" class="file-input" accept=".pdf,.doc,.docx">
                            <label for="cv" class="file-label">
                                <i class="fas fa-cloud-upload-alt file-icon"></i>
                                <div class="file-text">
                                    <div class="file-title">Click to upload your CV</div>
                                    <div class="file-subtitle">PDF, DOC, or DOCX (Max 5MB)</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Information -->
                <div class="form-section">
                    <h3 class="section-title">Additional Information</h3>
                    
                    <div class="form-group">
                        <label for="bio">Tell us about yourself <span class="required">*</span></label>
                        <textarea id="bio" name="bio" placeholder="Describe your background, expertise, and what makes you a great teacher..." required><?php echo htmlspecialchars($bio ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="linkedin">LinkedIn Profile</label>
                            <input type="url" id="linkedin" name="linkedin" value="<?php echo htmlspecialchars($linkedin ?? ''); ?>" placeholder="https://linkedin.com/in/yourprofile">
                        </div>
                        <div class="form-group">
                            <label for="portfolio">Portfolio/Website</label>
                            <input type="url" id="portfolio" name="portfolio" value="<?php echo htmlspecialchars($portfolio ?? ''); ?>" placeholder="https://yourportfolio.com">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="why_teach">Why do you want to teach on EduLearn?</label>
                        <textarea id="why_teach" name="why_teach" placeholder="Share your motivation for teaching and what you hope to achieve..."><?php echo htmlspecialchars($why_teach ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="submit-section">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Submit Application
                    </button>
                    <p style="margin-top: 1rem; color: #6b7280; font-size: 0.9rem;">
                        By submitting this application, you agree to our Terms of Service and Privacy Policy.
                    </p>
                </div>
            </form>
        </div>
    </section>
    
    <?php include 'footer.php'; ?>
    
    <script>
        // File upload feedback
        document.getElementById('cv').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const label = document.querySelector('.file-label');
            
            if (file) {
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                const fileName = file.name;
                
                if (fileSize > 5) {
                    alert('File size must be less than 5MB');
                    e.target.value = '';
                    return;
                }
                
                label.innerHTML = `
                    <i class="fas fa-check-circle file-icon" style="color: #10b981;"></i>
                    <div class="file-text">
                        <div class="file-title">${fileName}</div>
                        <div class="file-subtitle">Size: ${fileSize}MB</div>
                    </div>
                `;
            }
        });
        
        // Form validation
        document.querySelector('.application-form').addEventListener('submit', function(e) {
            const requiredFields = ['first_name', 'last_name', 'email', 'specialization', 'bio'];
            let hasErrors = false;
            
            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    input.style.borderColor = '#ef4444';
                    hasErrors = true;
                } else {
                    input.style.borderColor = 'rgba(139, 92, 246, 0.1)';
                }
            });
            
            if (hasErrors) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    </script>
</body>
</html>