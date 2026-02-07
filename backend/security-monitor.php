<?php
/**
 * Advanced Security Monitoring and Response System
 * VulnForge Academy - Real-time Threat Detection and Response
 */

require_once 'security-layer.php';

class SecurityMonitoringSystem {
    private $threat_database;
    private $response_actions;
    private $alert_thresholds;
    private $monitoring_active = true;
    
    public function __construct() {
        $this->initializeThreatDatabase();
        $this->initializeResponseActions();
        $this->initializeAlertThresholds();
        $this->startMonitoring();
    }
    
    private function initializeThreatDatabase() {
        $this->threat_database = [
            'sql_injection_patterns' => [
                "/(\%27)|(\')|(\-\-)|(\%23)|(#)/i",
                "/(\%3D)|(=)[^\n]*((\%27)|(\')|(\-\-)|(\%3B)|(;))/i",
                "/\w*((\%27)|(\'))((\%6F)|o|(\%4F))((\%72)|r|(\%52))/i",
                "/((\%27)|(\'))union/i",
                "/exec(\s|\+)+(s|x)+p\+/i"
            ],
            'xss_patterns' => [
                "/<script[^>]*>.*?<\/script>/is",
                "/javascript:/i",
                "/on\w+\s*=/i",
                "/<iframe[^>]*>/i",
                "/<object[^>]*>/i"
            ],
            'path_traversal_patterns' => [
                "/\.\.\/|\.\.\\\/i",
                "/\.\.%2f|\.\.%5c/i",
                "/etc\/passwd/i",
                "/boot\.ini/i",
                "/windows\/system32/"
            ],
            'command_injection_patterns' => [
                "/;.*?(cat|ls|nc|netcat|wget|curl)/i",
                "/\|.*?(cat|ls|nc|netcat|wget|curl)/i",
                "/`[^`]*`/i",
                "/\$\([^)]*\)/i"
            ],
            'automated_tool_patterns' => [
                'sqlmap' => '/sqlmap/i',
                'nikto' => '/nikto/i',
                'nmap' => '/nmap/i',
                'dirb' => '/dirb/i',
                'gobuster' => '/gobuster/i',
                'burp' => '/burp/i',
                'zap' => '/zap/i'
            ]
        ];
    }
    
    private function initializeResponseActions() {
        $this->response_actions = [
            'log_only' => 'logSuspiciousActivity',
            'block_ip' => 'blockSuspiciousIP',
            'rate_limit' => 'applyRateLimiting',
            'capture_attempt' => 'captureAttackAttempt',
            'notify_admin' => 'sendSecurityAlert',
            'temporary_ban' => 'applyTemporaryBan',
            'honeypot_redirect' => 'redirectToHoneypot'
        ];
    }
    
    private function initializeAlertThresholds() {
        $this->alert_thresholds = [
            'sql_injection_attempts' => 1,
            'xss_attempts' => 1,
            'path_traversal_attempts' => 1,
            'command_injection_attempts' => 1,
            'automated_tool_detection' => 1,
            'failed_logins_per_hour' => 5,
            'suspicious_requests_per_minute' => 20,
            'error_page_access_per_hour' => 10
        ];
    }
    
    private function startMonitoring() {
        if (!$this->monitoring_active) {
            return;
        }
        
        // Monitor incoming requests
        $this->monitorIncomingRequest();
        
        // Monitor user behavior
        if (isset($_SESSION['username'])) {
            $this->monitorUserBehavior($_SESSION['username']);
        }
        
        // Monitor file access attempts
        $this->monitorFileAccess();
        
        // Monitor database queries
        $this->monitorDatabaseActivity();
        
        // Monitor session security
        $this->monitorSessionSecurity();
    }
    
    private function monitorIncomingRequest() {
        $request_data = [
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'referer' => $_SERVER['HTTP_REFERER'] ?? '',
            'payload' => json_encode($_REQUEST)
        ];
        
        // Check for various attack patterns
        $this->checkSQLInjection($request_data);
        $this->checkXSSAttempts($request_data);
        $this->checkPathTraversal($request_data);
        $this->checkCommandInjection($request_data);
        $this->checkAutomatedTools($request_data);
        $this->checkSuspiciousHeaders($request_data);
        $this->checkRateAnomalies($request_data);
    }
    
    private function checkSQLInjection($request_data) {
        foreach ($this->threat_database['sql_injection_patterns'] as $pattern) {
            if (preg_match($pattern, $request_data['uri']) || 
                preg_match($pattern, $request_data['payload'])) {
                
                $this->triggerThreatResponse('sql_injection', $request_data);
                return;
            }
        }
    }
    
    private function checkXSSAttempts($request_data) {
        foreach ($this->threat_database['xss_patterns'] as $pattern) {
            if (preg_match($pattern, $request_data['payload'])) {
                $this->triggerThreatResponse('xss_attempt', $request_data);
                return;
            }
        }
    }
    
    private function checkPathTraversal($request_data) {
        foreach ($this->threat_database['path_traversal_patterns'] as $pattern) {
            if (preg_match($pattern, $request_data['uri'])) {
                $this->triggerThreatResponse('path_traversal', $request_data);
                return;
            }
        }
    }
    
    private function checkCommandInjection($request_data) {
        foreach ($this->threat_database['command_injection_patterns'] as $pattern) {
            if (preg_match($pattern, $request_data['payload'])) {
                $this->triggerThreatResponse('command_injection', $request_data);
                return;
            }
        }
    }
    
    private function checkAutomatedTools($request_data) {
        foreach ($this->threat_database['automated_tool_patterns'] as $tool => $pattern) {
            if (preg_match($pattern, $request_data['user_agent'])) {
                $request_data['detected_tool'] = $tool;
                $this->triggerThreatResponse('automated_tool', $request_data);
                return;
            }
        }
    }
    
    private function checkSuspiciousHeaders($request_data) {
        $suspicious_headers = [
            'X-Forwarded-Host',
            'X-Original-URL',
            'X-Rewrite-URL'
        ];
        
        foreach ($suspicious_headers as $header) {
            if (isset($_SERVER[$header])) {
                $request_data['suspicious_header'] = $header;
                $request_data['header_value'] = $_SERVER[$header];
                $this->triggerThreatResponse('suspicious_header', $request_data);
                return;
            }
        }
    }
    
    private function checkRateAnomalies($request_data) {
        $ip = $request_data['ip'];
        $key = "rate_check_{$ip}";
        
        $current_count = apcu_fetch($key) ?: 0;
        apcu_inc($key, 1, $success, 60); // 1 minute window
        
        if ($current_count > $this->alert_thresholds['suspicious_requests_per_minute']) {
            $request_data['request_count'] = $current_count;
            $this->triggerThreatResponse('rate_anomaly', $request_data);
        }
    }
    
    private function monitorUserBehavior($username) {
        $behavior_data = [
            'username' => $username,
            'current_page' => $_SERVER['REQUEST_URI'],
            'time_on_page' => time() - ($_SESSION['page_start_time'] ?? time()),
            'action_pattern' => $this->analyzeActionPattern($username)
        ];
        
        // Check for suspicious behavior patterns
        if ($this->isUnusualBehavior($behavior_data)) {
            $this->triggerThreatResponse('unusual_behavior', $behavior_data);
        }
        
        // Update user activity tracking
        $this->updateUserActivity($username);
    }
    
    private function analyzeActionPattern($username) {
        $recent_actions = apcu_fetch("user_actions_{$username}", $success) ?: [];
        
        // Analyze the pattern of actions
        $pattern = [
            'total_actions' => count($recent_actions),
            'unique_pages' => count(array_unique(array_column($recent_actions, 'page'))),
            'average_time_per_page' => $this->calculateAverageTime($recent_actions),
            'suspicious_patterns' => $this->detectSuspiciousPatterns($recent_actions)
        ];
        
        return $pattern;
    }
    
    private function isUnusualBehavior($behavior_data) {
        $pattern = $behavior_data['action_pattern'];
        
        // Check for too fast navigation (indicating automated tools)
        if ($pattern['average_time_per_page'] < 2) {
            return true;
        }
        
        // Check for accessing too many unique pages quickly
        if ($pattern['unique_pages'] > 20 && $pattern['total_actions'] > 50) {
            return true;
        }
        
        // Check for suspicious patterns
        if (!empty($pattern['suspicious_patterns'])) {
            return true;
        }
        
        return false;
    }
    
    private function detectSuspiciousPatterns($actions) {
        $suspicious_patterns = [];
        
        // Check for systematic directory traversal
        $pages = array_column($actions, 'page');
        $dir_traversal_count = 0;
        foreach ($pages as $page) {
            if (strpos($page, '..') !== false || strpos($page, '/') !== false) {
                $dir_traversal_count++;
            }
        }
        
        if ($dir_traversal_count > 3) {
            $suspicious_patterns[] = 'directory_traversal';
        }
        
        // Check for repeated admin page access
        $admin_access_count = 0;
        foreach ($pages as $page) {
            if (stripos($page, 'admin') !== false) {
                $admin_access_count++;
            }
        }
        
        if ($admin_access_count > 5) {
            $suspicious_patterns[] = 'excessive_admin_access';
        }
        
        return $suspicious_patterns;
    }
    
    private function calculateAverageTime($actions) {
        if (empty($actions)) {
            return 0;
        }
        
        $total_time = array_sum(array_column($actions, 'time_spent'));
        return $total_time / count($actions);
    }
    
    private function updateUserActivity($username) {
        $activity_data = [
            'timestamp' => time(),
            'page' => $_SERVER['REQUEST_URI'],
            'time_spent' => time() - ($_SESSION['page_start_time'] ?? time()),
            'ip' => $_SERVER['REMOTE_ADDR']
        ];
        
        $user_activities = apcu_fetch("user_actions_{$username}", $success) ?: [];
        $user_activities[] = $activity_data;
        
        // Keep only last 100 activities
        if (count($user_activities) > 100) {
            $user_activities = array_slice($user_activities, -100);
        }
        
        apcu_store("user_actions_{$username}", $user_activities, 3600); // 1 hour cache
        
        $_SESSION['page_start_time'] = time();
    }
    
    private function monitorFileAccess() {
        $requested_file = $_SERVER['REQUEST_URI'];
        
        // Check for sensitive file access attempts
        $sensitive_files = [
            '/.env',
            '/config.php',
            '/database.php',
            '/wp-config.php',
            '/admin/config.php',
            '/includes/config.php',
            '/backup.sql',
            '/dump.sql'
        ];
        
        foreach ($sensitive_files as $sensitive_file) {
            if (strpos($requested_file, $sensitive_file) !== false) {
                $file_access_data = [
                    'file_requested' => $sensitive_file,
                    'full_uri' => $requested_file,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ];
                
                $this->triggerThreatResponse('sensitive_file_access', $file_access_data);
                return;
            }
        }
    }
    
    private function monitorDatabaseActivity() {
        // This would be called when executing database queries
        $query = func_get_arg(0) ?? '';
        
        if ($this->isMaliciousQuery($query)) {
            $query_data = [
                'malicious_query' => $query,
                'ip' => $_SERVER['REMOTE_ADDR'],
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            $this->triggerThreatResponse('malicious_database_query', $query_data);
        }
    }
    
    private function isMaliciousQuery($query) {
        $malicious_patterns = [
            "/union\s+select/i",
            "/drop\s+table/i",
            "/delete\s+from/i",
            "/update\s+set/i",
            "/insert\s+into/i",
            "/information_schema/i",
            "/mysql\.user/i",
            "/load_file\s*\(/i",
            "/into\s+outfile/i"
        ];
        
        foreach ($malicious_patterns as $pattern) {
            if (preg_match($pattern, $query)) {
                return true;
            }
        }
        
        return false;
    }
    
    private function monitorSessionSecurity() {
        if (!isset($_SESSION['username'])) {
            return;
        }
        
        // Check for session hijacking attempts
        $current_ip = $_SERVER['REMOTE_ADDR'];
        $session_ip = $_SESSION['ip_address'] ?? null;
        
        if ($session_ip && $session_ip !== $current_ip) {
            $session_data = [
                'original_ip' => $session_ip,
                'current_ip' => $current_ip,
                'username' => $_SESSION['username'],
                'session_id' => session_id()
            ];
            
            $this->triggerThreatResponse('session_hijack_attempt', $session_data);
            
            // Invalidate session
            session_destroy();
            return;
        }
        
        // Check for session timeout anomalies
        $last_activity = $_SESSION['last_activity'] ?? 0;
        $current_time = time();
        
        if ($current_time - $last_activity > 3600) { // 1 hour
            // Normal timeout - update activity
            $_SESSION['last_activity'] = $current_time;
        }
        
        // Check for suspicious session token usage
        if (!$this->validateSessionToken()) {
            $this->triggerThreatResponse('invalid_session_token', ['session_id' => session_id()]);
        }
    }
    
    private function validateSessionToken() {
        if (!isset($_SESSION['secure_token'])) {
            return false;
        }
        
        $expected_token = $this->generateSessionToken();
        return hash_equals($_SESSION['secure_token'], $expected_token);
    }
    
    private function generateSessionToken() {
        return hash('sha256', $_SESSION['username'] . session_id() . $_SERVER['REMOTE_ADDR']);
    }
    
    private function triggerThreatResponse($threat_type, $data) {
        // Log the threat
        $this->logThreat($threat_type, $data);
        
        // Determine response actions based on threat severity
        $response_actions = $this->determineResponseActions($threat_type);
        
        // Execute response actions
        foreach ($response_actions as $action) {
            $this->executeResponseAction($action, $data);
        }
        
        // Update threat statistics
        $this->updateThreatStatistics($threat_type);
    }
    
    private function determineResponseActions($threat_type) {
        $threat_severity = [
            'sql_injection' => ['log_only', 'capture_attempt', 'notify_admin', 'block_ip'],
            'xss_attempt' => ['log_only', 'capture_attempt', 'notify_admin'],
            'path_traversal' => ['log_only', 'capture_attempt', 'notify_admin', 'block_ip'],
            'command_injection' => ['log_only', 'capture_attempt', 'notify_admin', 'block_ip'],
            'automated_tool' => ['log_only', 'rate_limit', 'block_ip'],
            'suspicious_header' => ['log_only', 'capture_attempt'],
            'rate_anomaly' => ['log_only', 'rate_limit', 'temporary_ban'],
            'unusual_behavior' => ['log_only', 'notify_admin'],
            'sensitive_file_access' => ['log_only', 'capture_attempt', 'notify_admin', 'block_ip'],
            'malicious_database_query' => ['log_only', 'capture_attempt', 'notify_admin', 'block_ip'],
            'session_hijack_attempt' => ['log_only', 'notify_admin', 'block_ip'],
            'invalid_session_token' => ['log_only']
        ];
        
        return $threat_severity[$threat_type] ?? ['log_only'];
    }
    
    private function executeResponseAction($action, $data) {
        $method = $this->response_actions[$action] ?? null;
        
        if ($method && method_exists($this, $method)) {
            $this->$method($data);
        }
    }
    
    private function logThreat($threat_type, $data) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'threat_type' => $threat_type,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'data' => $data,
            'session_id' => session_id()
        ];
        
        // Log to multiple secure locations
        $this->logToFile($log_entry);
        $this->logToDatabase($log_entry);
        $this->logToExternalService($log_entry);
    }
    
    private function logToFile($log_entry) {
        $log_file = '/tmp/.security_threats.log';
        $log_line = json_encode($log_entry) . "\n";
        
        file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
    }
    
    private function logToDatabase($log_entry) {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=vulnforge_academy", "root", "");
            $stmt = $pdo->prepare("
                INSERT INTO security_logs (threat_type, ip_address, user_agent, log_data, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $log_entry['threat_type'],
                $log_entry['ip'],
                $log_entry['user_agent'],
                json_encode($log_entry['data'])
            ]);
        } catch (Exception $e) {
            error_log("Failed to log to database: " . $e->getMessage());
        }
    }
    
    private function logToExternalService($log_entry) {
        // Send to external security monitoring service
        $webhook_url = getenv('SECURITY_WEBHOOK_URL');
        
        if ($webhook_url) {
            $ch = curl_init($webhook_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($log_entry));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            curl_close($ch);
        }
    }
    
    private function blockSuspiciousIP($data) {
        $ip = $data['ip'] ?? $_SERVER['REMOTE_ADDR'];
        
        // Add to blocked IPs list
        $blocked_ips = apcu_fetch('blocked_ips', $success) ?: [];
        $blocked_ips[$ip] = time() + 86400; // 24 hour ban
        apcu_store('blocked_ips', $blocked_ips, 86400);
        
        // Update firewall rules if possible
        $this->updateFirewallRules($ip, 'block');
    }
    
    private function applyRateLimiting($data) {
        $ip = $data['ip'] ?? $_SERVER['REMOTE_ADDR'];
        $key = "rate_limit_{$ip}";
        
        // Apply aggressive rate limiting
        apcu_store($key, time(), 300); // 5 minute block
    }
    
    private function captureAttackAttempt($data) {
        // Capture full request details for analysis
        $capture_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'method' => $_SERVER['REQUEST_METHOD'],
            'uri' => $_SERVER['REQUEST_URI'],
            'headers' => getallheaders(),
            'payload' => $_REQUEST,
            'server_vars' => $_SERVER,
            'threat_data' => $data
        ];
        
        // Store capture data
        $capture_file = '/tmp/.attack_captures/' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.json';
        file_put_contents($capture_file, json_encode($capture_data, JSON_PRETTY_PRINT));
    }
    
    private function sendSecurityAlert($data) {
        $alert_data = [
            'severity' => 'HIGH',
            'type' => 'Security Threat Detected',
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'threat_details' => $data
        ];
        
        // Send email alert (implement email service)
        $this->sendEmailAlert($alert_data);
        
        // Send webhook alert
        $this->sendWebhookAlert($alert_data);
    }
    
    private function sendEmailAlert($alert_data) {
        $admin_email = getenv('SECURITY_ADMIN_EMAIL');
        
        if ($admin_email) {
            $subject = "Security Alert: {$alert_data['type']}";
            $message = "Security threat detected:\n\n" . json_encode($alert_data, JSON_PRETTY_PRINT);
            
            mail($admin_email, $subject, $message);
        }
    }
    
    private function sendWebhookAlert($alert_data) {
        $webhook_url = getenv('SECURITY_WEBHOOK_URL');
        
        if ($webhook_url) {
            $ch = curl_init($webhook_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($alert_data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_exec($ch);
            curl_close($ch);
        }
    }
    
    private function applyTemporaryBan($data) {
        $ip = $data['ip'] ?? $_SERVER['REMOTE_ADDR'];
        
        // Add temporary ban
        $temp_bans = apcu_fetch('temp_bans', $success) ?: [];
        $temp_bans[$ip] = time() + 3600; // 1 hour ban
        apcu_store('temp_bans', $temp_bans, 3600);
    }
    
    private function redirectToHoneypot($data) {
        header('Location: /fake-admin-panel');
        exit;
    }
    
    private function updateFirewallRules($ip, $action) {
        // This would integrate with system firewall (iptables, ufw, etc.)
        // For demonstration, we'll log the action
        error_log("Firewall action: $action for IP: $ip");
    }
    
    private function updateThreatStatistics($threat_type) {
        $stats_key = "threat_stats_{$threat_type}";
        $stats = apcu_fetch($stats_key, $success) ?: ['count' => 0, 'last_occurrence' => 0];
        
        $stats['count']++;
        $stats['last_occurrence'] = time();
        
        apcu_store($stats_key, $stats, 86400); // 24 hour stats
    }
    
    // Public methods for checking IP status
    public function isIPBlocked($ip) {
        $blocked_ips = apcu_fetch('blocked_ips', $success) ?: [];
        
        if (isset($blocked_ips[$ip]) && $blocked_ips[$ip] > time()) {
            return true;
        }
        
        $temp_bans = apcu_fetch('temp_bans', $success) ?: [];
        if (isset($temp_bans[$ip]) && $temp_bans[$ip] > time()) {
            return true;
        }
        
        return false;
    }
    
    public function isRateLimited($ip) {
        $rate_key = "rate_limit_{$ip}";
        return apcu_exists($rate_key);
    }
    
    // Security dashboard data
    public function getSecurityDashboard() {
        return [
            'blocked_ips_count' => count(apcu_fetch('blocked_ips', $success) ?: []),
            'temp_bans_count' => count(apcu_fetch('temp_bans', $success) ?: []),
            'threat_stats' => $this->getThreatStatistics(),
            'recent_threats' => $this->getRecentThreats()
        ];
    }
    
    private function getThreatStatistics() {
        $threat_types = ['sql_injection', 'xss_attempt', 'path_traversal', 'command_injection', 'automated_tool'];
        $stats = [];
        
        foreach ($threat_types as $type) {
            $stats[$type] = apcu_fetch("threat_stats_{$type}", $success) ?: ['count' => 0];
        }
        
        return $stats;
    }
    
    private function getRecentThreats() {
        // Read from log file
        $log_file = '/tmp/.security_threats.log';
        
        if (!file_exists($log_file)) {
            return [];
        }
        
        $lines = file($log_file);
        $recent = array_slice($lines, -10); // Last 10 threats
        
        $threats = [];
        foreach ($recent as $line) {
            $threats[] = json_decode($line, true);
        }
        
        return array_reverse($threats);
    }
}

// Initialize monitoring system
$security_monitor = new SecurityMonitoringSystem();

// Check if current IP is blocked
if ($security_monitor->isIPBlocked($_SERVER['REMOTE_ADDR'])) {
    http_response_code(403);
    exit('Access denied - IP blocked');
}

// Check if IP is rate limited
if ($security_monitor->isRateLimited($_SERVER['REMOTE_ADDR'])) {
    http_response_code(429);
    exit('Rate limit exceeded');
}
?>