<?php
/**
 * LEVEL 10: INSECURE DESERIALIZATION RCE - THE FINAL BOSS
 * Difficulty: IMPOSSIBLE
 *
 * This level requires:
 * 1. Understanding PHP object serialization
 * 2. Finding gadget chains in the codebase
 * 3. Bypassing signature verification
 * 4. Escaping a restricted execution environment
 * 5. Chaining multiple vulnerabilities
 *
 * Protections:
 * - Serialized data is signed with HMAC
 * - Allowed classes whitelist
 * - disable_functions blocks dangerous functions
 * - open_basedir restriction
 *
 * Attack chain:
 * 1. Find the signing key (weak entropy or leaked)
 * 2. Identify usable gadget classes
 * 3. Craft malicious serialized payload
 * 4. Sign the payload
 * 5. Trigger deserialization
 * 6. Achieve RCE through gadget chain
 */
require_once '../config.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

// Signing key for serialized data (weak!)
$SERIALIZE_KEY = 'VulnForge_Serialize_2024';  // Discoverable via timing attack

// Allowed classes for unserialization
$ALLOWED_CLASSES = ['UserPrefs', 'CacheEntry', 'SessionData'];

// Gadget class #1 - Has __destruct that writes files
class FileLogger {
    public $logFile = '/tmp/app.log';
    public $logData = '';

    public function __destruct() {
        if ($this->logFile && $this->logData) {
            file_put_contents($this->logFile, $this->logData, FILE_APPEND);
        }
    }
}

// Gadget class #2 - Has __toString that executes commands
class CommandResult {
    public $command = 'echo OK';
    public $output = '';

    public function __toString() {
        // "Safe" command execution for showing results
        $this->output = shell_exec($this->command);
        return $this->output ?? '';
    }
}

// Gadget class #3 - Has __wakeup that includes files
class TemplateLoader {
    public $templatePath = 'templates/default.php';

    public function __wakeup() {
        if (file_exists($this->templatePath)) {
            include($this->templatePath);
        }
    }
}

// Gadget class #4 - Chain trigger via __call
class ServiceProxy {
    public $service;
    public $method;
    public $args = [];

    public function __call($name, $arguments) {
        if ($this->service && method_exists($this->service, $this->method)) {
            return call_user_func_array([$this->service, $this->method], $this->args);
        }
    }
}

// "Safe" user preferences class
class UserPrefs {
    public $theme = 'dark';
    public $language = 'en';
    public $notifications = true;
}

// Cache entry class
class CacheEntry {
    public $key;
    public $value;
    public $expires;
}

// Session data class (the entry point!)
class SessionData {
    public $userId;
    public $data;
    public $metadata;

    public function getMetadata() {
        // VULNERABLE: metadata could be a gadget object with __toString
        return "Metadata: " . $this->metadata;
    }
}

// Sign serialized data
function signData($data) {
    global $SERIALIZE_KEY;
    return hash_hmac('sha256', $data, $SERIALIZE_KEY);
}

// Verify signature
function verifySignature($data, $signature) {
    global $SERIALIZE_KEY;
    $expected = hash_hmac('sha256', $data, $SERIALIZE_KEY);

    // VULNERABLE: Not using hash_equals (timing attack possible!)
    // Also vulnerable: Signature only checks first 32 chars
    return substr($expected, 0, 32) === substr($signature, 0, 32);
}

// "Safe" unserialization
function safeUnserialize($data, $signature) {
    global $ALLOWED_CLASSES;

    // Verify signature first
    if (!verifySignature($data, $signature)) {
        return null;
    }

    // VULNERABLE: The class whitelist is checked AFTER unserialization starts
    // PHP 7+ allows allowed_classes in options, but we're not using it properly!

    $decoded = base64_decode($data);

    // Check for obviously malicious content
    $blocked = ['system', 'exec', 'passthru', 'shell_exec', 'eval', 'assert'];
    foreach ($blocked as $func) {
        if (stripos($decoded, $func) !== false) {
            logAttack('DESERIALIZE_BLOCKED', $func);
            return null;
        }
    }

    // VULNERABLE: We should use allowed_classes option!
    // Instead, we unserialize first, then check
    $obj = @unserialize($decoded);

    if ($obj && !in_array(get_class($obj), $ALLOWED_CLASSES)) {
        // Too late! __wakeup already executed!
        return null;
    }

    return $obj;
}

$result = '';
$error = '';
$prefs = null;

// Handle preference loading
if (isset($_COOKIE['user_prefs'])) {
    $parts = explode('.', $_COOKIE['user_prefs']);
    if (count($parts) === 2) {
        $prefs = safeUnserialize($parts[0], $parts[1]);
    }
}

// Handle preference saving
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_prefs'])) {
    $newPrefs = new UserPrefs();
    $newPrefs->theme = $_POST['theme'] ?? 'dark';
    $newPrefs->language = $_POST['language'] ?? 'en';

    $serialized = base64_encode(serialize($newPrefs));
    $signature = signData($serialized);

    setcookie('user_prefs', $serialized . '.' . $signature, time() + 86400, '/');
    $result = "Preferences saved!";
    $prefs = $newPrefs;
}

// Hidden admin endpoint for testing serialization
if (isset($_POST['test_deserialize']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $data = $_POST['test_data'];
    $sig = $_POST['test_sig'];
    $obj = safeUnserialize($data, $sig);
    $result = "Deserialization test: " . (is_object($obj) ? get_class($obj) : 'failed');
}

// Signature verification timing endpoint (for attack)
if (isset($_GET['verify_timing'])) {
    $data = $_GET['data'] ?? '';
    $sig = $_GET['sig'] ?? '';

    $start = microtime(true);
    $valid = verifySignature($data, $sig);
    $elapsed = (microtime(true) - $start) * 1000;

    header('Content-Type: application/json');
    echo json_encode(['valid' => $valid, 'time_ms' => $elapsed]);
    exit;
}

// Key derivation hint endpoint
if (isset($_GET['key_hint'])) {
    // Accidentally exposed debug info
    header('Content-Type: application/json');
    echo json_encode([
        'algorithm' => 'HMAC-SHA256',
        'key_format' => 'VulnForge_Serialize_YYYY',
        'key_length' => strlen($SERIALIZE_KEY),
        'year_hint' => '20XX'
    ]);
    exit;
}

// Flag location
$flagFile = __DIR__ . '/../secret/final_flag.txt';
file_put_contents($flagFile, 'FLAG{d353r14l1z4t10n_rc3_g0d}');

// Alternative RCE detection
if (isset($_GET['rce_check'])) {
    // Check if any files were created by RCE
    $uploads = glob(__DIR__ . '/../uploads/*.php');
    $temp = glob('/tmp/vulnforge_*');

    if (!empty($uploads) || !empty($temp)) {
        $result = "RCE detected! Flag: " . $GLOBALS['FLAGS'][10];
    }
}

// Flag submission
if (isset($_POST['flag']) && $_POST['flag'] === $GLOBALS['FLAGS'][10]) {
    $conn = getDbConnection();
    $conn->query("INSERT INTO user_progress (user_id, level_id, flag) VALUES ({$_SESSION['user_id']}, 10, '{$_POST['flag']}')");
    header('Location: ../dashboard.php?success=10&final=true');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 10: Deserialization RCE - THE FINAL BOSS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../index.php">VulnForge Academy</a></div>
        <div class="nav-links"><a href="../dashboard.php">Dashboard</a></div>
    </nav>

    <main class="container">
        <div class="level-header">
            <h1>Level 10: Insecure Deserialization RCE</h1>
            <span class="difficulty impossible">IMPOSSIBLE</span>
        </div>

        <div class="scenario-box final-boss">
            <h3>THE FINAL BOSS</h3>
            <p>A "secure" serialization system with signature verification and class whitelisting.</p>
            <p><strong>Objective:</strong> Achieve Remote Code Execution through deserialization.</p>

            <div class="attack-requirements">
                <h4>You'll Need:</h4>
                <ul>
                    <li>Crack or leak the HMAC signing key</li>
                    <li>Find gadget chains in the codebase</li>
                    <li>Bypass the class whitelist</li>
                    <li>Craft a working exploit payload</li>
                    <li>Sign it correctly</li>
                    <li>Trigger execution</li>
                </ul>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($result): ?>
            <div class="alert alert-success"><?php echo $result; ?></div>
        <?php endif; ?>

        <div class="prefs-section">
            <h3>User Preferences (Serialized)</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Theme</label>
                    <select name="theme">
                        <option value="dark" <?php echo ($prefs && $prefs->theme === 'dark') ? 'selected' : ''; ?>>Dark</option>
                        <option value="light" <?php echo ($prefs && $prefs->theme === 'light') ? 'selected' : ''; ?>>Light</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Language</label>
                    <select name="language">
                        <option value="en">English</option>
                        <option value="es">Spanish</option>
                        <option value="fr">French</option>
                    </select>
                </div>
                <button type="submit" name="save_prefs" class="btn btn-primary">Save Preferences</button>
            </form>

            <?php if (isset($_COOKIE['user_prefs'])): ?>
            <div class="cookie-display">
                <h4>Current Cookie:</h4>
                <code><?php echo htmlspecialchars($_COOKIE['user_prefs']); ?></code>
            </div>
            <?php endif; ?>
        </div>

        <div class="info-box">
            <h4>Security Measures:</h4>
            <ul>
                <li>HMAC-SHA256 signature verification</li>
                <li>Class whitelist: UserPrefs, CacheEntry, SessionData</li>
                <li>Blocked functions in serialized data</li>
            </ul>
            <p><em>This should be completely secure... shouldn't it?</em></p>
        </div>

        <form method="POST" class="flag-form">
            <input type="text" name="flag" placeholder="Enter final flag..." required>
            <button type="submit" class="btn btn-primary">Submit Final Flag</button>
        </form>
    </main>

    <!-- DEBUG: Available gadget classes for "testing"
         FileLogger - writes to files via __destruct
         CommandResult - executes via __toString
         TemplateLoader - includes files via __wakeup
         ServiceProxy - chains via __call
    -->
</body>
</html>
