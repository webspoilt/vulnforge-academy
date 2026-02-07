<?php
/**
 * LEVEL 4: CSRF TOKEN PREDICTION
 * Difficulty: EXPERT
 *
 * CSRF tokens are used but they're predictable!
 * Token generation: HMAC(session_id + floor(time/300), secret)
 *
 * Attack chain:
 * 1. Discover token generation algorithm via source analysis
 * 2. Realize tokens are time-based (5-minute windows)
 * 3. Obtain victim's session ID via separate XSS or session fixation
 * 4. Predict future token using known algorithm
 * 5. Craft CSRF payload to transfer money
 *
 * Additional obstacles:
 * - Referer header checking (bypassable)
 * - SameSite cookies (but set to Lax, not Strict)
 * - Double-submit cookie (but both values from same source)
 */
require_once '../config.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

$conn = getDbConnection();
$userId = $_SESSION['user_id'];
$message = '';
$error = '';

// Get user's bank account
$result = $conn->query("SELECT * FROM bank_accounts WHERE user_id = $userId");
$account = $result->fetch_assoc();
if (!$account) {
    $conn->query("INSERT INTO bank_accounts (user_id, account_hash, balance) VALUES ($userId, '" . md5($userId . time()) . "', 10000.00)");
    $account = ['balance' => 10000.00, 'account_hash' => md5($userId . time())];
}

// Victim account (admin's account that you need to drain)
$adminAccount = $conn->query("SELECT * FROM bank_accounts WHERE user_id = 1")->fetch_assoc();

// CSRF Token - looks secure but predictable
$csrfToken = generateCSRFToken(); // Uses time-based generation from config.php

// Double-submit cookie pattern (also vulnerable)
setcookie('csrf_cookie', $csrfToken, 0, '/', '', false, false); // No HttpOnly, no Secure!

// Transfer money endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transfer'])) {
    $toAccount = $_POST['to_account'] ?? '';
    $amount = floatval($_POST['amount'] ?? 0);
    $submittedToken = $_POST['csrf_token'] ?? '';
    $cookieToken = $_COOKIE['csrf_cookie'] ?? '';

    // "Security" checks
    $checks = [];

    // Check 1: CSRF Token validation (bypassable - tokens are predictable)
    if (!verifyCSRFToken($submittedToken)) {
        $checks[] = 'Invalid CSRF token';
    }

    // Check 2: Double-submit cookie (bypassable - same predictable value)
    if ($submittedToken !== $cookieToken) {
        // But wait - if cookie is not set, this check is skipped!
        if ($cookieToken !== '') {
            $checks[] = 'Cookie mismatch';
        }
    }

    // Check 3: Referer header (easily bypassable)
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if ($referer && strpos($referer, $_SERVER['HTTP_HOST']) === false) {
        // But null referer is allowed! (for privacy proxies)
        if ($referer !== '') {
            $checks[] = 'Invalid referer';
        }
    }

    // Check 4: Origin header (optional, often missing)
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    // This check is never actually enforced - bug!

    if (empty($checks) && $amount > 0 && $amount <= $account['balance']) {
        // Process transfer
        $newBalance = $account['balance'] - $amount;

        $conn->query("UPDATE bank_accounts SET balance = balance - $amount WHERE user_id = $userId");

        // If transferring to admin account, check for flag condition
        if ($toAccount === $adminAccount['account_hash'] && $amount >= 5000) {
            $message = "Transfer successful! Wait... you just gave money TO the admin? FLAG{csrf_t0k3n_pr3d1ct10n}";
        } else {
            $conn->query("UPDATE bank_accounts SET balance = balance + $amount WHERE account_hash = '$toAccount'");
            $message = "Transferred $$amount successfully";
        }

        $account['balance'] = $newBalance;
    } else if (!empty($checks)) {
        $error = "Security check failed: " . implode(', ', $checks);
    } else {
        $error = "Invalid amount";
    }
}

// API endpoint to get current token (for "legitimate" AJAX calls - but exposes algorithm)
if (isset($_GET['api']) && $_GET['api'] === 'token') {
    header('Content-Type: application/json');
    // VULNERABLE: Exposes token generation timing
    echo json_encode([
        'token' => $csrfToken,
        'expires_in' => 300 - (time() % 300), // Reveals time window!
        'generated_at' => floor(time() / 300) * 300 // Reveals exact generation time!
    ]);
    exit;
}

// Debug endpoint
if (isset($_GET['debug']) && $_GET['debug'] === 'token_info') {
    header('Content-Type: application/json');
    echo json_encode([
        'algorithm' => 'HMAC-SHA256',
        'inputs' => 'session_id + time_window',
        'time_window' => '300 seconds',
        'current_window' => floor(time() / 300)
        // Key is still hidden... or is it?
    ]);
    exit;
}

// Flag submission
if (isset($_POST['flag']) && $_POST['flag'] === $GLOBALS['FLAGS'][4]) {
    $conn->query("INSERT INTO user_progress (user_id, level_id, flag) VALUES ({$_SESSION['user_id']}, 4, '{$_POST['flag']}')");
    header('Location: ../dashboard.php?success=4');
    exit;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 4: CSRF Token Prediction</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../index.php">VulnForge Academy</a></div>
        <div class="nav-links"><a href="../dashboard.php">Dashboard</a></div>
    </nav>

    <main class="container">
        <div class="level-header">
            <h1>Level 4: CSRF Token Prediction</h1>
            <span class="difficulty expert">EXPERT</span>
        </div>

        <div class="scenario-box">
            <h3>Scenario: Secure Banking Portal</h3>
            <p>This bank uses industry-standard CSRF protection: tokens, double-submit cookies, and referer checking. Try to perform an unauthorized transfer.</p>
            <p><strong>Objective:</strong> Exploit the CSRF protection to make a transfer.</p>
            <p><em>Hint: Transfer $5000+ to the admin's account to prove the exploit works.</em></p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="bank-dashboard">
            <div class="balance-card">
                <h3>Your Balance</h3>
                <p class="balance">$<?php echo number_format($account['balance'], 2); ?></p>
                <small>Account: <?php echo substr($account['account_hash'], 0, 8); ?>...</small>
            </div>

            <form method="POST" class="transfer-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="transfer" value="1">

                <div class="form-group">
                    <label>Recipient Account Hash</label>
                    <input type="text" name="to_account" required placeholder="Enter account hash">
                </div>
                <div class="form-group">
                    <label>Amount ($)</label>
                    <input type="number" name="amount" required min="1" max="<?php echo $account['balance']; ?>" step="0.01">
                </div>
                <button type="submit" class="btn btn-primary">Transfer</button>
            </form>

            <div class="target-info">
                <h4>Admin Account (for testing):</h4>
                <code><?php echo $adminAccount['account_hash']; ?></code>
            </div>
        </div>

        <form method="POST" class="flag-form">
            <input type="text" name="flag" placeholder="Enter flag..." required>
            <button type="submit" class="btn btn-primary">Submit Flag</button>
        </form>
    </main>

    <script>
    // Token refresh logic (exposes timing)
    async function refreshToken() {
        const resp = await fetch('?api=token');
        const data = await resp.json();
        document.querySelector('[name=csrf_token]').value = data.token;
        console.log('Token refreshes in:', data.expires_in, 'seconds');
        console.log('Current time window:', data.generated_at);
    }
    setInterval(refreshToken, 60000);
    </script>
</body>
</html>
