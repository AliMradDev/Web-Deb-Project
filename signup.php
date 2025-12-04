<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: Home.php");
    exit();
}

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'database.php';
    
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'student';
    $subscription_type = $_POST['subscription_type'] ?? 'free';
    $university_id = trim($_POST['university_id'] ?? '');
    $university_name = trim($_POST['university_name'] ?? '');
    $agree_terms = isset($_POST['agree_terms']);
    $subscribe_newsletter = isset($_POST['subscribe_newsletter']);
    
    // Validation
    $errors = [];
    
    if (empty($first_name)) $errors[] = 'First name is required.';
    if (empty($last_name)) $errors[] = 'Last name is required.';
    if (empty($username)) $errors[] = 'Username is required.';
    if (empty($email)) $errors[] = 'Email is required.';
    if (empty($password)) $errors[] = 'Password is required.';
    if (empty($confirm_password)) $errors[] = 'Please confirm your password.';
    if (!$agree_terms) $errors[] = 'You must agree to the Terms of Service.';
    
    // Advanced validation
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (!empty($username) && (strlen($username) < 3 || strlen($username) > 20)) {
        $errors[] = 'Username must be between 3 and 20 characters.';
    }
    
    if (!empty($username) && !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores.';
    }
    
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match.';
        }
    }
    
    // University subscription validation
    if ($subscription_type === 'university' && empty($university_id)) {
        $errors[] = 'University ID is required for university subscription.';
    }
    
    if (!empty($phone) && !preg_match('/^[\+]?[1-9][\d]{7,14}$/', $phone)) {
        $errors[] = 'Please enter a valid phone number.';
    }
    
    if (empty($errors)) {
        try {
            $pdo = getDbConnection();
            
            // Check if username or email already exists in users_acc table
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users_acc WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetchColumn() > 0) {
                $error_message = 'Username or email already exists.';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Generate email verification token
                $email_verification_token = bin2hex(random_bytes(32));
                
                // Prepare data for insertion
                $user_data = [
                    'username' => $username,
                    'email' => $email,
                    'password' => $hashed_password,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'phone' => $phone,
                    'role' => $role,
                    'subscription_type' => $subscription_type,
                    'email_verification_token' => $email_verification_token,
                    'email_verified' => 0,
                    'status' => 'active'
                ];
                
                // Build dynamic INSERT query based on existing columns
                // First, check what columns exist in users_acc
                $stmt = $pdo->query("DESCRIBE users_acc");
                $existing_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                // Filter user_data to only include existing columns
                $filtered_data = [];
                $placeholders = [];
                $values = [];
                
                foreach ($user_data as $column => $value) {
                    if (in_array($column, $existing_columns)) {
                        $filtered_data[$column] = $value;
                        $placeholders[] = '?';
                        $values[] = $value;
                    }
                }
                
                // Add created_at if column exists
                if (in_array('created_at', $existing_columns)) {
                    $filtered_data['created_at'] = date('Y-m-d H:i:s');
                    $placeholders[] = '?';
                    $values[] = $filtered_data['created_at'];
                } elseif (in_array('date_created', $existing_columns)) {
                    $filtered_data['date_created'] = date('Y-m-d H:i:s');
                    $placeholders[] = '?';
                    $values[] = $filtered_data['date_created'];
                }
                
                // Build and execute the query
                $columns = implode(', ', array_keys($filtered_data));
                $placeholders_str = implode(', ', $placeholders);
                
                $sql = "INSERT INTO users_acc ($columns) VALUES ($placeholders_str)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($values);
                
                $user_id = $pdo->lastInsertId();
                
                // Handle teacher application
                if ($role === 'teacher') {
                    try {
                        // Check if teacher_application table exists
                        $stmt = $pdo->query("SHOW TABLES LIKE 'teacher_application'");
                        if ($stmt->rowCount() > 0) {
                            $stmt = $pdo->prepare("
                                INSERT INTO teacher_application (user_id, status, application_date) 
                                VALUES (?, 'pending', NOW())
                            ");
                            $stmt->execute([$user_id]);
                        } elseif ($pdo->query("SHOW TABLES LIKE 'teachers'")->rowCount() > 0) {
                            $stmt = $pdo->prepare("
                                INSERT INTO teachers (user_id, status, application_date) 
                                VALUES (?, 'pending', NOW())
                            ");
                            $stmt->execute([$user_id]);
                        }
                    } catch (Exception $e) {
                        // Teacher table insert failed, but user creation succeeded
                        error_log("Teacher application insert failed: " . $e->getMessage());
                    }
                }
                
                // Handle subscription
                try {
                    // Check if subscription table exists
                    $stmt = $pdo->query("SHOW TABLES LIKE 'subscription'");
                    if ($stmt->rowCount() > 0) {
                        // Get subscription table structure
                        $stmt = $pdo->query("DESCRIBE subscription");
                        $sub_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        
                        $sub_data = [];
                        if (in_array('user_id', $sub_columns)) $sub_data['user_id'] = $user_id;
                        if (in_array('subscription_type', $sub_columns)) $sub_data['subscription_type'] = $subscription_type;
                        if (in_array('plan_type', $sub_columns)) $sub_data['plan_type'] = $subscription_type;
                        if (in_array('status', $sub_columns)) $sub_data['status'] = 'active';
                        if (in_array('start_date', $sub_columns)) $sub_data['start_date'] = date('Y-m-d');
                        if (in_array('created_at', $sub_columns)) $sub_data['created_at'] = date('Y-m-d H:i:s');
                        
                        if (!empty($sub_data)) {
                            $sub_columns_str = implode(', ', array_keys($sub_data));
                            $sub_placeholders = implode(', ', array_fill(0, count($sub_data), '?'));
                            
                            $stmt = $pdo->prepare("INSERT INTO subscription ($sub_columns_str) VALUES ($sub_placeholders)");
                            $stmt->execute(array_values($sub_data));
                        }
                    }
                } catch (Exception $e) {
                    // Subscription insert failed, but user creation succeeded
                    error_log("Subscription insert failed: " . $e->getMessage());
                }
                
                // Handle university verification
                if ($subscription_type === 'university' && !empty($university_id)) {
                    try {
                        // Check if university verification table exists
                        $stmt = $pdo->query("SHOW TABLES LIKE 'university_verifications'");
                        if ($stmt->rowCount() == 0) {
                            $stmt = $pdo->query("SHOW TABLES LIKE 'university_verification'");
                        }
                        
                        if ($stmt->rowCount() > 0) {
                            $table_name = $stmt->fetchColumn();
                            $stmt = $pdo->prepare("
                                INSERT INTO $table_name (user_id, university_id, university_name, status, submitted_at) 
                                VALUES (?, ?, ?, 'pending', NOW())
                            ");
                            $stmt->execute([$user_id, $university_id, $university_name]);
                        }
                    } catch (Exception $e) {
                        error_log("University verification insert failed: " . $e->getMessage());
                    }
                }
                
                // Success! Redirect to login
                $_SESSION['registration_success'] = true;
                $_SESSION['registered_email'] = $email;
                header("Location: login.php?registered=true");
                exit();
            }
        } catch (PDOException $e) {
            $error_message = 'Registration failed. Please try again.';
            error_log("Registration error: " . $e->getMessage());
        }
    } else {
        $error_message = implode(' ', $errors);
    }
}

// Get selected plan from URL
$selected_plan = $_GET['plan'] ?? 'free';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - EduLearn Academy</title>
    <link href="Web3.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Auth container styles (same as login) */
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gradient);
            position: relative;
            overflow: hidden;
            padding: 2rem 0;
        }
        
        .auth-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" patternUnits="userSpaceOnUse" width="100" height="100"><circle cx="25" cy="25" r="1" fill="%23ffffff" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .auth-card {
            background: var(--white);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
            margin: 2rem;
            position: relative;
            z-index: 2;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .auth-header h1 {
            color: var(--text-dark);
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .auth-header p {
            color: var(--text-light);
            font-size: 1rem;
        }
        
        .logo-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-color);
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid rgba(139, 92, 246, 0.1);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            background: var(--white);
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon input {
            padding-left: 2.5rem;
        }
        
        .input-icon i {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }
        
        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }
        
        .strength-bar {
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 0.25rem;
        }
        
        .strength-bar-fill {
            height: 100%;
            transition: all 0.3s ease;
        }
        
        .strength-weak { background: var(--error-color); }
        .strength-fair { background: var(--warning-color); }
        .strength-good { background: var(--success-color); }
        
        .university-section {
            display: none;
            background: var(--light-bg);
            padding: 1.5rem;
            border-radius: 12px;
            border: 2px solid var(--primary-color);
            margin-bottom: 1.5rem;
        }
        
        .university-section.active {
            display: block;
        }
        
        .university-info {
            background: #e0f2fe;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-color);
        }
        
        .university-info h4 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin: 0;
            margin-top: 0.25rem;
        }
        
        .checkbox-group label {
            margin: 0;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .checkbox-group a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .checkbox-group a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 1px solid;
        }
        
        .alert-success {
            background: #f0f9ff;
            color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .alert-error {
            background: #fef2f2;
            color: var(--error-color);
            border-color: var(--error-color);
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(139, 92, 246, 0.1);
        }
        
        .auth-footer p {
            color: var(--text-light);
            margin-bottom: 1rem;
        }
        
        .auth-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }
        
        .auth-footer a:hover {
            color: var(--secondary-color);
        }

        /* Default CSS variables if not defined */
        :root {
            --primary-color: #8b5cf6;
            --secondary-color: #7c3aed;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --white: #ffffff;
            --light-bg: #f8fafc;
            --error-color: #ef4444;
            --warning-color: #f59e0b;
            --success-color: #10b981;
            --transition: all 0.3s ease;
            --gradient: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        .btn {
            background: var(--primary-color);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-primary {
            background: var(--primary-color);
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .auth-card {
                padding: 2rem;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="Home.php" class="logo-link">
                    <i class="fas fa-graduation-cap"></i>
                    EduLearn Academy
                </a>
                <h1>Create Your Account</h1>
                <p>Join thousands of learners and start your journey today</p>
            </div>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="signupForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($first_name ?? ''); ?>" 
                                   placeholder="First Name" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($last_name ?? ''); ?>" 
                                   placeholder="Last Name" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-icon">
                        <i class="fas fa-at"></i>
                        <input type="text" id="username" name="username" 
                               value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                               placeholder="Choose a username" required>
                    </div>
                    <small style="color: var(--text-light); font-size: 0.85rem;">
                        3-20 characters, letters, numbers, and underscores only
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                               placeholder="Enter your email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number (Optional)</label>
                    <div class="input-icon">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($phone ?? ''); ?>" 
                               placeholder="+1 (555) 123-4567">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" 
                                   placeholder="Create password" required>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('password')"></i>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar">
                                <div class="strength-bar-fill" id="strengthBar"></div>
                            </div>
                            <span id="strengthText">Password strength</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   placeholder="Confirm password" required>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm_password')"></i>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="role">I want to join as</label>
                    <select id="role" name="role">
                        <option value="student" <?php echo ($role ?? 'student') === 'student' ? 'selected' : ''; ?>>
                            Student - I want to learn
                        </option>
                        <option value="teacher" <?php echo ($role ?? '') === 'teacher' ? 'selected' : ''; ?>>
                            Teacher - I want to teach (requires approval)
                        </option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="subscription_type">Subscription Plan</label>
                    <select id="subscription_type" name="subscription_type" onchange="toggleUniversitySection()">
                        <option value="free" <?php echo $selected_plan === 'free' ? 'selected' : ''; ?>>
                            Free Plan - Basic access
                        </option>
                        <option value="pro" <?php echo $selected_plan === 'pro' ? 'selected' : ''; ?>>
                            Pro Plan - $19.99/month
                        </option>
                        <option value="university" <?php echo $selected_plan === 'university' ? 'selected' : ''; ?>>
                            University Plan - $9.99/month (Verification required)
                        </option>
                    </select>
                </div>
                
                <!-- University Verification Section -->
                <div class="university-section" id="universitySection">
                    <div class="university-info">
                        <h4><i class="fas fa-university"></i> University Verification</h4>
                        <p>University subscription requires verification of your student or faculty status. You'll start with a free account until verification is complete.</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="university_id">University ID / Student ID</label>
                        <div class="input-icon">
                            <i class="fas fa-id-card"></i>
                            <input type="text" id="university_id" name="university_id" 
                                   value="<?php echo htmlspecialchars($university_id ?? ''); ?>" 
                                   placeholder="Enter your university or student ID">
                        </div>
                        <small style="color: var(--text-light); font-size: 0.85rem;">
                            Enter your official university ID, student ID, or faculty ID number
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="university_name">University/Institution Name</label>
                        <div class="input-icon">
                            <i class="fas fa-university"></i>
                            <input type="text" id="university_name" name="university_name" 
                                   placeholder="Full name of your university or institution">
                        </div>
                    </div>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="agree_terms" name="agree_terms" required>
                    <label for="agree_terms">
                        I agree to the <a href="terms.php" target="_blank">Terms of Service</a> 
                        and <a href="privacy.php" target="_blank">Privacy Policy</a>
                    </label>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="subscribe_newsletter" name="subscribe_newsletter">
                    <label for="subscribe_newsletter">
                        Subscribe to our newsletter for course updates and learning tips
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem;">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account?</p>
                <a href="login.php">Sign in here</a>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = passwordInput.parentNode.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        function toggleUniversitySection() {
            const subscriptionType = document.getElementById('subscription_type').value;
            const universitySection = document.getElementById('universitySection');
            const universityId = document.getElementById('university_id');
            
            if (subscriptionType === 'university') {
                universitySection.classList.add('active');
                universityId.required = true;
            } else {
                universitySection.classList.remove('active');
                universityId.required = false;
            }
        }
        
        // Password strength checker
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            let score = 0;
            const checks = {
                length: password.length >= 8,
                lowercase: /[a-z]/.test(password),
                uppercase: /[A-Z]/.test(password),
                numbers: /\d/.test(password),
                special: /[^a-zA-Z0-9]/.test(password)
            };
            
            score = Object.values(checks).filter(Boolean).length;
            
            let strength = 'weak';
            let width = '20%';
            let className = 'strength-weak';
            
            if (score >= 4) {
                strength = 'strong';
                width = '100%';
                className = 'strength-good';
            } else if (score >= 3) {
                strength = 'fair';
                width = '60%';
                className = 'strength-fair';
            }
            
            strengthBar.style.width = width;
            strengthBar.className = 'strength-bar-fill ' + className;
            strengthText.textContent = `Password strength: ${strength}`;
        }
        
        // Username validation
        function validateUsername(username) {
            const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
            return usernameRegex.test(username);
        }
        
        // Real-time validation
        document.getElementById('password').addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });
        
        document.getElementById('username').addEventListener('input', function() {
            const username = this.value;
            const isValid = validateUsername(username);
            
            if (username.length > 0 && !isValid) {
                this.style.borderColor = 'var(--error-color)';
            } else {
                this.style.borderColor = 'rgba(139, 92, 246, 0.1)';
            }
        });
        
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword.length > 0 && password !== confirmPassword) {
                this.style.borderColor = 'var(--error-color)';
            } else {
                this.style.borderColor = 'rgba(139, 92, 246, 0.1)';
            }
        });
        
        // Form submission validation
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const agreeTerms = document.getElementById('agree_terms').checked;
            const subscriptionType = document.getElementById('subscription_type').value;
            const universityId = document.getElementById('university_id').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long');
                return false;
            }
            
            if (!agreeTerms) {
                e.preventDefault();
                alert('Please agree to the Terms of Service');
                return false;
            }
            
            if (subscriptionType === 'university' && !universityId.trim()) {
                e.preventDefault();
                alert('University ID is required for university subscription');
                return false;
            }
        });
        
        // Initialize university section based on selected plan
        document.addEventListener('DOMContentLoaded', function() {
            toggleUniversitySection();
        });
    </script>
</body>
</html>