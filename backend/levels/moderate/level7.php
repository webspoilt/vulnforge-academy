<?php
/**
 * LEVEL 7: COOKIE MANIPULATION
 * Difficulty: MODERATE
 *
 * Role is stored in cookie - change it to become admin
 * Solution: Edit cookie 'role' from 'user' to 'admin'
 */
require_once '../../config.php';

$error = '';
$success = '';

// Set default cookie if not exists
if (!isset($_COOKIE['user_role'])) {
    setcookie('user_role', 'guest', time() + 3600, '/');
    $_COOKIE['user_role'] = 'guest';
}

$role = $_COOKIE['user_role'];

// Check if admin
if ($role === 'admin') {
    $success = "Welcome Admin! FLAG{cookie_monster}";
}

// Flag submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flag'])) {
    if (checkFlag(7, $_POST['flag'])) {
        if (isset($_SESSION['user_id'])) {
            saveProgress($_SESSION['user_id'], 7, $_POST['flag']);
        }
        $success = "Correct! Level 7 completed!";
    } else {
        $error = "Incorrect flag.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 7: Cookie Manipulation - VulnForge</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../../index.php">VulnForge Academy</a></div>
        <div class="nav-links"><a href="../../dashboard.php">Dashboard</a></div>
    </nav>

    <main class="container">
        <div class="level-header">
            <h1>Level 7: Cookie Manipulation</h1>
            <span class="difficulty moderate">MODERATE - 25 Points</span>
        </div>

        <div class="scenario-box">
            <h3>Scenario: Role-Based Access</h3>
            <p>The application stores your role in a cookie. Manipulate it to become admin.</p>
            <p><strong>Objective:</strong> Change your role to admin using browser developer tools.</p>

            <div class="hints-box">
                <h4>Hints:</h4>
                <ul>
                    <li>Open Developer Tools (F12) -> Application -> Cookies</li>
                    <li>Look for the 'user_role' cookie</li>
                    <li>Change its value to 'admin' and refresh</li>
                    <li>Or use: <code>document.cookie = "user_role=admin"</code> in console</li>
                </ul>
            </div>
        </div>

        <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

        <div class="role-display" style="background: var(--bg-card); padding: 30px; border-radius: 10px; text-align: center;">
            <h2>Current Role: <span style="color: <?php echo $role === 'admin' ? '#00ff66' : '#ff6600'; ?>;"><?php echo htmlspecialchars($role); ?></span></h2>
            <p>Your role is stored in a cookie called: <code>user_role</code></p>

            <?php if ($role !== 'admin'): ?>
                <p style="margin-top: 20px; color: var(--text-secondary);">You need admin access to see the flag...</p>
            <?php endif; ?>
        </div>

        <div style="margin-top: 20px; padding: 15px; background: #1a1a25; border-radius: 5px;">
            <strong>Current Cookies:</strong>
            <pre style="margin-top: 10px;"><?php print_r($_COOKIE); ?></pre>
        </div>

        <form method="POST" class="flag-form">
            <input type="text" name="flag" placeholder="Enter flag..." required>
            <button type="submit" class="btn btn-primary">Submit Flag</button>
        </form>
    </main>
</body>
</html>
