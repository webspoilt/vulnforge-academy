<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us - VulnForge Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="index.php">VulnForge Academy</a></div>
    </nav>
    <main class="container">
        <section class="hero">
            <h1>Contact Us</h1>
            <p class="subtitle">We'd love to hear from you</p>
            <div class="auth-card" style="max-width: 500px; margin: 40px auto;">
                <form method="POST">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="message" rows="5" required style="width: 100%; padding: 10px; background: var(--bg-dark); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: 8px;"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                </form>
                <p style="margin-top: 20px; text-align: center;">Or email us directly at: <a href="mailto:support@vulnforge.academy">support@vulnforge.academy</a></p>
            </div>
        </section>
    </main>
</body>
</html>
