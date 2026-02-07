<?php
/**
 * LEVEL 10: LOCAL FILE INCLUSION (LFI)
 * Difficulty: MODERATE
 *
 * Include parameter vulnerable to LFI
 * Solution: ?page=../../../etc/passwd or ?page=flag.txt
 */
require_once '../../config.php';

$error = '';
$success = '';
$content = '';

// Create flag file
$flagDir = __DIR__ . '/../../secret/';
if (!file_exists($flagDir)) mkdir($flagDir, 0755, true);
file_put_contents($flagDir . 'flag.txt', 'FLAG{lfi_include}');

$page = $_GET['page'] ?? 'home';

// VULNERABLE: Direct include with minimal filtering
$allowed = ['home', 'about', 'contact'];

// Basic filter that can be bypassed
if (strpos($page, 'http') !== false) {
    $error = "Remote includes blocked!";
} else {
    $filePath = __DIR__ . '/pages/' . $page . '.php';

    // Try to include the file
    // VULNERABLE: Path traversal possible!
    if (file_exists($filePath)) {
        ob_start();
        include($filePath);
        $content = ob_get_clean();
    } else {
        // Try without .php extension
        $rawPath = __DIR__ . '/pages/' . $page;
        if (file_exists($rawPath)) {
            $content = file_get_contents($rawPath);
        } else {
            // VULNERABLE: Will try to include any path!
            $traversalPath = __DIR__ . '/' . $page;
            if (file_exists($traversalPath)) {
                $content = file_get_contents($traversalPath);
            } else {
                $error = "Page not found: " . htmlspecialchars($page);
            }
        }
    }
}

// Flag submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flag'])) {
    if (checkFlag(10, $_POST['flag'])) {
        if (isset($_SESSION['user_id'])) {
            saveProgress($_SESSION['user_id'], 10, $_POST['flag']);
        }
        $success = "Correct! Level 10 completed!";
    } else {
        $error = "Incorrect flag.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 10: LFI - VulnForge</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../../index.php">VulnForge Academy</a></div>
        <div class="nav-links"><a href="../../dashboard.php">Dashboard</a></div>
    </nav>

    <main class="container">
        <div class="level-header">
            <h1>Level 10: Local File Inclusion</h1>
            <span class="difficulty moderate">MODERATE - 25 Points</span>
        </div>

        <div class="scenario-box">
            <h3>Scenario: Page Loader</h3>
            <p>A CMS that loads pages dynamically. The page parameter is vulnerable to LFI.</p>
            <p><strong>Objective:</strong> Read the flag file at /secret/flag.txt</p>

            <div class="hints-box">
                <h4>Hints:</h4>
                <ul>
                    <li>Look at the URL: <code>?page=home</code></li>
                    <li>Use ../ to go up directories: <code>?page=../../secret/flag.txt</code></li>
                    <li>On Linux, try: <code>?page=../../../../../etc/passwd</code></li>
                </ul>
            </div>
        </div>

        <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

        <div class="nav-links-page" style="margin: 20px 0;">
            <a href="?page=home" class="btn <?php echo $page === 'home' ? 'btn-primary' : ''; ?>">Home</a>
            <a href="?page=about" class="btn <?php echo $page === 'about' ? 'btn-primary' : ''; ?>">About</a>
            <a href="?page=contact" class="btn <?php echo $page === 'contact' ? 'btn-primary' : ''; ?>">Contact</a>
        </div>

        <div class="page-content" style="background: var(--bg-card); padding: 30px; border-radius: 10px; min-height: 200px;">
            <h2>Page: <?php echo htmlspecialchars($page); ?></h2>
            <?php if ($content): ?>
                <pre><?php echo htmlspecialchars($content); ?></pre>
            <?php else: ?>
                <p>Welcome to our website!</p>
            <?php endif; ?>
        </div>

        <p style="margin-top: 20px; color: var(--text-secondary);">
            Current URL: <code>?page=<?php echo htmlspecialchars($page); ?></code>
        </p>

        <form method="POST" class="flag-form">
            <input type="text" name="flag" placeholder="Enter flag..." required>
            <button type="submit" class="btn btn-primary">Submit Flag</button>
        </form>
    </main>
</body>
</html>
