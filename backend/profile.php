<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$conn = getDbConnection();
$userId = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $userId")->fetch_assoc();
$progress = $conn->query("SELECT level_id, solved_at FROM user_progress WHERE user_id = $userId ORDER BY level_id");

$completedLevels = [];
while ($row = $progress->fetch_assoc()) {
    $completedLevels[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile - VulnForge Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="index.php">VulnForge Academy</a></div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <main class="container">
        <h1>Your Profile</h1>

        <div class="profile-card" style="background: var(--bg-card); padding: 30px; border-radius: 10px; margin: 20px 0;">
            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
            <p><strong>Joined:</strong> <?php echo $user['created_at']; ?></p>
        </div>

        <h3>Completed Levels</h3>
        <?php if (empty($completedLevels)): ?>
            <p>No levels completed yet. Get hacking!</p>
        <?php else: ?>
            <ul style="list-style: none;">
                <?php foreach ($completedLevels as $level): ?>
                    <li style="padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                        Level <?php echo $level['level_id']; ?> - Completed on <?php echo $level['solved_at']; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <p style="margin-top: 30px; color: var(--text-secondary);">
            Total Progress: <?php echo count($completedLevels); ?>/10 Levels
        </p>
    </main>
</body>
</html>
