-- ============================================================
-- MIHAN - Demo Seed Data
-- Accounts: password for ALL users = Passw0rd!
-- Use on a fresh/demo database after importing database.sql.
-- ============================================================

CREATE DATABASE IF NOT EXISTS mihan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mihan;

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE platform_commissions;
TRUNCATE TABLE payments;
TRUNCATE TABLE invoices;
TRUNCATE TABLE ai_recommendations;
TRUNCATE TABLE ai_interactions;
TRUNCATE TABLE complaint_messages;
TRUNCATE TABLE complaints;
TRUNCATE TABLE reviews;
TRUNCATE TABLE request_status_history;
TRUNCATE TABLE service_requests;
TRUNCATE TABLE provider_portfolio;
TRUNCATE TABLE provider_availability;
TRUNCATE TABLE provider_locations;
TRUNCATE TABLE provider_services;
TRUNCATE TABLE provider_profiles;
TRUNCATE TABLE notifications;
TRUNCATE TABLE activity_logs;
TRUNCATE TABLE system_settings;
TRUNCATE TABLE role_permissions;
TRUNCATE TABLE permissions;
TRUNCATE TABLE services;
TRUNCATE TABLE categories;
TRUNCATE TABLE locations;
TRUNCATE TABLE users;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Users
-- ------------------------------------------------------------
INSERT INTO users
(id, name, email, phone, password_hash, role, is_active, is_verified_email)
VALUES
(1, 'مدير النظام', 'admin@mihan.test', '777000001', '$2y$12$bRho7ptTdUavPCzrnZisJ.bYWue8v/NTtAR4nRKJpWjrG6zx6eYYG', 'admin', 1, 1),
(2, 'أحمد محمد', 'customer1@mihan.test', '777000002', '$2y$12$bRho7ptTdUavPCzrnZisJ.bYWue8v/NTtAR4nRKJpWjrG6zx6eYYG', 'customer', 1, 1),
(3, 'سارة علي', 'customer2@mihan.test', '777000003', '$2y$12$bRho7ptTdUavPCzrnZisJ.bYWue8v/NTtAR4nRKJpWjrG6zx6eYYG', 'customer', 1, 1),
(4, 'محمد عبدالله', 'provider1@mihan.test', '777000004', '$2y$12$bRho7ptTdUavPCzrnZisJ.bYWue8v/NTtAR4nRKJpWjrG6zx6eYYG', 'provider', 1, 1),
(5, 'علي صالح', 'provider2@mihan.test', '777000005', '$2y$12$bRho7ptTdUavPCzrnZisJ.bYWue8v/NTtAR4nRKJpWjrG6zx6eYYG', 'provider', 1, 1),
(6, 'خالد أحمد', 'provider3@mihan.test', '777000006', '$2y$12$bRho7ptTdUavPCzrnZisJ.bYWue8v/NTtAR4nRKJpWjrG6zx6eYYG', 'provider', 1, 1),
(7, 'نورة العتيبي', 'employee@mihan.test', '777000007', '$2y$12$bRho7ptTdUavPCzrnZisJ.bYWue8v/NTtAR4nRKJpWjrG6zx6eYYG', 'employee', 1, 1);

-- ------------------------------------------------------------
-- Locations
-- ------------------------------------------------------------
INSERT INTO locations (id, parent_id, name_ar, name_en, level) VALUES
(1, NULL, 'تعز', 'Taiz', 'governorate'),
(2, 1, 'مدينة تعز', 'Taiz City', 'city'),
(3, 2, 'القاهرة', 'Al-Qahirah', 'district'),
(4, 2, 'المظفر', 'Al-Mudhaffar', 'district'),
(5, 2, 'صالة', 'Sala', 'district'),
(6, NULL, 'عدن', 'Aden', 'governorate'),
(7, 6, 'مدينة عدن', 'Aden City', 'city'),
(8, 7, 'المنصورة', 'Al-Mansurah', 'district'),
(9, 7, 'خور مكسر', 'Khor Maksar', 'district');

-- ------------------------------------------------------------
-- Categories
-- ------------------------------------------------------------
INSERT INTO categories (id, name_ar, name_en, icon) VALUES
(1, 'التكييف والتبريد', 'Air Conditioning', 'fa-snowflake'),
(2, 'الكهرباء', 'Electrical', 'fa-bolt'),
(3, 'السباكة', 'Plumbing', 'fa-faucet-drip'),
(4, 'النجارة', 'Carpentry', 'fa-hammer'),
(5, 'الأجهزة المنزلية', 'Home Appliances', 'fa-blender'),
(6, 'الدهان والتشطيبات', 'Painting & Finishing', 'fa-paint-roller'),
(7, 'تنظيف المنازل', 'Home Cleaning', 'fa-broom'),
(8, 'صيانة السيارات', 'Car Maintenance', 'fa-car');

-- ------------------------------------------------------------
-- Services
-- ------------------------------------------------------------
INSERT INTO services (id, category_id, name_ar, name_en, description) VALUES
(1, 1, 'صيانة مكيفات', 'AC Maintenance', 'فحص وصيانة وإصلاح أجهزة التكييف.'),
(2, 1, 'تنظيف مكيفات', 'AC Cleaning', 'تنظيف الوحدات الداخلية والخارجية للمكيفات.'),
(3, 1, 'تركيب مكيفات', 'AC Installation', 'تركيب وتجهيز أجهزة التكييف.'),
(4, 2, 'تمديدات كهربائية', 'Electrical Wiring', 'تنفيذ وإصلاح التمديدات الكهربائية المنزلية.'),
(5, 2, 'إصلاح الأعطال الكهربائية', 'Electrical Repairs', 'تشخيص وإصلاح الأعطال والقواطع والمفاتيح.'),
(6, 3, 'إصلاح تسربات المياه', 'Water Leak Repair', 'كشف وإصلاح تسربات المياه.'),
(7, 3, 'صيانة وتمديد السباكة', 'Plumbing Maintenance', 'إصلاح وتمديد شبكات السباكة.'),
(8, 4, 'أعمال النجارة', 'Carpentry Work', 'تصنيع وإصلاح الأبواب والأثاث والأعمال الخشبية.'),
(9, 5, 'صيانة الغسالات', 'Washing Machine Repair', 'تشخيص وإصلاح أعطال الغسالات.'),
(10, 5, 'صيانة الثلاجات', 'Refrigerator Repair', 'تشخيص وإصلاح أعطال الثلاجات.'),
(11, 6, 'دهان المنازل', 'Home Painting', 'أعمال الدهان الداخلي والخارجي.'),
(12, 7, 'تنظيف المنازل', 'Home Cleaning', 'خدمات تنظيف المنازل والمكاتب.'),
(13, 8, 'صيانة السيارات', 'Car Maintenance', 'فحص وصيانة الأعطال الأساسية للسيارات.');

-- ------------------------------------------------------------
-- Provider Profiles
-- ------------------------------------------------------------
INSERT INTO provider_profiles
(id, user_id, category_id, profession, specialization, bio, experience_years,
 is_verified, is_available, avg_rating, rating_count, completed_jobs, response_rate)
VALUES
(1, 4, 1, 'فني تكييف وتبريد', 'صيانة وتنظيف المكيفات',
 'فني متخصص في صيانة وتنظيف وتركيب أجهزة التكييف بخبرة عملية.',
 7, 1, 1, 4.80, 5, 126, 96.00),
(2, 5, 2, 'فني كهرباء', 'الأعمال الكهربائية المنزلية',
 'متخصص في التمديدات وتشخيص الأعطال الكهربائية المنزلية.',
 6, 1, 1, 4.60, 4, 98, 94.00),
(3, 6, 3, 'سباك', 'السباكة وكشف التسربات',
 'متخصص في إصلاح التسربات وتمديد وصيانة شبكات المياه.',
 8, 1, 1, 4.70, 6, 143, 97.00);

-- ------------------------------------------------------------
-- Provider Services
-- ------------------------------------------------------------
INSERT INTO provider_services (provider_id, service_id, price_from, price_to) VALUES
(1, 1, 5000, 20000),
(1, 2, 3000, 10000),
(1, 3, 10000, 30000),
(2, 4, 5000, 30000),
(2, 5, 3000, 15000),
(3, 6, 3000, 12000),
(3, 7, 5000, 25000);

-- ------------------------------------------------------------
-- Provider Locations
-- ------------------------------------------------------------
INSERT INTO provider_locations (provider_id, location_id) VALUES
(1, 2), (1, 3), (1, 4), (1, 5),
(2, 2), (2, 3), (2, 4),
(3, 2), (3, 4), (3, 5);

-- ------------------------------------------------------------
-- Weekly Availability
-- 0=Sunday ... 6=Saturday
-- ------------------------------------------------------------
INSERT INTO provider_availability (provider_id, day_of_week, start_time, end_time) VALUES
(1, 0, '08:00:00', '12:00:00'),
(1, 0, '16:00:00', '20:00:00'),
(1, 1, '08:00:00', '18:00:00'),
(1, 2, '08:00:00', '18:00:00'),
(1, 3, '08:00:00', '18:00:00'),
(1, 4, '08:00:00', '18:00:00'),
(1, 5, '16:00:00', '20:00:00'),
(2, 0, '09:00:00', '17:00:00'),
(2, 1, '09:00:00', '17:00:00'),
(2, 2, '09:00:00', '17:00:00'),
(2, 3, '09:00:00', '17:00:00'),
(2, 4, '09:00:00', '17:00:00'),
(2, 5, '09:00:00', '13:00:00'),
(3, 0, '08:00:00', '16:00:00'),
(3, 1, '08:00:00', '16:00:00'),
(3, 2, '08:00:00', '16:00:00'),
(3, 3, '08:00:00', '16:00:00'),
(3, 4, '08:00:00', '16:00:00'),
(3, 5, '08:00:00', '12:00:00');

-- ------------------------------------------------------------
-- Sample Service Requests
-- ------------------------------------------------------------
INSERT INTO service_requests
(id, request_number, customer_id, provider_id, service_id, location_id, description,
 preferred_date, preferred_time, budget, notes, status, created_at, updated_at)
VALUES
(1, 'MIH-000001', 2, 1, 1, 3,
 'المكيف يعمل لكن لا يبرد بشكل جيد.',
 '2026-09-13', '10:00:00', 15000,
 'يفضل الحضور صباحًا.', 'completed', '2026-09-08 09:00:00', '2026-09-13 12:00:00'),
(2, 'MIH-000002', 3, 2, 5, 4,
 'القاطع يفصل عند تشغيل أكثر من جهاز.',
 '2026-09-14', '11:00:00', 10000,
 'يوجد انقطاع متكرر في الغرفة الرئيسية.', 'completed', '2026-09-09 10:30:00', '2026-09-14 13:00:00'),
(3, 'MIH-000003', 2, 3, 6, 5,
 'يوجد تسرب ماء أسفل الحوض.',
 '2026-09-15', '09:00:00', 8000,
 NULL, 'accepted', '2026-09-11 14:00:00', '2026-09-11 15:00:00'),
(4, 'MIH-000004', 3, NULL, 2, 3,
 'أحتاج تنظيف وصيانة للمكيف قبل الصيف.',
 '2026-09-16', '17:00:00', 7000,
 NULL, 'pending', '2026-09-12 08:30:00', '2026-09-12 08:30:00'),
(5, 'MIH-000005', 2, NULL, 9, 4,
 'الغسالة لا تبدأ التشغيل.', NULL, NULL, 12000,
 NULL, 'pending', '2026-09-12 09:00:00', '2026-09-12 09:00:00'),
(6, 'MIH-000006', 3, NULL, 3, 3,
 'أحتاج تركيب مكيف جديد.', NULL, NULL, 30000,
 NULL, 'pending', '2026-09-07 11:00:00', '2026-09-07 11:30:00');

-- ------------------------------------------------------------
-- Request Status History
-- ------------------------------------------------------------
INSERT INTO request_status_history
(request_id, changed_by, old_status, new_status, changed_at) VALUES
(1, 2, NULL, 'pending', '2026-09-08 09:00:00'),
(1, 4, 'pending', 'accepted', '2026-09-08 10:00:00'),
(1, 4, 'accepted', 'in_progress', '2026-09-13 10:15:00'),
(1, 4, 'in_progress', 'completed', '2026-09-13 12:00:00'),
(2, 3, NULL, 'pending', '2026-09-09 10:30:00'),
(2, 5, 'pending', 'accepted', '2026-09-09 11:00:00'),
(2, 5, 'accepted', 'in_progress', '2026-09-14 11:15:00'),
(2, 5, 'in_progress', 'completed', '2026-09-14 13:00:00'),
(3, 2, NULL, 'pending', '2026-09-11 14:00:00'),
(3, 6, 'pending', 'accepted', '2026-09-11 15:00:00'),
(4, 3, NULL, 'pending', '2026-09-12 08:30:00'),
(5, 2, NULL, 'pending', '2026-09-12 09:00:00'),
(6, 3, NULL, 'pending', '2026-09-07 11:00:00'),
(6, 4, 'pending', 'rejected', '2026-09-07 11:30:00'),
(6, 4, 'rejected', 'pending', '2026-09-07 11:31:00');

-- ------------------------------------------------------------
-- Reviews
-- ------------------------------------------------------------
INSERT INTO reviews
(request_id, customer_id, provider_id, rating, comment, provider_reply, created_at) VALUES
(1, 2, 1, 5, 'خدمة ممتازة والتزام بالموعد.', 'شكرًا لك، سعدنا بخدمتك.', '2026-09-13 14:00:00'),
(2, 3, 2, 4, 'تم حل المشكلة بسرعة.', 'شكرًا لتقييمك.', '2026-09-14 15:00:00');

-- ------------------------------------------------------------
-- Invoices / Payments / Commissions
-- ------------------------------------------------------------
INSERT INTO invoices
(id, invoice_number, request_id, service_price, commission_rate, commission_amount,
 provider_net, total_amount, payment_status, created_at) VALUES
(1, 'INV-000001', 1, 15000, 0.1000, 1500, 13500, 15000, 'paid', '2026-09-13 12:05:00'),
(2, 'INV-000002', 2, 10000, 0.1000, 1000, 9000, 10000, 'paid', '2026-09-14 13:05:00');

INSERT INTO payments
(id, invoice_id, method, amount, status, paid_at, is_simulated) VALUES
(1, 1, 'simulated_wallet', 15000, 'paid', '2026-09-13 12:10:00', 1),
(2, 2, 'simulated_card', 10000, 'paid', '2026-09-14 13:10:00', 1);

INSERT INTO platform_commissions (id, invoice_id, amount) VALUES
(1, 1, 1500),
(2, 2, 1000);

-- ------------------------------------------------------------
-- Notifications
-- ------------------------------------------------------------
INSERT INTO notifications (user_id, title, message, link, is_read) VALUES
(2, 'تم إكمال طلبك', 'تم إكمال طلب صيانة المكيف. يمكنك الآن تقييم مقدم الخدمة.', '/customer/requests/1', 0),
(3, 'تم إكمال طلبك', 'تم إكمال طلب الكهرباء. يمكنك الآن تقييم مقدم الخدمة.', '/customer/requests/2', 1),
(4, 'طلب خدمة جديد', 'لديك طلب تكييف جديد بانتظار المراجعة.', '/provider/requests/4', 0),
(6, 'تم قبول الطلب', 'تم إسناد طلب صيانة تسرب المياه إليك.', '/provider/requests/3', 0),
(1, 'شكوى جديدة', 'تم تسجيل شكوى جديدة وتحتاج إلى مراجعة.', '/admin/complaints', 0);

-- ------------------------------------------------------------
-- Sample Complaint
-- ------------------------------------------------------------
INSERT INTO complaints
(id, complaint_number, created_by, against_user_id, request_id, subject, description, status) VALUES
(1, 'CMP-000001', 3, 5, 2,
 'تأخر في الموعد',
 'أرغب في تسجيل ملاحظة حول التأخر في موعد الخدمة.',
 'resolved');

INSERT INTO complaint_messages (complaint_id, sender_id, message) VALUES
(1, 3, 'أرجو مراجعة الموضوع.'),
(1, 1, 'تمت مراجعة الطلب والتواصل مع مقدم الخدمة.');

-- ------------------------------------------------------------
-- AI Sample Data
-- ------------------------------------------------------------
INSERT INTO ai_interactions
(user_id, input_text, detected_category_id, detected_service_id, confidence, engine_used) VALUES
(2, 'المكيف يعمل لكن لا يبرد', 1, 1, 92.00, 'local'),
(3, 'القاطع يفصل عند تشغيل الأجهزة', 2, 5, 88.00, 'local'),
(2, 'يوجد تسريب ماء تحت الحوض', 3, 6, 94.00, 'local');

INSERT INTO ai_recommendations
(request_id, customer_id, provider_id, score, score_breakdown) VALUES
(1, 2, 1, 96.00, '{"service":30,"location":20,"rating":19.2,"experience":10,"availability":10,"reliability":6.8}'),
(2, 3, 2, 91.00, '{"service":30,"location":20,"rating":18.4,"experience":9,"availability":8,"reliability":5.6}'),
(3, 2, 3, 94.00, '{"service":30,"location":20,"rating":18.8,"experience":10,"availability":9,"reliability":6.2}');

-- ------------------------------------------------------------
-- Permissions foundation
-- ------------------------------------------------------------
INSERT INTO permissions (`key`, description) VALUES
('manage_users', 'إدارة المستخدمين'),
('manage_categories', 'إدارة التصنيفات والخدمات'),
('manage_locations', 'إدارة المناطق'),
('verify_providers', 'توثيق مقدمي الخدمة'),
('manage_complaints', 'إدارة الشكاوى'),
('view_reports', 'مشاهدة التقارير'),
('manage_requests', 'إدارة الطلبات'),
('manage_finance', 'إدارة المالية والفواتير'),
('manage_settings', 'إدارة إعدادات النظام'),
('manage_ai', 'إدارة مزودي ونماذج الذكاء الاصطناعي'),
('create_request', 'إنشاء طلب خدمة'),
('provide_service', 'تقديم الخدمات'),
('manage_payouts', 'إدارة مستحقات مقدمي الخدمة'),
('manage_employees', 'إدارة موظفي المنصة'),
('view_providers', 'عرض مقدمي الخدمة'),
('view_finance', 'عرض التقارير المالية'),
('view_conversations', 'عرض المحادثات'),
('manage_reviews', 'إدارة التقييمات'),
('view_dashboard', 'عرض لوحة التحكم');

-- Admin gets ALL permissions
INSERT INTO role_permissions (role, permission_id)
SELECT 'admin', id FROM permissions;

-- Customer gets create_request only
INSERT INTO role_permissions (role, permission_id)
SELECT 'customer', id FROM permissions WHERE `key` = 'create_request';

-- Provider gets provide_service only
INSERT INTO role_permissions (role, permission_id)
SELECT 'provider', id FROM permissions WHERE `key` = 'provide_service';

-- Employee gets operational permissions (NOT manage_employees, manage_users, manage_ai, manage_settings, manage_payouts, manage_finance)
INSERT INTO role_permissions (role, permission_id)
SELECT 'employee', id FROM permissions WHERE `key` IN (
  'view_providers', 'manage_reviews', 'view_finance', 'view_conversations',
  'manage_complaints', 'manage_requests', 'view_reports', 'view_dashboard'
);

-- ------------------------------------------------------------
-- System Settings
-- ------------------------------------------------------------
INSERT INTO system_settings (`key`, `value`) VALUES
('site_name', 'مِهَن – MIHAN'),
('site_tagline', 'منصة ذكية لإدارة الخدمات المهنية المحلية ودعم القرار'),
('commission_rate', '0.10'),
('default_language', 'ar'),
('ai_engine', 'local');

-- ------------------------------------------------------------
-- Refresh provider rating aggregates to match demo reviews (requests 1 and 2)
-- ------------------------------------------------------------
UPDATE provider_profiles pp
SET avg_rating = COALESCE((SELECT ROUND(AVG(r.rating),2) FROM reviews r WHERE r.provider_id = pp.id), 0),
    rating_count = COALESCE((SELECT COUNT(*) FROM reviews r WHERE r.provider_id = pp.id), 0);

-- ============================================================
-- End MIHAN Demo Seed
-- ============================================================

INSERT INTO ai_provider_settings(provider,model,base_url,is_enabled,is_default) VALUES
('local',NULL,NULL,1,1),('gemini','gemini-2.5-flash','https://generativelanguage.googleapis.com/v1beta/openai',0,0),('openai','gpt-4o-mini','https://api.openai.com/v1',0,0),('groq',NULL,'https://api.groq.com/openai/v1',0,0),('openai_compatible',NULL,NULL,0,0)
ON DUPLICATE KEY UPDATE provider=VALUES(provider);
