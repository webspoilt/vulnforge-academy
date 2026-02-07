-- VulnForge Academy - Security Database Schema
-- Enhanced database structure for security features

-- ===========================================
-- SECURITY TABLES
-- ===========================================

-- Login attempt tracking table
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN DEFAULT FALSE,
    blocked BOOLEAN DEFAULT FALSE,
    blocked_reason TEXT,
    INDEX idx_username_time (username, attempt_time),
    INDEX idx_ip_time (ip_address, attempt_time),
    INDEX idx_attempt_time (attempt_time)
);

-- Security logs table
CREATE TABLE IF NOT EXISTS security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    threat_type VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    log_data JSON,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved BOOLEAN DEFAULT FALSE,
    INDEX idx_threat_type (threat_type),
    INDEX idx_created_at (created_at),
    INDEX idx_severity (severity),
    INDEX idx_resolved (resolved)
);

-- User activity tracking table
CREATE TABLE IF NOT EXISTS user_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    page_url TEXT,
    user_agent TEXT,
    session_duration INT,
    referer TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username_time (username, created_at),
    INDEX idx_ip_time (ip_address, created_at),
    INDEX idx_page_url (page_url(255))
);

-- Blocked IPs tracking table
CREATE TABLE IF NOT EXISTS blocked_ips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) UNIQUE NOT NULL,
    blocked_reason TEXT,
    blocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    blocked_by VARCHAR(255),
    is_permanent BOOLEAN DEFAULT FALSE,
    INDEX idx_expires (expires_at),
    INDEX idx_blocked_at (blocked_at)
);

-- User location history
CREATE TABLE IF NOT EXISTS login_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    country VARCHAR(100),
    city VARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username_time (username, attempt_time),
    INDEX idx_ip_time (ip_address, attempt_time)
);

-- Honeypot trigger logs
CREATE TABLE IF NOT EXISTS honeypot_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    honeypot_endpoint VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    payload JSON,
    triggered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_endpoint (honeypot_endpoint),
    INDEX idx_triggered_at (triggered_at),
    INDEX idx_ip_time (ip_address, triggered_at)
);

-- Security configuration table
CREATE TABLE IF NOT EXISTS security_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(255) UNIQUE NOT NULL,
    config_value TEXT,
    config_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by VARCHAR(255)
);

-- Session security tracking
CREATE TABLE IF NOT EXISTS session_security (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) UNIQUE NOT NULL,
    username VARCHAR(255),
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_valid BOOLEAN DEFAULT TRUE,
    csrf_token VARCHAR(255),
    INDEX idx_session_id (session_id),
    INDEX idx_username (username),
    INDEX idx_last_activity (last_activity)
);

-- Rate limiting tracking
CREATE TABLE IF NOT EXISTS rate_limit_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL, -- IP address or user ID
    action_type VARCHAR(100) NOT NULL,
    request_count INT DEFAULT 1,
    window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    window_end TIMESTAMP,
    blocked BOOLEAN DEFAULT FALSE,
    INDEX idx_identifier_action (identifier, action_type),
    INDEX idx_window_start (window_start)
);

-- ===========================================
-- ENHANCED USER TABLES
-- ===========================================

-- Enhanced users table
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS status ENUM('active', 'blocked', 'suspended', 'pending') DEFAULT 'active',
ADD COLUMN IF NOT EXISTS blocked_reason TEXT,
ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS login_count INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS failed_login_attempts INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS account_locked_until TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS email_verified BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS two_factor_enabled BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS security_level ENUM('basic', 'enhanced', 'admin') DEFAULT 'basic',
ADD COLUMN IF NOT EXISTS created_ip VARCHAR(45),
ADD COLUMN IF NOT EXISTS last_ip VARCHAR(45),
ADD COLUMN IF NOT EXISTS subscription_tier ENUM('free', 'pro', 'enterprise') DEFAULT 'free',
ADD COLUMN IF NOT EXISTS subscription_expires TIMESTAMP NULL;

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_users_status ON users(status);
CREATE INDEX IF NOT EXISTS idx_users_last_login ON users(last_login);
CREATE INDEX IF NOT EXISTS idx_users_subscription ON users(subscription_tier);

-- ===========================================
-- CHALLENGE PROGRESSION ENHANCEMENT
-- ===========================================

-- Challenge attempts tracking
CREATE TABLE IF NOT EXISTS challenge_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    challenge_id INT NOT NULL,
    attempt_data JSON,
    success BOOLEAN DEFAULT FALSE,
    time_taken INT, -- seconds
    hints_used INT DEFAULT 0,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    INDEX idx_username_challenge (username, challenge_id),
    INDEX idx_challenge_time (challenge_id, attempt_time),
    INDEX idx_success (success)
);

-- User challenge progress
CREATE TABLE IF NOT EXISTS user_challenge_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    challenge_id INT NOT NULL,
    status ENUM('not_started', 'in_progress', 'completed', 'failed') DEFAULT 'not_started',
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    best_time INT, -- best completion time in seconds
    attempts_count INT DEFAULT 0,
    hints_used_total INT DEFAULT 0,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_challenge (username, challenge_id),
    INDEX idx_status (status),
    INDEX idx_last_activity (last_activity)
);

-- ===========================================
-- SUBSCRIPTION ENHANCEMENT
-- ===========================================

-- Subscription tracking
CREATE TABLE IF NOT EXISTS user_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    subscription_type ENUM('free', 'pro', 'enterprise') NOT NULL,
    status ENUM('active', 'cancelled', 'expired', 'pending') DEFAULT 'active',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    auto_renew BOOLEAN DEFAULT TRUE,
    payment_method VARCHAR(100),
    amount DECIMAL(10, 2),
    currency VARCHAR(3) DEFAULT 'USD',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username_status (username, status),
    INDEX idx_expires (expires_at)
);

-- Payment history
CREATE TABLE IF NOT EXISTS payment_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    subscription_id INT,
    transaction_id VARCHAR(255) UNIQUE,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    payment_method VARCHAR(100),
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    INDEX idx_username_date (username, payment_date),
    INDEX idx_status (status)
);

-- ===========================================
-- CERTIFICATE SYSTEM ENHANCEMENT
-- ===========================================

-- Certificate tracking
CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    certificate_type ENUM('challenge_completion', 'course_completion', 'special_achievement') NOT NULL,
    certificate_data JSON, -- Store certificate details
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    is_revoked BOOLEAN DEFAULT FALSE,
    revoked_at TIMESTAMP NULL,
    revoked_reason TEXT,
    verification_code VARCHAR(255) UNIQUE,
    pdf_path VARCHAR(500),
    INDEX idx_username_type (username, certificate_type),
    INDEX idx_issued_at (issued_at),
    INDEX idx_verification_code (verification_code)
);

-- ===========================================
-- INITIAL SECURITY CONFIGURATION
-- ===========================================

-- Insert default security configuration
INSERT IGNORE INTO security_config (config_key, config_value, config_type) VALUES
('max_login_attempts', '5', 'integer'),
('lockout_duration', '900', 'integer'), -- 15 minutes in seconds
('session_timeout', '3600', 'integer'), -- 1 hour
('require_email_verification', 'true', 'boolean'),
('enable_two_factor', 'false', 'boolean'),
('rate_limit_requests_per_minute', '60', 'integer'),
('rate_limit_requests_per_hour', '1000', 'integer'),
('enable_honeypots', 'true', 'boolean'),
('log_retention_days', '90', 'integer'),
('enable_geolocation_tracking', 'false', 'boolean'),
('admin_email', 'admin@vulnforge.academy', 'string'),
('security_notification_email', 'security@vulnforge.academy', 'string'),
('enable_advanced_monitoring', 'true', 'boolean'),
('threat_response_level', 'medium', 'string'); -- low, medium, high, critical

-- ===========================================
-- SAMPLE DATA FOR TESTING
-- ===========================================

-- Insert sample admin user (password: admin123)
INSERT IGNORE INTO users (username, email, password, role, status, security_level, subscription_tier) VALUES
('admin', 'admin@vulnforge.academy', '$argon2id$v=19$m=65536,t=4,p=3$YWRtaW4xMjM0NTY$ZHVtbXktcGFzc3dvcmQ', 'admin', 'active', 'admin', 'enterprise');

-- Insert sample regular user (password: user12345)
INSERT IGNORE INTO users (username, email, password, role, status, security_level, subscription_tier) VALUES
('user1', 'user1@example.com', '$argon2id$v=19$m=65536,t=4,p=3$dXNlcjEyMzQ1Ng$ZXhhbXBsZS1wYXNzd29yZA', 'user', 'active', 'basic', 'free');

-- ===========================================
-- VIEWS FOR REPORTING
-- ===========================================

-- Security dashboard view
CREATE OR REPLACE VIEW security_dashboard AS
SELECT 
    DATE(created_at) as date,
    threat_type,
    COUNT(*) as threat_count,
    COUNT(DISTINCT ip_address) as unique_ips,
    severity
FROM security_logs 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(created_at), threat_type, severity
ORDER BY date DESC, threat_count DESC;

-- User activity summary view
CREATE OR REPLACE VIEW user_activity_summary AS
SELECT 
    username,
    COUNT(*) as total_requests,
    COUNT(DISTINCT ip_address) as unique_ips,
    MIN(created_at) as first_activity,
    MAX(created_at) as last_activity,
    AVG(session_duration) as avg_session_duration
FROM user_activities 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY username
ORDER BY total_requests DESC;

-- ===========================================
-- STORED PROCEDURES
-- ===========================================

DELIMITER //

-- Procedure to clean old logs
CREATE PROCEDURE CleanOldLogs(IN days_to_keep INT)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE table_name VARCHAR(255);
    DECLARE cur CURSOR FOR 
        SELECT DISTINCT table_name 
        FROM information_schema.tables 
        WHERE table_schema = DATABASE() 
        AND table_name IN ('security_logs', 'login_attempts', 'user_activities', 'honeypot_logs');
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO table_name;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        SET @sql = CONCAT('DELETE FROM ', table_name, ' WHERE created_at < DATE_SUB(NOW(), INTERVAL ', days_to_keep, ' DAY)');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END LOOP;
    
    CLOSE cur;
END//

-- Procedure to block IP address
CREATE PROCEDURE BlockIP(IN ip_addr VARCHAR(45), IN reason TEXT, IN duration_hours INT)
BEGIN
    DECLARE expires_at TIMESTAMP;
    
    SET expires_at = DATE_ADD(NOW(), INTERVAL duration_hours HOUR);
    
    INSERT INTO blocked_ips (ip_address, blocked_reason, blocked_at, expires_at, is_permanent)
    VALUES (ip_addr, reason, NOW(), expires_at, FALSE)
    ON DUPLICATE KEY UPDATE
        blocked_reason = reason,
        expires_at = expires_at,
        is_permanent = FALSE;
END//

-- Procedure to get security stats
CREATE PROCEDURE GetSecurityStats()
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM security_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) as threats_24h,
        (SELECT COUNT(*) FROM blocked_ips WHERE expires_at > NOW()) as active_blocks,
        (SELECT COUNT(*) FROM login_attempts WHERE attempt_time >= DATE_SUB(NOW(), INTERVAL 1 HOUR) AND success = FALSE) as failed_logins_1h,
        (SELECT COUNT(*) FROM honeypot_logs WHERE triggered_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) as honeypot_triggers_24h;
END//

DELIMITER ;

-- ===========================================
-- TRIGGERS FOR AUTOMATION
-- ===========================================

DELIMITER //

-- Trigger to update user statistics on login
CREATE TRIGGER update_user_login_stats 
AFTER INSERT ON login_attempts
FOR EACH ROW
BEGIN
    IF NEW.success = TRUE THEN
        UPDATE users 
        SET 
            login_count = login_count + 1,
            last_login = NEW.attempt_time,
            last_ip = NEW.ip_address,
            failed_login_attempts = 0
        WHERE username = NEW.username;
    ELSE
        UPDATE users 
        SET 
            failed_login_attempts = failed_login_attempts + 1
        WHERE username = NEW.username;
    END IF;
END//

-- Trigger to auto-block after too many failed attempts
CREATE TRIGGER auto_block_user
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    IF NEW.failed_login_attempts >= 5 AND OLD.failed_login_attempts < 5 THEN
        CALL BlockIP(
            (SELECT ip_address FROM login_attempts WHERE username = NEW.username ORDER BY attempt_time DESC LIMIT 1),
            'Automatic block due to failed login attempts',
            24
        );
    END IF;
END//

DELIMITER ;

-- ===========================================
-- INDEXES FOR PERFORMANCE
-- ===========================================

-- Additional indexes for better performance
CREATE INDEX IF NOT EXISTS idx_security_logs_threat_time ON security_logs(threat_type, created_at);
CREATE INDEX IF NOT EXISTS idx_user_activities_user_time ON user_activities(username, created_at);
CREATE INDEX IF NOT EXISTS idx_login_attempts_success_time ON login_attempts(success, attempt_time);
CREATE INDEX IF NOT EXISTS idx_blocked_ips_active ON blocked_ips(expires_at) WHERE expires_at > NOW();

-- ===========================================
-- COMMENTS AND DOCUMENTATION
-- ===========================================

-- Add table comments
ALTER TABLE login_attempts COMMENT = 'Tracks all login attempts for security monitoring';
ALTER TABLE security_logs COMMENT = 'Central security event logging table';
ALTER TABLE user_activities COMMENT = 'Tracks user navigation and behavior patterns';
ALTER TABLE blocked_ips COMMENT = 'IP addresses blocked for security reasons';
ALTER TABLE honeypot_logs COMMENT = 'Logs of honeypot endpoint accesses';
ALTER TABLE session_security COMMENT = 'Session security tracking and validation';
ALTER TABLE rate_limit_tracking COMMENT = 'Rate limiting enforcement and tracking';