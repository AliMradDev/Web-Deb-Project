<?php
session_start();
require_once 'database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get database connection
try {
    $pdo = getDbConnection();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];

// Get user's subscriptions
try {
    $stmt = $pdo->prepare("
        SELECT 
            id,
            subscription_type,
            status,
            start_date,
            end_date,
            price,
            payment_method,
            created_at,
            CASE 
                WHEN status = 'active' AND end_date > NOW() THEN 'Active'
                WHEN status = 'active' AND end_date <= NOW() THEN 'Expired'
                WHEN status = 'cancelled' THEN 'Cancelled'
                WHEN status = 'pending' THEN 'Pending'
                ELSE 'Inactive'
            END as display_status,
            DATEDIFF(end_date, NOW()) as days_remaining
        FROM user_subscriptions 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $subscriptions = [];
    $error_message = "Error loading subscriptions: " . $e->getMessage();
}

// Get current active subscription
$current_subscription = null;
foreach ($subscriptions as $sub) {
    if ($sub['display_status'] === 'Active') {
        $current_subscription = $sub;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Subscriptions - EduLearn Academy</title>
    <link href="Web3.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin-top: 80px;
            background: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin: -2rem -2rem 2rem -2rem;
        }

        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Current Subscription Card */
        .current-subscription {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 30px rgba(16, 185, 129, 0.2);
        }

        .current-subscription.expired {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .current-subscription.none {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        }

        .subscription-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .subscription-type {
            font-size: 1.5rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .subscription-status {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .subscription-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .detail-item {
            text-align: center;
        }

        .detail-label {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-size: 1.2rem;
            font-weight: 600;
        }

        /* Subscription History */
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .subscriptions-grid {
            display: grid;
            gap: 1rem;
        }

        .subscription-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }

        .subscription-card:hover {
            transform: translateY(-2px);
        }

        .subscription-card-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .subscription-info {
            flex: 1;
        }

        .subscription-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .subscription-period {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-expired {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-cancelled {
            background: #f3f4f6;
            color: #374151;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .subscription-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #f3f4f6;
        }

        .meta-item {
            text-align: center;
        }

        .meta-label {
            font-size: 0.8rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            font-weight: 600;
        }

        .meta-value {
            font-weight: 600;
            color: #1f2937;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: #8b5cf6;
            color: white;
        }

        .btn-primary:hover {
            background: #7c3aed;
        }

        .btn-outline {
            background: transparent;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }

        .btn-outline:hover {
            background: #f9fafb;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }

        .empty-state h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }

        /* Error Message */
        .error-message {
            background: #fef2f2;
            color: #dc2626;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .page-header {
                margin: -1rem -1rem 2rem -1rem;
                padding: 2rem 1rem;
            }

            .page-header h1 {
                font-size: 1.8rem;
            }

            .subscription-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .subscription-details {
                grid-template-columns: 1fr;
            }

            .subscription-card-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .action-buttons {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-crown"></i> My Subscriptions</h1>
            <p>Manage your subscription plans and billing information</p>
        </div>

        <!-- Error Message -->
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Current Subscription -->
        <div class="current-subscription <?php echo $current_subscription ? 'active' : 'none'; ?>">
            <div class="subscription-header">
                <div>
                    <div class="subscription-type">
                        <?php if ($current_subscription): ?>
                            <?php echo ucfirst($current_subscription['subscription_type']); ?> Plan
                        <?php else: ?>
                            No Active Subscription
                        <?php endif; ?>
                    </div>
                </div>
                <div class="subscription-status">
                    <?php if ($current_subscription): ?>
                        <?php echo $current_subscription['display_status']; ?>
                    <?php else: ?>
                        Free Plan
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($current_subscription): ?>
                <div class="subscription-details">
                    <div class="detail-item">
                        <div class="detail-label">Days Remaining</div>
                        <div class="detail-value">
                            <?php echo max(0, $current_subscription['days_remaining']); ?> days
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">End Date</div>
                        <div class="detail-value">
                            <?php echo date('M j, Y', strtotime($current_subscription['end_date'])); ?>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Monthly Price</div>
                        <div class="detail-value">
                            $<?php echo number_format($current_subscription['price'], 2); ?>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Payment Method</div>
                        <div class="detail-value">
                            <?php echo ucfirst($current_subscription['payment_method'] ?? 'N/A'); ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p style="margin-top: 1rem; opacity: 0.9;">
                    You're currently on the free plan. Upgrade to access premium courses and features.
                </p>
                <div style="margin-top: 1rem;">
                    <a href="pricing.php" class="btn btn-primary">
                        <i class="fas fa-arrow-up"></i> Upgrade Now
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Subscription History -->
        <section>
            <h2 class="section-title">
                <i class="fas fa-history"></i>
                Subscription History
            </h2>

            <?php if (empty($subscriptions)): ?>
                <div class="empty-state">
                    <i class="fas fa-receipt"></i>
                    <h3>No Subscription History</h3>
                    <p>You haven't subscribed to any plans yet.</p>
                    <a href="pricing.php" class="btn btn-primary" style="margin-top: 1rem;">
                        View Plans
                    </a>
                </div>
            <?php else: ?>
                <div class="subscriptions-grid">
                    <?php foreach ($subscriptions as $subscription): ?>
                        <div class="subscription-card">
                            <div class="subscription-card-header">
                                <div class="subscription-info">
                                    <div class="subscription-name">
                                        <?php echo ucfirst($subscription['subscription_type']); ?> Plan
                                    </div>
                                    <div class="subscription-period">
                                        <?php echo date('M j, Y', strtotime($subscription['start_date'])); ?> - 
                                        <?php echo date('M j, Y', strtotime($subscription['end_date'])); ?>
                                    </div>
                                </div>
                                <div class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $subscription['display_status'])); ?>">
                                    <?php echo $subscription['display_status']; ?>
                                </div>
                            </div>

                            <div class="subscription-meta">
                                <div class="meta-item">
                                    <div class="meta-label">Price</div>
                                    <div class="meta-value">$<?php echo number_format($subscription['price'], 2); ?></div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-label">Payment</div>
                                    <div class="meta-value"><?php echo ucfirst($subscription['payment_method'] ?? 'N/A'); ?></div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-label">Started</div>
                                    <div class="meta-value"><?php echo date('M j, Y', strtotime($subscription['created_at'])); ?></div>
                                </div>
                                <?php if ($subscription['display_status'] === 'Active'): ?>
                                    <div class="meta-item">
                                        <div class="meta-label">Days Left</div>
                                        <div class="meta-value"><?php echo max(0, $subscription['days_remaining']); ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="action-buttons">
                                <?php if ($subscription['display_status'] === 'Active'): ?>
                                    <a href="billing.php?action=manage&id=<?php echo $subscription['id']; ?>" class="btn btn-outline">
                                        <i class="fas fa-cog"></i> Manage
                                    </a>
                                    <a href="billing.php?action=cancel&id=<?php echo $subscription['id']; ?>" class="btn btn-outline">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                <?php elseif ($subscription['display_status'] === 'Expired'): ?>
                                    <a href="pricing.php" class="btn btn-primary">
                                        <i class="fas fa-redo"></i> Renew
                                    </a>
                                <?php endif; ?>
                                <a href="billing.php?action=invoice&id=<?php echo $subscription['id']; ?>" class="btn btn-outline">
                                    <i class="fas fa-download"></i> Invoice
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <script>
        // Auto-refresh page every 5 minutes to update subscription status
        setTimeout(() => {
            window.location.reload();
        }, 5 * 60 * 1000);

        // Confirmation for cancellation
        document.addEventListener('DOMContentLoaded', function() {
            const cancelButtons = document.querySelectorAll('a[href*="action=cancel"]');
            cancelButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to cancel your subscription? You will lose access to premium features at the end of your billing period.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>