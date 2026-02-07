<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$conn = getDbConnection();
$userId = $_SESSION['user_id'];
$completedLevels = [];
$totalPoints = 0;
$userTier = getUserSubscriptionTier($userId);

// Get user statistics
if ($conn) {
    $progress = $conn->query("SELECT level_id FROM user_progress WHERE user_id = $userId");
    if ($progress) {
        while ($row = $progress->fetch_assoc()) {
            $completedLevels[] = intval($row['level_id']);
        }
    }
    
    $pointsResult = $conn->query("SELECT points, subscription_tier, email_verified FROM users WHERE id = $userId");
    if ($pointsResult) {
        $userData = $pointsResult->fetch_assoc();
        $totalPoints = $userData['points'] ?? 0;
        $userTier = $userData['subscription_tier'] ?? 'free';
        $emailVerified = $userData['email_verified'] ?? 0;
    }
    
    // Get recent activity
    $recentActivity = $conn->query("SELECT * FROM user_progress WHERE user_id = $userId ORDER BY solved_at DESC LIMIT 5");
    $recentLevels = [];
    if ($recentActivity) {
        while ($row = $recentActivity->fetch_assoc()) {
            $recentLevels[] = $row;
        }
    }
}

$maxAccessibleLevel = SUBSCRIPTION_TIERS[$userTier]['max_levels'] ?? 10;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - VulnForge Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="description" content="Your VulnForge Academy dashboard - track progress, manage subscription, and access challenges">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="index.php">VulnForge Academy</a></div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="leaderboard.php">Leaderboard</a>
            <a href="profile.php">Profile</a>
            <a href="pricing.php">Pricing</a>
            <a href="certificates.php">Certificates</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <main class="container">
        <!-- Dashboard Header -->
        <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1>Challenge Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
                <div class="subscription-badge">
                    <span class="tier-badge <?php echo $userTier; ?>">
                        <?php echo ucfirst($userTier); ?> Plan
                    </span>
                    <?php if ($userTier === 'free'): ?>
                        <a href="pricing.php" class="upgrade-link">Upgrade for more features →</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="stats-box">
                <div class="stat-item">
                    <h3 style="color: var(--accent-green);"><?php echo $totalPoints; ?></h3>
                    <p>Total Points</p>
                </div>
                <div class="stat-item">
                    <h3 style="color: var(--accent-blue);"><?php echo count($completedLevels); ?>/20</h3>
                    <p>Levels Completed</p>
                </div>
                <div class="stat-item">
                    <h3 style="color: var(--accent-purple);"><?php echo $maxAccessibleLevel; ?></h3>
                    <p>Accessible Levels</p>
                </div>
            </div>
        </div>

        <!-- Subscription Status Banner -->
        <?php if ($userTier === 'free'): ?>
            <div class="upgrade-banner" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 15px; margin-bottom: 30px; color: white; text-align: center;">
                <h3>🚀 Unlock Your Full Potential</h3>
                <p>Upgrade to Pro and access all 20 levels, premium challenges, and certificates!</p>
                <a href="pricing.php" class="btn" style="background: white; color: #667eea; margin-top: 10px;">Upgrade Now</a>
            </div>
        <?php endif; ?>

        <!-- Email Verification Notice -->
        <?php if (!$emailVerified): ?>
            <div class="verification-banner" style="background: var(--warning-color); padding: 15px; border-radius: 10px; margin-bottom: 20px; color: white; text-align: center;">
                <p>📧 Please verify your email address to access all features. <a href="verify-email.php" style="color: white; text-decoration: underline;">Resend verification email</a></p>
            </div>
        <?php endif; ?>

        <!-- Quick Stats -->
        <div class="quick-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
            <div class="stat-card">
                <h4>Current Streak</h4>
                <p class="stat-number">7 days</p>
            </div>
            <div class="stat-card">
                <h4>Average Solve Time</h4>
                <p class="stat-number">25 min</p>
            </div>
            <div class="stat-card">
                <h4>Success Rate</h4>
                <p class="stat-number">85%</p>
            </div>
            <div class="stat-card">
                <h4>Rank</h4>
                <p class="stat-number">#<?php echo rand(1, 100); ?></p>
            </div>
        </div>

        <!-- Recent Activity -->
        <?php if (!empty($recentLevels)): ?>
        <section class="recent-activity" style="margin-bottom: 40px;">
            <h2>Recent Activity</h2>
            <div class="activity-list">
                <?php foreach ($recentLevels as $activity): ?>
                <div class="activity-item">
                    <div class="activity-icon" style="background: var(--success-color);">
                        ✓
                    </div>
                    <div class="activity-content">
                        <h4>Level <?php echo $activity['level_id']; ?> Completed</h4>
                        <p><?php echo $LEVELS[$activity['level_id']]['name'] ?? 'Unknown'; ?> • +<?php echo $activity['points_earned']; ?> points</p>
                        <small><?php echo date('M j, Y g:i A', strtotime($activity['solved_at'])); ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Challenge Levels -->
        <section class="challenges">
            <h2 style="margin-bottom: 30px;">Vulnerability Challenges</h2>
            
            <!-- Easy Levels -->
            <div class="difficulty-section">
                <h3 style="color: var(--success-color); margin-bottom: 20px;">
                    EASY (Beginner) • Levels 1-5
                </h3>
                <div class="levels-grid">
                    <?php
                    $easyLevels = [
                        1 => ['name' => 'Basic SQL Injection', 'desc' => 'Simple SQLi with no filters', 'points' => 10],
                        2 => ['name' => 'Reflected XSS', 'desc' => 'Inject JavaScript into the page', 'points' => 10],
                        3 => ['name' => 'Simple IDOR', 'desc' => 'Access other users data by ID', 'points' => 10],
                        4 => ['name' => 'Robots.txt Secrets', 'desc' => 'Find hidden directories', 'points' => 10],
                        5 => ['name' => 'HTML Source Leak', 'desc' => 'Secrets in page source', 'points' => 10],
                    ];
                    foreach ($easyLevels as $num => $level):
                        $completed = in_array($num, $completedLevels);
                        $accessible = $num <= $maxAccessibleLevel;
                        $statusClass = $completed ? 'completed' : ($accessible ? 'available' : 'locked');
                    ?>
                    <div class="level-card <?php echo $statusClass; ?>">
                        <div class="level-header">
                            <span class="difficulty easy">EASY</span>
                            <span class="points"><?php echo $level['points']; ?>pts</span>
                        </div>
                        <h3>Level <?php echo $num; ?>: <?php echo $level['name']; ?></h3>
                        <p><?php echo $level['desc']; ?></p>
                        <div class="level-footer">
                            <?php if ($completed): ?>
                                <span class="badge completed">✓ COMPLETED</span>
                            <?php elseif ($accessible): ?>
                                <a href="levels/level<?php echo $num; ?>.php" class="btn btn-primary">Start Challenge</a>
                            <?php else: ?>
                                <span class="badge locked">🔒 UPGRADE REQUIRED</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Moderate Levels -->
            <div class="difficulty-section">
                <h3 style="color: var(--warning-color); margin-bottom: 20px;">
                    MODERATE • Levels 6-10
                </h3>
                <div class="levels-grid">
                    <?php
                    $moderateLevels = [
                        6 => ['name' => 'Authentication Bypass', 'desc' => 'Circumvent login restrictions', 'points' => 25],
                        7 => ['name' => 'Cookie Manipulation', 'desc' => 'Exploit session management', 'points' => 25],
                        8 => ['name' => 'Hidden Parameters', 'desc' => 'Discover undocumented features', 'points' => 25],
                        9 => ['name' => 'File Upload Bypass', 'desc' => 'Upload malicious files', 'points' => 25],
                        10 => ['name' => 'Local File Inclusion', 'desc' => 'Include sensitive system files', 'points' => 25],
                    ];
                    foreach ($moderateLevels as $num => $level):
                        $completed = in_array($num, $completedLevels);
                        $accessible = $num <= $maxAccessibleLevel;
                        $statusClass = $completed ? 'completed' : ($accessible ? 'available' : 'locked');
                    ?>
                    <div class="level-card <?php echo $statusClass; ?>">
                        <div class="level-header">
                            <span class="difficulty moderate">MODERATE</span>
                            <span class="points"><?php echo $level['points']; ?>pts</span>
                        </div>
                        <h3>Level <?php echo $num; ?>: <?php echo $level['name']; ?></h3>
                        <p><?php echo $level['desc']; ?></p>
                        <div class="level-footer">
                            <?php if ($completed): ?>
                                <span class="badge completed">✓ COMPLETED</span>
                            <?php elseif ($accessible): ?>
                                <a href="levels/level<?php echo $num; ?>.php" class="btn btn-primary">Start Challenge</a>
                            <?php else: ?>
                                <span class="badge locked">🔒 UPGRADE REQUIRED</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Hard+ Levels (Pro only) -->
            <?php if ($userTier !== 'free'): ?>
            <div class="difficulty-section">
                <h3 style="color: var(--error-color); margin-bottom: 20px;">
                    HARD+ (PRO) • Levels 11-20
                </h3>
                <div class="levels-grid">
                    <?php
                    $hardLevels = [
                        11 => ['name' => 'Blind SQLi + WAF', 'desc' => 'Advanced SQL injection with WAF bypass', 'points' => 50],
                        12 => ['name' => 'XSS Filter Evasion', 'desc' => 'Bypass XSS protection filters', 'points' => 50],
                        13 => ['name' => 'IDOR Hash Cracking', 'desc' => 'Break weak hash implementations', 'points' => 50],
                        14 => ['name' => 'CSRF Token Prediction', 'desc' => 'Predict and exploit CSRF tokens', 'points' => 50],
                        15 => ['name' => 'Path Traversal Encoding', 'desc' => 'Advanced directory traversal', 'points' => 50],
                        16 => ['name' => 'Blind Command Injection', 'desc' => 'Time-based command execution', 'points' => 100],
                        17 => ['name' => 'XXE Out-of-Band', 'desc' => 'External entity exploitation', 'points' => 100],
                        18 => ['name' => 'Race Condition', 'desc' => 'Concurrent request exploitation', 'points' => 100],
                        19 => ['name' => 'SSRF Filter Bypass', 'desc' => 'Server-side request forgery advanced', 'points' => 200],
                        20 => ['name' => 'Deserialization RCE', 'desc' => 'Remote code execution via deserialization', 'points' => 200],
                    ];
                    foreach ($hardLevels as $num => $level):
                        $completed = in_array($num, $completedLevels);
                        $accessible = $num <= $maxAccessibleLevel;
                        $statusClass = $completed ? 'completed' : ($accessible ? 'available' : 'locked');
                        $difficulty = $num <= 15 ? 'hard' : ($num <= 18 ? 'expert' : 'nightmare');
                    ?>
                    <div class="level-card <?php echo $statusClass; ?>">
                        <div class="level-header">
                            <span class="difficulty <?php echo $difficulty; ?>"><?php echo strtoupper($difficulty); ?></span>
                            <span class="points"><?php echo $level['points']; ?>pts</span>
                        </div>
                        <h3>Level <?php echo $num; ?>: <?php echo $level['name']; ?></h3>
                        <p><?php echo $level['desc']; ?></p>
                        <div class="level-footer">
                            <?php if ($completed): ?>
                                <span class="badge completed">✓ COMPLETED</span>
                            <?php elseif ($accessible): ?>
                                <a href="levels/level<?php echo $num; ?>.php" class="btn btn-primary">Start Challenge</a>
                            <?php else: ?>
                                <span class="badge locked">🔒 UPGRADE REQUIRED</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </section>

        <!-- Monetization: Affiliate Links Section -->
        <section class="affiliate-section" style="margin: 60px 0; padding: 40px; background: var(--bg-card); border-radius: 15px; border: 1px solid var(--border-color);">
            <h2 style="text-align: center; margin-bottom: 30px;">Recommended Learning Resources</h2>
            <div class="affiliate-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px;">
                <div class="affiliate-card">
                    <h4>🔐 OWASP Top 10 Course</h4>
                    <p>Master the most critical web application security risks</p>
                    <a href="<?php echo AFFILIATE_BASE_URL; ?>/owasp-top10" target="_blank" class="btn btn-outline">Learn More</a>
                </div>
                <div class="affiliate-card">
                    <h4>🎯 Bug Bounty Methodology</h4>
                    <p>Professional bug hunting techniques and workflows</p>
                    <a href="<?php echo AFFILIATE_BASE_URL; ?>/bug-bounty-methodology" target="_blank" class="btn btn-outline">Learn More</a>
                </div>
                <div class="affiliate-card">
                    <h4>⚡ Burp Suite Mastery</h4>
                    <p>Advanced penetration testing with Burp Suite</p>
                    <a href="<?php echo AFFILIATE_BASE_URL; ?>/burp-suite-mastery" target="_blank" class="btn btn-outline">Learn More</a>
                </div>
            </div>
            <p style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: var(--text-secondary);">
                ✨ These are affiliate links. We earn a small commission if you purchase, at no extra cost to you.
            </p>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>VulnForge Academy</h4>
                <p>Master ethical hacking through hands-on challenges</p>
            </div>
            <div class="footer-section">
                <h4>Support</h4>
                <ul>
                    <li><a href="support.php">Help Center</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="certificates.php">Certificates</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 VulnForge Academy - For Educational Purposes Only</p>
        </div>
    </footer>

    <style>
        .subscription-badge {
            margin-top: 10px;
        }
        
        .tier-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .tier-badge.free {
            background: var(--text-secondary);
            color: white;
        }
        
        .tier-badge.pro {
            background: var(--accent-blue);
            color: white;
        }
        
        .tier-badge.enterprise {
            background: var(--accent-purple);
            color: white;
        }
        
        .upgrade-link {
            margin-left: 10px;
            color: var(--accent-blue);
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .stats-box {
            display: flex;
            gap: 30px;
            text-align: center;
        }
        
        .stat-item h3 {
            margin-bottom: 5px;
        }
        
        .quick-stats .stat-card {
            background: var(--bg-card);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid var(--border-color);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--accent-blue);
        }
        
        .activity-list {
            background: var(--bg-card);
            border-radius: 10px;
            padding: 20px;
            border: 1px solid var(--border-color);
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .level-card {
            background: var(--bg-card);
            padding: 25px;
            border-radius: 15px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .level-card.available:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-heavy);
            border-color: var(--accent-blue);
        }
        
        .level-card.completed {
            border-color: var(--success-color);
            background: linear-gradient(135deg, var(--bg-card) 0%, rgba(0,204,102,0.1) 100%);
        }
        
        .level-card.locked {
            opacity: 0.6;
        }
        
        .level-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .points {
            background: var(--accent-yellow);
            color: var(--bg-dark);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .level-footer {
            margin-top: 20px;
        }
        
        .badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .badge.completed {
            background: var(--success-color);
            color: white;
        }
        
        .badge.locked {
            background: var(--text-secondary);
            color: white;
        }
        
        .affiliate-card {
            background: var(--bg-input);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        
        .difficulty-section {
            margin-bottom: 50px;
        }
    </style>
</body>
</html>