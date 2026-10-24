-- ============================================================
-- مِهَن (Mihan) - Database Schema
-- MySQL 8.x - InnoDB - utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS mihan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mihan;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------
-- Users & Roles
-- ---------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer','provider','employee','admin') NOT NULL DEFAULT 'customer',
    profile_photo VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_verified_email TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('customer','provider','employee','admin') NOT NULL,
    permission_id INT NOT NULL,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE KEY uq_role_perm (role, permission_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Locations (Yemen-oriented hierarchy)
-- ---------------------------------------------------------
CREATE TABLE locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT DEFAULT NULL,
    name_ar VARCHAR(150) NOT NULL,
    name_en VARCHAR(150) DEFAULT NULL,
    level ENUM('governorate','city','district','area') NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (parent_id) REFERENCES locations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Categories & Services
-- ---------------------------------------------------------
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_ar VARCHAR(150) NOT NULL,
    name_en VARCHAR(150) DEFAULT NULL,
    icon VARCHAR(100) DEFAULT 'fa-toolbox',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name_ar VARCHAR(150) NOT NULL,
    name_en VARCHAR(150) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Provider profile & related
-- ---------------------------------------------------------
CREATE TABLE provider_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    category_id INT DEFAULT NULL,
    profession VARCHAR(150) DEFAULT NULL,
    specialization VARCHAR(150) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    experience_years INT DEFAULT 0,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    avg_rating DECIMAL(3,2) NOT NULL DEFAULT 0,
    rating_count INT NOT NULL DEFAULT 0,
    completed_jobs INT NOT NULL DEFAULT 0,
    response_rate DECIMAL(5,2) NOT NULL DEFAULT 100.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE provider_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    service_id INT NOT NULL,
    price_from DECIMAL(10,2) DEFAULT 0,
    price_to DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (provider_id) REFERENCES provider_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    UNIQUE KEY uq_provider_service (provider_id, service_id)
) ENGINE=InnoDB;

CREATE TABLE provider_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    location_id INT NOT NULL,
    FOREIGN KEY (provider_id) REFERENCES provider_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE,
    UNIQUE KEY uq_provider_location (provider_id, location_id)
) ENGINE=InnoDB;

CREATE TABLE provider_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    day_of_week TINYINT NOT NULL COMMENT '0=Sunday..6=Saturday',
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    FOREIGN KEY (provider_id) REFERENCES provider_profiles(id) ON DELETE CASCADE,
    INDEX idx_provider_availability_provider_day_time (provider_id, day_of_week, start_time, end_time)
) ENGINE=InnoDB;

CREATE TABLE provider_portfolio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    caption VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (provider_id) REFERENCES provider_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Service Requests / Workflow
-- ---------------------------------------------------------
CREATE TABLE service_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_number VARCHAR(30) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    provider_id INT DEFAULT NULL,
    service_id INT NOT NULL,
    location_id INT DEFAULT NULL,
    description TEXT DEFAULT NULL,
    preferred_date DATE DEFAULT NULL,
    preferred_time TIME DEFAULT NULL,
    budget DECIMAL(10,2) DEFAULT NULL,
    final_price DECIMAL(10,2) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    status ENUM('new','pending','accepted','in_progress','completed','cancelled','rejected') NOT NULL DEFAULT 'new',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES provider_profiles(id) ON DELETE SET NULL,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE request_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    changed_by INT DEFAULT NULL,
    old_status VARCHAR(20) DEFAULT NULL,
    new_status VARCHAR(20) NOT NULL,
    changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES service_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Reviews
-- ---------------------------------------------------------
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    provider_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT DEFAULT NULL,
    provider_reply TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES service_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES provider_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Complaints
-- ---------------------------------------------------------
CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_number VARCHAR(30) NOT NULL UNIQUE,
    created_by INT NOT NULL,
    against_user_id INT DEFAULT NULL,
    request_id INT DEFAULT NULL,
    subject VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('open','in_review','resolved','rejected','closed') NOT NULL DEFAULT 'open',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (against_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (request_id) REFERENCES service_requests(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE complaint_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Notifications
-- ---------------------------------------------------------
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message VARCHAR(500) NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Financial: invoices, payments, commissions
-- ---------------------------------------------------------
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(30) NOT NULL UNIQUE,
    request_id INT NOT NULL UNIQUE,
    service_price DECIMAL(10,2) NOT NULL,
    commission_rate DECIMAL(5,4) NOT NULL DEFAULT 0.10,
    commission_amount DECIMAL(10,2) NOT NULL,
    provider_net DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES service_requests(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    method ENUM('simulated_card','simulated_wallet') NOT NULL DEFAULT 'simulated_wallet',
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('unpaid','paid','refunded') NOT NULL DEFAULT 'paid',
    paid_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_simulated TINYINT(1) NOT NULL DEFAULT 1,
    reference VARCHAR(100) DEFAULT NULL,
    notes VARCHAR(500) DEFAULT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE platform_commissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- AI
-- ---------------------------------------------------------
CREATE TABLE ai_recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT DEFAULT NULL,
    customer_id INT NOT NULL,
    provider_id INT NOT NULL,
    score DECIMAL(6,2) NOT NULL,
    score_breakdown JSON DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES provider_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ai_interactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    input_text TEXT NOT NULL,
    detected_category_id INT DEFAULT NULL,
    detected_service_id INT DEFAULT NULL,
    confidence DECIMAL(5,2) DEFAULT 0,
    engine_used VARCHAR(30) DEFAULT 'local',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Provider platform evaluations
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS provider_evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    admin_id INT NOT NULL,
    quality_score TINYINT NOT NULL,
    reliability_score TINYINT NOT NULL,
    communication_score TINYINT NOT NULL,
    compliance_score TINYINT NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    comment TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_provider_eval(provider_id, created_at),
    FOREIGN KEY(provider_id) REFERENCES provider_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY(admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- AI Provider configuration & logs
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS ai_provider_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider VARCHAR(40) NOT NULL UNIQUE,
    model VARCHAR(120) DEFAULT NULL,
    api_key_encrypted TEXT DEFAULT NULL,
    base_url VARCHAR(500) DEFAULT NULL,
    temperature DECIMAL(4,2) DEFAULT 0.20,
    max_tokens INT DEFAULT 700,
    timeout INT DEFAULT 20,
    system_prompt TEXT DEFAULT NULL,
    is_enabled TINYINT(1) NOT NULL DEFAULT 0,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ai_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY, user_id INT DEFAULT NULL, provider VARCHAR(40) NOT NULL, operation VARCHAR(40) NOT NULL,
    success TINYINT(1) NOT NULL DEFAULT 0, fallback_used TINYINT(1) NOT NULL DEFAULT 0, duration_ms INT DEFAULT NULL,
    error_message VARCHAR(500) DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX idx_ai_logs_created(created_at), INDEX idx_ai_logs_provider(provider),
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO ai_provider_settings(provider,model,base_url,is_enabled,is_default) VALUES
('local',NULL,NULL,1,0),('gemini','gemini-3.1-flash-lite','https://generativelanguage.googleapis.com/v1beta',0,0),('openai','gpt-4o-mini','https://api.openai.com/v1',0,0),('groq',NULL,'https://api.groq.com/openai/v1',0,0),('openai_compatible',NULL,NULL,0,0);

-- ---------------------------------------------------------
-- System
-- ---------------------------------------------------------
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT DEFAULT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO system_settings (`key`,`value`) VALUES
('platform_commission_rate','0.10'),
('site_name','مِهَن'),
('site_tagline','منصة الخدمات المهنية'),
('default_language','ar'),
('timezone','Asia/Aden'),
('allow_customer_registration','1'),
('allow_provider_registration','1'),
('maintenance_mode','0'),
('maintenance_message','المنصة تحت الصيانة المؤقتة، نعود إليكم قريبًا.'),
('auto_provider_available','1'),
('reviews_enabled','1'),
('complaints_enabled','1'),
('notify_on_request','1'),
('currency','ريال يمني'),
('currency_code','YER'),
('invoice_prefix','INV'),
('notifications_enabled','1'),
('notify_on_completion','1'),
('notify_on_payment','1'),
('notify_on_complaint','1'),
('force_https','0'),
('activity_log_enabled','1'),
('show_system_errors','0')
ON DUPLICATE KEY UPDATE value=VALUES(value);

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    description VARCHAR(500) DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Indexes for common query patterns
-- ============================================================
CREATE INDEX idx_requests_status ON service_requests(status);
CREATE INDEX idx_requests_customer ON service_requests(customer_id);
CREATE INDEX idx_requests_provider ON service_requests(provider_id);
CREATE INDEX idx_provider_rating ON provider_profiles(avg_rating);
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read);
CREATE INDEX idx_complaints_status ON complaints(status);
