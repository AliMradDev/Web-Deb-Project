<?php
session_start();
require_once 'database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=pricing.php');
    exit();
}

// Get plan from URL
$plan = $_GET['plan'] ?? '';
if (!in_array($plan, ['premium', 'university'])) {
    header('Location: pricing.php');
    exit();
}

// Plan details
$plans = [
    'premium' => [
        'name' => 'Premium Plan',
        'price' => 19.99,
        'description' => 'Full access to all courses, live classes, certificates, and priority support',
        'features' => [
            'Full course catalog access',
            'Unlimited course enrollment',
            'Live interactive classes',
            'Course completion certificates',
            'Priority customer support',
            'Offline viewing capability'
        ]
    ],
    'university' => [
        'name' => 'University Student Plan',
        'price' => 9.99,
        'description' => 'Student discount plan with full course access',
        'features' => [
            'Full course catalog access',
            'Unlimited course enrollment',
            'Live interactive classes',
            'Course completion certificates',
            'Standard customer support'
        ]
    ]
];

$selected_plan = $plans[$plan];

// Handle payment processing (mock)
if ($_POST && isset($_POST['process_payment'])) {
    try {
        $pdo = getDbConnection();
        
        // Simulate payment processing delay
        sleep(2);
        
        // Insert subscription record
        $start_date = date('Y-m-d H:i:s');
        $end_date = date('Y-m-d H:i:s', strtotime('+1 month'));
        
        $stmt = $pdo->prepare("
            INSERT INTO user_subscriptions 
            (user_id, subscription_type, status, start_date, end_date, price, payment_method, created_at) 
            VALUES (?, ?, 'active', ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_SESSION['user_id'],
            $plan,
            $start_date,
            $end_date,
            $selected_plan['price'],
            $_POST['payment_method'] ?? 'credit_card',
            $start_date
        ]);
        
        // Update session
        $_SESSION['subscription'] = $plan;
        
        // Redirect to success page
        header('Location: checkout.php?success=1&plan=' . urlencode($plan));
        exit();
        
    } catch (Exception $e) {
        $error_message = "Payment failed. Please try again.";
    }
}

// Check for success
$success = isset($_GET['success']) && $_GET['success'] == '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $success ? 'Payment Successful' : 'Checkout'; ?> - EduLearn Academy</title>
    <link href="Web3.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin-top: 80px;
            background: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .checkout-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin: -2rem -2rem 2rem -2rem;
        }

        .checkout-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .checkout-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .checkout-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .plan-summary {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            height: fit-content;
        }

        .plan-summary h3 {
            font-size: 1.5rem;
            color: #1f2937;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .plan-price {
            font-size: 2.5rem;
            font-weight: bold;
            color: #8b5cf6;
            margin-bottom: 1rem;
        }

        .plan-price span {
            font-size: 1rem;
            color: #6b7280;
            font-weight: normal;
        }

        .plan-description {
            color: #6b7280;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .plan-features {
            list-style: none;
            padding: 0;
        }

        .plan-features li {
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #1f2937;
        }

        .plan-features i {
            color: #10b981;
            width: 16px;
        }

        .payment-form {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section h4 {
            color: #1f2937;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            color: #374151;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #8b5cf6;
        }

        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1rem;
        }

        .payment-method {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .payment-option {
            flex: 1;
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-option:hover {
            border-color: #8b5cf6;
        }

        .payment-option.selected {
            border-color: #8b5cf6;
            background: rgba(139, 92, 246, 0.05);
        }

        .payment-option i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #8b5cf6;
        }

        .order-total {
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .total-row.final {
            font-size: 1.2rem;
            font-weight: bold;
            color: #1f2937;
            border-top: 1px solid #e5e7eb;
            padding-top: 0.5rem;
            margin-top: 1rem;
        }

        .btn {
            width: 100%;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            display: inline-block;
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
            color: #6b7280;
            border: 2px solid #e5e7eb;
        }

        .btn-outline:hover {
            border-color: #8b5cf6;
            color: #8b5cf6;
        }

        .security-notice {
            background: #f0f9ff;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
            margin-bottom: 2rem;
        }

        .security-notice i {
            color: #3b82f6;
            margin-right: 0.5rem;
        }

        .error-message {
            background: #fef2f2;
            color: #dc2626;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
        }

        /* Success Page Styles */
        .success-container {
            text-align: center;
            padding: 3rem;
        }

        .success-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 3rem;
            color: white;
        }

        .success-title {
            font-size: 2.5rem;
            color: #1f2937;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .success-message {
            font-size: 1.2rem;
            color: #6b7280;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .success-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e5e7eb;
            border-top: 4px solid #8b5cf6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .checkout-header {
                margin: -1rem -1rem 2rem -1rem;
                padding: 2rem 1rem;
            }

            .checkout-header h1 {
                font-size: 1.8rem;
            }

            .checkout-content {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .payment-method {
                flex-direction: column;
            }

            .success-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <!-- Success Page -->
        <?php if ($success): ?>
            <div class="checkout-header">
                <h1>Payment Successful!</h1>
                <p>Welcome to your new subscription plan</p>
            </div>

            <div class="success-container">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h2 class="success-title">Congratulations!</h2>
                <p class="success-message">
                    Your subscription to the <strong><?php echo htmlspecialchars($selected_plan['name']); ?></strong> has been activated successfully. 
                    You now have full access to all premium courses and features.
                </p>
                
                <div class="success-actions">
                    <a href="courses.php" class="btn btn-primary">
                        <i class="fas fa-graduation-cap"></i> Browse Courses
                    </a>
                    <a href="my-subscriptions.php" class="btn btn-outline">
                        <i class="fas fa-receipt"></i> View Subscription
                    </a>
                </div>
            </div>

        <!-- Checkout Form -->
        <?php else: ?>
            <div class="checkout-header">
                <h1>Secure Checkout</h1>
                <p>Complete your subscription to <?php echo htmlspecialchars($selected_plan['name']); ?></p>
            </div>

            <!-- Error Message -->
            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="checkout-content">
                <!-- Plan Summary -->
                <div class="plan-summary">
                    <h3>
                        <i class="fas fa-crown"></i>
                        <?php echo htmlspecialchars($selected_plan['name']); ?>
                    </h3>
                    <div class="plan-price">
                        $<?php echo number_format($selected_plan['price'], 2); ?>
                        <span>/month</span>
                    </div>
                    <p class="plan-description">
                        <?php echo htmlspecialchars($selected_plan['description']); ?>
                    </p>
                    
                    <ul class="plan-features">
                        <?php foreach ($selected_plan['features'] as $feature): ?>
                            <li>
                                <i class="fas fa-check"></i>
                                <?php echo htmlspecialchars($feature); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Payment Form -->
                <div class="payment-form">
                    <div class="security-notice">
                        <i class="fas fa-shield-alt"></i>
                        <strong>Demo Mode:</strong> This is a simulation for educational purposes. No real payment will be processed.
                    </div>

                    <form method="POST" id="paymentForm">
                        <input type="hidden" name="process_payment" value="1">

                        <!-- Payment Method -->
                        <div class="form-section">
                            <h4><i class="fas fa-credit-card"></i> Payment Method</h4>
                            <div class="payment-method">
                                <div class="payment-option selected" onclick="selectPayment('credit_card', this)">
                                    <i class="fas fa-credit-card"></i>
                                    <div>Credit Card</div>
                                    <input type="radio" name="payment_method" value="credit_card" checked style="display: none;">
                                </div>
                                <div class="payment-option" onclick="selectPayment('paypal', this)">
                                    <i class="fab fa-paypal"></i>
                                    <div>PayPal</div>
                                    <input type="radio" name="payment_method" value="paypal" style="display: none;">
                                </div>
                            </div>
                        </div>

                        <!-- Card Details -->
                        <div class="form-section" id="cardDetails">
                            <h4><i class="fas fa-lock"></i> Card Details</h4>
                            <div class="form-group">
                                <label for="card_number">Card Number</label>
                                <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" value="4111 1111 1111 1111" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiry">Expiry Date</label>
                                    <input type="text" id="expiry" name="expiry" placeholder="MM/YY" value="12/25" required>
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV</label>
                                    <input type="text" id="cvv" name="cvv" placeholder="123" value="123" required>
                                </div>
                            </div>
                        </div>

                        <!-- Billing Information -->
                        <div class="form-section">
                            <h4><i class="fas fa-user"></i> Billing Information</h4>
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" id="address" name="address" placeholder="123 Main Street" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text" id="city" name="city" placeholder="New York" required>
                                </div>
                                <div class="form-group">
                                    <label for="zip">ZIP Code</label>
                                    <input type="text" id="zip" name="zip" placeholder="10001" required>
                                </div>
                            </div>
                        </div>

                        <!-- Order Total -->
                        <div class="order-total">
                            <div class="total-row">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($selected_plan['price'], 2); ?></span>
                            </div>
                            <div class="total-row">
                                <span>Tax:</span>
                                <span>$0.00</span>
                            </div>
                            <div class="total-row final">
                                <span>Total:</span>
                                <span>$<?php echo number_format($selected_plan['price'], 2); ?></span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary" id="payButton">
                            <i class="fas fa-lock"></i> Complete Payment
                        </button>
                        
                        <a href="pricing.php" class="btn btn-outline" style="margin-top: 1rem;">
                            <i class="fas fa-arrow-left"></i> Back to Plans
                        </a>
                    </form>
                </div>
            </div>

            <!-- Loading Overlay -->
            <div class="loading" id="loadingOverlay">
                <div class="spinner"></div>
                <p>Processing your payment...</p>
                <p><small>This may take a few seconds</small></p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function selectPayment(method, element) {
            // Remove selected class from all options
            document.querySelectorAll('.payment-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            element.classList.add('selected');
            
            // Check the hidden radio button
            element.querySelector('input[type="radio"]').checked = true;
            
            // Toggle card details visibility
            const cardDetails = document.getElementById('cardDetails');
            if (method === 'credit_card') {
                cardDetails.style.display = 'block';
            } else {
                cardDetails.style.display = 'none';
            }
        }

        // Form submission with loading
        document.getElementById('paymentForm')?.addEventListener('submit', function(e) {
            const loading = document.getElementById('loadingOverlay');
            const form = document.querySelector('.checkout-content');
            
            // Show loading overlay
            loading.style.display = 'block';
            form.style.opacity = '0.5';
            form.style.pointerEvents = 'none';
            
            // Change button text
            const payButton = document.getElementById('payButton');
            payButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            payButton.disabled = true;
        });

        // Auto-format card number
        document.getElementById('card_number')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            if (formattedValue !== e.target.value) {
                e.target.value = formattedValue;
            }
        });

        // Auto-format expiry date
        document.getElementById('expiry')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });

        // Limit CVV to 3 digits
        document.getElementById('cvv')?.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').substring(0, 3);
        });
    </script>
</body>
</html>