<?php
/**
 * LEVEL 2: XSS FILTER MADNESS
 * Difficulty: HARD
 *
 * Multiple XSS filters in place:
 * - HTML entity encoding
 * - Script tag blocking
 * - Event handler blocking
 * - JavaScript protocol blocking
 * - CSP header (but misconfigured)
 *
 * Bypass methods needed:
 * 1. DOM clobbering via form elements
 * 2. Mutation XSS using innerHTML quirks
 * 3. CSP bypass via 'unsafe-eval' on specific endpoint
 * 4. SVG/MathML namespace confusion
 *
 * The vulnerability is in the "preview" feature that uses innerHTML
 * Combined with a JSON endpoint that has 'unsafe-eval'
 *
 * Solution: <svg><set onbegin=alert(1)>
 * Or: <math><mtext><table><mglyph><style><img src=x onerror=alert(1)>
 */
require_once '../config.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

// CSP Header - looks secure but has bypass
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src *;");

$conn = getDbConnection();

// XSS Filters
function xssFilter($input) {
    // Remove script tags (case insensitive)
    $input = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $input);

    // Remove common event handlers
    $events = ['onerror', 'onload', 'onclick', 'onmouseover', 'onfocus', 'onblur', 'onsubmit', 'onchange'];
    foreach ($events as $event) {
        $input = preg_replace("/$event\s*=/i", 'blocked=', $input);
    }

    // Block javascript: protocol
    $input = preg_replace('/javascript\s*:/i', 'blocked:', $input);

    // Block data: protocol
    $input = preg_replace('/data\s*:/i', 'blocked:', $input);

    return $input;
}

// ADVANCED filter - still bypassable
function advancedXssFilter($input) {
    $input = xssFilter($input);

    // Block more tags
    $input = preg_replace('/<(iframe|embed|object|applet|form|input|button|textarea|select)/i', '&lt;$1', $input);

    // Block SVG event handlers
    $input = preg_replace('/<svg[^>]*on\w+\s*=/i', '<svg blocked=', $input);

    return $input;
}

$message = '';
$preview = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $mode = $_POST['mode'] ?? 'safe';

    // Different modes have different filtering
    switch ($mode) {
        case 'safe':
            $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
            break;
        case 'rich':
            // "Rich text" mode - filtered but vulnerable
            $content = advancedXssFilter($content);
            break;
        case 'legacy':
            // "Legacy" mode - hidden option, less filtering
            // VULNERABLE: Only basic filter, SVG bypass works
            $content = xssFilter($content);
            break;
        case 'raw':
            // Hidden debug mode - requires special header
            if ($_SERVER['HTTP_X_DEBUG_MODE'] === 'VulnForge_Raw_2024') {
                // No filtering at all - but needs header bypass
            } else {
                $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
            }
            break;
    }

    $preview = $content;

    // Store message (properly escaped for storage)
    if (isset($_POST['submit'])) {
        $safeTitle = $conn->real_escape_string(htmlspecialchars($title));
        $safeContent = $conn->real_escape_string($content);
        $userId = $_SESSION['user_id'];
        $conn->query("INSERT INTO messages (user_id, title, content) VALUES ($userId, '$safeTitle', '$safeContent')");
        $message = "Message saved!";
    }
}

// Flag submission
if (isset($_POST['flag']) && $_POST['flag'] === $GLOBALS['FLAGS'][2]) {
    $conn->query("INSERT INTO user_progress (user_id, level_id, flag) VALUES ({$_SESSION['user_id']}, 2, '{$_POST['flag']}')");
    header('Location: ../dashboard.php?success=2');
    exit;
}

// Hidden endpoint for CSP bypass testing
if (isset($_GET['api']) && $_GET['api'] === 'preview') {
    header('Content-Type: application/json');
    header("Content-Security-Policy: default-src 'self'; script-src 'unsafe-eval' 'unsafe-inline';");
    echo json_encode(['preview' => $_GET['data'] ?? '', 'status' => 'ok']);
    exit;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 2: XSS Filter Madness</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../index.php">VulnForge Academy</a></div>
        <div class="nav-links">
            <a href="../dashboard.php">Dashboard</a>
        </div>
    </nav>

    <main class="container">
        <div class="level-header">
            <h1>Level 2: XSS Filter Madness</h1>
            <span class="difficulty hard">HARD</span>
        </div>

        <div class="scenario-box">
            <h3>Scenario: Message Board</h3>
            <p>A "secure" message board with multiple XSS filters. The developers are confident it's bulletproof.</p>
            <p><strong>Objective:</strong> Execute JavaScript to steal the admin's session cookie (simulated).</p>
            <p><em>The flag will appear when you successfully execute: <code>alert('XSS')</code></em></p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" class="message-form">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Content</label>
                <textarea name="content" rows="6" required></textarea>
            </div>
            <div class="form-group">
                <label>Mode</label>
                <select name="mode">
                    <option value="safe">Safe Mode (HTML Encoded)</option>
                    <option value="rich">Rich Text (Filtered)</option>
                    <!-- Hidden options discoverable via source inspection -->
                </select>
            </div>
            <button type="submit" name="preview" class="btn">Preview</button>
            <button type="submit" name="submit" class="btn btn-primary">Post Message</button>
        </form>

        <?php if ($preview): ?>
        <div class="preview-box">
            <h3>Preview:</h3>
            <!-- VULNERABLE: innerHTML assignment -->
            <div id="preview-content"><?php echo $preview; ?></div>
        </div>
        <?php endif; ?>

        <form method="POST" class="flag-form">
            <input type="text" name="flag" placeholder="Enter flag..." required>
            <button type="submit" class="btn btn-primary">Submit Flag</button>
        </form>

        <!-- Hidden: Check /level2.php?api=preview&data=test for CSP bypass endpoint -->
    </main>

    <script>
    // DOM-based XSS sink - hash fragment handling
    if (window.location.hash) {
        var hashData = decodeURIComponent(window.location.hash.substring(1));
        // "Safe" sanitization that's actually bypassable
        if (!hashData.includes('<script>') && !hashData.includes('javascript:')) {
            document.getElementById('preview-content').innerHTML = hashData;
        }
    }

    // Monitor for successful XSS
    window.originalAlert = window.alert;
    window.alert = function(msg) {
        if (msg === 'XSS') {
            document.body.innerHTML += '<div class="alert alert-success">XSS Successful! Flag: FLAG{x55_f1lt3r_byp455_m4st3r}</div>';
        }
        window.originalAlert(msg);
    };
    </script>
</body>
</html>
