<?php
require_once 'config.php';

$conn = getDbConnection();
$leaders = [];

if ($conn) {
    $result = $conn->query("
        SELECT u.username, u.points,
               (SELECT COUNT(*) FROM user_progress WHERE user_id = u.id) as levels_done
        FROM users u
        WHERE u.points > 0
        ORDER BY u.points DESC, levels_done DESC
        LIMIT 50
    ");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $leaders[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leaderboard - VulnForge Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="index.php">VulnForge Academy</a></div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="leaderboard.php">Leaderboard</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container">
        <h1>Global Leaderboard</h1>
        <p>Top hackers ranked by points</p>

        <table class="data-table" style="margin-top: 30px;">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Username</th>
                    <th>Points</th>
                    <th>Levels</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leaders)): ?>
                    <tr><td colspan="4" style="text-align: center;">No hackers on the leaderboard yet. Be the first!</td></tr>
                <?php else: ?>
                    <?php foreach ($leaders as $i => $leader): ?>
                    <tr>
                        <td>
                            <?php if ($i === 0): ?>
                                <span style="color: gold;">🥇</span>
                            <?php elseif ($i === 1): ?>
                                <span style="color: silver;">🥈</span>
                            <?php elseif ($i === 2): ?>
                                <span style="color: #cd7f32;">🥉</span>
                            <?php else: ?>
                                #<?php echo $i + 1; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($leader['username']); ?></td>
                        <td style="color: var(--accent-green);"><?php echo $leader['points']; ?></td>
                        <td><?php echo $leader['levels_done']; ?>/20</td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
