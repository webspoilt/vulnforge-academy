<?php
/**
 * Secure URL Router with Obfuscation and Dynamic Routing
 * VulnForge Academy - Advanced Routing System (FIXED VERSION)
 */

require_once 'security-layer-fixed.php';

class SecureRouter {
    private $security_manager;
    private $route_cache = [];
    private $encryption_key;
    
    // Obfuscated route mappings
    private $route_map = [
        'x7k9m2p5q8r3t1' => ['controller' => 'admin', 'action' => 'dashboard'],
        'z9b4n6v1c8x5w2' => ['controller' => 'api', 'action' => 'auth'],
        'k3j8h5g2f9d6a7' => ['controller' => 'config', 'action' => 'settings'],
        'm4p1l8o6e9c3z0' => ['controller' => 'database', 'action' => 'manage'],
        'y2s5a8b4n7c1x9' => ['controller' => 'includes', 'action' => 'functions'],
        'f6d8e1c9b4a7x2' => ['controller' => 'auth', 'action' => 'login'],
        'q1w5e8r2t6y9u3' => ['controller' => 'modules', 'action' => 'load'],
        
        // Public routes
        'home' => ['controller' => 'index', 'action' => 'home'],
        'login' => ['controller' => 'auth', 'action' => 'login'],
        'register' => ['controller' => 'auth', 'action' => 'register'],
        'dashboard' => ['controller' => 'user', 'action' => 'dashboard'],
        'levels' => ['controller' => 'challenge', 'action' => 'levels'],
        'pricing' => ['controller' => 'billing', 'action' => 'pricing']
    ];
    
    public function __construct() {
        $this->security_manager = new SecurityManager();
        $this->encryption_key = $this->security_manager->getEncryptionKey();
        $this->initializeRouteCache();
    }
    
    private function initializeRouteCache() {
        // Pre-generate route cache for performance
        $this->route_cache = [
            'public_routes' => array_filter($this->route_map, function($route) {
                return in_array($route['controller'], ['index', 'auth', 'billing']);
            }),
            'protected_routes' => array_filter($this->route_map, function($route) {
                return !in_array($route['controller'], ['index', 'auth', 'billing']);
            }),
            'admin_routes' => array_filter($this->route_map, function($route) {
                return in_array($route['controller'], ['admin', 'config', 'database']);
            })
        ];
    }
    
    public function handleRequest() {
        $request_uri = $this->cleanRequestUri($_SERVER['REQUEST_URI'] ?? '/');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Log the request for security monitoring
        $this->logRequest($request_uri, $method);
        
        // Check rate limiting
        $rate_limiter = new AdvancedRateLimiter();
        if (!$rate_limiter->checkLimit('general_request')) {
            http_response_code(429);
            exit('Rate limit exceeded');
        }
        
        $rate_limiter->incrementCounter('general_request');
        
        // Parse the request
        $route_info = $this->parseRoute($request_uri);
        
        if (!$route_info) {
            // Try dynamic route generation
            $route_info = $this->generateDynamicRoute($request_uri);
        }
        
        if (!$route_info) {
            // Check for honeypot routes
            if ($this->isHoneypotRoute($request_uri)) {
                $this->handleHoneypotRequest($request_uri);
                return;
            }
            
            // Return 404 with decoy content
            $this->handle404($request_uri);
            return;
        }
        
        // Security checks before route execution
        if (!$this->preRouteSecurityCheck($route_info)) {
            http_response_code(403);
            exit('Access denied');
        }
        
        // Execute the route
        $this->executeRoute($route_info);
    }
    
    private function cleanRequestUri($uri) {
        // Remove query parameters for route matching
        $uri = parse_url($uri, PHP_URL_PATH) ?? '/';
        
        // Remove trailing slash (except for root)
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }
        
        return $uri;
    }
    
    private function parseRoute($uri) {
        // Direct route mapping
        if (isset($this->route_map[$uri])) {
            return $this->route_map[$uri];
        }
        
        // Encrypted route pattern
        if (strpos($uri, '/secure/') === 0) {
            $encrypted_path = substr($uri, 8); // Remove '/secure/'
            return $this->parseEncryptedRoute($encrypted_path);
        }
        
        // Dynamic pattern matching
        return $this->matchDynamicPattern($uri);
    }
    
    private function parseEncryptedRoute($encrypted_path) {
        try {
            // Decrypt the route path
            $decrypted = $this->security_manager->decrypt(urldecode($encrypted_path));
            $route_data = json_decode($decrypted, true);
            
            if ($route_data && isset($route_data['controller']) && isset($route_data['action'])) {
                // Verify route signature
                if ($this->verifyRouteSignature($route_data)) {
                    return $route_data;
                }
            }
        } catch (Exception $e) {
            $this->logSuspiciousActivity('Invalid encrypted route: ' . $e->getMessage());
        }
        
        return null;
    }
    
    private function verifyRouteSignature($route_data) {
        if (!isset($route_data['signature'])) {
            return false;
        }
        
        $signature = $route_data['signature'];
        unset($route_data['signature']);
        
        $expected_signature = hash_hmac('sha256', json_encode($route_data), $this->encryption_key);
        
        return hash_equals($expected_signature, $signature);
    }
    
    private function matchDynamicPattern($uri) {
        // Pattern: /[controller]/[action] with obfuscated names
        $parts = explode('/', trim($uri, '/'));
        
        if (count($parts) >= 2) {
            $controller_obfuscated = $parts[0];
            $action_obfuscated = $parts[1];
            
            // Try to match obfuscated controller
            foreach ($this->route_map as $obfuscated => $route_info) {
                if (strpos($controller_obfuscated, substr($obfuscated, 0, 4)) === 0) {
                    return [
                        'controller' => $route_info['controller'],
                        'action' => $action_obfuscated,
                        'parameters' => array_slice($parts, 2)
                    ];
                }
            }
        }
        
        return null;
    }
    
    private function generateDynamicRoute($uri) {
        // Generate a one-time dynamic route for security
        $session_id = session_id();
        $timestamp = time();
        $route_seed = hash('sha256', $session_id . $timestamp . $uri);
        
        // Check if this route was recently generated
        if (isset($_SESSION['dynamic_routes'][$route_seed])) {
            $route_data = $_SESSION['dynamic_routes'][$route_seed];
            unset($_SESSION['dynamic_routes'][$route_seed]); // One-time use
            return $route_data;
        }
        
        return null;
    }
    
    private function isHoneypotRoute($uri) {
        $honeypot_routes = [
            '/fake-admin-panel',
            '/database-debug',
            '/backup-files',
            '/.env',
            '/config.php',
            '/admin.php',
            '/phpmyadmin',
            '/wp-admin',
            '/server-status'
        ];
        
        return in_array($uri, $honeypot_routes);
    }
    
    private function handleHoneypotRequest($uri) {
        $honeypot = new HoneypotManager();
        // The honeypot manager is already initialized and will handle this
        exit;
    }
    
    private function handle404($uri) {
        // Generate a realistic 404 page with decoy information
        $decoy_data = $this->generateDecoy404Content();
        
        http_response_code(404);
        echo $this->render404Page($decoy_data);
        
        // Log the 404 request
        $this->logSuspiciousActivity('404 request: ' . $uri);
    }
    
    private function generateDecoy404Content() {
        // Generate fake file/directory structure
        $fake_structure = [
            'files' => [
                'config.php',
                'database.php',
                'admin.php',
                'backup.sql',
                'users.txt',
                'logs/error.log'
            ],
            'directories' => [
                'admin',
                'api',
                'config',
                'backup',
                'logs',
                'tmp'
            ]
        ];
        
        return $fake_structure;
    }
    
    private function render404Page($decoy_data) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>404 - Page Not Found</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; background: #1a1a1a; color: #fff; }
                .container { max-width: 800px; margin: 0 auto; }
                .error { background: #2a2a2a; padding: 30px; border-radius: 10px; margin: 20px 0; }
                .file-list { background: #1a1a1a; padding: 20px; border-radius: 5px; margin: 20px 0; }
                .file-list ul { list-style: none; padding: 0; }
                .file-list li { padding: 5px 0; border-bottom: 1px solid #333; }
                .file-list li:last-child { border-bottom: none; }
                a { color: #4CAF50; text-decoration: none; }
                a:hover { text-decoration: underline; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>404 - Page Not Found</h1>
                <div class="error">
                    <h2>The page you're looking for doesn't exist.</h2>
                    <p>Available files and directories:</p>
                </div>
                
                <div class="file-list">
                    <h3>Files:</h3>
                    <ul>
                        <?php foreach ($decoy_data['files'] as $file): ?>
                        <li><?php echo htmlspecialchars($file); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <h3>Directories:</h3>
                    <ul>
                        <?php foreach ($decoy_data['directories'] as $dir): ?>
                        <li><?php echo htmlspecialchars($dir); ?>/</li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <p><a href="/">Return to Homepage</a></p>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    private function preRouteSecurityCheck($route_info) {
        $controller = $route_info['controller'];
        
        // Check authentication for protected routes
        if (in_array($controller, ['admin', 'config', 'database'])) {
            return $this->checkAuthentication();
        }
        
        // Check user permissions
        if (isset($route_info['required_permission'])) {
            return $this->checkPermission($route_info['required_permission']);
        }
        
        return true;
    }
    
    private function checkAuthentication() {
        return isset($_SESSION['username']) && isset($_SESSION['secure_token']);
    }
    
    private function checkPermission($permission) {
        // Implement permission checking logic
        $user_permissions = $_SESSION['user_permissions'] ?? [];
        return in_array($permission, $user_permissions);
    }
    
    private function executeRoute($route_info) {
        $controller = $route_info['controller'];
        $action = $route_info['action'];
        $parameters = $route_info['parameters'] ?? [];
        
        // Add random delay to make timing attacks harder
        $this->addRandomDelay();
        
        // Execute controller
        $this->loadController($controller, $action, $parameters);
    }
    
    // FIXED: Enhanced controller loading with error handling
    private function loadController($controller, $action, $parameters) {
        // Controller file path with obfuscation
        $controller_file = "controllers/{$controller}.controller.php";
        
        // FIXED: Check if file exists before including
        if (!file_exists($controller_file)) {
            $this->logSuspiciousActivity('Controller not found: ' . $controller_file);
            http_response_code(404);
            echo "Controller not found";
            return;
        }
        
        try {
            require_once $controller_file;
            
            // Instantiate controller
            $class_name = ucfirst($controller) . 'Controller';
            
            if (!class_exists($class_name)) {
                $this->logSuspiciousActivity('Controller class not found: ' . $class_name);
                http_response_code(500);
                echo "Controller class error";
                return;
            }
            
            $controller_instance = new $class_name();
            
            // Call action method
            $method_name = $action . 'Action';
            
            if (!method_exists($controller_instance, $method_name)) {
                $this->logSuspiciousActivity('Action method not found: ' . $method_name);
                http_response_code(404);
                echo "Action not found";
                return;
            }
            
            // Execute the action with error handling
            try {
                call_user_func_array([$controller_instance, $method_name], $parameters);
            } catch (Exception $e) {
                $this->logSuspiciousActivity('Controller action failed: ' . $e->getMessage());
                http_response_code(500);
                echo "Action execution error";
            }
            
        } catch (Exception $e) {
            $this->logSuspiciousActivity('Controller load failed: ' . $e->getMessage());
            http_response_code(500);
            echo "Controller error";
        }
    }
    
    private function addRandomDelay() {
        // Add random delay between 50-150ms
        $delay = rand(50000, 150000); // Microseconds
        usleep($delay);
    }
    
    private function logRequest($uri, $method) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'method' => $method,
            'uri' => $uri,
            'session_id' => session_id()
        ];
        
        // Log to secure location
        $log_file = '/tmp/.secure_requests.log';
        if (is_writable(dirname($log_file))) {
            file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
        }
    }
    
    private function logSuspiciousActivity($activity) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'activity' => $activity,
            'session_id' => session_id()
        ];
        
        error_log('SECURITY_SUSPICIOUS: ' . json_encode($log_entry));
    }
    
    // Utility methods for generating secure URLs
    public function generateSecureUrl($controller, $action, $parameters = []) {
        $route_data = [
            'controller' => $controller,
            'action' => $action,
            'parameters' => $parameters
        ];
        
        // Add signature
        $route_data['signature'] = hash_hmac('sha256', json_encode($route_data), $this->encryption_key);
        
        // Encrypt the route data
        $encrypted = $this->security_manager->encrypt(json_encode($route_data));
        
        return '/secure/' . urlencode($encrypted);
    }
    
    public function generateObfuscatedUrl($controller, $action, $parameters = []) {
        // Find obfuscated controller name
        $obfuscated_controller = null;
        foreach ($this->route_map as $obfuscated => $route_info) {
            if ($route_info['controller'] === $controller) {
                $obfuscated_controller = $obfuscated;
                break;
            }
        }
        
        if ($obfuscated_controller) {
            $url = '/' . $obfuscated_controller . '/' . $action;
            if (!empty($parameters)) {
                $url .= '/' . implode('/', $parameters);
            }
            return $url;
        }
        
        // Fallback to regular URL
        return "/{$controller}/{$action}";
    }
}

// Controller base class with security features
abstract class SecureController {
    protected $security_manager;
    protected $rate_limiter;
    
    public function __construct() {
        $this->security_manager = new SecurityManager();
        $this->rate_limiter = new AdvancedRateLimiter();
        
        // Check rate limiting for each controller action
        if (!$this->rate_limiter->checkLimit('general_request')) {
            http_response_code(429);
            exit('Rate limit exceeded');
        }
        
        $this->rate_limiter->incrementCounter('general_request');
    }
    
    protected function render($view, $data = []) {
        // Add security headers
        $this->addSecurityHeaders();
        
        // Extract data for view
        extract($data);
        
        // Include view with output buffering
        ob_start();
        $view_file = "views/{$view}.php";
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo "View not found: {$view}";
        }
        $content = ob_get_clean();
        
        // Add security wrapper
        return $this->wrapWithSecurity($content);
    }
    
    protected function addSecurityHeaders() {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\'');
    }
    
    private function wrapWithSecurity($content) {
        // Add anti-debugging measures
        $content = preg_replace('/var_dump\s*\(/', '/* var_dump removed for security */', $content);
        $content = preg_replace('/print_r\s*\(/', '/* print_r removed for security */', $content);
        $content = preg_replace('/error_reporting\s*\(/', '/* error_reporting disabled */', $content);
        
        // Add security watermark
        $watermark = "\n<!-- VulnForge Academy Security Layer v2025.1 -->\n";
        
        return str_replace('</body>', $watermark . '</body>', $content);
    }
    
    protected function requireAuth() {
        if (!isset($_SESSION['username'])) {
            header('Location: /login');
            exit;
        }
    }
    
    protected function requireAdmin() {
        $this->requireAuth();
        if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            http_response_code(403);
            exit('Admin access required');
        }
    }
    
    protected function jsonResponse($data, $status_code = 200) {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

// Example secure controller
class SecureIndexController extends SecureController {
    public function homeAction() {
        $data = [
            'title' => 'VulnForge Academy',
            'user' => $_SESSION['username'] ?? null,
            'secure_token' => $_SESSION['secure_token'] ?? null
        ];
        
        echo $this->render('index/home', $data);
    }
}

// Initialize and run the router
$router = new SecureRouter();
$router->handleRequest();
?>