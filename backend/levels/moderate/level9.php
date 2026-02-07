<?php
/**
 * LEVEL 9: FILE UPLOAD BYPASS
 * Difficulty: MODERATE
 *
 * Extension blacklist can be bypassed
 * Solution: Upload .php5, .phtml, or use double extension .php.jpg
 */
require_once '../../config.php';

$error = '';
$success = '';
$uploadedFile = '';

$uploadDir = __DIR__ . '/../../uploads/';
if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $filename = $file['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // VULNERABLE: Blacklist approach - can be bypassed!
    $blacklist = ['php', 'exe', 'sh', 'bat'];

    if (in_array($ext, $blacklist)) {
        $error = "Blocked: .$ext files not allowed!";
    } else {
        $newName = uniqid() . '_' . basename($filename);
        $targetPath = $uploadDir . $newName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $uploadedFile = '/uploads/' . $newName;
            $success = "File uploaded: <a href='$uploadedFile'>$newName</a>";

            // Check if they uploaded executable PHP
            $content = file_get_contents($targetPath);
            if (strpos($content, '<?php') !== false || strpos($content, '<?=') !== false) {
                $success .= "<br><br>PHP code detected! FLAG{upload_shell}";
            }
        } else {
            $error = "Upload failed";
        }
    }
}

// Flag submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flag'])) {
    if (checkFlag(9, $_POST['flag'])) {
        if (isset($_SESSION['user_id'])) {
            saveProgress($_SESSION['user_id'], 9, $_POST['flag']);
        }
        $success = "Correct! Level 9 completed!";
    } else {
        $error = "Incorrect flag.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 9: File Upload - VulnForge</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../../index.php">VulnForge Academy</a></div>
        <div class="nav-links"><a href="../../dashboard.php">Dashboard</a></div>
    </nav>

    <main class="container">
        <div class="level-header">
            <h1>Level 9: File Upload Bypass</h1>
            <span class="difficulty moderate">MODERATE - 25 Points</span>
        </div>

        <div class="scenario-box">
            <h3>Scenario: Image Uploader</h3>
            <p>An image uploader that blocks .php files. Find a way to upload PHP code anyway.</p>
            <p><strong>Objective:</strong> Upload a file containing PHP code.</p>

            <div class="hints-box">
                <h4>Hints:</h4>
                <ul>
                    <li>The filter uses a blacklist (blocks .php, .exe, etc.)</li>
                    <li>Alternative PHP extensions: .php5, .phtml, .php7</li>
                    <li>Double extensions: shell.php.jpg</li>
                    <li>Null byte: shell.php%00.jpg (older servers)</li>
                </ul>
            </div>
        </div>

        <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

        <div class="upload-form" style="background: var(--bg-card); padding: 30px; border-radius: 10px;">
            <h2>Upload Image</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="file" name="file" required>
                </div>
                <button type="submit" class="btn btn-primary">Upload</button>
            </form>

            <p style="margin-top: 15px; color: var(--text-secondary);">
                Blocked extensions: .php, .exe, .sh, .bat
            </p>
        </div>

        <div style="margin-top: 20px; padding: 15px; background: #1a1a25; border-radius: 5px;">
            <strong>Sample PHP shell to upload:</strong>
            <pre>&lt;?php echo "Shell uploaded!"; ?&gt;</pre>
            <small>Save this as shell.phtml or shell.php5 and upload</small>
        </div>

        <form method="POST" class="flag-form">
            <input type="text" name="flag" placeholder="Enter flag..." required>
            <button type="submit" class="btn btn-primary">Submit Flag</button>
        </form>
    </main>
</body>
</html>
