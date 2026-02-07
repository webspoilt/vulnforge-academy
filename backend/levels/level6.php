<?php
/**
 * LEVEL 6: BLIND COMMAND INJECTION
 * Difficulty: EXPERT
 *
 * No output from commands! Must use:
 * - Time-based detection (sleep commands)
 * - Out-of-band exfiltration (DNS, HTTP callbacks)
 * - File-based exfiltration (write to accessible location)
 *
 * Filters block:
 * - Common commands: cat, ls, whoami, id, nc, curl, wget
 * - Pipes: |
 * - Semicolons: ;
 * - Backticks: `
 * - $() syntax
 * - Spaces (must use ${IFS} or tabs)
 *
 * Bypass techniques:
 * - Use alternatives: /bin/c?t, w'h'oami, ${PATH:0:1}
 * - Hex encoding: $'\x63\x61\x74'
 * - Base64: echo YmFzaCAtYyAn... | base64 -d | sh
 * - Wildcards: /???/??t /???/p??s??
 * - Variable substitution: $@ $* expansion
 */
require_once '../config.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

$result = '';
$error = '';

// Create flag file
$flagFile = __DIR__ . '/../secret/cmd_flag.txt';
file_put_contents($flagFile, 'FLAG{bl1nd_cmd_1nj3ct10n}');

// Command filter - blocks obvious injection
function filterCommand($input) {
    // Block dangerous characters
    $blocked_chars = ['|', ';', '`', '$', '(', ')', '{', '}', '<', '>', '&', '!', "\n", "\r"];
    foreach ($blocked_chars as $char) {
        if (strpos($input, $char) !== false) {
            return false;
        }
    }

    // Block dangerous commands
    $blocked_cmds = [
        'cat', 'ls', 'dir', 'type', 'more', 'less', 'head', 'tail',
        'nc', 'netcat', 'curl', 'wget', 'fetch',
        'bash', 'sh', 'zsh', 'ksh', 'csh',
        'python', 'perl', 'ruby', 'php', 'node',
        'whoami', 'id', 'uname', 'hostname',
        'passwd', 'shadow', 'env', 'set',
        'chmod', 'chown', 'rm', 'mv', 'cp',
        'base64', 'xxd', 'od'
    ];

    $lower = strtolower($input);
    foreach ($blocked_cmds as $cmd) {
        if (preg_match("/\b$cmd\b/", $lower)) {
            return false;
        }
    }

    // Block path traversal in commands
    if (preg_match('/\.\./', $input)) {
        return false;
    }

    return true;
}

// "Secure" command execution wrapper
function secureExec($cmd) {
    // Add timeout to prevent DoS
    $fullCmd = "timeout 5 $cmd 2>&1";

    // Execute and capture (but we won't show output!)
    $output = [];
    $retval = 0;
    exec($fullCmd, $output, $retval);

    // Log for monitoring
    logAttack('COMMAND_EXEC', $cmd . ' -> ' . $retval);

    return $retval; // Only return exit code, not output!
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target = $_POST['target'] ?? '';

    if (empty($target)) {
        $error = "Please enter a target";
    } else if (!filterCommand($target)) {
        $error = "Invalid characters or commands detected!";
        sleep(2); // Slow down attackers
    } else if (!filter_var($target, FILTER_VALIDATE_IP) && !preg_match('/^[a-zA-Z0-9\.\-]+$/', $target)) {
        // Additional validation - but this can be bypassed!
        // The regex allows dots and dashes, which enables injection
        // Payload: 127.0.0.1%0asleep${IFS}5 (with URL-encoded newline)
        $error = "Invalid target format";
    } else {
        // VULNERABLE: Command injection via target
        // The filter misses:
        // - URL-encoded newlines (%0a)
        // - Encoded spaces using ${IFS}
        // - Command substitution using $'...'
        // - Wildcard expansion

        // Alternative injection point in options
        $options = $_POST['options'] ?? '';

        // "Safe" ping command construction
        // VULNERABLE: Options parameter is less filtered!
        $cmd = "ping -c 1 " . escapeshellarg($target);

        // But wait... there's a "custom options" feature!
        if (!empty($options)) {
            // This is where the real vulnerability is
            // The options are added without proper escaping in certain conditions
            if (strlen($options) < 20 && filterCommand($options)) {
                // VULNERABLE: Still injectable with short, filtered payloads
                // Payload in options: -c 1 127.0.0.1%0asleep${IFS}5
                $cmd = "ping $options $target";
            }
        }

        $exitCode = secureExec($cmd);

        // Only show success/failure, not output
        if ($exitCode === 0) {
            $result = "Host is reachable (exit code: 0)";
        } else {
            $result = "Host unreachable or command failed (exit code: $exitCode)";
        }
    }
}

// Hidden debug endpoint for testing
if (isset($_GET['test_cmd']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    // Admin-only command tester - but can you become admin?
    $testCmd = $_GET['test_cmd'];
    header('Content-Type: text/plain');
    echo "Testing: $testCmd\n";
    passthru($testCmd);
    exit;
}

// Time-based detection endpoint
if (isset($_GET['timing'])) {
    $start = microtime(true);
    // Execute whatever was timed
    $elapsed = microtime(true) - $start;
    header('Content-Type: application/json');
    echo json_encode(['elapsed_ms' => $elapsed * 1000]);
    exit;
}

// Flag submission
if (isset($_POST['flag']) && $_POST['flag'] === $GLOBALS['FLAGS'][6]) {
    $conn = getDbConnection();
    $conn->query("INSERT INTO user_progress (user_id, level_id, flag) VALUES ({$_SESSION['user_id']}, 6, '{$_POST['flag']}')");
    header('Location: ../dashboard.php?success=6');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 6: Blind Command Injection</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../index.php">VulnForge Academy</a></div>
        <div class="nav-links"><a href="../dashboard.php">Dashboard</a></div>
    </nav>

    <main class="container">
        <div class="level-header">
            <h1>Level 6: Blind Command Injection</h1>
            <span class="difficulty expert">EXPERT</span>
        </div>

        <div class="scenario-box">
            <h3>Scenario: Network Diagnostic Tool</h3>
            <p>A ping utility with "comprehensive" command injection protection. No output is displayed - only success/failure.</p>
            <p><strong>Objective:</strong> Execute commands to read the flag. You'll need out-of-band or time-based techniques.</p>
            <p><em>Flag location: /secret/cmd_flag.txt</em></p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($result): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($result); ?></div>
        <?php endif; ?>

        <form method="POST" class="ping-form">
            <div class="form-group">
                <label>Target Host/IP</label>
                <input type="text" name="target" placeholder="e.g., 127.0.0.1 or google.com" required>
            </div>

            <!-- Hidden advanced options - discoverable -->
            <div class="form-group advanced-options" style="display:none;">
                <label>Custom Options (Advanced)</label>
                <input type="text" name="options" placeholder="e.g., -c 3">
            </div>

            <button type="submit" class="btn btn-primary">Ping</button>
            <button type="button" onclick="document.querySelector('.advanced-options').style.display='block'" class="btn">Show Advanced</button>
        </form>

        <div class="help-box">
            <h4>Blocked for your protection:</h4>
            <ul>
                <li>Special characters: | ; ` $ ( ) { } &lt; &gt; &amp;</li>
                <li>Dangerous commands: cat, ls, curl, bash, etc.</li>
                <li>Path traversal: ..</li>
            </ul>
            <p><em>Our filters are impenetrable... right?</em></p>
        </div>

        <form method="POST" class="flag-form">
            <input type="text" name="flag" placeholder="Enter flag..." required>
            <button type="submit" class="btn btn-primary">Submit Flag</button>
        </form>
    </main>
</body>
</html>
