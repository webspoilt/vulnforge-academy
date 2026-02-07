<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$conn = getDbConnection();

// Get user's completed levels
$completedLevels = getProgress($userId);
$totalPoints = 0;
$userTier = getUserSubscriptionTier($userId);

// Get user data
if ($conn) {
    $userResult = $conn->query("SELECT username, points, subscription_tier FROM users WHERE id = $userId");
    if ($userResult && $userData = $userResult->fetch_assoc()) {
        $totalPoints = $userData['points'] ?? 0;
        $username = $userData['username'] ?? 'User';
        $userTier = $userData['subscription_tier'] ?? 'free';
    }
}

// Certificate generation logic
$canGenerateCertificate = false;
$certificateType = '';
$completionPercentage = (count($completedLevels) / 20) * 100;

if ($userTier === 'free' && count($completedLevels) >= 5) {
    $canGenerateCertificate = true;
    $certificateType = 'Basic Completion';
} elseif ($userTier === 'pro' && count($completedLevels) >= 15) {
    $canGenerateCertificate = true;
    $certificateType = 'Advanced Completion';
} elseif ($userTier === 'enterprise' && count($completedLevels) >= 20) {
    $canGenerateCertificate = true;
    $certificateType = 'Expert Completion';
}

// Handle certificate generation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_certificate'])) {
    if ($canGenerateCertificate) {
        // Generate unique verification code
        $verificationCode = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 12));
        
        // Save certificate to database
        if ($conn) {
            $stmt = $conn->prepare("INSERT INTO certificates (user_id, certificate_type, course_name, score, verification_code) VALUES (?, ?, ?, ?, ?)");
            $courseName = "VulnForge Academy - $certificateType";
            $score = round($completionPercentage);
            $stmt->bind_param("issis", $userId, $certificateType, $courseName, $score, $verificationCode);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $generatedCertificate = true;
                $certVerificationCode = $verificationCode;
            }
        }
    }
}

// Get user's existing certificates
$certificates = [];
if ($conn) {
    $certResult = $conn->query("SELECT * FROM certificates WHERE user_id = $userId ORDER BY issued_at DESC");
    if ($certResult) {
        while ($row = $certResult->fetch_assoc()) {
            $certificates[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificates - VulnForge Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .certificate-preview {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            color: #333;
            border: 10px solid #2c3e50;
            padding: 40px;
            margin: 30px 0;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .certificate-header {
            text-align: center;
            border-bottom: 3px solid #3498db;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .certificate-logo {
            font-size: 3rem;
            color: #e74c3c;
            margin-bottom: 10px;
        }
        
        .certificate-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2c3e50;
            margin: 20px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .certificate-subtitle {
            font-size: 1.2rem;
            color: #7f8c8d;
            font-style: italic;
        }
        
        .certificate-body {
            text-align: center;
            margin: 40px 0;
        }
        
        .recipient-name {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            margin: 20px 0;
            text-decoration: underline;
        }
        
        .achievement-text {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #34495e;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .completion-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin: 30px 0;
            text-align: center;
        }
        
        .stat-item {
            background: rgba(52, 73, 94, 0.1);
            padding: 15px;
            border-radius: 10px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #e74c3c;
        }
        
        .certificate-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #bdc3c7;
        }
        
        .signature-area {
            text-align: center;
        }
        
        .signature-line {
            border-top: 2px solid #2c3e50;
            width: 200px;
            margin: 10px 0 5px 0;
        }
        
        .verification-code {
            background: #ecf0f1;
            padding: 10px 20px;
            border-radius: 5px;
            font-family: monospace;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .certificate-actions {
            text-align: center;
            margin: 30px 0;
        }
        
        .certificate-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin: 40px 0;
        }
        
        .certificate-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s ease;
        }
        
        .certificate-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-heavy);
        }
        
        .certificate-type {
            background: var(--accent-blue);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .certificate-type.basic {
            background: var(--success-color);
        }
        
        .certificate-type.advanced {
            background: var(--accent-blue);
        }
        
        .certificate-type.expert {
            background: var(--accent-purple);
        }
        
        .eligibility-check {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            text-align: center;
        }
        
        .eligibility-status {
            font-size: 1.2rem;
            margin: 20px 0;
        }
        
        .eligible {
            color: var(--success-color);
        }
        
        .not-eligible {
            color: var(--warning-color);
        }
        
        .requirements-list {
            text-align: left;
            max-width: 400px;
            margin: 20px auto;
        }
        
        .requirement {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }
        
        .requirement.met {
            color: var(--success-color);
        }
        
        .requirement.unmet {
            color: var(--warning-color);
        }
        
        .requirement-icon {
            margin-right: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="index.php">VulnForge Academy</a></div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="certificates.php" class="active">Certificates</a>
            <a href="subscription.php">Subscription</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <main class="container">
        <h1>Certificates</h1>
        <p style="color: var(--text-secondary); margin-bottom: 30px;">
            Showcase your cybersecurity skills with official VulnForge Academy certificates
        </p>

        <?php if (isset($generatedCertificate)): ?>
            <div class="alert alert-success">
                <strong>Certificate Generated Successfully!</strong><br>
                Verification Code: <span class="verification-code"><?php echo $certVerificationCode; ?></span><br>
                <small>You can verify this certificate using the code above.</small>
            </div>
        <?php endif; ?>

        <!-- Eligibility Check -->
        <section class="eligibility-check">
            <h2>Certificate Eligibility</h2>
            <div class="eligibility-status <?php echo $canGenerateCertificate ? 'eligible' : 'not-eligible'; ?>">
                <?php if ($canGenerateCertificate): ?>
                    ✓ You're eligible to generate a certificate!
                <?php else: ?>
                    ✗ Complete the requirements to earn a certificate
                <?php endif; ?>
            </div>
            
            <div class="requirements-list">
                <?php
                $requirements = [
                    ['text' => 'Minimum 5 levels completed', 'met' => count($completedLevels) >= 5],
                    ['text' => 'Pro subscription (for Advanced/Expert)', 'met' => $userTier !== 'free'],
                    ['text' => '15+ levels for Advanced certificate', 'met' => count($completedLevels) >= 15],
                    ['text' => 'All 20 levels for Expert certificate', 'met' => count($completedLevels) >= 20]
                ];
                
                foreach ($requirements as $req):
                ?>
                <div class="requirement <?php echo $req['met'] ? 'met' : 'unmet'; ?>">
                    <span class="requirement-icon"><?php echo $req['met'] ? '✓' : '✗'; ?></span>
                    <?php echo $req['text']; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($canGenerateCertificate): ?>
                <form method="POST" style="margin-top: 30px;">
                    <input type="hidden" name="generate_certificate" value="1">
                    <button type="submit" class="btn btn-primary">Generate Certificate</button>
                </form>
            <?php else: ?>
                <div style="margin-top: 20px;">
                    <a href="pricing.php" class="btn btn-primary">Upgrade to Pro</a>
                    <a href="dashboard.php" class="btn btn-secondary">Continue Learning</a>
                </div>
            <?php endif; ?>
        </section>

        <!-- Certificate Preview (when eligible) -->
        <?php if ($canGenerateCertificate && !isset($generatedCertificate)): ?>
        <section class="certificate-preview">
            <div class="certificate-header">
                <div class="certificate-logo">🛡️</div>
                <h1 class="certificate-title">Certificate of Completion</h1>
                <p class="certificate-subtitle">VulnForge Academy Cybersecurity Training</p>
            </div>
            
            <div class="certificate-body">
                <p style="font-size: 1.2rem; margin-bottom: 20px;">This is to certify that</p>
                <div class="recipient-name"><?php echo htmlspecialchars($username); ?></div>
                <p style="font-size: 1.1rem; margin: 30px 0;">has successfully completed the</p>
                <h2 style="color: #e74c3c; font-size: 1.8rem;"><?php echo $certificateType; ?></h2>
                <p class="achievement-text">
                    demonstrating proficiency in ethical, and hacking, vulnerability assessment cybersecurity best practices 
                    through hands-on challenge completion and practical skill development.
                </p>
                
                <div class="completion-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($completedLevels); ?></div>
                        <div>Levels Completed</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo round($completionPercentage); ?>%</div>
                        <div>Completion Rate</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $totalPoints; ?></div>
                        <div>Total Points</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo ucfirst($userTier); ?></div>
                        <div>Certification Level</div>
                    </div>
                </div>
            </div>
            
            <div class="certificate-footer">
                <div class="signature-area">
                    <div class="signature-line"></div>
                    <p style="margin: 5px 0; font-weight: bold;">Dr. Sarah Chen</p>
                    <p style="font-size: 0.9rem; color: #7f8c8d;">Academic Director</p>
                    <p style="font-size: 0.9rem; color: #7f8c8d;">VulnForge Academy</p>
                </div>
                
                <div class="verification-area">
                    <p style="font-size: 0.9rem; color: #7f8c8d;">Verification Code:</p>
                    <div class="verification-code">[Will be generated]</div>
                </div>
                
                <div class="date-area">
                    <p style="font-size: 0.9rem; color: #7f8c8d;">Date of Completion:</p>
                    <p style="font-weight: bold;"><?php echo date('F j, Y'); ?></p>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Generated Certificate Display -->
        <?php if (isset($generatedCertificate)): ?>
        <section class="certificate-preview">
            <div class="certificate-header">
                <div class="certificate-logo">🛡️</div>
                <h1 class="certificate-title">Certificate of Completion</h1>
                <p class="certificate-subtitle">VulnForge Academy Cybersecurity Training</p>
            </div>
            
            <div class="certificate-body">
                <p style="font-size: 1.2rem; margin-bottom: 20px;">This is to certify that</p>
                <div class="recipient-name"><?php echo htmlspecialchars($username); ?></div>
                <p style="font-size: 1.1rem; margin: 30px 0;">has successfully completed the</p>
                <h2 style="color: #e74c3c; font-size: 1.8rem;"><?php echo $certificateType; ?></h2>
                <p class="achievement-text">
                    demonstrating proficiency in ethical hacking, vulnerability assessment, and cybersecurity best practices 
                    through hands-on challenge completion and practical skill development.
                </p>
                
                <div class="completion-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($completedLevels); ?></div>
                        <div>Levels Completed</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo round($completionPercentage); ?>%</div>
                        <div>Completion Rate</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $totalPoints; ?></div>
                        <div>Total Points</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $score ?? 0; ?>%</div>
                        <div>Final Score</div>
                    </div>
                </div>
            </div>
            
            <div class="certificate-footer">
                <div class="signature-area">
                    <div class="signature-line"></div>
                    <p style="margin: 5px 0; font-weight: bold;">Dr. Sarah Chen</p>
                    <p style="font-size: 0.9rem; color: #7f8c8d;">Academic Director</p>
                    <p style="font-size: 0.9rem; color: #7f8c8d;">VulnForge Academy</p>
                </div>
                
                <div class="verification-area">
                    <p style="font-size: 0.9rem; color: #7f8c8d;">Verification Code:</p>
                    <div class="verification-code"><?php echo $certVerificationCode; ?></div>
                </div>
                
                <div class="date-area">
                    <p style="font-size: 0.9rem; color: #7f8c8d;">Date of Completion:</p>
                    <p style="font-weight: bold;"><?php echo date('F j, Y'); ?></p>
                </div>
            </div>
            
            <div class="certificate-actions">
                <button onclick="window.print()" class="btn btn-primary">Print Certificate</button>
                <button onclick="downloadCertificate()" class="btn btn-secondary">Download PDF</button>
                <button onclick="shareCertificate('<?php echo $certVerificationCode; ?>')" class="btn btn-outline">Share</button>
            </div>
        </section>
        <?php endif; ?>

        <!-- Existing Certificates -->
        <?php if (!empty($certificates)): ?>
        <section class="existing-certificates">
            <h2>Your Certificates</h2>
            <div class="certificate-grid">
                <?php foreach ($certificates as $cert): ?>
                <div class="certificate-card">
                    <div class="certificate-type <?php echo strtolower($cert['certificate_type']); ?>">
                        <?php echo $cert['certificate_type']; ?>
                    </div>
                    <h3><?php echo htmlspecialchars($cert['course_name']); ?></h3>
                    <p><strong>Score:</strong> <?php echo $cert['score']; ?>%</p>
                    <p><strong>Issued:</strong> <?php echo date('M j, Y', strtotime($cert['issued_at'])); ?></p>
                    <p><strong>Verification Code:</strong> <span class="verification-code"><?php echo $cert['verification_code']; ?></span></p>
                    <div style="margin-top: 15px;">
                        <button onclick="verifyCertificate('<?php echo $cert['verification_code']; ?>')" class="btn btn-outline">Verify</button>
                        <button onclick="shareCertificate('<?php echo $cert['verification_code']; ?>')" class="btn btn-secondary">Share</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Benefits Section -->
        <section class="benefits-section" style="margin: 60px 0;">
            <h2>Why Get Certified?</h2>
            <div class="benefits-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px;">
                <div class="benefit-card">
                    <h4>🎯 Career Advancement</h4>
                    <p>Stand out to employers with verified cybersecurity skills</p>
                </div>
                <div class="benefit-card">
                    <h4>💼 Professional Recognition</h4>
                    <p>Industry-recognized certification for your resume</p>
                </div>
                <div class="benefit-card">
                    <h4>📈 Skill Validation</h4>
                    <p>Prove your hands-on experience with practical challenges</p>
                </div>
                <div class="benefit-card">
                    <h4>🌐 Global Verification</h4>
                    <p>Certificates can be verified online by employers</p>
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

    <script>
        function downloadCertificate() {
            // In a real implementation, this would generate a PDF
            alert('PDF download would be implemented here using a library like jsPDF or Puppeteer');
        }
        
        function shareCertificate(verificationCode) {
            if (navigator.share) {
                navigator.share({
                    title: 'VulnForge Academy Certificate',
                    text: `Check out my VulnForge Academy certification! Verification: ${verificationCode}`,
                    url: window.location.href
                });
            } else {
                // Fallback to clipboard
                const shareText = `Check out my VulnForge Academy certification! Verification: ${verificationCode}`;
                navigator.clipboard.writeText(shareText).then(() => {
                    alert('Certificate details copied to clipboard!');
                });
            }
        }
        
        function verifyCertificate(verificationCode) {
            // In a real implementation, this would verify against the database
            alert(`Certificate verification would be implemented here for code: ${verificationCode}`);
        }
    </script>
</body>
</html>