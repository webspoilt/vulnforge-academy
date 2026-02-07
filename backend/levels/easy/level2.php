<?php
/**
 * LEVEL 2: REFLECTED XSS
 * Difficulty: EASY
 *
 * Basic XSS with no filtering - input is reflected directly
 * Solution: <script>alert('XSS')</script>
 */
require_once '../../config.php';

$name = $_GET['name'] ?? '';
$error = '';
$success = '';

// Flag submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flag'])) {
    if (checkFlag(2, $_POST['flag'])) {
        if (isset($_SESSION['user_id'])) {
            saveProgress($_SESSION['user_id'], 2, $_POST['flag']);
        }
        $success = "Correct! Level 2 completed!";
    } else {
        $error = "Incorrect flag.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 2: Reflected XSS - VulnForge</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../../index.php">VulnForge Academy</a></div>
        <div class="nav-links"><a href="../../dashboard.php">Dashboard</a></div>
    </nav>

    <main class="container">
        <div class="level-header">
            <h1>Level 2: Reflected XSS</h1>
            <span class="difficulty easy">EASY - 10 Points</span>
        </div>

        <div class="scenario-box">
            <h3>Scenario: Greeting Page</h3>
            <p>A simple page that greets users by name. The name is reflected without sanitization.</p>
            <p><strong>Objective:</strong> Execute JavaScript to display an alert box.</p>

            <div class="hints-box">
                <h4>Hints:</h4>
                <ul>
                    <li>XSS means Cross-Site Scripting - injecting JavaScript</li>
                    <li>Try: <code>&lt;script&gt;alert('XSS')&lt;/script&gt;</code></li>
                    <li>The page reflects your input directly in HTML</li>
                </ul>
            </div>
        </div>

        <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

        <form method="GET" class="search-form">
            <input type="text" name="name" placeholder="Enter your name..." value="<?php echo htmlspecialchars($name); ?>">
            <button type="submit" class="btn btn-primary">Greet Me</button>
        </form>

        <?php if ($name): ?>
        <div class="greeting-box" style="padding: 30px; background: var(--bg-card); border-radius: 10px; margin: 20px 0; text-align: center;">
            <h2>Hello, <?php echo $name; ?>!</h2>
            <!-- VULNERABLE: No escaping! -->
            <p>Welcome to VulnForge Academy.</p>
        </div>
        <?php endif; ?>

        <form method="POST" class="flag-form">
            <input type="text" name="flag" placeholder="Enter flag..." required>
            <button type="submit" class="btn btn-primary">Submit Flag</button>
        </form>
    </main>

    <script>
    // Detect successful XSS
    var originalAlert = window.alert;
    window.alert = function(msg) {
        document.body.innerHTML += '<div class="alert alert-success" style="margin:20px;">XSS Successful! Flag: FLAG{xss_alert_box}</div>';
        originalAlert(msg);
    };
    </script>
</body>
</html>
