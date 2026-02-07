<?php
/**
 * LEVEL 7: XXE OUT-OF-BAND EXFILTRATION
 * Difficulty: NIGHTMARE
 *
 * Standard XXE is blocked:
 * - DOCTYPE declarations filtered
 * - ENTITY keyword blocked
 * - SYSTEM and PUBLIC blocked
 * - External entity processing "disabled"
 *
 * Required techniques:
 * 1. Parameter entity injection (bypasses ENTITY filter)
 * 2. DTD-based XXE via external DTD file
 * 3. UTF-16 encoding to bypass text filters
 * 4. Out-of-band exfiltration (no direct output)
 * 5. Error-based XXE for data extraction
 *
 * Attack chain:
 * 1. Discover XML endpoint accepts SVG files
 * 2. SVG uses XML parser with XXE vulnerability
 * 3. Create malicious DTD on external server
 * 4. Reference DTD via parameter entity
 * 5. Exfiltrate flag via DNS or HTTP callback
 */
require_once '../config.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

$result = '';
$error = '';

// Create flag file
$flagFile = __DIR__ . '/../secret/xxe_flag.txt';
file_put_contents($flagFile, 'FLAG{xx3_0ob_exf1ltr4t10n}');

// XXE Filter - blocks obvious attacks
function xxeFilter($xml) {
    $blocked = [
        '<!DOCTYPE',
        '<!ENTITY',
        'SYSTEM',
        'PUBLIC',
        'file://',
        'php://',
        'expect://',
        'data://',
        '/etc/passwd',
        '/etc/shadow'
    ];

    $upper = strtoupper($xml);
    foreach ($blocked as $b) {
        if (strpos($upper, strtoupper($b)) !== false) {
            return false;
        }
    }

    return true;
}

// "Secure" XML parser configuration
function createSecureParser() {
    // libxml options that "should" prevent XXE
    $options = LIBXML_NONET; // Disable network access... but not really enforced!

    return $options;
}

// SVG processing endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['svg'])) {
    $file = $_FILES['svg'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $content = file_get_contents($file['tmp_name']);

        // Check file extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'svg' && $ext !== 'xml') {
            $error = "Only SVG/XML files allowed";
        } else if (!xxeFilter($content)) {
            $error = "Malicious content detected!";
            logAttack('XXE_BLOCKED', substr($content, 0, 500));
        } else {
            // VULNERABLE: The filter can be bypassed!
            // Bypass 1: UTF-16 encoding (filter only checks UTF-8)
            // Bypass 2: Parameter entities: %xxe; instead of &xxe;
            // Bypass 3: Nested entity references
            // Bypass 4: External DTD loading (not blocked by filter)

            // "Secure" parsing
            libxml_disable_entity_loader(false); // VULNERABLE: Explicitly enables entities!
            $oldValue = libxml_use_internal_errors(true);

            $doc = new DOMDocument();
            $doc->loadXML($content, LIBXML_NOENT | LIBXML_DTDLOAD); // VULNERABLE flags!

            $errors = libxml_get_errors();
            libxml_clear_errors();
            libxml_use_internal_errors($oldValue);

            if (!empty($errors)) {
                // Error-based XXE - errors might leak data!
                $errorMessages = [];
                foreach ($errors as $e) {
                    // VULNERABLE: Error messages can contain entity-expanded content!
                    $errorMessages[] = trim($e->message);
                }
                $error = "XML Parse Errors: " . implode('; ', $errorMessages);
            } else {
                // Parse successful
                $svg = $doc->documentElement;
                if ($svg && $svg->nodeName === 'svg') {
                    $result = "SVG processed successfully!";

                    // Extract dimensions (potential data exfiltration point)
                    $width = $svg->getAttribute('width');
                    $height = $svg->getAttribute('height');

                    // If dimensions contain our flag pattern, it means XXE worked!
                    if (strpos($width . $height, 'FLAG') !== false) {
                        $result .= " (Something interesting in dimensions...)";
                    }
                }
            }
        }
    }
}

// Alternative XML endpoint - even more vulnerable
if (isset($_POST['xml_data'])) {
    $xmlData = $_POST['xml_data'];

    // This endpoint uses different filtering (can you spot the difference?)
    if (preg_match('/<!ENTITY\s+\w+\s+SYSTEM/i', $xmlData)) {
        $error = "External entities blocked!";
    } else {
        // VULNERABLE: Parameter entities not blocked!
        // Payload: <!DOCTYPE foo [<!ENTITY % xxe SYSTEM "file:///secret/xxe_flag.txt">%xxe;]>

        libxml_disable_entity_loader(false);
        $doc = simplexml_load_string($xmlData, 'SimpleXMLElement', LIBXML_NOENT);

        if ($doc === false) {
            $errors = libxml_get_errors();
            $error = "Parse error: " . ($errors[0]->message ?? 'Unknown error');
        } else {
            $result = "XML processed: " . $doc->getName();
        }
    }
}

// XInclude endpoint (alternative attack vector)
if (isset($_POST['xinclude'])) {
    $xmlData = $_POST['xinclude'];

    $doc = new DOMDocument();
    $doc->loadXML($xmlData);
    $doc->xinclude(); // VULNERABLE: XInclude processing enabled!

    $result = "XInclude processed";
}

// Callback receiver for OOB exfiltration testing
if (isset($_GET['callback'])) {
    $data = $_GET['data'] ?? '';
    file_put_contents(__DIR__ . '/../logs/xxe_callbacks.log', date('Y-m-d H:i:s') . " | $data\n", FILE_APPEND);
    echo "OK";
    exit;
}

// Flag submission
if (isset($_POST['flag']) && $_POST['flag'] === $GLOBALS['FLAGS'][7]) {
    $conn = getDbConnection();
    $conn->query("INSERT INTO user_progress (user_id, level_id, flag) VALUES ({$_SESSION['user_id']}, 7, '{$_POST['flag']}')");
    header('Location: ../dashboard.php?success=7');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 7: XXE Out-of-Band</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../index.php">VulnForge Academy</a></div>
        <div class="nav-links"><a href="../dashboard.php">Dashboard</a></div>
    </nav>

    <main class="container">
        <div class="level-header">
            <h1>Level 7: XXE Out-of-Band Exfiltration</h1>
            <span class="difficulty nightmare">NIGHTMARE</span>
        </div>

        <div class="scenario-box">
            <h3>Scenario: SVG Image Processor</h3>
            <p>An image service that processes SVG files. The developers have "disabled" XXE by filtering dangerous keywords.</p>
            <p><strong>Objective:</strong> Use XXE to read the flag file. Direct output is blocked - you'll need OOB techniques.</p>
            <p><em>Flag location: /secret/xxe_flag.txt</em></p>
            <p><em>Callback endpoint: ?callback=1&data=YOUR_DATA</em></p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($result): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($result); ?></div>
        <?php endif; ?>

        <div class="upload-section">
            <h3>Upload SVG File</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="svg" accept=".svg,.xml" required>
                <button type="submit" class="btn btn-primary">Process SVG</button>
            </form>
        </div>

        <div class="xml-section">
            <h3>Direct XML Input</h3>
            <form method="POST">
                <textarea name="xml_data" rows="10" placeholder="Enter XML..."></textarea>
                <button type="submit" class="btn">Process XML</button>
            </form>
        </div>

        <div class="blocked-info">
            <h4>Blocked by Filter:</h4>
            <code>&lt;!DOCTYPE&gt;, &lt;!ENTITY&gt;, SYSTEM, PUBLIC, file://, php://</code>
            <p><em>Surely this covers all XXE attacks... right?</em></p>
        </div>

        <form method="POST" class="flag-form">
            <input type="text" name="flag" placeholder="Enter flag..." required>
            <button type="submit" class="btn btn-primary">Submit Flag</button>
        </form>
    </main>
</body>
</html>
