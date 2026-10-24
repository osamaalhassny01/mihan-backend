USE mihan;

-- ============================================================
-- Migration 003: Comprehensive RBAC Permissions Catalogue
-- ============================================================

INSERT IGNORE INTO permissions (`key`, description) VALUES
('manage_users', 'إدارة المستخدمين'),
('manage_categories', 'إدارة التصنيفات والخدمات'),
('manage_locations', 'إدارة المناطق'),
('verify_providers', 'توثيق مقدمي الخدمة'),
('manage_complaints', 'إدارة الشكاوى'),
('view_reports', 'مشاهدة التقارير والتحليلات'),
('manage_requests', 'إدارة الطلبات'),
('manage_finance', 'إدارة المالية والفواتير'),
('manage_settings', 'إدارة إعدادات النظام'),
('manage_ai', 'إدارة مزودي ونماذج الذكاء الاصطناعي'),
('create_request', 'إنشاء طلب خدمة'),
('provide_service', 'تقديم الخدمات');

-- Assign all permissions to admin
INSERT IGNORE INTO role_permissions (role, permission_id)
SELECT 'admin', id FROM permissions;

-- Assign customer permissions
INSERT IGNORE INTO role_permissions (role, permission_id)
SELECT 'customer', id FROM permissions WHERE `key` = 'create_request';

-- Assign provider permissions
INSERT IGNORE INTO role_permissions (role, permission_id)
SELECT 'provider', id FROM permissions WHERE `key` = 'provide_service';
