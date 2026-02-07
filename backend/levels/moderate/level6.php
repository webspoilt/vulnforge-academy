<?php
/**
 * LEVEL 6: AUTHENTICATION BYPASS
 * Difficulty: MODERATE
 *
 * SQL injection in login - need to bypass password check
 * Solution: username: admin'-- or admin' OR '1'='1'--
 */
require_once '../../config.php';

$error = '';
$success = '';
$loggedIn = false;
$user = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $conn = getDbConnection();

    // VULNERABLE: SQL injection in login query
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '" . md5($password) . "'";

    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $loggedIn = true;

        if ($user['role'] === 'admin') {
            $success = "Welcome Admin! FLAG{auth_bypass_win}";
        } else {
            $success = "Logged in as: " . htmlspecialchars($user['username']);
        }
    } else {
        $error = "Invalid credentials";
    }
}

// Flag submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flag'])) {
    if (checkFlag(6, $_POST['flag'])) {
        if (isset($_SESSION['user_id'])) {
            saveProgress($_SESSION['user_id'], 6, $_POST['flag']);
        }
        $success = "Correct! Level 6 completed!";
    } else {
        $error = "Incorrect flag.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 6: Auth Bypass - VulnForge</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../../index.php">VulnForge Academy</a></div>
        <div class="nav-links"><a href="../../dashboard.php">Dashboard</a></div>
    </nav>

    <main class="container">
        <div class="level-header">
            <h1>Level 6: Authentication Bypass</h1>
            <span class="difficulty moderate">MODERATE - 25 Points</span>
        </div>

        <div class="scenario-box">
            <h3>Scenario: Admin Login Portal</h3>
            <p>A login page vulnerable to SQL injection. Bypass authentication to login as admin.</p>
            <p><strong>Objective:</strong> Login as the admin user without knowing the password.</p>

            <div class="hints-box">
                <h4>Hints:</h4>
                <ul>
                    <li>The login query checks: username AND password</li>
                    <li>Can you use SQL injection to always make the query true?</li>
                    <li>Try: <code>admin'--</code> as username (comments out password check)</li>
                </ul>
            </div>
        </div>

        <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

        <?php if (!$loggedIn): ?>
        <div class="auth-card">
            <h2>Login</h2>
            <form method="POST">
                <input type="hidden" name="login" value="1">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
        </div>
        <?php else: ?>
        <div class="alert alert-success">
            <h3>Logged in as: <?php echo htmlspecialchars($user['username']); ?></h3>
            <p>Role: <?php echo htmlspecialchars($user['role']); ?></p>
        </div>
        <?php endif; ?>

        <form method="POST" class="flag-form">
            <input type="text" name="flag" placeholder="Enter flag..." required>
            <button type="submit" class="btn btn-primary">Submit Flag</button>
        </form>
    </main>
</body>
</html>
