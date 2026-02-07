<?php
/**
 * VulnForge Academy Enhanced - All Skill Levels
 * Beginner -> Intermediate -> Advanced -> Expert -> Nightmare
 * Enhanced with monetization features and improved security
 */
session_start();

// Environment-based config (for free hosting platforms)
define('DB_HOST', getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: getenv('MYSQL_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: getenv('MYSQL_PASSWORD') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: 'vulnforge');
define('SECRET_KEY', getenv('SECRET_KEY') ?: 'vulnforge_secret_2024_enhanced');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:8000');

// Monetization Configuration
define('GITHUB_SPONSORS_URL', 'https://github.com/sponsors/vulnforge-academy');
define('BUY_ME_COFFEE_URL', 'https://www.buymeacoffee.com/vulnforgeacademy');
define('AFFILIATE_BASE_URL', 'https://affiliate-link.example.com');

// Subscription tiers
define('SUBSCRIPTION_TIERS', [
    'free' => [
        'name' => 'Free',
        'price' => 0,
        'features' => ['basic_levels', 'community_support'],
        'max_levels' => 10
    ],
    'pro' => [
        'name' => 'Pro',
        'price' => 19.99,
        'features' => ['all_levels', 'premium_content', 'email_support', 'progress_tracking'],
        'max_levels' => 20
    ],
    'enterprise' => [
        'name' => 'Enterprise',
        'price' => 99.99,
        'features' => ['everything', 'live_workshops', 'corporate_support', 'custom_challenges'],
        'max_levels' => 20
    ]
]);

// Point values per difficulty (enhanced)
$POINTS = ['easy' => 10, 'moderate' => 25, 'hard' => 50, 'expert' => 100, 'nightmare' => 200];

// Enhanced flag system
$FLAGS = [
    // EASY (1-5) - Beginner friendly, obvious vulnerabilities
    1 => 'FLAG{first_sqli_success}',
    2 => 'FLAG{xss_alert_box}',
    3 => 'FLAG{idor_user_data}',
    4 => 'FLAG{robots_secrets}',
    5 => 'FLAG{source_code_leak}',

    // MODERATE (6-10) - Requires some thinking
    6 => 'FLAG{auth_bypass_win}',
    7 => 'FLAG{cookie_monster}',
    8 => 'FLAG{hidden_params}',
    9 => 'FLAG{upload_shell}',
    10 => 'FLAG{lfi_include}',

    // HARD (11-15) - WAF bypass, encoding tricks
    11 => 'FLAG{blind_sqli_pro}',
    12 => 'FLAG{xss_filter_bypass}',
    13 => 'FLAG{idor_hash_crack}',
    14 => 'FLAG{csrf_predicted}',
    15 => 'FLAG{path_encoded}',

    // EXPERT (16-18) - Multi-step, OOB techniques
    16 => 'FLAG{cmd_injection}',
    17 => 'FLAG{xxe_exfil}',
    18 => 'FLAG{race_winner}',

    // NIGHTMARE (19-20) - Chain exploits
    19 => 'FLAG{ssrf_bypass}',
    20 => 'FLAG{deser_rce_god}'
];

$LEVELS = [
    1 => ['name' => 'Basic SQL Injection', 'diff' => 'easy', 'points' => 10, 'category' => 'web'],
    2 => ['name' => 'Reflected XSS', 'diff' => 'easy', 'points' => 10, 'category' => 'web'],
    3 => ['name' => 'Simple IDOR', 'diff' => 'easy', 'points' => 10, 'category' => 'web'],
    4 => ['name' => 'Robots.txt Secrets', 'diff' => 'easy', 'points' => 10, 'category' => 'recon'],
    5 => ['name' => 'HTML Source Leak', 'diff' => 'easy', 'points' => 10, 'category' => 'web'],
    6 => ['name' => 'Authentication Bypass', 'diff' => 'moderate', 'points' => 25, 'category' => 'auth'],
    7 => ['name' => 'Cookie Manipulation', 'diff' => 'moderate', 'points' => 25, 'category' => 'web'],
    8 => ['name' => 'Hidden Parameters', 'diff' => 'moderate', 'points' => 25, 'category' => 'web'],
    9 => ['name' => 'File Upload Bypass', 'diff' => 'moderate', 'points' => 25, 'category' => 'web'],
    10 => ['name' => 'Local File Inclusion', 'diff' => 'moderate', 'points' => 25, 'category' => 'web'],
    11 => ['name' => 'Blind SQLi + WAF', 'diff' => 'hard', 'points' => 50, 'category' => 'web'],
    12 => ['name' => 'XSS Filter Evasion', 'diff' => 'hard', 'points' => 50, 'category' => 'web'],
    13 => ['name' => 'IDOR Hash Cracking', 'diff' => 'hard', 'points' => 50, 'category' => 'crypto'],
    14 => ['name' => 'CSRF Token Prediction', 'diff' => 'hard', 'points' => 50, 'category' => 'web'],
    15 => ['name' => 'Path Traversal Encoding', 'diff' => 'hard', 'points' => 50, 'category' => 'web'],
    16 => ['name' => 'Blind Command Injection', 'diff' => 'expert', 'points' => 100, 'category' => 'system'],
    17 => ['name' => 'XXE Out-of-Band', 'diff' => 'expert', 'points' => 100, 'category' => 'xml'],
    18 => ['name' => 'Race Condition', 'diff' => 'expert', 'points' => 100, 'category' => 'logic'],
    19 => ['name' => 'SSRF Filter Bypass', 'diff' => 'nightmare', 'points' => 200, 'category' => 'network'],
    20 => ['name' => 'Deserialization RCE', 'diff' => 'nightmare', 'points' => 200, 'category' => 'web']
];

// Enhanced WAF patterns
$WAF_PATTERNS = [
    '/union\s+select/i', '/or\s+1\s*=\s*1/i', '/<script>/i',
    '/onerror\s*=/i', '/\.\.\/\.\.\//i', '/etc\/passwd/i',
    '/system\(/i', '/exec\(/i', '/shell_exec\(/i'
];

// Enhanced database connection with better error handling
function getDbConnection() {
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($conn->connect_error) {
                error_log("Database connection failed: " . $conn->connect_error);
                return null;
            }
            $conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            error_log("Database connection exception: " . $e->getMessage());
            return null;
        }
    }
    return $conn;
}

// Enhanced password hashing (still educational for weak examples)
function hashPassword($pass) {
    // Note: In production, use password_hash()
    return md5($pass); // Educational: intentionally weak for demonstration
}

function verifyPassword($password, $hash) {
    return hashPassword($password) === $hash;
}

// Enhanced WAF check
function wafCheck($input, $levelNum = 1) {
    global $WAF_PATTERNS;
    if ($levelNum < 11) return true; // No WAF for easy/moderate

    $decoded = urldecode(urldecode($input));
    foreach ($WAF_PATTERNS as $pattern) {
        if (preg_match($pattern, $decoded)) return false;
    }
    return true;
}

// Enhanced progress saving with subscription checks
function saveProgress($userId, $levelId, $flag) {
    global $LEVELS;
    $conn = getDbConnection();
    if (!$conn) return false;

    // Check subscription limits
    $userTier = getUserSubscriptionTier($userId);
    if (!canAccessLevel($userTier, $levelId)) {
        return false;
    }

    $points = $LEVELS[$levelId]['points'] ?? 10;
    
    try {
        $stmt = $conn->prepare("INSERT IGNORE INTO user_progress (user_id, level_id, flag, points_earned) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $userId, $levelId, $flag, $points);
        $stmt->execute();

        // Update total points
        $conn->query("UPDATE users SET points = (SELECT COALESCE(SUM(points_earned),0) FROM user_progress WHERE user_id = $userId) WHERE id = $userId");

        return true;
    } catch (Exception $e) {
        error_log("Progress save error: " . $e->getMessage());
        return false;
    }
}

function getProgress($userId) {
    $conn = getDbConnection();
    if (!$conn) return [];

    $result = $conn->query("SELECT level_id FROM user_progress WHERE user_id = $userId");
    $levels = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $levels[] = intval($row['level_id']);
        }
    }
    return $levels;
}

function checkFlag($levelId, $submitted) {
    global $FLAGS;
    return isset($FLAGS[$levelId]) && trim($submitted) === $FLAGS[$levelId];
}

// Enhanced CSRF protection
function generateCSRFToken() {
    $time = floor(time() / 300) * 300;
    return substr(hash_hmac('sha256', session_id() . $time . SECRET_KEY, SECRET_KEY), 0, 32);
}

function verifyCSRFToken($token) {
    $time = floor(time() / 300) * 300;
    $expected = substr(hash_hmac('sha256', session_id() . $time . SECRET_KEY, SECRET_KEY), 0, 32);
    return hash_equals($expected, $token);
}

// Subscription management functions
function getUserSubscriptionTier($userId) {
    $conn = getDbConnection();
    if (!$conn) return 'free';
    
    $result = $conn->query("SELECT subscription_tier FROM users WHERE id = $userId");
    if ($result && $row = $result->fetch_assoc()) {
        return $row['subscription_tier'] ?? 'free';
    }
    return 'free';
}

function canAccessLevel($tier, $levelId) {
    $maxLevels = SUBSCRIPTION_TIERS[$tier]['max_levels'] ?? 10;
    return $levelId <= $maxLevels;
}

// Enhanced logging
function logActivity($action, $data = '') {
    $log = date('Y-m-d H:i:s') . "|" . ($_SERVER['REMOTE_ADDR'] ?? 'CLI') . "|$action|$data\n";
    @file_put_contents(__DIR__ . '/logs/activity.log', $log, FILE_APPEND | LOCK_EX);
}

// Email validation
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Rate limiting
function checkRateLimit($ip, $action, $maxAttempts = 5, $timeWindow = 300) {
    $logFile = __DIR__ . '/logs/rate_limit.log';
    $currentTime = time();
    
    if (!file_exists($logFile)) {
        return true;
    }
    
    $lines = file($logFile);
    $recentAttempts = [];
    
    foreach ($lines as $line) {
        $parts = explode('|', trim($line));
        if (count($parts) >= 4) {
            $timestamp = strtotime($parts[0]);
            $logIp = $parts[1];
            $logAction = $parts[2];
            
            if ($logIp === $ip && $logAction === $action && ($currentTime - $timestamp) <= $timeWindow) {
                $recentAttempts[] = $timestamp;
            }
        }
    }
    
    return count($recentAttempts) < $maxAttempts;
}

function logRateLimit($ip, $action) {
    $log = date('Y-m-d H:i:s') . "|" . $ip . "|$action|\n";
    @file_put_contents(__DIR__ . '/logs/rate_limit.log', $log, FILE_APPEND | LOCK_EX);
}
?>