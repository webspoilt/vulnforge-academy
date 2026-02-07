<?php
/**
 * LEVEL 3: IDOR HASH CRACKING
 * Difficulty: HARD
 *
 * Objects use UUIDs instead of sequential IDs - but they're predictable
 * Access control uses HMAC signatures - but the key is weak
 *
 * Attack chain required:
 * 1. Discover the UUID pattern (time-based UUID v1)
 * 2. Find the signature algorithm (HMAC-SHA256)
 * 3. Crack the weak signing key through timing attack or wordlist
 * 4. Forge signatures to access other users' data
 *
 * Red herrings:
 * - Random-looking tokens that are actually deterministic
 * - Fake "secure" endpoints that are honeypots
 *
 * Solution: The signing key is 'secret123' - discoverable via
 * timing attack on signature verification or by finding key in JS
 */
require_once '../config.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

$conn = getDbConnection();

// Weak signing key (discoverable)
$SIGNING_KEY = 'secret123';

// Generate "secure" resource token
function generateResourceToken($resourceId, $userId) {
    global $SIGNING_KEY;
    $data = $resourceId . ':' . $userId . ':' . floor(time() / 3600); // Hour-based expiry
    $signature = hash_hmac('sha256', $data, $SIGNING_KEY);
    return base64_encode($data) . '.' . substr($signature, 0, 16);
}

// Verify token - VULNERABLE to timing attack
function verifyResourceToken($token, $expectedUserId) {
    global $SIGNING_KEY;

    $parts = explode('.', $token);
    if (count($parts) !== 2) return false;

    $data = base64_decode($parts[0]);
    $providedSig = $parts[1];

    $dataParts = explode(':', $data);
    if (count($dataParts) !== 3) return false;

    $resourceId = $dataParts[0];
    $userId = $dataParts[1];
    $timestamp = $dataParts[2];

    // VULNERABLE: Not using constant-time comparison
    // Also vulnerable: Only checking first 16 chars of signature
    $expectedSig = substr(hash_hmac('sha256', $data, $SIGNING_KEY), 0, 16);

    // Timing attack possible here
    if ($providedSig === $expectedSig) {
        // Additional check - but bypassable by forging correct userId
        // The REAL vulnerability: we don't verify userId matches session
        return ['resourceId' => $resourceId, 'userId' => $userId, 'valid' => true];
    }

    return false;
}

// User's documents
$userId = $_SESSION['user_id'];
$documents = [];
$targetDoc = null;

// Generate user's document UUIDs (predictable pattern)
function generateDocUUID($docId, $userId, $timestamp) {
    // Predictable UUID based on sequential values
    // Pattern: first 8 chars from timestamp, next from userId, rest from docId
    $timePart = substr(md5($timestamp), 0, 8);
    $userPart = substr(md5($userId), 0, 4);
    $docPart = substr(md5($docId), 0, 4);
    return $timePart . '-' . $userPart . '-' . '4' . substr(md5($docId . $timestamp), 0, 3) . '-' . 'a' . substr(md5($userId . $docId), 0, 3) . '-' . substr(md5($docId . $userId . $timestamp), 0, 12);
}

// Create sample documents for current user
$sampleDocs = [
    ['id' => 1, 'title' => 'My Notes', 'content' => 'Regular user content', 'created' => time() - 86400],
    ['id' => 2, 'title' => 'Private Data', 'content' => 'More user content', 'created' => time() - 43200],
];

foreach ($sampleDocs as $doc) {
    $uuid = generateDocUUID($doc['id'], $userId, $doc['created']);
    $token = generateResourceToken($uuid, $userId);
    $documents[] = [
        'uuid' => $uuid,
        'title' => $doc['title'],
        'token' => $token
    ];
}

// Admin's secret document (target)
$adminDoc = [
    'id' => 999,
    'userId' => 1, // Admin
    'title' => 'SECRET: Flag Storage',
    'content' => 'FLAG{1d0r_h4sh_cr4ck3d}',
    'created' => time() - 3600
];
$adminUUID = generateDocUUID($adminDoc['id'], $adminDoc['userId'], $adminDoc['created']);

// Handle document access
if (isset($_GET['doc']) && isset($_GET['token'])) {
    $requestedDoc = $_GET['doc'];
    $requestedToken = $_GET['token'];

    $verification = verifyResourceToken($requestedToken, $userId);

    if ($verification && $verification['valid']) {
        // VULNERABLE: Uses userId from token, not session!
        if ($verification['resourceId'] === $requestedDoc) {
            if ($requestedDoc === $adminUUID) {
                $targetDoc = $adminDoc;
            } else {
                $targetDoc = ['title' => 'Document Found', 'content' => 'Access granted to: ' . $requestedDoc];
            }
        } else {
            $targetDoc = ['title' => 'Error', 'content' => 'Resource ID mismatch'];
        }
    } else {
        $targetDoc = ['title' => 'Access Denied', 'content' => 'Invalid or expired token'];
    }
}

// Signature cracking endpoint (for timing attack practice)
if (isset($_GET['verify'])) {
    $testSig = $_GET['verify'];
    $testData = base64_decode($_GET['data'] ?? '');

    $start = microtime(true);
    $expected = hash_hmac('sha256', $testData, $SIGNING_KEY);

    // Character by character comparison (vulnerable to timing)
    $match = true;
    for ($i = 0; $i < min(strlen($testSig), strlen($expected)); $i++) {
        if ($testSig[$i] !== $expected[$i]) {
            $match = false;
            break;
        }
        usleep(1000); // Amplified timing difference for training
    }

    $elapsed = (microtime(true) - $start) * 1000;

    header('Content-Type: application/json');
    echo json_encode(['match' => $match, 'time_ms' => $elapsed, 'chars_checked' => $i ?? 0]);
    exit;
}

// Flag submission
if (isset($_POST['flag']) && $_POST['flag'] === $GLOBALS['FLAGS'][3]) {
    $conn->query("INSERT INTO user_progress (user_id, level_id, flag) VALUES ({$_SESSION['user_id']}, 3, '{$_POST['flag']}')");
    header('Location: ../dashboard.php?success=3');
    exit;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 3: IDOR Hash Cracking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../index.php">VulnForge Academy</a></div>
        <div class="nav-links"><a href="../dashboard.php">Dashboard</a></div>
    </nav>

    <main class="container">
        <div class="level-header">
            <h1>Level 3: IDOR Hash Cracking</h1>
            <span class="difficulty hard">HARD</span>
        </div>

        <div class="scenario-box">
            <h3>Scenario: Document Management System</h3>
            <p>Documents are protected with UUIDs and cryptographic signatures. Surely that's secure?</p>
            <p><strong>Objective:</strong> Access the admin's secret document.</p>
        </div>

        <h3>Your Documents:</h3>
        <div class="documents-list">
            <?php foreach ($documents as $doc): ?>
            <div class="doc-card">
                <h4><?php echo htmlspecialchars($doc['title']); ?></h4>
                <p class="uuid">UUID: <?php echo $doc['uuid']; ?></p>
                <a href="?doc=<?php echo urlencode($doc['uuid']); ?>&token=<?php echo urlencode($doc['token']); ?>" class="btn">View</a>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($targetDoc): ?>
        <div class="document-view">
            <h3><?php echo htmlspecialchars($targetDoc['title']); ?></h3>
            <div class="doc-content"><?php echo htmlspecialchars($targetDoc['content']); ?></div>
        </div>
        <?php endif; ?>

        <form method="POST" class="flag-form">
            <input type="text" name="flag" placeholder="Enter flag..." required>
            <button type="submit" class="btn btn-primary">Submit Flag</button>
        </form>

        <!-- Developer notes (intentionally exposed):
             Signing key for dev: check config
             Token format: base64(resourceId:userId:hourTimestamp).signature
             Verify endpoint: ?verify=SIG&data=BASE64DATA
        -->
    </main>

    <script>
    // "Hidden" signing implementation in JS (discoverable)
    const HMAC_KEY = 'secret123'; // TODO: Remove before production!
    </script>
</body>
</html>
