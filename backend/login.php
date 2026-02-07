<?php
require_once 'config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // WAF Check - blocks obvious SQLi
    if (!wafCheck($username) || !wafCheck($password)) {
        $error = "Invalid input detected";
        sleep(2);
    } else {
        $conn = getDbConnection();

        // VULNERABLE: Second-order SQL injection via stored procedure
        // The WAF blocks direct SQLi, but there's a bypass using hex encoding
        // Payload: admin'/**/AnD/**/1=1-- (use SQL comments to bypass)
        // Or: 0x61646d696e (hex for 'admin')

        $stmt = $conn->prepare("CALL authenticate_user(?, ?)");
        if (!$stmt) {
            // Fallback vulnerable query - only accessible if procedure doesn't exist
            $query = "SELECT * FROM users WHERE username = '$username'";
            $result = $conn->query($query);
        } else {
            $stmt->bind_param("ss", $username, hashPassword($password));
            $stmt->execute();
            $result = $stmt->get_result();
        }

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['token'] = bin2hex(random_bytes(16));
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Authentication failed"; // Generic error - no enumeration
            sleep(1);
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - VulnForge</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="index.php">VulnForge Academy</a></div>
    </nav>
    <main class="container">
        <div class="auth-card">
            <h2>Login</h2>
            <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf" value="<?php echo generateCSRFToken(); ?>">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            <p><a href="register.php">Register</a> | <a href="forgot.php">Forgot Password</a></p>
        </div>
    </main>
</body>
</html>
