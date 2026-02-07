<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Privacy Policy - VulnForge Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="index.php">VulnForge Academy</a></div>
    </nav>
    <main class="container">
        <section class="hero">
            <h1>Privacy Policy</h1>
            <div class="auth-card" style="max-width: 800px; margin: 40px auto; text-align: left;">
                <p><strong>Last updated:</strong> <?php echo date('F Y'); ?></p>
                
                <h3 style="color: var(--accent-green); margin-top: 20px;">Information We Collect</h3>
                <p>We collect information you provide directly, including username, email, and progress data.</p>
                
                <h3 style="color: var(--accent-green); margin-top: 20px;">How We Use Information</h3>
                <p>We use your information to provide the VulnForge Academy service, track learning progress, and improve our platform.</p>
                
                <h3 style="color: var(--accent-green); margin-top: 20px;">Data Security</h3>
                <p>We implement security measures to protect your personal information. Passwords are hashed and sensitive data is encrypted.</p>
                
                <h3 style="color: var(--accent-green); margin-top: 20px;">Contact</h3>
                <p>Questions? <a href="contact.php">Contact us</a>.</p>
            </div>
        </section>
    </main>
</body>
</html>
