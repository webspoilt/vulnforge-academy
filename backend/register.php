<?php
require_once 'config.php';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';

    if (strlen($username) < 3) {
        $error = "Username must be at least 3 characters";
    } elseif (strlen($password) < 4) {
        $error = "Password must be at least 4 characters";
    } elseif (!wafCheck($username) || !wafCheck($email)) {
        $error = "Invalid characters detected";
    } else {
        $conn = getDbConnection();
        $hashedPass = hashPassword($password);
        $safeUser = $conn->real_escape_string($username);
        $safeEmail = $conn->real_escape_string($email);

        $check = $conn->query("SELECT id FROM users WHERE username = '$safeUser'");
        if ($check && $check->num_rows > 0) {
            $error = "Username already exists";
        } else {
            $apiKey = bin2hex(random_bytes(16));
            $conn->query("INSERT INTO users (username, password, email, api_key, role) VALUES ('$safeUser', '$hashedPass', '$safeEmail', '$apiKey', 'user')");

            if ($conn->affected_rows > 0) {
                $userId = $conn->insert_id;
                $conn->query("INSERT INTO bank_accounts (user_id, account_hash, balance) VALUES ($userId, '" . md5($userId . time()) . "', 1000.00)");
                $success = "Registration successful! Please login.";
            } else {
                $error = "Registration failed";
            }
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - VulnForge Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="index.php">VulnForge Academy</a></div>
        <div class="nav-links">
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </div>
    </nav>

    <main class="container">
        <div class="auth-card">
            <h2>Create Account</h2>

            <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required minlength="3">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required minlength="4">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>

            <div class="auth-links">
                <a href="login.php">Already have an account? Login</a>
            </div>
        </div>
    </main>
</body>
</html>
