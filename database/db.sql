-- VulnForge Academy Enhanced Database
-- Compatible with: MySQL, MariaDB, PlanetScale, TiDB, Railway MySQL

CREATE DATABASE IF NOT EXISTS vulnforge;
USE vulnforge;

-- Enhanced Users table with subscription support
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    email_verified TINYINT DEFAULT 0,
    subscription_tier VARCHAR(20) DEFAULT 'free',
    subscription_expires TIMESTAMP NULL,
    role VARCHAR(20) DEFAULT 'user',
    points INT DEFAULT 0,
    total_levels_completed INT DEFAULT 0,
    last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Enhanced Progress with analytics
CREATE TABLE IF NOT EXISTS user_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    level_id INT NOT NULL,
    flag VARCHAR(100),
    points_earned INT DEFAULT 0,
    time_spent_seconds INT DEFAULT 0,
    attempts_count INT DEFAULT 1,
    solved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_progress (user_id, level_id)
);

-- Subscription payments
CREATE TABLE IF NOT EXISTS subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tier VARCHAR(20) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    status VARCHAR(20) DEFAULT 'active',
    payment_method VARCHAR(50),
    external_subscription_id VARCHAR(100),
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Affiliate tracking
CREATE TABLE IF NOT EXISTS affiliate_clicks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    course_id VARCHAR(100),
    course_name VARCHAR(255),
    affiliate_url TEXT,
    clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Certificate records
CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    certificate_type VARCHAR(50) NOT NULL,
    course_name VARCHAR(255),
    score INT,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verification_code VARCHAR(100) UNIQUE
);

-- Support tickets
CREATE TABLE IF NOT EXISTS support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'open',
    priority VARCHAR(20) DEFAULT 'medium',
    assigned_to VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Workshop registrations
CREATE TABLE IF NOT EXISTS workshop_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    workshop_name VARCHAR(255) NOT NULL,
    workshop_date TIMESTAMP NOT NULL,
    status VARCHAR(20) DEFAULT 'registered',
    payment_status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Corporate clients
CREATE TABLE IF NOT EXISTS corporate_clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    contact_email VARCHAR(100) NOT NULL,
    contact_name VARCHAR(100),
    plan_type VARCHAR(50),
    max_users INT,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User sessions for security
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    is_active TINYINT DEFAULT 1
);

-- Products for SQLi levels (enhanced)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    description TEXT,
    price DECIMAL(10,2),
    category VARCHAR(50),
    admin_notes TEXT,
    stock_quantity INT DEFAULT 0,
    is_hidden TINYINT DEFAULT 0
);

-- User profiles for IDOR
CREATE TABLE IF NOT EXISTS profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    full_name VARCHAR(100),
    bio TEXT,
    private_data TEXT,
    is_admin TINYINT DEFAULT 0,
    avatar_url VARCHAR(255)
);

-- Guestbook for XSS
CREATE TABLE IF NOT EXISTS guestbook (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bank for CSRF
CREATE TABLE IF NOT EXISTS bank_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    balance DECIMAL(15,2) DEFAULT 1000.00
);

-- Files for upload vulns
CREATE TABLE IF NOT EXISTS files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    filename VARCHAR(255),
    original_filename VARCHAR(255),
    file_size INT,
    mime_type VARCHAR(100),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Coupons for race conditions
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50),
    user_id INT,
    used TINYINT DEFAULT 0
);

-- Analytics events
CREATE TABLE IF NOT EXISTS analytics_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    event_type VARCHAR(50),
    event_data JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Enhanced sample data
INSERT INTO users (username, password, email, role, points, subscription_tier) VALUES
('admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@vulnforge.test', 'admin', 0, 'enterprise'),
('john', '527bd5b5d689e2c32ae974c6229ff785', 'john@vulnforge.test', 'user', 0, 'pro'),
('jane', 'ee11cbb19052e40b07aac0ca060c23ee', 'jane@vulnforge.test', 'user', 0, 'free'),
('guest', '084e0343a0486ff05530df6c705c8bb4', 'guest@vulnforge.test', 'guest', 0, 'free')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO products (name, description, price, category, admin_notes, stock_quantity) VALUES
('Laptop Pro', 'High-end laptop for developers', 1299.99, 'electronics', 'Cost: $800', 50),
('Security Book', 'Web hacking handbook', 49.99, 'books', 'FLAG{first_sqli_success}', 100),
('USB Drive', '256GB encrypted USB', 79.99, 'electronics', 'Best seller', 200),
('VPN Service', '1 year subscription', 99.99, 'software', 'Margin: 80%', 999),
('Hidden Product', 'Secret item', 0.01, 'secret', 'Admin eyes only!', 1)
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO profiles (user_id, full_name, bio, private_data, is_admin) VALUES
(1, 'Admin User', 'System administrator', 'FLAG{idor_user_data}', 1),
(2, 'John Doe', 'Security researcher', 'SSN: 123-45-6789', 0),
(3, 'Jane Smith', 'Bug bounty hunter', 'Phone: 555-0123', 0)
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO guestbook (name, message) VALUES
('Welcome Bot', 'Welcome to VulnForge Academy!'),
('Admin', 'Please be respectful in comments.')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO bank_accounts (user_id, balance) VALUES
(1, 99999.00), (2, 1000.00), (3, 1500.00), (4, 500.00)
ON DUPLICATE KEY UPDATE id=id;

-- Create indexes for better performance
CREATE INDEX idx_user_progress_user_id ON user_progress(user_id);
CREATE INDEX idx_user_progress_level_id ON user_progress(level_id);
CREATE INDEX idx_users_subscription_tier ON users(subscription_tier);
CREATE INDEX idx_subscriptions_user_id ON subscriptions(user_id);
CREATE INDEX idx_subscriptions_status ON subscriptions(status);
CREATE INDEX idx_analytics_events_user_id ON analytics_events(user_id);
CREATE INDEX idx_analytics_events_type ON analytics_events(event_type);
CREATE INDEX idx_user_sessions_token ON user_sessions(session_token);
CREATE INDEX idx_user_sessions_expires ON user_sessions(expires_at);