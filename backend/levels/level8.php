<?php
/**
 * LEVEL 8: RACE CONDITION HELL
 * Difficulty: NIGHTMARE
 *
 * Tiny race windows (10-50ms), mutex "protection", file locks.
 * Must exploit TOCTOU (Time-of-Check to Time-of-Use) vulnerabilities.
 *
 * Scenarios:
 * 1. Coupon code race - use same code twice
 * 2. File upload race - bypass extension check via rename
 * 3. Balance transfer race - double-spend attack
 * 4. Privilege escalation race - become admin temporarily
 *
 * Techniques needed:
 * - Parallel request flooding
 * - Request timing synchronization
 * - Connection pooling abuse
 * - HTTP/2 multiplexing for synchronized delivery
 */
require_once '../config.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

$conn = getDbConnection();
$userId = $_SESSION['user_id'];
$message = '';
$error = '';

// Initialize user's race balance
$balance = $conn->query("SELECT balance FROM bank_accounts WHERE user_id = $userId")->fetch_assoc()['balance'] ?? 1000;

// SCENARIO 1: Coupon Race Condition
// Coupon can only be used once, but race condition allows multiple uses
$couponCode = 'BONUS500';
$couponValue = 500;

if (isset($_POST['use_coupon'])) {
    $code = $_POST['coupon_code'] ?? '';

    if ($code === $couponCode) {
        // Check if coupon was used (TOCTOU vulnerability here!)
        $check = $conn->query("SELECT used FROM coupons WHERE code = '$code' AND user_id = $userId");
        $couponData = $check->fetch_assoc();

        // Simulate processing delay (increases race window)
        usleep(rand(10000, 50000)); // 10-50ms delay

        if (!$couponData) {
            // First time use - insert and credit
            // VULNERABLE: Race between check and insert!
            $conn->query("INSERT INTO coupons (code, user_id, used) VALUES ('$code', $userId, 1)");
            $conn->query("UPDATE bank_accounts SET balance = balance + $couponValue WHERE user_id = $userId");
            $message = "Coupon applied! +\$$couponValue";
            $balance += $couponValue;
        } else if (!$couponData['used']) {
            // Mark as used and credit
            $conn->query("UPDATE coupons SET used = 1 WHERE code = '$code' AND user_id = $userId");
            $conn->query("UPDATE bank_accounts SET balance = balance + $couponValue WHERE user_id = $userId");
            $message = "Coupon applied! +\$$couponValue";
            $balance += $couponValue;
        } else {
            $error = "Coupon already used!";
        }
    } else {
        $error = "Invalid coupon code";
    }
}

// SCENARIO 2: Transfer Race Condition (Double Spend)
if (isset($_POST['transfer'])) {
    $amount = floatval($_POST['amount'] ?? 0);
    $toUser = intval($_POST['to_user'] ?? 0);

    if ($amount > 0 && $toUser > 0) {
        // Check balance (TOCTOU - check happens before debit)
        $currentBalance = $conn->query("SELECT balance FROM bank_accounts WHERE user_id = $userId")->fetch_assoc()['balance'];

        if ($currentBalance >= $amount) {
            // Processing delay
            usleep(rand(20000, 100000)); // 20-100ms

            // VULNERABLE: Balance checked above, but another request might have already debited!
            $conn->query("UPDATE bank_accounts SET balance = balance - $amount WHERE user_id = $userId");
            $conn->query("UPDATE bank_accounts SET balance = balance + $amount WHERE user_id = $toUser");

            $newBalance = $conn->query("SELECT balance FROM bank_accounts WHERE user_id = $userId")->fetch_assoc()['balance'];
            $balance = $newBalance;

            // Flag condition: If you manage to transfer more than your balance (negative balance)
            if ($newBalance < 0) {
                $message = "Transfer complete! Wait... your balance is negative? FLAG{r4c3_c0nd1t10n_w1nn3r}";
            } else {
                $message = "Transferred \$$amount to user $toUser";
            }
        } else {
            $error = "Insufficient balance";
        }
    }
}

// SCENARIO 3: File Upload Race Condition
$uploadDir = __DIR__ . '/../uploads/race/';
if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // "Secure" upload process
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Step 1: Move to temp location with safe extension
        $tempName = uniqid() . '.tmp';
        $tempPath = $uploadDir . $tempName;
        move_uploaded_file($file['tmp_name'], $tempPath);

        // Artificial delay (this is the race window!)
        usleep(rand(50000, 200000)); // 50-200ms

        // Step 2: Validate and rename
        // VULNERABLE: File could be accessed/modified between move and rename!
        if (in_array($ext, ['jpg', 'png', 'gif', 'txt'])) {
            $finalName = uniqid() . '.' . $ext;
            rename($tempPath, $uploadDir . $finalName);
            $message = "File uploaded: $finalName";
        } else {
            // Delete unsafe file
            usleep(10000); // Even more delay before deletion!
            unlink($tempPath);
            $error = "Invalid file type. File was deleted.";
        }

        // If attacker accessed the .tmp file before deletion and it was PHP...
        // They could execute code!
    }
}

// SCENARIO 4: Admin Privilege Race
if (isset($_POST['check_admin'])) {
    // Temporarily elevate to check something
    $originalRole = $_SESSION['role'];

    // This is a terrible pattern but exists in real code
    $_SESSION['role'] = 'admin';

    // Delay during "admin check"
    usleep(rand(100000, 500000)); // 100-500ms window!

    // VULNERABLE: If another request comes in during this window, it sees admin role!

    // Do admin check
    $isAdmin = $_SESSION['role'] === 'admin';

    // Restore role
    $_SESSION['role'] = $originalRole;

    $message = "Admin check complete";
}

// Race condition testing endpoint
if (isset($_GET['race_test'])) {
    header('Content-Type: application/json');
    $start = microtime(true);

    // Random delay to simulate race
    usleep(rand(1000, 10000));

    $elapsed = (microtime(true) - $start) * 1000;
    echo json_encode([
        'request_id' => uniqid(),
        'elapsed_ms' => $elapsed,
        'balance' => $balance,
        'timestamp' => microtime(true)
    ]);
    exit;
}

// Create coupons table if needed
$conn->query("CREATE TABLE IF NOT EXISTS coupons (id INT AUTO_INCREMENT PRIMARY KEY, code VARCHAR(50), user_id INT, used TINYINT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

// Flag submission
if (isset($_POST['flag']) && $_POST['flag'] === $GLOBALS['FLAGS'][8]) {
    $conn->query("INSERT INTO user_progress (user_id, level_id, flag) VALUES ({$_SESSION['user_id']}, 8, '{$_POST['flag']}')");
    header('Location: ../dashboard.php?success=8');
    exit;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 8: Race Condition Hell</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../index.php">VulnForge Academy</a></div>
        <div class="nav-links"><a href="../dashboard.php">Dashboard</a></div>
    </nav>

    <main class="container">
        <div class="level-header">
            <h1>Level 8: Race Condition Hell</h1>
            <span class="difficulty nightmare">NIGHTMARE</span>
        </div>

        <div class="scenario-box">
            <h3>Scenario: Banking & Coupon System</h3>
            <p>A system with multiple race condition vulnerabilities. The windows are tiny (10-100ms).</p>
            <p><strong>Objective:</strong> Exploit race conditions to get more money than you should have, or transfer more than your balance.</p>
            <p><em>Hint: You'll need to send many parallel requests. Consider using Turbo Intruder or custom scripts.</em></p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="balance-display">
            <h3>Your Balance: $<?php echo number_format($balance, 2); ?></h3>
        </div>

        <div class="race-scenarios">
            <div class="scenario-card">
                <h4>Coupon Redemption</h4>
                <p>Use coupon code to get bonus money. Each coupon can only be used once...</p>
                <form method="POST">
                    <input type="text" name="coupon_code" placeholder="Enter coupon code" value="BONUS500">
                    <button type="submit" name="use_coupon" class="btn">Apply Coupon</button>
                </form>
            </div>

            <div class="scenario-card">
                <h4>Money Transfer</h4>
                <p>Transfer money to another user. You can't transfer more than your balance... right?</p>
                <form method="POST">
                    <input type="number" name="amount" placeholder="Amount" min="1" value="1000">
                    <input type="number" name="to_user" placeholder="To User ID" value="1">
                    <button type="submit" name="transfer" class="btn">Transfer</button>
                </form>
            </div>

            <div class="scenario-card">
                <h4>File Upload</h4>
                <p>Upload files (safe extensions only). But there's a tiny window...</p>
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="file" required>
                    <button type="submit" class="btn">Upload</button>
                </form>
                <small>Uploads go to: /uploads/race/</small>
            </div>
        </div>

        <form method="POST" class="flag-form">
            <input type="text" name="flag" placeholder="Enter flag..." required>
            <button type="submit" class="btn btn-primary">Submit Flag</button>
        </form>
    </main>

    <script>
    // Helper for testing race conditions
    async function raceTest(count = 10) {
        const promises = [];
        for (let i = 0; i < count; i++) {
            promises.push(fetch('?race_test=1').then(r => r.json()));
        }
        const results = await Promise.all(promises);
        console.table(results);
    }
    console.log('Run raceTest(50) to test parallel requests');
    </script>
</body>
</html>
