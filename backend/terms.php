<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Terms of Service - VulnForge Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="index.php">VulnForge Academy</a></div>
    </nav>
    <main class="container">
        <section class="hero">
            <h1>Terms of Service</h1>
            <div class="auth-card" style="max-width: 800px; margin: 40px auto; text-align: left;">
                <p><strong>Last updated:</strong> <?php echo date('F Y'); ?></p>
                
                <h3 style="color: var(--accent-green); margin-top: 20px;">Acceptance of Terms</h3>
                <p>By using VulnForge Academy, you agree to these terms of service.</p>
                
                <h3 style="color: var(--accent-green); margin-top: 20px;">Educational Purpose Only</h3>
                <p>This platform is for <strong>educational purposes only</strong>. All vulnerabilities are intentional and designed for learning. You agree to:</p>
                <ul style="margin: 10px 0 10px 20px;">
                    <li>Use skills learned ethically and legally</li>
                    <li>Never attack systems without authorization</li>
                    <li>Report any real vulnerabilities responsibly</li>
                </ul>
                
                <h3 style="color: var(--accent-green); margin-top: 20px;">Account Responsibility</h3>
                <p>You are responsible for maintaining the security of your account and password.</p>
                
                <h3 style="color: var(--accent-green); margin-top: 20px;">Prohibited Activities</h3>
                <p>Attempting to exploit or attack our actual infrastructure (not the intentional challenges) is strictly prohibited.</p>
            </div>
        </section>
    </main>
</body>
</html>
