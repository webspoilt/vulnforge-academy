<?php
/**
 * LEVEL 5: PATH TRAVERSAL ENCODING HELL
 * Difficulty: EXPERT
 *
 * Multiple encoding layers block traversal attempts:
 * - URL decoding twice
 * - Blocking ../ and ..\
 * - Blocking /etc/, /var/, /proc/
 * - Null byte filtering
 * - Extension whitelist
 *
 * Bypass techniques needed:
 * 1. Triple URL encoding: %252e%252e%252f
 * 2. Unicode normalization: ..%c0%af or ..%ef%bc%8f
 * 3. UTF-8 overlong encoding
 * 4. Double URL + Unicode combination
 * 5. Using ..;/ for Tomcat-style bypass (works here too!)
 *
 * The flag is in /secret/flag.txt (relative to web root)
 */
require_once '../config.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

$content = '';
$error = '';
$filename = '';

// Create the secret flag file
$secretDir = __DIR__ . '/../secret/';
if (!file_exists($secretDir)) {
    mkdir($secretDir, 0755, true);
}
file_put_contents($secretDir . 'flag.txt', 'FLAG{p4th_tr4v3rs4l_3nc0d3d}');

// Allowed directory for file viewing
$baseDir = __DIR__ . '/../uploads/';
if (!file_exists($baseDir)) {
    mkdir($baseDir, 0755, true);
    file_put_contents($baseDir . 'readme.txt', 'Welcome to the file viewer. Only files in this directory should be accessible.');
    file_put_contents($baseDir . 'sample.txt', 'This is a sample file.');
}

// Path traversal filter - multiple layers
function filterPath($path) {
    // Layer 1: Double URL decode
    $path = urldecode(urldecode($path));

    // Layer 2: Block obvious traversal
    $blocked = ['../', '..\\', '/./', '\\.\\'];
    foreach ($blocked as $b) {
        if (strpos($path, $b) !== false) {
            return false;
        }
    }

    // Layer 3: Block absolute paths
    if (preg_match('/^\/|^[a-zA-Z]:/', $path)) {
        return false;
    }

    // Layer 4: Block sensitive directories
    $sensitive = ['/etc/', '/var/', '/proc/', '/sys/', 'passwd', 'shadow', 'config.php'];
    foreach ($sensitive as $s) {
        if (stripos($path, $s) !== false) {
            return false;
        }
    }

    // Layer 5: Null byte check
    if (strpos($path, "\x00") !== false) {
        return false;
    }

    // Layer 6: Extension whitelist (but with bypass!)
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $allowed = ['txt', 'md', 'log', 'html', 'css', 'js'];
    if (!in_array($ext, $allowed)) {
        return false;
    }

    return $path;
}

// VULNERABLE: The filter misses several bypass techniques
// 1. ..;/ (semicolon bypass)
// 2. ....// (double dot removal leaves ../)
// 3. .%2e/ (partial encoding)
// 4. Unicode: %c0%ae%c0%ae/ or %e0%40%ae
// 5. Triple encoding when server does extra decode

function advancedFilterPath($path) {
    // First apply basic filter
    $filtered = filterPath($path);
    if ($filtered === false) return false;

    // Additional "protection"
    // VULNERABLE: Doesn't handle ....// -> ../
    $path = str_replace(['../', '..\\'], '', $filtered);

    // VULNERABLE: Doesn't recursively clean
    // ....// becomes ../ after one pass!

    return $path;
}

if (isset($_GET['file'])) {
    $filename = $_GET['file'];

    // Apply "secure" filtering
    $cleanPath = advancedFilterPath($filename);

    if ($cleanPath === false) {
        $error = "Blocked: Invalid path detected";
        logAttack('PATH_TRAVERSAL_BLOCKED', $filename);
    } else {
        // Additional normalization (vulnerable!)
        // This actually enables the attack by normalizing Unicode
        $cleanPath = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $cleanPath);

        $fullPath = $baseDir . $cleanPath;

        // Resolve path (vulnerable to symlinks too!)
        if (file_exists($fullPath) && is_file($fullPath)) {
            // Final check: should be under baseDir
            // VULNERABLE: realpath() can be tricked with certain encodings
            $realPath = realpath($fullPath);
            $realBase = realpath($baseDir);

            // This check seems secure but...
            // The path was already traversed before realpath!
            if (strpos($realPath, $realBase) === 0) {
                $content = file_get_contents($fullPath);
            } else {
                // Path escaped! But we still read it above...
                // Actually, this is a race condition too
                $error = "Access denied: Path outside allowed directory";
            }
        } else {
            $error = "File not found: " . htmlspecialchars($cleanPath);
        }
    }
}

// Alternative vulnerable endpoint - different bypass needed
if (isset($_GET['download'])) {
    $file = $_GET['download'];

    // "Secure" download with base64 encoding requirement
    // Attackers must base64-encode the traversal payload
    $decoded = base64_decode($file);

    if ($decoded && filterPath($decoded)) {
        $path = $baseDir . $decoded;
        if (file_exists($path)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($decoded) . '"');
            readfile($path);
            exit;
        }
    }
    $error = "Download failed";
}

// Flag submission
if (isset($_POST['flag']) && $_POST['flag'] === $GLOBALS['FLAGS'][5]) {
    $conn = getDbConnection();
    $conn->query("INSERT INTO user_progress (user_id, level_id, flag) VALUES ({$_SESSION['user_id']}, 5, '{$_POST['flag']}')");
    header('Location: ../dashboard.php?success=5');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 5: Path Traversal Encoding</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../index.php">VulnForge Academy</a></div>
        <div class="nav-links"><a href="../dashboard.php">Dashboard</a></div>
    </nav>

    <main class="container">
        <div class="level-header">
            <h1>Level 5: Path Traversal Encoding Hell</h1>
            <span class="difficulty expert">EXPERT</span>
        </div>

        <div class="scenario-box">
            <h3>Scenario: Secure File Viewer</h3>
            <p>A file viewer with "enterprise-grade" path traversal protection. Multiple encoding filters, extension whitelisting, and path normalization.</p>
            <p><strong>Objective:</strong> Read the flag file at /secret/flag.txt</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="file-browser">
            <h3>Available Files:</h3>
            <ul>
                <?php
                $files = scandir($baseDir);
                foreach ($files as $f) {
                    if ($f !== '.' && $f !== '..') {
                        echo '<li><a href="?file=' . urlencode($f) . '">' . htmlspecialchars($f) . '</a></li>';
                    }
                }
                ?>
            </ul>
        </div>

        <form method="GET" class="file-form">
            <input type="text" name="file" placeholder="Enter filename..." value="<?php echo htmlspecialchars($filename); ?>">
            <button type="submit" class="btn">View File</button>
        </form>

        <?php if ($content): ?>
        <div class="file-content">
            <h3>File Content:</h3>
            <pre><?php echo htmlspecialchars($content); ?></pre>
        </div>
        <?php endif; ?>

        <form method="POST" class="flag-form">
            <input type="text" name="flag" placeholder="Enter flag..." required>
            <button type="submit" class="btn btn-primary">Submit Flag</button>
        </form>
    </main>
</body>
</html>
