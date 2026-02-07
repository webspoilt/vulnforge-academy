<?php
/**
 * LEVEL 4: ROBOTS.TXT SECRETS
 * Difficulty: EASY
 *
 * Information disclosure via robots.txt
 * Solution: Check /robots.txt, find hidden directory
 */
require_once '../../config.php';

$error = '';
$success = '';

// Flag submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flag'])) {
    if (checkFlag(4, $_POST['flag'])) {
        if (isset($_SESSION['user_id'])) {
            saveProgress($_SESSION['user_id'], 4, $_POST['flag']);
        }
        $success = "Correct! Level 4 completed!";
    } else {
        $error = "Incorrect flag.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 4: Robots.txt Secrets - VulnForge</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../../index.php">VulnForge Academy</a></div>
        <div class="nav-links"><a href="../../dashboard.php">Dashboard</a></div>
    </nav>

    <main class="container">
        <div class="level-header">
            <h1>Level 4: Information Disclosure</h1>
            <span class="difficulty easy">EASY - 10 Points</span>
        </div>

        <div class="scenario-box">
            <h3>Scenario: Corporate Website</h3>
            <p>A company website that accidentally exposes sensitive paths.</p>
            <p><strong>Objective:</strong> Find the hidden admin area using common reconnaissance techniques.</p>

            <div class="hints-box">
                <h4>Hints:</h4>
                <ul>
                    <li>What file do search engines check to know what NOT to index?</li>
                    <li>Try visiting: <code>/robots.txt</code></li>
                    <li>Developers sometimes hide directories in robots.txt</li>
                </ul>
            </div>
        </div>

        <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

        <div class="company-page" style="background: var(--bg-card); padding: 30px; border-radius: 10px;">
            <h2>Welcome to SecureCorp</h2>
            <p>We are a leading cybersecurity company providing enterprise solutions.</p>
            <p>Contact us at: info@securecorp.fake</p>

            <h3 style="margin-top: 20px;">Our Services:</h3>
            <ul>
                <li>Penetration Testing</li>
                <li>Security Audits</li>
                <li>Incident Response</li>
            </ul>
        </div>

        <div style="margin-top: 30px;">
            <h4>Common Recon Files:</h4>
            <ul style="list-style: none;">
                <li><a href="../../robots.txt">/robots.txt</a> - Crawler instructions</li>
                <li><a href="../../sitemap.xml">/sitemap.xml</a> - Site structure</li>
                <li><a href="../../.git/">.git/</a> - Version control (if exposed)</li>
            </ul>
        </div>

        <form method="POST" class="flag-form">
            <input type="text" name="flag" placeholder="Enter flag..." required>
            <button type="submit" class="btn btn-primary">Submit Flag</button>
        </form>
    </main>
</body>
</html>
