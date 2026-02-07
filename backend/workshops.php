<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Workshops - VulnForge Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="index.php">VulnForge Academy</a></div>
        <div class="nav-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </nav>
    <main class="container">
        <section class="hero">
            <h1>Live Workshops</h1>
            <p class="subtitle">Interactive cybersecurity training sessions with industry experts</p>
            <div class="auth-card" style="max-width: 600px; margin: 40px auto;">
                <h3 style="color: var(--accent-green);">Coming Soon!</h3>
                <p>We're preparing exciting live workshops covering:</p>
                <ul style="text-align: left; margin: 20px 0;">
                    <li>Advanced SQL Injection Techniques</li>
                    <li>Bug Bounty Hunting Masterclass</li>
                    <li>Web Application Pentesting</li>
                    <li>Real-world CTF Preparation</li>
                </ul>
                <p>Join the <a href="pricing.php">Pro plan</a> to get early access!</p>
            </div>
        </section>
    </main>
</body>
</html>
