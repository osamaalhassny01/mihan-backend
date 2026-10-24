-- MIHAN 011: four-role platform, internal messaging, customer platform payment, provider payouts
USE mihan;

ALTER TABLE users MODIFY COLUMN role ENUM('customer','provider','employee','admin') NOT NULL DEFAULT 'customer';
ALTER TABLE role_permissions MODIFY COLUMN role ENUM('customer','provider','employee','admin') NOT NULL;

CREATE TABLE IF NOT EXISTS conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    provider_id INT NOT NULL,
    last_message_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES service_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES provider_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS conversation_messages (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation_messages_conversation_created (conversation_id, created_at),
    INDEX idx_conversation_messages_sender_read (sender_id, is_read)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS provider_payouts (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    invoice_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','paid','failed','cancelled') NOT NULL DEFAULT 'paid',
    reference VARCHAR(100) DEFAULT NULL,
    notes VARCHAR(500) DEFAULT NULL,
    paid_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_by INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (provider_id) REFERENCES provider_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_provider_payout_provider_status (provider_id, status),
    INDEX idx_provider_payout_invoice (invoice_id)
) ENGINE=InnoDB;

INSERT IGNORE INTO permissions (`key`, description) VALUES
('view_providers', 'عرض ومتابعة مقدمي الخدمة'),
('manage_reviews', 'إدارة ومتابعة التقييمات'),
('view_finance', 'مشاهدة العمليات المالية'),
('manage_payouts', 'إدارة وتحويل مستحقات مقدمي الخدمة'),
('view_conversations', 'متابعة محادثات طلبات الخدمة'),
('manage_employees', 'إدارة موظفي المنصة'),
('view_dashboard', 'عرض لوحة المتابعة التشغيلية');

INSERT IGNORE INTO role_permissions (role, permission_id)
SELECT 'employee', id FROM permissions
WHERE `key` IN ('view_providers','manage_reviews','view_finance','view_conversations','manage_complaints','manage_requests','view_dashboard','view_reports');

INSERT INTO system_settings (`key`,`value`) VALUES
('internal_messaging_enabled','1'),
('platform_payment_required','1')
ON DUPLICATE KEY UPDATE value=VALUES(value);
