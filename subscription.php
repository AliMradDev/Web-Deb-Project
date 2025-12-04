<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login with return URL
    $return_url = isset($_GET['return']) ? urlencode($_GET['return']) : '';
    header('Location: login.php?return=' . $return_url);
    exit;
}

// Handle subscription selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe'])) {
    $plan = $_POST['plan'] ?? '';
    $valid_plans = ['premium', 'student'];
    
    if (in_array($plan, $valid_plans)) {
        // Set subscription in session (in real app, save to database)
        $_SESSION['subscription'] = $plan;
        $_SESSION['subscription_date'] = date('Y-m-d H:i:s');
        
        // In a real application, you would:
        // 1. Process payment with Stripe/PayPal
        // 2. Save subscription to database
        // 3. Send confirmation email
        
        /*
        Example database code:
        $pdo = new PDO('mysql:host=localhost;dbname=edulearn', $username, $password);
        $stmt = $pdo->prepare("INSERT INTO subscriptions (user_id, plan, status, created_at) VALUES (?, ?, 'active', NOW())");
        $stmt->execute([$_SESSION['user_id'], $plan]);
        */
        
        // Redirect to success page or back to course
        $redirect_url = isset($_POST['return_url']) ? $_POST['return_url'] : 'subscription.php?success=1';
        header('Location: ' . $redirect_url);
        exit;
    } else {
        $error = 'Invalid subscription plan selected.';
    }
}

// Get current subscription status
$current_subscription = $_SESSION['subscription'] ?? 'free';
$is_subscribed = $current_subscription !== 'free';

// Handle success message
$success_message = '';
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = 'Subscription activated successfully! You can now enroll in premium courses.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription - EduLearn</title>
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

        /* Hero Section */
        .hero-section {
            margin-top: 80px;
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

        /* Success Message */
        .success-banner {
            background: #f0f9ff;
            color: #10b981;
            border: 1px solid #10b981;
            padding: 1rem;
            border-radius: 8px;
            margin: 2rem 0;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        /* Current Subscription Status */
        .current-status {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.08);
            margin: 2rem 0;
            text-align: center;
        }

        .status-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .status-icon.free {
            color: #6b7280;
        }

        .status-icon.premium {
            color: #10b981;
        }

        .current-status h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #1f2937;
        }

        .current-status p {
            color: #6b7280;
            margin-bottom: 2rem;
        }

        /* Plans Section */
        .subscription-plans {
            padding: 4rem 0;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            color: #1f2937;
            margin-bottom: 1rem;
        }

        .section-desc {
            text-align: center;
            font-size: 1.1rem;
            color: #6b7280;
            max-width: 600px;
            margin: 0 auto 3rem;
        }

        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .plan-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            position: relative;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.08);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(139, 92, 246, 0.15);
        }

        .plan-card.featured {
            border: 2px solid #8b5cf6;
            transform: scale(1.05);
        }

        .plan-card.current {
            border: 2px solid #10b981;
            background: #f0fdf4;
        }

        .plan-badge {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }

        .current-badge {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .plan-header h3 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: #1f2937;
            font-weight: 600;
        }

        .price {
            font-size: 3rem;
            font-weight: bold;
            color: #8b5cf6;
            margin-bottom: 2rem;
        }

        .price span {
            font-size: 1rem;
            color: #6b7280;
            font-weight: normal;
        }

        .plan-features ul {
            list-style: none;
            text-align: left;
            margin-bottom: 2rem;
        }

        .plan-features li {
            padding: 0.8rem 0;
            border-bottom: 1px solid rgba(139, 92, 246, 0.1);
            display: flex;
            align-items: center;
            font-size: 1rem;
            color: #1f2937;
        }

        .plan-features li:last-child {
            border-bottom: none;
        }

        .plan-features i {
            margin-right: 1rem;
            width: 20px;
            text-align: center;
        }

        .fa-check {
            color: #10b981;
        }

        .fa-times {
            color: #ef4444;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            cursor: pointer;
            font-size: 1rem;
            min-width: 180px;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.4);
        }

        .btn-outline {
            background: transparent;
            color: #8b5cf6;
            border: 2px solid #8b5cf6;
        }

        .btn-outline:hover {
            background: #8b5cf6;
            color: white;
            transform: translateY(-2px);
        }

        .btn-current {
            background: #10b981;
            color: white;
        }

        .btn-current:disabled {
            cursor: not-allowed;
            opacity: 0.7;
        }

        /* Error Message */
        .error-message {
            background: #fef2f2;
            color: #ef4444;
            border: 1px solid #ef4444;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            text-align: center;
        }

        /* Demo Notice */
        .demo-notice {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #f59e0b;
            padding: 1rem;
            border-radius: 8px;
            margin: 2rem 0;
            text-align: center;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .plans-grid {
                grid-template-columns: 1fr;
            }
            
            .plan-card.featured {
                transform: none;
            }
            
            .nav-links, .navbar-nav, .menu, .navigation {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1>Choose Your Learning Plan</h1>
                <p>Unlock unlimited access to premium courses, expert instructors, and cutting-edge learning resources. Transform your career with the right subscription plan.</p>
            </div>
        </div>
    </section>

    <div class="container">
        <?php if ($success_message): ?>
            <div class="success-banner">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Demo Notice -->
        <div class="demo-notice">
            <i class="fas fa-info-circle"></i>
            <strong>Demo Mode:</strong> This is a demonstration. No actual payment will be processed. Subscriptions are simulated for testing purposes.
        </div>

        <!-- Current Subscription Status -->
        <div class="current-status">
            <div class="status-icon <?php echo $is_subscribed ? 'premium' : 'free'; ?>">
                <i class="fas fa-<?php echo $is_subscribed ? 'crown' : 'user'; ?>"></i>
            </div>
            <h3>Current Plan: <?php echo ucfirst($current_subscription); ?></h3>
            <p>
                <?php if ($is_subscribed): ?>
                    You have access to all premium courses and features.
                <?php else: ?>
                    Upgrade to premium to access all courses and features.
                <?php endif; ?>
            </p>
            
            <?php if ($is_subscribed): ?>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="plan" value="free">
                    <button type="submit" name="subscribe" class="btn btn-outline">
                        <i class="fas fa-times"></i> Cancel Subscription
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Subscription Plans Section -->
        <section class="subscription-plans">
            <h2 class="section-title">Subscription Plans</h2>
            <p class="section-desc">Choose the plan that fits your learning goals and budget. All plans include access to our supportive learning community.</p>
            
            <div class="plans-grid">
                <!-- Free Plan -->
                <div class="plan-card <?php echo $current_subscription === 'free' ? 'current' : ''; ?>">
                    <?php if ($current_subscription === 'free'): ?>
                        <div class="plan-badge current-badge">Current Plan</div>
                    <?php endif; ?>
                    <div class="plan-header">
                        <h3>Free Plan</h3>
                        <p class="price">$0<span>/month</span></p>
                    </div>
                    <div class="plan-features">
                        <ul>
                            <li><i class="fas fa-check"></i> Limited course catalog</li>
                            <li><i class="fas fa-check"></i> Course previews</li>
                            <li><i class="fas fa-check"></i> Community access</li>
                            <li><i class="fas fa-times"></i> Live classes</li>
                            <li><i class="fas fa-times"></i> Course certificates</li>
                            <li><i class="fas fa-times"></i> Priority support</li>
                        </ul>
                    </div>
                    <div class="plan-footer">
                        <?php if ($current_subscription === 'free'): ?>
                            <button class="btn btn-current" disabled>Current Plan</button>
                        <?php else: ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="plan" value="free">
                                <input type="hidden" name="return_url" value="<?php echo $_GET['return'] ?? 'subscription.php?success=1'; ?>">
                                <button type="submit" name="subscribe" class="btn btn-outline">Downgrade</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Premium Plan -->
                <div class="plan-card featured <?php echo $current_subscription === 'premium' ? 'current' : ''; ?>">
                    <?php if ($current_subscription === 'premium'): ?>
                        <div class="plan-badge current-badge">Current Plan</div>
                    <?php else: ?>
                        <div class="plan-badge">Most Popular</div>
                    <?php endif; ?>
                    <div class="plan-header">
                        <h3>Premium Plan</h3>
                        <p class="price">$19.99<span>/month</span></p>
                    </div>
                    <div class="plan-features">
                        <ul>
                            <li><i class="fas fa-check"></i> Full course catalog</li>
                            <li><i class="fas fa-check"></i> Unlimited course access</li>
                            <li><i class="fas fa-check"></i> Live classes</li>
                            <li><i class="fas fa-check"></i> Course certificates</li>
                            <li><i class="fas fa-check"></i> Priority support</li>
                            <li><i class="fas fa-check"></i> Offline viewing</li>
                        </ul>
                    </div>
                    <div class="plan-footer">
                        <?php if ($current_subscription === 'premium'): ?>
                            <button class="btn btn-current" disabled>Current Plan</button>
                        <?php else: ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="plan" value="premium">
                                <input type="hidden" name="return_url" value="<?php echo $_GET['return'] ?? 'subscription.php?success=1'; ?>">
                                <button type="submit" name="subscribe" class="btn btn-primary">Subscribe Now</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Student Plan -->
                <div class="plan-card <?php echo $current_subscription === 'student' ? 'current' : ''; ?>">
                    <?php if ($current_subscription === 'student'): ?>
                        <div class="plan-badge current-badge">Current Plan</div>
                    <?php endif; ?>
                    <div class="plan-header">
                        <h3>University Student</h3>
                        <p class="price">$9.99<span>/month</span></p>
                    </div>
                    <div class="plan-features">
                        <ul>
                            <li><i class="fas fa-check"></i> Full course catalog</li>
                            <li><i class="fas fa-check"></i> Unlimited course access</li>
                            <li><i class="fas fa-check"></i> Live classes</li>
                            <li><i class="fas fa-check"></i> Course certificates</li>
                            <li><i class="fas fa-check"></i> Standard support</li>
                            <li><i class="fas fa-times"></i> Offline viewing</li>
                        </ul>
                    </div>
                    <div class="plan-footer">
                        <?php if ($current_subscription === 'student'): ?>
                            <button class="btn btn-current" disabled>Current Plan</button>
                        <?php else: ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="plan" value="student">
                                <input type="hidden" name="return_url" value="<?php echo $_GET['return'] ?? 'subscription.php?success=1'; ?>">
                                <button type="submit" name="subscribe" class="btn btn-outline">Verify & Subscribe</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
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

        // Auto-redirect after subscription with return URL
        <?php if (isset($_GET['return']) && $success_message): ?>
            setTimeout(() => {
                window.location.href = '<?php echo urldecode($_GET['return']); ?>';
            }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>