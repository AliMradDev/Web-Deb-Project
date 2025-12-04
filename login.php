<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: Home.php");
    exit();
}

$error_message = '';
$success_message = '';

// Check for registration success message
if (isset($_GET['registered']) && $_GET['registered'] === 'true') {
    $success_message = 'Registration successful! Please log in with your credentials.';
}

if (isset($_SESSION['registration_success'])) {
    $success_message = 'Registration successful! Please log in with your credentials.';
    unset($_SESSION['registration_success']);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'database.php';
    
    $login_field = trim($_POST['login_field'] ?? $_POST['email'] ?? $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    // Validation
    if (empty($login_field)) {
        $error_message = 'Please enter your email or username.';
    } elseif (empty($password)) {
        $error_message = 'Please enter your password.';
    } else {
        try {
            $pdo = getDbConnection();
            
            // Check what columns exist in users_acc table
            $stmt = $pdo->query("DESCRIBE users_acc");
            $existing_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Build query based on available columns
            $query_parts = [];
            $params = [];
            
            // Check if user can login with email
            if (in_array('email', $existing_columns)) {
                $query_parts[] = "email = ?";
                $params[] = $login_field;
            }
            
            // Check if user can login with username
            if (in_array('username', $existing_columns)) {
                $query_parts[] = "username = ?";
                $params[] = $login_field;
            }
            
            if (empty($query_parts)) {
                $error_message = 'Login system configuration error. Please contact support.';
            } else {
                // Build the SELECT query
                $select_fields = ['id', 'password'];
                
                // Add optional fields if they exist
                $optional_fields = ['first_name', 'last_name', 'username', 'email', 'role', 'subscription_type', 'status', 'email_verified', 'profile_image'];
                foreach ($optional_fields as $field) {
                    if (in_array($field, $existing_columns)) {
                        $select_fields[] = $field;
                    }
                }
                
                $select_clause = implode(', ', $select_fields);
                $where_clause = implode(' OR ', $query_parts);
                
                $stmt = $pdo->prepare("SELECT $select_clause FROM users_acc WHERE $where_clause LIMIT 1");
                $stmt->execute($params);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password'])) {
                    // Check if account is active (if status column exists)
                    if (isset($user['status']) && $user['status'] !== 'active') {
                        $error_message = 'Your account is currently ' . $user['status'] . '. Please contact support.';
                    } else {
                        // Login successful - set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['logged_in'] = true;
                        
                        // Set optional session variables if data exists
                        if (isset($user['first_name'])) $_SESSION['first_name'] = $user['first_name'];
                        if (isset($user['last_name'])) $_SESSION['last_name'] = $user['last_name'];
                        if (isset($user['username'])) $_SESSION['username'] = $user['username'];
                        if (isset($user['email'])) $_SESSION['email'] = $user['email'];
                        if (isset($user['role'])) $_SESSION['role'] = $user['role'];
                        if (isset($user['subscription_type'])) $_SESSION['subscription_type'] = $user['subscription_type'];
                        if (isset($user['profile_image'])) $_SESSION['profile_image'] = $user['profile_image'];
                        
                        // Set default values for missing fields
                        if (!isset($_SESSION['first_name'])) $_SESSION['first_name'] = 'User';
                        if (!isset($_SESSION['subscription_type'])) $_SESSION['subscription_type'] = 'free';
                        if (!isset($_SESSION['role'])) $_SESSION['role'] = 'student';
                        
                        // Handle remember me
                        if ($remember_me) {
                            // Set cookies for 30 days
                            setcookie('remember_user', $user['id'], time() + (30 * 24 * 60 * 60), '/');
                            setcookie('remember_token', hash('sha256', $user['id'] . $user['password']), time() + (30 * 24 * 60 * 60), '/');
                        }
                        
                        // Update last login if column exists
                        try {
                            if (in_array('last_login', $existing_columns)) {
                                $stmt = $pdo->prepare("UPDATE users_acc SET last_login = NOW() WHERE id = ?");
                                $stmt->execute([$user['id']]);
                            } elseif (in_array('last_login_date', $existing_columns)) {
                                $stmt = $pdo->prepare("UPDATE users_acc SET last_login_date = NOW() WHERE id = ?");
                                $stmt->execute([$user['id']]);
                            }
                        } catch (Exception $e) {
                            // Ignore error if last_login update fails
                            error_log("Last login update failed: " . $e->getMessage());
                        }
                        
                        // Get user's subscription info from subscription table
                        try {
                            $stmt = $pdo->query("SHOW TABLES LIKE 'subscription'");
                            if ($stmt->rowCount() > 0) {
                                $stmt = $pdo->prepare("SELECT subscription_type, status FROM subscription WHERE user_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
                                $stmt->execute([$user['id']]);
                                $subscription = $stmt->fetch();
                                
                                if ($subscription) {
                                    $_SESSION['subscription'] = $subscription['subscription_type'];
                                } else {
                                    $_SESSION['subscription'] = $_SESSION['subscription_type'] ?? 'free';
                                }
                            } else {
                                $_SESSION['subscription'] = $_SESSION['subscription_type'] ?? 'free';
                            }
                        } catch (Exception $e) {
                            $_SESSION['subscription'] = 'free';
                        }
                        
                        // Redirect to intended page or dashboard
                        $redirect_url = $_GET['redirect'] ?? $_POST['redirect'] ?? 'Home.php';
                        
                        // Security check for redirect URL
                        if (!filter_var($redirect_url, FILTER_VALIDATE_URL)) {
                            // If it's not a full URL, treat as relative path
                            if (strpos($redirect_url, '/') !== 0 && strpos($redirect_url, '..') === false) {
                                header("Location: $redirect_url");
                            } else {
                                header("Location: Home.php");
                            }
                        } else {
                            header("Location: Home.php");
                        }
                        exit();
                    }
                } else {
                    $error_message = 'Invalid email/username or password.';
                }
            }
        } catch (PDOException $e) {
            $error_message = 'Login failed. Please try again later.';
            error_log("Login error: " . $e->getMessage());
        }
    }
}

// Check for remember me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user']) && isset($_COOKIE['remember_token'])) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM users_acc WHERE id = ?");
        $stmt->execute([$_COOKIE['remember_user']]);
        $user = $stmt->fetch();
        
        if ($user && hash('sha256', $user['id'] . $user['password']) === $_COOKIE['remember_token']) {
            // Auto-login user
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['logged_in'] = true;
            if (isset($user['first_name'])) $_SESSION['first_name'] = $user['first_name'];
            if (isset($user['email'])) $_SESSION['email'] = $user['email'];
            header("Location: Home.php");
            exit();
        }
    } catch (Exception $e) {
        // Clear invalid cookies
        setcookie('remember_user', '', time() - 3600, '/');
        setcookie('remember_token', '', time() - 3600, '/');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EduLearn Academy</title>
    <link href="Web3.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Auth container styles */
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
            max-width: 450px;
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
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid rgba(139, 92, 246, 0.1);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            background: var(--white);
        }
        
        .form-group input:focus {
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
        
        .form-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin: 0;
        }
        
        .checkbox-group label {
            margin: 0;
            font-size: 0.9rem;
            cursor: pointer;
        }
        
        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            transition: var(--transition);
        }
        
        .forgot-password:hover {
            color: var(--secondary-color);
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

        .social-login {
            margin: 2rem 0;
        }

        .social-login-divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .social-login-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: rgba(139, 92, 246, 0.1);
            z-index: 1;
        }

        .social-login-divider span {
            background: var(--white);
            padding: 0 1rem;
            position: relative;
            z-index: 2;
        }

        .demo-accounts {
            background: #f0f9ff;
            border: 1px solid #3b82f6;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
        }

        .demo-accounts h4 {
            color: #1e40af;
            margin-bottom: 0.5rem;
        }

        .demo-accounts .demo-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            font-size: 0.75rem;
            cursor: pointer;
            margin: 0.2rem;
            transition: background 0.3s;
        }

        .demo-accounts .demo-btn:hover {
            background: #2563eb;
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
            width: 100%;
        }

        .btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-primary {
            background: var(--primary-color);
        }

        @media (max-width: 768px) {
            .auth-card {
                padding: 2rem;
                margin: 1rem;
            }
            
            .form-row {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
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
                <h1>Welcome Back</h1>
                <p>Sign in to continue your learning journey</p>
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

            <form method="POST" action="" id="loginForm">
                <?php if (isset($_GET['redirect'])): ?>
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect']); ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="login_field">Email or Username</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="login_field" name="login_field" 
                               value="<?php echo htmlspecialchars($login_field ?? ''); ?>" 
                               placeholder="Enter your email or username" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" 
                               placeholder="Enter your password" required>
                        <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="checkbox-group">
                        <input type="checkbox" id="remember_me" name="remember_me">
                        <label for="remember_me">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account?</p>
                <a href="signup.php">Create an account</a>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle');
            
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

        function fillLogin(email, password) {
            document.getElementById('login_field').value = email;
            document.getElementById('password').value = password;
        }
        
        // Auto-focus on email field
        document.getElementById('login_field').focus();
        
        // Handle form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = document.querySelector('.btn-primary');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
            submitBtn.disabled = true;
            
            // Re-enable button after 5 seconds in case of error
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });

        // Handle Enter key in password field
        document.getElementById('password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('loginForm').submit();
            }
        });
    </script>
</body>
</html>