<?php
/**
 * ADMIN PANEL - THE ULTIMATE HIDDEN BOSS
 * Multiple vulnerabilities here for extra challenges:
 * - Authentication bypass via SQL injection
 * - Unrestricted file upload
 * - Command execution
 * - Information disclosure
 */
require_once '../config.php';

$error = '';
$loggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Admin login - VULNERABLE to SQL injection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Light WAF (bypassable)
    if (preg_match('/union|select|from|where/i', $username . $password)) {
        $error = "Blocked by WAF";
        sleep(3);
    } else {
        $conn = getDbConnection();
        // VULNERABLE: SQL injection with WAF bypass
        // Payload: admin'/**/OR/**/1=1-- -
        // Payload: admin' AND '1'='1
        $query = "SELECT * FROM users WHERE username = '$username' AND password = '" . hashPassword($password) . "' AND role = 'admin'";

        $result = $conn->query($query);
        if ($result && $result->num_rows > 0) {
            $_SESSION['admin_logged_in'] = true;
            $loggedIn = true;
        } else {
            $error = "Invalid credentials";
            logAttack('ADMIN_LOGIN_FAIL', "User: $username");
        }
        $conn->close();
    }
}

// Admin logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged_in']);
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - VulnForge</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../index.php">VulnForge Academy</a></div>
        <div class="nav-links">
            <?php if ($loggedIn): ?>
                <a href="?logout=1">Logout</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container">
        <?php if (!$loggedIn): ?>
            <div class="auth-card">
                <h2>Admin Login</h2>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="admin_login" value="1">
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
            </div>
        <?php else: ?>
            <h1>Admin Dashboard</h1>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">

                <!-- User Management -->
                <div class="scenario-card">
                    <h3>User Management</h3>
                    <?php
                    $conn = getDbConnection();
                    $users = $conn->query("SELECT id, username, email, role FROM users LIMIT 10");
                    ?>
                    <table class="data-table">
                        <tr><th>ID</th><th>User</th><th>Role</th></tr>
                        <?php while ($u = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><?php echo $u['role']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>

                <!-- File Upload - VULNERABLE -->
                <div class="scenario-card">
                    <h3>Upload File</h3>
                    <form method="POST" enctype="multipart/form-data" action="upload.php">
                        <input type="file" name="file" required>
                        <button type="submit" class="btn">Upload</button>
                    </form>
                    <small>Uploads go to: /uploads/admin/</small>
                </div>

                <!-- System Info -->
                <div class="scenario-card">
                    <h3>System Info</h3>
                    <pre style="font-size: 0.8rem;">
PHP: <?php echo phpversion(); ?>

Server: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>

Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?>

User: <?php echo get_current_user(); ?>
                    </pre>
                </div>

                <!-- Secret Flags -->
                <div class="scenario-card">
                    <h3>Master Flag Storage</h3>
                    <p>Congratulations on reaching the admin panel!</p>
                    <code style="display: block; padding: 15px; background: #000;">FLAG{admin_panel_accessed}</code>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
