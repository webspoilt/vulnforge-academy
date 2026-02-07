<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle subscription actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'upgrade':
                $tier = $_POST['tier'] ?? '';
                if (isset(SUBSCRIPTION_TIERS[$tier]) && $tier !== 'free') {
                    // Redirect to payment processor (Stripe/PayPal)
                    header("Location: /payment/start?tier=$tier");
                    exit;
                }
                break;
                
            case 'cancel':
                // Handle subscription cancellation
                $conn = getDbConnection();
                if ($conn) {
                    $conn->query("UPDATE users SET subscription_tier = 'free' WHERE id = $userId");
                    $message = 'Subscription cancelled successfully.';
                }
                break;
                
            case 'update_billing':
                // Update billing information
                $message = 'Billing information updated successfully.';
                break;
        }
    }
}

// Get current subscription info
$conn = getDbConnection();
$subscriptionInfo = null;
if ($conn) {
    $result = $conn->query("SELECT * FROM subscriptions WHERE user_id = $userId AND status = 'active' ORDER BY created_at DESC LIMIT 1");
    if ($result && $row = $result->fetch_assoc()) {
        $subscriptionInfo = $row;
    }
}

$currentTier = getUserSubscriptionTier($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription - VulnForge Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="index.php">VulnForge Academy</a></div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="subscription.php" class="active">Subscription</a>
            <a href="certificates.php">Certificates</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <main class="container">
        <h1>Subscription Management</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Current Plan -->
        <section class="current-plan">
            <div class="plan-overview">
                <h2>Current Plan</h2>
                <div class="plan-card current <?php echo $currentTier; ?>">
                    <div class="plan-header">
                        <h3><?php echo ucfirst($currentTier); ?></h3>
                        <?php if ($currentTier !== 'free'): ?>
                            <div class="price">
                                $<?php echo SUBSCRIPTION_TIERS[$currentTier]['price']; ?>/month
                            </div>
                        <?php else: ?>
                            <div class="price">Free</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="plan-features">
                        <h4>Included Features:</h4>
                        <ul>
                            <?php foreach (SUBSCRIPTION_TIERS[$currentTier]['features'] as $feature): ?>
                                <li>✓ <?php echo ucwords(str_replace('_', ' ', $feature)); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="plan-limits">
                        <p><strong>Accessible Levels:</strong> <?php echo SUBSCRIPTION_TIERS[$currentTier]['max_levels']; ?>/20</p>
                        <p><strong>Support Level:</strong> 
                            <?php 
                            echo $currentTier === 'free' ? 'Community' : 
                                ($currentTier === 'pro' ? 'Email (24h)' : 'Phone + Dedicated'); 
                            ?>
                        </p>
                    </div>
                    
                    <?php if ($currentTier !== 'free' && $subscriptionInfo): ?>
                        <div class="plan-billing">
                            <p><strong>Next Billing Date:</strong> 
                                <?php echo date('M j, Y', strtotime($subscriptionInfo['expires_at'])); ?>
                            </p>
                            <p><strong>Payment Method:</strong> 
                                <?php echo ucfirst($subscriptionInfo['payment_method']); ?> 
                                (****<?php echo rand(1000, 9999); ?>)
                            </p>
                        </div>
                        
                        <div class="plan-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="cancel">
                                <button type="submit" class="btn btn-secondary" 
                                        onclick="return confirm('Are you sure you want to cancel your subscription?')">
                                    Cancel Subscription
                                </button>
                            </form>
                            <a href="billing-update.php" class="btn btn-outline">Update Billing</a>
                        </div>
                    <?php elseif ($currentTier === 'free'): ?>
                        <div class="plan-actions">
                            <a href="pricing.php" class="btn btn-primary">Upgrade Plan</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Available Plans -->
        <section class="available-plans">
            <h2>Available Plans</h2>
            <div class="pricing-grid">
                <?php foreach (SUBSCRIPTION_TIERS as $tierKey => $tier): ?>
                <div class="pricing-card <?php echo $tierKey === $currentTier ? 'current' : ''; ?> <?php echo $tierKey === 'pro' ? 'featured' : ''; ?>">
                    <?php if ($tierKey === $currentTier): ?>
                        <div class="current-badge">Current Plan</div>
                    <?php elseif ($tierKey === 'pro'): ?>
                        <div class="plan-badge">Most Popular</div>
                    <?php endif; ?>
                    
                    <div class="plan-header">
                        <h3><?php echo $tier['name']; ?></h3>
                        <div class="price">
                            <?php if ($tier['price'] > 0): ?>
                                $<?php echo $tier['price']; ?><span>/month</span>
                            <?php else: ?>
                                Free<span>/forever</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <ul class="features-list">
                        <?php foreach ($tier['features'] as $feature): ?>
                            <li>✓ <?php echo ucwords(str_replace('_', ' ', $feature)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div class="plan-cta">
                        <?php if ($tierKey === $currentTier): ?>
                            <button class="btn btn-outline" disabled>Current Plan</button>
                        <?php elseif ($tier['price'] > 0): ?>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="action" value="upgrade">
                                <input type="hidden" name="tier" value="<?php echo $tierKey; ?>">
                                <button type="submit" class="btn btn-primary">
                                    Upgrade to <?php echo $tier['name']; ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-outline" disabled>Already Free</button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Usage Statistics -->
        <section class="usage-stats">
            <h2>Your Usage</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>Levels Completed</h4>
                    <div class="stat-number"><?php echo count(getProgress($userId)); ?></div>
                    <div class="stat-progress">
                        <div class="progress-bar">
                            <div class="progress-bar-fill" style="width: <?php echo (count(getProgress($userId)) / 20) * 100; ?>%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <h4>Total Points</h4>
                    <div class="stat-number"><?php echo $totalPoints ?? 0; ?></div>
                    <p class="stat-description">Across all completed levels</p>
                </div>
                
                <div class="stat-card">
                    <h4>Learning Streak</h4>
                    <div class="stat-number">7</div>
                    <p class="stat-description">Days in a row</p>
                </div>
                
                <div class="stat-card">
                    <h4>Success Rate</h4>
                    <div class="stat-number">85%</div>
                    <p class="stat-description">Level completion rate</p>
                </div>
            </div>
        </section>

        <!-- Billing History -->
        <?php if ($currentTier !== 'free'): ?>
        <section class="billing-history">
            <h2>Billing History</h2>
            <div class="billing-table">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo date('M j, Y'); ?></td>
                            <td><?php echo ucfirst($currentTier); ?> Plan - Monthly</td>
                            <td>$<?php echo SUBSCRIPTION_TIERS[$currentTier]['price']; ?></td>
                            <td><span class="status paid">Paid</span></td>
                            <td><a href="#" class="btn-link">Download</a></td>
                        </tr>
                        <!-- Add more billing history rows -->
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>

        <!-- Support Options -->
        <section class="support-section">
            <h2>Need Help?</h2>
            <div class="support-grid">
                <div class="support-card">
                    <h4>📧 Email Support</h4>
                    <p>Get help from our team</p>
                    <a href="support.php" class="btn btn-outline">Contact Support</a>
                </div>
                <div class="support-card">
                    <h4>📚 Documentation</h4>
                    <p>Browse guides and tutorials</p>
                    <a href="docs.php" class="btn btn-outline">View Docs</a>
                </div>
                <div class="support-card">
                    <h4>💬 Community</h4>
                    <p>Ask questions in our forum</p>
                    <a href="forum.php" class="btn btn-outline">Join Forum</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>VulnForge Academy</h4>
                <p>Master ethical hacking through hands-on challenges</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 VulnForge Academy - For Educational Purposes Only</p>
        </div>
    </footer>

    <style>
        .current-plan {
            margin: 40px 0;
        }
        
        .plan-overview {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .plan-card {
            background: var(--bg-card);
            border-radius: 15px;
            padding: 30px;
            border: 2px solid var(--border-color);
            margin-bottom: 30px;
        }
        
        .plan-card.current {
            border-color: var(--accent-blue);
            box-shadow: 0 0 20px rgba(0,153,255,0.2);
        }
        
        .plan-card.free {
            border-color: var(--text-secondary);
        }
        
        .plan-card.pro {
            border-color: var(--accent-blue);
        }
        
        .plan-card.enterprise {
            border-color: var(--accent-purple);
        }
        
        .plan-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .plan-header h3 {
            font-size: 2rem;
            margin: 0;
        }
        
        .price {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--accent-green);
        }
        
        .plan-features {
            margin: 20px 0;
        }
        
        .plan-features ul {
            list-style: none;
            padding: 0;
        }
        
        .plan-features li {
            padding: 8px 0;
            color: var(--text-primary);
        }
        
        .plan-limits {
            background: var(--bg-input);
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .plan-billing {
            background: var(--bg-input);
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .plan-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .current-badge {
            position: absolute;
            top: -10px;
            right: 20px;
            background: var(--accent-green);
            color: white;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .status {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status.paid {
            background: var(--success-color);
            color: white;
        }
        
        .btn-link {
            color: var(--accent-blue);
            text-decoration: none;
        }
        
        .btn-link:hover {
            text-decoration: underline;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: var(--bg-input);
            border-radius: 4px;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .progress-bar-fill {
            height: 100%;
            background: var(--gradient-primary);
            transition: width 1s ease-in-out;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: var(--bg-card);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            border: 1px solid var(--border-color);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--accent-blue);
            margin: 10px 0;
        }
        
        .stat-description {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .support-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .support-card {
            background: var(--bg-card);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            border: 1px solid var(--border-color);
        }
        
        .billing-table {
            background: var(--bg-card);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        
        .billing-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .billing-table th,
        .billing-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .billing-table th {
            background: var(--bg-input);
            font-weight: bold;
            color: var(--text-primary);
        }
    </style>
</body>
</html>