<?php
/**
 * LEVEL 8: HIDDEN PARAMETERS
 * Difficulty: MODERATE
 *
 * Hidden form field controls admin access
 * Solution: Change hidden field 'is_admin' to '1' or 'true'
 */
require_once '../../config.php';

$error = '';
$success = '';
$showSecret = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_form'])) {
    $isAdmin = $_POST['is_admin'] ?? '0';
    $username = $_POST['username'] ?? '';

    // VULNERABLE: Trusts hidden field!
    if ($isAdmin === '1' || $isAdmin === 'true') {
        $showSecret = true;
        $success = "Admin access granted! FLAG{hidden_params}";
    } else {
        $success = "Welcome, " . htmlspecialchars($username) . "! (Regular user)";
    }
}

// Flag submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flag'])) {
    if (checkFlag(8, $_POST['flag'])) {
        if (isset($_SESSION['user_id'])) {
            saveProgress($_SESSION['user_id'], 8, $_POST['flag']);
        }
        $success = "Correct! Level 8 completed!";
    } else {
        $error = "Incorrect flag.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 8: Hidden Parameters - VulnForge</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../../index.php">VulnForge Academy</a></div>
        <div class="nav-links"><a href="../../dashboard.php">Dashboard</a></div>
    </nav>

    <main class="container">
        <div class="level-header">
            <h1>Level 8: Hidden Parameter Tampering</h1>
            <span class="difficulty moderate">MODERATE - 25 Points</span>
        </div>

        <div class="scenario-box">
            <h3>Scenario: User Registration</h3>
            <p>A registration form with a hidden field that controls admin status.</p>
            <p><strong>Objective:</strong> Modify the hidden field to register as admin.</p>

            <div class="hints-box">
                <h4>Hints:</h4>
                <ul>
                    <li>Inspect the form HTML (right-click -> Inspect)</li>
                    <li>Look for hidden input fields</li>
                    <li>Change the hidden field's value before submitting</li>
                    <li>Or intercept the request with Burp Suite</li>
                </ul>
            </div>
        </div>

        <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

        <div class="auth-card">
            <h2>Register Account</h2>
            <form method="POST">
                <!-- VULNERABLE: Hidden field controls admin status! -->
                <input type="hidden" name="is_admin" value="0">
                <input type="hidden" name="role" value="user">
                <input type="hidden" name="submit_form" value="1">

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>
        </div>

        <p style="margin-top: 20px; color: var(--text-secondary);">
            <em>Tip: Hidden fields are not visible but still sent with the form...</em>
        </p>

        <form method="POST" class="flag-form">
            <input type="text" name="flag" placeholder="Enter flag..." required>
            <button type="submit" class="btn btn-primary">Submit Flag</button>
        </form>
    </main>
</body>
</html>
