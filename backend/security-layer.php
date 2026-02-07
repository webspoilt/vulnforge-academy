<?php
/**
 * Secure Configuration with Obfuscation and Multi-Layer Protection
 * VulnForge Academy - Enhanced Security Layer (FIXED VERSION)
 */

// Prevent direct access
if (!defined('VFA_SECURE_ACCESS')) {
    define('VFA_SECURE_ACCESS', hash('sha256', 'vulnforge-academy-2025'));
}

// Encryption utilities
class SecurityManager {
    private $encryption_key;
    private $hash_key;
    
    public function __construct() {
        $this->encryption_key = $this->getEncryptionKey();
        $this->hash_key = $this->getHashKey();
    }
    
    private function getEncryptionKey() {
        // Key derived from multiple sources
        $sources = [
            $_SERVER['HTTP_HOST'] ?? 'localhost',
            $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            gethostname() ?? 'server',
            'vulnforge_academy_2025'
        ];
        return hash('sha256', implode('|', $sources));
    }
    
    private function getHashKey() {
        return hash('sha256', $this->encryption_key . 'salt_vfa_2025');
    }
    
    public function encrypt($data) {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $this->encryption_key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    public function decrypt($data) {
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $this->encryption_key, 0, $iv);
    }
    
    public function generateSecureToken() {
        return bin2hex(random_bytes(32));
    }
    
    public function hashPassword($password) {
        return password_hash($password . $this->hash_key, PASSWORD_ARGON2ID);
    }
    
    public function verifyPassword($password, $hash) {
        return password_verify($password . $this->hash_key, $hash);
    }
}

// Directory obfuscation system (FIXED)
class DirectoryObfuscator {
    private static $directory_map = [
        'admin' => 'x7k9m2p5q8r3t1',
        'api' => 'z9b4n6v1c8x5w2',
        'config' => 'k3j8h5g2f9d6a7',
        'database' => 'm4p1l8o6e9c3z0',
        'includes' => 'y2s5a8b4n7c1x9',
        'auth' => 'f6d8e1c9b4a7x2',
        'modules' => 'q1w5e8r2t6y9u3'
    ];
    
    public static function obfuscatePath($real_path) {
        // FIXED: Direct lookup instead of array_search
        if (isset(self::$directory_map[$real_path])) {
            return self::$directory_map[$real_path];
        }
        // Generate dynamic obfuscation for unknown paths
        return self::generateDynamicObfuscation($real_path);
    }
    
    public static function deobfuscatePath($obfuscated_path) {
        // FIXED: Search by value (obfuscated path) to get real path
        $reverse_map = array_flip(self::$directory_map);
        return $reverse_map[$obfuscated_path] ?? $obfuscated_path;
    }
    
    private static function generateDynamicObfuscation($path) {
        // FIXED: Use multiple entropy sources for better randomness
        $entropy = [
            $path,
            bin2hex(random_bytes(16)),
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            microtime(true)
        ];
        return substr(hash('sha256', implode('|', $entropy)), 0, 16);
    }
}

// File extension obfuscation
class FileObfuscator {
    private static $extension_map = [
        'php' => 'vfa',
        'inc' => 'core',
        'conf' => 'cfg',
        'log' => 'sys'
    ];
    
    public static function obfuscateExtension($filename) {
        $path_info = pathinfo($filename);
        if (isset(self::$extension_map[$path_info['extension']])) {
            $path_info['extension'] = self::$extension_map[$path_info['extension']];
            return $path_info['filename'] . '.' . $path_info['extension'];
        }
        return $filename;
    }
    
    public static function getRealExtension($filename) {
        $path_info = pathinfo($filename);
        $reverse_map = array_flip(self::$extension_map);
        return $reverse_map[$path_info['extension']] ?? $path_info['extension'];
    }
}

// Request signature validator (FIXED)
class RequestValidator {
    private $secret_key;
    
    public function __construct() {
        $this->secret_key = hash('sha256', 'request_signature_key_vfa_2025');
    }
    
    public function validateRequest() {
        // Check if signature exists
        if (!isset($_SERVER['HTTP_X_REQUEST_SIGNATURE'])) {
            return $this->isPublicEndpoint();
        }
        
        $signature = $_SERVER['HTTP_X_REQUEST_SIGNATURE'];
        $payload = $this->getRequestPayload();
        $expected = hash_hmac('sha256', $payload, $this->secret_key);
        
        if (!hash_equals($expected, $signature)) {
            $this->logSuspiciousActivity('Invalid request signature');
            return false;
        }
        
        return true;
    }
    
    // FIXED: Added null coalescing operators for safety
    private function getRequestPayload() {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') . 
               ($_SERVER['REQUEST_URI'] ?? '/') . 
               json_encode($_REQUEST ?? []) . 
               ($_SERVER['HTTP_USER_AGENT'] ?? '');
    }
    
    private function isPublicEndpoint() {
        $public_endpoints = ['/', '/index.php', '/login.php', '/register.php'];
        return in_array($_SERVER['REQUEST_URI'] ?? '/', $public_endpoints);
    }
    
    private function logSuspiciousActivity($activity) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'activity' => $activity,
            'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ];
        error_log('SECURITY_ALERT: ' . json_encode($log_entry));
    }
}

// Advanced authentication system (ENHANCED)
class AdvancedAuth {
    private $security_manager;
    
    public function __construct() {
        $this->security_manager = new SecurityManager();
    }
    
    public function authenticate($username, $password) {
        // Multi-layer authentication
        if (!$this->basicAuthCheck($username, $password)) {
            return false;
        }
        
        if (!$this->behavioralCheck($username)) {
            return false;
        }
        
        if (!$this->contextualCheck()) {
            return false;
        }
        
        return $this->establishSecureSession($username);
    }
    
    private function basicAuthCheck($username, $password) {
        // Check against database
        $pdo = $this->getDatabaseConnection();
        if (!$pdo) {
            $this->logSuspiciousActivity('Database connection failed during auth');
            return false;
        }
        
        try {
            $stmt = $pdo->prepare("SELECT id, password_hash, status FROM users WHERE username = ? AND status = 'active'");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !$this->security_manager->verifyPassword($password, $user['password_hash'])) {
                $this->logFailedLogin($username);
                return false;
            }
            
            return $user;
        } catch (Exception $e) {
            $this->logSuspiciousActivity('Database error during auth: ' . $e->getMessage());
            return false;
        }
    }
    
    // FIXED: Enhanced database connection with better error handling
    private function getDatabaseConnection() {
        $config = [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'dbname' => getenv('DB_NAME') ?: 'vulnforge_academy',
            'username' => getenv('DB_USER') ?: 'root',
            'password' => getenv('DB_PASS') ?: ''
        ];
        
        try {
            $pdo = new PDO(
                "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 5 // 5 second timeout
                ]
            );
            return $pdo;
        } catch (PDOException $e) {
            // Log error securely without exposing details
            error_log("Database connection failed: " . date('Y-m-d H:i:s'));
            
            if (defined('SECURITY_DEBUG') && SECURITY_DEBUG) {
                error_log("Debug: " . $e->getMessage());
            }
            
            return null;
        }
    }
    
    private function behavioralCheck($username) {
        // Check for suspicious behavior patterns
        $recent_attempts = $this->getRecentLoginAttempts($username);
        
        // Too many recent attempts
        if (count($recent_attempts) > 10) {
            $this->logSuspiciousActivity($username . ' - excessive login attempts');
            return false;
        }
        
        // Check for geographic anomalies
        if (!$this->validateLocation($username)) {
            return false;
        }
        
        return true;
    }
    
    private function contextualCheck() {
        // Time-based restrictions
        $current_hour = date('H');
        if ($current_hour < 6 || $current_hour > 22) {
            // Allow admin users only during off-hours
            if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
                return false;
            }
        }
        
        // User agent validation
        if (!$this->validateUserAgent()) {
            return false;
        }
        
        return true;
    }
    
    private function validateLocation($username) {
        // Get user's usual login locations
        $usual_locations = $this->getUserLocations($username);
        $current_location = $this->getCurrentLocation();
        
        // If no previous locations, allow and record
        if (empty($usual_locations)) {
            $this->recordUserLocation($username, $current_location);
            return true;
        }
        
        // Check if current location is familiar
        foreach ($usual_locations as $location) {
            $distance = $this->calculateDistance($current_location, $location);
            if ($distance < 100) { // Within 100km
                return true;
            }
        }
        
        // Unusual location - require additional verification
        $this->logSuspiciousActivity("Unusual login location for user: $username");
        return $this->requireAdditionalVerification($username);
    }
    
    private function validateUserAgent() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Block obviously automated tools
        $suspicious_patterns = [
            'sqlmap', 'nikto', 'nmap', 'dirb', 'gobuster',
            'curl', 'wget', 'python', 'requests', 'scrapy'
        ];
        
        foreach ($suspicious_patterns as $pattern) {
            if (stripos($user_agent, $pattern) !== false) {
                $this->logSuspiciousActivity("Suspicious user agent: $user_agent");
                return false;
            }
        }
        
        return true;
    }
    
    private function establishSecureSession($username) {
        // Generate secure session token
        $session_token = $this->security_manager->generateSecureToken();
        
        // Store in secure way
        $_SESSION['secure_token'] = $session_token;
        $_SESSION['username'] = $username;
        $_SESSION['login_time'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        
        // Set secure cookie parameters
        $secure_params = [
            'lifetime' => 0,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ];
        
        setcookie('vfa_session', $session_token, $secure_params);
        
        // Log successful authentication
        $this->logSuccessfulLogin($username);
        
        return true;
    }
    
    private function getRecentLoginAttempts($username) {
        $pdo = $this->getDatabaseConnection();
        if (!$pdo) return [];
        
        try {
            $stmt = $pdo->prepare("
                SELECT ip_address, user_agent, attempt_time 
                FROM login_attempts 
                WHERE username = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ORDER BY attempt_time DESC
            ");
            $stmt->execute([$username]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getCurrentLocation() {
        return [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'country' => 'Unknown',
            'timezone' => date_default_timezone_get()
        ];
    }
    
    private function getUserLocations($username) {
        $pdo = $this->getDatabaseConnection();
        if (!$pdo) return [];
        
        try {
            $stmt = $pdo->prepare("
                SELECT DISTINCT ip_address, country 
                FROM login_history 
                WHERE username = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stmt->execute([$username]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function calculateDistance($loc1, $loc2) {
        // Simple distance calculation (in production, use proper geolocation)
        return rand(10, 1000);
    }
    
    private function requireAdditionalVerification($username) {
        $this->logSuspiciousActivity("Additional verification required for user: $username");
        return false;
    }
    
    private function recordUserLocation($username, $location) {
        $pdo = $this->getDatabaseConnection();
        if (!$pdo) return;
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO login_history (username, ip_address, country, attempt_time)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$username, $location['ip'], $location['country']]);
        } catch (Exception $e) {
            // Silent fail for location recording
        }
    }
    
    private function logFailedLogin($username) {
        $pdo = $this->getDatabaseConnection();
        if (!$pdo) return;
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO login_attempts (username, ip_address, user_agent, attempt_time, success)
                VALUES (?, ?, ?, NOW(), 0)
            ");
            $stmt->execute([$username, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
            
            // Check for brute force attempts
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as attempts 
                FROM login_attempts 
                WHERE username = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND success = 0
            ");
            $stmt->execute([$username]);
            $result = $stmt->fetch();
            
            if ($result && $result['attempts'] >= 5) {
                $this->blockUser($username, 'Brute force protection');
            }
        } catch (Exception $e) {
            // Silent fail for logging
        }
    }
    
    private function logSuccessfulLogin($username) {
        $pdo = $this->getDatabaseConnection();
        if (!$pdo) return;
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO login_attempts (username, ip_address, user_agent, attempt_time, success)
                VALUES (?, ?, ?, NOW(), 1)
            ");
            $stmt->execute([$username, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
            
            // Update last login
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE username = ?");
            $stmt->execute([$username]);
        } catch (Exception $e) {
            // Silent fail for logging
        }
    }
    
    private function logSuspiciousActivity($activity) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'activity' => $activity
        ];
        error_log('SECURITY_SUSPICIOUS: ' . json_encode($log_entry));
    }
    
    private function blockUser($username, $reason) {
        $pdo = $this->getDatabaseConnection();
        if (!$pdo) return;
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET status = 'blocked', blocked_reason = ? WHERE username = ?");
            $stmt->execute([$reason, $username]);
            
            $this->logSuspiciousActivity("User blocked: $username - $reason");
        } catch (Exception $e) {
            // Silent fail for blocking
        }
    }
}

// Honeypot system (ENHANCED with output sanitization)
class HoneypotManager {
    public function __construct() {
        $this->setupHoneypots();
    }
    
    private function setupHoneypots() {
        $this->createHoneypotEndpoint('/fake-admin-panel', 'handleFakeAdmin');
        $this->createHoneypotEndpoint('/database-debug', 'handleFakeDbDebug');
        $this->createHoneypotEndpoint('/backup-files', 'handleFakeBackup');
        $this->createHoneypotEndpoint('/.env', 'handleFakeEnv');
        $this->createHoneypotEndpoint('/config.php', 'handleFakeConfig');
    }
    
    private function createHoneypotEndpoint($path, $handler) {
        if (($_SERVER['REQUEST_URI'] ?? '') === $path) {
            $this->$handler();
            exit;
        }
    }
    
    private function handleFakeAdmin() {
        $this->logAttackerActivity('fake_admin_panel_access');
        $this->generateFakeAdminPanel();
    }
    
    private function handleFakeDbDebug() {
        $this->logAttackerActivity('database_debug_access');
        
        // FIXED: Sanitized output
        header('Content-Type: text/html; charset=utf-8');
        echo "<!DOCTYPE html><html><head><title>Database Debug Panel</title></head><body>";
        echo "<h1>Database Debug Panel</h1>";
        echo "<p>Database: mysql://fake_user:fake_pass@localhost/fake_db</p>";
        echo "<p>Tables: " . htmlspecialchars("users, passwords, admin_logs") . "</p>";
        echo "<p>Status: Connected</p>";
        
        echo "<h2>User Credentials:</h2>";
        echo "<pre>";
        for ($i = 1; $i <= 10; $i++) {
            echo "admin" . $i . ": password" . $i . "123\n";
        }
        echo "</pre>";
        echo "</body></html>";
    }
    
    private function handleFakeBackup() {
        $this->logAttackerActivity('backup_access');
        
        $fake_data = "VulnForge Academy Database Backup\n";
        $fake_data .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $fake_data .= "CREATE DATABASE vulnforge_backup;\n";
        $fake_data .= "USE vulnforge_backup;\n\n";
        
        for ($i = 1; $i <= 100; $i++) {
            $fake_data .= "INSERT INTO users (username, email, password) VALUES ('user" . $i . "', 'user" . $i . "@example.com', 'hashed_password" . $i . "');\n";
        }
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="vulnforge_backup.sql"');
        echo $fake_data;
    }
    
    private function handleFakeEnv() {
        $this->logAttackerActivity('env_file_access');
        
        header('Content-Type: text/plain; charset=utf-8');
        echo "DB_HOST=localhost\n";
        echo "DB_USER=root\n";
        echo "DB_PASS=password123\n";
        echo "DB_NAME=vulnforge_academy\n";
        echo "SECRET_KEY=sk-fake-secret-key-for-demo\n";
        echo "ADMIN_EMAIL=admin@vulnforge.com\n";
        echo "API_KEY=ak-fake-api-key-for-demo\n";
    }
    
    private function handleFakeConfig() {
        $this->logAttackerActivity('config_file_access');
        
        header('Content-Type: text/plain; charset=utf-8');
        echo "<?php\n";
        echo "// VulnForge Academy Configuration\n";
        echo "\$config = [\n";
        echo "    'db_host' => 'localhost',\n";
        echo "    'db_user' => 'root',\n";
        echo "    'db_pass' => 'password123',\n";
        echo "    'admin_user' => 'admin',\n";
        echo "    'admin_pass' => 'admin123',\n";
        echo "    'debug_mode' => true,\n";
        echo "    'encryption_key' => '32-character-encryption-key-here'\n";
        echo "];\n";
    }
    
    private function generateFakeAdminPanel() {
        header('Content-Type: text/html; charset=utf-8');
        echo "<!DOCTYPE html><html><head><title>Admin Panel - VulnForge Academy</title>";
        echo "<style>body{font-family:Arial;margin:40px;background:#f0f0f0;} .form{margin:20px 0;} input{margin:5px;padding:8px;}</style>";
        echo "</head>";
        echo "<body><h1>VulnForge Academy - Admin Panel</h1>";
        echo "<p>Welcome to the admin panel. Use these credentials:</p>";
        echo "<ul>";
        echo "<li>Username: admin</li>";
        echo "<li>Password: admin123</li>";
        echo "</ul>";
        echo "<form method='post'>";
        echo "<div class='form'><input type='text' name='username' placeholder='Username'><br>";
        echo "<input type='password' name='password' placeholder='Password'><br>";
        echo "<button type='submit'>Login</button></div>";
        echo "</form>";
        echo "</body></html>";
    }
    
    private function logAttackerActivity($activity) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'honeypot_activity' => $activity,
            'payload' => $_REQUEST
        ];
        
        // Log to hidden file
        $log_file = '/tmp/.honeypot_log';
        if (is_writable(dirname($log_file))) {
            file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
        }
        
        // Also log to main security log
        error_log('HONEYPOT_TRIGGERED: ' . json_encode($log_entry));
    }
}

// Rate limiting with behavioral analysis (ENHANCED)
class AdvancedRateLimiter {
    private $limits = [
        'requests_per_minute' => 60,
        'requests_per_hour' => 1000,
        'admin_requests_per_minute' => 10,
        'login_attempts_per_hour' => 5
    ];
    
    public function checkLimit($action, $user_id = null) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Get current request rate
        $stats = $this->getRequestStats($ip, $user_id);
        
        switch ($action) {
            case 'general_request':
                return $this->checkGeneralLimit($stats);
            case 'admin_request':
                return $this->checkAdminLimit($stats);
            case 'login_attempt':
                return $this->checkLoginLimit($stats);
            default:
                return true;
        }
    }
    
    private function checkGeneralLimit($stats) {
        return $stats['requests_last_minute'] < $this->limits['requests_per_minute'] &&
               $stats['requests_last_hour'] < $this->limits['requests_per_hour'];
    }
    
    private function checkAdminLimit($stats) {
        return $stats['admin_requests_last_minute'] < $this->limits['admin_requests_per_minute'];
    }
    
    private function checkLoginLimit($stats) {
        return $stats['login_attempts_last_hour'] < $this->limits['login_attempts_per_hour'];
    }
    
    private function getRequestStats($ip, $user_id) {
        $cache_key = "stats_{$ip}_{$user_id}";
        $stats = apcu_fetch($cache_key, $success);
        
        if (!$success) {
            $stats = [
                'requests_last_minute' => 0,
                'requests_last_hour' => 0,
                'admin_requests_last_minute' => 0,
                'login_attempts_last_hour' => 0
            ];
        }
        
        return $stats;
    }
    
    public function incrementCounter($action, $user_id = null) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $cache_key = "stats_{$ip}_{$user_id}";
        $stats = $this->getRequestStats($ip, $user_id);
        
        $now = time();
        
        switch ($action) {
            case 'general_request':
                $stats['requests_last_minute']++;
                $stats['requests_last_hour']++;
                break;
            case 'admin_request':
                $stats['admin_requests_last_minute']++;
                break;
            case 'login_attempt':
                $stats['login_attempts_last_hour']++;
                break;
        }
        
        apcu_store($cache_key, $stats, 3600); // 1 hour cache
        
        // ENHANCED: Periodic cleanup to prevent memory leaks
        if (rand(1, 100) === 1) { // 1% chance
            $this->cleanupOldStats();
        }
    }
    
    // ENHANCED: Added cleanup function to prevent memory leaks
    private function cleanupOldStats() {
        if (!function_exists('apcu_cache_info')) {
            return; // APCu not available
        }
        
        try {
            $cache_info = apcu_cache_info(true);
            if (!$cache_info || !isset($cache_info['cache_list'])) {
                return;
            }
            
            foreach ($cache_info['cache_list'] as $entry) {
                if (strpos($entry['info'], 'stats_') === 0) {
                    // Check if entry is old and unused
                    $age = time() - $entry['creation_time'];
                    if ($age > 7200 && $entry['num_hits'] === 0) { // 2 hours old, never accessed
                        apcu_delete($entry['info']);
                    }
                }
            }
        } catch (Exception $e) {
            // Silent fail for cleanup
        }
    }
}

// Initialize security components
function initializeSecurity() {
    // Start secure session
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
    
    // Validate request
    $validator = new RequestValidator();
    if (!$validator->validateRequest()) {
        http_response_code(403);
        exit('Access Denied');
    }
    
    // Initialize rate limiting
    $rate_limiter = new AdvancedRateLimiter();
    
    // Initialize honeypot manager
    new HoneypotManager();
}

// Call initialization
initializeSecurity();
?>