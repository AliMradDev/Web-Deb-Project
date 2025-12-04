<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $pdo = getDbConnection();
    
    // Fetch active subscription for the logged-in user
    $stmt = $pdo->prepare("SELECT * FROM user_subscriptions WHERE user_id = ? ORDER BY start_date DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $subscription = $stmt->fetch();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Billing</title>
<link href="Web3.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>

<div class="container" style="max-width: 600px; margin: 2rem auto;">
    <h1>Billing & Subscription</h1>

    <?php if ($subscription): ?>
        <div class="subscription-details" style="border: 1px solid #ddd; padding: 1rem; border-radius: 8px;">
            <p><strong>Subscription Type:</strong> <?php echo htmlspecialchars($subscription['subscription_type']); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($subscription['status']); ?></p>
            <p><strong>Start Date:</strong> <?php echo htmlspecialchars($subscription['start_date']); ?></p>
            <p><strong>End Date:</strong> <?php echo htmlspecialchars($subscription['end_date'] ?? 'N/A'); ?></p>
            <p><strong>Price:</strong> $<?php echo number_format($subscription['price'], 2); ?></p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($subscription['payment_method']); ?></p>

            <div class="action-buttons" style="margin-top: 1rem;">
                <?php if (strtolower($subscription['status']) === 'active'): ?>
                    <a href="cancel_subscription.php?id=<?php echo $subscription['id']; ?>" class="btn btn-outline">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                <?php elseif (strtolower($subscription['status']) === 'expired'): ?>
                    <a href="pricing.php" class="btn btn-primary">
                        <i class="fas fa-redo"></i> Renew
                    </a>
                <?php endif; ?>
                <a href="invoice.php?id=<?php echo $subscription['id']; ?>" class="btn btn-outline">
                    <i class="fas fa-download"></i> Invoice
                </a>
            </div>
        </div>
    <?php else: ?>
        <p>You do not have any subscriptions yet. <a href="pricing.php">Subscribe now</a>.</p>
    <?php endif; ?>
</div>

</body>
</html>
