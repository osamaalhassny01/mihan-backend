-- ============================================================
-- Migration 012: User Management, Employee Dashboard & User Permissions Override System
-- ============================================================

USE mihan;

-- 1. Ensure user role ENUM supports all 4 platform roles
ALTER TABLE users MODIFY COLUMN role ENUM('customer','provider','employee','admin') NOT NULL DEFAULT 'customer';
ALTER TABLE role_permissions MODIFY COLUMN role ENUM('customer','provider','employee','admin') NOT NULL;

-- 2. Create user_permissions table for individual user permission overrides
CREATE TABLE IF NOT EXISTS user_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    permission_id INT NOT NULL,
    is_allowed TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE KEY uq_user_perm (user_id, permission_id),
    INDEX idx_user_perm_user (user_id)
) ENGINE=InnoDB;

-- 3. Ensure all 19 standard platform permissions exist
INSERT IGNORE INTO permissions (`key`, description) VALUES
('manage_users', 'إدارة المستخدمين وحساباتهم'),
('manage_categories', 'إدارة التصنيفات والخدمات'),
('manage_locations', 'إدارة المناطق والمواقع'),
('verify_providers', 'توثيق مقدمي الخدمة'),
('manage_complaints', 'إدارة ومعالجة الشكاوى'),
('view_reports', 'مشاهدة التقارير والإحصائيات'),
('manage_requests', 'إدارة ومتابعة طلبات الخدمات'),
('manage_finance', 'إدارة العمليات المالية والفواتير'),
('manage_settings', 'إدارة إعدادات المنصة والنظام'),
('manage_ai', 'إدارة نماذج ومزودي الذكاء الاصطناعي'),
('create_request', 'إنشاء طلب خدمة جديد'),
('provide_service', 'تقديم الخدمات وقبول الطلبات'),
('manage_payouts', 'إدارة وتحويل مستحقات مقدمي الخدمة'),
('manage_employees', 'إدارة موظفي المنصة وصلاحياتهم'),
('view_providers', 'عرض قائمة ومحفظة مقدمي الخدمة'),
('view_finance', 'عرض ومتابعة التقارير المالية'),
('view_conversations', 'عرض ومراقبة محادثات الطلبات'),
('manage_reviews', 'إدارة ومراجعة تقييمات العملاء'),
('view_dashboard', 'عرض لوحة التحكم والإحصائيات العامة');

-- 4. Map Admin Role Permissions (ALL permissions)
INSERT IGNORE INTO role_permissions (role, permission_id)
SELECT 'admin', id FROM permissions;

-- 5. Map Customer Role Permissions
INSERT IGNORE INTO role_permissions (role, permission_id)
SELECT 'customer', id FROM permissions WHERE `key` = 'create_request';

-- 6. Map Provider Role Permissions
INSERT IGNORE INTO role_permissions (role, permission_id)
SELECT 'provider', id FROM permissions WHERE `key` = 'provide_service';

-- 7. Map Employee Role Permissions (Operational suite only, excluding admin-exclusive keys)
INSERT IGNORE INTO role_permissions (role, permission_id)
SELECT 'employee', id FROM permissions WHERE `key` IN (
    'view_dashboard',
    'view_providers',
    'manage_reviews',
    'view_finance',
    'view_conversations',
    'manage_complaints',
    'manage_requests',
    'view_reports'
);
