<?php
/**
 * LEVEL 9: SSRF FILTER BYPASS
 * Difficulty: NIGHTMARE
 *
 * Heavy SSRF protections:
 * - URL validation
 * - IP blacklist (127.0.0.1, 10.x, 192.168.x, etc.)
 * - Protocol whitelist (http, https only)
 * - DNS resolution check
 * - Redirect following disabled
 *
 * Bypass techniques needed:
 * 1. DNS rebinding attack
 * 2. IP address encoding (decimal, hex, octal)
 * 3. IPv6 localhost: [::1], [0:0:0:0:0:0:0:1]
 * 4. URL parsing confusion
 * 5. Redirect chain abuse
 * 6. Cloud metadata endpoints
 * 7. Protocol smuggling
 */
require_once '../config.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

$result = '';
$error = '';
$responseBody = '';

// Create internal secret endpoint
$secretFile = __DIR__ . '/../secret/internal_flag.txt';
file_put_contents($secretFile, 'FLAG{55rf_1nt3rn4l_4cc355}');

// Blacklisted IPs
$IP_BLACKLIST = [
    '127.0.0.1', 'localhost', '0.0.0.0',
    '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16',
    '169.254.0.0/16', // Link-local
    '::1', '0:0:0:0:0:0:0:1', // IPv6 localhost
];

// Check if IP is in blacklist range
function isBlacklistedIP($ip) {
    global $IP_BLACKLIST;

    // Normalize IP
    $ip = strtolower(trim($ip));

    foreach ($IP_BLACKLIST as $blocked) {
        if (strpos($blocked, '/') !== false) {
            // CIDR check
            list($subnet, $mask) = explode('/', $blocked);
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $maskLong = ~((1 << (32 - $mask)) - 1);

            if ($ipLong && ($ipLong & $maskLong) === ($subnetLong & $maskLong)) {
                return true;
            }
        } else if ($ip === $blocked) {
            return true;
        }
    }

    return false;
}

// Validate URL
function validateURL($url) {
    // Parse URL
    $parsed = parse_url($url);

    if (!$parsed || !isset($parsed['host'])) {
        return ['valid' => false, 'error' => 'Invalid URL format'];
    }

    // Protocol check
    $scheme = strtolower($parsed['scheme'] ?? '');
    if (!in_array($scheme, ['http', 'https'])) {
        return ['valid' => false, 'error' => 'Only HTTP/HTTPS allowed'];
    }

    $host = $parsed['host'];

    // Block IP addresses in URL
    if (filter_var($host, FILTER_VALIDATE_IP)) {
        if (isBlacklistedIP($host)) {
            return ['valid' => false, 'error' => 'Blacklisted IP'];
        }
    }

    // DNS resolution check
    // VULNERABLE: DNS rebinding can bypass this!
    $resolved = gethostbyname($host);
    if ($resolved === $host) {
        return ['valid' => false, 'error' => 'DNS resolution failed'];
    }

    // Check resolved IP
    // VULNERABLE: DNS could return different IP on second resolution!
    if (isBlacklistedIP($resolved)) {
        return ['valid' => false, 'error' => 'Resolved to blacklisted IP'];
    }

    // Port check
    $port = $parsed['port'] ?? ($scheme === 'https' ? 443 : 80);
    if (!in_array($port, [80, 443, 8080, 8443])) {
        return ['valid' => false, 'error' => 'Invalid port'];
    }

    return ['valid' => true, 'resolved' => $resolved, 'parsed' => $parsed];
}

// URL fetcher
function fetchURL($url) {
    $validation = validateURL($url);

    if (!$validation['valid']) {
        return ['error' => $validation['error']];
    }

    // Set up cURL with "safe" options
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false, // No redirects... or so we think
        CURLOPT_TIMEOUT => 5,
        CURLOPT_MAXREDIRS => 0,
        CURLOPT_SSL_VERIFYPEER => false, // VULNERABLE: No cert verification!
        CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
    ]);

    // VULNERABLE: DNS rebinding can occur between validation and actual request!
    // Time gap allows DNS TTL=0 rebinding

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['error' => $error];
    }

    return [
        'status' => $httpCode,
        'body' => $response,
        'final_url' => $finalURL
    ];
}

// Handle URL fetch request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = $_POST['url'];

    // Additional URL tricks check
    // VULNERABLE: Many bypasses exist!

    // Block internal references (but incomplete!)
    $blocked_patterns = [
        '/127\.0\.0\.1/',
        '/localhost/i',
        '/\[::1\]/',
        '/0\.0\.0\.0/',
        '/internal/i',
        '/metadata/i'
    ];

    $blocked = false;
    foreach ($blocked_patterns as $pattern) {
        if (preg_match($pattern, $url)) {
            $blocked = true;
            break;
        }
    }

    // BYPASS TECHNIQUES NOT BLOCKED:
    // - Decimal IP: 2130706433 = 127.0.0.1
    // - Hex IP: 0x7f000001 = 127.0.0.1
    // - Octal IP: 0177.0.0.01 = 127.0.0.1
    // - Mixed: 127.0.0.1.xip.io -> resolves to 127.0.0.1
    // - URL auth: http://user@127.0.0.1:80@external.com/
    // - URL parsing: http://external.com#@127.0.0.1
    // - IPv6 encodings: http://[0000:0000:0000:0000:0000:0000:0000:0001]/
    // - Short IPv6: http://[::ffff:127.0.0.1]/

    if ($blocked) {
        $error = "Blocked URL pattern detected";
        logAttack('SSRF_BLOCKED', $url);
    } else {
        $response = fetchURL($url);

        if (isset($response['error'])) {
            $error = "Fetch failed: " . $response['error'];
        } else {
            $result = "Status: " . $response['status'];
            $responseBody = $response['body'];

            // Check if flag was retrieved (internal endpoint hit)
            if (strpos($responseBody, 'FLAG{') !== false) {
                $result .= " - You accessed an internal resource!";
            }
        }
    }
}

// Internal endpoint (target to reach via SSRF)
if (isset($_GET['internal']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1') {
    // Only accessible from localhost!
    header('Content-Type: text/plain');
    echo file_get_contents($secretFile);
    exit;
}

// Metadata endpoint simulation (like AWS)
if (isset($_GET['metadata']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1') {
    header('Content-Type: application/json');
    echo json_encode([
        'instance-id' => 'i-1234567890abcdef0',
        'secret-flag' => $GLOBALS['FLAGS'][9],
        'credentials' => [
            'aws_access_key' => 'AKIAIOSFODNN7EXAMPLE',
            'aws_secret_key' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY'
        ]
    ]);
    exit;
}

// Flag submission
if (isset($_POST['flag']) && $_POST['flag'] === $GLOBALS['FLAGS'][9]) {
    $conn = getDbConnection();
    $conn->query("INSERT INTO user_progress (user_id, level_id, flag) VALUES ({$_SESSION['user_id']}, 9, '{$_POST['flag']}')");
    header('Location: ../dashboard.php?success=9');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 9: SSRF Filter Bypass</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../index.php">VulnForge Academy</a></div>
        <div class="nav-links"><a href="../dashboard.php">Dashboard</a></div>
    </nav>

    <main class="container">
        <div class="level-header">
            <h1>Level 9: SSRF Filter Bypass</h1>
            <span class="difficulty nightmare">NIGHTMARE</span>
        </div>

        <div class="scenario-box">
            <h3>Scenario: URL Preview Service</h3>
            <p>A URL fetcher with "enterprise-grade" SSRF protection. IP blacklists, DNS checks, protocol filters.</p>
            <p><strong>Objective:</strong> Access the internal endpoint at 127.0.0.1/level9.php?internal=1</p>
            <p><em>Hints: DNS rebinding, IP encoding tricks, or URL parsing confusion</em></p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($result): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($result); ?></div>
        <?php endif; ?>

        <form method="POST" class="url-form">
            <div class="form-group">
                <label>URL to Fetch</label>
                <input type="url" name="url" placeholder="https://example.com" required>
            </div>
            <button type="submit" class="btn btn-primary">Fetch URL</button>
        </form>

        <?php if ($responseBody): ?>
        <div class="response-box">
            <h3>Response Body:</h3>
            <pre><?php echo htmlspecialchars(substr($responseBody, 0, 5000)); ?></pre>
        </div>
        <?php endif; ?>

        <div class="protection-info">
            <h4>SSRF Protection Active:</h4>
            <ul>
                <li>IP Blacklist: 127.0.0.1, 10.x.x.x, 192.168.x.x, etc.</li>
                <li>DNS Resolution Check</li>
                <li>Protocol Whitelist: HTTP/HTTPS only</li>
                <li>Redirect Following: Disabled</li>
            </ul>
            <p><em>Surely this is impossible to bypass...</em></p>
        </div>

        <form method="POST" class="flag-form">
            <input type="text" name="flag" placeholder="Enter flag..." required>
            <button type="submit" class="btn btn-primary">Submit Flag</button>
        </form>
    </main>
</body>
</html>
