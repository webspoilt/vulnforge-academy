<?php
/**
 * LEVEL 5: HTML SOURCE CODE LEAK
 * Difficulty: EASY
 *
 * Sensitive information in HTML comments and hidden fields
 * Solution: View page source (Ctrl+U) to find the flag
 */
require_once '../../config.php';

$error = '';
$success = '';

// Flag submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flag'])) {
    if (checkFlag(5, $_POST['flag'])) {
        if (isset($_SESSION['user_id'])) {
            saveProgress($_SESSION['user_id'], 5, $_POST['flag']);
        }
        $success = "Correct! Level 5 completed!";
    } else {
        $error = "Incorrect flag.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 5: Source Code Leak - VulnForge</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <!-- TODO: Remove debug info before production -->
    <!-- Developer: The API key is sk_test_12345 -->
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../../index.php">VulnForge Academy</a></div>
        <div class="nav-links"><a href="../../dashboard.php">Dashboard</a></div>
    </nav>

    <!-- DEBUG: Admin password backup: admin123 -->
    <!-- NOTE: Flag for this level is hidden in the source -->

    <main class="container">
        <div class="level-header">
            <h1>Level 5: HTML Source Code Analysis</h1>
            <span class="difficulty easy">EASY - 10 Points</span>
        </div>

        <div class="scenario-box">
            <h3>Scenario: Login Page</h3>
            <p>A login page with secrets hidden in the source code.</p>
            <p><strong>Objective:</strong> Find the flag hidden in the HTML source.</p>

            <div class="hints-box">
                <h4>Hints:</h4>
                <ul>
                    <li>Right-click and "View Page Source" (or press Ctrl+U)</li>
                    <li>Developers sometimes leave comments in HTML</li>
                    <li>Check for hidden form fields</li>
                    <li>Look for TODO, DEBUG, NOTE comments</li>
                </ul>
            </div>
        </div>

        <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

        <div class="login-form-demo" style="max-width: 400px; margin: 30px auto; background: var(--bg-card); padding: 30px; border-radius: 10px;">
            <h2>Staff Login</h2>
            <form method="POST" action="#">
                <!-- Hidden field with sensitive data -->
                <input type="hidden" name="debug_mode" value="enabled">
                <input type="hidden" name="api_version" value="2.0-beta">
                <!-- FLAG{source_code_leak} -->
                <input type="hidden" name="internal_token" value="FLAG{source_code_leak}">

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" disabled placeholder="Enter username">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" disabled placeholder="Enter password">
                </div>
                <button type="button" class="btn btn-primary btn-block" disabled>Login (Demo)</button>
            </form>
        </div>

        <!--
        ============================================
        DEVELOPER NOTES - REMOVE BEFORE DEPLOYMENT!
        ============================================
        Database: mysql://admin:secretpass@localhost/vulnforge
        Backup server: 10.0.0.50
        Admin panel: /super-secret-admin/

        Test accounts:
        - admin / admin123
        - test / test

        The flag is in a hidden input field above.
        ============================================
        -->

        <form method="POST" class="flag-form">
            <input type="text" name="flag" placeholder="Enter flag..." required>
            <button type="submit" class="btn btn-primary">Submit Flag</button>
        </form>
    </main>

    <!-- Google Analytics - TODO: Add real tracking ID -->
    <script>
        // Debug: console.log("User session:", "<?php echo session_id(); ?>");
        // var secretKey = "not_the_flag_but_check_html_comments";
    </script>
</body>
</html>
