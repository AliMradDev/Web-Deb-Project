<?php
session_start();
require_once 'database.php';

// Check if user is logged in
$user_logged_in = isset($_SESSION['user_id']);
$current_subscription = $_SESSION['subscription'] ?? 'free';
$user_name = $_SESSION['username'] ?? '';

// Get database connection for user info if needed
if ($user_logged_in) {
    try {
        $pdo = getDbConnection();
        // Get current subscription details
        $stmt = $pdo->prepare("
            SELECT subscription_type, status, end_date 
            FROM user_subscriptions 
            WHERE user_id = ? AND status = 'active' 
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $active_subscription = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($active_subscription) {
            $current_subscription = $active_subscription['subscription_type'];
        }
    } catch (Exception $e) {
        // Continue with session data if database fails
    }
}

// Handle subscription upgrade requests
if ($_POST && $user_logged_in) {
    $plan = $_POST['plan'] ?? '';
    if (in_array($plan, ['premium', 'university'])) {
        // Redirect to payment processing page
        header("Location: checkout.php?plan=" . urlencode($plan));
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Plans - EduLearn Academy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="Web3.css" rel="stylesheet">
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* User Status Bar */
        .user-status {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 1rem 0;
            text-align: center;
        }

        .user-status.logged-out {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .user-status.premium {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .user-status h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.2rem;
        }

        .user-status p {
            margin: 0;
            opacity: 0.9;
        }

        /* Hero Section */
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

        /* Subscription Plans Section */
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

        .plan-card.featured:hover {
            transform: scale(1.05) translateY(-5px);
        }

        .plan-card.current-plan {
            border: 2px solid #10b981;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(5, 150, 105, 0.05));
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
            background: none;
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
            cursor: default;
        }

        .btn-current:hover {
            transform: none;
            box-shadow: none;
        }

        .btn-disabled {
            background: #9ca3af;
            color: white;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .btn-disabled:hover {
            transform: none;
            box-shadow: none;
        }

        .btn-login {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.4);
        }

        /* Benefits Section */
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

        /* FAQ Section */
        .faq-section {
            padding: 4rem 0;
        }

        .faq-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .faq-item {
            background: white;
            margin-bottom: 1rem;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.08);
        }

        .faq-question {
            padding: 1.5rem;
            cursor: pointer;
            font-weight: 600;
            color: #1f2937;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.3s ease;
        }

        .faq-question:hover {
            background: rgba(139, 92, 246, 0.05);
        }

        .faq-answer {
            padding: 0 1.5rem;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
            color: #6b7280;
        }

        .faq-answer.active {
            padding: 1.5rem;
            max-height: 200px;
        }

        .faq-icon {
            color: #8b5cf6;
            transition: transform 0.3s ease;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }

        .cta-content h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .cta-content p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-cta {
            background: white;
            color: #8b5cf6;
            padding: 1rem 3rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .hero-stats {
                flex-direction: column;
                gap: 1rem;
            }
            
            .benefits-title, .section-title {
                font-size: 2rem;
            }
            
            .plans-grid {
                grid-template-columns: 1fr;
            }
            
            .plan-card.featured {
                transform: none;
            }

            .cta-content h2 {
                font-size: 2rem;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .plan-card {
            animation: fadeInUp 0.6s ease forwards;
        }

        .plan-card:nth-child(1) {
            animation-delay: 0s;
        }

        .plan-card:nth-child(2) {
            animation-delay: 0.1s;
        }

        .plan-card:nth-child(3) {
            animation-delay: 0.2s;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'header.php'; ?>

    <!-- User Status Bar -->
    <div class="user-status <?php echo !$user_logged_in ? 'logged-out' : ($current_subscription !== 'free' ? 'premium' : ''); ?>">
        <div class="container">
            <?php if (!$user_logged_in): ?>
                <h3><i class="fas fa-sign-in-alt"></i> Not Logged In</h3>
                <p>Please log in to subscribe to a plan and access premium courses.</p>
            <?php elseif ($current_subscription === 'free'): ?>
                <h3><i class="fas fa-user"></i> Welcome, <?php echo htmlspecialchars($user_name); ?>!</h3>
                <p>You're currently on the free plan. Upgrade to unlock premium features!</p>
            <?php else: ?>
                <h3><i class="fas fa-crown"></i> <?php echo ucfirst($current_subscription); ?> Member - <?php echo htmlspecialchars($user_name); ?></h3>
                <p>You have full access to all premium courses and features!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1>Choose Your Learning Path</h1>
                <p>Unlock unlimited access to premium courses, expert instructors, and cutting-edge learning resources. Transform your career with the right subscription plan.</p>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <span class="stat-number">10,000+</span>
                        <span class="stat-label">Active Students</span>
                    </div>
                    <div class="hero-stat">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Expert Courses</span>
                    </div>
                    <div class="hero-stat">
                        <span class="stat-number">95%</span>
                        <span class="stat-label">Success Rate</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Subscription Plans Section -->
    <section class="subscription-plans">
        <div class="container">
            <h2 class="section-title">Subscription Plans</h2>
            <p class="section-desc">Choose the plan that fits your learning goals and budget. All plans include access to our supportive learning community.</p>
            
            <div class="plans-grid">
                <!-- Free Plan -->
                <div class="plan-card <?php echo $current_subscription === 'free' ? 'current-plan' : ''; ?>">
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
                        <?php if (!$user_logged_in): ?>
                            <a href="signup.php" class="btn btn-login">Sign Up Free</a>
                        <?php elseif ($current_subscription === 'free'): ?>
                            <button class="btn btn-current">Current Plan</button>
                        <?php else: ?>
                            <button class="btn btn-disabled">Downgrade Available</button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Premium Plan -->
                <div class="plan-card featured <?php echo $current_subscription === 'premium' ? 'current-plan' : ''; ?>">
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
                        <?php if (!$user_logged_in): ?>
                            <a href="login.php?redirect=pricing.php" class="btn btn-login">Login to Subscribe</a>
                        <?php elseif ($current_subscription === 'premium'): ?>
                            <button class="btn btn-current">Current Plan</button>
                        <?php else: ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="plan" value="premium">
                                <button type="submit" class="btn btn-primary">Subscribe Now</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- University Plan -->
                <div class="plan-card <?php echo $current_subscription === 'university' ? 'current-plan' : ''; ?>">
                    <?php if ($current_subscription === 'university'): ?>
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
                        <?php if (!$user_logged_in): ?>
                            <a href="login.php?redirect=pricing.php" class="btn btn-login">Login to Subscribe</a>
                        <?php elseif ($current_subscription === 'university'): ?>
                            <button class="btn btn-current">Current Plan</button>
                        <?php else: ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="plan" value="university">
                                <button type="submit" class="btn btn-outline">Verify & Subscribe</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits-section">
        <div class="benefits-container">
            <h2 class="benefits-title">Why Choose EduLearn Academy?</h2>
            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3 class="benefit-title">Expert Instructors</h3>
                    <p class="benefit-description">Learn from industry professionals with years of real-world experience and proven track records in their fields.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="benefit-title">Flexible Learning</h3>
                    <p class="benefit-description">Study at your own pace, anytime, anywhere. Access courses on all devices with seamless synchronization.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h3 class="benefit-title">Industry Certificates</h3>
                    <p class="benefit-description">Earn recognized certificates upon course completion to boost your resume and advance your career.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="benefit-title">Learning Community</h3>
                    <p class="benefit-description">Connect with fellow learners, participate in discussions, and build valuable professional networks.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="faq-container">
            <h2 class="section-title">Frequently Asked Questions</h2>
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>Can I switch plans anytime?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    <p>Yes! You can upgrade or downgrade your plan at any time. Changes will be reflected in your next billing cycle, and you'll receive a prorated refund or charge as applicable.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>Is there a free trial for premium plans?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    <p>We offer a 7-day free trial for all premium plans. No credit card required to start your trial. You can cancel anytime during the trial period with no charges.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>How do I verify my student status?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    <p>You'll need to provide a valid student ID, enrollment verification letter, or official transcript during the signup process. We partner with SheerID for instant verification.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>What payment methods do you accept?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    <p>We accept all major credit cards (Visa, MasterCard, American Express), PayPal, and bank transfers. All payments are processed securely through our encrypted payment system.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Start Learning?</h2>
                <p>Join thousands of successful students who have transformed their careers with our expert-led courses and comprehensive learning platform.</p>
                <?php if (!$user_logged_in): ?>
                    <a href="signup.php" class="btn-cta">Start Your Journey Today</a>
                <?php elseif ($current_subscription === 'free'): ?>
                    <a href="#subscription-plans" class="btn-cta">Upgrade Your Plan</a>
                <?php else: ?>
                    <a href="courses.php" class="btn-cta">Browse Courses</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script>
        function toggleFAQ(element) {
            const answer = element.nextElementSibling;
            const icon = element.querySelector('.faq-icon');
            
            // Close all other FAQs
            document.querySelectorAll('.faq-answer').forEach(faq => {
                if (faq !== answer) {
                    faq.classList.remove('active');
                }
            });
            
            document.querySelectorAll('.faq-icon').forEach(faqIcon => {
                if (faqIcon !== icon) {
                    faqIcon.style.transform = 'rotate(0deg)';
                }
            });
            
            // Toggle current FAQ
            answer.classList.toggle('active');
            const isActive = answer.classList.contains('active');
            icon.style.transform = isActive ? 'rotate(180deg)' : 'rotate(0deg)';
        }

        // Add smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Confirmation for subscription
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const plan = this.querySelector('input[name="plan"]').value;
                const planName = plan === 'premium' ? 'Premium ($19.99/month)' : 'University Student ($9.99/month)';
                
                if (!confirm(`Are you sure you want to subscribe to the ${planName} plan? You will be redirected to the payment page.`)) {
                    e.preventDefault();
                }
            });
        });

        // Add scroll effect for navbar
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (header && window.scrollY > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.backdropFilter = 'blur(10px)';
            } else if (header) {
                header.style.background = 'white';
                header.style.backdropFilter = 'none';
            }
        });
    </script>
</body>
</html>