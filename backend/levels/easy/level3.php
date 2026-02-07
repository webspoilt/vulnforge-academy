<?php
/**
 * LEVEL 3: SIMPLE IDOR
 * Difficulty: EASY
 *
 * Insecure Direct Object Reference - just change the ID in URL
 * Solution: Change ?id=2 to ?id=1 to view admin profile
 */
require_once '../../config.php';

$profileId = $_GET['id'] ?? 2; // Default to user 2
$error = '';
$success = '';
$profile = null;

$conn = getDbConnection();
if ($conn) {
    // VULNERABLE: No authorization check - any user can view any profile!
    $result = $conn->query("SELECT * FROM profiles WHERE user_id = $profileId");
    $profile = $result->fetch_assoc();
}

// Flag submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flag'])) {
    if (checkFlag(3, $_POST['flag'])) {
        if (isset($_SESSION['user_id'])) {
            saveProgress($_SESSION['user_id'], 3, $_POST['flag']);
        }
        $success = "Correct! Level 3 completed!";
    } else {
        $error = "Incorrect flag.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 3: Simple IDOR - VulnForge</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../../index.php">VulnForge Academy</a></div>
        <div class="nav-links"><a href="../../dashboard.php">Dashboard</a></div>
    </nav>

    <main class="container">
        <div class="level-header">
            <h1>Level 3: Insecure Direct Object Reference</h1>
            <span class="difficulty easy">EASY - 10 Points</span>
        </div>

        <div class="scenario-box">
            <h3>Scenario: User Profile Viewer</h3>
            <p>View user profiles by ID. Your profile is ID 2.</p>
            <p><strong>Objective:</strong> Access the admin's private profile (ID 1) to find the flag.</p>

            <div class="hints-box">
                <h4>Hints:</h4>
                <ul>
                    <li>Look at the URL - notice the <code>?id=2</code> parameter</li>
                    <li>What happens if you change it to a different number?</li>
                    <li>IDOR = accessing other users' data by changing IDs</li>
                </ul>
            </div>
        </div>

        <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

        <div class="profile-links" style="margin: 20px 0;">
            <a href="?id=2" class="btn <?php echo $profileId == 2 ? 'btn-primary' : ''; ?>">Your Profile (ID: 2)</a>
            <a href="?id=3" class="btn <?php echo $profileId == 3 ? 'btn-primary' : ''; ?>">Jane's Profile (ID: 3)</a>
            <!-- Admin profile not linked... but does it exist? -->
        </div>

        <?php if ($profile): ?>
        <div class="profile-card" style="background: var(--bg-card); padding: 30px; border-radius: 10px;">
            <h2><?php echo htmlspecialchars($profile['full_name']); ?></h2>
            <p><strong>Bio:</strong> <?php echo htmlspecialchars($profile['bio']); ?></p>
            <?php if ($profile['is_admin']): ?>
                <p><strong>Role:</strong> <span style="color: #ff3333;">Administrator</span></p>
            <?php endif; ?>
            <p><strong>Private Data:</strong> <?php echo htmlspecialchars($profile['private_data']); ?></p>
        </div>
        <?php else: ?>
            <p>Profile not found.</p>
        <?php endif; ?>

        <p style="margin-top: 20px; color: var(--text-secondary);">
            Current URL: <code>level3.php?id=<?php echo htmlspecialchars($profileId); ?></code>
        </p>

        <form method="POST" class="flag-form">
            <input type="text" name="flag" placeholder="Enter flag..." required>
            <button type="submit" class="btn btn-primary">Submit Flag</button>
        </form>
    </main>
</body>
</html>
